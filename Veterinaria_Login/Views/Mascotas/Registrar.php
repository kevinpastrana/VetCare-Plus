<?php
/**
 * REGISTRAR MASCOTA
 * Formulario para registrar nuevas mascotas
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Obtener lista de dueños para el select
$conn = getConnection();
$query_duenos = "SELECT id_dueno, primer_nombre, primer_apellido FROM dueno ORDER BY primer_nombre";
$duenos = $conn->query($query_duenos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Mascota - VetCare Plus</title>
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
                    Registrar Mascota
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
                <a href="listar.php">Mascotas</a>
                <span class="separator">/</span>
                <span class="current">Registrar</span>
            </div>

            <div class="form-container">
                <!-- Panel de ayuda ARRIBA del formulario -->
                <div class="alert alert-info">
                    <div class="alert-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Información Importante</h4>
                        <ul>
                            <li>Los campos marcados con <span class="required">*</span> son obligatorios</li>
                            <li>Debes seleccionar un dueño registrado en el sistema</li>
                            <li>La foto es opcional pero ayuda a identificar a la mascota</li>
                            <li>Completa el historial médico para un mejor seguimiento</li>
                            <li>Verifica bien los datos antes de guardar</li>
                        </ul>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-header">
                        <div class="header-icon">
                            <i class="fas fa-paw"></i>
                        </div>
                        <div class="header-text">
                            <h2>Información de la Mascota</h2>
                            <p>Complete todos los campos requeridos para registrar una nueva mascota en el sistema</p>
                        </div>
                    </div>

                    <form id="formMascota" method="POST" action="Procesar.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="registrar">

                        <!-- Información del Dueño -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-user"></i>
                                Propietario
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="id_dueno">Dueño de la Mascota <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-user"></i>
                                        <select id="id_dueno" name="id_dueno" required>
                                            <option value="">Seleccione un dueño...</option>
                                            <?php while($dueno = $duenos->fetch_assoc()): ?>
                                                <option value="<?php echo $dueno['id_dueno']; ?>">
                                                    <?php echo htmlspecialchars($dueno['primer_nombre'] . ' ' . $dueno['primer_apellido']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Si el dueño no está en la lista, debes registrarlo primero
                                    </span>
                                    <span class="error-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Información Básica -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-paw"></i>
                                Datos Básicos
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre">Nombre de la Mascota <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-tag"></i>
                                        <input 
                                            type="text" 
                                            id="nombre" 
                                            name="nombre" 
                                            placeholder="Ej: Max, Luna, Rocky..."
                                            required
                                            maxlength="50"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="foto">Foto de la Mascota</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-camera"></i>
                                        <input 
                                            type="file" 
                                            id="foto" 
                                            name="foto"
                                            accept="image/*"
                                        >
                                    </div>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Formato: JPG, PNG. Máximo 5MB
                                    </span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fecha_nacimiento">Fecha de Nacimiento <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                        <input 
                                            type="date" 
                                            id="fecha_nacimiento" 
                                            name="fecha_nacimiento"
                                            required
                                            max="<?php echo date('Y-m-d'); ?>"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="genero">Género <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-venus-mars"></i>
                                        <select id="genero" name="genero" required>
                                            <option value="">Seleccione...</option>
                                            <option value="Macho">Macho</option>
                                            <option value="Hembra">Hembra</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Características -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-list"></i>
                                Características
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="especie">Especie <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-dog"></i>
                                        <input 
                                            type="text" 
                                            id="especie" 
                                            name="especie"
                                            placeholder="Ej: Perro, Gato, Conejo..."
                                            required
                                            maxlength="50"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="raza">Raza <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-certificate"></i>
                                        <input 
                                            type="text" 
                                            id="raza" 
                                            name="raza"
                                            placeholder="Ej: Labrador, Siamés, Mestizo..."
                                            required
                                            maxlength="50"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="peso">Peso (kg) <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-weight"></i>
                                        <input 
                                            type="number" 
                                            id="peso" 
                                            name="peso"
                                            placeholder="Ej: 5.5"
                                            required
                                            step="0.1"
                                            min="0.1"
                                            max="999.9"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="color">Color</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-palette"></i>
                                        <input 
                                            type="text" 
                                            id="color" 
                                            name="color"
                                            placeholder="Ej: Negro, Blanco, Café..."
                                            maxlength="50"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Médica -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-notes-medical"></i>
                                Información Médica
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="diagnostico">Diagnóstico Actual</label>
                                    <textarea 
                                        id="diagnostico" 
                                        name="diagnostico"
                                        placeholder="Describa el estado de salud actual o diagnósticos relevantes..."
                                        rows="4"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="antecedentes">Antecedentes Médicos</label>
                                    <textarea 
                                        id="antecedentes" 
                                        name="antecedentes"
                                        placeholder="Historial de enfermedades, cirugías, alergias, tratamientos previos..."
                                        rows="4"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fecha_ingreso">Fecha de Ingreso</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar-check"></i>
                                        <input 
                                            type="date" 
                                            id="fecha_ingreso" 
                                            name="fecha_ingreso"
                                            max="<?php echo date('Y-m-d'); ?>"
                                        >
                                    </div>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Primera vez que la mascota visitó la clínica
                                    </span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="estado">Estado <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-heartbeat"></i>
                                        <select id="estado" name="estado" required>
                                            <option value="">Seleccione...</option>
                                            <option value="Activo" selected>Activo</option>
                                            <option value="Inactivo">Inactivo</option>
                                            <option value="Fallecido">Fallecido</option>
                                        </select>
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="form-actions">
                            <a href="listar.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                            <button type="reset" class="btn btn-danger" onclick="return confirm('¿Estás seguro de limpiar todos los campos?')">
                                <i class="fas fa-eraser"></i>
                                Limpiar Formulario
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Guardar Mascota
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="../../Assets/Js/FormsMascotas.js"></script>
</body>
</html>
<?php $conn->close(); ?>