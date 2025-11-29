<?php
/**
 * FUNCTIONS.PHP - Funciones Auxiliares del Sistema
 * Funciones reutilizables para seguridad, validación y utilidades
 */

/**
 * Sanitizar entrada de usuario
 * 
 * @param string $data Datos a sanitizar
 * @return string Datos sanitizados
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Verificar si el usuario está autenticado
 * 
 * @return bool True si está autenticado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Requerir autenticación
 * Redirige al login si no está autenticado
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
    
    // Verificar timeout de sesión (30 minutos de inactividad)
    $timeout = 1800; // 30 minutos
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_unset();
        session_destroy();
        header('Location: index.php?timeout=1');
        exit();
    }
    
    // Actualizar tiempo de última actividad
    $_SESSION['last_activity'] = time();
}

/**
 * Verificar si el usuario tiene un cargo específico
 * 
 * @param int $cargo_id ID del cargo requerido
 * @return bool True si tiene el cargo
 */
function hasRole($cargo_id) {
    return isset($_SESSION['user_cargo_id']) && $_SESSION['user_cargo_id'] == $cargo_id;
}

/**
 * Verificar si el usuario es administrador
 * 
 * @return bool True si es administrador
 */
function isAdmin() {
    return hasRole(1); // 1 = Veterinario (considerado admin en este ejemplo)
}

/**
 * Generar token CSRF
 * 
 * @return string Token generado
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 * 
 * @param string $token Token a verificar
 * @return bool True si el token es válido
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validar correo electrónico
 * 
 * @param string $email Correo a validar
 * @return bool True si es válido
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar número de teléfono (formato colombiano)
 * 
 * @param string $phone Teléfono a validar
 * @return bool True si es válido
 */
function isValidPhone($phone) {
    // Formato: 10 dígitos comenzando con 3
    return preg_match('/^3\d{9}$/', $phone);
}

/**
 * Validar cédula colombiana
 * 
 * @param string $cedula Cédula a validar
 * @return bool True si es válida
 */
function isValidCedula($cedula) {
    // Entre 6 y 10 dígitos
    return preg_match('/^\d{6,10}$/', $cedula);
}

/**
 * Hashear contraseña de forma segura
 * 
 * @param string $password Contraseña en texto plano
 * @return string Hash de la contraseña
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verificar contraseña hasheada
 * 
 * @param string $password Contraseña en texto plano
 * @param string $hash Hash almacenado
 * @return bool True si coinciden
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Formatear fecha al formato español
 * 
 * @param string $date Fecha en formato Y-m-d
 * @return string Fecha formateada
 */
function formatDate($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Formatear fecha y hora
 * 
 * @param string $datetime Fecha y hora
 * @return string Formato legible
 */
function formatDateTime($datetime) {
    $timestamp = strtotime($datetime);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Calcular edad desde fecha de nacimiento
 * 
 * @param string $birthdate Fecha de nacimiento (Y-m-d)
 * @return int Edad en años
 */
function calculateAge($birthdate) {
    $birth = new DateTime($birthdate);
    $today = new DateTime('today');
    return $birth->diff($today)->y;
}

/**
 * Generar nombre aleatorio para archivos
 * 
 * @param string $original_name Nombre original del archivo
 * @return string Nombre único generado
 */
function generateUniqueFileName($original_name) {
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    return uniqid('file_', true) . '.' . $extension;
}

/**
 * Redireccionar con mensaje
 * 
 * @param string $url URL de destino
 * @param string $message Mensaje a mostrar
 * @param string $type Tipo de mensaje (success, error, info)
 */
function redirect($url, $message = '', $type = 'info') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header('Location: ' . $url);
    exit();
}

/**
 * Obtener y limpiar mensaje flash
 * 
 * @return array|null Array con mensaje y tipo, o null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'text' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }
    return null;
}

/**
 * Registrar log de actividad
 * 
 * @param string $action Acción realizada
 * @param string $details Detalles adicionales
 */
function logActivity($action, $details = '') {
    try {
        $user_id = $_SESSION['user_id'] ?? 0;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $log_entry = sprintf(
            "[%s] Usuario: %d | IP: %s | Acción: %s | Detalles: %s\n",
            date('Y-m-d H:i:s'),
            $user_id,
            $ip_address,
            $action,
            $details
        );
        
        // Determinar la ruta correcta para la carpeta logs
        // Intentar diferentes rutas posibles
        $possible_paths = [
            __DIR__ . '/../logs',           // Desde Includes/ hacia raíz
            __DIR__ . '/../../logs',        // Dos niveles arriba
            $_SERVER['DOCUMENT_ROOT'] . '/Veterinaria_Login/logs'
        ];
        
        $log_dir = null;
        foreach ($possible_paths as $path) {
            if (is_dir(dirname($path)) || @mkdir(dirname($path), 0777, true)) {
                $log_dir = $path;
                break;
            }
        }
        
        // Si no se pudo crear la carpeta, usar carpeta temporal del sistema
        if (!$log_dir) {
            $log_dir = sys_get_temp_dir() . '/veterinaria_logs';
        }
        
        // Crear carpeta logs si no existe
        if (!file_exists($log_dir)) {
            @mkdir($log_dir, 0777, true);
        }
        
        $log_file = $log_dir . '/activity.log';
        
        // Intentar escribir el log
        @error_log($log_entry, 3, $log_file);
    } catch (Exception $e) {
        // Si falla el log, no hacer nada para no interrumpir la operación principal
        // Opcionalmente registrar en el error log de PHP
        @error_log('Error al escribir log de actividad: ' . $e->getMessage());
    }
}

/**
 * Limpiar variable GET/POST de forma segura
 * 
 * @param string $key Nombre de la variable
 * @param mixed $default Valor por defecto
 * @param string $method Método HTTP (GET o POST)
 * @return mixed Valor sanitizado
 */
function getInput($key, $default = null, $method = 'GET') {
    $source = ($method === 'POST') ? $_POST : $_GET;
    
    if (!isset($source[$key])) {
        return $default;
    }
    
    return sanitize_input($source[$key]);
}

/**
 * Convertir bytes a formato legible
 * 
 * @param int $bytes Tamaño en bytes
 * @return string Tamaño formateado
 */
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}