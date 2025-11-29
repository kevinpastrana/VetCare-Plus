/**
 * FORMSFACTURA.JS - JavaScript para formularios de Facturas
 * Sistema de Gestión Veterinaria
 */

// ============================================
// VALIDACIÓN DE FORMULARIOS
// ============================================

const form = document.getElementById('formFactura');

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
    
    // Validación especial: al menos un detalle debe estar agregado
    const detalles = document.querySelectorAll('[name*="detalles"][name*="[concepto]"]');
    if (detalles.length === 0) {
        mostrarMensaje('Debes agregar al menos un concepto a la factura', 'error');
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
    
    // Validación de fecha de factura (no puede ser futura)
    if (campo.id === 'fecha_factura' && valor) {
        const fechaFactura = new Date(valor);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        
        if (fechaFactura > hoy) {
            mostrarError(campo, 'La fecha de factura no puede ser futura');
            return false;
        }
    }
    
    // Validación de fecha de vencimiento (debe ser igual o posterior a fecha factura)
    if (campo.id === 'fecha_vencimiento' && valor) {
        const fechaVencimiento = new Date(valor);
        const fechaFacturaInput = document.getElementById('fecha_factura');
        if (fechaFacturaInput && fechaFacturaInput.value) {
            const fechaFactura = new Date(fechaFacturaInput.value);
            
            if (fechaVencimiento < fechaFactura) {
                mostrarError(campo, 'La fecha de vencimiento debe ser igual o posterior a la fecha de factura');
                return false;
            }
        }
    }
    
    // Validación de descuento
    if (campo.id === 'descuento' && valor) {
        const descuento = parseFloat(valor);
        if (isNaN(descuento) || descuento < 0 || descuento > 100) {
            mostrarError(campo, 'El descuento debe estar entre 0 y 100%');
            return false;
        }
    }
    
    // Validación de cantidad en detalles
    if (campo.name && campo.name.includes('[cantidad]') && valor) {
        const cantidad = parseInt(valor);
        if (isNaN(cantidad) || cantidad <= 0) {
            mostrarError(campo, 'La cantidad debe ser mayor a 0');
            return false;
        }
    }
    
    // Validación de precio en detalles
    if (campo.name && campo.name.includes('[precio_unitario]') && valor) {
        const precio = parseFloat(valor);
        if (isNaN(precio) || precio <= 0) {
            mostrarError(campo, 'El precio debe ser mayor a 0');
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
    
    // Convertir FormData a objeto para enviar detalles como array
    const data = {};
    const detalles = [];
    
    formData.forEach((value, key) => {
        if (key.includes('detalles[')) {
            // Extraer índice y campo del detalle
            const match = key.match(/detalles\[(\d+)\]\[(\w+)\]/);
            if (match) {
                const index = match[1];
                const field = match[2];
                
                if (!detalles[index]) {
                    detalles[index] = {};
                }
                detalles[index][field] = value;
            }
        } else {
            data[key] = value;
        }
    });
    
    // Filtrar detalles vacíos y agregar al objeto
    data.detalles = detalles.filter(d => d && d.concepto);
    data.action = 'registrar';
    
    // Validar que haya al menos un detalle
    if (data.detalles.length === 0) {
        mostrarMensaje('Debes agregar al menos un concepto a la factura', 'error');
        return;
    }
    
    console.log('Datos a enviar:', data); // Para debugging
    
    // Mostrar estado de carga
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    btnSubmit.disabled = true;
    btnSubmit.classList.add('loading');
    
    try {
        const response = await fetch('Procesar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        console.log('Response status:', response.status); // Para debugging
        
        // Verificar si la respuesta es JSON válida
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            const textResponse = await response.text();
            console.error('Respuesta del servidor (no JSON):', textResponse);
            throw new Error('La respuesta del servidor no es JSON válida');
        }
        
        const result = await response.json();
        console.log('Respuesta del servidor:', result); // Para debugging
        
        if (result.success) {
            mostrarMensaje('¡Factura registrada exitosamente!', 'success');
            
            // Limpiar formulario
            form.reset();
            
            // Limpiar detalles
            const container = document.getElementById('detalles-container');
            if (container) {
                container.innerHTML = '';
            }
            
            // Agregar un detalle vacío
            if (typeof agregarDetalle === 'function') {
                agregarDetalle();
            }
            
            // Redirigir después de 1.5 segundos
            setTimeout(() => {
                window.location.href = 'Listar.php';
            }, 1500);
        } else {
            mostrarMensaje(result.message || 'Error al registrar la factura', 'error');
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
// GESTIÓN DE ITEMS DE FACTURA
// ============================================

let contadorDetalles = 0;

// Agregar primer detalle al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    if (typeof agregarDetalle === 'function') {
        agregarDetalle();
    }
});

// Función para agregar detalle (puede recibir datos existentes para modo edición)
window.agregarDetalle = function(datos = null) {
    contadorDetalles++;
    const container = document.getElementById('detalles-container');
    
    if (!container) {
        console.error('No se encontró el contenedor de detalles');
        return;
    }
    
    const concepto = datos ? datos.concepto : '';
    const descripcion = datos ? datos.descripcion : '';
    const cantidad = datos ? datos.cantidad : 1;
    const precio = datos ? datos.precio_unitario : 0;
    const subtotal = datos ? datos.subtotal : 0;
    
    const detalleHTML = `
        <div class="detalle-item" id="detalle-${contadorDetalles}" style="border: 2px solid #e2e8f0; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem; position: relative;">
            <button type="button" class="btn-remove-detalle" onclick="eliminarDetalle(${contadorDetalles})" 
                    style="position: absolute; top: 10px; right: 10px; background: #fee2e2; color: #dc2626; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Concepto *</label>
                    <input type="text" name="detalles[${contadorDetalles}][concepto]" 
                           value="${concepto}" placeholder="Ej: Consulta veterinaria" required>
                </div>
                <div class="form-group">
                    <label>Cantidad *</label>
                    <input type="number" name="detalles[${contadorDetalles}][cantidad]" 
                           value="${cantidad}" min="1" onchange="calcularSubtotalDetalle(${contadorDetalles})" required>
                </div>
                <div class="form-group">
                    <label>Precio Unitario *</label>
                    <input type="number" name="detalles[${contadorDetalles}][precio_unitario]" 
                           value="${precio}" step="0.01" onchange="calcularSubtotalDetalle(${contadorDetalles})" required>
                </div>
                <div class="form-group">
                    <label>Subtotal</label>
                    <input type="number" name="detalles[${contadorDetalles}][subtotal]" 
                           value="${subtotal}" step="0.01" readonly style="font-weight: 600; color: #6366f1; background: #f3f4f6;">
                </div>
            </div>
            <div class="form-row" style="margin-top: 1rem;">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Descripción (Opcional)</label>
                    <textarea name="detalles[${contadorDetalles}][descripcion]" 
                              placeholder="Detalles adicionales del servicio o producto..." 
                              style="min-height: 80px; width: 100%;">${descripcion}</textarea>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', detalleHTML);
    
    if (!datos) {
        calcularTotales();
    }
}

// Función para eliminar un detalle
window.eliminarDetalle = function(id) {
    const detalle = document.getElementById(`detalle-${id}`);
    if (detalle) {
        if (confirm('¿Eliminar este concepto?')) {
            detalle.remove();
            calcularTotales();
        }
    }
}

// Función para calcular subtotal de un detalle específico
window.calcularSubtotalDetalle = function(id) {
    const detalle = document.getElementById(`detalle-${id}`);
    if (!detalle) return;
    
    const cantidad = parseFloat(detalle.querySelector('[name*="[cantidad]"]').value) || 0;
    const precio = parseFloat(detalle.querySelector('[name*="[precio_unitario]"]').value) || 0;
    const subtotal = cantidad * precio;
    
    detalle.querySelector('[name*="[subtotal]"]').value = subtotal.toFixed(2);
    calcularTotales();
}

// Función para calcular totales generales
window.calcularTotales = function() {
    let subtotalGeneral = 0;
    
    // Sumar todos los subtotales de los detalles
    document.querySelectorAll('[name*="[subtotal]"]').forEach(input => {
        subtotalGeneral += parseFloat(input.value) || 0;
    });
    
    const descuentoInput = document.getElementById('descuento');
    const impuestoInput = document.getElementById('impuesto');
    
    const descuento = descuentoInput ? (parseFloat(descuentoInput.value) || 0) : 0;
    const impuesto = impuestoInput ? (parseFloat(impuestoInput.value) || 0) : 0;
    
    const subtotalConDescuento = subtotalGeneral - descuento;
    const total = subtotalConDescuento + impuesto;
    
    const subtotalElement = document.getElementById('subtotal');
    const totalElement = document.getElementById('total');
    
    if (subtotalElement) subtotalElement.value = subtotalGeneral.toFixed(2);
    if (totalElement) totalElement.value = total.toFixed(2);
    
    calcularSaldo();
}

// Función para calcular saldo - CORREGIDO AQUÍ
window.calcularSaldo = function() {
    const totalElement = document.getElementById('total');
    const pagadoElement = document.getElementById('pagado');
    const saldoElement = document.getElementById('saldo');
    const estadoSelect = document.getElementById('estado');
    
    if (!totalElement || !pagadoElement || !saldoElement) return;
    
    const total = parseFloat(totalElement.value) || 0;
    const pagado = parseFloat(pagadoElement.value) || 0;
    const saldo = total - pagado;
    
    saldoElement.value = saldo.toFixed(2);
    
    // Actualizar estado automáticamente - CORREGIDO: Usar "Pagada" en lugar de "Pagado"
    if (estadoSelect) {
        if (saldo <= 0 && total > 0) {
            estadoSelect.value = 'Pagada'; // ← CAMBIO AQUÍ: de 'Pagado' a 'Pagada'
        } else if (saldo > 0) {
            estadoSelect.value = 'Pendiente';
        }
    }
}

// ============================================
// FORMATOS AUTOMÁTICOS
// ============================================

// Formato de descuento (solo números)
const descuentoInput = document.getElementById('descuento');
if (descuentoInput) {
    descuentoInput.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, '');
        if (parseInt(valor) > 100) valor = '100';
        this.value = valor;
    });
    
    descuentoInput.addEventListener('change', function() {
        calcularTotales();
    });
}

// Formato de impuesto
const impuestoInput = document.getElementById('impuesto');
if (impuestoInput) {
    impuestoInput.addEventListener('change', function() {
        calcularTotales();
    });
}

// Formato de pagado
const pagadoInput = document.getElementById('pagado');
if (pagadoInput) {
    pagadoInput.addEventListener('change', function() {
        calcularSaldo();
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
    
    .btn-remove-detalle:hover {
        background: #fca5a5 !important;
    }
`;
document.head.appendChild(style);

console.log('✅ Validación de formularios de Facturas cargada');