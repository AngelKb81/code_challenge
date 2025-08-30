#!/bin/bash

# ==================================================
# Script Rapido di Avvio - Code Challenge App  
# Versione semplificata per avvio veloce
# ==================================================

echo "🚀 Avvio rapido Code Challenge..."

# Verifica directory progetto
if [ ! -f "artisan" ]; then
    echo "❌ Errore: Non sei nella directory del progetto Laravel!"
    exit 1
fi

# Avvia database se necessario
echo "📊 Configurazione database..."
php artisan migrate:fresh --seed --force

# Pulisci cache
echo "🧹 Pulizia cache..."
php artisan config:clear && php artisan route:clear && php artisan view:clear

echo ""
echo "✅ Pronto! Avvio server..."
echo "📍 URL: http://localhost:8000"
echo "🔐 Admin: admin@example.com / password"
echo ""

# Avvia server Laravel
php artisan serve
