<?php
/**
 * LISTAR CARGOS
 * Visualización y gestión de cargos del sistema
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';
$conn = getConnection();

// Obtener todos los cargos con el número de empleados
$query = "SELECT 
    c.id_cargo,
    c.nombre_cargo,
    c.descripcion,
    c.estado,
    COUNT(e.id_empleado) as total_empleados,
    COUNT(CASE WHEN e.estado = 'activo' THEN 1 END) as empleados_activos
FROM cargo c
LEFT JOIN empleado e ON c.id_cargo = e.id_cargo
GROUP BY c.id_cargo
ORDER BY c.estado DESC, c.nombre_cargo ASC";

$resultado = $conn->query($query);
$total_cargos = $resultado->num_rows;

// Calcular estadísticas
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN estado = 'Activo' THEN 1 ELSE 0 END) as activos,
    SUM(CASE WHEN estado = 'Inactivo' THEN 1 ELSE 0 END) as inactivos
FROM cargo";
$stats = $conn->query($stats_query)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargos - VetCare Plus</title>
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
                    <i class="fas fa-users-cog"></i>
                    Gestión de Cargos
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
                <span class="current">Cargos</span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <i class="fas fa-user-tag"></i>
                    Listado de Cargos
                </h1>
                <p>Administra los roles y posiciones del personal</p>
            </div>

            <!-- Search Header -->
            <div class="table-header">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por nombre o descripción...">
                </div>
                <a href="Registrar.php" class="btn-primary">
                    <i class="fas fa-plus"></i>
                    Nuevo Cargo
                </a>
            </div>

            <!-- Estadísticas -->
            <div class="stats-cards">
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total']); ?></h3>
                        <p>Total Cargos</p>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['activos']); ?></h3>
                        <p>Cargos Activos</p>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-icon">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['inactivos']); ?></h3>
                        <p>Cargos Inactivos</p>
                    </div>
                </div>
            </div>

            <!-- Tabla de Cargos -->
            <div class="table-container">
                <table class="data-table" id="tablaCargos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Cargo</th>
                            <th>Descripción</th>
                            <th>Empleados</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_cargos > 0): ?>
                            <?php while($cargo = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($cargo['id_cargo'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cargo['nombre_cargo']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($cargo['descripcion']): ?>
                                            <span class="text-truncate" title="<?php echo htmlspecialchars($cargo['descripcion']); ?>">
                                                <?php echo htmlspecialchars($cargo['descripcion']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin descripción</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($cargo['total_empleados'] > 0): ?>
                                            <span class="badge badge-info">
                                                <i class="fas fa-users"></i>
                                                <?php echo $cargo['empleados_activos']; ?> / <?php echo $cargo['total_empleados']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin empleados</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($cargo['estado'] == 'Activo'): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle"></i>
                                                Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times-circle"></i>
                                                Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="actions">
                                            <button class="btn-icon btn-view" 
                                                    onclick="verDetalle(<?php echo $cargo['id_cargo']; ?>)"
                                                    title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="Editar.php?id=<?php echo $cargo['id_cargo']; ?>" 
                                               class="btn-icon btn-edit"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($cargo['total_empleados'] == 0): ?>
                                                <button class="btn-icon btn-delete" 
                                                        onclick="confirmarEliminar(<?php echo $cargo['id_cargo']; ?>, '<?php echo addslashes($cargo['nombre_cargo']); ?>')"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-icon btn-delete" 
                                                        onclick="alert('No se puede eliminar este cargo porque tiene empleados asignados')"
                                                        title="No se puede eliminar"
                                                        style="opacity: 0.5; cursor: not-allowed;">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-user-tag"></i>
                                        <p>No hay cargos registrados</p>
                                        <a href="Registrar.php" class="btn-primary">
                                            <i class="fas fa-plus"></i>
                                            Registrar Primer Cargo
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
                <h2><i class="fas fa-user-tag"></i> Detalle del Cargo</h2>
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
                <p>¿Estás seguro de que deseas eliminar el cargo <strong id="nombreEliminar"></strong>?</p>
                <p class="text-muted"><i class="fas fa-info-circle"></i> Esta acción no se puede deshacer.</p>
            </div>
            <div class="form-actions">
                <button class="btn-secondary" onclick="cerrarModal('modalEliminar')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="btn-danger" onclick="eliminarCargo()">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>

    <script src="../../Assets/Js/FormsCargo.js"></script>
    <script>
        const tabla = document.getElementById('tablaCargos');
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
                    const c = data.cargo;
                    
                    let empleadosHTML = '';
                    if (c.empleados && c.empleados.length > 0) {
                        empleadosHTML = `
                            <div class="detail-section full-width">
                                <h3><i class="fas fa-users"></i> Empleados Asignados (${c.empleados.length})</h3>
                                <ul style="list-style: none; padding: 0;">
                                    ${c.empleados.map(emp => `
                                        <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                                            <strong>${emp.nombre_completo}</strong> 
                                            <br><small class="text-muted">
                                                <i class="fas fa-envelope"></i> ${emp.correo_electronico} | 
                                                <i class="fas fa-calendar"></i> Desde ${new Date(emp.fecha_contratacion).toLocaleDateString('es-CO')} | 
                                                <span class="badge ${emp.estado === 'activo' ? 'badge-success' : 'badge-danger'}">${emp.estado}</span>
                                            </small>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                        `;
                    }
                    
                    content.innerHTML = `
                        <div class="detail-grid">
                            <div class="detail-section">
                                <h3><i class="fas fa-briefcase"></i> Información del Cargo</h3>
                                <p><strong>Nombre:</strong> ${c.nombre_cargo}</p>
                                <p><strong>Estado:</strong> 
                                    <span class="badge ${c.estado === 'Activo' ? 'badge-success' : 'badge-danger'}">
                                        ${c.estado}
                                    </span>
                                </p>
                                <p><strong>Total Empleados:</strong> ${c.total_empleados}</p>
                            </div>
                            
                            ${c.descripcion ? `
                            <div class="detail-section full-width">
                                <h3><i class="fas fa-file-alt"></i> Descripción</h3>
                                <p>${c.descripcion}</p>
                            </div>
                            ` : ''}
                            
                            ${empleadosHTML}
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
        
        async function eliminarCargo() {
            if (!idEliminar) return;
            
            try {
                const response = await fetch('Procesar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=eliminar&id_cargo=${idEliminar}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✓ Cargo eliminado correctamente');
                    window.location.reload();
                } else {
                    alert('✗ Error: ' + data.message);
                }
            } catch (error) {
                alert('✗ Error al eliminar el cargo');
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