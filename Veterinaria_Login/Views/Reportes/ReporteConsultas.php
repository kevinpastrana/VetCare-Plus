<?php
/**
 * REPORTECONSULTAS.PHP - Reporte de Consultas Médicas
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

// Obtener filtros
$fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01'); // Primer día del mes actual
$fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d'); // Fecha actual
$id_mascota = $_GET['id_mascota'] ?? '';
$id_empleado = $_GET['id_empleado'] ?? '';

// Construir consulta SQL con filtros - CORREGIDO: usar campos correctos de la BD
$sql = "SELECT 
    c.id_consulta,
    c.fecha_consulta,
    c.motivo,
    c.diagnostico,
    c.tratamiento,
    CONCAT(m.nombre, ' (', m.especie, ')') AS mascota,
    CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno,
    CONCAT(e.primer_nombre, ' ', e.primer_apellido) AS veterinario
FROM consulta c
INNER JOIN mascota m ON c.id_mascota = m.id_mascota
INNER JOIN dueno d ON m.id_dueno = d.id_dueno
INNER JOIN empleado e ON c.id_empleado = e.id_empleado
WHERE c.fecha_consulta BETWEEN :fecha_desde AND :fecha_hasta";

if (!empty($id_mascota)) {
    $sql .= " AND c.id_mascota = :id_mascota";
}

if (!empty($id_empleado)) {
    $sql .= " AND c.id_empleado = :id_empleado";
}

$sql .= " ORDER BY c.fecha_consulta DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':fecha_desde', $fecha_desde);
    $stmt->bindParam(':fecha_hasta', $fecha_hasta);
    
    if (!empty($id_mascota)) {
        $stmt->bindParam(':id_mascota', $id_mascota);
    }
    
    if (!empty($id_empleado)) {
        $stmt->bindParam(':id_empleado', $id_empleado);
    }
    
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_consultas = count($consultas);
    
    // Obtener listas para filtros - CORREGIDO: usar campos correctos
    $stmt_mascotas = $pdo->query("SELECT id_mascota, nombre, especie FROM mascota WHERE estado = 'Activo' ORDER BY nombre");
    $mascotas = $stmt_mascotas->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_empleados = $pdo->query("SELECT id_empleado, primer_nombre, primer_apellido FROM empleado WHERE id_cargo IN (SELECT id_cargo FROM cargo WHERE nombre_cargo LIKE '%Veterinario%') AND estado = 'activo' ORDER BY primer_nombre");
    $empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error al obtener datos: " . $e->getMessage();
    $consultas = [];
    $total_consultas = 0;
    $mascotas = [];
    $empleados = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Consultas - Sistema Veterinaria</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../Assets/Css/Dashboard.css">
    <link rel="stylesheet" href="../../Assets/Css/Reportes.css">
    <link rel="stylesheet" href="../../Assets/Css/FormsConsultas.css">
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
                    <i class="fas fa-stethoscope"></i>
                    Reporte de Consultas Médicas
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
            <!-- Filtros -->
            <div class="filters-card">
                <h3 style="margin-bottom: 1.5rem; color: #1e293b; font-size: 1.25rem;">
                    <i class="fas fa-filter"></i> Filtros de Búsqueda
                </h3>
                <form method="GET" action="">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label for="fecha_desde">
                                <i class="fas fa-calendar-alt"></i> Fecha Desde
                            </label>
                            <input type="date" id="fecha_desde" name="fecha_desde" 
                                   value="<?php echo htmlspecialchars($fecha_desde); ?>" 
                                   required>
                        </div>
                        
                        <div class="filter-group">
                            <label for="fecha_hasta">
                                <i class="fas fa-calendar-check"></i> Fecha Hasta
                            </label>
                            <input type="date" id="fecha_hasta" name="fecha_hasta" 
                                   value="<?php echo htmlspecialchars($fecha_hasta); ?>" 
                                   required>
                        </div>
                        
                        <div class="filter-group">
                            <label for="id_mascota">
                                <i class="fas fa-dog"></i> Mascota (Opcional)
                            </label>
                            <select id="id_mascota" name="id_mascota">
                                <option value="">Todas las mascotas</option>
                                <?php foreach($mascotas as $mascota): ?>
                                    <option value="<?php echo $mascota['id_mascota']; ?>"
                                            <?php echo ($id_mascota == $mascota['id_mascota']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($mascota['nombre'] . ' - ' . $mascota['especie']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="id_empleado">
                                <i class="fas fa-user-md"></i> Veterinario (Opcional)
                            </label>
                            <select id="id_empleado" name="id_empleado">
                                <option value="">Todos los veterinarios</option>
                                <?php foreach($empleados as $empleado): ?>
                                    <option value="<?php echo $empleado['id_empleado']; ?>"
                                            <?php echo ($id_empleado == $empleado['id_empleado']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($empleado['primer_nombre'] . ' ' . $empleado['primer_apellido']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter primary">
                            <i class="fas fa-search"></i>
                            Generar Reporte
                        </button>
                        <a href="ReporteConsultas.php" class="btn-filter secondary">
                            <i class="fas fa-undo"></i>
                            Limpiar Filtros
                        </a>
                    </div>
                </form>
            </div>

            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $total_consultas; ?></div>
                        <div class="stat-label">Consultas en el período</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">
                            <?php 
                            $dias = (strtotime($fecha_hasta) - strtotime($fecha_desde)) / (60 * 60 * 24) + 1;
                            echo round($dias);
                            ?>
                        </div>
                        <div class="stat-label">Días analizados</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">
                            <?php 
                            if ($dias > 0) {
                                echo number_format($total_consultas / $dias, 1);
                            } else {
                                echo "0";
                            }
                            ?>
                        </div>
                        <div class="stat-label">Promedio diario</div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Resultados -->
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-table"></i>
                        Resultados del Reporte
                        <span class="badge badge-info"><?php echo $total_consultas; ?> registros</span>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button onclick="imprimirReporte()" class="btn-icon btn-view" title="Imprimir">
                            <i class="fas fa-print"></i>
                        </button>
                        <button onclick="exportarExcel()" class="btn-icon btn-edit" title="Exportar Excel">
                            <i class="fas fa-file-excel"></i>
                        </button>
                    </div>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" style="margin: 1.5rem;">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php elseif (empty($consultas)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No se encontraron consultas con los filtros seleccionados</p>
                        <a href="ReporteConsultas.php" class="btn-primary">
                            <i class="fas fa-redo"></i>
                            Restablecer filtros
                        </a>
                    </div>
                <?php else: ?>
                    <table class="data-table" id="tablaReporte">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Mascota</th>
                                <th>Dueño</th>
                                <th>Veterinario</th>
                                <th>Motivo</th>
                                <th>Diagnóstico</th>
                                <th>Tratamiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($consultas as $consulta): ?>
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
                                    <td>
                                        <span class="text-truncate" title="<?php echo htmlspecialchars($consulta['diagnostico']); ?>">
                                            <?php echo htmlspecialchars($consulta['diagnostico']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-truncate" title="<?php echo htmlspecialchars($consulta['tratamiento']); ?>">
                                            <?php echo htmlspecialchars($consulta['tratamiento']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 VetCare Plus - Sistema de Gestión Veterinaria | Desarrollado con ❤️</p>
    </footer>

    <script>
        function imprimirReporte() {
            window.print();
        }
        
        function exportarExcel() {
            // Implementación básica de exportar a Excel
            const tabla = document.getElementById('tablaReporte');
            if (!tabla) {
                alert('No hay datos para exportar');
                return;
            }
            
            let csv = [];
            const filas = tabla.querySelectorAll('tr');
            
            filas.forEach(fila => {
                const cols = fila.querySelectorAll('td, th');
                const csvrow = [];
                cols.forEach(col => {
                    csvrow.push('"' + col.innerText.replace(/"/g, '""') + '"');
                });
                csv.push(csvrow.join(','));
            });
            
            const csvString = csv.join('\n');
            const blob = new Blob(['\ufeff' + csvString], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            link.setAttribute('href', url);
            link.setAttribute('download', 'reporte_consultas_' + new Date().getTime() + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>