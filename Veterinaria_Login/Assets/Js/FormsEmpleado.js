/**
 * FORMSEMPLEADO.JS - JavaScript para formularios de Empleados
 * Sistema de Gestión Veterinaria
 */

// ============================================
// VALIDACIÓN DE FORMULARIOS
// ============================================

const form = document.getElementById('formEmpleado');

if (form) {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (validarFormulario()) {
            await enviarFormulario();
        }
    });
}

/**
 * Validar todos los campos del formulario
 */
function validarFormulario() {
    let esValido = true;
    const campos = form.querySelectorAll('input[required], select[required]');
    
    campos.forEach(campo => {
        if (!validarCampo(campo)) {
            esValido = false;
        }
    });
    
    // Validar que las contraseñas coincidan
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        mostrarError(document.getElementById('confirm_password'), 'Las contraseñas no coinciden');
        esValido = false;
    }
    
    return esValido;
}

/**
 * Validar un campo individual
 */
function validarCampo(campo) {
    const valor = campo.value.trim();
    
    // Validación básica de requerido
    if (campo.hasAttribute('required') && valor === '') {
        mostrarError(campo, 'Este campo es obligatorio');
        return false;
    }
    
    // Validaciones específicas por tipo
    switch(campo.type) {
        case 'email':
            if (!validarEmail(valor)) {
                mostrarError(campo, 'Ingresa un correo válido');
                return false;
            }
            break;
            
        case 'tel':
            if (!validarTelefono(valor)) {
                mostrarError(campo, 'Ingresa un teléfono válido (10 dígitos iniciando con 3)');
                return false;
            }
            break;
            
        case 'password':
            if (campo.id === 'password' && valor.length < 8) {
                mostrarError(campo, 'La contraseña debe tener al menos 8 caracteres');
                return false;
            }
            break;
    }
    
    // Validación de cédula
    if (campo.id === 'cedula' && !validarCedula(valor)) {
        mostrarError(campo, 'La cédula debe tener entre 6 y 10 dígitos');
        return false;
    }
    
    // Validación de fecha de nacimiento (mayor de 18 años)
    if (campo.id === 'fecha_nacimiento' && valor) {
        const fechaNac = new Date(valor);
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNac.getFullYear();
        const mes = hoy.getMonth() - fechaNac.getMonth();
        
        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
            edad--;
        }
        
        if (edad < 18) {
            mostrarError(campo, 'El empleado debe ser mayor de 18 años');
            return false;
        }
    }
    
    limpiarError(campo);
    return true;
}

/**
 * Validar email
 */
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validar teléfono colombiano
 */
function validarTelefono(telefono) {
    const regex = /^3\d{9}$/;
    return regex.test(telefono);
}

/**
 * Validar cédula
 */
function validarCedula(cedula) {
    const regex = /^\d{6,10}$/;
    return regex.test(cedula);
}

/**
 * Mostrar mensaje de error en campo
 */
function mostrarError(campo, mensaje) {
    campo.classList.add('error');
    
    // Buscar o crear elemento de error
    let errorElement = campo.parentElement.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('span');
        errorElement.className = 'error-message';
        campo.parentElement.appendChild(errorElement);
    }
    
    errorElement.textContent = mensaje;
    errorElement.style.display = 'block';
}

/**
 * Limpiar mensaje de error
 */
function limpiarError(campo) {
    campo.classList.remove('error');
    const errorElement = campo.parentElement.querySelector('.error-message');
    if (errorElement) {
        errorElement.style.display = 'none';
    }
}

/**
 * Enviar formulario via AJAX
 */
async function enviarFormulario() {
    const btnSubmit = form.querySelector('button[type="submit"]');
    const btnText = btnSubmit.innerHTML;
    const formData = new FormData(form);
    
    // Mostrar estado de carga
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    btnSubmit.disabled = true;
    btnSubmit.classList.add('loading');
    
    try {
        const response = await fetch('Procesar.php', {
            method: 'POST',
            body: formData
        });
        
        // Verificar si la respuesta es JSON válida
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error('La respuesta del servidor no es JSON válida');
        }
        
        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje('¡Empleado registrado exitosamente!', 'success');
            form.reset();
            
            // Redirigir después de 1.5 segundos
            setTimeout(() => {
                window.location.href = '../../Dashboard.php';
            }, 1500);
        } else {
            mostrarMensaje(data.message || 'Error al registrar', 'error');
            btnSubmit.innerHTML = btnText;
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('loading');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión. Intenta nuevamente.', 'error');
        btnSubmit.innerHTML = btnText;
        btnSubmit.disabled = false;
        btnSubmit.classList.remove('loading');
    }
}

