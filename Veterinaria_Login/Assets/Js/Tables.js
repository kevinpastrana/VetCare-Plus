/**
 * TABLES.JS - Funcionalidad para tablas de datos
 * Sistema de Gestión Veterinaria
 */

// ============================================
// VARIABLES GLOBALES
// ============================================
let idEliminar = null;
let registrosPorPagina = 10;
let paginaActual = 1;
let datosOriginales = [];

// ============================================
// BÚSQUEDA EN TABLA
// ============================================
const searchInput = document.getElementById('searchInput');
const tabla = document.getElementById('tablaDuenos');

if (searchInput && tabla) {
    // Guardar datos originales
    const tbody = tabla.querySelector('tbody');
    const filas = Array.from(tbody.querySelectorAll('tr'));
    datosOriginales = filas.map(fila => ({
        elemento: fila,
        texto: fila.textContent.toLowerCase()
    }));

    searchInput.addEventListener('input', function() {
        const busqueda = this.value.toLowerCase().trim();
        
        if (busqueda === '') {
            // Mostrar todas las filas
            datosOriginales.forEach(dato => {
                dato.elemento.style.display = '';
            });
            actualizarContador(datosOriginales.length);
        } else {
            // Filtrar filas
            let visibles = 0;
            datosOriginales.forEach(dato => {
                if (dato.texto.includes(busqueda)) {
                    dato.elemento.style.display = '';
                    visibles++;
                } else {
                    dato.elemento.style.display = 'none';
                }
            });
            actualizarContador(visibles);
        }
        
        // Resetear a página 1
        paginaActual = 1;
        aplicarPaginacion();
    });
}

/**
 * Actualizar contador de registros
 */
function actualizarContador(cantidad) {
    const contador = document.getElementById('totalRegistros');
    if (contador) {
        contador.textContent = cantidad;
    }
}

// ============================================
// ORDENAR TABLA
// ============================================
let ordenAscendente = true;
let columnaOrdenada = -1;

function ordenarTabla(columna) {
    if (!tabla) return;
    
    const tbody = tabla.querySelector('tbody');
    const filas = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
    
    // Cambiar dirección si es la misma columna
    if (columna === columnaOrdenada) {
        ordenAscendente = !ordenAscendente;
    } else {
        ordenAscendente = true;
        columnaOrdenada = columna;
    }
    
    // Ordenar filas
    filas.sort((a, b) => {
        const celdaA = a.cells[columna].textContent.trim();
        const celdaB = b.cells[columna].textContent.trim();
        
        // Intentar comparar como números
        const numA = parseFloat(celdaA.replace(/[^0-9.-]/g, ''));
        const numB = parseFloat(celdaB.replace(/[^0-9.-]/g, ''));
        
        if (!isNaN(numA) && !isNaN(numB)) {
            return ordenAscendente ? numA - numB : numB - numA;
        }
        
        // Comparar como texto
        return ordenAscendente 
            ? celdaA.localeCompare(celdaB)
            : celdaB.localeCompare(celdaA);
    });
    
    // Reordenar en el DOM
    filas.forEach(fila => tbody.appendChild(fila));
    
    // Actualizar iconos de ordenamiento
    const encabezados = tabla.querySelectorAll('th');
    encabezados.forEach((th, index) => {
        const icono = th.querySelector('i');
        if (icono) {
            if (index === columna) {
                icono.className = ordenAscendente 
                    ? 'fas fa-sort-up' 
                    : 'fas fa-sort-down';
            } else {
                icono.className = 'fas fa-sort';
            }
        }
    });
}

// ============================================
// PAGINACIÓN
// ============================================
function cambiarPagina(direccion) {
    if (!tabla) return;
    
    const tbody = tabla.querySelector('tbody');
    const filasVisibles = Array.from(tbody.querySelectorAll('tr')).filter(
        fila => fila.style.display !== 'none'
    );
    
    const totalPaginas = Math.ceil(filasVisibles.length / registrosPorPagina);
    
    paginaActual += direccion;
    
    if (paginaActual < 1) paginaActual = 1;
    if (paginaActual > totalPaginas) paginaActual = totalPaginas;
    
    aplicarPaginacion();
}

