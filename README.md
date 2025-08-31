# 📦 Code Challenge - Sistema Gestione Magazzino

> **Web Application completa per la gestione del magazzino sviluppata con Laravel 11, Vue 3 e Inertia.js**

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red?style=for-the-badge&logo=laravel" alt="Laravel 11">
  <img src="https://img.shields.io/badge/Vue.js-3-green?style=for-the-badge&logo=vue.js" alt="Vue 3">
  <img src="https://img.shields.io/badge/Inertia.js-2.1.3-purple?style=for-the-badge" alt="Inertia.js">
  <img src="https://img.shields.io/badge/Tailwind-CSS-blue?style=for-the-badge&logo=tailwindcss" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/MySQL-Database-orange?style=for-the-badge&logo=mysql" alt="MySQL">
</p>

## 🚀 **CONSEGNA PROGETTO**

### ✅ **Progetto Completo e Funzionante**
Questa repository contiene un sistema di gestione magazzino enterprise-ready con setup automatico. I valutatori riceveranno:

- **✅ Codice sorgente completo** - Laravel 11 + Vue 3 + database MySQL
- **✅ Database riproducibile** - Migrazioni + seeder con dati di test realistici  
- **✅ Setup automatizzato** - 5 comandi per ambiente locale funzionante
- **✅ Credenziali di test** - Login admin/user inclusi per testing immediato
- **✅ Documentation completa** - Architettura, funzionalità e API

**📋 Vedi [DELIVERY_GUIDE.md](./DELIVERY_GUIDE.md) per dettagli consegna**

---

## 🎯 **Panoramica Progetto**

Sistema completo di gestione magazzino con autenticazione basata su ruoli, interfaccia moderna e operazioni CRUD complete. Sviluppato come code challenge per dimostrare competenze full-stack moderne.

## 🏗️ **Scelte Architetturali**

### **Approccio Quantity-Based vs Asset-Based**

Per questo progetto abbiamo scelto un **approccio quantity-based** semplificato invece di un sistema enterprise asset-based più complesso. Ecco il ragionamento:

#### **🔹 Approccio Implementato (Quantity-Based)**
```sql
Items Table:
- iPhone 15 Pro | quantity: 3 | available_quantity: (calcolato dinamicamente)
- MacBook Pro   | quantity: 5 | available_quantity: quantity - approved_requests

Requests Table:
- user_id | item_id | quantity | status
```

#### **🔹 Approccio Enterprise (Asset-Based)**
```sql
Categories → Brands → Models → Items (Asset Instances)
- Tracciabilità individuale degli asset
- Seriali univoci per ogni item
- Assegnazioni specifiche asset-to-user
- Stati granulari per singoli asset
```

### **📋 Motivazioni della Scelta**

#### **✅ Vantaggi Approccio Attuale**
- **Semplicità implementativa**: Meno tabelle, relazioni più dirette
- **Performance superiore**: Calcoli veloci, meno JOIN nelle query
- **Sviluppo rapido**: Ideale per prototipazione e demo
- **UI intuitiva**: Interfacce meno complesse da gestire
- **Adeguato al contesto**: Sistema di test senza requisiti di compliance

#### **⚠️ Limitazioni Consapevoli**
- **Tracciabilità limitata**: Non tracciamo singoli asset ma quantità aggregate
- **Seriali non gestiti**: Perdiamo granularità sui numeri seriali individuali
- **Manutenzione semplificata**: Non possiamo gestire stati di singoli asset
- **Audit trail ridotto**: Meno dettagli per reporting enterprise

#### **🎯 Quando Scegliere Asset-Based**
Un approccio enterprise asset-based sarebbe preferibile per:
- **Sistemi di produzione** con requisiti di compliance
- **IT Asset Management** dove ogni dispositivo deve essere tracciato
- **Organizzazioni enterprise** con normative strict di audit
- **Gestione garanzie** individuali e contratti di manutenzione
- **Inventory da alta frequenza** con rotazione complessa

#### **🎯 Quando Quantity-Based è Appropriato**
Il nostro approccio è ideale per:
- **Prototipi e demo** come questo progetto
- **Sistemi semplificati** senza requisiti di tracciabilità individuale
- **Inventory di consumabili** dove la granularità individuale non serve
- **MVP e startup** che necessitano velocità di sviluppo

### **🔮 Evoluzione Futura**
Il sistema attuale può essere facilmente esteso verso un approccio asset-based aggiungendo:
```sql
-- Tabelle master data
categories, brands, models

-- Asset instances
items → asset_instances (con seriali individuali)

-- Relazioni request-to-asset
request_assets (per tracciare assegnazioni specifiche)
```

