/**
 * DASHBOARD.JS - Interactividad del Dashboard
 * Sistema de Gestión Veterinaria
 */

// ============================================
// VARIABLES GLOBALES
// ============================================
const logoutModal = document.getElementById('logoutModal');
const menuCards = document.querySelectorAll('.menu-card');

// ============================================
// MODAL DE LOGOUT
// ============================================

/**
 * Mostrar modal de confirmación de logout
 */
function confirmarLogout() {
    logoutModal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

/**
 * Cerrar modal
 */
function cerrarModal() {
    logoutModal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Cerrar modal al hacer clic fuera de él
logoutModal.addEventListener('click', (e) => {
    if (e.target === logoutModal) {
        cerrarModal();
    }
});

// Cerrar modal con tecla ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && logoutModal.classList.contains('show')) {
        cerrarModal();
    }
});

// ============================================
// ANIMACIONES DE TARJETAS
// ============================================

/**
 * Animar tarjetas al entrar en el viewport
 */
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            setTimeout(() => {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }, index * 100);
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Aplicar animación inicial
menuCards.forEach((card) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'all 0.5s ease';
    observer.observe(card);
});

// ============================================
// EFECTO DE RIPPLE EN TARJETAS
// ============================================

/**
 * Crear efecto ripple al hacer clic
 */
function createRipple(event, element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');
    
    element.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

// Agregar efecto ripple a las tarjetas
menuCards.forEach(card => {
    card.addEventListener('click', function(e) {
        // Solo si no hay un ripple activo
        if (!this.querySelector('.ripple')) {
            createRipple(e, this);
        }
    });
});

// Estilos CSS para el ripple (se agregan dinámicamente)
const style = document.createElement('style');
style.textContent = `
    .menu-card {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ============================================
// TRACKING DE ACTIVIDAD
// ============================================

/**
 * Registrar última actividad del usuario
 */
function actualizarActividad() {
    fetch('includes/update_activity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    }).catch(err => console.log('Error actualizando actividad'));
}

// Actualizar actividad cada 5 minutos
setInterval(actualizarActividad, 5 * 60 * 1000);

// ============================================
// TOOLTIPS INFORMATIVOS
// ============================================

/**
 * Agregar información adicional al pasar el mouse
 */
menuCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        const module = this.dataset.module;
        const tooltip = getTooltipInfo(module);
        
        // Aquí podrías mostrar un tooltip con información adicional
        console.log(`Módulo: ${module} - ${tooltip}`);
    });
});

/**
 * Obtener información del módulo
 */
function getTooltipInfo(module) {
    const tooltips = {
        'dueno': 'Gestiona la información de los propietarios de mascotas',
        'mascota': 'Administra el registro de pacientes animales',
        'empleado': 'Controla el personal de la clínica',
        'consulta': 'Registra las atenciones médicas diarias',
        'factura': 'Maneja la facturación y cobros',
        'procedimiento': 'Gestiona cirugías y tratamientos especiales',
        'vacuna': 'Control del calendario de vacunación',
        'cargo': 'Administra los roles y permisos del sistema',
        'reportes': 'Genera estadísticas y análisis de datos'
    };
    
    return tooltips[module] || 'Información no disponible';
}

// ============================================
// ATAJOS DE TECLADO
// ============================================

/**
 * Navegación con teclado
 */
document.addEventListener('keydown', (e) => {
    // ALT + L = Logout
    if (e.altKey && e.key === 'l') {
        e.preventDefault();
        confirmarLogout();
    }
    
    // ALT + 1-9 = Acceder a módulos
    if (e.altKey && e.key >= '1' && e.key <= '9') {
        e.preventDefault();
        const index = parseInt(e.key) - 1;
        if (menuCards[index]) {
            menuCards[index].click();
        }
    }
});

// ============================================
// MOSTRAR ATAJOS AL USUARIO
// ============================================

/**
 * Mostrar ayuda de atajos de teclado
 */
function mostrarAyudaAtajos() {
    console.log(`
    🎹 ATAJOS DE TECLADO DISPONIBLES:
    ─────────────────────────────────
    ALT + L          → Cerrar sesión
    ALT + 1-9        → Acceder a módulos (1=Dueño, 2=Mascota, etc.)
    ESC              → Cerrar modal
    `);
}

// Mostrar ayuda al cargar la página
console.log('%c🐾 Sistema de Gestión Veterinaria', 'color: #667eea; font-size: 20px; font-weight: bold;');
console.log('%c✨ Dashboard cargado correctamente', 'color: #10b981; font-size: 14px;');
mostrarAyudaAtajos();

// ============================================
// VALIDACIÓN DE SESIÓN
// ============================================

/**
 * Verificar si la sesión sigue activa
 */
function verificarSesion() {
    fetch('includes/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (!data.active) {
                // Sesión expirada, redirigir al login
                window.location.href = 'index.php?timeout=1';
            }
        })
        .catch(err => console.log('Error verificando sesión'));
}

// Verificar sesión cada 2 minutos
setInterval(verificarSesion, 2 * 60 * 1000);

// ============================================
// NAVEGACIÓN CON ANIMACIÓN
// ============================================

/**
 * Agregar efecto de carga al navegar
 */
menuCards.forEach(card => {
    card.addEventListener('click', function(e) {
        // Si no es un clic medio o con CTRL (para abrir en nueva pestaña)
        if (e.button === 0 && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
            
            // Agregar clase de carga
            this.classList.add('loading');
            
            // Navegar después de la animación
            setTimeout(() => {
                window.location.href = this.href;
            }, 300);
        }
    });
});

// Agregar estilos para el estado de carga
const loadingStyle = document.createElement('style');
loadingStyle.textContent = `
    .menu-card.loading {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .menu-card.loading .card-icon {
        animation: pulse 0.5s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
`;
document.head.appendChild(loadingStyle);

// ============================================
// INICIALIZACIÓN
// ============================================

/**
 * Ejecutar al cargar la página
 */
window.addEventListener('DOMContentLoaded', () => {
    console.log('✅ Dashboard inicializado correctamente');
    
    // Verificar sesión inicial
    verificarSesion();
    
    // Marcar actividad inicial
    actualizarActividad();
});