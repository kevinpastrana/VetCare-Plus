<?php
/**
 * LISTAR FACTURAS
 * Muestra tabla con todas las facturas registradas
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Obtener todas las facturas con información relacionada
$conn = getConnection();
$query = "SELECT 
    f.id_factura,
    f.numero_factura,
    f.fecha_factura,
    f.fecha_vencimiento,
    f.metodo_pago,
    f.subtotal,
    f.descuento,
    f.impuesto,
    f.total,
    f.pagado,
    f.saldo,
    f.estado,
    d.primer_nombre AS dueno_nombre,
    d.primer_apellido AS dueno_apellido,
    d.cedula AS dueno_cedula,
    c.id_consulta
FROM factura f
INNER JOIN dueno d ON f.id_dueno = d.id_dueno
LEFT JOIN consulta c ON f.id_consulta = c.id_consulta
ORDER BY f.fecha_factura DESC, f.id_factura DESC";

$facturas = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturas - VetCare Plus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../Assets/Css/Dashboard.css">
    <link rel="stylesheet" href="../../Assets/Css/FormsFacturas.css">
    <link rel="js" href="../../Assets/Js/FormsFacturas.js">
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
                    <i class="fas fa-file-invoice-dollar"></i>
                    Gestión de Facturas
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
                <span class="current">Facturas</span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <i class="fas fa-file-invoice"></i>
                    Listado de Facturas
                </h1>
                <p>Administra las facturas y pagos del sistema</p>
            </div>

            <!-- Header con búsqueda y botón -->
            <div class="table-header">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por número, cliente..." onkeyup="filtrarTabla()">
                </div>
                <a href="Registrar.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Nueva Factura
                </a>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="stats-cards">
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $facturas->num_rows; ?></h3>
                        <p>Total Facturas</p>
                    </div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?php 
                            $pagadas = $conn->query("SELECT COUNT(*) as total FROM factura WHERE estado = 'Pagada'");
                            echo $pagadas->fetch_assoc()['total'];
                            ?>
                        </h3>
                        <p>Pagadas</p>
                    </div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?php 
                            $pendientes = $conn->query("SELECT COUNT(*) as total FROM factura WHERE estado = 'Pendiente'");
                            echo $pendientes->fetch_assoc()['total'];
                            ?>
                        </h3>
                        <p>Pendientes</p>
                    </div>
                </div>

                <div class="stat-card red">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?php 
                            $total_saldo = $conn->query("SELECT SUM(saldo) as total FROM factura WHERE estado != 'Pagada'");
                            $saldo = $total_saldo->fetch_assoc()['total'] ?? 0;
                            echo '$' . number_format($saldo, 0, ',', '.');
                            ?>
                        </h3>
                        <p>Saldo Pendiente</p>
                    </div>
                </div>
            </div>

            <!-- Tabla de facturas -->
            <div class="table-container">
                <table class="data-table" id="facturasTable">
                    <thead>
                        <tr>
                            <th>N° Factura</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Método Pago</th>
                            <th>Subtotal</th>
                            <th>Descuento</th>
                            <th>Impuesto</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($facturas && $facturas->num_rows > 0): ?>
                            <?php while($factura = $facturas->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($factura['numero_factura']); ?></strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($factura['fecha_factura'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($factura['dueno_nombre'] . ' ' . $factura['dueno_apellido']); ?></strong>
                                        <br>
                                        <small class="text-muted">CC: <?php echo htmlspecialchars($factura['dueno_cedula']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-method">
                                            <i class="fas fa-<?php 
                                                echo $factura['metodo_pago'] == 'Efectivo' ? 'money-bill' : 
                                                    ($factura['metodo_pago'] == 'Tarjeta' ? 'credit-card' : 'exchange-alt'); 
                                            ?>"></i>
                                            <?php echo htmlspecialchars($factura['metodo_pago']); ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($factura['subtotal'], 0, ',', '.'); ?></td>
                                    <td>$<?php echo number_format($factura['descuento'], 0, ',', '.'); ?></td>
                                    <td>$<?php echo number_format($factura['impuesto'], 0, ',', '.'); ?></td>
                                    <td><strong class="text-primary">$<?php echo number_format($factura['total'], 0, ',', '.'); ?></strong></td>
                                    <td class="text-success">$<?php echo number_format($factura['pagado'], 0, ',', '.'); ?></td>
                                    <td class="text-danger">$<?php echo number_format($factura['saldo'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php 
                                        $estado = $factura['estado'];
                                        $badge_class = 'info';
                                        
                                        switch($estado) {
                                            case 'Pagada':
                                                $badge_class = 'success';
                                                break;
                                            case 'Pendiente':
                                                $badge_class = 'warning';
                                                break;
                                            case 'Vencida':
                                                $badge_class = 'danger';
                                                break;
                                            case 'Anulada':
                                                $badge_class = 'danger';
                                                break;
                                            default:
                                                $badge_class = 'info';
                                        }
                                        ?>
                                        <span class="badge badge-<?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($estado); ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <button class="btn-icon btn-view" onclick="verDetalle(<?php echo $factura['id_factura']; ?>)" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-icon btn-print" onclick="imprimirFactura(<?php echo $factura['id_factura']; ?>)" title="Imprimir">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <a href="Editar.php?id=<?php echo $factura['id_factura']; ?>" class="btn-icon btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn-icon btn-delete" onclick="eliminarFactura(<?php echo $factura['id_factura']; ?>)" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-file-invoice"></i>
                                        <p>No hay facturas registradas</p>
                                        <a href="Registrar.php" class="btn btn-primary">
                                            <i class="fas fa-plus"></i>
                                            Crear Primera Factura
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
                <h2><i class="fas fa-file-invoice"></i> Detalles de la Factura</h2>
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
            const table = document.getElementById('facturasTable');
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
                        const f = data.factura;
                        
                        // Determinar color del badge según estado
                        let badgeClass = 'info';
                        if (f.estado === 'Pagada') badgeClass = 'success';
                        else if (f.estado === 'Pendiente') badgeClass = 'warning';
                        else if (f.estado === 'Vencida' || f.estado === 'Anulada') badgeClass = 'danger';
                        
                        content.innerHTML = `
                            <div class="detail-grid">
                                <div class="detail-section">
                                    <h3><i class="fas fa-info-circle"></i> Información General</h3>
                                    <p><strong>N° Factura:</strong> ${f.numero_factura}</p>
                                    <p><strong>Fecha:</strong> ${f.fecha_factura}</p>
                                    <p><strong>Vencimiento:</strong> ${f.fecha_vencimiento || 'N/A'}</p>
                                    <p><strong>Estado:</strong> <span class="badge badge-${badgeClass}">${f.estado}</span></p>
                                </div>
                                
                                <div class="detail-section">
                                    <h3><i class="fas fa-user"></i> Cliente</h3>
                                    <p><strong>Nombre:</strong> ${f.dueno_nombre}</p>
                                    <p><strong>Cédula:</strong> ${f.dueno_cedula}</p>
                                </div>
                                
                                <div class="detail-section">
                                    <h3><i class="fas fa-credit-card"></i> Método de Pago</h3>
                                    <p>${f.metodo_pago}</p>
                                </div>
                                
                                <div class="detail-section full-width">
                                    <h3><i class="fas fa-calculator"></i> Resumen Financiero</h3>
                                    <div class="financial-summary">
                                        <div class="summary-row">
                                            <span>Subtotal:</span>
                                            <strong>$${parseFloat(f.subtotal).toLocaleString('es-CO')}</strong>
                                        </div>
                                        <div class="summary-row">
                                            <span>Descuento:</span>
                                            <strong class="text-success">-$${parseFloat(f.descuento).toLocaleString('es-CO')}</strong>
                                        </div>
                                        <div class="summary-row">
                                            <span>Impuesto:</span>
                                            <strong>$${parseFloat(f.impuesto).toLocaleString('es-CO')}</strong>
                                        </div>
                                        <div class="summary-row total">
                                            <span>TOTAL:</span>
                                            <strong>$${parseFloat(f.total).toLocaleString('es-CO')}</strong>
                                        </div>
                                        <div class="summary-row">
                                            <span>Pagado:</span>
                                            <strong class="text-success">$${parseFloat(f.pagado).toLocaleString('es-CO')}</strong>
                                        </div>
                                        <div class="summary-row">
                                            <span>Saldo:</span>
                                            <strong class="text-danger">$${parseFloat(f.saldo).toLocaleString('es-CO')}</strong>
                                        </div>
                                    </div>
                                </div>

                                ${f.detalles && f.detalles.length > 0 ? `
                                <div class="detail-section full-width">
                                    <h3><i class="fas fa-list"></i> Detalles de Factura</h3>
                                    <table class="detail-table">
                                        <thead>
                                            <tr>
                                                <th>Concepto</th>
                                                <th>Descripción</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unit.</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${f.detalles.map(d => `
                                                <tr>
                                                    <td>${d.concepto}</td>
                                                    <td>${d.descripcion || '-'}</td>
                                                    <td>${d.cantidad}</td>
                                                    <td>$${parseFloat(d.precio_unitario).toLocaleString('es-CO')}</td>
                                                    <td><strong>$${parseFloat(d.subtotal).toLocaleString('es-CO')}</strong></td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
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

        function imprimirFactura(id) {
            window.open(`Imprimir.php?id=${id}`, '_blank');
        }
        
        function eliminarFactura(id) {
            if (confirm('¿Estás seguro de eliminar esta factura? Esta acción no se puede deshacer.')) {
                fetch('Procesar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=eliminar&id_factura=${id}`
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