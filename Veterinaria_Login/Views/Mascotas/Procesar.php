<?php
/**
 * PROCESAR.PHP - Procesar acciones de mascotas
 * Maneja registro, edición y eliminación de mascotas
 */

// Limpiar cualquier salida previa
ob_start();

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';

// Limpiar buffer de salida
ob_clean();

// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'No estás autenticado'
    ]);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit();
}

// Obtener la acción a realizar
$action = $_POST['action'] ?? '';

try {
    $conn = getConnection();
    
    switch ($action) {
        case 'registrar':
            $result = registrarMascota($conn);
            echo json_encode($result);
            break;
            
        case 'editar':
            $result = editarMascota($conn);
            echo json_encode($result);
            break;
            
        case 'eliminar':
            $result = eliminarMascota($conn);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    error_log('Error en procesar.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

// Limpiar y enviar salida
ob_end_flush();
exit();

/**
 * Registrar una nueva mascota
 */
function registrarMascota($conn) {
    try {
        // Validar y sanitizar datos
        $id_dueno = intval($_POST['id_dueno'] ?? 0);
        $nombre = sanitize_input($_POST['nombre'] ?? '');
        $fecha_nacimiento = sanitize_input($_POST['fecha_nacimiento'] ?? '');
        $genero = sanitize_input($_POST['genero'] ?? '');
        $especie = sanitize_input($_POST['especie'] ?? '');
        $raza = sanitize_input($_POST['raza'] ?? '');
        $peso = floatval($_POST['peso'] ?? 0);
        $color = sanitize_input($_POST['color'] ?? '');
        $diagnostico = sanitize_input($_POST['diagnostico'] ?? '');
        $antecedentes = sanitize_input($_POST['antecedentes'] ?? '');
        $fecha_ingreso = sanitize_input($_POST['fecha_ingreso'] ?? '');
        $estado = sanitize_input($_POST['estado'] ?? 'Activo');
        
        // Validaciones
        if ($id_dueno <= 0) {
            return [
                'success' => false,
                'message' => 'Debe seleccionar un dueño válido'
            ];
        }
        
        if (empty($nombre) || empty($especie) || empty($raza)) {
            return [
                'success' => false,
                'message' => 'Los campos obligatorios no pueden estar vacíos'
            ];
        }
        
        if ($peso <= 0) {
            return [
                'success' => false,
                'message' => 'El peso debe ser un valor positivo'
            ];
        }
        
        // Verificar que el dueño existe
        $stmt = $conn->prepare("SELECT id_dueno FROM dueno WHERE id_dueno = ?");
        $stmt->bind_param("i", $id_dueno);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'El dueño seleccionado no existe en el sistema'
            ];
        }
        $stmt->close();
        
        // Manejar la foto (si se subió)
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $foto_nombre = $_FILES['foto']['name'];
            $foto_extension = strtolower(pathinfo($foto_nombre, PATHINFO_EXTENSION));
            
            // Validar extensión
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($foto_extension, $extensiones_permitidas)) {
                return [
                    'success' => false,
                    'message' => 'Formato de foto no permitido. Use JPG, PNG o GIF'
                ];
            }
            
            // Validar tamaño (5MB máximo)
            if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'La foto no debe superar los 5MB'
                ];
            }
            
            // Crear nombre único para la foto
            $foto = uniqid('mascota_') . '.' . $foto_extension;
            $foto_destino = '../../uploads/mascotas/' . $foto;
            
            // Crear directorio si no existe
            if (!is_dir('../../uploads/mascotas/')) {
                mkdir('../../uploads/mascotas/', 0777, true);
            }
            
            // Mover archivo
            if (!move_uploaded_file($foto_tmp, $foto_destino)) {
                return [
                    'success' => false,
                    'message' => 'Error al subir la foto'
                ];
            }
        }
        
        // Insertar la nueva mascota
        $sql = "INSERT INTO mascota (
            id_dueno,
            nombre,
            foto,
            fecha_nacimiento,
            genero,
            especie,
            raza,
            peso,
            color,
            diagnostico,
            antecedentes,
            fecha_ingreso,
            fecha_ultima_visita,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Error al preparar la consulta: ' . $conn->error
            ];
        }
        
        $stmt->bind_param(
            "issssssdsssss",
            $id_dueno,
            $nombre,
            $foto,
            $fecha_nacimiento,
            $genero,
            $especie,
            $raza,
            $peso,
            $color,
            $diagnostico,
            $antecedentes,
            $fecha_ingreso,
            $estado
        );
        
        if ($stmt->execute()) {
            $id_mascota = $conn->insert_id;
            
            // Log de actividad
            try {
                logActivity('REGISTRAR_MASCOTA', "Mascota registrada: $nombre (ID: $id_mascota)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Mascota registrada exitosamente',
                'id_mascota' => $id_mascota
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            // Eliminar foto si hubo error
            if ($foto && file_exists($foto_destino)) {
                unlink($foto_destino);
            }
            
            return [
                'success' => false,
                'message' => 'Error al registrar la mascota: ' . $error
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error inesperado: ' . $e->getMessage()
        ];
    }
}

/**
 * Editar una mascota existente
 */
function editarMascota($conn) {
    try {
        $id_mascota = intval($_POST['id_mascota'] ?? 0);
        
        if ($id_mascota <= 0) {
            return [
                'success' => false,
                'message' => 'ID de mascota no válido'
            ];
        }
        
        // Validar y sanitizar datos
        $id_dueno = intval($_POST['id_dueno'] ?? 0);
        $nombre = sanitize_input($_POST['nombre'] ?? '');
        $fecha_nacimiento = sanitize_input($_POST['fecha_nacimiento'] ?? '');
        $genero = sanitize_input($_POST['genero'] ?? '');
        $especie = sanitize_input($_POST['especie'] ?? '');
        $raza = sanitize_input($_POST['raza'] ?? '');
        $peso = floatval($_POST['peso'] ?? 0);
        $color = sanitize_input($_POST['color'] ?? '');
        $diagnostico = sanitize_input($_POST['diagnostico'] ?? '');
        $antecedentes = sanitize_input($_POST['antecedentes'] ?? '');
        $fecha_ingreso = sanitize_input($_POST['fecha_ingreso'] ?? '');
        $estado = sanitize_input($_POST['estado'] ?? 'Activo');
        
        // Validaciones
        if ($id_dueno <= 0) {
            return [
                'success' => false,
                'message' => 'Debe seleccionar un dueño válido'
            ];
        }
        
        if (empty($nombre) || empty($especie) || empty($raza)) {
            return [
                'success' => false,
                'message' => 'Los campos obligatorios no pueden estar vacíos'
            ];
        }
        
        if ($peso <= 0) {
            return [
                'success' => false,
                'message' => 'El peso debe ser un valor positivo'
            ];
        }
        
        // Obtener foto actual
        $stmt = $conn->prepare("SELECT foto FROM mascota WHERE id_mascota = ?");
        $stmt->bind_param("i", $id_mascota);
        $stmt->execute();
        $result = $stmt->get_result();
        $mascota_actual = $result->fetch_assoc();
        $stmt->close();
        
        if (!$mascota_actual) {
            return [
                'success' => false,
                'message' => 'La mascota no existe'
            ];
        }
        
        $foto = $mascota_actual['foto'];
        
        // Manejar nueva foto si se subió
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $foto_nombre = $_FILES['foto']['name'];
            $foto_extension = strtolower(pathinfo($foto_nombre, PATHINFO_EXTENSION));
            
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($foto_extension, $extensiones_permitidas)) {
                return [
                    'success' => false,
                    'message' => 'Formato de foto no permitido'
                ];
            }
            
            if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'La foto no debe superar los 5MB'
                ];
            }
            
            // Eliminar foto anterior
            if ($foto && file_exists('../../uploads/mascotas/' . $foto)) {
                unlink('../../uploads/mascotas/' . $foto);
            }
            
            // Subir nueva foto
            $foto = uniqid('mascota_') . '.' . $foto_extension;
            $foto_destino = '../../uploads/mascotas/' . $foto;
            
            if (!is_dir('../../uploads/mascotas/')) {
                mkdir('../../uploads/mascotas/', 0777, true);
            }
            
            if (!move_uploaded_file($foto_tmp, $foto_destino)) {
                return [
                    'success' => false,
                    'message' => 'Error al subir la foto'
                ];
            }
        }
        
        // Actualizar la mascota
        $sql = "UPDATE mascota SET 
            id_dueno = ?,
            nombre = ?,
            foto = ?,
            fecha_nacimiento = ?,
            genero = ?,
            especie = ?,
            raza = ?,
            peso = ?,
            color = ?,
            diagnostico = ?,
            antecedentes = ?,
            fecha_ingreso = ?,
            fecha_ultima_visita = NOW(),
            estado = ?
            WHERE id_mascota = ?";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Error al preparar la consulta: ' . $conn->error
            ];
        }
        
        $stmt->bind_param(
            "issssssdsssssi",
            $id_dueno,
            $nombre,
            $foto,
            $fecha_nacimiento,
            $genero,
            $especie,
            $raza,
            $peso,
            $color,
            $diagnostico,
            $antecedentes,
            $fecha_ingreso,
            $estado,
            $id_mascota
        );
        
        if ($stmt->execute()) {
            try {
                logActivity('EDITAR_MASCOTA', "Mascota editada: $nombre (ID: $id_mascota)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Mascota actualizada exitosamente'
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            return [
                'success' => false,
                'message' => 'Error al actualizar la mascota: ' . $error
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error inesperado: ' . $e->getMessage()
        ];
    }
}

/**
 * Eliminar una mascota
 */
function eliminarMascota($conn) {
    try {
        $id_mascota = intval($_POST['id_mascota'] ?? 0);
        
        if ($id_mascota <= 0) {
            return [
                'success' => false,
                'message' => 'ID de mascota no válido'
            ];
        }
        
        // Obtener información de la mascota antes de eliminar
        $stmt = $conn->prepare("SELECT nombre, foto FROM mascota WHERE id_mascota = ?");
        $stmt->bind_param("i", $id_mascota);
        $stmt->execute();
        $result = $stmt->get_result();
        $mascota = $result->fetch_assoc();
        
        if (!$mascota) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'La mascota no existe'
            ];
        }
        $stmt->close();
        
        // Eliminar la mascota
        $stmt = $conn->prepare("DELETE FROM mascota WHERE id_mascota = ?");
        $stmt->bind_param("i", $id_mascota);
        
        if ($stmt->execute()) {
            // Eliminar foto si existe
            if ($mascota['foto'] && file_exists('../../uploads/mascotas/' . $mascota['foto'])) {
                unlink('../../uploads/mascotas/' . $mascota['foto']);
            }
            
            try {
                logActivity('ELIMINAR_MASCOTA', "Mascota eliminada: {$mascota['nombre']} (ID: $id_mascota)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Mascota eliminada exitosamente'
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            return [
                'success' => false,
                'message' => 'Error al eliminar la mascota: ' . $error
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error inesperado: ' . $e->getMessage()
        ];
    }
}
?>