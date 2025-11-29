<?php
/**
 * REGISTRAR DUEÑO
 * Formulario para registrar nuevos propietarios de mascotas
 */

session_start();
require_once '../../Includes/functions.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dueño - VetCare Plus</title>
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
                    Registrar Dueño
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
                <a href="listar.php">Dueños</a>
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
                            <h2>Información del Propietario</h2>
                            <p>Complete todos los campos requeridos para registrar un nuevo dueño en el sistema</p>
                        </div>
                    </div>

                    <form id="formDueno" method="POST" action="procesar.php">
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
                                            max="<?php echo date('Y-m-d'); ?>"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="genero">Género <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-venus-mars"></i>
                                        <select id="genero" name="genero" required>
                                            <option value="">Seleccione...</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="ocupacion">Ocupación</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-briefcase"></i>
                                        <input 
                                            type="text" 
                                            id="ocupacion" 
                                            name="ocupacion"
                                            placeholder="Ej: Ingeniero, Estudiante..."
                                            maxlength="50"
                                        >
                                    </div>
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
                                    <label for="ciudad">Ciudad <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-city"></i>
                                        <input 
                                            type="text" 
                                            id="ciudad" 
                                            name="ciudad"
                                            placeholder="Ej: Neiva, Bogotá..."
                                            required
                                            maxlength="50"
                                        >
                                    </div>
                                    <span class="error-message"></span>
                                </div>
                                
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
                                Guardar Dueño
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