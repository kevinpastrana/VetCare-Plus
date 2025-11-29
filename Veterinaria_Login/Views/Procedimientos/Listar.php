<?php
/**
 * LISTAR PROCEDIMIENTOS
 * Visualización y gestión de procedimientos médicos
 * VERSIÓN CORREGIDA - Badges con gradientes hermosos
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';
$conn = getConnection();

// Obtener todos los procedimientos
$query = "SELECT 
    p.id_procedimiento,
    p.nombre,
    p.tipo,
    p.fecha_procedimiento,
    p.costo,
    p.estado,
    p.esterilizacion,
    m.nombre AS mascota_nombre,
    m.especie,
    CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno_nombre,
    CONCAT(e.primer_nombre, ' ', e.primer_apellido) AS veterinario_nombre
FROM procedimiento p
INNER JOIN mascota m ON p.id_mascota = m.id_mascota
INNER JOIN dueno d ON m.id_dueno = d.id_dueno
INNER JOIN empleado e ON p.id_empleado = e.id_empleado
ORDER BY p.fecha_procedimiento DESC, p.id_procedimiento DESC";

$resultado = $conn->query($query);
$total_procedimientos = $resultado->num_rows;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procedimientos - VetCare Plus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">        
    <link rel="stylesheet" href="../../Assets/Css/Dashboard.css">
    <link rel="stylesheet" href="../../Assets/Css/Tables.css">
    <link rel="stylesheet" href="../../Assets/Css/TablesProcedimientos.css">
    <link rel="stylesheet" href="../../Assets/Css/Forms.css">
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
                    <i class="fas fa-syringe"></i>
                    Gestión de Procedimientos
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
        <div class="container-fluid">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="../../Dashboard.php"><i class="fas fa-home"></i> Inicio</a>
                <span class="separator">/</span>
                <span class="current">Procedimientos</span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <div class="header-left">
                    <h2>
                        <i class="fas fa-syringe"></i>
                        Listado de Procedimientos
                    </h2>
                    <p>Administra todos los procedimientos médicos realizados</p>
                </div>
                <div class="header-right">
                    <a href="Registrar.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Nuevo Procedimiento
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters-card">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por procedimiento, mascota, dueño...">
                </div>
                <div class="filter-actions">
                    <button class="btn btn-outline" onclick="exportarExcel()">
                        <i class="fas fa-file-excel"></i>
                        Exportar
                    </button>
                    <button class="btn btn-outline" onclick="imprimirTabla()">
                        <i class="fas fa-print"></i>
                        Imprimir
                    </button>
                </div>
            </div>

            <!-- Tabla de Procedimientos -->
            <div class="table-card">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-list"></i>
                        Total de Registros: <span id="totalRegistros"><?php echo $total_procedimientos; ?></span>
                    </div>
                    <div class="table-pagination">
                        <button class="btn-pagination" onclick="cambiarPagina(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="pagination-info">
                            Página <span id="paginaActual">1</span> de <span id="totalPaginas">1</span>
                        </span>
                        <button class="btn-pagination" onclick="cambiarPagina(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="tablaProcedimientos">
                        <thead>
                            <tr>
                                <th onclick="ordenarTabla(0)">ID <i class="fas fa-sort"></i></th>
                                <th onclick="ordenarTabla(1)">Procedimiento <i class="fas fa-sort"></i></th>
                                <th onclick="ordenarTabla(2)">Tipo <i class="fas fa-sort"></i></th>
                                <th onclick="ordenarTabla(3)">Mascota <i class="fas fa-sort"></i></th>
                                <th onclick="ordenarTabla(4)">Dueño <i class="fas fa-sort"></i></th>
                                <th onclick="ordenarTabla(5)">Veterinario <i class="fas fa-sort"></i></th>
                                <th onclick="ordenarTabla(6)">Fecha <i class="fas fa-sort"></i></th>
                                <th onclick="ordenarTabla(7)">Costo <i class="fas fa-sort"></i></th>
                                <th onclick="ordenarTabla(8)">Estado <i class="fas fa-sort"></i></th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_procedimientos > 0): ?>
                                <?php while($proc = $resultado->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?php echo str_pad($proc['id_procedimiento'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($proc['nombre']); ?></strong>
                                            <?php if ($proc['esterilizacion']): ?>
                                                <br><span class="badge badge-info"><i class="fas fa-check-circle"></i> Incluye esterilización</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $tipo_icons = [
                                                'Cirugía' => 'fa-scalpel',
                                                'Tratamiento' => 'fa-pills',
                                                'Diagnóstico' => 'fa-stethoscope',
                                                'Preventivo' => 'fa-shield-alt'
                                            ];
                                            $icon = $tipo_icons[$proc['tipo']] ?? 'fa-syringe';
                                            ?>
                                            <i class="fas <?php echo $icon; ?>"></i>
                                            <?php echo htmlspecialchars($proc['tipo']); ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($proc['mascota_nombre']); ?></strong>
                                            <br><small class="text-secondary"><?php echo htmlspecialchars($proc['especie']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($proc['dueno_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($proc['veterinario_nombre']); ?></td>
                                        <td>
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($proc['fecha_procedimiento'])); ?>
                                        </td>
                                        <td>
                                            <?php if ($proc['costo']): ?>
                                                <strong class="text-success">$<?php echo number_format($proc['costo'], 0, ',', '.'); ?></strong>
                                            <?php else: ?>
                                                <span class="text-secondary">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            // CORRECCIÓN: Badges con las clases correctas y hermosas
                                            $estados_badges = [
                                                'Programado' => ['class' => 'badge-info', 'icon' => 'fa-clock'],
                                                'En Proceso' => ['class' => 'badge-blue', 'icon' => 'fa-spinner'],
                                                'Completado' => ['class' => 'badge-success', 'icon' => 'fa-check-circle'],
                                                'Cancelado' => ['class' => 'badge-danger', 'icon' => 'fa-times-circle']
                                            ];
                                            $badge_info = $estados_badges[$proc['estado']] ?? ['class' => 'badge-gray', 'icon' => 'fa-question'];
                                            ?>
                                            <span class="badge <?php echo $badge_info['class']; ?>">
                                                <i class="fas <?php echo $badge_info['icon']; ?>"></i>
                                                <?php echo htmlspecialchars($proc['estado']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="action-buttons">
                                                <button class="btn-action btn-view" 
                                                        onclick="verDetalle(<?php echo $proc['id_procedimiento']; ?>)"
                                                        title="Ver detalle">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="Editar.php?id=<?php echo $proc['id_procedimiento']; ?>" 
                                                   class="btn-action btn-edit"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn-action btn-delete" 
                                                        onclick="confirmarEliminar(<?php echo $proc['id_procedimiento']; ?>, '<?php echo addslashes($proc['nombre']); ?>')"
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
                                            <i class="fas fa-syringe"></i>
                                            <p>No hay procedimientos registrados</p>
                                            <a href="Registrar.php" class="btn btn-primary">
                                                <i class="fas fa-plus"></i>
                                                Registrar Primer Procedimiento
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Ver Detalle -->
    <div id="modalDetalle" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3><i class="fas fa-syringe"></i> Detalle del Procedimiento</h3>
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
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
                <button class="btn-close" onclick="cerrarModal('modalEliminar')">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el procedimiento <strong id="nombreEliminar"></strong>?</p>
                <p class="text-danger"><i class="fas fa-warning"></i> Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModal('modalEliminar')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="btn btn-danger" onclick="eliminarProcedimiento()">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>

    <script src="../../Assets/Js/Tables.js"></script>
    <script>
        // Adaptar funciones específicas para procedimientos
        const tabla = document.getElementById('tablaProcedimientos');
        
        async function verDetalle(id) {
            const modal = document.getElementById('modalDetalle');
            const content = document.getElementById('detalleContent');
            
            content.innerHTML = `
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando información...</p>
                </div>
            `;
            modal.classList.add('show');
            
            try {
                const response = await fetch(`Procesar.php?action=detalle&id=${id}`);
                const data = await response.json();
                
                if (data.success) {
                    const p = data.procedimiento;
                    
                    // Determinar badge del estado
                    const estadosBadges = {
                        'Programado': 'badge-info',
                        'En Proceso': 'badge-blue',
                        'Completado': 'badge-success',
                        'Cancelado': 'badge-danger'
                    };
                    const badgeClass = estadosBadges[p.estado] || 'badge-gray';
                    
                    content.innerHTML = `
                        <div class="detalle-grid">
                            <div class="detalle-section">
                                <h4><i class="fas fa-info-circle"></i> Información General</h4>
                                <p><strong>Nombre:</strong> ${p.nombre}</p>
                                <p><strong>Tipo:</strong> ${p.tipo}</p>
                                <p><strong>Fecha:</strong> ${p.fecha_procedimiento}</p>
                                <p><strong>Estado:</strong> <span class="badge ${badgeClass}">${p.estado}</span></p>
                                <p><strong>Esterilización:</strong> ${p.esterilizacion ? '<span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>' : '<span class="badge badge-gray">No</span>'}</p>
                                ${p.costo ? `<p><strong>Costo:</strong> <span class="text-success">$${parseFloat(p.costo).toLocaleString('es-CO')}</span></p>` : ''}
                            </div>
                            
                            <div class="detalle-section">
                                <h4><i class="fas fa-paw"></i> Información del Paciente</h4>
                                <p><strong>Mascota:</strong> ${p.mascota_nombre}</p>
                                <p><strong>Especie:</strong> ${p.especie}</p>
                                <p><strong>Raza:</strong> ${p.raza || 'No especificada'}</p>
                                <p><strong>Dueño:</strong> ${p.dueno_nombre}</p>
                                <p><strong>Veterinario:</strong> ${p.veterinario_nombre}</p>
                            </div>
                        </div>
                        
                        ${p.descripcion ? `
                        <div class="detalle-section" style="margin-top: 1.5rem;">
                            <h4><i class="fas fa-file-medical-alt"></i> Descripción</h4>
                            <p>${p.descripcion}</p>
                        </div>
                        ` : ''}
                        
                        ${p.diagnostico ? `
                        <div class="detalle-section" style="margin-top: 1.5rem;">
                            <h4><i class="fas fa-stethoscope"></i> Diagnóstico</h4>
                            <p>${p.diagnostico}</p>
                        </div>
                        ` : ''}
                        
                        ${p.patologia ? `
                        <div class="detalle-section" style="margin-top: 1.5rem;">
                            <h4><i class="fas fa-disease"></i> Patología</h4>
                            <p>${p.patologia}</p>
                        </div>
                        ` : ''}
                    `;
                } else {
                    content.innerHTML = `
                        <div style="background: #fee2e2; padding: 1.5rem; border-radius: 12px; color: #991b1b;">
                            <i class="fas fa-exclamation-circle"></i>
                            ${data.message}
                        </div>
                    `;
                }
            } catch (error) {
                content.innerHTML = `
                    <div style="background: #fee2e2; padding: 1.5rem; border-radius: 12px; color: #991b1b;">
                        <i class="fas fa-times-circle"></i>
                        Error al cargar la información
                    </div>
                `;
            }
        }
        
        let idEliminar = null;
        
        function confirmarEliminar(id, nombre) {
            idEliminar = id;
            document.getElementById('nombreEliminar').textContent = nombre;
            document.getElementById('modalEliminar').classList.add('show');
        }
        
        async function eliminarProcedimiento() {
            if (!idEliminar) return;
            
            try {
                const response = await fetch('Procesar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=eliminar&id_procedimiento=${idEliminar}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✓ Procedimiento eliminado correctamente');
                    window.location.reload();
                } else {
                    alert('✗ Error: ' + data.message);
                }
            } catch (error) {
                alert('✗ Error al eliminar el procedimiento');
            }
            
            cerrarModal('modalEliminar');
        }
        
        function cerrarModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('show');
            }
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
        
        // Cerrar con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.show').forEach(modal => {
                    cerrarModal(modal.id);
                });
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>