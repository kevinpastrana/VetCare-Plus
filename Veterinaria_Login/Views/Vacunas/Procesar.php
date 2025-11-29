<?php
/**
 * PROCESAR VACUNAS
 * Maneja todas las operaciones CRUD de vacunas
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
            registrarVacuna($conn);
            break;
        
        case 'actualizar':
            actualizarVacuna($conn);
            break;
        
        case 'eliminar':
            eliminarVacuna($conn);
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
 * Registrar nueva vacuna
 */
function registrarVacuna($conn) {
    // Datos de la vacuna
    $nombre_vacuna = trim($_POST['nombre_vacuna'] ?? '');
    $laboratorio = trim($_POST['laboratorio'] ?? '');
    $lote = trim($_POST['lote'] ?? '') ?: null;
    $dosis = trim($_POST['dosis'] ?? '') ?: null;
    $via_administracion = trim($_POST['via_administracion'] ?? '');
    $fecha_aplicacion = trim($_POST['fecha_aplicacion'] ?? '');
    $proxima_aplicacion = trim($_POST['proxima_aplicacion'] ?? '') ?: null;
    $id_mascota = intval($_POST['id_mascota'] ?? 0);
    $id_empleado = intval($_POST['id_empleado'] ?? 0);
    $observaciones = trim($_POST['observaciones'] ?? '') ?: null;
    
    // Validaciones
    if (empty($nombre_vacuna) || empty($laboratorio) || empty($via_administracion) || 
        empty($fecha_aplicacion) || $id_mascota <= 0 || $id_empleado <= 0) {
        throw new Exception('Todos los campos obligatorios deben ser completados');
    }
    
    // Validar vía de administración
    $vias_validas = ['Subcutánea', 'Intramuscular', 'Oral', 'Intranasal'];
    if (!in_array($via_administracion, $vias_validas)) {
        throw new Exception('Vía de administración no válida');
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
    
    // Validar que próxima aplicación sea posterior a fecha de aplicación
    if ($proxima_aplicacion && strtotime($proxima_aplicacion) <= strtotime($fecha_aplicacion)) {
        throw new Exception('La próxima aplicación debe ser posterior a la fecha de aplicación');
    }
    
    // Insertar vacuna
    $stmt = $conn->prepare("INSERT INTO vacuna (
        id_mascota, nombre_vacuna, laboratorio, lote, dosis, 
        via_administracion, fecha_aplicacion, proxima_aplicacion, 
        id_empleado, observaciones
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("isssssssss",
        $id_mascota,
        $nombre_vacuna,
        $laboratorio,
        $lote,
        $dosis,
        $via_administracion,
        $fecha_aplicacion,
        $proxima_aplicacion,
        $id_empleado,
        $observaciones
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Error al registrar la vacuna: ' . $stmt->error);
    }
    
    $id_vacuna = $conn->insert_id;
    
    echo json_encode([
        'success' => true,
        'message' => 'Vacuna registrada exitosamente',
        'id_vacuna' => $id_vacuna
    ]);
}

/**
 * Actualizar vacuna existente
 */
function actualizarVacuna($conn) {
    $id_vacuna = intval($_POST['id_vacuna'] ?? 0);
    $nombre_vacuna = trim($_POST['nombre_vacuna'] ?? '');
    $laboratorio = trim($_POST['laboratorio'] ?? '');
    $lote = trim($_POST['lote'] ?? '') ?: null;
    $dosis = trim($_POST['dosis'] ?? '') ?: null;
    $via_administracion = trim($_POST['via_administracion'] ?? '');
    $fecha_aplicacion = trim($_POST['fecha_aplicacion'] ?? '');
    $proxima_aplicacion = trim($_POST['proxima_aplicacion'] ?? '') ?: null;
    $id_mascota = intval($_POST['id_mascota'] ?? 0);
    $id_empleado = intval($_POST['id_empleado'] ?? 0);
    $observaciones = trim($_POST['observaciones'] ?? '') ?: null;
    
    if ($id_vacuna <= 0 || empty($nombre_vacuna) || empty($laboratorio) || 
        empty($via_administracion) || empty($fecha_aplicacion) || 
        $id_mascota <= 0 || $id_empleado <= 0) {
        throw new Exception('Datos incompletos para actualizar');
    }
    
    // Validar vía de administración
    $vias_validas = ['Subcutánea', 'Intramuscular', 'Oral', 'Intranasal'];
    if (!in_array($via_administracion, $vias_validas)) {
        throw new Exception('Vía de administración no válida');
    }
    
    $stmt = $conn->prepare("UPDATE vacuna SET 
        id_mascota = ?, nombre_vacuna = ?, laboratorio = ?, lote = ?, dosis = ?, 
        via_administracion = ?, fecha_aplicacion = ?, proxima_aplicacion = ?, 
        id_empleado = ?, observaciones = ?
        WHERE id_vacuna = ?");
    
    $stmt->bind_param("isssssssssi",
        $id_mascota,
        $nombre_vacuna,
        $laboratorio,
        $lote,
        $dosis,
        $via_administracion,
        $fecha_aplicacion,
        $proxima_aplicacion,
        $id_empleado,
        $observaciones,
        $id_vacuna
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar la vacuna: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('No se realizaron cambios o la vacuna no existe');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Vacuna actualizada exitosamente'
    ]);
}

/**
 * Eliminar vacuna
 */
function eliminarVacuna($conn) {
    $id_vacuna = intval($_POST['id_vacuna'] ?? 0);
    
    if ($id_vacuna <= 0) {
        throw new Exception('ID de vacuna inválido');
    }
    
    $stmt = $conn->prepare("DELETE FROM vacuna WHERE id_vacuna = ?");
    $stmt->bind_param("i", $id_vacuna);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al eliminar la vacuna: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Vacuna no encontrada');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Vacuna eliminada exitosamente'
    ]);
}

/**
 * Obtener detalle completo de vacuna
 */
function obtenerDetalle($conn) {
    $id_vacuna = intval($_GET['id'] ?? 0);
    
    if ($id_vacuna <= 0) {
        throw new Exception('ID de vacuna inválido');
    }
    
    $query = "SELECT 
        v.*,
        m.nombre AS mascota_nombre,
        m.especie,
        m.raza,
        CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno_nombre,
        CONCAT(e.primer_nombre, ' ', e.primer_apellido) AS veterinario_nombre
    FROM vacuna v
    INNER JOIN mascota m ON v.id_mascota = m.id_mascota
    INNER JOIN dueno d ON m.id_dueno = d.id_dueno
    INNER JOIN empleado e ON v.id_empleado = e.id_empleado
    WHERE v.id_vacuna = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_vacuna);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Vacuna no encontrada');
    }
    
    $vacuna = $result->fetch_assoc();
    
    // Formatear fechas
    $vacuna['fecha_aplicacion'] = date('d/m/Y', strtotime($vacuna['fecha_aplicacion']));
    if ($vacuna['proxima_aplicacion']) {
        $vacuna['proxima_aplicacion'] = date('d/m/Y', strtotime($vacuna['proxima_aplicacion']));
    }
    
    echo json_encode([
        'success' => true,
        'vacuna' => $vacuna
    ]);
}
?>