<?php
/**
 * PROCESAR.PHP - Procesar acciones de consultas
 * Maneja registro, edición, eliminación y detalles de consultas
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

// Obtener la acción a realizar
$action = $_REQUEST['action'] ?? '';

try {
    $conn = getConnection();
    
    switch ($action) {
        case 'registrar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit();
            }
            $result = registrarConsulta($conn);
            echo json_encode($result);
            break;
            
        case 'editar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit();
            }
            $result = editarConsulta($conn);
            echo json_encode($result);
            break;
            
        case 'eliminar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit();
            }
            $result = eliminarConsulta($conn);
            echo json_encode($result);
            break;
            
        case 'detalle':
            $result = obtenerDetalle($conn);
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
 * Registrar una nueva consulta
 */
function registrarConsulta($conn) {
    try {
        // Validar y sanitizar datos
        $fecha_consulta = sanitize_input($_POST['fecha_consulta'] ?? '');
        $hora_consulta = sanitize_input($_POST['hora_consulta'] ?? '');
        $id_mascota = intval($_POST['id_mascota'] ?? 0);
        $id_empleado = intval($_POST['id_empleado'] ?? 0);
        $motivo = sanitize_input($_POST['motivo'] ?? '');
        $sintomas = sanitize_input($_POST['sintomas'] ?? '');
        $diagnostico = sanitize_input($_POST['diagnostico'] ?? '');
        $tratamiento = sanitize_input($_POST['tratamiento'] ?? '');
        $observaciones = sanitize_input($_POST['observaciones'] ?? '');
        $proxima_cita = sanitize_input($_POST['proxima_cita'] ?? '');
        $estado = sanitize_input($_POST['estado'] ?? 'Completada');
        
        // Validaciones
        if (empty($fecha_consulta) || empty($hora_consulta)) {
            return [
                'success' => false,
                'message' => 'La fecha y hora de consulta son obligatorias'
            ];
        }
        
        if ($id_mascota <= 0) {
            return [
                'success' => false,
                'message' => 'Debe seleccionar una mascota válida'
            ];
        }
        
        if ($id_empleado <= 0) {
            return [
                'success' => false,
                'message' => 'Debe seleccionar un veterinario válido'
            ];
        }
        
        if (empty($motivo) || empty($sintomas)) {
            return [
                'success' => false,
                'message' => 'El motivo y los síntomas son obligatorios'
            ];
        }
        
        // Validar que la fecha no sea futura
        if ($fecha_consulta > date('Y-m-d')) {
            return [
                'success' => false,
                'message' => 'La fecha de consulta no puede ser futura'
            ];
        }
        
        // Verificar que la mascota existe
        $stmt = $conn->prepare("SELECT id_mascota FROM mascota WHERE id_mascota = ?");
        $stmt->bind_param("i", $id_mascota);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'La mascota seleccionada no existe'
            ];
        }
        $stmt->close();
        
        // Verificar que el empleado existe
        $stmt = $conn->prepare("SELECT id_empleado FROM empleado WHERE id_empleado = ?");
        $stmt->bind_param("i", $id_empleado);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'El veterinario seleccionado no existe'
            ];
        }
        $stmt->close();
        
        // Preparar próxima cita (null si está vacío)
        $proxima_cita = empty($proxima_cita) ? null : $proxima_cita;
        
        // Insertar la nueva consulta
        $sql = "INSERT INTO consulta (
            fecha_consulta,
            hora_consulta,
            motivo,
            sintomas,
            diagnostico,
            tratamiento,
            observaciones,
            proxima_cita,
            id_mascota,
            id_empleado,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Error al preparar la consulta: ' . $conn->error
            ];
        }
        
        $stmt->bind_param(
            "ssssssssiis",
            $fecha_consulta,
            $hora_consulta,
            $motivo,
            $sintomas,
            $diagnostico,
            $tratamiento,
            $observaciones,
            $proxima_cita,
            $id_mascota,
            $id_empleado,
            $estado
        );
        
        if ($stmt->execute()) {
            $id_consulta = $conn->insert_id;
            
            // Log de actividad
            try {
                logActivity('REGISTRAR_CONSULTA', "Consulta registrada (ID: $id_consulta)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Consulta registrada exitosamente',
                'id_consulta' => $id_consulta
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            return [
                'success' => false,
                'message' => 'Error al registrar la consulta: ' . $error
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
 * Editar una consulta existente
 */
function editarConsulta($conn) {
    try {
        $id_consulta = intval($_POST['id_consulta'] ?? 0);
        
        if ($id_consulta <= 0) {
            return [
                'success' => false,
                'message' => 'ID de consulta no válido'
            ];
        }
        
        // Validar y sanitizar datos
        $fecha_consulta = sanitize_input($_POST['fecha_consulta'] ?? '');
        $hora_consulta = sanitize_input($_POST['hora_consulta'] ?? '');
        $id_mascota = intval($_POST['id_mascota'] ?? 0);
        $id_empleado = intval($_POST['id_empleado'] ?? 0);
        $motivo = sanitize_input($_POST['motivo'] ?? '');
        $sintomas = sanitize_input($_POST['sintomas'] ?? '');
        $diagnostico = sanitize_input($_POST['diagnostico'] ?? '');
        $tratamiento = sanitize_input($_POST['tratamiento'] ?? '');
        $observaciones = sanitize_input($_POST['observaciones'] ?? '');
        $proxima_cita = sanitize_input($_POST['proxima_cita'] ?? '');
        $estado = sanitize_input($_POST['estado'] ?? 'Completada');
        
        // Validaciones (mismas que en registrar)
        if (empty($fecha_consulta) || empty($hora_consulta) || empty($motivo) || empty($sintomas)) {
            return [
                'success' => false,
                'message' => 'Los campos obligatorios no pueden estar vacíos'
            ];
        }
        
        if ($id_mascota <= 0 || $id_empleado <= 0) {
            return [
                'success' => false,
                'message' => 'Debe seleccionar una mascota y veterinario válidos'
            ];
        }
        
        // Preparar próxima cita
        $proxima_cita = empty($proxima_cita) ? null : $proxima_cita;
        
        // Actualizar la consulta
        $sql = "UPDATE consulta SET 
            fecha_consulta = ?,
            hora_consulta = ?,
            motivo = ?,
            sintomas = ?,
            diagnostico = ?,
            tratamiento = ?,
            observaciones = ?,
            proxima_cita = ?,
            id_mascota = ?,
            id_empleado = ?,
            estado = ?
            WHERE id_consulta = ?";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Error al preparar la consulta: ' . $conn->error
            ];
        }
        
        $stmt->bind_param(
            "ssssssssiiis",
            $fecha_consulta,
            $hora_consulta,
            $motivo,
            $sintomas,
            $diagnostico,
            $tratamiento,
            $observaciones,
            $proxima_cita,
            $id_mascota,
            $id_empleado,
            $estado,
            $id_consulta
        );
        
        if ($stmt->execute()) {
            try {
                logActivity('EDITAR_CONSULTA', "Consulta editada (ID: $id_consulta)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Consulta actualizada exitosamente'
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            return [
                'success' => false,
                'message' => 'Error al actualizar la consulta: ' . $error
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
 * Eliminar una consulta
 */
function eliminarConsulta($conn) {
    try {
        $id_consulta = intval($_POST['id_consulta'] ?? 0);
        
        if ($id_consulta <= 0) {
            return [
                'success' => false,
                'message' => 'ID de consulta no válido'
            ];
        }
        
        // Verificar que existe
        $stmt = $conn->prepare("SELECT id_consulta FROM consulta WHERE id_consulta = ?");
        $stmt->bind_param("i", $id_consulta);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows == 0) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'La consulta no existe'
            ];
        }
        $stmt->close();
        
        // Eliminar la consulta
        $stmt = $conn->prepare("DELETE FROM consulta WHERE id_consulta = ?");
        $stmt->bind_param("i", $id_consulta);
        
        if ($stmt->execute()) {
            try {
                logActivity('ELIMINAR_CONSULTA', "Consulta eliminada (ID: $id_consulta)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Consulta eliminada exitosamente'
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            return [
                'success' => false,
                'message' => 'Error al eliminar la consulta: ' . $error
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
 * Obtener detalle de una consulta
 */
function obtenerDetalle($conn) {
    try {
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'ID no válido'
            ];
        }
        
        $query = "SELECT 
            c.*,
            m.nombre AS nombre_mascota,
            m.especie,
            CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno_nombre,
            CONCAT(e.primer_nombre, ' ', e.primer_apellido) AS veterinario_nombre
        FROM consulta c
        INNER JOIN mascota m ON c.id_mascota = m.id_mascota
        INNER JOIN dueno d ON m.id_dueno = d.id_dueno
        INNER JOIN empleado e ON c.id_empleado = e.id_empleado
        WHERE c.id_consulta = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $consulta = $result->fetch_assoc();
            $stmt->close();
            
            // Formatear fechas
            $consulta['fecha_consulta'] = date('d/m/Y', strtotime($consulta['fecha_consulta']));
            $consulta['hora_consulta'] = date('h:i A', strtotime($consulta['hora_consulta']));
            if ($consulta['proxima_cita']) {
                $consulta['proxima_cita'] = date('d/m/Y', strtotime($consulta['proxima_cita']));
            }
            
            return [
                'success' => true,
                'consulta' => $consulta
            ];
        } else {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'Consulta no encontrada'
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}
?>