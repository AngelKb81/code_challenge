<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Request;
use App\Models\User;
use App\Models\Item;
use Carbon\Carbon;

class RequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ottieni alcuni utenti e item per creare le richieste
        $users = User::where('role', 'user')->get();
        $admins = User::where('role', 'admin')->get();
        $items = Item::all();

        if ($users->isEmpty() || $items->isEmpty()) {
            $this->command->warn('Assicurati di aver eseguito prima UserSeeder e ItemSeeder');
            return;
        }

        $requests = [
            // Richieste approvate e attive
            [
                'user_id' => $users->random()->id,
                'item_id' => ($macbook = $items->where('name', 'MacBook Pro 14" M3')->first()) ? $macbook->id : $items->random()->id,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(20),
                'status' => 'approved',
                'priority' => 'medium',
                'reason' => 'Sviluppo applicazione mobile per nuovo progetto cliente',
                'notes' => 'Necessario per configurazione ambiente di sviluppo iOS',
                'approved_by' => $admins->random()->id,
                'approved_at' => Carbon::now()->subDays(8),
                'created_at' => Carbon::now()->subDays(12),
                'updated_at' => Carbon::now()->subDays(8)
            ],
            [
                'user_id' => $users->random()->id,
                'item_id' => ($monitor = $items->where('name', 'Monitor LG UltraWide 34"')->first()) ? $monitor->id : $items->random()->id,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(25),
                'status' => 'approved',
                'priority' => 'low',
                'reason' => 'Miglioramento postazione di lavoro per aumento produttività',
                'notes' => 'Monitor aggiuntivo per lavoro su più progetti contemporaneamente',
                'approved_by' => $admins->random()->id,
                'approved_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(3)
            ],

            // Richieste in attesa di approvazione
            [
                'user_id' => $users->random()->id,
                'item_id' => ($ipad = $items->where('name', 'iPad Pro 12.9" M2')->first()) ? $ipad->id : $items->random()->id,
                'start_date' => Carbon::now()->addDays(3),
                'end_date' => Carbon::now()->addDays(15),
                'status' => 'pending',
                'priority' => 'high',
                'reason' => 'Presentazione commerciale importante con cliente strategico',
                'notes' => 'Necessario per demo interattiva durante presentazione',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2)
            ],
            [
                'user_id' => $users->random()->id,
                'item_id' => ($projector = $items->where('name', 'Proiettore Epson EB-FH06')->first()) ? $projector->id : $items->random()->id,
                'start_date' => Carbon::now()->addDays(7),
                'end_date' => Carbon::now()->addDays(14),
                'status' => 'pending',
                'priority' => 'medium',
                'reason' => 'Formazione team su nuove procedure aziendali',
                'notes' => 'Per sala conferenze piano secondo, training di una settimana',
                'created_at' => Carbon::now()->subDay(),
                'updated_at' => Carbon::now()->subDay()
            ],

            // Richieste rifiutate
            [
                'user_id' => $users->random()->id,
                'item_id' => ($camera = $items->where('name', 'Telecamera Sony FX30')->first()) ? $camera->id : $items->random()->id,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(10),
                'status' => 'rejected',
                'priority' => 'low',
                'reason' => 'Video personale per social media',
                'notes' => 'Richiesta per uso non aziendale',
                'approved_by' => $admins->random()->id,
                'approved_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(6),
                'updated_at' => Carbon::now()->subDays(3)
            ],

            // Richieste completate
            [
                'user_id' => $users->random()->id,
                'item_id' => ($dell = $items->where('name', 'Dell OptiPlex 7090')->first()) ? $dell->id : $items->random()->id,
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->subDays(10),
                'status' => 'returned',
                'priority' => 'high',
                'reason' => 'Sostituzione temporanea workstation in riparazione',
                'notes' => 'Richiesta urgente per continuità lavorativa',
                'approved_by' => $admins->random()->id,
                'approved_at' => Carbon::now()->subDays(28),
                'created_at' => Carbon::now()->subDays(32),
                'updated_at' => Carbon::now()->subDays(10)
            ],
            [
                'user_id' => $users->random()->id,
                'item_id' => ($microphone = $items->where('name', 'Microfono Shure SM7B')->first()) ? $microphone->id : $items->random()->id,
                'start_date' => Carbon::now()->subDays(20),
                'end_date' => Carbon::now()->subDays(5),
                'status' => 'returned',
                'priority' => 'medium',
                'reason' => 'Registrazione podcast aziendale mensile',
                'notes' => 'Episodio speciale con ospite esterno',
                'approved_by' => $admins->random()->id,
                'approved_at' => Carbon::now()->subDays(18),
                'created_at' => Carbon::now()->subDays(22),
                'updated_at' => Carbon::now()->subDays(5)
            ],

            // Richieste scadute
            [
                'user_id' => $users->random()->id,
                'item_id' => ($accessPoint = $items->where('name', 'Access Point WiFi 6E')->first()) ? $accessPoint->id : $items->random()->id,
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->subDays(2),
                'status' => 'overdue',
                'priority' => 'medium',
                'reason' => 'Test copertura WiFi in nuova area uffici',
                'notes' => 'Installazione temporanea per valutazione prestazioni',
                'approved_by' => $admins->random()->id,
                'approved_at' => Carbon::now()->subDays(13),
                'created_at' => Carbon::now()->subDays(17),
                'updated_at' => Carbon::now()->subDays(13)
            ],

            // Richieste future ad alta priorità
            [
                'user_id' => $users->random()->id,
                'item_id' => ($desk = $items->where('name', 'Scrivania Regolabile Elettrica')->first()) ? $desk->id : $items->random()->id,
                'start_date' => Carbon::now()->addDays(14),
                'end_date' => Carbon::now()->addDays(60),
                'status' => 'pending',
                'priority' => 'high',
                'reason' => 'Setup postazione ergonomica per nuovo dipendente senior',
                'notes' => 'Necessario per onboarding previsto tra due settimane',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'user_id' => $users->random()->id,
                'item_id' => ($chair = $items->where('name', 'Sedia Ergonomica Herman Miller')->first()) ? $chair->id : $items->random()->id,
                'start_date' => Carbon::now()->addDays(14),
                'end_date' => Carbon::now()->addDays(60),
                'status' => 'pending',
                'priority' => 'high',
                'reason' => 'Completamento setup ergonomico postazione senior',
                'notes' => 'Da abbinare alla scrivania regolabile per nuovo dipendente',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        foreach ($requests as $requestData) {
            // Verifica che user_id e item_id esistano
            if (User::find($requestData['user_id']) && Item::find($requestData['item_id'])) {
                Request::create($requestData);
            }
        }
    }
}
