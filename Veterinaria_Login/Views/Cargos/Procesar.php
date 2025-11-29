<?php
/**
 * PROCESAR CARGOS
 * Maneja todas las operaciones CRUD de cargos
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
            registrarCargo($conn);
            break;
        
        case 'actualizar':
            actualizarCargo($conn);
            break;
        
        case 'eliminar':
            eliminarCargo($conn);
            break;
        
        case 'cambiar_estado':
            cambiarEstado($conn);
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
 * Registrar nuevo cargo
 */
function registrarCargo($conn) {
    // Datos del cargo
    $nombre_cargo = trim($_POST['nombre_cargo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '') ?: null;
    $estado = trim($_POST['estado'] ?? 'Activo');
    
    // Validaciones
    if (empty($nombre_cargo)) {
        throw new Exception('El nombre del cargo es obligatorio');
    }
    
    // Validar longitud del nombre
    if (strlen($nombre_cargo) > 50) {
        throw new Exception('El nombre del cargo no puede exceder 50 caracteres');
    }
    
    // Validar longitud de la descripción
    if ($descripcion && strlen($descripcion) > 200) {
        throw new Exception('La descripción no puede exceder 200 caracteres');
    }
    
    // Validar estado
    if (!in_array($estado, ['Activo', 'Inactivo'])) {
        throw new Exception('Estado no válido');
    }
    
    // Verificar que el cargo no exista ya
    $stmt = $conn->prepare("SELECT id_cargo FROM cargo WHERE nombre_cargo = ?");
    $stmt->bind_param("s", $nombre_cargo);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Ya existe un cargo con ese nombre');
    }
    
    // Insertar cargo
    $stmt = $conn->prepare("INSERT INTO cargo (nombre_cargo, descripcion, estado) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre_cargo, $descripcion, $estado);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al registrar el cargo: ' . $stmt->error);
    }
    
    $id_cargo = $conn->insert_id;
    
    echo json_encode([
        'success' => true,
        'message' => 'Cargo registrado exitosamente',
        'id_cargo' => $id_cargo
    ]);
}

/**
 * Actualizar cargo existente
 */
function actualizarCargo($conn) {
    $id_cargo = intval($_POST['id_cargo'] ?? 0);
    $nombre_cargo = trim($_POST['nombre_cargo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '') ?: null;
    $estado = trim($_POST['estado'] ?? 'Activo');
    
    if ($id_cargo <= 0 || empty($nombre_cargo)) {
        throw new Exception('Datos incompletos para actualizar');
    }
    
    // Validar longitud del nombre
    if (strlen($nombre_cargo) > 50) {
        throw new Exception('El nombre del cargo no puede exceder 50 caracteres');
    }
    
    // Validar longitud de la descripción
    if ($descripcion && strlen($descripcion) > 200) {
        throw new Exception('La descripción no puede exceder 200 caracteres');
    }
    
    // Validar estado
    if (!in_array($estado, ['Activo', 'Inactivo'])) {
        throw new Exception('Estado no válido');
    }
    
    // Verificar que el cargo no exista ya (excepto el actual)
    $stmt = $conn->prepare("SELECT id_cargo FROM cargo WHERE nombre_cargo = ? AND id_cargo != ?");
    $stmt->bind_param("si", $nombre_cargo, $id_cargo);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Ya existe otro cargo con ese nombre');
    }
    
    // Actualizar cargo
    $stmt = $conn->prepare("UPDATE cargo SET nombre_cargo = ?, descripcion = ?, estado = ? WHERE id_cargo = ?");
    $stmt->bind_param("sssi", $nombre_cargo, $descripcion, $estado, $id_cargo);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar el cargo: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('No se realizaron cambios o el cargo no existe');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cargo actualizado exitosamente'
    ]);
}

/**
 * Eliminar cargo
 */
function eliminarCargo($conn) {
    $id_cargo = intval($_POST['id_cargo'] ?? 0);
    
    if ($id_cargo <= 0) {
        throw new Exception('ID de cargo inválido');
    }
    
    // Verificar que el cargo no esté siendo usado por empleados
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM empleado WHERE id_cargo = ?");
    $stmt->bind_param("i", $id_cargo);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['total'] > 0) {
        throw new Exception('No se puede eliminar el cargo porque está asignado a ' . $result['total'] . ' empleado(s)');
    }
    
    // Eliminar cargo
    $stmt = $conn->prepare("DELETE FROM cargo WHERE id_cargo = ?");
    $stmt->bind_param("i", $id_cargo);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al eliminar el cargo: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Cargo no encontrado');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cargo eliminado exitosamente'
    ]);
}

/**
 * Cambiar estado del cargo (Activo/Inactivo)
 */
function cambiarEstado($conn) {
    $id_cargo = intval($_POST['id_cargo'] ?? 0);
    $nuevo_estado = trim($_POST['estado'] ?? '');
    
    if ($id_cargo <= 0 || empty($nuevo_estado)) {
        throw new Exception('Datos incompletos');
    }
    
    // Validar estado
    if (!in_array($nuevo_estado, ['Activo', 'Inactivo'])) {
        throw new Exception('Estado no válido');
    }
    
    // Actualizar estado
    $stmt = $conn->prepare("UPDATE cargo SET estado = ? WHERE id_cargo = ?");
    $stmt->bind_param("si", $nuevo_estado, $id_cargo);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al cambiar el estado: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Cargo no encontrado o el estado ya es el mismo');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Estado actualizado exitosamente'
    ]);
}

/**
 * Obtener detalle completo del cargo
 */
function obtenerDetalle($conn) {
    $id_cargo = intval($_GET['id'] ?? 0);
    
    if ($id_cargo <= 0) {
        throw new Exception('ID de cargo inválido');
    }
    
    // Obtener información del cargo y número de empleados
    $query = "SELECT 
        c.*,
        COUNT(e.id_empleado) as total_empleados
    FROM cargo c
    LEFT JOIN empleado e ON c.id_cargo = e.id_cargo AND e.estado = 'activo'
    WHERE c.id_cargo = ?
    GROUP BY c.id_cargo";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_cargo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Cargo no encontrado');
    }
    
    $cargo = $result->fetch_assoc();
    
    // Obtener lista de empleados con este cargo
    $stmt = $conn->prepare("SELECT 
        CONCAT(primer_nombre, ' ', primer_apellido) as nombre_completo,
        correo_electronico,
        fecha_contratacion,
        estado
    FROM empleado 
    WHERE id_cargo = ?
    ORDER BY primer_nombre");
    
    $stmt->bind_param("i", $id_cargo);
    $stmt->execute();
    $empleados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $cargo['empleados'] = $empleados;
    
    echo json_encode([
        'success' => true,
        'cargo' => $cargo
    ]);
}
?>