<?php
/**
 * PROCESAR.PHP - Procesar acciones de empleados
 * Maneja registro, edición y eliminación de empleados
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
            $result = registrarEmpleado($conn);
            echo json_encode($result);
            break;
            
        case 'editar':
            $result = editarEmpleado($conn);
            echo json_encode($result);
            break;
            
        case 'eliminar':
            $result = eliminarEmpleado($conn);
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
 * Registrar un nuevo empleado
 */
function registrarEmpleado($conn) {
    try {
        // Validar y sanitizar datos
        $primer_nombre = sanitize_input($_POST['primer_nombre'] ?? '');
        $segundo_nombre = sanitize_input($_POST['segundo_nombre'] ?? '');
        $primer_apellido = sanitize_input($_POST['primer_apellido'] ?? '');
        $segundo_apellido = sanitize_input($_POST['segundo_apellido'] ?? '');
        $cedula = sanitize_input($_POST['cedula'] ?? '');
        $telefono = sanitize_input($_POST['telefono'] ?? '');
        $correo_electronico = sanitize_input($_POST['correo_electronico'] ?? '');
        $direccion = sanitize_input($_POST['direccion'] ?? '');
        $fecha_nacimiento = sanitize_input($_POST['fecha_nacimiento'] ?? '');
        $fecha_contratacion = sanitize_input($_POST['fecha_contratacion'] ?? '');
        $id_cargo = intval($_POST['id_cargo'] ?? 0);
        $password = $_POST['password'] ?? '';
        $estado = sanitize_input($_POST['estado'] ?? 'activo');
        
        // Validaciones
        if (empty($primer_nombre) || empty($primer_apellido) || empty($cedula)) {
            return [
                'success' => false,
                'message' => 'Los campos obligatorios no pueden estar vacíos'
            ];
        }
        
        if ($id_cargo <= 0) {
            return [
                'success' => false,
                'message' => 'Debe seleccionar un cargo válido'
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
        
        if (strlen($password) < 8) {
            return [
                'success' => false,
                'message' => 'La contraseña debe tener al menos 8 caracteres'
            ];
        }
        
        // Validar edad (mayor de 18 años)
        if ($fecha_nacimiento) {
            $nacimiento = new DateTime($fecha_nacimiento);
            $hoy = new DateTime();
            $edad = $hoy->diff($nacimiento)->y;
            
            if ($edad < 18) {
                return [
                    'success' => false,
                    'message' => 'El empleado debe ser mayor de 18 años'
                ];
            }
        }
        
        // Verificar si la cédula ya existe
        $stmt = $conn->prepare("SELECT id_empleado FROM empleado WHERE cedula = ?");
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
        
        // Verificar si el correo ya existe
        $stmt = $conn->prepare("SELECT id_empleado FROM empleado WHERE correo_electronico = ?");
        $stmt->bind_param("s", $correo_electronico);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'El correo electrónico ya está registrado en el sistema'
            ];
        }
        $stmt->close();
        
        // Encriptar contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar el nuevo empleado
        $sql = "INSERT INTO empleado (
            primer_nombre, 
            segundo_nombre, 
            primer_apellido, 
            segundo_apellido, 
            cedula, 
            telefono, 
            correo_electronico, 
            direccion, 
            fecha_nacimiento, 
            fecha_contratacion,
            id_cargo,
            password,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Error al preparar la consulta: ' . $conn->error
            ];
        }
        
        $stmt->bind_param(
            "sssssssssssss",
            $primer_nombre,
            $segundo_nombre,
            $primer_apellido,
            $segundo_apellido,
            $cedula,
            $telefono,
            $correo_electronico,
            $direccion,
            $fecha_nacimiento,
            $fecha_contratacion,
            $id_cargo,
            $password_hash,
            $estado
        );
        
        if ($stmt->execute()) {
            $id_empleado = $conn->insert_id;
            
            // Log de actividad
            try {
                logActivity('REGISTRAR_EMPLEADO', "Empleado registrado: $primer_nombre $primer_apellido (ID: $id_empleado)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Empleado registrado exitosamente',
                'id_empleado' => $id_empleado
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            return [
                'success' => false,
                'message' => 'Error al registrar el empleado: ' . $error
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
 * Editar un empleado existente
 */
function editarEmpleado($conn) {
    try {
        $id_empleado = intval($_POST['id_empleado'] ?? 0);
        
        if ($id_empleado <= 0) {
            return [
                'success' => false,
                'message' => 'ID de empleado no válido'
            ];
        }
        
        // Validar y sanitizar datos
        $primer_nombre = sanitize_input($_POST['primer_nombre'] ?? '');
        $segundo_nombre = sanitize_input($_POST['segundo_nombre'] ?? '');
        $primer_apellido = sanitize_input($_POST['primer_apellido'] ?? '');
        $segundo_apellido = sanitize_input($_POST['segundo_apellido'] ?? '');
        $cedula = sanitize_input($_POST['cedula'] ?? '');
        $telefono = sanitize_input($_POST['telefono'] ?? '');
        $correo_electronico = sanitize_input($_POST['correo_electronico'] ?? '');
        $direccion = sanitize_input($_POST['direccion'] ?? '');
        $fecha_nacimiento = sanitize_input($_POST['fecha_nacimiento'] ?? '');
        $fecha_contratacion = sanitize_input($_POST['fecha_contratacion'] ?? '');
        $id_cargo = intval($_POST['id_cargo'] ?? 0);
        $password = $_POST['password'] ?? '';
        $estado = sanitize_input($_POST['estado'] ?? 'activo');
        
        // Validaciones
        if (empty($primer_nombre) || empty($primer_apellido) || empty($cedula)) {
            return [
                'success' => false,
                'message' => 'Los campos obligatorios no pueden estar vacíos'
            ];
        }
        
        if ($id_cargo <= 0) {
            return [
                'success' => false,
                'message' => 'Debe seleccionar un cargo válido'
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
        
        // Validar contraseña solo si se proporciona
        if (!empty($password) && strlen($password) < 8) {
            return [
                'success' => false,
                'message' => 'La contraseña debe tener al menos 8 caracteres'
            ];
        }
        
        // Verificar si la cédula ya existe en otro registro
        $stmt = $conn->prepare("SELECT id_empleado FROM empleado WHERE cedula = ? AND id_empleado != ?");
        $stmt->bind_param("si", $cedula, $id_empleado);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'La cédula ya está registrada en otro empleado'
            ];
        }
        $stmt->close();
        
        // Verificar si el correo ya existe en otro registro
        $stmt = $conn->prepare("SELECT id_empleado FROM empleado WHERE correo_electronico = ? AND id_empleado != ?");
        $stmt->bind_param("si", $correo_electronico, $id_empleado);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'El correo electrónico ya está registrado en otro empleado'
            ];
        }
        $stmt->close();
        
        // Actualizar el empleado
        if (!empty($password)) {
            // Si se proporciona nueva contraseña, actualizarla
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "UPDATE empleado SET 
                primer_nombre = ?,
                segundo_nombre = ?,
                primer_apellido = ?,
                segundo_apellido = ?,
                cedula = ?,
                telefono = ?,
                correo_electronico = ?,
                direccion = ?,
                fecha_nacimiento = ?,
                fecha_contratacion = ?,
                id_cargo = ?,
                password = ?,
                estado = ?
                WHERE id_empleado = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssisssi",
                $primer_nombre,
                $segundo_nombre,
                $primer_apellido,
                $segundo_apellido,
                $cedula,
                $telefono,
                $correo_electronico,
                $direccion,
                $fecha_nacimiento,
                $fecha_contratacion,
                $id_cargo,
                $password_hash,
                $estado,
                $id_empleado
            );
        } else {
            // Si no se proporciona contraseña, no actualizarla
            $sql = "UPDATE empleado SET 
                primer_nombre = ?,
                segundo_nombre = ?,
                primer_apellido = ?,
                segundo_apellido = ?,
                cedula = ?,
                telefono = ?,
                correo_electronico = ?,
                direccion = ?,
                fecha_nacimiento = ?,
                fecha_contratacion = ?,
                id_cargo = ?,
                estado = ?
                WHERE id_empleado = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssissi",
                $primer_nombre,
                $segundo_nombre,
                $primer_apellido,
                $segundo_apellido,
                $cedula,
                $telefono,
                $correo_electronico,
                $direccion,
                $fecha_nacimiento,
                $fecha_contratacion,
                $id_cargo,
                $estado,
                $id_empleado
            );
        }
        
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Error al preparar la consulta: ' . $conn->error
            ];
        }
        
        if ($stmt->execute()) {
            try {
                logActivity('EDITAR_EMPLEADO', "Empleado editado: $primer_nombre $primer_apellido (ID: $id_empleado)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Empleado actualizado exitosamente'
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            return [
                'success' => false,
                'message' => 'Error al actualizar el empleado: ' . $error
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
 * Eliminar un empleado
 */
function eliminarEmpleado($conn) {
    try {
        $id_empleado = intval($_POST['id_empleado'] ?? 0);
        
        if ($id_empleado <= 0) {
            return [
                'success' => false,
                'message' => 'ID de empleado no válido'
            ];
        }
        
        // Obtener información del empleado antes de eliminar
        $stmt = $conn->prepare("SELECT primer_nombre, primer_apellido FROM empleado WHERE id_empleado = ?");
        $stmt->bind_param("i", $id_empleado);
        $stmt->execute();
        $result = $stmt->get_result();
        $empleado = $result->fetch_assoc();
        
        if (!$empleado) {
            $stmt->close();
            return [
                'success' => false,
                'message' => 'El empleado no existe'
            ];
        }
        $stmt->close();
        
        // Eliminar el empleado
        $stmt = $conn->prepare("DELETE FROM empleado WHERE id_empleado = ?");
        $stmt->bind_param("i", $id_empleado);
        
        if ($stmt->execute()) {
            try {
                logActivity('ELIMINAR_EMPLEADO', "Empleado eliminado: {$empleado['primer_nombre']} {$empleado['primer_apellido']} (ID: $id_empleado)");
            } catch (Exception $e) {
                // Ignorar errores de log
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Empleado eliminado exitosamente'
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            
            return [
                'success' => false,
                'message' => 'Error al eliminar el empleado: ' . $error
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