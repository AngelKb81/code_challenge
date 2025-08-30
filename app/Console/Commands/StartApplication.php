<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Process as SymfonyProcess;

class StartApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start {--dev : Avvia in modalità development con Vite} {--build : Compila assets prima dell\'avvio}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Avvia automaticamente l\'applicazione Code Challenge con tutti i servizi necessari';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Avvio Code Challenge Application...');
        $this->line('==========================================');

        // Verifica dipendenze
        $this->checkDependencies();

        // Installa dipendenze se necessario
        $this->installDependencies();

        // Configura ambiente
        $this->setupEnvironment();

        // Configura database
        $this->setupDatabase();

        // Pulisci cache
        $this->clearCache();

        // Compila assets se richiesto
        if ($this->option('build')) {
            $this->buildAssets();
        }

        // Avvia servers
        $this->startServers();
    }

    private function checkDependencies()
    {
        $this->info('🔍 Verifico dipendenze...');

        $dependencies = [
            'php' => 'PHP',
            'composer' => 'Composer',
            'node' => 'Node.js',
            'npm' => 'NPM'
        ];

        foreach ($dependencies as $command => $name) {
            $result = shell_exec("which $command");
            if (empty($result)) {
                $this->error("❌ $name non è installato!");
                exit(1);
            }
        }

        $this->info('✅ Tutte le dipendenze sono presenti');
    }

    private function installDependencies()
    {
        // Installa dipendenze PHP
        if (!is_dir('vendor')) {
            $this->info('📦 Installo dipendenze PHP...');
            $this->call('install', ['--no-interaction' => true]);
            $this->info('✅ Dipendenze PHP installate');
        }

        // Installa dipendenze Node.js
        if (!is_dir('node_modules')) {
            $this->info('📦 Installo dipendenze Node.js...');
            Process::run('npm install')->throw();
            $this->info('✅ Dipendenze Node.js installate');
        }
    }

    private function setupEnvironment()
    {
        // Crea .env se non esiste
        if (!file_exists('.env')) {
            $this->info('📝 Creo file .env...');
            copy('.env.example', '.env');
            $this->info('✅ File .env creato');
        }

        // Genera chiave applicazione se necessario
        if (!str_contains(file_get_contents('.env'), 'APP_KEY=base64:')) {
            $this->info('🔑 Genero chiave applicazione...');
            $this->call('key:generate');
            $this->info('✅ Chiave applicazione generata');
        }
    }

    private function setupDatabase()
    {
        $this->info('🗄️ Configurazione database...');

        try {
            // Prova a connettersi al database
            $this->call('migrate:status');

            // Chiedi se ricaricare il database
            if ($this->confirm('Vuoi ricaricare il database con dati di esempio?', true)) {
                $this->info('🔄 Ricarico database...');
                $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
                $this->info('✅ Database configurato e popolato');

                $this->line('');
                $this->info('👥 UTENTI DI TEST (password: "password"):');
                $this->line('   🔐 Admin: admin@example.com');
                $this->line('   👤 User:  user@example.com');
                $this->line('');
            }
        } catch (\Exception $e) {
            $this->warn('⚠️ Database non configurato o non raggiungibile');
            $this->line('');
            $this->line('📋 CONFIGURAZIONE DATABASE RICHIESTA:');
            $this->line('1. Assicurati che MySQL sia in esecuzione');
            $this->line('2. Crea un database chiamato "code_challenge"');
            $this->line('3. Configura le credenziali in .env');
            $this->line('');

            if (!$this->confirm('Hai configurato il database?')) {
                $this->error('Configurazione database necessaria. Comando interrotto.');
                exit(1);
            }

            $this->setupDatabase(); // Riprova
        }
    }

    private function clearCache()
    {
        $this->info('🧹 Pulisco cache...');

        $commands = ['config:clear', 'route:clear', 'view:clear', 'cache:clear'];

        foreach ($commands as $command) {
            $this->call($command);
        }

        $this->info('✅ Cache pulita');
    }

    private function buildAssets()
    {
        $this->info('🏗️ Compilo assets per produzione...');
        Process::run('npm run build')->throw();
        $this->info('✅ Assets compilati');
    }

    private function startServers()
    {
        $this->line('');
        $this->info('🎉 APPLICAZIONE PRONTA!');
        $this->line('======================');
        $this->line('');
        $this->line('📍 URL Applicazione: http://localhost:8000');

        if ($this->option('dev')) {
            $this->line('📍 Server Vite (dev): http://localhost:5173');
        }

        $this->line('');
        $this->info('📦 DATI DI ESEMPIO:');
        $this->line('   ✓ 12 utenti creati');
        $this->line('   ✓ 14 articoli magazzino');
        $this->line('   ✓ 10 richieste di esempio');
        $this->line('');

        if ($this->option('dev')) {
            $this->info('🚀 Avvio server Laravel e Vite...');
            $this->line('Premi Ctrl+C per fermare tutto');
            $this->line('');

            // Avvia Laravel serve in background
            $laravel = new SymfonyProcess(['php', 'artisan', 'serve']);
            $laravel->setTimeout(null);
            $laravel->start();

            // Avvia Vite dev server
            $vite = new SymfonyProcess(['npm', 'run', 'dev']);
            $vite->setTimeout(null);
            $vite->start();

            // Mantieni il processo attivo
            while ($laravel->isRunning() || $vite->isRunning()) {
                usleep(100000); // 0.1 secondi
            }
        } else {
            $this->info('🚀 Avvio server Laravel...');
            $this->call('serve');
        }
    }
}
