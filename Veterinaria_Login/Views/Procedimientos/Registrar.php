<?php
/**
 * REGISTRAR PROCEDIMIENTO
 * Formulario para crear nuevos procedimientos médicos
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
    <title>Nuevo Procedimiento - VetCare Plus</title>
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
                    Volver a Procedimientos
                </a>
            </div>
            <div class="nav-center">
                <h1 class="page-title">
                    <i class="fas fa-syringe"></i>
                    Nuevo Procedimiento
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
                <a href="Listar.php">Procedimientos</a>
                <span class="separator">/</span>
                <span class="current">Nuevo Procedimiento</span>
            </div>

            <!-- Alert Info -->
            <div class="alert alert-info">
                <div class="alert-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="alert-content">
                    <h4>Instrucciones para registrar un procedimiento</h4>
                    <ul>
                        <li>Complete la información general del procedimiento</li>
                        <li>Seleccione la mascota que recibirá el tratamiento</li>
                        <li>Registre el historial médico y diagnóstico</li>
                        <li>Indique si incluye esterilización</li>
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
                            <i class="fas fa-syringe"></i>
                        </div>
                        <div class="header-text">
                            <h2>Registrar Nuevo Procedimiento</h2>
                            <p>Complete los datos del procedimiento médico</p>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form id="formProcedimiento" method="POST">
                        <!-- Información General -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-info-circle"></i>
                                Información General
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre">Nombre del Procedimiento <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-file-medical"></i>
                                        <input type="text" id="nombre" name="nombre" 
                                               placeholder="Ej: Cirugía de esterilización" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="tipo">Tipo de Procedimiento <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-stethoscope"></i>
                                        <select id="tipo" name="tipo" required>
                                            <option value="">Seleccione tipo...</option>
                                            <option value="Cirugía">Cirugía</option>
                                            <option value="Tratamiento">Tratamiento</option>
                                            <option value="Diagnóstico">Diagnóstico</option>
                                            <option value="Preventivo">Preventivo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="id_mascota">Mascota <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-paw"></i>
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

                                <div class="form-group">
                                    <label for="id_empleado">Veterinario Responsable <span class="required">*</span></label>
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

                            <div class="form-row">
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label for="descripcion">Descripción</label>
                                    <textarea id="descripcion" name="descripcion" 
                                              placeholder="Descripción general del procedimiento..."
                                              style="min-height: 100px;"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Información Médica -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-heartbeat"></i>
                                Información Médica
                            </h3>

                            <div class="form-row">
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label for="patologia">Patología</label>
                                    <textarea id="patologia" name="patologia" 
                                              placeholder="Descripción de la patología o condición..."
                                              style="min-height: 100px;"></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label for="historial_medico">Historial Médico</label>
                                    <textarea id="historial_medico" name="historial_medico" 
                                              placeholder="Historial médico relevante..."
                                              style="min-height: 100px;"></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label for="diagnostico">Diagnóstico</label>
                                    <textarea id="diagnostico" name="diagnostico" 
                                              placeholder="Diagnóstico del veterinario..."
                                              style="min-height: 100px;"></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label for="antecedentes">Antecedentes</label>
                                    <textarea id="antecedentes" name="antecedentes" 
                                              placeholder="Antecedentes médicos relevantes..."
                                              style="min-height: 100px;"></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="esterilizacion">¿Incluye Esterilización?</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-check-circle"></i>
                                        <select id="esterilizacion" name="esterilizacion">
                                            <option value="0">No</option>
                                            <option value="1">Sí</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Programación y Costos -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-calendar-alt"></i>
                                Programación y Costos
                            </h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fecha_procedimiento">Fecha del Procedimiento <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar"></i>
                                        <input type="date" id="fecha_procedimiento" name="fecha_procedimiento" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="hora_inicio">Hora de Inicio</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-clock"></i>
                                        <input type="time" id="hora_inicio" name="hora_inicio">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="hora_fin">Hora de Finalización</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-clock"></i>
                                        <input type="time" id="hora_fin" name="hora_fin">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="costo">Costo (COP)</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                        <input type="number" id="costo" name="costo" step="0.01" 
                                               placeholder="0.00" min="0">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="estado">Estado <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-flag"></i>
                                        <select id="estado" name="estado" required>
                                            <option value="Programado">Programado</option>
                                            <option value="En Proceso">En Proceso</option>
                                            <option value="Completado" selected>Completado</option>
                                            <option value="Cancelado">Cancelado</option>
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
                                Guardar Procedimiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Script externo para manejo de formulario -->
    <script src="../../Assets/Js/FormsProcedimiento.js"></script>
</body>
</html>
<?php $conn->close(); ?>