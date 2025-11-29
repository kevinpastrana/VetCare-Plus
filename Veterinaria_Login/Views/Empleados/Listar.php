<?php
/**
 * LISTAR EMPLEADOS
 * Muestra tabla con todos los empleados registrados
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Obtener empleados de la base de datos
$conn = getConnection();
$query = "SELECT e.*, c.nombre_cargo 
          FROM empleado e
          LEFT JOIN cargo c ON e.id_cargo = c.id_cargo
          ORDER BY e.primer_nombre, e.primer_apellido";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Empleados - VetCare Plus</title>
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
                    <i class="fas fa-users"></i>
                    Listado de Empleados
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
                <span class="current">Empleados</span>
            </div>

            <!-- Header con acciones -->
            <div class="page-header">
                <div class="header-left">
                    <h2>
                        <i class="fas fa-users"></i>
                        Listado de Empleados
                    </h2>
                    <p>Gestiona la información del personal de la clínica</p>
                </div>
                <div class="header-right">
                    <a href="registrar.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Nuevo Empleado
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
                        placeholder="Buscar por nombre, cédula, correo o teléfono..."
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

            <!-- Tabla de empleados -->
            <div class="table-card">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-table"></i>
                        <span id="totalRegistros"><?php echo $result->num_rows; ?></span> Empleados Registrados
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
                    <table id="tablaEmpleados" class="data-table">
                        <thead>
                            <tr>
                                <th onclick="ordenarTabla(0)">
                                    ID
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th onclick="ordenarTabla(1)">
                                    Nombre Completo
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th onclick="ordenarTabla(2)">
                                    Cédula
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th>Teléfono</th>
                                <th>Correo Electrónico</th>
                                <th>Cargo</th>
                                <th>Fecha Contratación</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="badge badge-info">#<?php echo $row['id_empleado']; ?></span></td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">
                                                <?php 
                                                $inicial = strtoupper(substr($row['primer_nombre'], 0, 1));
                                                echo $inicial; 
                                                ?>
                                            </div>
                                            <div class="user-info">
                                                <strong>
                                                    <?php 
                                                    echo htmlspecialchars($row['primer_nombre'] . ' ' . 
                                                         ($row['segundo_nombre'] ? $row['segundo_nombre'] . ' ' : '') . 
                                                         $row['primer_apellido'] . ' ' . 
                                                         ($row['segundo_apellido'] ?? ''));
                                                    ?>
                                                </strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                                    <td>
                                        <i class="fas fa-phone text-success"></i>
                                        <?php echo htmlspecialchars($row['telefono']); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope text-primary"></i>
                                        <?php echo htmlspecialchars($row['correo_electronico']); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-blue">
                                            <?php echo htmlspecialchars($row['nombre_cargo'] ?? 'Sin cargo'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar text-info"></i>
                                        <?php 
                                        if ($row['fecha_contratacion']) {
                                            $fecha = new DateTime($row['fecha_contratacion']);
                                            echo $fecha->format('d/m/Y');
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $row['estado'] == 'activo' ? 'blue' : 'gray'; 
                                        ?>">
                                            <?php echo ucfirst(htmlspecialchars($row['estado'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button 
                                                class="btn-action btn-view" 
                                                onclick="verDetalle(<?php echo $row['id_empleado']; ?>)"
                                                title="Ver detalle"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a 
                                                href="editar.php?id=<?php echo $row['id_empleado']; ?>" 
                                                class="btn-action btn-edit"
                                                title="Editar"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button 
                                                class="btn-action btn-delete" 
                                                onclick="confirmarEliminar(<?php echo $row['id_empleado']; ?>, '<?php echo htmlspecialchars($row['primer_nombre'] . ' ' . $row['primer_apellido']); ?>')"
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
                                            <i class="fas fa-users"></i>
                                            <p>No hay empleados registrados</p>
                                            <a href="registrar.php" class="btn btn-primary">
                                                <i class="fas fa-plus"></i>
                                                Registrar Primer Empleado
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
                <h3><i class="fas fa-user"></i> Detalle del Empleado</h3>
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
                <p>¿Estás seguro de eliminar al empleado <strong id="nombreEliminar"></strong>?</p>
                <p class="text-warning">
                    <i class="fas fa-exclamation-circle"></i>
                    Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModal('modalEliminar')">Cancelar</button>
                <button class="btn btn-danger" onclick="eliminarEmpleado()">
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