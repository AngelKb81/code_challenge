import './bootstrap';
import './route-safety';  // Importa la protezione per le route
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import DebugLogger from './utils/debug-logger.js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Global error handler per intercettare tutti gli errori JavaScript
window.addEventListener('error', (event) => {
    DebugLogger.error('Global JavaScript Error', {
        message: event.message,
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
        error: event.error,
        stack: event.error?.stack
    });
});

// Global promise rejection handler
window.addEventListener('unhandledrejection', (event) => {
    DebugLogger.error('Unhandled Promise Rejection', {
        reason: event.reason,
        promise: event.promise
    });
});

// Log di avvio applicazione
DebugLogger.info('Starting Vue/Inertia application', { appName });

// Debug Ziggy configuration
DebugLogger.log('Ziggy configuration check', {
    Ziggy: typeof Ziggy !== 'undefined' ? 'available' : 'undefined',
    windowRoute: typeof window.route !== 'undefined' ? 'available' : 'undefined'
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        DebugLogger.log('Resolving page component', { name });
        return resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));
    },
    setup({ el, App, props, plugin }) {
        DebugLogger.log('Setting up Vue app', { 
            hasElement: !!el,
            hasApp: !!App,
            hasProps: !!props,
            propsKeys: props ? Object.keys(props) : []
        });

        const app = createApp({ render: () => h(App, props) })
            .use(plugin);

        // Check if route function is available globally
        if (typeof window.route !== 'undefined') {
            DebugLogger.log('Global route function is available');
        } else {
            DebugLogger.warn('Global route function not available');
        }

        app.mixin({
            methods: {
                route: window.route || (() => {
                    DebugLogger.warn('route() function called but not available');
                    return '#';
                }),
            },
        });

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
}).then(() => {
    DebugLogger.info('Vue/Inertia application started successfully');
}).catch((error) => {
    DebugLogger.error('Failed to start Vue/Inertia application', error);
});
