<?php
/**
 * REGISTRAR CARGO
 * Formulario para crear nuevos cargos
 */

session_start();
require_once '../../Includes/functions.php';
require_once '../../Config/database.php';
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Cargo - VetCare Plus</title>
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
                    Volver a Cargos
                </a>
            </div>
            <div class="nav-center">
                <h1 class="page-title">
                    <i class="fas fa-user-tag"></i>
                    Nuevo Cargo
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
                <a href="Listar.php">Cargos</a>
                <span class="separator">/</span>
                <span class="current">Nuevo Cargo</span>
            </div>

            <!-- Alert Info -->
            <div class="alert alert-info">
                <div class="alert-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="alert-content">
                    <h4>Instrucciones para registrar un cargo</h4>
                    <ul>
                        <li>Ingrese un nombre descriptivo para el cargo</li>
                        <li>Agregue una descripción detallada de las responsabilidades</li>
                        <li>Los cargos se pueden asignar a los empleados del sistema</li>
                        <li>Puede activar o desactivar cargos según sea necesario</li>
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
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <div class="header-text">
                            <h2>Registrar Nuevo Cargo</h2>
                            <p>Complete la información del cargo o posición</p>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form id="formCargo" method="POST">
                        <input type="hidden" name="action" value="registrar">
                        
                        <!-- Información del Cargo -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-briefcase"></i>
                                Información del Cargo
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre_cargo">Nombre del Cargo <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-id-badge"></i>
                                        <input type="text" id="nombre_cargo" name="nombre_cargo" 
                                               placeholder="Ej: Veterinario, Recepcionista, Asistente" 
                                               maxlength="50" required>
                                    </div>
                                    <small class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Máximo 50 caracteres
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="estado">Estado <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-toggle-on"></i>
                                        <select id="estado" name="estado" required>
                                            <option value="Activo" selected>Activo</option>
                                            <option value="Inactivo">Inactivo</option>
                                        </select>
                                    </div>
                                    <small class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Estado inicial del cargo
                                    </small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label for="descripcion">Descripción del Cargo</label>
                                    <textarea id="descripcion" name="descripcion" 
                                              placeholder="Describa las responsabilidades, funciones y tareas principales del cargo..."
                                              maxlength="200"
                                              style="min-height: 120px;"></textarea>
                                    <small class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Máximo 200 caracteres. <span id="charCount">0/200</span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="alert alert-warning" style="margin-top: 1.5rem;">
                            <div class="alert-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="alert-content">
                                <h4>Importante</h4>
                                <ul>
                                    <li>Los cargos se asignan a los empleados durante su registro</li>
                                    <li>No podrá eliminar un cargo que esté asignado a empleados activos</li>
                                    <li>Puede desactivar temporalmente un cargo sin eliminarlo</li>
                                </ul>
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
                                Guardar Cargo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Script externo para manejo de formulario -->
    <script src="../../Assets/Js/FormsCargo.js"></script>
    
    <!-- Contador de caracteres -->
    <script>
        const descripcionTextarea = document.getElementById('descripcion');
        const charCount = document.getElementById('charCount');
        
        if (descripcionTextarea && charCount) {
            descripcionTextarea.addEventListener('input', function() {
                const count = this.value.length;
                charCount.textContent = `${count}/200`;
                
                if (count >= 180) {
                    charCount.style.color = '#dc2626';
                } else if (count >= 150) {
                    charCount.style.color = '#f59e0b';
                } else {
                    charCount.style.color = '#64748b';
                }
            });
        }
    </script>
</body>
</html>