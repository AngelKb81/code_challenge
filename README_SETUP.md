# 🚀 COMANDI DI AVVIO AUTOMATICO

## Metodo 1: Script Bash (Raccomandato)

```bash
# Avvia tutto automaticamente
./start-app.sh
```

**Cosa fa lo script:**
- ✅ Verifica tutte le dipendenze (PHP, Composer, Node.js, NPM)
- ✅ Installa dipendenze PHP e Node.js se necessario  
- ✅ Crea e configura file .env
- ✅ Genera chiave applicazione Laravel
- ✅ Configura e popola database con dati di esempio
- ✅ Pulisce tutte le cache
- ✅ Avvia server Laravel su porta 8000
- ✅ Avvia server Vite per development
- ✅ Mostra informazioni di accesso e utenti di test

**Utenti di test:**
- 🔐 Admin: `admin@example.com` / `password`
- 👤 User: `user@example.com` / `password`

## Metodo 2: Comando Artisan

```bash
# Avvio base (solo Laravel)
php artisan app:start

# Avvio con Vite development server
php artisan app:start --dev

# Avvio con compilazione assets
php artisan app:start --build
```

## Metodo 3: Script NPM

```bash
# Avvia Laravel + Vite simultaneamente  
npm run start

# Solo server Laravel (produzione)
npm run start-prod

# Setup completo progetto
npm run setup
```

---

# Code Challenge - Sistema Gestione Magazzino

## Configurazione del progetto completata! 🎉

### Struttura del progetto:
- **Backend**: Laravel 11 con Inertia.js
- **Frontend**: Vue 3 con componenti moderni
- **Database**: SQLite con sistema di ruoli utente
- **Styling**: Tailwind CSS

### Comandi Artisan essenziali:

#### Database e Migrazioni:
```bash
# Eseguire tutte le migrazioni
php artisan migrate

# Eseguire migrazioni con seed
php artisan migrate --seed

# Reset completo database
php artisan migrate:reset

# Refresh database (drop + migrate)
php artisan migrate:refresh

# Refresh con seed
php artisan migrate:refresh --seed

# Status migrazioni
php artisan migrate:status

# Rollback ultima migrazione
php artisan migrate:rollback

# Rollback tutte le migrazioni
php artisan migrate:rollback --all
```

#### Seeders:
```bash
# Eseguire tutti i seeders
php artisan db:seed

# Eseguire seeder specifico
php artisan db:seed --class=UserSeeder
```

#### Gestione utenti e ruoli:
```bash
# Creare nuovo utente admin via tinker
php artisan tinker
>>> User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => Hash::make('password'), 'role' => 'admin'])

# Cambiare ruolo utente esistente
>>> $user = User::find(1)
>>> $user->role = 'admin'
>>> $user->save()
```

#### Cache e ottimizzazione:
```bash
# Clear cache applicazione
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Ottimizzazione per produzione
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Development:
```bash
# Avviare server Laravel
php artisan serve

# Avviare Vite dev server (in terminal separato)
npm run dev

# Build assets per produzione
npm run build

# Rigenerare routes per Ziggy
php artisan ziggy:generate
```

### Credenziali di accesso:
- **Admin**: admin@example.com / password
- **User**: user@example.com / password

### URLs principali:
- Homepage: http://127.0.0.1:8000/
- Login: http://127.0.0.1:8000/login
- Dashboard: http://127.0.0.1:8000/dashboard

### Funzionalità implementate:
✅ Layout di base con autenticazione
✅ Sistema di ruoli (admin/user)
✅ Integrazione completa Laravel + Vue + Inertia
✅ Database seeded con utenti di esempio
✅ Middleware di autenticazione configurato
✅ Route configurate con protezione
✅ Interfaccia responsive con Tailwind CSS

### Struttura directory frontend:
- `resources/js/Pages/` - Pagine Vue
- `resources/js/Layouts/` - Layout componenti
- `resources/js/Components/` - Componenti riutilizzabili
- `resources/views/app.blade.php` - Template principale Inertia

### Prossimi passi suggeriti:
1. Implementare registrazione utenti
2. Aggiungere gestione profilo utente
3. Creare pannello admin per gestione utenti
4. Implementare middleware per controllo ruoli
5. Aggiungere funzionalità specifiche per ruoli
