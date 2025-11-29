<?php
/**
 * CHECK_SESSION.PHP
 * Verificar si la sesión del usuario sigue activa
 */

session_start();
header('Content-Type: application/json');

$timeout = 1800; // 30 minutos en segundos

$response = [
    'active' => false,
    'remaining_time' => 0
];

if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
    $inactive_time = time() - $_SESSION['last_activity'];
    
    if ($inactive_time < $timeout) {
        $response['active'] = true;
        $response['remaining_time'] = $timeout - $inactive_time;
    } else {
        // Sesión expirada
        session_unset();
        session_destroy();
    }
}

echo json_encode($response);