### 🌟 **Caratteristiche Principali**

- ✅ **Autenticazione Completa** - Sistema login/logout con gestione sessioni
- ✅ **Gestione Ruoli** - Distinzione Admin/User con autorizzazioni granulari
- ✅ **CRUD Magazzino** - Gestione completa articoli (solo Admin)
- ✅ **Sistema Richieste** - Workflow completo per richiesta/approvazione item
- ✅ **Calcolo Disponibilità** - Quantità disponibili calcolate dinamicamente
- ✅ **Dashboard Interattiva** - Interfaccia responsiva e moderna
- ✅ **SPA Experience** - Single Page Application con Inertia.js
- ✅ **Dati Realistici** - Seeder con dati di esempio pronti all'uso
- ✅ **Avvio Automatico** - Script per setup e lancio con un comando

### 📘 **Tipo di Progetto**

**Questo è un progetto dimostrativo/code challenge** progettato per:
- Mostrare competenze full-stack moderne (Laravel 11 + Vue 3)
- Dimostrare architetture SPA con Inertia.js
- Implementare patterns di sviluppo clean e scalabili
- Fornire una base solida per prototipi rapidi

**Non è un sistema enterprise-ready** ma può essere facilmente esteso verso un approccio più complesso per contesti di produzione con requisiti di compliance e tracciabilità granulare.

---

## 🚀 **AVVIO RAPIDO**

### Metodo 1: Script Automatico (Raccomandato)
```bash
# Clona e avvia tutto automaticamente
git clone https://github.com/AngelKb81/code_challenge.git
cd code_challenge
./start-app.sh
```

### Metodo 2: Avvio Veloce
```bash
# Solo database e server
./quick-start.sh
```

### Metodo 3: Comando Artisan
```bash
# Con development server
php artisan app:start --dev
```

### Metodo 4: NPM Script
```bash
# Laravel + Vite simultaneamente
npm run start
```

---

## 🏗️ **ARCHITETTURA TECNICA**

### Stack Tecnologico
- **Backend**: Laravel 11 con Eloquent ORM
- **Frontend**: Vue 3 (Composition API) 
- **Bridge**: Inertia.js v2.1.3 per SPA senza API
- **Styling**: Tailwind CSS v3.4
- **Build**: Vite 6.0 con HMR
- **Database**: MySQL con migrazioni e seeder

### Struttura Database

#### 🗄️ **Tabelle Principali**

**Users** - Gestione utenti e autenticazione
```sql
- id, name, email, password
- role (enum: 'admin', 'user')  
- email_verified_at, timestamps
```

**Items** - Articoli del magazzino
```sql
- id, name, description, category
- quantity, unit_price, location
- status (enum: 'available', 'not_available', 'maintenance', 'reserved')
- min_quantity, timestamps
- available_quantity (calcolato: quantity - approved_requests)
```

**Requests** - Richieste di articoli
```sql
- id, user_id, item_id
- quantity_requested, status
- notes, timestamps
- Relazioni: belongsTo User, belongsTo Item
```

### 🔐 **Sistema Autorizzazioni**

**Gate Personalizzati**:
- `admin-only`: Accesso esclusivo amministratori
- `user-operations`: Operazioni base utenti

**Middleware Protetto**:
- Tutte le route CRUD magazzino richiedono ruolo admin
- Dashboard personalizzata per ruolo
- Controlli a livello di controller e vista

---

## 📋 **FUNZIONALITÀ COMPLETE**

### 👤 **Gestione Utenti**

#### Autenticazione
- ✅ Login/Logout sicuro con validazione
- ✅ Gestione sessioni persistenti  
- ✅ Middleware di protezione route
- ✅ Redirect automatici per ruolo

#### Ruoli e Permessi
- 🔐 **Admin**: CRUD completo articoli, gestione magazzino
- 👤 **User**: Visualizzazione dashboard, richieste future

### 📦 **Gestione Magazzino (Admin)**

#### Interface CRUD Completa
- ✅ **Lista Articoli** (`/warehouse/manage`)
  - Tabella responsive con paginazione
  - Filtri per categoria e stato
  - Ricerca in tempo reale
  - Azioni rapide (modifica/elimina)

- ✅ **Creazione Articoli** (`/warehouse/create`)
  - Form validato con controlli frontend/backend
  - Gestione dinamica categorie
  - Calcolo automatico stato stock
  - Feedback visivo operazioni

