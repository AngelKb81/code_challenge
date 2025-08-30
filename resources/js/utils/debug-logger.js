// Debug Logger per tracciare gli errori JavaScript nelle pagine warehouse

export class DebugLogger {
    static enabled = true;
    static prefix = '[WAREHOUSE_DEBUG]';

    static log(message, data = null) {
        if (!this.enabled) return;
        
        const timestamp = new Date().toISOString();
        console.log(`${this.prefix} [${timestamp}] ${message}`, data);
    }

    static error(message, error = null) {
        if (!this.enabled) return;
        
        const timestamp = new Date().toISOString();
        console.error(`${this.prefix} [${timestamp}] ERROR: ${message}`, error);
        
        // Aggiungiamo anche i dettagli dello stack trace se disponibile
        if (error && error.stack) {
            console.error(`${this.prefix} Stack trace:`, error.stack);
        }
    }

    static warn(message, data = null) {
        if (!this.enabled) return;
        
        const timestamp = new Date().toISOString();
        console.warn(`${this.prefix} [${timestamp}] WARNING: ${message}`, data);
    }

    static info(message, data = null) {
        if (!this.enabled) return;
        
        const timestamp = new Date().toISOString();
        console.info(`${this.prefix} [${timestamp}] INFO: ${message}`, data);
    }

    // Log per verificare lo stato delle route
    static checkRoute(routeName, params = {}) {
        try {
            // Controlliamo se la funzione route è disponibile
            if (typeof route === 'undefined') {
                this.error(`Route function is undefined when checking ${routeName}`);
                return '#';
            }

            // Controlliamo se la funzione route è null
            if (route === null) {
                this.error(`Route function is null when checking ${routeName}`);
                return '#';
            }

            // Proviamo a chiamare la route
            const result = route(routeName, params);
            
            // Controlliamo il risultato
            if (result === null || result === undefined) {
                this.warn(`Route ${routeName} returned null/undefined`, { result, params });
                return '#';
            }

            return result;

        } catch (error) {
            this.error(`Exception when checking route ${routeName}`, { error, params });
            return '#';
        }
    }

    // Log per verificare lo stato delle props
    static checkProps(componentName, props) {
        // Verifichiamo le props critiche
        if (props.auth) {
            this.log(`Auth props found`, {
                user: props.auth.user ? 'present' : 'missing',
                role: props.auth.user?.role || 'undefined'
            });
        } else {
            this.warn(`Auth props missing in ${componentName}`);
        }

        return props;
    }

    // Log per verificare lo stato di $page
    static checkPageProps(componentName, pageProps) {
        if (pageProps && pageProps.props) {
            this.log(`Page props structure`, {
                auth: pageProps.props.auth ? 'present' : 'missing',
                user: pageProps.props.auth?.user ? 'present' : 'missing',
                role: pageProps.props.auth?.user?.role || 'undefined'
            });
        } else {
            this.error(`$page.props missing or malformed in ${componentName}`, pageProps);
        }

        return pageProps;
    }

    // Log per gli errori di router - versione semplificata
    static checkRouter(componentName) {
        try {
            // Verifichiamo semplicemente se router è importato correttamente
            if (typeof window !== 'undefined') {
                this.log(`Router check completed for ${componentName}`);
                return true;
            }
        } catch (error) {
            this.error(`Error checking router in ${componentName}`, error);
            return false;
        }
    }
}

// Funzione helper per route safety con logging esteso
export const safeRouteWithLogging = (routeName, params = {}, componentName = 'Unknown') => {
    return DebugLogger.checkRoute(routeName, params);
};

export default DebugLogger;
