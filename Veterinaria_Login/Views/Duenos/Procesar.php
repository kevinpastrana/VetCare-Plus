<?php
/**
 * PROCESAR.PHP - Procesar acciones de dueños
 * Maneja registro, edición y eliminación de dueños
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
            $result = registrarDueno($conn);
            echo json_encode($result);
            break;
            
        case 'editar':
            $result = editarDueno($conn);
            echo json_encode($result);
            break;
            
        case 'eliminar':
            $result = eliminarDueno($conn);
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
 * Registrar un nuevo dueño
 */
function registrarDueno($conn) {
    try {
        // Validar y sanitizar datos
        $primer_nombre = sanitize_input($_POST['primer_nombre'] ?? '');
        $segundo_nombre = sanitize_input($_POST['segundo_nombre'] ?? '');
        $primer_apellido = sanitize_input($_POST['primer_apellido'] ?? '');
        $segundo_apellido = sanitize_input($_POST['segundo_apellido'] ?? '');
        $cedula = sanitize_input($_POST['cedula'] ?? '');
        $fecha_nacimiento = sanitize_input($_POST['fecha_nacimiento'] ?? '');
        $genero = sanitize_input($_POST['genero'] ?? '');
        $ocupacion = sanitize_input($_POST['ocupacion'] ?? '');
        $telefono = sanitize_input($_POST['telefono'] ?? '');
        $correo_electronico = sanitize_input($_POST['correo_electronico'] ?? '');
        $ciudad = sanitize_input($_POST['ciudad'] ?? '');
        $direccion = sanitize_input($_POST['direccion'] ?? '');
        
        // Validaciones
        if (empty($primer_nombre) || empty($primer_apellido) || empty($cedula)) {
            return [
                'success' => false,
                'message' => 'Los campos obligatorios no pueden estar vacíos'
            ];
        }
        
        if (!isValidCedula($cedula)) {
            return [
                'success' => false,
                'message' => 'La cédula debe tener entre 6 y 10 dígitos'
            ];
        }
        
        if (!isValidEmail($correo_electronico)) {
            return [
                'success' => false,
                'message' => 'El correo electrónico no es válido'
            ];
        }
        
        if (!isValidPhone($telefono)) {
            return [
                'success' => false,
                'message' => 'El teléfono debe ser un número colombiano de 10 dígitos que inicie con 3'
            ];
        }
        
        // Verificar si la cédula ya existe
        $stmt = $conn->prepare("SELECT id_dueno FROM dueno WHERE cedula = ?");
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $conn->error
            ];
        }
        
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'La cédula ya está registrada en el sistema'
            ];
        }
        $stmt->close();
        
        // Insertar el nuevo dueño
        $sql = "INSERT INTO dueno (
            primer_nombre, 
            segundo_nombre, 
            primer_apellido, 
            segundo_apellido, 
            cedula, 
            fecha_nacimiento, 
            genero, 
            ocupacion, 
            telefono, 
            correo_electronico, 
            ciudad, 
            direccion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Error al preparar la consulta: ' . $conn->error
            ];
        }
        
        $stmt->bind_param(
            "ssssssssssss",
            $primer_nombre,
            $segundo_nombre,
            $primer_apellido,
            $segundo_apellido,
            $cedula,
            $fecha_nacimiento,
            $genero,
            $ocupacion,
            $telefono,
            $correo_electronico,
            $ciudad,
            $direccion
        );
        
        if ($stmt->execute()) {
            $id_dueno = $conn->insert_id;
            
            // Log de actividad (si falla, no afecta el resultado)
            try {
                logActivity('REGISTRAR_DUENO', "Dueño registrado: $primer_nombre $primer_apellido (ID: $id_dueno)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Dueño registrado exitosamente',
                'id_dueno' => $id_dueno
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            return [
                'success' => false,
                'message' => 'Error al registrar el dueño: ' . $error
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
 * Editar un dueño existente
 */
function editarDueno($conn) {
    try {
        $id_dueno = intval($_POST['id_dueno'] ?? 0);
        
        if ($id_dueno <= 0) {
            return [
                'success' => false,
                'message' => 'ID de dueño no válido'
            ];
        }
        
        // Validar y sanitizar datos
        $primer_nombre = sanitize_input($_POST['primer_nombre'] ?? '');
        $segundo_nombre = sanitize_input($_POST['segundo_nombre'] ?? '');
        $primer_apellido = sanitize_input($_POST['primer_apellido'] ?? '');
        $segundo_apellido = sanitize_input($_POST['segundo_apellido'] ?? '');
        $cedula = sanitize_input($_POST['cedula'] ?? '');
        $fecha_nacimiento = sanitize_input($_POST['fecha_nacimiento'] ?? '');
        $genero = sanitize_input($_POST['genero'] ?? '');
        $ocupacion = sanitize_input($_POST['ocupacion'] ?? '');
        $telefono = sanitize_input($_POST['telefono'] ?? '');
        $correo_electronico = sanitize_input($_POST['correo_electronico'] ?? '');
        $ciudad = sanitize_input($_POST['ciudad'] ?? '');
        $direccion = sanitize_input($_POST['direccion'] ?? '');
        
        // Validaciones
        if (empty($primer_nombre) || empty($primer_apellido) || empty($cedula)) {
            return [
                'success' => false,
                'message' => 'Los campos obligatorios no pueden estar vacíos'
            ];
        }
        
        if (!isValidCedula($cedula)) {
            return [
                'success' => false,
                'message' => 'La cédula debe tener entre 6 y 10 dígitos'
            ];
        }
        
        if (!isValidEmail($correo_electronico)) {
            return [
                'success' => false,
                'message' => 'El correo electrónico no es válido'
            ];
        }
        
        if (!isValidPhone($telefono)) {
            return [
                'success' => false,
                'message' => 'El teléfono debe ser un número colombiano de 10 dígitos que inicie con 3'
            ];
        }
        
        // Verificar si la cédula ya existe en otro registro
        $stmt = $conn->prepare("SELECT id_dueno FROM dueno WHERE cedula = ? AND id_dueno != ?");
        $stmt->bind_param("si", $cedula, $id_dueno);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'La cédula ya está registrada en otro dueño'
            ];
        }
        $stmt->close();
        
        // Actualizar el dueño
        $sql = "UPDATE dueno SET 
            primer_nombre = ?,
            segundo_nombre = ?,
            primer_apellido = ?,
            segundo_apellido = ?,
            cedula = ?,
            fecha_nacimiento = ?,
            genero = ?,
            ocupacion = ?,
            telefono = ?,
            correo_electronico = ?,
            ciudad = ?,
            direccion = ?
            WHERE id_dueno = ?";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Error al preparar la consulta: ' . $conn->error
            ];
        }
        
        $stmt->bind_param(
            "ssssssssssssi",
            $primer_nombre,
            $segundo_nombre,
            $primer_apellido,
            $segundo_apellido,
            $cedula,
            $fecha_nacimiento,
            $genero,
            $ocupacion,
            $telefono,
            $correo_electronico,
            $ciudad,
            $direccion,
            $id_dueno
        );
        
        if ($stmt->execute()) {
            try {
                logActivity('EDITAR_DUENO', "Dueño editado: $primer_nombre $primer_apellido (ID: $id_dueno)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Dueño actualizado exitosamente'
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            return [
                'success' => false,
                'message' => 'Error al actualizar el dueño: ' . $error
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
 * Eliminar un dueño
 */
function eliminarDueno($conn) {
    try {
        $id_dueno = intval($_POST['id_dueno'] ?? 0);
        
        if ($id_dueno <= 0) {
            return [
                'success' => false,
                'message' => 'ID de dueño no válido'
            ];
        }
        
        // Verificar si el dueño tiene mascotas asociadas (si existe la tabla mascota)
        $result = $conn->query("SHOW TABLES LIKE 'mascota'");
        if ($result && $result->num_rows > 0) {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM mascota WHERE id_dueno = ?");
            $stmt->bind_param("i", $id_dueno);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['total'] > 0) {
                $stmt->close();
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar el dueño porque tiene ' . $row['total'] . ' mascota(s) registrada(s)'
                ];
            }
            $stmt->close();
        }
        
        // Obtener información del dueño antes de eliminar
        $stmt = $conn->prepare("SELECT primer_nombre, primer_apellido FROM dueno WHERE id_dueno = ?");
        $stmt->bind_param("i", $id_dueno);
        $stmt->execute();
        $result = $stmt->get_result();
        $dueno = $result->fetch_assoc();
        
        if (!$dueno) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'El dueño no existe'
            ];
        }
        $stmt->close();
        
        // Eliminar el dueño
        $stmt = $conn->prepare("DELETE FROM dueno WHERE id_dueno = ?");
        $stmt->bind_param("i", $id_dueno);
        
        if ($stmt->execute()) {
            try {
                logActivity('ELIMINAR_DUENO', "Dueño eliminado: {$dueno['primer_nombre']} {$dueno['primer_apellido']} (ID: $id_dueno)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Dueño eliminado exitosamente'
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            return [
                'success' => false,
                'message' => 'Error al eliminar el dueño: ' . $error
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