- ✅ **Modifica Articoli** (`/warehouse/edit/{id}`)
  - Form pre-popolato con dati esistenti
  - Validazione incrementale
  - Aggiornamento PATCH ottimizzato

#### Business Logic
- ✅ Calcolo automatico quantità disponibile (quantity - approved_requests)
- ✅ Gestione stati operativi (available/not_available/maintenance/reserved)
- ✅ Validazione quantità e controlli integrità
- ✅ Gestione errori e notifiche

### 🎨 **Interface Utente**

#### Dashboard Personalizzata
- ✅ Layout responsivo mobile-first
- ✅ Statistiche magazzino in tempo reale
- ✅ Navigazione condizionale per ruolo
- ✅ Componenti Vue riutilizzabili

#### Componenti Sviluppati
- `Dashboard.vue` - Pannello principale
- `ManageItems.vue` - Gestione completa articoli
- `CreateItem.vue` - Form creazione
- `EditItem.vue` - Form modifica
- `AuthenticatedLayout.vue` - Layout base

---

## � **Documentazione Completa**

Per approfondimenti tecnici dettagliati, decisioni architetturali e specifiche implementative:

- **[📋 TECHNICAL_DOCS.md](./TECHNICAL_DOCS.md)** - Documentazione tecnica completa
  - Analisi dettagliata Quantity-Based vs Asset-Based approach
  - Giustificazioni delle scelte architetturali 
  - Matrice di decisione e trade-offs
  - Schema database e relazioni
  - Performance e ottimizzazioni
  - Percorsi di migrazione futura

---

## �🗂️ **STRUTTURA CODEBASE**

### Controller
```
app/Http/Controllers/
├── Auth/AuthenticatedSessionController.php    # Login/Logout
├── DashboardController.php                    # Dashboard principale  
├── ProfileController.php                      # Gestione profilo
└── WarehouseController.php                    # CRUD Magazzino completo
    ├── index()          # Dashboard magazzino
    ├── manageItems()    # Lista articoli admin
    ├── createItem()     # Form creazione
    ├── storeItem()      # Salvataggio nuovo
    ├── editItem()       # Form modifica
    ├── updateItem()     # Aggiornamento
    └── destroyItem()    # Eliminazione
```

### Models con Relazioni
```
app/Models/
├── User.php           # hasMany requests
├── Item.php           # hasMany requests  
└── Request.php        # belongsTo user, belongsTo item
```

### Routes Organizzate
```
routes/web.php
├── Auth routes        # Login/logout/register
├── Dashboard          # GET /dashboard
├── Profile routes     # Gestione profilo
└── Warehouse routes   # Gruppo protetto middleware 'can:admin-only'
    ├── GET /warehouse                    # Dashboard magazzino
    ├── GET /warehouse/manage             # Lista articoli
    ├── GET /warehouse/create             # Form creazione
    ├── POST /warehouse                   # Store nuovo
    ├── GET /warehouse/{item}/edit        # Form modifica
    ├── PATCH /warehouse/{item}           # Update
    └── DELETE /warehouse/{item}          # Delete
```

### Vue Components
```
resources/js/Pages/
├── Auth/Login.vue                # Form login
├── Dashboard.vue                 # Dashboard principale
├── Profile/Edit.vue              # Modifica profilo
└── Warehouse/
    ├── Index.vue                 # Dashboard magazzino
    ├── ManageItems.vue          # Gestione completa articoli
    ├── CreateItem.vue           # Form creazione
    └── EditItem.vue             # Form modifica
```

---

## 📊 **DATI DI ESEMPIO**

### Utenti di Test (password: `password`)
```
🔐 Admin: admin@example.com
👤 User:  user@example.com  
+ 10 utenti aggiuntivi con dati realistici
```

### Articoli Magazzino (14 items)
```
📱 Elettronica: laptop, smartphone, tablet, cuffie
🏠 Casa: scrivania, sedia, lampada, aspirapolvere  
📚 Ufficio: notebook, penne, stampante, carta
🎮 Gaming: console, controller
```

### Richieste di Esempio (10 requests)
```
- Richieste simulate da utenti diversi
- Stati: pending, approved, rejected  
- Quantità e note realistiche
```

---

## ⚙️ **CONFIGURAZIONE SVILUPPO**

### Requisiti Sistema
- PHP 8.2+
- Composer 2.0+
- Node.js 18+
- NPM 8+
- MySQL 8.0+

