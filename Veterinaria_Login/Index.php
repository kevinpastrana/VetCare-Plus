<?php
/**
 * INDEX.PHP - Página de Login
 * Sistema de Gestión Veterinaria
 */

session_start();

// Si ya está autenticado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: Dashboard.php');
    exit();
}

// Obtener mensaje de error si existe
$error_message = '';
if (isset($_SESSION['error_login'])) {
    $error_message = $_SESSION['error_login'];
    unset($_SESSION['error_login']);
}

// Verificar si viene de logout
$logout_message = '';
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $logout_message = 'Has cerrado sesión correctamente';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de gestión veterinaria - Acceso al panel administrativo">
    <title>Login - Clínica Veterinaria VetCare Plus</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS del Login -->
    <link rel="stylesheet" href="Assets/Css/Login.css">
    
    <style>
        /* Estilos adicionales para alertas */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert.show {
            display: flex;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .demo-credentials {
            margin-top: 20px;
            padding: 15px;
            background: #f0f9ff;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #0369a1;
            border-left: 4px solid #0ea5e9;
        }

        .demo-credentials strong {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .demo-credentials code {
            background: #dbeafe;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Sección Izquierda - Branding -->
        <div class="brand-section">
            <div class="brand-logo">
                <i class="fas fa-paw"></i>
            </div>
            <h1 class="brand-title">VetCare Plus</h1>
            <p class="brand-subtitle">
                Sistema Integral de Gestión Veterinaria
            </p>
            <div class="brand-features">
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Gestión completa de pacientes</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Historial médico digitalizado</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Agenda de citas inteligente</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Facturación automatizada</span>
                </div>
            </div>
        </div>

        <!-- Sección Derecha - Formulario -->
        <div class="form-section">
            <div class="form-header">
                <h2>Iniciar Sesión</h2>
                <p>Ingresa tus credenciales para acceder al sistema</p>
            </div>

            <!-- Mensaje de error -->
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error show">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
            <?php endif; ?>

            <!-- Mensaje de logout exitoso -->
            <?php if (!empty($logout_message)): ?>
            <div class="alert alert-success show">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($logout_message); ?></span>
            </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="Login.php" novalidate>
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-control" 
                            placeholder="Ingresa tu usuario"
                            autocomplete="username"
                            required
                            aria-describedby="usernameError"
                        >
                    </div>
                    <span class="error-message" id="usernameError" role="alert"></span>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Ingresa tu contraseña"
                            autocomplete="current-password"
                            required
                            aria-describedby="passwordError"
                        >
                    </div>
                    <span class="error-message" id="passwordError" role="alert"></span>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Recordarme</span>
                    </label>
                    <a href="#" class="forgot-password" onclick="alert('Funcionalidad en desarrollo'); return false;">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    Iniciar Sesión
                </button>
            </form>

            <div class="demo-credentials">
                <strong><i class="fas fa-info-circle"></i> Credenciales de prueba:</strong>
                Usuario: <code>admin</code> | Contraseña: <code>admin123</code>
            </div>
        </div>
    </div>

    <!-- JavaScript del Login -->
    <script src="Assets/Js/Login.js"></script>
</body>
</html>