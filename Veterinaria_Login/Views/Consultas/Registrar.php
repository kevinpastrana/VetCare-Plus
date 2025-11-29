<?php
/**
 * REGISTRAR CONSULTA
 * Formulario para registrar nuevas consultas médicas
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Obtener lista de mascotas para el select
$conn = getConnection();
$query_mascotas = "SELECT m.id_mascota, m.nombre, m.especie, d.primer_nombre, d.primer_apellido 
                   FROM mascota m 
                   INNER JOIN dueno d ON m.id_dueno = d.id_dueno 
                   WHERE m.estado = 'Activo'
                   ORDER BY m.nombre";
$mascotas = $conn->query($query_mascotas);

// Obtener lista de veterinarios (empleados con cargo de veterinario)
$query_veterinarios = "SELECT e.id_empleado, e.primer_nombre, e.primer_apellido, c.nombre_cargo
                       FROM empleado e
                       INNER JOIN cargo c ON e.id_cargo = c.id_cargo
                       WHERE e.estado = 'activo'
                       ORDER BY e.primer_nombre";
$veterinarios = $conn->query($query_veterinarios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Consulta - VetCare Plus</title>
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
                    <i class="fas fa-stethoscope"></i>
                    Registrar Consulta
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
                <a href="Listar.php">Consultas</a>
                <span class="separator">/</span>
                <span class="current">Registrar</span>
            </div>

            <div class="form-container">
                <!-- Panel de ayuda -->
                <div class="alert alert-info">
                    <div class="alert-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Información Importante</h4>
                        <ul>
                            <li>Los campos marcados con <span class="required">*</span> son obligatorios</li>
                            <li>Selecciona la mascota que será atendida en la consulta</li>
                            <li>Registra el veterinario que realizará la atención</li>
                            <li>Describe detalladamente los síntomas y el diagnóstico</li>
                            <li>Puedes programar una próxima cita si es necesario</li>
                        </ul>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-header">
                        <div class="header-icon">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="header-text">
                            <h2>Información de la Consulta</h2>
                            <p>Complete todos los campos requeridos para registrar una nueva consulta médica</p>
                        </div>
                    </div>

                    <form id="formConsulta" method="POST" action="Procesar.php">
                        <input type="hidden" name="action" value="registrar">

                        <!-- Información de Fecha y Hora -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-calendar-alt"></i>
                                Fecha y Hora
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fecha_consulta">Fecha de Consulta <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar"></i>
                                        <input 
                                            type="date" 
                                            id="fecha_consulta" 
                                            name="fecha_consulta" 
                                            required
                                            value="<?php echo date('Y-m-d'); ?>"
                                            max="<?php echo date('Y-m-d'); ?>"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="hora_consulta">Hora de Consulta <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-clock"></i>
                                        <input 
                                            type="time" 
                                            id="hora_consulta" 
                                            name="hora_consulta" 
                                            required
                                            value="<?php echo date('H:i'); ?>"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Mascota y Veterinario -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-user-md"></i>
                                Paciente y Veterinario
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="id_mascota">Mascota (Paciente) <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-paw"></i>
                                        <select id="id_mascota" name="id_mascota" required>
                                            <option value="">Seleccione una mascota...</option>
                                            <?php while($mascota = $mascotas->fetch_assoc()): ?>
                                                <option value="<?php echo $mascota['id_mascota']; ?>">
                                                    <?php echo htmlspecialchars($mascota['nombre'] . ' (' . $mascota['especie'] . ') - Dueño: ' . $mascota['primer_nombre'] . ' ' . $mascota['primer_apellido']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Selecciona la mascota que será atendida
                                    </span>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="id_empleado">Veterinario <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-user-md"></i>
                                        <select id="id_empleado" name="id_empleado" required>
                                            <option value="">Seleccione un veterinario...</option>
                                            <?php while($veterinario = $veterinarios->fetch_assoc()): ?>
                                                <option value="<?php echo $veterinario['id_empleado']; ?>">
                                                    <?php echo htmlspecialchars($veterinario['primer_nombre'] . ' ' . $veterinario['primer_apellido'] . ' - ' . $veterinario['nombre_cargo']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Veterinario que realizará la consulta
                                    </span>
                                    <span class="error-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Motivo y Síntomas -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-notes-medical"></i>
                                Motivo y Síntomas
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="motivo">Motivo de Consulta <span class="required">*</span></label>
                                    <textarea 
                                        id="motivo" 
                                        name="motivo"
                                        placeholder="Describa el motivo principal de la consulta..."
                                        required
                                        rows="3"
                                    ></textarea>
                                    <span class="error-message"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="sintomas">Síntomas Observados <span class="required">*</span></label>
                                    <textarea 
                                        id="sintomas" 
                                        name="sintomas"
                                        placeholder="Liste los síntomas presentados por la mascota..."
                                        required
                                        rows="4"
                                    ></textarea>
                                    <span class="error-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Diagnóstico y Tratamiento -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-diagnoses"></i>
                                Diagnóstico y Tratamiento
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="diagnostico">Diagnóstico</label>
                                    <textarea 
                                        id="diagnostico" 
                                        name="diagnostico"
                                        placeholder="Diagnóstico médico realizado..."
                                        rows="4"
                                    ></textarea>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Puede completarse después de la evaluación
                                    </span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="tratamiento">Tratamiento Prescrito</label>
                                    <textarea 
                                        id="tratamiento" 
                                        name="tratamiento"
                                        placeholder="Medicamentos, dosis, instrucciones..."
                                        rows="4"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="observaciones">Observaciones Adicionales</label>
                                    <textarea 
                                        id="observaciones" 
                                        name="observaciones"
                                        placeholder="Cualquier información adicional relevante..."
                                        rows="3"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Estado y Próxima Cita -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-clipboard-check"></i>
                                Estado y Seguimiento
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="estado">Estado de la Consulta <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-check-circle"></i>
                                        <select id="estado" name="estado" required>
                                            <option value="">Seleccione...</option>
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="Completada" selected>Completada</option>
                                            <option value="Cancelada">Cancelada</option>
                                        </select>
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="proxima_cita">Próxima Cita (Opcional)</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar-plus"></i>
                                        <input 
                                            type="date" 
                                            id="proxima_cita" 
                                            name="proxima_cita"
                                            min="<?php echo date('Y-m-d'); ?>"
                                        >
                                    </div>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Si requiere seguimiento
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="form-actions">
                            <a href="Listar.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                            <button type="reset" class="btn btn-danger" onclick="return confirm('¿Estás seguro de limpiar todos los campos?')">
                                <i class="fas fa-eraser"></i>
                                Limpiar Formulario
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Guardar Consulta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="../../Assets/Js/FormsConsulta.js"></script>
</body>
</html>
<?php $conn->close(); ?>