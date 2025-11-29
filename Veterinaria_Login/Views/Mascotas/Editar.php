<?php
/**
 * EDITAR MASCOTA
 * Formulario para editar mascotas existentes
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Obtener ID de la mascota
$id_mascota = intval($_GET['id'] ?? 0);

if ($id_mascota <= 0) {
    header('Location: listar.php');
    exit();
}

$conn = getConnection();

// Obtener datos de la mascota
$stmt = $conn->prepare("SELECT * FROM mascota WHERE id_mascota = ?");
$stmt->bind_param("i", $id_mascota);
$stmt->execute();
$result = $stmt->get_result();
$mascota = $result->fetch_assoc();
$stmt->close();

if (!$mascota) {
    header('Location: listar.php');
    exit();
}

// Obtener lista de dueños
$query_duenos = "SELECT id_dueno, primer_nombre, primer_apellido FROM dueno ORDER BY primer_nombre";
$duenos = $conn->query($query_duenos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mascota - VetCare Plus</title>
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
                    <i class="fas fa-edit"></i>
                    Editar Mascota
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
                <span class="current">Editar</span>
            </div>

            <div class="form-container">
                <!-- Panel de información -->
                <div class="alert alert-info">
                    <div class="alert-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Información Importante</h4>
                        <ul>
                            <li>Estás editando los datos de <strong><?php echo htmlspecialchars($mascota['nombre']); ?></strong></li>
                            <li>Los campos marcados con <span class="required">*</span> son obligatorios</li>
                            <li>Si subes una nueva foto, reemplazará la anterior</li>
                            <li>Verifica bien los cambios antes de guardar</li>
                        </ul>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-header">
                        <div class="header-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="header-text">
                            <h2>Editar Información de la Mascota</h2>
                            <p>Modifica los campos necesarios y guarda los cambios</p>
                        </div>
                    </div>

                    <form id="formMascota" method="POST" action="procesar.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="editar">
                        <input type="hidden" name="id_mascota" value="<?php echo $id_mascota; ?>">

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
                                                <option value="<?php echo $dueno['id_dueno']; ?>"
                                                    <?php echo ($dueno['id_dueno'] == $mascota['id_dueno']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dueno['primer_nombre'] . ' ' . $dueno['primer_apellido']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
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
                                            value="<?php echo htmlspecialchars($mascota['nombre']); ?>"
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
                                    <?php if ($mascota['foto']): ?>
                                        <span class="form-hint">
                                            <i class="fas fa-check-circle"></i>
                                            Foto actual: <?php echo htmlspecialchars($mascota['foto']); ?>
                                        </span>
                                    <?php endif; ?>
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
                                            value="<?php echo $mascota['fecha_nacimiento']; ?>"
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
                                            <option value="Macho" <?php echo ($mascota['genero'] == 'Macho') ? 'selected' : ''; ?>>Macho</option>
                                            <option value="Hembra" <?php echo ($mascota['genero'] == 'Hembra') ? 'selected' : ''; ?>>Hembra</option>
                                            <option value="Otro" <?php echo ($mascota['genero'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
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
                                            value="<?php echo htmlspecialchars($mascota['especie']); ?>"
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
                                            value="<?php echo htmlspecialchars($mascota['raza']); ?>"
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
                                            value="<?php echo $mascota['peso']; ?>"
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
                                            value="<?php echo htmlspecialchars($mascota['color']); ?>"
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
                                        rows="4"
                                    ><?php echo htmlspecialchars($mascota['diagnostico']); ?></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="antecedentes">Antecedentes Médicos</label>
                                    <textarea 
                                        id="antecedentes" 
                                        name="antecedentes"
                                        rows="4"
                                    ><?php echo htmlspecialchars($mascota['antecedentes']); ?></textarea>
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
                                            value="<?php echo $mascota['fecha_ingreso']; ?>"
                                            max="<?php echo date('Y-m-d'); ?>"
                                        >
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="estado">Estado <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-heartbeat"></i>
                                        <select id="estado" name="estado" required>
                                            <option value="">Seleccione...</option>
                                            <option value="Activo" <?php echo ($mascota['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                                            <option value="Inactivo" <?php echo ($mascota['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                                            <option value="Fallecido" <?php echo ($mascota['estado'] == 'Fallecido') ? 'selected' : ''; ?>>Fallecido</option>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="../../Assets/Js/Forms.js"></script>
</body>
</html>
<?php $conn->close(); ?>