<?php
/**
 * EDITAR EMPLEADO
 * Formulario para editar empleados existentes
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Obtener ID del empleado
$id_empleado = intval($_GET['id'] ?? 0);

if ($id_empleado <= 0) {
    header('Location: listar.php');
    exit();
}

$conn = getConnection();

// Obtener datos del empleado
$stmt = $conn->prepare("SELECT * FROM empleado WHERE id_empleado = ?");
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$result = $stmt->get_result();
$empleado = $result->fetch_assoc();
$stmt->close();

if (!$empleado) {
    header('Location: listar.php');
    exit();
}

// Obtener lista de cargos
$query_cargos = "SELECT id_cargo, nombre_cargo FROM cargo ORDER BY nombre_cargo";
$cargos = $conn->query($query_cargos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado - VetCare Plus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../Assets/Css/Dashboard.css">
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
                    Editar Empleado
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
                <a href="listar.php">Empleados</a>
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
                            <li>Estás editando los datos de <strong><?php echo htmlspecialchars($empleado['primer_nombre'] . ' ' . $empleado['primer_apellido']); ?></strong></li>
                            <li>Los campos marcados con <span class="required">*</span> son obligatorios</li>
                            <li>Deja el campo de contraseña vacío si no deseas cambiarla</li>
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
                            <h2>Editar Información del Empleado</h2>
                            <p>Modifica los campos necesarios y guarda los cambios</p>
                        </div>
                    </div>

                    <form id="formEmpleado" method="POST" action="procesar.php">
                        <input type="hidden" name="action" value="editar">
                        <input type="hidden" name="id_empleado" value="<?php echo $id_empleado; ?>">

                        <!-- Información Personal -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-user"></i>
                                Datos Personales
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="primer_nombre">Primer Nombre <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-user"></i>
                                        <input 
                                            type="text" 
                                            id="primer_nombre" 
                                            name="primer_nombre" 
                                            value="<?php echo htmlspecialchars($empleado['primer_nombre']); ?>"
                                            required
                                            maxlength="50"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="segundo_nombre">Segundo Nombre</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-user"></i>
                                        <input 
                                            type="text" 
                                            id="segundo_nombre" 
                                            name="segundo_nombre"
                                            value="<?php echo htmlspecialchars($empleado['segundo_nombre']); ?>"
                                            maxlength="50"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="primer_apellido">Primer Apellido <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-signature"></i>
                                        <input 
                                            type="text" 
                                            id="primer_apellido" 
                                            name="primer_apellido"
                                            value="<?php echo htmlspecialchars($empleado['primer_apellido']); ?>"
                                            required
                                            maxlength="50"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="segundo_apellido">Segundo Apellido</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-signature"></i>
                                        <input 
                                            type="text" 
                                            id="segundo_apellido" 
                                            name="segundo_apellido"
                                            value="<?php echo htmlspecialchars($empleado['segundo_apellido']); ?>"
                                            maxlength="50"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="cedula">Cédula <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-id-card"></i>
                                        <input 
                                            type="text" 
                                            id="cedula" 
                                            name="cedula"
                                            value="<?php echo htmlspecialchars($empleado['cedula']); ?>"
                                            required
                                            maxlength="10"
                                            pattern="\d{6,10}"
                                        >
                                    </div>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Entre 6 y 10 dígitos numéricos
                                    </span>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="fecha_nacimiento">Fecha de Nacimiento <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                        <input 
                                            type="date" 
                                            id="fecha_nacimiento" 
                                            name="fecha_nacimiento"
                                            value="<?php echo $empleado['fecha_nacimiento']; ?>"
                                            required
                                            max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-address-book"></i>
                                Información de Contacto
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="telefono">Teléfono Celular <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-phone"></i>
                                        <input 
                                            type="tel" 
                                            id="telefono" 
                                            name="telefono"
                                            value="<?php echo htmlspecialchars($empleado['telefono']); ?>"
                                            required
                                            maxlength="10"
                                            pattern="3\d{9}"
                                        >
                                    </div>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        10 dígitos, debe iniciar con 3
                                    </span>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="correo_electronico">Correo Electrónico <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-envelope"></i>
                                        <input 
                                            type="email" 
                                            id="correo_electronico" 
                                            name="correo_electronico"
                                            value="<?php echo htmlspecialchars($empleado['correo_electronico']); ?>"
                                            required
                                            maxlength="100"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="direccion">Dirección Residencia <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <input 
                                            type="text" 
                                            id="direccion" 
                                            name="direccion"
                                            value="<?php echo htmlspecialchars($empleado['direccion']); ?>"
                                            required
                                            maxlength="100"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Información Laboral -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-briefcase"></i>
                                Información Laboral
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="id_cargo">Cargo <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-user-tie"></i>
                                        <select id="id_cargo" name="id_cargo" required>
                                            <option value="">Seleccione un cargo...</option>
                                            <?php 
                                            if ($cargos && $cargos->num_rows > 0) {
                                                while($cargo = $cargos->fetch_assoc()): 
                                            ?>
                                                <option value="<?php echo $cargo['id_cargo']; ?>"
                                                    <?php echo ($cargo['id_cargo'] == $empleado['id_cargo']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cargo['nombre_cargo']); ?>
                                                </option>
                                            <?php 
                                                endwhile;
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="fecha_contratacion">Fecha de Contratación <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-calendar-check"></i>
                                        <input 
                                            type="date" 
                                            id="fecha_contratacion" 
                                            name="fecha_contratacion"
                                            value="<?php echo $empleado['fecha_contratacion']; ?>"
                                            required
                                            max="<?php echo date('Y-m-d'); ?>"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="estado">Estado <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-toggle-on"></i>
                                        <select id="estado" name="estado" required>
                                            <option value="">Seleccione...</option>
                                            <option value="activo" <?php echo ($empleado['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                                            <option value="inactivo" <?php echo ($empleado['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                                        </select>
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Acceso -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-lock"></i>
                                Cambiar Contraseña (Opcional)
                            </h3>
                            
                            <div class="alert alert-warning">
                                <div class="alert-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="alert-content">
                                    <p><strong>Nota:</strong> Solo completa estos campos si deseas cambiar la contraseña. Si los dejas vacíos, la contraseña actual no se modificará.</p>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password">Nueva Contraseña</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-key"></i>
                                        <input 
                                            type="password" 
                                            id="password" 
                                            name="password"
                                            placeholder="Mínimo 8 caracteres (dejar vacío para no cambiar)"
                                            minlength="8"
                                            maxlength="255"
                                        >
                                    </div>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Mínimo 8 caracteres, incluye mayúsculas, minúsculas y números
                                    </span>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirmar Nueva Contraseña</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-key"></i>
                                        <input 
                                            type="password" 
                                            id="confirm_password" 
                                            name="confirm_password"
                                            placeholder="Repita la nueva contraseña"
                                            minlength="8"
                                            maxlength="255"
                                        >
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
    <script>
        // Validar que las contraseñas coincidan (solo si se llenan)
        document.getElementById('formEmpleado').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Solo validar si se ingresó alguna contraseña
            if (password || confirmPassword) {
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden');
                    document.getElementById('confirm_password').focus();
                    return false;
                }
                
                if (password.length < 8) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 8 caracteres');
                    document.getElementById('password').focus();
                    return false;
                }
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>