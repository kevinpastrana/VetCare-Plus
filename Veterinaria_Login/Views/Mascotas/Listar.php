<?php
/**
 * LISTAR MASCOTAS
 * Muestra tabla con todas las mascotas registradas
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Obtener mascotas de la base de datos con información del dueño
$conn = getConnection();
$query = "SELECT m.*, 
          CONCAT(d.primer_nombre, ' ', d.primer_apellido) as nombre_dueno,
          d.telefono as telefono_dueno
          FROM mascota m
          LEFT JOIN dueno d ON m.id_dueno = d.id_dueno
          ORDER BY m.nombre";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Mascotas - VetCare Plus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../Assets/Css/Dashboard.css">
    <link rel="stylesheet" href="../../Assets/Css/Tables.css">
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
                    <i class="fas fa-paw"></i>
                    Listado de Mascotas
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
                <span class="current">Mascotas</span>
            </div>

            <!-- Header con acciones -->
            <div class="page-header">
                <div class="header-left">
                    <h2>
                        <i class="fas fa-paw"></i>
                        Listado de Mascotas
                    </h2>
                    <p>Gestiona la información de las mascotas registradas</p>
                </div>
                <div class="header-right">
                    <a href="registrar.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Nueva Mascota
                    </a>
                </div>
            </div>

            <!-- Filtros y búsqueda -->
            <div class="filters-card">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Buscar por nombre, especie, raza o dueño..."
                    >
                </div>
                <div class="filter-actions">
                    <button class="btn btn-outline" onclick="exportarExcel()">
                        <i class="fas fa-file-excel"></i>
                        Exportar Excel
                    </button>
                    <button class="btn btn-outline" onclick="imprimirTabla()">
                        <i class="fas fa-print"></i>
                        Imprimir
                    </button>
                </div>
            </div>

            <!-- Tabla de mascotas -->
            <div class="table-card">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-table"></i>
                        <span id="totalRegistros"><?php echo $result->num_rows; ?></span> Mascotas Registradas
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
                    <table id="tablaMascotas" class="data-table">
                        <thead>
                            <tr>
                                <th onclick="ordenarTabla(0)">
                                    ID
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th onclick="ordenarTabla(1)">
                                    Nombre
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th onclick="ordenarTabla(2)">
                                    Especie
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th>Raza</th>
                                <th>Género</th>
                                <th>Edad</th>
                                <th>Dueño</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): 
                                    // Calcular edad
                                    $edad = '';
                                    if ($row['fecha_nacimiento']) {
                                        $nacimiento = new DateTime($row['fecha_nacimiento']);
                                        $hoy = new DateTime();
                                        $diff = $hoy->diff($nacimiento);
                                        if ($diff->y > 0) {
                                            $edad = $diff->y . ' año' . ($diff->y > 1 ? 's' : '');
                                        } else if ($diff->m > 0) {
                                            $edad = $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '');
                                        } else {
                                            $edad = $diff->d . ' día' . ($diff->d > 1 ? 's' : '');
                                        }
                                    }
                                ?>
                                <tr>
                                    <td><span class="badge badge-info">#<?php echo $row['id_mascota']; ?></span></td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">
                                                <?php 
                                                $inicial = strtoupper(substr($row['nombre'], 0, 1));
                                                echo $inicial; 
                                                ?>
                                            </div>
                                            <div class="user-info">
                                                <strong><?php echo htmlspecialchars($row['nombre']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-blue">
                                            <?php echo htmlspecialchars($row['especie']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['raza']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['genero'] == 'Macho' ? 'blue' : 'pink'; ?>">
                                            <?php echo htmlspecialchars($row['genero']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $edad; ?></td>
                                    <td>
                                        <i class="fas fa-user text-primary"></i>
                                        <?php echo htmlspecialchars($row['nombre_dueno']); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $row['estado'] == 'Activo' ? 'blue' : 
                                                 ($row['estado'] == 'Inactivo' ? 'gray' : 'pink'); 
                                        ?>">
                                            <?php echo htmlspecialchars($row['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button 
                                                class="btn-action btn-view" 
                                                onclick="verDetalle(<?php echo $row['id_mascota']; ?>)"
                                                title="Ver detalle"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a 
                                                href="editar.php?id=<?php echo $row['id_mascota']; ?>" 
                                                class="btn-action btn-edit"
                                                title="Editar"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button 
                                                class="btn-action btn-delete" 
                                                onclick="confirmarEliminar(<?php echo $row['id_mascota']; ?>, '<?php echo htmlspecialchars($row['nombre']); ?>')"
                                                title="Eliminar"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-paw"></i>
                                            <p>No hay mascotas registradas</p>
                                            <a href="registrar.php" class="btn btn-primary">
                                                <i class="fas fa-plus"></i>
                                                Registrar Primera Mascota
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
                <h3><i class="fas fa-paw"></i> Detalle de la Mascota</h3>
                <button class="btn-close" onclick="cerrarModal('modalDetalle')">&times;</button>
            </div>
            <div class="modal-body" id="detalleContent">
                <!-- Se llenará dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Eliminar -->
    <div id="modalEliminar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación</h3>
                <button class="btn-close" onclick="cerrarModal('modalEliminar')">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de eliminar a la mascota <strong id="nombreEliminar"></strong>?</p>
                <p class="text-warning">
                    <i class="fas fa-exclamation-circle"></i>
                    Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModal('modalEliminar')">Cancelar</button>
                <button class="btn btn-danger" onclick="eliminarMascota()">
                    <i class="fas fa-trash"></i>
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <script src="../../Assets/Js/Tables.js"></script>
</body>
</html>
<?php $conn->close(); ?>