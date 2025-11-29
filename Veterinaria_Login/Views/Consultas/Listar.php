<?php
/**
 * LISTAR CONSULTAS
 * Muestra tabla con todas las consultas registradas
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Obtener todas las consultas con información de mascota y empleado
$conn = getConnection();
$query = "SELECT 
    c.id_consulta,
    c.fecha_consulta,
    c.hora_consulta,
    c.motivo,
    c.sintomas,
    c.diagnostico,
    c.tratamiento,
    c.observaciones,
    c.proxima_cita,
    c.estado,
    m.nombre AS nombre_mascota,
    m.especie,
    d.primer_nombre AS dueno_nombre,
    d.primer_apellido AS dueno_apellido,
    e.primer_nombre AS veterinario_nombre,
    e.primer_apellido AS veterinario_apellido
FROM consulta c
INNER JOIN mascota m ON c.id_mascota = m.id_mascota
INNER JOIN dueno d ON m.id_dueno = d.id_dueno
INNER JOIN empleado e ON c.id_empleado = e.id_empleado
ORDER BY c.fecha_consulta DESC, c.hora_consulta DESC";

$consultas = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultas - VetCare Plus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../Assets/Css/Dashboard.css">
    <link rel="stylesheet" href="../../Assets/Css/FormsConsultas.css">
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
                    <i class="fas fa-stethoscope"></i>
                    Gestión de Consultas
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
                <span class="current">Consultas</span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <i class="fas fa-stethoscope"></i>
                    Listado de Consultas
                </h1>
                <p>Gestiona el historial médico y las consultas veterinarias</p>
            </div>

            <!-- Header con búsqueda y botón -->
            <div class="table-header">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar consultas..." onkeyup="filtrarTabla()">
                </div>
                <a href="Registrar.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Nueva Consulta
                </a>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="stats-cards">
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $consultas->num_rows; ?></h3>
                        <p>Total Consultas</p>
                    </div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?php 
                            $hoy = date('Y-m-d');
                            $consultas_hoy = $conn->query("SELECT COUNT(*) as total FROM consulta WHERE fecha_consulta = '$hoy'");
                            echo $consultas_hoy->fetch_assoc()['total'];
                            ?>
                        </h3>
                        <p>Consultas Hoy</p>
                    </div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?php 
                            $proximas = $conn->query("SELECT COUNT(*) as total FROM consulta WHERE proxima_cita >= CURDATE() AND estado = 'Completada'");
                            echo $proximas->fetch_assoc()['total'];
                            ?>
                        </h3>
                        <p>Próximas Citas</p>
                    </div>
                </div>
            </div>

            <!-- Tabla de consultas -->
            <div class="table-container">
                <table class="data-table" id="consultasTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Mascota</th>
                            <th>Dueño</th>
                            <th>Veterinario</th>
                            <th>Motivo</th>
                            <th>Diagnóstico</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($consultas && $consultas->num_rows > 0): ?>
                            <?php while($consulta = $consultas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $consulta['id_consulta']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($consulta['fecha_consulta'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($consulta['hora_consulta'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($consulta['nombre_mascota']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($consulta['especie']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($consulta['dueno_nombre'] . ' ' . $consulta['dueno_apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($consulta['veterinario_nombre'] . ' ' . $consulta['veterinario_apellido']); ?></td>
                                    <td>
                                        <span class="text-truncate" title="<?php echo htmlspecialchars($consulta['motivo']); ?>">
                                            <?php echo htmlspecialchars(substr($consulta['motivo'], 0, 30)) . (strlen($consulta['motivo']) > 30 ? '...' : ''); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-truncate" title="<?php echo htmlspecialchars($consulta['diagnostico']); ?>">
                                            <?php echo htmlspecialchars(substr($consulta['diagnostico'], 0, 30)) . (strlen($consulta['diagnostico']) > 30 ? '...' : ''); ?>
                                        </span>
                                    </td>
                                    <td>
<span class="badge badge-<?php 
    echo $consulta['estado'] == 'Completada' ? 'success' : 
        ($consulta['estado'] == 'Pendiente' ? 'warning' : 'danger'); 
?>">
    <?php echo htmlspecialchars($consulta['estado']); ?>
</span>
                                    </td>
                                    <td class="actions">
                                        <button class="btn-icon btn-view" onclick="verDetalle(<?php echo $consulta['id_consulta']; ?>)" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="Editar.php?id=<?php echo $consulta['id_consulta']; ?>" class="btn-icon btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn-icon btn-delete" onclick="eliminarConsulta(<?php echo $consulta['id_consulta']; ?>)" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-stethoscope"></i>
                                        <p>No hay consultas registradas</p>
                                        <a href="Registrar.php" class="btn btn-primary">
                                            <i class="fas fa-plus"></i>
                                            Registrar Primera Consulta
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

    <!-- Modal de detalles -->
    <div id="modalDetalle" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2><i class="fas fa-file-medical"></i> Detalles de la Consulta</h2>
                <button class="btn-close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body" id="detalleContent">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    Cargando...
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función de búsqueda en tiempo real
        function filtrarTabla() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('consultasTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const row = tr[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell) {
                        const textValue = cell.textContent || cell.innerText;
                        if (textValue.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                row.style.display = found ? '' : 'none';
            }
        }

        function verDetalle(id) {
            const modal = document.getElementById('modalDetalle');
            const content = document.getElementById('detalleContent');
            
            modal.style.display = 'flex';
            content.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
            
            fetch(`Procesar.php?action=detalle&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const c = data.consulta;
                        content.innerHTML = `
                            <div class="detail-grid">
                                <div class="detail-section">
                                    <h3><i class="fas fa-info-circle"></i> Información General</h3>
                                    <p><strong>Fecha:</strong> ${c.fecha_consulta}</p>
                                    <p><strong>Hora:</strong> ${c.hora_consulta}</p>
                                    <p><strong>Estado:</strong> <span class="badge badge-${c.estado == 'Completada' ? 'success' : 'warning'}">${c.estado}</span></p>
                                </div>
                                
                                <div class="detail-section">
                                    <h3><i class="fas fa-paw"></i> Mascota</h3>
                                    <p><strong>Nombre:</strong> ${c.nombre_mascota}</p>
                                    <p><strong>Especie:</strong> ${c.especie}</p>
                                    <p><strong>Dueño:</strong> ${c.dueno_nombre}</p>
                                </div>
                                
                                <div class="detail-section">
                                    <h3><i class="fas fa-user-md"></i> Veterinario</h3>
                                    <p>${c.veterinario_nombre}</p>
                                </div>
                                
                                <div class="detail-section full-width">
                                    <h3><i class="fas fa-clipboard"></i> Motivo de Consulta</h3>
                                    <p>${c.motivo}</p>
                                </div>
                                
                                <div class="detail-section full-width">
                                    <h3><i class="fas fa-heartbeat"></i> Síntomas</h3>
                                    <p>${c.sintomas || 'No especificados'}</p>
                                </div>
                                
                                <div class="detail-section full-width">
                                    <h3><i class="fas fa-diagnoses"></i> Diagnóstico</h3>
                                    <p>${c.diagnostico || 'No especificado'}</p>
                                </div>
                                
                                <div class="detail-section full-width">
                                    <h3><i class="fas fa-pills"></i> Tratamiento</h3>
                                    <p>${c.tratamiento || 'No especificado'}</p>
                                </div>
                                
                                <div class="detail-section full-width">
                                    <h3><i class="fas fa-notes-medical"></i> Observaciones</h3>
                                    <p>${c.observaciones || 'Sin observaciones'}</p>
                                </div>
                                
                                ${c.proxima_cita ? `
                                <div class="detail-section">
                                    <h3><i class="fas fa-calendar-check"></i> Próxima Cita</h3>
                                    <p>${c.proxima_cita}</p>
                                </div>
                                ` : ''}
                            </div>
                        `;
                    } else {
                        content.innerHTML = '<div class="error">Error al cargar los detalles</div>';
                    }
                })
                .catch(error => {
                    content.innerHTML = '<div class="error">Error de conexión</div>';
                });
        }
        
        function cerrarModal() {
            document.getElementById('modalDetalle').style.display = 'none';
        }
        
        function eliminarConsulta(id) {
            if (confirm('¿Estás seguro de eliminar esta consulta? Esta acción no se puede deshacer.')) {
                fetch('Procesar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=eliminar&id_consulta=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error de conexión');
                });
            }
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('modalDetalle');
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>