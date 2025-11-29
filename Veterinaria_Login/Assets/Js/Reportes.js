/**
 * REPORTES.JS - JavaScript para el módulo de Reportes
 * Sistema de Gestión Veterinaria VetCare Plus
 */

// ============================================
// ANIMACIONES DE TARJETAS
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Animar tarjetas al cargar
    const reporteCards = document.querySelectorAll('.reporte-card');
    
    reporteCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    console.log('✅ Módulo de reportes cargado correctamente');
});

// ============================================
// FUNCIONES DE EXPORTACIÓN
// ============================================

/**
 * Exportar reporte a PDF
 */
function exportarPDF() {
    // Implementación básica
    alert('Funcionalidad de exportar a PDF en desarrollo.\n\nPor ahora puedes usar Ctrl+P para imprimir la página.');
    window.print();
}

/**
 * Exportar reporte a Excel
 */
function exportarExcel() {
    const tabla = document.querySelector('.data-table');
    
    if (!tabla) {
        alert('No hay datos para exportar');
        return;
    }
    
    try {
        let csv = [];
        const filas = tabla.querySelectorAll('tr');
        
        filas.forEach(fila => {
            const cols = fila.querySelectorAll('td, th');
            const csvrow = [];
            
            cols.forEach(col => {
                // Limpiar el texto y escapar comillas
                let texto = col.innerText.replace(/"/g, '""');
                csvrow.push('"' + texto + '"');
            });
            
            csv.push(csvrow.join(','));
        });
        
        const csvString = csv.join('\n');
        const blob = new Blob(['\ufeff' + csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', 'reporte_' + new Date().getTime() + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        mostrarMensaje('Reporte exportado exitosamente', 'success');
    } catch (error) {
        console.error('Error al exportar:', error);
        alert('Error al exportar el reporte');
    }
}

/**
 * Imprimir reporte actual
 */
function imprimirReporte() {
    window.print();
}

// ============================================
// VALIDACIÓN DE FILTROS DE FECHA
// ============================================

const fechaDesde = document.getElementById('fecha_desde');
const fechaHasta = document.getElementById('fecha_hasta');

if (fechaDesde && fechaHasta) {
    fechaDesde.addEventListener('change', function() {
        // La fecha "hasta" no puede ser menor que la fecha "desde"
        if (fechaHasta.value && this.value > fechaHasta.value) {
            fechaHasta.value = this.value;
        }
        fechaHasta.min = this.value;
    });
    
    fechaHasta.addEventListener('change', function() {
        // La fecha "desde" no puede ser mayor que la fecha "hasta"
        if (fechaDesde.value && this.value < fechaDesde.value) {
            fechaDesde.value = this.value;
        }
        fechaDesde.max = this.value;
    });
}

// ============================================
// FILTROS DINÁMICOS
// ============================================

/**
 * Aplicar filtros al reporte
 */
function aplicarFiltros() {
    const form = document.querySelector('.filters-card form');
    if (form) {
        // Validar que las fechas sean correctas
        if (fechaDesde && fechaHasta) {
            if (fechaDesde.value > fechaHasta.value) {
                alert('La fecha "desde" no puede ser mayor que la fecha "hasta"');
                return false;
            }
        }
        
        form.submit();
    }
}

/**
 * Limpiar todos los filtros
 */
function limpiarFiltros() {
    const form = document.querySelector('.filters-card form');
    if (form) {
        // Resetear el formulario
        form.reset();
        
        // Establecer fechas por defecto
        if (fechaDesde) {
            fechaDesde.value = new Date().toISOString().split('T')[0].slice(0, 8) + '01';
        }
        if (fechaHasta) {
            fechaHasta.value = new Date().toISOString().split('T')[0];
        }
    }
}

// ============================================
// BÚSQUEDA EN TABLA
// ============================================

const searchInput = document.getElementById('searchInput');
const tabla = document.querySelector('.data-table tbody');

if (searchInput && tabla) {
    searchInput.addEventListener('input', function() {
        const busqueda = this.value.toLowerCase().trim();
        const filas = tabla.querySelectorAll('tr');
        let visibles = 0;
        
        filas.forEach(fila => {
            const texto = fila.textContent.toLowerCase();
            
            if (busqueda === '' || texto.includes(busqueda)) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });
        
        // Actualizar contador
        actualizarContador(visibles);
    });
}

/**
 * Actualizar contador de registros visibles
 */
function actualizarContador(cantidad) {
    const contador = document.getElementById('totalRegistros');
    if (contador) {
        contador.textContent = cantidad;
    }
}

// ============================================
// GRÁFICAS Y VISUALIZACIONES
// ============================================

/**
 * Generar gráfica simple con datos
 */
function generarGrafica(datos, tipo = 'bar') {
    // Aquí podrías integrar Chart.js o similar
    console.log('Generando gráfica:', tipo, datos);
    alert('Funcionalidad de gráficas en desarrollo');
}

// ============================================
// MENSAJES Y NOTIFICACIONES
// ============================================

/**
 * Mostrar mensaje al usuario
 */
function mostrarMensaje(mensaje, tipo = 'info') {
    // Crear elemento de mensaje
    const alert = document.createElement('div');
    alert.className = `alert alert-${tipo}`;
    alert.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        animation: slideInRight 0.3s ease;
    `;
    
    const iconos = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    alert.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas ${iconos[tipo] || iconos.info}" style="font-size: 1.5rem;"></i>
            <span>${mensaje}</span>
        </div>
    `;
    
    document.body.appendChild(alert);
    
    // Auto-remover después de 3 segundos
    setTimeout(() => {
        alert.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

// ============================================
// UTILIDADES
// ============================================

/**
 * Formatear número como moneda
 */
function formatearMoneda(numero) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(numero);
}

/**
 * Formatear fecha
 */
function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Calcular porcentaje
 */
function calcularPorcentaje(valor, total) {
    if (total === 0) return 0;
    return ((valor / total) * 100).toFixed(1);
}

// ============================================
// PRINT STYLES
// ============================================

// Agregar estilos de impresión dinámicamente
const printStyles = document.createElement('style');
printStyles.textContent = `
    @media print {
        .navbar,
        .footer,
        .filters-card,
        .quick-actions,
        .btn-reporte,
        .btn-icon,
        .action-btn {
            display: none !important;
        }
        
        .main-content {
            padding: 0 !important;
        }
        
        .reporte-card,
        .stat-card {
            break-inside: avoid;
            box-shadow: none;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }
        
        .table-container {
            box-shadow: none;
            border: 1px solid #ddd;
        }
        
        .data-table {
            font-size: 10pt;
        }
        
        .data-table thead {
            background: #f0f0f0 !important;
            color: #000 !important;
        }
        
        @page {
            margin: 2cm;
        }
        
        h1, h2, h3 {
            page-break-after: avoid;
        }
    }
`;
document.head.appendChild(printStyles);

// ============================================
// ANIMACIONES CSS
// ============================================

const animationStyles = document.createElement('style');
animationStyles.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .alert {
        animation: slideInRight 0.3s ease;
    }
`;
document.head.appendChild(animationStyles);

// ============================================
// ATAJOS DE TECLADO
// ============================================

document.addEventListener('keydown', (e) => {
    // Ctrl + P = Imprimir
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        imprimirReporte();
    }
    
    // Ctrl + E = Exportar Excel
    if (e.ctrlKey && e.key === 'e') {
        e.preventDefault();
        exportarExcel();
    }
    
    // Ctrl + F = Buscar
    if (e.ctrlKey && e.key === 'f' && searchInput) {
        e.preventDefault();
        searchInput.focus();
    }
});

// ============================================
// TOOLTIPS
// ============================================

// Agregar tooltips a elementos truncados
document.querySelectorAll('.text-truncate').forEach(element => {
    if (element.scrollWidth > element.clientWidth) {
        element.style.cursor = 'help';
    }
});

// ============================================
// INICIALIZACIÓN
// ============================================

console.log('📊 Módulo de reportes inicializado');
console.log('⌨️ Atajos: Ctrl+P (Imprimir) | Ctrl+E (Exportar) | Ctrl+F (Buscar)');