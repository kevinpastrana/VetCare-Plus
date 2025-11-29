<?php
/**
 * REGISTRAR VACUNA
 * Formulario para crear nuevos registros de vacunación
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';
$conn = getConnection();

// Obtener mascotas activas
$mascotas = $conn->query("SELECT m.id_mascota, m.nombre, m.especie, m.raza,
                           CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS dueno_nombre
                           FROM mascota m
                           INNER JOIN dueno d ON m.id_dueno = d.id_dueno
                           WHERE m.estado = 'Activo'
                           ORDER BY m.nombre");

// Obtener empleados (veterinarios)
$empleados = $conn->query("SELECT e.id_empleado, CONCAT(e.primer_nombre, ' ', e.primer_apellido) AS nombre_completo
                           FROM empleado e
                           WHERE e.estado = 'activo' AND e.id_cargo IN (1, 2)
                           ORDER BY e.primer_nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Vacuna - VetCare Plus</title>
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
                    Volver a Vacunas
                </a>
            </div>
            <div class="nav-center">
                <h1 class="page-title">
                    <i class="fas fa-shield-virus"></i>
                    Nueva Vacuna
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
                <a href="Listar.php">Vacunas</a>
                <span class="separator">/</span>
                <span class="current">Nueva Vacuna</span>
            </div>

            <!-- Alert Info -->
            <div class="alert alert-info">
                <div class="alert-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="alert-content">
                    <h4>Instrucciones para registrar una vacuna</h4>
                    <ul>
                        <li>Seleccione la mascota que recibirá la vacuna</li>
                        <li>Complete los datos de la vacuna (nombre, laboratorio, lote, dosis)</li>
                        <li>Indique la vía de administración y fechas de aplicación</li>
                        <li>Puede programar la próxima aplicación si es necesaria</li>
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
                            <i class="fas fa-shield-virus"></i>
                        </div>
                        <div class="header-text">
                            <h2>Registrar Nueva Vacuna</h2>
                            <p>Complete los datos del registro de vacunación</p>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form id="formVacuna" method="POST">
                        <input type="hidden" name="action" value="registrar">
                        
                        <!-- Información del Paciente -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-paw"></i>
                                Información del Paciente
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label for="id_mascota">Mascota <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-dog"></i>
                                        <select id="id_mascota" name="id_mascota" required>
                                            <option value="">Seleccione una mascota...</option>
                                            <?php while($mascota = $mascotas->fetch_assoc()): ?>
                                                <option value="<?php echo $mascota['id_mascota']; ?>">
                                                    <?php echo htmlspecialchars($mascota['nombre'] . ' - ' . $mascota['especie'] . ' (' . $mascota['dueno_nombre'] . ')'); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de la Vacuna -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-syringe"></i>
                                Información de la Vacuna
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre_vacuna">Nombre de la Vacuna <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-prescription-bottle-medical"></i>
                                        <input type="text" id="nombre_vacuna" name="nombre_vacuna" 
                                               placeholder="Ej: Rabia, Parvovirus, Triple Felina" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="laboratorio">Laboratorio <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-flask"></i>
                                        <input type="text" id="laboratorio" name="laboratorio" 
                                               placeholder="Ej: Zoetis, Virbac, MSD" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="lote">Lote</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-barcode"></i>
                                        <input type="text" id="lote" name="lote" 
                                               placeholder="Número de lote">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="dosis">Dosis</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-vial"></i>
                                        <input type="text" id="dosis" name="dosis" 
                                               placeholder="Ej: 1ml, 2ml">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="via_administracion">Vía de Administración <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-syringe"></i>
                                        <select id="via_administracion" name="via_administracion" required>
                                            <option value="">Seleccione vía...</option>
                                            <option value="Subcutánea">Subcutánea</option>
                                            <option value="Intramuscular">Intramuscular</option>
                                            <option value="Oral">Oral</option>
                                            <option value="Intranasal">Intranasal</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas y Personal -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-calendar-alt"></i>
                                Fechas y Personal
                            </h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fecha_aplicacion">Fecha de Aplicación <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar"></i>
                                        <input type="date" id="fecha_aplicacion" name="fecha_aplicacion" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <small class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Fecha en que se aplicó la vacuna
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="proxima_aplicacion">Próxima Aplicación</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar-plus"></i>
                                        <input type="date" id="proxima_aplicacion" name="proxima_aplicacion">
                                    </div>
                                    <small class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Fecha de refuerzo o revacunación
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="id_empleado">Veterinario <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-user-md"></i>
                                        <select id="id_empleado" name="id_empleado" required>
                                            <option value="">Seleccione veterinario...</option>
                                            <?php while($empleado = $empleados->fetch_assoc()): ?>
                                                <option value="<?php echo $empleado['id_empleado']; ?>">
                                                    <?php echo htmlspecialchars($empleado['nombre_completo']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-notes-medical"></i>
                                Observaciones
                            </h3>

                            <div class="form-row">
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label for="observaciones">Observaciones Adicionales</label>
                                    <textarea id="observaciones" name="observaciones" 
                                              placeholder="Reacciones, efectos secundarios, notas adicionales..."
                                              style="min-height: 120px;"></textarea>
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
                                Guardar Vacuna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Script externo para manejo de formulario -->
    <script src="../../Assets/Js/FormsVacuna.js"></script>
</body>
</html>
<?php $conn->close(); ?>