/**
 * Mostrar mensaje de éxito/error
 */
function mostrarMensaje(mensaje, tipo) {
    // Eliminar mensaje anterior si existe
    const mensajeAnterior = document.querySelector('.form-message');
    if (mensajeAnterior) {
        mensajeAnterior.remove();
    }
    
    // Crear nuevo mensaje
    const mensajeElement = document.createElement('div');
    mensajeElement.className = `form-message ${tipo}`;
    mensajeElement.innerHTML = `
        <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${mensaje}</span>
    `;
    
    // Insertar antes del formulario
    const formCard = document.querySelector('.form-card');
    if (formCard) {
        formCard.insertBefore(mensajeElement, formCard.firstChild);
    } else {
        form.insertBefore(mensajeElement, form.firstChild);
    }
    
    // Scroll al mensaje
    mensajeElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Auto-ocultar después de 5 segundos si es error
    if (tipo === 'error') {
        setTimeout(() => {
            mensajeElement.remove();
        }, 5000);
    }
}

// ============================================
// VALIDACIÓN EN TIEMPO REAL
// ============================================

document.querySelectorAll('input, select').forEach(campo => {
    campo.addEventListener('blur', function() {
        if (this.value.trim() !== '') {
            validarCampo(this);
        }
    });
    
    campo.addEventListener('input', function() {
        if (this.classList.contains('error')) {
            limpiarError(this);
        }
    });
});

// Validación en tiempo real de contraseñas coincidentes
const confirmPasswordInput = document.getElementById('confirm_password');
if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword && password !== confirmPassword) {
            mostrarError(this, 'Las contraseñas no coinciden');
        } else {
            limpiarError(this);
        }
    });
}

// ============================================
// FORMATOS AUTOMÁTICOS
// ============================================

// Formato de teléfono
const telefonoInput = document.getElementById('telefono');
if (telefonoInput) {
    telefonoInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '').substring(0, 10);
    });
}

// Formato de cédula
const cedulaInput = document.getElementById('cedula');
if (cedulaInput) {
    cedulaInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '').substring(0, 10);
    });
}

// Capitalizar nombres y apellidos
const nombreInputs = document.querySelectorAll('[id*="nombre"], [id*="apellido"]');
nombreInputs.forEach(input => {
    input.addEventListener('blur', function() {
        this.value = this.value
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    });
});

// Mostrar/ocultar contraseñas
const passwordInputs = document.querySelectorAll('input[type="password"]');
passwordInputs.forEach(input => {
    // Crear botón para mostrar/ocultar contraseña
    const toggleButton = document.createElement('button');
    toggleButton.type = 'button';
    toggleButton.className = 'toggle-password';
    toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
    toggleButton.style.cssText = 'position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;';
    
    const parent = input.parentElement;
    parent.style.position = 'relative';
    parent.appendChild(toggleButton);
    
    toggleButton.addEventListener('click', function() {
        if (input.type === 'password') {
            input.type = 'text';
            this.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            input.type = 'password';
            this.innerHTML = '<i class="fas fa-eye"></i>';
        }
    });
});

// ============================================
// CONFIRMACIÓN AL SALIR
// ============================================

let formModificado = false;

if (form) {
    form.addEventListener('input', () => {
        formModificado = true;
    });
    
    window.addEventListener('beforeunload', (e) => {
        if (formModificado) {
            e.preventDefault();
            e.returnValue = '¿Estás seguro de salir? Los cambios no guardados se perderán.';
        }
    });
    
    // No mostrar alerta al enviar el formulario
    form.addEventListener('submit', () => {
        formModificado = false;
    });
}

// ============================================
// ESTILOS ADICIONALES PARA ERRORES Y MENSAJES
// ============================================

const style = document.createElement('style');
style.textContent = `
    input.error,
    select.error {
        border-color: var(--danger) !important;
        animation: shake 0.3s;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .error-message {
        color: var(--danger);
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: block;
    }
    
    .form-message {
        padding: 1rem 1.25rem;
        border-radius: var(--radius-md);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .form-message.success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }
    
    .form-message.error {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }
    
    .form-message i {
        font-size: 1.25rem;
    }
    
    button.loading {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .toggle-password:hover {
        color: #333 !important;
    }
`;
document.head.appendChild(style);

console.log('✅ Validación de formularios de Empleados cargada');