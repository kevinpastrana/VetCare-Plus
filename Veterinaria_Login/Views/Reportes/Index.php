<?php
/**
 * INDEX.PHP - Módulo de Reportes
 * Sistema de Gestión Veterinaria VetCare Plus
 */

session_start();
require_once '../../Includes/functions.php';

// Verificar autenticación
requireLogin();

$user_name = $_SESSION['user_name'] ?? 'Usuario';
$user_cargo = $_SESSION['user_cargo'] ?? 'Sin cargo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Sistema Veterinaria</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../Assets/Css/Dashboard.css">
    <link rel="stylesheet" href="../../Assets/Css/Reportes.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../../Dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al Dashboard</span>
                </a>
            </div>
            <div class="nav-center">
                <h1 class="page-title">
                    <i class="fas fa-chart-bar"></i>
                    Reportes y Estadísticas
                </h1>
            </div>
            <div class="nav-right">
                <div class="user-badge">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($user_name); ?></span>
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
                    <h2>📊 Centro de Reportes</h2>
                    <p>Genera reportes detallados y estadísticas del sistema</p>
                </div>
            </section>

            <!-- Reportes Grid -->
            <section class="reportes-section">
                <h3 class="section-title">
                    <i class="fas fa-file-alt"></i>
                    Reportes Disponibles
                </h3>
                

                
                <div class="reportes-grid">
                    <!-- Reporte de Consultas -->
                    <div class="reporte-card blue">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <div class="card-info">
                                <h4>Consultas Médicas</h4>
                                <p>Reporte de consultas realizadas</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <p>Genera reportes de consultas por fecha, mascota o veterinario</p>
                        </div>
                        <div class="card-footer">
                            <a href="ReporteConsultas.php" class="btn-reporte">
                                <i class="fas fa-chart-line"></i>
                                Ver Reporte
                            </a>
                        </div>
                    </div>

                    <!-- Reporte General -->
                    <div class="reporte-card red">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="card-info">
                                <h4>Dashboard General</h4>
                                <p>Vista completa del sistema</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <p>Resumen de todas las estadísticas importantes</p>
                        </div>
                        <div class="card-footer">
                            <a href="ReporteGeneral.php" class="btn-reporte">
                                <i class="fas fa-tachometer-alt"></i>
                                Ver Reporte
                            </a>
                        </div>
                    </div>

                    <!-- Reporte de Facturación -->
                    <div class="reporte-card green">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="card-info">
                                <h4>Facturación</h4>
                                <p>Análisis de ingresos y pagos</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <p>Reportes de ingresos, facturas pendientes y pagadas</p>
                        </div>
                        <div class="card-footer">
                            <a href="ReporteFacturacion.php" class="btn-reporte">
                                <i class="fas fa-dollar-sign"></i>
                                Ver Reporte
                            </a>
                        </div>
                    </div>

                    <!-- Reporte de Mascotas -->
                    <div class="reporte-card orange">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-dog"></i>
                            </div>
                            <div class="card-info">
                                <h4>Mascotas Registradas</h4>
                                <p>Estadísticas de pacientes</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <p>Información por especie, raza y estado de salud</p>
                        </div>
                        <div class="card-footer">
                            <a href="ReporteMascotas.php" class="btn-reporte">
                                <i class="fas fa-paw"></i>
                                Ver Reporte
                            </a>
                        </div>
                    </div>

                    <!-- Reporte de Procedimientos -->
                    <div class="reporte-card purple">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-syringe"></i>
                            </div>
                            <div class="card-info">
                                <h4>Procedimientos</h4>
                                <p>Cirugías y tratamientos</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <p>Reporte de procedimientos realizados y programados</p>
                        </div>
                        <div class="card-footer">
                            <a href="ReporteProcedimientos.php" class="btn-reporte">
                                <i class="fas fa-procedures"></i>
                                Ver Reporte
                            </a>
                        </div>
                    </div>

                    <!-- Reporte de Vacunas -->
                    <div class="reporte-card pink">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-shield-virus"></i>
                            </div>
                            <div class="card-info">
                                <h4>Control de Vacunas</h4>
                                <p>Vacunación y seguimiento</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <p>Vacunas aplicadas, próximas y vencidas</p>
                        </div>
                        <div class="card-footer">
                            <a href="ReporteVacunas.php" class="btn-reporte">
                                <i class="fas fa-calendar-check"></i>
                                Ver Reporte
                            </a>
                        </div>
                    </div>

                    <!-- Reporte de Dueños -->
                    <div class="reporte-card cyan">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-info">
                                <h4>Propietarios</h4>
                                <p>Base de clientes</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <p>Información de dueños y sus mascotas</p>
                        </div>
                        <div class="card-footer">
                            <a href="ReporteDuenos.php" class="btn-reporte">
                                <i class="fas fa-user-friends"></i>
                                Ver Reporte
                            </a>
                        </div>
                    </div>

                    <!-- Reporte de Empleados -->
                    <div class="reporte-card indigo">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="card-info">
                                <h4>Personal</h4>
                                <p>Empleados y actividad</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <p>Información del personal y su desempeño</p>
                        </div>
                        <div class="card-footer">
                            <a href="ReporteEmpleados.php" class="btn-reporte">
                                <i class="fas fa-id-badge"></i>
                                Ver Reporte
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Accesos Rápidos -->
            <section class="quick-actions">
                <h3 class="section-title">
                    <i class="fas fa-bolt"></i>
                    Accesos Rápidos
                </h3>
                
                <div class="actions-grid">
                    <button class="action-btn" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        <span>Imprimir Página</span>
                    </button>
                    <button class="action-btn" onclick="exportarPDF()">
                        <i class="fas fa-file-pdf"></i>
                        <span>Exportar PDF</span>
                    </button>
                    <button class="action-btn" onclick="exportarExcel()">
                        <i class="fas fa-file-excel"></i>
                        <span>Exportar Excel</span>
                    </button>
                    <button class="action-btn" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i>
                        <span>Actualizar</span>
                    </button>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 VetCare Plus - Sistema de Gestión Veterinaria | Desarrollado con ❤️</p>
    </footer>

    <!-- JavaScript -->
    <script src="../../Assets/Js/Reportes.js"></script>
</body>
</html>