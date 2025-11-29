/**
 * LOGIN.JS - Validación del formulario de login (Versión Simple)
 * Gestiona validaciones del lado del cliente
 */

// Elementos del DOM
const loginForm = document.getElementById('loginForm');
const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const loginBtn = document.getElementById('loginBtn');

/**
 * Mostrar error en un campo específico
 */
function showError(input, message) {
    const errorElement = document.getElementById(input.id + 'Error');
    input.classList.add('error');
    errorElement.textContent = message;
    errorElement.classList.add('show');
    input.setAttribute('aria-invalid', 'true');
}

/**
 * Limpiar error de un campo
 */
function clearError(input) {
    const errorElement = document.getElementById(input.id + 'Error');
    input.classList.remove('error');
    errorElement.classList.remove('show');
    input.removeAttribute('aria-invalid');
}

/**
 * Validar campo de usuario
 */
function validateUsername() {
    const value = usernameInput.value.trim();
    
    if (value === '') {
        showError(usernameInput, 'El campo usuario es obligatorio');
        return false;
    }
    
    if (value.length < 3) {
        showError(usernameInput, 'El usuario debe tener al menos 3 caracteres');
        return false;
    }
    
    clearError(usernameInput);
    return true;
}

/**
 * Validar campo de contraseña
 */
function validatePassword() {
    const value = passwordInput.value;
    
    if (value === '') {
        showError(passwordInput, 'El campo contraseña es obligatorio');
        return false;
    }
    
    if (value.length < 3) {
        showError(passwordInput, 'La contraseña debe tener al menos 3 caracteres');
        return false;
    }
    
    clearError(passwordInput);
    return true;
}

/**
 * Manejar envío del formulario
 */
loginForm.addEventListener('submit', function(e) {
    // Validar todos los campos
    const isUsernameValid = validateUsername();
    const isPasswordValid = validatePassword();
    
    if (!isUsernameValid || !isPasswordValid) {
        e.preventDefault();
        return false;
    }
    
    // Si todo está bien, mostrar loading
    loginBtn.disabled = true;
    loginBtn.textContent = 'Iniciando sesión...';
    
    // El formulario se enviará normalmente (sin AJAX)
    return true;
});

/**
 * Validación en tiempo real
 */
usernameInput.addEventListener('input', () => {
    if (usernameInput.value.trim() !== '') {
        clearError(usernameInput);
    }
});

passwordInput.addEventListener('input', () => {
    if (passwordInput.value !== '') {
        clearError(passwordInput);
    }
});

/**
 * Validar al perder el foco (blur)
 */
usernameInput.addEventListener('blur', validateUsername);
passwordInput.addEventListener('blur', validatePassword);

/**
 * Prevenir pegado de espacios en usuario
 */
usernameInput.addEventListener('paste', (e) => {
    setTimeout(() => {
        usernameInput.value = usernameInput.value.trim();
    }, 10);
});

/**
 * Recuperar usuario guardado si existe
 */
window.addEventListener('DOMContentLoaded', () => {
    const savedUsername = localStorage.getItem('remember_username');
    if (savedUsername) {
        usernameInput.value = savedUsername;
        document.getElementById('remember').checked = true;
        passwordInput.focus();
    } else {
        usernameInput.focus();
    }
    
    // Guardar usuario si marca "recordarme"
    loginForm.addEventListener('submit', () => {
        const rememberCheckbox = document.getElementById('remember');
        if (rememberCheckbox.checked) {
            localStorage.setItem('remember_username', usernameInput.value);
        } else {
            localStorage.removeItem('remember_username');
        }
    });
});

/**
 * Detectar si Caps Lock está activado
 */
passwordInput.addEventListener('keyup', (e) => {
    if (e.getModifierState && e.getModifierState('CapsLock')) {
        showError(passwordInput, '⚠️ Mayúsculas activadas');
    }
});

/**
 * Auto-ocultar alertas después de 5 segundos
 */
document.querySelectorAll('.alert').forEach(alert => {
    if (alert.classList.contains('show')) {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    }
});

console.log('✅ Sistema de login cargado');