### Setup Manuale
```bash
# 1. Installa dipendenze
composer install
npm install

# 2. Configura ambiente
cp .env.example .env
php artisan key:generate

# 3. Configura database in .env
DB_DATABASE=code_challenge
DB_USERNAME=your_username  
DB_PASSWORD=your_password

# 4. Setup database
php artisan migrate:fresh --seed

# 5. Avvia development
php artisan serve          # Laravel su :8000
npm run dev                # Vite HMR su :5173
```

### Comandi Utili
```bash
# Cache management
php artisan config:clear && php artisan route:clear && php artisan view:clear

# Database
php artisan migrate:fresh --seed    # Ricrea con dati
php artisan db:seed                 # Solo dati

# Assets
npm run build                       # Build produzione
npm run dev                         # Development con HMR
```

---

## 🔧 **FEATURES TECNICHE AVANZATE**

### Laravel
- ✅ **Eloquent ORM** con relazioni complesse
- ✅ **Gates & Policies** per autorizzazioni granulari
- ✅ **Form Requests** con validazione custom
- ✅ **Resource Controllers** RESTful
- ✅ **Middleware** personalizzati
- ✅ **Seeders** con dati realistici usando Faker
- ✅ **Artisan Commands** personalizzati

### Vue.js & Inertia
- ✅ **Composition API** con script setup
- ✅ **Inertia Form Helpers** per gestione stato
- ✅ **Reactive Data** con ref/reactive
- ✅ **Component Props** tipizzati
- ✅ **Event Handling** ottimizzato
- ✅ **Conditional Rendering** per ruoli

### Frontend
- ✅ **Tailwind CSS** con design system coerente
- ✅ **Responsive Design** mobile-first
- ✅ **Component Architecture** modulare
- ✅ **Form Validation** real-time
- ✅ **Loading States** e feedback utente
- ✅ **Modal Confirmations** per azioni critiche

---

## 📁 **STRUTTURA PROGETTO**

```
code_challenge/
├── 📂 app/
│   ├── 📂 Console/Commands/
│   │   └── StartApplication.php         # Comando avvio custom
│   ├── 📂 Http/Controllers/
│   │   ├── 📂 Auth/
│   │   ├── DashboardController.php
│   │   └── WarehouseController.php      # CRUD completo
│   ├── 📂 Models/
│   │   ├── User.php                     # Con ruoli
│   │   ├── Item.php                     # Articoli magazzino
│   │   └── Request.php                  # Richieste
│   └── 📂 Providers/
│       └── AppServiceProvider.php       # Gates autorizzazioni
├── 📂 database/
│   ├── 📂 migrations/                   # Schema completo
│   ├── 📂 seeders/                      # Dati realistici
│   └── 📂 factories/                    # Factory per testing
├── 📂 resources/
│   ├── 📂 js/
│   │   ├── 📂 Components/               # Componenti riutilizzabili
│   │   ├── 📂 Layouts/
│   │   └── 📂 Pages/                    # Route components
│   └── 📂 views/                        # Template Blade base
├── 📂 routes/
│   └── web.php                          # Route organizzate per feature
├── 📄 start-app.sh                      # Script avvio completo
├── 📄 quick-start.sh                    # Script avvio rapido
└── 📄 README.md                         # Questa documentazione
```

---

## 🎯 **OBIETTIVI RAGGIUNTI**

### ✅ Requisiti Tecnici
- [x] Laravel 11 con architettura MVC
- [x] Vue 3 con Composition API
- [x] Inertia.js per SPA experience
- [x] Database relazionale con migrazioni
- [x] Autenticazione e autorizzazioni
- [x] Interface responsiva moderna

### ✅ Business Logic
- [x] Sistema ruoli Admin/User
- [x] CRUD completo articoli magazzino
- [x] Gestione stati e quantità
- [x] Validazioni frontend/backend
- [x] Dashboard personalizzate per ruolo

### ✅ User Experience
- [x] Interface intuitiva e moderna
- [x] Feedback visivo operazioni
- [x] Gestione errori elegante
- [x] Performance ottimizzate
- [x] Mobile responsive

### ✅ Developer Experience  
- [x] Codebase organizzato e documentato
- [x] Scripts automatizzati per setup
- [x] Dati di esempio realistici
- [x] Debugging e logging
- [x] Git workflow strutturato

---

## 🤝 **CONTRIBUTI**

Sviluppato da **Angelo Corbelli** come code challenge.

### Contatti
- 📧 Email: angelo.corbelli81@gmail.com
- 💼 LinkedIn: [Angelo Corbelli](https://www.linkedin.com/in/angelo-corbelli-22bb0716b/)
- 🐙 GitHub: [@AngelKb81](https://github.com/AngelKb81)

---

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
