#!/bin/bash

# ==================================================
# Script di Avvio Automatico - Code Challenge App
# Laravel 11 + Vue 3 + Inertia.js + Magazzino
# AGGIORNATO: 30 Agosto 2025 - Include Statistics Dashboard
# ==================================================

echo "🚀 Avvio Code Challenge Application..."
echo "======================================"

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funzione per stampare messaggi colorati
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verifica che siamo nella directory corretta
if [ ! -f "artisan" ]; then
    print_error "Errore: Non sei nella directory del progetto Laravel!"
    echo "Vai nella directory del progetto e riprova."
    exit 1
fi

print_status "Verifico dipendenze..."

# Controlla se PHP è installato
if ! command -v php &> /dev/null; then
    print_error "PHP non è installato!"
    exit 1
fi

# Controlla se Composer è installato
if ! command -v composer &> /dev/null; then
    print_error "Composer non è installato!"
    exit 1
fi

# Controlla se Node.js è installato
if ! command -v node &> /dev/null; then
    print_error "Node.js non è installato!"
    exit 1
fi

# Controlla se NPM è installato
if ! command -v npm &> /dev/null; then
    print_error "NPM non è installato!"
    exit 1
fi

print_success "Tutte le dipendenze sono presenti ✓"

# Installa dipendenze PHP se necessario
if [ ! -d "vendor" ]; then
    print_status "Installo dipendenze PHP..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
    print_success "Dipendenze PHP installate ✓"
fi

# Installa dipendenze Node.js se necessario
if [ ! -d "node_modules" ]; then
    print_status "Installo dipendenze Node.js..."
    npm install
    print_success "Dipendenze Node.js installate ✓"
fi

# Crea file .env se non esiste
if [ ! -f ".env" ]; then
    print_status "Creo file .env..."
    cp .env.example .env
    print_success "File .env creato ✓"
fi

# Genera chiave applicazione se necessario
if ! grep -q "APP_KEY=base64:" .env; then
    print_status "Genero chiave applicazione..."
    php artisan key:generate --ansi
    print_success "Chiave applicazione generata ✓"
fi

# Controlla configurazione database
print_status "Verifico configurazione database..."
if ! php artisan migrate:status &> /dev/null; then
    print_warning "Database non configurato o non raggiungibile"
    echo ""
    echo "📋 CONFIGURAZIONE DATABASE RICHIESTA:"
    echo "1. Assicurati che MySQL sia in esecuzione"
    echo "2. Crea un database chiamato 'code_challenge'"
    echo "3. Configura le credenziali in .env:"
    echo "   DB_DATABASE=code_challenge"
    echo "   DB_USERNAME=tuo_username"
    echo "   DB_PASSWORD=tua_password"
    echo ""
    read -p "Hai configurato il database? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_warning "Configurazione database necessaria. Script interrotto."
        exit 1
    fi
fi

# Esegui migrazioni e seed
print_status "Eseguo migrazioni e popolamento database..."
php artisan migrate:fresh --seed --force
print_success "Database configurato e popolato ✓"

# Pulisci cache
print_status "Pulisco cache applicazione..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
print_success "Cache pulita ✓"

# Compila assets per produzione (opzionale)
read -p "Vuoi compilare gli assets per produzione? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Compilo assets per produzione..."
    npm run build
    print_success "Assets compilati per produzione ✓"
fi

echo ""
echo "🎉 APPLICAZIONE PRONTA!"
echo "======================"
echo ""
echo "📍 URL Applicazione: http://localhost:8000"
echo "📍 Server Vite (dev): http://localhost:5173 (o porta disponibile)"
echo ""
echo "👥 UTENTI DI TEST (password: 'password'):"
echo "   🔐 Admin: admin@example.com"
echo "   👤 User:  user@example.com"
echo ""
echo "📦 DATI DI ESEMPIO:"
echo "   ✓ 12 utenti creati"
echo "   ✓ 14 articoli magazzino"
echo "   ✓ 10 richieste di esempio"
echo ""
echo "🚀 AVVIO SERVERS:"
echo "   1. Server Laravel su porta 8000"
echo "   2. Server Vite per development"
echo ""

# Funzione per gestire l'interruzione del script
cleanup() {
    print_warning "Interruzione ricevuta. Chiudo i server..."
    kill $(jobs -p) 2>/dev/null
    exit 0
}

# Imposta trap per gestire Ctrl+C
trap cleanup SIGINT

# Avvia server Laravel in background
print_status "Avvio server Laravel..."
php artisan serve > /dev/null 2>&1 &
LARAVEL_PID=$!

# Aspetta un momento per assicurarsi che il server sia avviato
sleep 2

# Controlla se il server Laravel è attivo
if kill -0 $LARAVEL_PID 2>/dev/null; then
    print_success "Server Laravel avviato su http://localhost:8000 ✓"
else
    print_error "Errore nell'avvio del server Laravel!"
    exit 1
fi

# Avvia server Vite per development
print_status "Avvio server Vite per development..."
npm run dev > /dev/null 2>&1 &
VITE_PID=$!

# Aspetta un momento per assicurarsi che Vite sia avviato
sleep 3

if kill -0 $VITE_PID 2>/dev/null; then
    print_success "Server Vite avviato ✓"
else
    print_warning "Server Vite potrebbe avere problemi (controlla manualmente)"
fi

echo ""
print_success "🌟 TUTTO PRONTO! Applicazione in esecuzione..."
echo ""
echo "💡 COMANDI UTILI:"
echo "   • Ctrl+C per fermare i server"
echo "   • Vai su http://localhost:8000 per iniziare"
echo "   • Login admin: admin@example.com / password"
echo ""

# Mantieni lo script in esecuzione
print_status "Server in esecuzione. Premi Ctrl+C per fermare tutto."
wait
