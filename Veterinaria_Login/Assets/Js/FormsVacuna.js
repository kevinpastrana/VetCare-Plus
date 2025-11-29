/**
 * FORMSVACUNA.JS - JavaScript para formularios de Vacunas
 * Sistema de Gestión Veterinaria
 */

// ============================================
// VALIDACIÓN DE FORMULARIOS
// ============================================

const form = document.getElementById('formVacuna');

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
    const campos = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    campos.forEach(campo => {
        if (!validarCampo(campo)) {
            esValido = false;
        }
    });
    
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
    
    // Validación de fecha de aplicación (no puede ser futura)
    if (campo.id === 'fecha_aplicacion' && valor) {
        const fechaAplicacion = new Date(valor);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        
        if (fechaAplicacion > hoy) {
            mostrarError(campo, 'La fecha de aplicación no puede ser futura');
            return false;
        }
    }
    
    // Validación de próxima aplicación (debe ser posterior a fecha de aplicación)
    if (campo.id === 'proxima_aplicacion' && valor) {
        const proximaAplicacion = new Date(valor);
        const fechaAplicacionInput = document.getElementById('fecha_aplicacion');
        
        if (fechaAplicacionInput && fechaAplicacionInput.value) {
            const fechaAplicacion = new Date(fechaAplicacionInput.value);
            
            if (proximaAplicacion <= fechaAplicacion) {
                mostrarError(campo, 'La próxima aplicación debe ser posterior a la fecha de aplicación');
                return false;
            }
        }
    }
    
    limpiarError(campo);
    return true;
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
    
    console.log('Enviando vacuna...'); // Debug
    
    try {
        const response = await fetch('Procesar.php', {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status); // Debug
        
        // Verificar si la respuesta es JSON válida
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            const textResponse = await response.text();
            console.error('Respuesta del servidor (no JSON):', textResponse);
            throw new Error('La respuesta del servidor no es JSON válida');
        }
        
        const data = await response.json();
        console.log('Respuesta del servidor:', data); // Debug
        
        if (data.success) {
            mostrarMensaje('¡Vacuna registrada exitosamente!', 'success');
            form.reset();
            
            // Redirigir después de 1.5 segundos
            setTimeout(() => {
                window.location.href = 'Listar.php';
            }, 1500);
        } else {
            mostrarMensaje(data.message || 'Error al registrar la vacuna', 'error');
            btnSubmit.innerHTML = btnText;
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('loading');
        }
    } catch (error) {
        console.error('Error completo:', error);
        mostrarMensaje('Error de conexión. Por favor revisa la consola del navegador.', 'error');
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

document.querySelectorAll('input, select, textarea').forEach(campo => {
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

// ============================================
// FORMATOS AUTOMÁTICOS
// ============================================

// Capitalizar nombre de vacuna
const nombreVacunaInput = document.getElementById('nombre_vacuna');
if (nombreVacunaInput) {
    nombreVacunaInput.addEventListener('blur', function() {
        this.value = this.value
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    });
}

// Capitalizar laboratorio
const laboratorioInput = document.getElementById('laboratorio');
if (laboratorioInput) {
    laboratorioInput.addEventListener('blur', function() {
        this.value = this.value
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    });
}

// Lote en mayúsculas
const loteInput = document.getElementById('lote');
if (loteInput) {
    loteInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
}

// Actualizar hint de próxima aplicación
const fechaAplicacionInput = document.getElementById('fecha_aplicacion');
const proximaAplicacionInput = document.getElementById('proxima_aplicacion');

if (fechaAplicacionInput && proximaAplicacionInput) {
    fechaAplicacionInput.addEventListener('change', function() {
        // Sugerir próxima aplicación a 1 mes
        if (this.value) {
            const fecha = new Date(this.value);
            fecha.setMonth(fecha.getMonth() + 1);
            proximaAplicacionInput.min = this.value;
        }
    });
}

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
    select.error,
    textarea.error {
        border-color: #dc2626 !important;
        animation: shake 0.3s;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .error-message {
        color: #dc2626;
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: block;
    }
    
    .form-message {
        padding: 1rem 1.25rem;
        border-radius: 8px;
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
`;
document.head.appendChild(style);

console.log('✅ Validación de formularios de Vacunas cargada');