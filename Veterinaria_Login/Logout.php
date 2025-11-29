<?php
/**
 * LOGOUT.PHP - Cerrar Sesión
 * Destruye la sesión del usuario y redirige al login
 */

session_start();

// Registrar logout en logs
if (isset($_SESSION['user_name'])) {
    error_log("🚪 Logout - Usuario: {$_SESSION['user_name']} (ID: {$_SESSION['user_id']})");
}

// Eliminar todas las variables de sesión
$_SESSION = array();

// Eliminar cookie de sesión
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Eliminar cookie "remember"
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/', '', false, true);
}

// Destruir la sesión
session_destroy();

// Redirigir al login con mensaje de logout exitoso
header('Location: Index.php?logout=1');
exit();