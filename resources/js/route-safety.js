// Helper per gestire route sicure
window.safeRoute = function(name, params = {}) {
    try {
        const routeObj = route(name, params);
        if (!routeObj || routeObj === null) {
            console.warn(`Route '${name}' not found or unauthorized`);
            return '#';
        }
        return routeObj;
    } catch (error) {
        console.warn(`Error accessing route '${name}':`, error);
        return '#';
    }
};

// Override della funzione route originale per maggiore sicurezza
const originalRoute = window.route;
window.route = function(name, params = {}) {
    try {
        const result = originalRoute(name, params);
        if (!result || result === null) {
            console.warn(`Route '${name}' not found or unauthorized`);
            return '#';
        }
        return result;
    } catch (error) {
        console.warn(`Error accessing route '${name}':`, error);
        return '#';
    }
};
