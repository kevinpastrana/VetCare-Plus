<?php
/**
 * PROCESAR PROCEDIMIENTOS
 * Maneja todas las operaciones CRUD de procedimientos
 */

session_start();
require_once '../../Config/database.php';
require_once '../../Includes/functions.php';
requireLogin();

header('Content-Type: application/json');

$conn = getConnection();

$action = $_REQUEST['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'registrar':
            registrarProcedimiento($conn);
            break;
        
        case 'actualizar':
            actualizarProcedimiento($conn);
            break;
        
        case 'eliminar':
            eliminarProcedimiento($conn);
            break;
        
        case 'detalle':
            obtenerDetalle($conn);
            break;
        
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();

/**
 * Registrar nuevo procedimiento
 */
function registrarProcedimiento($conn) {
    // Datos del procedimiento
    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '') ?: null;
    $patologia = trim($_POST['patologia'] ?? '') ?: null;
    $historial_medico = trim($_POST['historial_medico'] ?? '') ?: null;
    $esterilizacion = intval($_POST['esterilizacion'] ?? 0);
    $diagnostico = trim($_POST['diagnostico'] ?? '') ?: null;
    $antecedentes = trim($_POST['antecedentes'] ?? '') ?: null;
    $fecha_procedimiento = trim($_POST['fecha_procedimiento'] ?? '');
    $hora_inicio = trim($_POST['hora_inicio'] ?? '') ?: null;
    $hora_fin = trim($_POST['hora_fin'] ?? '') ?: null;
    $costo = !empty($_POST['costo']) ? floatval($_POST['costo']) : null;
    $id_mascota = intval($_POST['id_mascota'] ?? 0);
    $id_empleado = intval($_POST['id_empleado'] ?? 0);
    $estado = trim($_POST['estado'] ?? 'Completado');
    
    // Validaciones
    if (empty($nombre) || empty($tipo) || empty($fecha_procedimiento) || $id_mascota <= 0 || $id_empleado <= 0) {
        throw new Exception('Todos los campos obligatorios deben ser completados');
    }
    
    // Validar que la mascota existe y está activa
    $stmt = $conn->prepare("SELECT id_mascota FROM mascota WHERE id_mascota = ? AND estado = 'Activo'");
    $stmt->bind_param("i", $id_mascota);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('La mascota seleccionada no existe o no está activa');
    }
    
    // Validar que el empleado existe y está activo
    $stmt = $conn->prepare("SELECT id_empleado FROM empleado WHERE id_empleado = ? AND estado = 'activo'");
    $stmt->bind_param("i", $id_empleado);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('El empleado seleccionado no existe o no está activo');
    }
    
    // Insertar procedimiento
    $stmt = $conn->prepare("INSERT INTO procedimiento (
        nombre, tipo, descripcion, patologia, historial_medico, 
        esterilizacion, diagnostico, antecedentes, fecha_procedimiento, 
        hora_inicio, hora_fin, costo, id_mascota, id_empleado, estado
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssssssssssdiis",
        $nombre,             // s - string
        $tipo,               // s - string
        $descripcion,        // s - string (puede ser null)
        $patologia,          // s - string (puede ser null)
        $historial_medico,   // s - string (puede ser null)
        $esterilizacion,     // s - string (tinyint se envía como string)
        $diagnostico,        // s - string (puede ser null)
        $antecedentes,       // s - string (puede ser null)
        $fecha_procedimiento,// s - string
        $hora_inicio,        // s - string (puede ser null)
        $hora_fin,           // s - string (puede ser null)
        $costo,              // d - double (puede ser null)
        $id_mascota,         // i - integer
        $id_empleado,        // i - integer
        $estado              // s - string
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Error al registrar el procedimiento: ' . $stmt->error);
    }
    
    $id_procedimiento = $conn->insert_id;
    
    echo json_encode([
        'success' => true,
        'message' => 'Procedimiento registrado exitosamente',
        'id_procedimiento' => $id_procedimiento
    ]);
}

/**
 * Actualizar procedimiento existente
 */
