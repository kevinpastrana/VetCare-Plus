<?php
/**
 * DATABASE.PHP - Configuración de Base de Datos
 * Gestiona la conexión a MySQL/MariaDB
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'veterinaria');
define('DB_CHARSET', 'utf8mb4');

/**
 * Obtener conexión a la base de datos
 * 
 * @return mysqli Objeto de conexión
 * @throws Exception Si falla la conexión
 */
function getConnection() {
    static $connection = null;
    
    if ($connection === null) {
        // Crear nueva conexión
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Verificar errores de conexión
        if ($connection->connect_error) {
            error_log('Error de conexión a BD: ' . $connection->connect_error);
            throw new Exception('Error al conectar con la base de datos');
        }
        
        // Establecer charset
        if (!$connection->set_charset(DB_CHARSET)) {
            error_log('Error al establecer charset: ' . $connection->error);
        }
        
        // Configurar modo de errores
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }
    
    return $connection;
}

/**
 * Cerrar conexión a la base de datos
 */
function closeConnection() {
    global $connection;
    if ($connection !== null) {
        $connection->close();
        $connection = null;
    }
}

/**
 * Ejecutar una consulta preparada de forma segura
 * 
 * @param string $query Consulta SQL con placeholders
 * @param array $params Parámetros para la consulta
 * @param string $types Tipos de datos (i=integer, s=string, d=double, b=blob)
 * @return mysqli_result|bool Resultado de la consulta
 */
function executeQuery($query, $params = [], $types = '') {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result();
        
    } catch (Exception $e) {
        error_log('Error en consulta SQL: ' . $e->getMessage());
        return false;
    }
}

/**
 * Obtener un registro único
 * 
 * @param string $query Consulta SQL
 * @param array $params Parámetros
 * @param string $types Tipos de datos
 * @return array|null Registro encontrado o null
 */
function fetchOne($query, $params = [], $types = '') {
    $result = executeQuery($query, $params, $types);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Obtener múltiples registros
 * 
 * @param string $query Consulta SQL
 * @param array $params Parámetros
 * @param string $types Tipos de datos
 * @return array Array de registros
 */
function fetchAll($query, $params = [], $types = '') {
    $result = executeQuery($query, $params, $types);
    $records = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
    }
    
    return $records;
}

// Manejo de errores de conexión al inicio
try {
    $test_connection = getConnection();
    error_log('Conexión a base de datos establecida correctamente');
} catch (Exception $e) {
    error_log('CRÍTICO: No se pudo conectar a la base de datos');
    // En producción, redirigir a una página de error
}