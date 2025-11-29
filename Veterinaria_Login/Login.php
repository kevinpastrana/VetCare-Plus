<?php
/**
 * LOGIN.PHP - Procesamiento de Autenticación
 * Sistema de Gestión Veterinaria
 */

session_start();

// Incluir configuración de base de datos
require_once 'Config/database.php';
require_once 'Includes/functions.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Index.php');
    exit();
}

// Obtener y sanitizar datos del formulario
$username = sanitize_input($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validaciones del servidor
if (empty($username) || empty($password)) {
    $_SESSION['error_login'] = 'Por favor completa todos los campos';
    header('Location: Index.php');
    exit();
}

try {
    // Conectar a la base de datos
    $conn = getConnection();
    
    // Preparar consulta para buscar usuario
    $stmt = $conn->prepare("
        SELECT 
            e.id_empleado,
            e.primer_nombre,
            e.primer_apellido,
            e.correo_electronico,
            e.cedula,
            e.estado,
            e.password,
            c.nombre_cargo,
            c.id_cargo
        FROM empleado e
        INNER JOIN cargo c ON e.id_cargo = c.id_cargo
        WHERE e.correo_electronico = ? OR e.cedula = ?
        LIMIT 1
    ");
    
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Usuario no encontrado
        $_SESSION['error_login'] = 'Usuario o contraseña incorrectos';
        error_log("Login fallido - Usuario no encontrado: {$username}");
        header('Location: Index.php');
        exit();
    }
    
    $user = $result->fetch_assoc();
    
    // Verificar si el usuario está activo
    if ($user['estado'] !== 'activo') {
        $_SESSION['error_login'] = 'Tu cuenta está inactiva. Contacta al administrador';
        error_log("Login fallido - Usuario inactivo: {$username}");
        header('Location: Index.php');
        exit();
    }
    
    // Verificar contraseña
    $password_valida = false;
    
    // Método 1: Si la contraseña está hasheada con password_hash
    if (!empty($user['password']) && strlen($user['password']) >= 60) {
        if (password_verify($password, $user['password'])) {
            $password_valida = true;
        }
    }
    
    // Método 2: Comparación directa (temporal)
    if (!$password_valida && !empty($user['password'])) {
        if ($password === $user['password']) {
            $password_valida = true;
        }
    }
    
    // Método 3: Contraseña fija para testing
    if (!$password_valida && $password === 'admin123') {
        $password_valida = true;
    }
    
    if ($password_valida) {
        // ✅ LOGIN EXITOSO
        
        // Regenerar ID de sesión para prevenir session fixation
        session_regenerate_id(true);
        
        // Guardar datos en sesión
        $_SESSION['user_id'] = $user['id_empleado'];
        $_SESSION['user_name'] = $user['primer_nombre'] . ' ' . $user['primer_apellido'];
        $_SESSION['user_email'] = $user['correo_electronico'];
        $_SESSION['user_cargo'] = $user['nombre_cargo'];
        $_SESSION['user_cargo_id'] = $user['id_cargo'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Si marcó "Recordarme", crear cookie de sesión extendida
        if ($remember) {
            // Cookie por 30 días
            setcookie('remember_user', $user['id_empleado'], time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }
        
        // Registrar login exitoso en logs
        error_log("✅ Login exitoso - Usuario: {$user['primer_nombre']} {$user['primer_apellido']} (ID: {$user['id_empleado']})");
        
        // Redirigir al dashboard
        header('Location: Dashboard.php');
        exit();
        
    } else {
        // ❌ CONTRASEÑA INCORRECTA
        $_SESSION['error_login'] = 'Usuario o contraseña incorrectos';
        error_log("❌ Login fallido - Contraseña incorrecta para: {$username}");
        header('Location: Index.php');
        exit();
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Error del servidor
    error_log('🔴 Error en login: ' . $e->getMessage());
    $_SESSION['error_login'] = 'Error del servidor. Por favor intenta más tarde';
    header('Location: Index.php');
    exit();
}