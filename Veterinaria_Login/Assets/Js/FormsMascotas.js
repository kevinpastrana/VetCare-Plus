/**
 * FORMSMASCOTAS.JS - JavaScript para formularios de Mascotas
 * Sistema de Gestión Veterinaria
 */

// ============================================
// VALIDACIÓN DE FORMULARIOS
// ============================================

const form = document.getElementById('formMascota');

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
    
    // Validación de peso
    if (campo.id === 'peso' && valor) {
        const peso = parseFloat(valor);
        if (isNaN(peso) || peso <= 0 || peso > 999.9) {
            mostrarError(campo, 'Ingresa un peso válido (0.1 - 999.9 kg)');
            return false;
        }
    }
    
    // Validación de fecha de nacimiento
    if (campo.id === 'fecha_nacimiento' && valor) {
        const fechaNac = new Date(valor);
        const hoy = new Date();
        if (fechaNac > hoy) {
            mostrarError(campo, 'La fecha de nacimiento no puede ser futura');
            return false;
        }
    }
    
    // Validación de fecha de ingreso
    if (campo.id === 'fecha_ingreso' && valor) {
        const fechaIng = new Date(valor);
        const hoy = new Date();
        if (fechaIng > hoy) {
            mostrarError(campo, 'La fecha de ingreso no puede ser futura');
            return false;
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
            mostrarMensaje('¡Mascota registrada exitosamente!', 'success');
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

// ============================================
// FORMATOS AUTOMÁTICOS
// ============================================

// Formato de peso (solo números y punto decimal)
const pesoInput = document.getElementById('peso');
if (pesoInput) {
    pesoInput.addEventListener('input', function(e) {
        // Permitir solo números y un punto decimal
        let valor = this.value;
        valor = valor.replace(/[^0-9.]/g, '');
        
        // Permitir solo un punto decimal
        const partes = valor.split('.');
        if (partes.length > 2) {
            valor = partes[0] + '.' + partes.slice(1).join('');
        }
        
        this.value = valor;
    });
}

// Capitalizar nombre de mascota
const nombreInput = document.getElementById('nombre');
if (nombreInput) {
    nombreInput.addEventListener('blur', function() {
        this.value = this.value
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    });
}

// Capitalizar especie y raza
const especieInput = document.getElementById('especie');
const razaInput = document.getElementById('raza');
[especieInput, razaInput].forEach(input => {
    if (input) {
        input.addEventListener('blur', function() {
            this.value = this.value
                .toLowerCase()
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        });
    }
});

// Preview de imagen
const fotoInput = document.getElementById('foto');
if (fotoInput) {
    fotoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validar tamaño (5MB máximo)
            if (file.size > 5 * 1024 * 1024) {
                mostrarError(this, 'La imagen no debe superar los 5MB');
                this.value = '';
                return;
            }
            
            // Validar tipo
            const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!tiposPermitidos.includes(file.type)) {
                mostrarError(this, 'Solo se permiten imágenes JPG, PNG o GIF');
                this.value = '';
                return;
            }
            
            limpiarError(this);
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
`;
document.head.appendChild(style);

console.log('✅ Validación de formularios de Mascotas cargada');