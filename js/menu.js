/* ============================================================================
   SISTEMA TRIPLE - Menu JavaScript
   Funcionalidades e Interacciones del Dashboard
   ============================================================================ */

(function() {
    'use strict';

    // ========== CONFIGURACIÓN ==========
    const CONFIG = {
        clockUpdateInterval: 1000,
        notificationCount: 3,
        autoSaveInterval: 300000 // 5 minutos
    };

    // ========== UTILIDADES ==========
    const Utils = {
        // Formatear fecha
        formatDate: function(date) {
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            return date.toLocaleDateString('es-PE', options);
        },

        // Formatear hora
        formatTime: function(date) {
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            return `${hours}:${minutes}:${seconds}`;
        },

        // Crear notificación
        showNotification: function(message, type = 'info') {
            // Integración con biblioteca de notificaciones si existe
            console.log(`[${type.toUpperCase()}] ${message}`);
        },

        // Guardar preferencias
        savePreference: function(key, value) {
            try {
                localStorage.setItem(`triple_${key}`, JSON.stringify(value));
            } catch (e) {
                console.warn('No se pudo guardar la preferencia:', e);
            }
        },

        // Obtener preferencias
        getPreference: function(key, defaultValue = null) {
            try {
                const value = localStorage.getItem(`triple_${key}`);
                return value ? JSON.parse(value) : defaultValue;
            } catch (e) {
                return defaultValue;
            }
        }
    };

    // ========== RELOJ EN TIEMPO REAL ==========
    class Clock {
        constructor() {
            this.timeElement = document.getElementById('currentTime');
            this.interval = null;
        }

        init() {
            if (this.timeElement) {
                this.update();
                this.interval = setInterval(() => this.update(), CONFIG.clockUpdateInterval);
            }
        }

        update() {
            const now = new Date();
            this.timeElement.textContent = Utils.formatTime(now);
        }

        destroy() {
            if (this.interval) {
                clearInterval(this.interval);
            }
        }
    }

    // ========== ANIMACIONES DE CARDS ==========
    class CardAnimations {
        constructor() {
            this.cards = document.querySelectorAll('.system-card');
            this.observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
        }

        init() {
            // Animación de entrada con delay
            this.cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // Efectos de hover mejorados
            this.setupHoverEffects();

            // Intersection Observer para animaciones al scroll
            this.setupScrollAnimations();
        }

        setupHoverEffects() {
            this.cards.forEach(card => {
                // Mouse enter
                card.addEventListener('mouseenter', (e) => {
                    card.style.transform = 'translateY(-8px) scale(1.02)';
                    
                    // Efecto de brillo siguiendo el mouse
                    this.updateGlowPosition(card, e);
                });

                // Mouse move
                card.addEventListener('mousemove', (e) => {
                    this.updateGlowPosition(card, e);
                });

                // Mouse leave
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0) scale(1)';
                });
            });
        }

        updateGlowPosition(card, e) {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const glow = card.querySelector('.card-glow');
            if (glow) {
                glow.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255, 255, 255, 0.2) 0%, transparent 70%)`;
            }
        }

        setupScrollAnimations() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, this.observerOptions);

            this.cards.forEach(card => observer.observe(card));
        }
    }

    // ========== NOTIFICACIONES ==========
    class Notifications {
        constructor() {
            this.btn = document.getElementById('btnNotifications');
            this.badge = document.querySelector('.badge-notification');
            this.count = CONFIG.notificationCount;
        }

        init() {
            if (this.btn) {
                this.btn.addEventListener('click', () => this.show());
                this.updateBadge();
            }
        }

        show() {
            // Aquí se mostraría un modal o dropdown con notificaciones
            const messages = [
                'Nueva licencia pendiente de revisión',
                'Expediente #1234 asignado',
                'Inspección programada para mañana'
            ];

            let notificationHTML = '<div style="background: white; padding: 1rem; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); position: fixed; top: 80px; right: 20px; z-index: 2000; min-width: 320px;">';
            notificationHTML += '<h3 style="margin: 0 0 1rem 0; font-size: 1rem;">Notificaciones</h3>';
            
            messages.forEach((msg, i) => {
                notificationHTML += `<div style="padding: 0.75rem; background: #f8fafc; border-radius: 8px; margin-bottom: 0.5rem; font-size: 0.875rem;">${msg}</div>`;
            });
            
            notificationHTML += '</div>';
            
            const container = document.createElement('div');
            container.innerHTML = notificationHTML;
            container.id = 'notificationPanel';
            document.body.appendChild(container);

            // Cerrar al hacer clic fuera
            setTimeout(() => {
                document.addEventListener('click', this.closePanel);
            }, 100);
        }

        closePanel(e) {
            const panel = document.getElementById('notificationPanel');
            if (panel && !panel.contains(e.target)) {
                panel.remove();
                document.removeEventListener('click', this.closePanel);
            }
        }

        updateBadge() {
            if (this.badge && this.count > 0) {
                this.badge.textContent = this.count;
            }
        }

        markAsRead() {
            this.count = 0;
            this.updateBadge();
        }
    }

    // ========== CONFIGURACIÓN ==========
    class Settings {
        constructor() {
            this.btn = document.getElementById('btnSettings');
        }

        init() {
            if (this.btn) {
                this.btn.addEventListener('click', () => this.show());
            }
        }

        show() {
            // Modal de configuración
            Utils.showNotification('Abriendo configuración...', 'info');
            
            // Aquí se mostraría un modal con opciones de configuración
            console.log('Configuración del sistema');
        }
    }

    // ========== EFECTOS PARALLAX ==========
    class ParallaxEffect {
        constructor() {
            this.pattern = document.querySelector('.pattern-layer');
            this.isEnabled = true;
        }

        init() {
            if (this.pattern && this.isEnabled) {
                document.addEventListener('mousemove', (e) => this.update(e));
            }
        }

        update(e) {
            if (!this.isEnabled) return;

            const x = (e.clientX / window.innerWidth) * 30 - 15;
            const y = (e.clientY / window.innerHeight) * 30 - 15;
            
            requestAnimationFrame(() => {
                this.pattern.style.transform = `translate(${x}px, ${y}px)`;
            });
        }

        disable() {
            this.isEnabled = false;
        }
    }

    // ========== ANALYTICS Y TRACKING ==========
    class Analytics {
        constructor() {
            this.cards = document.querySelectorAll('.system-card');
        }

        init() {
            this.trackCardClicks();
            this.trackPageView();
        }

        trackCardClicks() {
            this.cards.forEach(card => {
                card.addEventListener('click', (e) => {
                    const system = this.getSystemName(card);
                    this.logEvent('card_click', { system });
                });
            });
        }

        trackPageView() {
            this.logEvent('page_view', { page: 'dashboard' });
        }

        getSystemName(card) {
            if (card.classList.contains('silif-card')) return 'SILIF';
            if (card.classList.contains('mpartes-card')) return 'MPARTES';
            if (card.classList.contains('sinadeci-card')) return 'SINADECI';
            return 'unknown';
        }

        logEvent(eventName, data) {
            // Aquí se enviarían los datos a Google Analytics u otro servicio
            console.log(`[Analytics] ${eventName}:`, data);
        }
    }

    // ========== GESTOS TOUCH PARA MÓVILES ==========
    class TouchGestures {
        constructor() {
            this.cards = document.querySelectorAll('.system-card');
            this.touchStartX = 0;
            this.touchEndX = 0;
        }

        init() {
            if ('ontouchstart' in window) {
                this.setupGestures();
            }
        }

        setupGestures() {
            this.cards.forEach(card => {
                card.addEventListener('touchstart', (e) => {
                    this.touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });

                card.addEventListener('touchend', (e) => {
                    this.touchEndX = e.changedTouches[0].screenX;
                    this.handleSwipe(card);
                }, { passive: true });
            });
        }

        handleSwipe(card) {
            const swipeThreshold = 50;
            const diff = this.touchStartX - this.touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left
                    console.log('Swipe left detected');
                } else {
                    // Swipe right
                    console.log('Swipe right detected');
                }
            }
        }
    }

    // ========== SESIÓN Y ACTIVIDAD ==========
    class SessionManager {
        constructor() {
            this.lastActivity = Date.now();
            this.timeoutDuration = 30 * 60 * 1000; // 30 minutos
            this.warningShown = false;
        }

        init() {
            this.trackActivity();
            this.startMonitoring();
        }

        trackActivity() {
            ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
                document.addEventListener(event, () => {
                    this.lastActivity = Date.now();
                    this.warningShown = false;
                }, { passive: true });
            });
        }

        startMonitoring() {
            setInterval(() => {
                const inactive = Date.now() - this.lastActivity;
                
                // Advertencia a los 25 minutos
                if (inactive > 25 * 60 * 1000 && !this.warningShown) {
                    this.showWarning();
                    this.warningShown = true;
                }

                // Cerrar sesión a los 30 minutos
                if (inactive > this.timeoutDuration) {
                    this.logout();
                }
            }, 60000); // Revisar cada minuto
        }

        showWarning() {
            Utils.showNotification('Tu sesión expirará pronto por inactividad', 'warning');
        }

        logout() {
            window.location.href = 'logout.php?timeout=1';
        }
    }

    // ========== INICIALIZACIÓN PRINCIPAL ==========
    class Dashboard {
        constructor() {
            this.clock = new Clock();
            this.cardAnimations = new CardAnimations();
            this.notifications = new Notifications();
            this.settings = new Settings();
            this.parallax = new ParallaxEffect();
            this.analytics = new Analytics();
            this.touchGestures = new TouchGestures();
            this.sessionManager = new SessionManager();
        }

        init() {
            // Esperar a que el DOM esté listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setup());
            } else {
                this.setup();
            }
        }

        setup() {
            console.log('🚀 Inicializando Dashboard TRIPLE...');

            // Inicializar módulos
            this.clock.init();
            this.cardAnimations.init();
            this.notifications.init();
            this.settings.init();
            this.parallax.init();
            this.analytics.init();
            this.touchGestures.init();
            this.sessionManager.init();

            // Configurar eventos globales
            this.setupGlobalEvents();

            // Mensaje de bienvenida
            setTimeout(() => {
                Utils.showNotification('Bienvenido al Sistema TRIPLE', 'success');
            }, 1000);

            console.log('✅ Dashboard inicializado correctamente');
        }

        setupGlobalEvents() {
            // Manejo de errores globales
            window.addEventListener('error', (e) => {
                console.error('Error global:', e.message);
            });

            // Advertencia antes de salir si hay cambios sin guardar
            window.addEventListener('beforeunload', (e) => {
                // Aquí se verificarían cambios sin guardar
                // e.preventDefault();
                // e.returnValue = '';
            });

            // Responsive: ajustar parallax en móviles
            if (window.innerWidth < 768) {
                this.parallax.disable();
            }
        }
    }

    // ========== INICIAR APLICACIÓN ==========
    const app = new Dashboard();
    app.init();

    // Exponer API pública
    window.TripleDashboard = {
        version: '1.0.0',
        utils: Utils,
        clock: app.clock,
        notifications: app.notifications
    };

})();