function actualizarProcedimiento($conn) {
    $id_procedimiento = intval($_POST['id_procedimiento'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '') ?: null;
    $patologia = trim($_POST['patologia'] ?? '') ?: null;
    $historial_medico = trim($_POST['historial_medico'] ?? '') ?: null;
    $esterilizacion = intval($_POST['esterilizacion'] ?? 0);
    $diagnostico = trim($_POST['diagnostico'] ?? '') ?: null;
    $antecedentes = trim($_POST['antecedentes'] ?? '') ?: null;
    $fecha_procedimiento = trim($_POST['fecha_procedimiento'] ?? '');
    $hora_inicio = trim($_POST['hora_inicio'] ?? '') ?: null;
    $hora_fin = trim($_POST['hora_fin'] ?? '') ?: null;
    $costo = !empty($_POST['costo']) ? floatval($_POST['costo']) : null;
    $id_mascota = intval($_POST['id_mascota'] ?? 0);
    $id_empleado = intval($_POST['id_empleado'] ?? 0);
    $estado = trim($_POST['estado'] ?? 'Completado');
    
    if ($id_procedimiento <= 0 || empty($nombre) || empty($tipo) || empty($fecha_procedimiento) || $id_mascota <= 0 || $id_empleado <= 0) {
        throw new Exception('Datos incompletos para actualizar');
    }
    
    $stmt = $conn->prepare("UPDATE procedimiento SET 
        nombre = ?, tipo = ?, descripcion = ?, patologia = ?, historial_medico = ?, 
        esterilizacion = ?, diagnostico = ?, antecedentes = ?, fecha_procedimiento = ?, 
        hora_inicio = ?, hora_fin = ?, costo = ?, id_mascota = ?, id_empleado = ?, estado = ?
        WHERE id_procedimiento = ?");
    
    $stmt->bind_param("sssssssssssdiisi",
        $nombre,
        $tipo,
        $descripcion,
        $patologia,
        $historial_medico,
        $esterilizacion,
        $diagnostico,
        $antecedentes,
        $fecha_procedimiento,
        $hora_inicio,
        $hora_fin,
        $costo,
        $id_mascota,
        $id_empleado,
        $estado,
        $id_procedimiento
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar el procedimiento: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('No se realizaron cambios o el procedimiento no existe');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Procedimiento actualizado exitosamente'
    ]);
}

/**
 * Eliminar procedimiento
 */
function eliminarProcedimiento($conn) {
    $id_procedimiento = intval($_POST['id_procedimiento'] ?? 0);
    
    if ($id_procedimiento <= 0) {
        throw new Exception('ID de procedimiento inválido');
    }
    
    $stmt = $conn->prepare("DELETE FROM procedimiento WHERE id_procedimiento = ?");
    $stmt->bind_param("i", $id_procedimiento);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al eliminar el procedimiento: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Procedimiento no encontrado');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Procedimiento eliminado exitosamente'
    ]);
}

/**
 * Obtener detalle completo de procedimiento
 */
function obtenerDetalle($conn) {
    $id_procedimiento = intval($_GET['id'] ?? 0);
    
    if ($id_procedimiento <= 0) {
        throw new Exception('ID de procedimiento inválido');
    }
    
    $query = "SELECT 
        p.*,
        m.nombre AS mascota_nombre,
        m.especie,
        m.raza,
        CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno_nombre,
        CONCAT(e.primer_nombre, ' ', e.primer_apellido) AS veterinario_nombre
    FROM procedimiento p
    INNER JOIN mascota m ON p.id_mascota = m.id_mascota
    INNER JOIN dueno d ON m.id_dueno = d.id_dueno
    INNER JOIN empleado e ON p.id_empleado = e.id_empleado
    WHERE p.id_procedimiento = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_procedimiento);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Procedimiento no encontrado');
    }
    
    $procedimiento = $result->fetch_assoc();
    
    // Formatear fecha
    $procedimiento['fecha_procedimiento'] = date('d/m/Y', strtotime($procedimiento['fecha_procedimiento']));
    
    echo json_encode([
        'success' => true,
        'procedimiento' => $procedimiento
    ]);
}
?>