function aplicarPaginacion() {
    if (!tabla) return;
    
    const tbody = tabla.querySelector('tbody');
    const filasVisibles = Array.from(tbody.querySelectorAll('tr')).filter(
        fila => fila.style.display !== 'none'
    );
    
    const totalPaginas = Math.ceil(filasVisibles.length / registrosPorPagina);
    const inicio = (paginaActual - 1) * registrosPorPagina;
    const fin = inicio + registrosPorPagina;
    
    // Mostrar/ocultar filas según la página
    filasVisibles.forEach((fila, index) => {
        if (index >= inicio && index < fin) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
    
    // Actualizar info de paginación
    const paginaActualEl = document.getElementById('paginaActual');
    const totalPaginasEl = document.getElementById('totalPaginas');
    
    if (paginaActualEl) paginaActualEl.textContent = paginaActual;
    if (totalPaginasEl) totalPaginasEl.textContent = totalPaginas || 1;
}

// Aplicar paginación inicial
window.addEventListener('DOMContentLoaded', () => {
    aplicarPaginacion();
});

// ============================================
// MODAL VER DETALLE
// ============================================
async function verDetalle(id) {
    const modal = document.getElementById('modalDetalle');
    const content = document.getElementById('detalleContent');
    
    if (!modal || !content) return;
    
    // Mostrar modal con loading
    content.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando información...</p>
        </div>
    `;
    modal.classList.add('show');
    
    try {
        // Obtener datos del dueño
        const response = await fetch(`obtener_detalle.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const dueno = data.dueno;
            content.innerHTML = `
                <div class="detalle-grid">
                    <div class="detalle-section">
                        <h4><i class="fas fa-user"></i> Información Personal</h4>
                        <div class="detalle-item">
                            <span class="label">Nombre Completo:</span>
                            <span class="value">${dueno.nombre_completo}</span>
                        </div>
                        <div class="detalle-item">
                            <span class="label">Cédula:</span>
                            <span class="value">${dueno.cedula}</span>
                        </div>
                        <div class="detalle-item">
                            <span class="label">Fecha de Nacimiento:</span>
                            <span class="value">${dueno.fecha_nacimiento}</span>
                        </div>
                        <div class="detalle-item">
                            <span class="label">Género:</span>
                            <span class="value">${dueno.genero}</span>
                        </div>
                        <div class="detalle-item">
                            <span class="label">Ocupación:</span>
                            <span class="value">${dueno.ocupacion || 'No especificada'}</span>
                        </div>
                    </div>
                    
                    <div class="detalle-section">
                        <h4><i class="fas fa-address-book"></i> Información de Contacto</h4>
                        <div class="detalle-item">
                            <span class="label">Teléfono:</span>
                            <span class="value"><i class="fas fa-phone text-success"></i> ${dueno.telefono}</span>
                        </div>
                        <div class="detalle-item">
                            <span class="label">Correo:</span>
                            <span class="value"><i class="fas fa-envelope text-primary"></i> ${dueno.correo_electronico}</span>
                        </div>
                        <div class="detalle-item">
                            <span class="label">Ciudad:</span>
                            <span class="value">${dueno.ciudad}</span>
                        </div>
                        <div class="detalle-item">
                            <span class="label">Dirección:</span>
                            <span class="value">${dueno.direccion}</span>
                        </div>
                    </div>
                </div>
            `;
        } else {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    ${data.message}
                </div>
            `;
        }
    } catch (error) {
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-times-circle"></i>
                Error al cargar la información
            </div>
        `;
    }
}

// ============================================
// MODAL ELIMINAR
// ============================================
function confirmarEliminar(id, nombre) {
    idEliminar = id;
    document.getElementById('nombreEliminar').textContent = nombre;
    document.getElementById('modalEliminar').classList.add('show');
}

async function eliminarDueno() {
    if (!idEliminar) return;
    
    try {
        const response = await fetch('procesar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=eliminar&id=${idEliminar}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Mostrar mensaje de éxito
            alert('Dueño eliminado correctamente');
            // Recargar página
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error al eliminar el dueño');
    }
    
    cerrarModal('modalEliminar');
}

// ============================================
// CERRAR MODAL
// ============================================
function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
    idEliminar = null;
}

// Cerrar modal al hacer clic fuera
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            cerrarModal(modal.id);
        }
    });
});

// Cerrar con ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.show').forEach(modal => {
            cerrarModal(modal.id);
        });
    }
});

// ============================================
// EXPORTAR Y IMPRIMIR
// ============================================
function exportarExcel() {
    alert('Funcionalidad de exportar a Excel en desarrollo');
    // Aquí implementarías la lógica de exportación
}

function imprimirTabla() {
    window.print();
}

console.log('✅ Funcionalidades de tabla cargadas');