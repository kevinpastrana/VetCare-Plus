<?php
/**
 * REPORTEGENERAL.PHP - Dashboard General del Sistema
 * Sistema de Gestión Veterinaria VetCare Plus
 */

session_start();
require_once '../../Includes/functions.php';

// Verificar autenticación
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Conexión a la base de datos
$host = 'localhost';
$dbname = 'veterinaria';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

try {
    // Obtener estadísticas generales
    
    // Total de mascotas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mascota");
    $total_mascotas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de dueños
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM dueno");
    $total_duenos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de empleados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM empleado");
    $total_empleados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Consultas del mes actual
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM consulta 
                        WHERE MONTH(fecha_consulta) = MONTH(CURRENT_DATE()) 
                        AND YEAR(fecha_consulta) = YEAR(CURRENT_DATE())");
    $consultas_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Consultas de hoy
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM consulta 
                        WHERE DATE(fecha_consulta) = CURRENT_DATE()");
    $consultas_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Ingresos del mes - CORREGIDO: fecha_factura en lugar de fecha_emision
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as total FROM factura 
                        WHERE MONTH(fecha_factura) = MONTH(CURRENT_DATE()) 
                        AND YEAR(fecha_factura) = YEAR(CURRENT_DATE())");
    $ingresos_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Facturas pendientes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM factura WHERE estado = 'Pendiente'");
    $facturas_pendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Procedimientos del mes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM procedimiento 
                        WHERE MONTH(fecha_procedimiento) = MONTH(CURRENT_DATE()) 
                        AND YEAR(fecha_procedimiento) = YEAR(CURRENT_DATE())");
    $procedimientos_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Mascotas por especie - CORREGIDO: COUNT con alias para evitar conflictos
    $stmt = $pdo->query("SELECT especie, COUNT(*) as cantidad 
                        FROM mascota 
                        WHERE estado = 'Activo'
                        GROUP BY especie 
                        ORDER BY cantidad DESC");
    $mascotas_especie = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Últimas consultas - CORREGIDO: usar campos correctos de la BD
    $stmt = $pdo->query("SELECT 
        c.id_consulta,
        c.fecha_consulta,
        c.motivo,
        CONCAT(m.nombre, ' (', m.especie, ')') AS mascota,
        CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno,
        CONCAT(e.primer_nombre, ' ', e.primer_apellido) AS veterinario
    FROM consulta c
    INNER JOIN mascota m ON c.id_mascota = m.id_mascota
    INNER JOIN dueno d ON m.id_dueno = d.id_dueno
    INNER JOIN empleado e ON c.id_empleado = e.id_empleado
    ORDER BY c.fecha_consulta DESC
    LIMIT 5");
    $ultimas_consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Próximas citas - CORREGIDO: usar campos correctos
    $stmt = $pdo->query("SELECT 
        c.id_consulta,
        c.proxima_cita,
        CONCAT(m.nombre, ' (', m.especie, ')') AS mascota,
        CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno
    FROM consulta c
    INNER JOIN mascota m ON c.id_mascota = m.id_mascota
    INNER JOIN dueno d ON m.id_dueno = d.id_dueno
    WHERE c.proxima_cita >= CURRENT_DATE()
    ORDER BY c.proxima_cita ASC
    LIMIT 5");
    $proximas_citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error al obtener estadísticas: " . $e->getMessage();
    // Valores por defecto en caso de error
    $total_mascotas = 0;
    $total_duenos = 0;
    $total_empleados = 0;
    $consultas_mes = 0;
    $consultas_hoy = 0;
    $ingresos_mes = 0;
    $facturas_pendientes = 0;
    $procedimientos_mes = 0;
    $mascotas_especie = [];
    $ultimas_consultas = [];
    $proximas_citas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard General - Sistema Veterinaria</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../Assets/Css/Dashboard.css">
    <link rel="stylesheet" href="../../Assets/Css/Reportes.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <a href="index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver a Reportes</span>
                </a>
            </div>
            <div class="nav-center">
                <h1 class="page-title">
                    <i class="fas fa-chart-pie"></i>
                    Dashboard General del Sistema
                </h1>
            </div>
            <div class="nav-right">
                <div class="user-badge">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($user_name); ?></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Estadísticas Principales -->
            <section style="margin-bottom: 2.5rem;">
                <h3 class="section-title">
                    <i class="fas fa-chart-bar"></i>
                    Estadísticas Principales
                </h3>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="fas fa-paw"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_mascotas; ?></div>
                            <div class="stat-label">Mascotas Registradas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_duenos; ?></div>
                            <div class="stat-label">Propietarios</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_empleados; ?></div>
                            <div class="stat-label">Empleados</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $consultas_mes; ?></div>
                            <div class="stat-label">Consultas este mes</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Estadísticas Secundarias -->
            <section style="margin-bottom: 2.5rem;">
                <h3 class="section-title">
                    <i class="fas fa-calendar-day"></i>
                    Actividad del Día
                </h3>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $consultas_hoy; ?></div>
                            <div class="stat-label">Consultas de hoy</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">
                                $<?php echo number_format($ingresos_mes, 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Ingresos del mes</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $facturas_pendientes; ?></div>
                            <div class="stat-label">Facturas pendientes</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <i class="fas fa-syringe"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $procedimientos_mes; ?></div>
                            <div class="stat-label">Procedimientos del mes</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Grid de 2 columnas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-bottom: 2.5rem;">
                <!-- Mascotas por Especie -->
                <div class="table-container">
                    <div class="table-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="table-title" style="color: white;">
                            <i class="fas fa-dog"></i>
                            Mascotas por Especie
                        </div>
                    </div>
                    <?php if (empty($mascotas_especie)): ?>
                        <div class="empty-state">
                            <i class="fas fa-paw"></i>
                            <p>No hay mascotas registradas</p>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Especie</th>
                                    <th style="text-align: right;">Cantidad</th>
                                    <th style="text-align: right;">Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($mascotas_especie as $especie): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($especie['especie']); ?></strong>
                                        </td>
                                        <td style="text-align: right;">
                                            <span class="badge badge-info">
                                                <?php echo $especie['cantidad']; ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php 
                                            $porcentaje = ($total_mascotas > 0) 
                                                ? round(($especie['cantidad'] / $total_mascotas) * 100, 1) 
                                                : 0;
                                            echo $porcentaje . '%';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Próximas Citas -->
                <div class="table-container">
                    <div class="table-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <div class="table-title" style="color: white;">
                            <i class="fas fa-calendar-alt"></i>
                            Próximas Citas
                        </div>
                    </div>
                    <?php if (empty($proximas_citas)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>No hay citas programadas</p>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Mascota</th>
                                    <th>Dueño</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($proximas_citas as $cita): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-calendar text-success"></i>
                                            <?php echo date('d/m/Y', strtotime($cita['proxima_cita'])); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($cita['mascota']); ?></td>
                                        <td><?php echo htmlspecialchars($cita['dueno']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Últimas Consultas -->
            <section>
                <h3 class="section-title">
                    <i class="fas fa-history"></i>
                    Últimas Consultas Realizadas
                </h3>
                
                <div class="table-container">
                    <?php if (empty($ultimas_consultas)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No hay consultas registradas</p>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Mascota</th>
                                    <th>Dueño</th>
                                    <th>Veterinario</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ultimas_consultas as $consulta): ?>
                                    <tr>
                                        <td><?php echo $consulta['id_consulta']; ?></td>
                                        <td>
                                            <i class="fas fa-calendar text-primary"></i>
                                            <?php echo date('d/m/Y', strtotime($consulta['fecha_consulta'])); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($consulta['mascota']); ?></td>
                                        <td><?php echo htmlspecialchars($consulta['dueno']); ?></td>
                                        <td><?php echo htmlspecialchars($consulta['veterinario']); ?></td>
                                        <td>
                                            <span class="text-truncate" title="<?php echo htmlspecialchars($consulta['motivo']); ?>">
                                                <?php echo htmlspecialchars($consulta['motivo']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Botones de Acción -->
            <div class="actions-grid" style="margin-top: 2.5rem;">
                <button class="action-btn" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    <span>Imprimir Dashboard</span>
                </button>
                <button class="action-btn" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt"></i>
                    <span>Actualizar Datos</span>
                </button>
                <a href="index.php" class="action-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver a Reportes</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 VetCare Plus - Sistema de Gestión Veterinaria | Desarrollado con ❤️</p>
    </footer>

    <script src="../../Assets/Js/Reportes.js"></script>
</body>
</html>