<?php
/**
 * REGISTRAR EMPLEADO
 * Formulario para registrar nuevos empleados
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Obtener lista de cargos para el select
$conn = getConnection();
$query_cargos = "SELECT id_cargo, nombre_cargo FROM cargo ORDER BY nombre_cargo";
$cargos = $conn->query($query_cargos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Empleado - VetCare Plus</title>
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
                    <i class="fas fa-user-plus"></i>
                    Registrar Empleado
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
                            <li>La cédula debe ser única en el sistema (entre 6 y 10 dígitos)</li>
                            <li>El correo electrónico debe ser válido y será usado para notificaciones</li>
                            <li>El teléfono debe ser un número celular colombiano de 10 dígitos</li>
                            <li>La contraseña debe tener al menos 8 caracteres para seguridad</li>
                            <li>Verifica bien los datos antes de guardar</li>
                        </ul>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-header">
                        <div class="header-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="header-text">
                            <h2>Información del Empleado</h2>
                            <p>Complete todos los campos requeridos para registrar un nuevo empleado en el sistema</p>
                        </div>
                    </div>

                    <form id="formEmpleado" method="POST" action="Procesar.php">
                        <input type="hidden" name="action" value="registrar">

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
                                            placeholder="Ej: Juan"
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
                                            placeholder="Ej: Carlos (Opcional)"
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
                                            placeholder="Ej: Pérez"
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
                                            placeholder="Ej: García (Opcional)"
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
                                            placeholder="Ej: 1084576190"
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
                                            required
                                            max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>"
                                        >
                                    </div>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Debe ser mayor de 18 años
                                    </span>
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
                                            placeholder="Ej: 3001234567"
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
                                            placeholder="Ej: juan.perez@email.com"
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
                                            placeholder="Ej: Calle 15 #20-30"
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
                                                <option value="<?php echo $cargo['id_cargo']; ?>">
                                                    <?php echo htmlspecialchars($cargo['nombre_cargo']); ?>
                                                </option>
                                            <?php 
                                                endwhile;
                                            } else {
                                                echo '<option value="" disabled>No hay cargos disponibles</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <span class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Selecciona el cargo del empleado
                                    </span>
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
                                            <option value="activo" selected>Activo</option>
                                            <option value="inactivo">Inactivo</option>
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
                                Credenciales de Acceso
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password">Contraseña <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-key"></i>
                                        <input 
                                            type="password" 
                                            id="password" 
                                            name="password"
                                            placeholder="Mínimo 8 caracteres"
                                            required
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
                                    <label for="confirm_password">Confirmar Contraseña <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-key"></i>
                                        <input 
                                            type="password" 
                                            id="confirm_password" 
                                            name="confirm_password"
                                            placeholder="Repita la contraseña"
                                            required
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
                            <button type="reset" class="btn btn-danger" onclick="return confirm('¿Estás seguro de limpiar todos los campos?')">
                                <i class="fas fa-eraser"></i>
                                Limpiar Formulario
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Guardar Empleado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="../../Assets/Js/FormsEmpleado.js"></script>
</body>
</html>
<?php $conn->close(); ?>