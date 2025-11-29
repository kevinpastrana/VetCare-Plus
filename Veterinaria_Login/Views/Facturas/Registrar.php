<?php
/**
 * REGISTRAR FACTURA
 * Formulario para crear nuevas facturas
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';
$conn = getConnection();

// Obtener dueños para el select
$duenos = $conn->query("SELECT id_dueno, CONCAT(primer_nombre, ' ', primer_apellido) AS nombre_completo, cedula 
                        FROM dueno ORDER BY primer_nombre, primer_apellido");

// Obtener consultas sin facturar
$consultas = $conn->query("SELECT c.id_consulta, c.fecha_consulta, m.nombre AS mascota, 
                           CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno
                           FROM consulta c
                           INNER JOIN mascota m ON c.id_mascota = m.id_mascota
                           INNER JOIN dueno d ON m.id_dueno = d.id_dueno
                           LEFT JOIN factura f ON c.id_consulta = f.id_consulta
                           WHERE f.id_factura IS NULL
                           ORDER BY c.fecha_consulta DESC");

// Generar número de factura automático
$ultimo_numero = $conn->query("SELECT numero_factura FROM factura ORDER BY id_factura DESC LIMIT 1");
$numero_sugerido = 'FAC-0001';
if ($ultimo_numero && $ultimo_numero->num_rows > 0) {
    $ultimo = $ultimo_numero->fetch_assoc()['numero_factura'];
    if (preg_match('/FAC-(\d+)/', $ultimo, $matches)) {
        $siguiente = intval($matches[1]) + 1;
        $numero_sugerido = 'FAC-' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Factura - VetCare Plus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../Assets/Css/Dashboard.css">
    <link rel="stylesheet" href="../../Assets/Css/Forms.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <a href="Listar.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Volver a Facturas
                </a>
            </div>
            <div class="nav-center">
                <h1 class="page-title">
                    <i class="fas fa-file-invoice-dollar"></i>
                    Nueva Factura
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
                <a href="Listar.php">Facturas</a>
                <span class="separator">/</span>
                <span class="current">Nueva Factura</span>
            </div>

            <!-- Alert Info -->
            <div class="alert alert-info">
                <div class="alert-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="alert-content">
                    <h4>Instrucciones para crear una factura</h4>
                    <ul>
                        <li>Complete la información general de la factura</li>
                        <li>Seleccione el cliente (dueño de mascota)</li>
                        <li>Agregue los conceptos y servicios facturados</li>
                        <li>Los cálculos se realizan automáticamente</li>
                        <li>Los campos marcados con <span class="required">*</span> son obligatorios</li>
                    </ul>
                </div>
            </div>

            <!-- Formulario -->
            <div class="form-container">
                <div class="form-card">
                    <!-- Form Header -->
                    <div class="form-header">
                        <div class="header-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="header-text">
                            <h2>Registrar Nueva Factura</h2>
                            <p>Complete los datos de la factura</p>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form id="formFactura" method="POST">
                        <!-- Información General -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-info-circle"></i>
                                Información General
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="numero_factura">Número de Factura <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-hashtag"></i>
                                        <input type="text" id="numero_factura" name="numero_factura" 
                                               value="<?php echo $numero_sugerido; ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="fecha_factura">Fecha de Factura <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar"></i>
                                        <input type="date" id="fecha_factura" name="fecha_factura" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="fecha_vencimiento">Fecha de Vencimiento</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar-times"></i>
                                        <input type="date" id="fecha_vencimiento" name="fecha_vencimiento">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="id_dueno">Cliente (Dueño) <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-user"></i>
                                        <select id="id_dueno" name="id_dueno" required>
                                            <option value="">Seleccione un cliente...</option>
                                            <?php while($dueno = $duenos->fetch_assoc()): ?>
                                                <option value="<?php echo $dueno['id_dueno']; ?>">
                                                    <?php echo htmlspecialchars($dueno['nombre_completo'] . ' - CC: ' . $dueno['cedula']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="metodo_pago">Método de Pago <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-credit-card"></i>
                                        <select id="metodo_pago" name="metodo_pago" required>
                                            <option value="">Seleccione método...</option>
                                            <option value="Efectivo">Efectivo</option>
                                            <option value="Tarjeta">Tarjeta de Crédito/Débito</option>
                                            <option value="Transferencia">Transferencia Bancaria</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="id_consulta">Consulta Asociada (Opcional)</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-stethoscope"></i>
                                        <select id="id_consulta" name="id_consulta">
                                            <option value="">Sin consulta asociada</option>
                                            <?php while($consulta = $consultas->fetch_assoc()): ?>
                                                <option value="<?php echo $consulta['id_consulta']; ?>">
                                                    <?php echo htmlspecialchars($consulta['dueno'] . ' - ' . $consulta['mascota'] . ' (' . date('d/m/Y', strtotime($consulta['fecha_consulta'])) . ')'); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles de Factura -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-list"></i>
                                Detalles de Factura
                            </h3>

                            <div id="detalles-container">
                                <!-- Los detalles se agregarán aquí dinámicamente -->
                            </div>

                            <button type="button" class="btn btn-outline" onclick="agregarDetalle()">
                                <i class="fas fa-plus"></i>
                                Agregar Concepto
                            </button>
                        </div>

                        <!-- Resumen Financiero -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-calculator"></i>
                                Resumen Financiero
                            </h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="subtotal">Subtotal</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                        <input type="number" id="subtotal" name="subtotal" step="0.01" 
                                               value="0" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="descuento">Descuento</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-percent"></i>
                                        <input type="number" id="descuento" name="descuento" step="0.01" 
                                               value="0">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="impuesto">Impuesto (IVA)</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-percentage"></i>
                                        <input type="number" id="impuesto" name="impuesto" step="0.01" 
                                               value="0">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="total">Total a Pagar</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <input type="number" id="total" name="total" step="0.01" 
                                               value="0" readonly style="font-weight: 700; color: #6366f1;">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="pagado">Monto Pagado</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-hand-holding-usd"></i>
                                        <input type="number" id="pagado" name="pagado" step="0.01" 
                                               value="0">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="saldo">Saldo Pendiente</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-wallet"></i>
                                        <input type="number" id="saldo" name="saldo" step="0.01" 
                                               value="0" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="estado">Estado de la Factura <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-flag"></i>
                                        <select id="estado" name="estado" required>
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="Pagada">Pagada</option>
                                            <option value="Vencida">Vencida</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-actions">
                            <a href="Listar.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Guardar Factura
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- IMPORTANTE: Cargar el script ANTES para que las funciones estén disponibles -->
    <script src="../../Assets/Js/FormsFacturas.js"></script>
</body>
</html>
<?php $conn->close(); ?>