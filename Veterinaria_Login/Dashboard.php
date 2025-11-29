<?php
/**
 * DASHBOARD.PHP - Panel Principal del Sistema
 * Sistema de Gestión Veterinaria - Versión Mejorada
 */

session_start();
require_once 'Includes/functions.php';

// Verificar autenticación
requireLogin();

// Obtener datos del usuario
$user_name = $_SESSION['user_name'] ?? 'Usuario';
$user_cargo = $_SESSION['user_cargo'] ?? 'Sin cargo';
$user_id = $_SESSION['user_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Veterinaria</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS del Dashboard -->
    <link rel="stylesheet" href="Assets/Css/Dashboard.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <div class="logo">
                    <i class="fas fa-paw"></i>
                    <span>VetCare Plus</span>
                </div>
            </div>
            <div class="nav-center">
                <h1 class="page-title">
                    <i class="fas fa-home"></i>
                    Sistema de Gestión Veterinaria
                </h1>
            </div>
            <div class="nav-right">
                <div class="user-menu">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($user_cargo); ?></span>
                    </div>
                    <button class="btn-logout" onclick="confirmarLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Salir</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Welcome Section -->
            <section class="welcome-section">
                <div class="welcome-card">
                    <h2>¡Bienvenido, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>! 👋</h2>
                    <p>Selecciona la opción que deseas gestionar en el sistema</p>
                </div>
            </section>

            <!-- Menu Grid -->
            <section class="menu-section">
                <h3 class="section-title">
                    <i class="fas fa-th-large"></i>
                    Módulos del Sistema
                </h3>
                
                <div class="menu-grid">
                    <!-- Registrar Dueño -->
                    <a href="Views/Duenos/listar.php" class="menu-card blue" data-module="dueno">
                        <div class="card-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="card-content">
                            <h4>Registrar Dueño</h4>
                            <p>Agregar nuevos propietarios de mascotas</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Registrar Mascota -->
                    <a href="Views/Mascotas/listar.php" class="menu-card green" data-module="mascota">
                        <div class="card-icon">
                            <i class="fas fa-dog"></i>
                        </div>
                        <div class="card-content">
                            <h4>Registrar Mascota</h4>
                            <p>Agregar pacientes al sistema</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Registrar Empleado -->
                    <a href="Views/Empleados/listar.php" class="menu-card orange" data-module="empleado">
                        <div class="card-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="card-content">
                            <h4>Registrar Empleado</h4>
                            <p>Gestionar personal del sistema</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Registrar Consulta -->
                    <a href="Views/Consultas/listar.php" class="menu-card cyan" data-module="consulta">
                        <div class="card-icon">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="card-content">
                            <h4>Registrar Consulta</h4>
                            <p>Registrar atenciones médicas</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Registrar Factura -->
                    <a href="Views/Facturas/listar.php" class="menu-card purple" data-module="factura">
                        <div class="card-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="card-content">
                            <h4>Registrar Factura</h4>
                            <p>Gestionar cobros y pagos</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Registrar Procedimiento -->
                    <a href="Views/Procedimientos/listar.php" class="menu-card indigo" data-module="procedimiento">
                        <div class="card-icon">
                            <i class="fas fa-syringe"></i>
                        </div>
                        <div class="card-content">
                            <h4>Registrar Procedimiento</h4>
                            <p>Cirugías y tratamientos especiales</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Registrar Vacuna -->
                    <a href="Views/Vacunas/listar.php" class="menu-card pink" data-module="vacuna">
                        <div class="card-icon">
                            <i class="fas fa-shield-virus"></i>
                        </div>
                        <div class="card-content">
                            <h4>Registrar Vacuna</h4>
                            <p>Control de vacunación</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Gestionar Cargos -->
                    <a href="Views/Cargos/listar.php" class="menu-card teal" data-module="cargo">
                        <div class="card-icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div class="card-content">
                            <h4>Gestionar Cargos</h4>
                            <p>Administrar roles del personal</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Reportes -->
                    <a href="Views/Reportes/index.php" class="menu-card red" data-module="reportes">
                        <div class="card-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="card-content">
                            <h4>Reportes</h4>
                            <p>Estadísticas y análisis del sistema</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 VetCare Plus - Sistema de Gestión Veterinaria | Desarrollado con ❤️</p>
    </footer>

    <!-- Modal de confirmación -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-sign-out-alt"></i>
                <h3>Cerrar Sesión</h3>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro que deseas salir del sistema?</p>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <a href="Logout.php" class="btn-confirm">Sí, Salir</a>
            </div>
        </div>
    </div>

    <!-- JavaScript del Dashboard -->
    <script src="Assets/Js/Dashboard.js"></script>
</body>
</html>