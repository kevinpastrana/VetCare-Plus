<?php
/**
 * PROCESAR FACTURAS
 * Maneja todas las operaciones CRUD de facturas
 */

session_start();
require_once '../../Config/database.php';
require_once '../../Includes/functions.php';
requireLogin();

header('Content-Type: application/json');

$conn = getConnection();

// Detectar si viene JSON o POST normal
$input = file_get_contents('php://input');
$jsonData = json_decode($input, true);

if ($jsonData) {
    $_POST = array_merge($_POST, $jsonData);
}

$action = $_REQUEST['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'registrar':
            registrarFactura($conn);
            break;
        
        case 'actualizar':
            actualizarFactura($conn);
            break;
        
        case 'eliminar':
            eliminarFactura($conn);
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
 * Registrar nueva factura
 */
function registrarFactura($conn) {
    // Datos de factura principal
    $numero_factura = trim($_POST['numero_factura'] ?? '');
    $fecha_factura = trim($_POST['fecha_factura'] ?? '');
    $fecha_vencimiento = trim($_POST['fecha_vencimiento'] ?? '') ?: null;
    $metodo_pago = trim($_POST['metodo_pago'] ?? '');
    $subtotal = floatval($_POST['subtotal'] ?? 0);
    $descuento = floatval($_POST['descuento'] ?? 0);
    $impuesto = floatval($_POST['impuesto'] ?? 0);
    $total = floatval($_POST['total'] ?? 0);
    $pagado = floatval($_POST['pagado'] ?? 0);
    $saldo = floatval($_POST['saldo'] ?? 0);
    $estado = trim($_POST['estado'] ?? 'Pendiente');
    $id_dueno = intval($_POST['id_dueno'] ?? 0);
    $id_consulta = !empty($_POST['id_consulta']) ? intval($_POST['id_consulta']) : null;
    
    // Validaciones
    if (empty($numero_factura) || empty($fecha_factura) || empty($metodo_pago) || $id_dueno <= 0) {
        throw new Exception('Todos los campos obligatorios deben ser completados');
    }
    
    // Verificar si el número de factura ya existe
    $stmt = $conn->prepare("SELECT id_factura FROM factura WHERE numero_factura = ?");
    $stmt->bind_param("s", $numero_factura);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('El número de factura ya existe');
    }
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Insertar factura principal
        $stmt = $conn->prepare("INSERT INTO factura (
            numero_factura, fecha_factura, fecha_vencimiento, metodo_pago, 
            subtotal, descuento, impuesto, total, pagado, saldo, estado, 
            id_dueno, id_consulta
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // CORREGIDO: 13 valores = 13 tipos (s s s s d d d d d d s i i)
        $stmt->bind_param("ssssddddddsii",
            $numero_factura,     // s - string
            $fecha_factura,      // s - string
            $fecha_vencimiento,  // s - string (puede ser null)
            $metodo_pago,        // s - string
            $subtotal,           // d - double
            $descuento,          // d - double
            $impuesto,           // d - double
            $total,              // d - double
            $pagado,             // d - double
            $saldo,              // d - double
            $estado,             // s - string
            $id_dueno,           // i - integer
            $id_consulta         // i - integer (puede ser null)
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Error al registrar la factura: ' . $stmt->error);
        }
        
        $id_factura = $conn->insert_id;
        
        // Insertar detalles de factura si existen
        if (!empty($_POST['detalles']) && is_array($_POST['detalles'])) {
            $stmt_detalle = $conn->prepare("INSERT INTO detalle_factura (
                id_factura, concepto, descripcion, cantidad, precio_unitario, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($_POST['detalles'] as $detalle) {
                $concepto = trim($detalle['concepto'] ?? '');
                $descripcion = trim($detalle['descripcion'] ?? '');
                $cantidad = intval($detalle['cantidad'] ?? 1);
                $precio_unitario = floatval($detalle['precio_unitario'] ?? 0);
                $subtotal_detalle = floatval($detalle['subtotal'] ?? 0);
                
                if (!empty($concepto) && $cantidad > 0 && $precio_unitario > 0) {
                    $stmt_detalle->bind_param("issidi",
                        $id_factura,         // i - integer
                        $concepto,           // s - string
                        $descripcion,        // s - string
                        $cantidad,           // i - integer
                        $precio_unitario,    // d - double
                        $subtotal_detalle    // i - double
                    );
                    
                    if (!$stmt_detalle->execute()) {
                        throw new Exception('Error al insertar detalle: ' . $stmt_detalle->error);
                    }
                }
            }
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Factura registrada exitosamente',
            'id_factura' => $id_factura
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Actualizar factura existente
 */
function actualizarFactura($conn) {
    $id_factura = intval($_POST['id_factura'] ?? 0);
    $numero_factura = trim($_POST['numero_factura'] ?? '');
    $fecha_factura = trim($_POST['fecha_factura'] ?? '');
    $fecha_vencimiento = trim($_POST['fecha_vencimiento'] ?? '') ?: null;
    $metodo_pago = trim($_POST['metodo_pago'] ?? '');
    $subtotal = floatval($_POST['subtotal'] ?? 0);
    $descuento = floatval($_POST['descuento'] ?? 0);
    $impuesto = floatval($_POST['impuesto'] ?? 0);
    $total = floatval($_POST['total'] ?? 0);
    $pagado = floatval($_POST['pagado'] ?? 0);
    $saldo = floatval($_POST['saldo'] ?? 0);
    $estado = trim($_POST['estado'] ?? 'Pendiente');
    $id_dueno = intval($_POST['id_dueno'] ?? 0);
    $id_consulta = !empty($_POST['id_consulta']) ? intval($_POST['id_consulta']) : null;
    
    if ($id_factura <= 0 || empty($numero_factura) || empty($fecha_factura) || $id_dueno <= 0) {
        throw new Exception('Datos incompletos para actualizar');
    }
    
    // Verificar si el número de factura ya existe en otra factura
    $stmt = $conn->prepare("SELECT id_factura FROM factura WHERE numero_factura = ? AND id_factura != ?");
    $stmt->bind_param("si", $numero_factura, $id_factura);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('El número de factura ya existe en otra factura');
    }
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("UPDATE factura SET 
            numero_factura = ?, fecha_factura = ?, fecha_vencimiento = ?, 
            metodo_pago = ?, subtotal = ?, descuento = ?, impuesto = ?, 
            total = ?, pagado = ?, saldo = ?, estado = ?, 
            id_dueno = ?, id_consulta = ?
            WHERE id_factura = ?");
        
        // CORREGIDO: 14 valores = 14 tipos (s s s s d d d d d d s i i i)
        $stmt->bind_param("ssssddddddsiii",
            $numero_factura,     // s - string
            $fecha_factura,      // s - string
            $fecha_vencimiento,  // s - string (puede ser null)
            $metodo_pago,        // s - string
            $subtotal,           // d - double
            $descuento,          // d - double
            $impuesto,           // d - double
            $total,              // d - double
            $pagado,             // d - double
            $saldo,              // d - double
            $estado,             // s - string
            $id_dueno,           // i - integer
            $id_consulta,        // i - integer (puede ser null)
            $id_factura          // i - integer
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Error al actualizar la factura: ' . $stmt->error);
        }
        
        // Eliminar detalles anteriores
        $conn->query("DELETE FROM detalle_factura WHERE id_factura = $id_factura");
        
        // Insertar nuevos detalles si existen
        if (!empty($_POST['detalles']) && is_array($_POST['detalles'])) {
            $stmt_detalle = $conn->prepare("INSERT INTO detalle_factura (
                id_factura, concepto, descripcion, cantidad, precio_unitario, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($_POST['detalles'] as $detalle) {
                $concepto = trim($detalle['concepto'] ?? '');
                $descripcion = trim($detalle['descripcion'] ?? '');
                $cantidad = intval($detalle['cantidad'] ?? 1);
                $precio_unitario = floatval($detalle['precio_unitario'] ?? 0);
                $subtotal_detalle = floatval($detalle['subtotal'] ?? 0);
                
                if (!empty($concepto) && $cantidad > 0 && $precio_unitario > 0) {
                    $stmt_detalle->bind_param("issidi",
                        $id_factura,         // i - integer
                        $concepto,           // s - string
                        $descripcion,        // s - string
                        $cantidad,           // i - integer
                        $precio_unitario,    // d - double
                        $subtotal_detalle    // d - double
                    );
                    
                    if (!$stmt_detalle->execute()) {
                        throw new Exception('Error al actualizar detalle: ' . $stmt_detalle->error);
                    }
                }
            }
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Factura actualizada exitosamente'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Eliminar factura
 */
function eliminarFactura($conn) {
    $id_factura = intval($_POST['id_factura'] ?? 0);
    
    if ($id_factura <= 0) {
        throw new Exception('ID de factura inválido');
    }
    
    $conn->begin_transaction();
    
    try {
        // Eliminar detalles primero
        $stmt = $conn->prepare("DELETE FROM detalle_factura WHERE id_factura = ?");
        $stmt->bind_param("i", $id_factura);
        $stmt->execute();
        
        // Eliminar factura
        $stmt = $conn->prepare("DELETE FROM factura WHERE id_factura = ?");
        $stmt->bind_param("i", $id_factura);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al eliminar la factura: ' . $stmt->error);
        }
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('Factura no encontrada');
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Factura eliminada exitosamente'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Obtener detalle completo de factura
 */
function obtenerDetalle($conn) {
    $id_factura = intval($_GET['id'] ?? 0);
    
    if ($id_factura <= 0) {
        throw new Exception('ID de factura inválido');
    }
    
    // Obtener factura principal
    $query = "SELECT 
        f.*,
        CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno_nombre,
        d.cedula AS dueno_cedula
    FROM factura f
    INNER JOIN dueno d ON f.id_dueno = d.id_dueno
    WHERE f.id_factura = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_factura);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Factura no encontrada');
    }
    
    $factura = $result->fetch_assoc();
    
    // Formatear fechas
    $factura['fecha_factura'] = date('d/m/Y', strtotime($factura['fecha_factura']));
    if ($factura['fecha_vencimiento']) {
        $factura['fecha_vencimiento'] = date('d/m/Y', strtotime($factura['fecha_vencimiento']));
    }
    
    // Obtener detalles
    $query_detalles = "SELECT * FROM detalle_factura WHERE id_factura = ?";
    $stmt_detalles = $conn->prepare($query_detalles);
    $stmt_detalles->bind_param("i", $id_factura);
    $stmt_detalles->execute();
    $result_detalles = $stmt_detalles->get_result();
    
    $detalles = [];
    while ($detalle = $result_detalles->fetch_assoc()) {
        $detalles[] = $detalle;
    }
    
    $factura['detalles'] = $detalles;
    
    echo json_encode([
        'success' => true,
        'factura' => $factura
    ]);
}
?>