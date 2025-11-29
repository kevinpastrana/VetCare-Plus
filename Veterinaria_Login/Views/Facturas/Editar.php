<?php
/**
 * EDITAR FACTURA
 * Formulario para modificar facturas existentes
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';
$conn = getConnection();

// Obtener ID de la factura
$id_factura = intval($_GET['id'] ?? 0);

if ($id_factura <= 0) {
    header('Location: Listar.php');
    exit;
}

// Obtener datos de la factura
$stmt = $conn->prepare("SELECT f.*, d.primer_nombre, d.primer_apellido 
                        FROM factura f 
                        INNER JOIN dueno d ON f.id_dueno = d.id_dueno 
                        WHERE f.id_factura = ?");
$stmt->bind_param("i", $id_factura);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: Listar.php');
    exit;
}

$factura = $result->fetch_assoc();

// Obtener detalles de la factura
$stmt_detalles = $conn->prepare("SELECT * FROM detalle_factura WHERE id_factura = ?");
$stmt_detalles->bind_param("i", $id_factura);
$stmt_detalles->execute();
$detalles = $stmt_detalles->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener dueños para el select
$duenos = $conn->query("SELECT id_dueno, CONCAT(primer_nombre, ' ', primer_apellido) AS nombre_completo, cedula 
                        FROM dueno ORDER BY primer_nombre, primer_apellido");

// Obtener consultas sin facturar + la actual si existe
$consultas_query = "SELECT c.id_consulta, c.fecha_consulta, m.nombre AS mascota, 
                    CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno
                    FROM consulta c
                    INNER JOIN mascota m ON c.id_mascota = m.id_mascota
                    INNER JOIN dueno d ON m.id_dueno = d.id_dueno
                    LEFT JOIN factura f ON c.id_consulta = f.id_consulta
                    WHERE f.id_factura IS NULL OR f.id_factura = ?
                    ORDER BY c.fecha_consulta DESC";
$stmt_consultas = $conn->prepare($consultas_query);
$stmt_consultas->bind_param("i", $id_factura);
$stmt_consultas->execute();
$consultas = $stmt_consultas->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Factura - VetCare Plus</title>
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
                    <i class="fas fa-edit"></i>
                    Editar Factura
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
                <span class="current">Editar Factura #<?php echo htmlspecialchars($factura['numero_factura']); ?></span>
            </div>

            <!-- Alert Info -->
            <div class="alert alert-warning">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-content">
                    <h4>Editando Factura</h4>
                    <p>Está modificando la factura <strong>#<?php echo htmlspecialchars($factura['numero_factura']); ?></strong>. Los cambios se guardarán inmediatamente.</p>
                </div>
            </div>

            <!-- Formulario -->
            <div class="form-container">
                <div class="form-card">
                    <!-- Form Header -->
                    <div class="form-header">
                        <div class="header-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="header-text">
                            <h2>Editar Factura #<?php echo htmlspecialchars($factura['numero_factura']); ?></h2>
                            <p>Modifique los datos necesarios</p>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form id="formFactura" method="POST">
                        <input type="hidden" name="id_factura" value="<?php echo $id_factura; ?>">
                        
                        <!-- Información General -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-info-circle"></i>
                                Información General
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="numero_factura">Número de Factura</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-hashtag"></i>
                                        <input type="text" id="numero_factura" name="numero_factura" 
                                               value="<?php echo htmlspecialchars($factura['numero_factura']); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="fecha_factura">Fecha de Factura</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar"></i>
                                        <input type="date" id="fecha_factura" name="fecha_factura" 
                                               value="<?php echo $factura['fecha_factura']; ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="fecha_vencimiento">Fecha de Vencimiento</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar-times"></i>
                                        <input type="date" id="fecha_vencimiento" name="fecha_vencimiento"
                                               value="<?php echo $factura['fecha_vencimiento']; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="id_dueno">Cliente (Dueño)</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-user"></i>
                                        <select id="id_dueno" name="id_dueno" required>
                                            <option value="">Seleccione un cliente...</option>
                                            <?php while($dueno = $duenos->fetch_assoc()): ?>
                                                <option value="<?php echo $dueno['id_dueno']; ?>" 
                                                    <?php echo ($dueno['id_dueno'] == $factura['id_dueno']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dueno['nombre_completo'] . ' - CC: ' . $dueno['cedula']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="metodo_pago">Método de Pago</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-credit-card"></i>
                                        <select id="metodo_pago" name="metodo_pago" required>
                                            <option value="Efectivo" <?php echo ($factura['metodo_pago'] == 'Efectivo') ? 'selected' : ''; ?>>Efectivo</option>
                                            <option value="Tarjeta" <?php echo ($factura['metodo_pago'] == 'Tarjeta') ? 'selected' : ''; ?>>Tarjeta de Crédito/Débito</option>
                                            <option value="Transferencia" <?php echo ($factura['metodo_pago'] == 'Transferencia') ? 'selected' : ''; ?>>Transferencia Bancaria</option>
                                            <option value="Otro" <?php echo ($factura['metodo_pago'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
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
                                                <option value="<?php echo $consulta['id_consulta']; ?>"
                                                    <?php echo ($consulta['id_consulta'] == $factura['id_consulta']) ? 'selected' : ''; ?>>
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
                                <!-- Los detalles existentes se cargarán aquí -->
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
                                               value="<?php echo $factura['subtotal']; ?>" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="descuento">Descuento</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-percent"></i>
                                        <input type="number" id="descuento" name="descuento" step="0.01" 
                                               value="<?php echo $factura['descuento']; ?>" onchange="calcularTotales()">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="impuesto">Impuesto (IVA)</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-percentage"></i>
                                        <input type="number" id="impuesto" name="impuesto" step="0.01" 
                                               value="<?php echo $factura['impuesto']; ?>" onchange="calcularTotales()">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="total">Total a Pagar</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <input type="number" id="total" name="total" step="0.01" 
                                               value="<?php echo $factura['total']; ?>" readonly style="font-weight: 700; color: #6366f1;">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="pagado">Monto Pagado</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-hand-holding-usd"></i>
                                        <input type="number" id="pagado" name="pagado" step="0.01" 
                                               value="<?php echo $factura['pagado']; ?>" onchange="calcularSaldo()">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="saldo">Saldo Pendiente</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-wallet"></i>
                                        <input type="number" id="saldo" name="saldo" step="0.01" 
                                               value="<?php echo $factura['saldo']; ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="estado">Estado de la Factura</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-flag"></i>
                                        <select id="estado" name="estado" required>
                                            <option value="Pendiente" <?php echo ($factura['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="Pagada" <?php echo ($factura['estado'] == 'Pagada') ? 'selected' : ''; ?>>Pagada</option>
                                            <option value="Vencida" <?php echo ($factura['estado'] == 'Vencida') ? 'selected' : ''; ?>>Vencida</option>
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
                                Actualizar Factura
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        let contadorDetalles = 0;
        const detallesExistentes = <?php echo json_encode($detalles); ?>;

        // Cargar detalles existentes al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            if (detallesExistentes.length > 0) {
                detallesExistentes.forEach(detalle => {
                    agregarDetalle(detalle);
                });
            } else {
                agregarDetalle();
            }
        });

        function agregarDetalle(datos = null) {
            contadorDetalles++;
            const container = document.getElementById('detalles-container');
            
            const concepto = datos ? datos.concepto : '';
            const descripcion = datos ? datos.descripcion : '';
            const cantidad = datos ? datos.cantidad : 1;
            const precio = datos ? datos.precio_unitario : 0;
            const subtotal = datos ? datos.subtotal : 0;
            
            const detalleHTML = `
                <div class="detalle-item" id="detalle-${contadorDetalles}" style="border: 2px solid #e2e8f0; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem; position: relative;">
                    <button type="button" class="btn-remove-detalle" onclick="eliminarDetalle(${contadorDetalles})" 
                            style="position: absolute; top: 10px; right: 10px; background: #fee2e2; color: #dc2626; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Concepto</label>
                            <input type="text" name="detalles[${contadorDetalles}][concepto]" 
                                   value="${concepto}" placeholder="Ej: Consulta veterinaria" required>
                        </div>
                        <div class="form-group">
                            <label>Cantidad</label>
                            <input type="number" name="detalles[${contadorDetalles}][cantidad]" 
                                   value="${cantidad}" min="1" onchange="calcularSubtotalDetalle(${contadorDetalles})" required>
                        </div>
                        <div class="form-group">
                            <label>Precio Unitario</label>
                            <input type="number" name="detalles[${contadorDetalles}][precio_unitario]" 
                                   value="${precio}" step="0.01" onchange="calcularSubtotalDetalle(${contadorDetalles})" required>
                        </div>
                        <div class="form-group">
                            <label>Subtotal</label>
                            <input type="number" name="detalles[${contadorDetalles}][subtotal]" 
                                   value="${subtotal}" step="0.01" readonly style="font-weight: 600; color: #6366f1;">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Descripción (Opcional)</label>
                            <textarea name="detalles[${contadorDetalles}][descripcion]" 
                                      placeholder="Detalles adicionales del servicio o producto..." 
                                      style="min-height: 80px;">${descripcion}</textarea>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', detalleHTML);
            
            if (!datos) {
                calcularTotales();
            }
        }

        function eliminarDetalle(id) {
            const detalle = document.getElementById(`detalle-${id}`);
            if (detalle) {
                detalle.remove();
                calcularTotales();
            }
        }

        function calcularSubtotalDetalle(id) {
            const detalle = document.getElementById(`detalle-${id}`);
            const cantidad = parseFloat(detalle.querySelector('[name*="[cantidad]"]').value) || 0;
            const precio = parseFloat(detalle.querySelector('[name*="[precio_unitario]"]').value) || 0;
            const subtotal = cantidad * precio;
            
            detalle.querySelector('[name*="[subtotal]"]').value = subtotal.toFixed(2);
            calcularTotales();
        }

        function calcularTotales() {
            let subtotalGeneral = 0;
            
            document.querySelectorAll('[name*="[subtotal]"]').forEach(input => {
                subtotalGeneral += parseFloat(input.value) || 0;
            });
            
            const descuento = parseFloat(document.getElementById('descuento').value) || 0;
            const impuesto = parseFloat(document.getElementById('impuesto').value) || 0;
            
            const subtotalConDescuento = subtotalGeneral - descuento;
            const total = subtotalConDescuento + impuesto;
            
            document.getElementById('subtotal').value = subtotalGeneral.toFixed(2);
            document.getElementById('total').value = total.toFixed(2);
            
            calcularSaldo();
        }

        function calcularSaldo() {
            const total = parseFloat(document.getElementById('total').value) || 0;
            const pagado = parseFloat(document.getElementById('pagado').value) || 0;
            const saldo = total - pagado;
            
            document.getElementById('saldo').value = saldo.toFixed(2);
            
            const estadoSelect = document.getElementById('estado');
            if (saldo <= 0 && total > 0) {
                estadoSelect.value = 'Pagada'; // ← CORREGIDO: 'Pagada' en lugar de 'Pagado'
            } else if (saldo > 0) {
                estadoSelect.value = 'Pendiente';
            }
        }

        // Manejar envío del formulario
        document.getElementById('formFactura').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'actualizar');
            
            const data = {};
            const detalles = [];
            
            formData.forEach((value, key) => {
                if (key.includes('detalles[')) {
                    const match = key.match(/detalles\[(\d+)\]\[(\w+)\]/);
                    if (match) {
                        const index = match[1];
                        const field = match[2];
                        
                        if (!detalles[index]) {
                            detalles[index] = {};
                        }
                        detalles[index][field] = value;
                    }
                } else {
                    data[key] = value;
                }
            });
            
            data.detalles = detalles.filter(d => d && d.concepto);
            
            fetch('Procesar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'Listar.php';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error de conexión');
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>