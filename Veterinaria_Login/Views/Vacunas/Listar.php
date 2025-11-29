<?php
/**
 * LISTAR VACUNAS
 * Visualización y gestión de registros de vacunación
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';
$conn = getConnection();

// Obtener todas las vacunas
$query = "SELECT 
    v.id_vacuna,
    v.nombre_vacuna,
    v.laboratorio,
    v.lote,
    v.dosis,
    v.via_administracion,
    v.fecha_aplicacion,
    v.proxima_aplicacion,
    m.nombre AS mascota_nombre,
    m.especie,
    CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno_nombre,
    CONCAT(e.primer_nombre, ' ', e.primer_apellido) AS veterinario_nombre
FROM vacuna v
INNER JOIN mascota m ON v.id_mascota = m.id_mascota
INNER JOIN dueno d ON m.id_dueno = d.id_dueno
INNER JOIN empleado e ON v.id_empleado = e.id_empleado
ORDER BY v.fecha_aplicacion DESC, v.id_vacuna DESC";

$resultado = $conn->query($query);
$total_vacunas = $resultado->num_rows;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacunas - VetCare Plus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">        
    <link rel="stylesheet" href="../../Assets/Css/Dashboard.css">
    <link rel="stylesheet" href="../../Assets/Css/FormsConsultas.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../../Dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Volver al Dashboard
                </a>
            </div>
            <div class="nav-center">
                <h1 class="page-title">
                    <i class="fas fa-shield-virus"></i>
                    Control de Vacunación
                </h1>
            </div>
            <div class="nav-right">
                <span class="user-badge">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($user_name); ?>
                </span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="../../Dashboard.php"><i class="fas fa-home"></i> Inicio</a>
                <span class="separator">/</span>
                <span class="current">Vacunas</span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <i class="fas fa-shield-virus"></i>
                    Listado de Vacunas
                </h1>
                <p>Administra el historial completo de vacunación</p>
            </div>

            <!-- Search Header -->
            <div class="table-header">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por vacuna, mascota, dueño...">
                </div>
                <a href="Registrar.php" class="btn-primary">
                    <i class="fas fa-plus"></i>
                    Nueva Vacuna
                </a>
            </div>

            <!-- Estadísticas -->
            <div class="stats-cards">
                <?php
                // Calcular estadísticas
                $hoy = date('Y-m-d');
                $proximas_30_dias = date('Y-m-d', strtotime('+30 days'));
                
                $stats_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN proxima_aplicacion IS NOT NULL AND proxima_aplicacion BETWEEN '$hoy' AND '$proximas_30_dias' THEN 1 ELSE 0 END) as proximas,
                    SUM(CASE WHEN fecha_aplicacion >= DATE_SUB('$hoy', INTERVAL 30 DAY) THEN 1 ELSE 0 END) as recientes
                FROM vacuna";
                $stats = $conn->query($stats_query)->fetch_assoc();
                ?>
                
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-shield-virus"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total']); ?></h3>
                        <p>Total Vacunas</p>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['recientes']); ?></h3>
                        <p>Últimos 30 días</p>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['proximas']); ?></h3>
                        <p>Próximas (30 días)</p>
                    </div>
                </div>
            </div>

            <!-- Tabla de Vacunas -->
            <div class="table-container">
                <table class="data-table" id="tablaVacunas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vacuna</th>
                            <th>Laboratorio</th>
                            <th>Mascota</th>
                            <th>Dueño</th>
                            <th>Vía</th>
                            <th>Fecha Aplicación</th>
                            <th>Próxima Dosis</th>
                            <th>Veterinario</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_vacunas > 0): ?>
                            <?php while($vac = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($vac['id_vacuna'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($vac['nombre_vacuna']); ?></strong>
                                        <?php if ($vac['lote']): ?>
                                            <br><small class="text-muted"><i class="fas fa-barcode"></i> Lote: <?php echo htmlspecialchars($vac['lote']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($vac['laboratorio']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($vac['mascota_nombre']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($vac['especie']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($vac['dueno_nombre']); ?></td>
                                    <td>
                                        <?php
                                        $via_icons = [
                                            'Subcutánea' => 'fa-syringe',
                                            'Intramuscular' => 'fa-syringe',
                                            'Oral' => 'fa-pills',
                                            'Intranasal' => 'fa-head-side-virus'
                                        ];
                                        $icon = $via_icons[$vac['via_administracion']] ?? 'fa-syringe';
                                        ?>
                                        <span class="badge badge-info">
                                            <i class="fas <?php echo $icon; ?>"></i>
                                            <?php echo htmlspecialchars($vac['via_administracion']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d/m/Y', strtotime($vac['fecha_aplicacion'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($vac['proxima_aplicacion']): ?>
                                            <?php
                                            $proxima = strtotime($vac['proxima_aplicacion']);
                                            $hoy_ts = strtotime(date('Y-m-d'));
                                            $diff_days = floor(($proxima - $hoy_ts) / 86400);
                                            
                                            if ($diff_days < 0) {
                                                $badge_class = 'badge-danger';
                                                $icon = 'fa-exclamation-triangle';
                                            } elseif ($diff_days <= 30) {
                                                $badge_class = 'badge-warning';
                                                $icon = 'fa-clock';
                                            } else {
                                                $badge_class = 'badge-success';
                                                $icon = 'fa-calendar-check';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <i class="fas <?php echo $icon; ?>"></i>
                                                <?php echo date('d/m/Y', $proxima); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin programar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($vac['veterinario_nombre']); ?></td>
                                    <td class="text-center">
                                        <div class="actions">
                                            <button class="btn-icon btn-view" 
                                                    onclick="verDetalle(<?php echo $vac['id_vacuna']; ?>)"
                                                    title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="Editar.php?id=<?php echo $vac['id_vacuna']; ?>" 
                                               class="btn-icon btn-edit"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn-icon btn-delete" 
                                                    onclick="confirmarEliminar(<?php echo $vac['id_vacuna']; ?>, '<?php echo addslashes($vac['nombre_vacuna']); ?>')"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">
                                    <div class="empty-state">
                                        <i class="fas fa-shield-virus"></i>
                                        <p>No hay vacunas registradas</p>
                                        <a href="Registrar.php" class="btn-primary">
                                            <i class="fas fa-plus"></i>
                                            Registrar Primera Vacuna
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Ver Detalle -->
    <div id="modalDetalle" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-shield-virus"></i> Detalle de la Vacuna</h2>
                <button class="btn-close" onclick="cerrarModal('modalDetalle')">&times;</button>
            </div>
            <div class="modal-body" id="detalleContent">
                <!-- El contenido se carga dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Modal Eliminar -->
    <div id="modalEliminar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h2>
                <button class="btn-close" onclick="cerrarModal('modalEliminar')">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el registro de <strong id="nombreEliminar"></strong>?</p>
                <p class="text-muted"><i class="fas fa-info-circle"></i> Esta acción no se puede deshacer.</p>
            </div>
            <div class="form-actions">
                <button class="btn-secondary" onclick="cerrarModal('modalEliminar')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="btn-danger" onclick="eliminarVacuna()">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>

    <script src="../../Assets/Js/FormsVacuna.js"></script>
    <script>
        const tabla = document.getElementById('tablaVacunas');
        const searchInput = document.getElementById('searchInput');
        
        // Búsqueda en tiempo real
        if (searchInput && tabla) {
            searchInput.addEventListener('input', function() {
                const busqueda = this.value.toLowerCase();
                const filas = tabla.querySelectorAll('tbody tr');
                
                filas.forEach(fila => {
                    const texto = fila.textContent.toLowerCase();
                    fila.style.display = texto.includes(busqueda) ? '' : 'none';
                });
            });
        }
        
        async function verDetalle(id) {
            const modal = document.getElementById('modalDetalle');
            const content = document.getElementById('detalleContent');
            
            content.innerHTML = `
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando información...</p>
                </div>
            `;
            modal.style.display = 'flex';
            
            try {
                const response = await fetch(`Procesar.php?action=detalle&id=${id}`);
                const data = await response.json();
                
                if (data.success) {
                    const v = data.vacuna;
                    content.innerHTML = `
                        <div class="detail-grid">
                            <div class="detail-section">
                                <h3><i class="fas fa-syringe"></i> Información de la Vacuna</h3>
                                <p><strong>Vacuna:</strong> ${v.nombre_vacuna}</p>
                                <p><strong>Laboratorio:</strong> ${v.laboratorio}</p>
                                ${v.lote ? `<p><strong>Lote:</strong> ${v.lote}</p>` : ''}
                                ${v.dosis ? `<p><strong>Dosis:</strong> ${v.dosis}</p>` : ''}
                                <p><strong>Vía:</strong> ${v.via_administracion}</p>
                            </div>
                            
                            <div class="detail-section">
                                <h3><i class="fas fa-paw"></i> Paciente</h3>
                                <p><strong>Mascota:</strong> ${v.mascota_nombre}</p>
                                <p><strong>Especie:</strong> ${v.especie}</p>
                                <p><strong>Raza:</strong> ${v.raza || 'No especificada'}</p>
                                <p><strong>Dueño:</strong> ${v.dueno_nombre}</p>
                            </div>
                            
                            <div class="detail-section">
                                <h3><i class="fas fa-calendar-alt"></i> Fechas</h3>
                                <p><strong>Aplicación:</strong> ${v.fecha_aplicacion}</p>
                                ${v.proxima_aplicacion ? `<p><strong>Próxima dosis:</strong> ${v.proxima_aplicacion}</p>` : ''}
                                <p><strong>Veterinario:</strong> ${v.veterinario_nombre}</p>
                            </div>
                            
                            ${v.observaciones ? `
                            <div class="detail-section full-width">
                                <h3><i class="fas fa-notes-medical"></i> Observaciones</h3>
                                <p>${v.observaciones}</p>
                            </div>
                            ` : ''}
                        </div>
                    `;
                } else {
                    content.innerHTML = `<div class="error">${data.message}</div>`;
                }
            } catch (error) {
                content.innerHTML = `<div class="error">Error al cargar la información</div>`;
            }
        }
        
        let idEliminar = null;
        
        function confirmarEliminar(id, nombre) {
            idEliminar = id;
            document.getElementById('nombreEliminar').textContent = nombre;
            document.getElementById('modalEliminar').style.display = 'flex';
        }
        
        async function eliminarVacuna() {
            if (!idEliminar) return;
            
            try {
                const response = await fetch('Procesar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=eliminar&id_vacuna=${idEliminar}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✓ Vacuna eliminada correctamente');
                    window.location.reload();
                } else {
                    alert('✗ Error: ' + data.message);
                }
            } catch (error) {
                alert('✗ Error al eliminar la vacuna');
            }
            
            cerrarModal('modalEliminar');
        }
        
        function cerrarModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            idEliminar = null;
        }
        
        // Cerrar modal al hacer clic fuera
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    cerrarModal(modal.id);
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>