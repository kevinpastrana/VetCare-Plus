<?php
/**
 * UPDATE_ACTIVITY.PHP
 * Actualizar la última actividad del usuario
 */

session_start();
header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
    $response['success'] = true;
    $response['message'] = 'Actividad actualizada';
    $response['timestamp'] = time();
} else {
    $response['message'] = 'No hay sesión activa';
}

echo json_encode($response);