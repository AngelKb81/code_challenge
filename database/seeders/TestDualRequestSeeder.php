<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Request;

class TestDualRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        // Test 1: Richiesta per item esistente
        $existingItemRequest = Request::create([
            'user_id' => $user->id,
            'request_type' => 'existing_item',
            'item_id' => 1, // Assumi che esista
            'start_date' => '2025-09-01',
            'end_date' => '2025-09-15',
            'reason' => 'Test richiesta per item esistente',
            'priority' => 'medium',
            'quantity_requested' => 1,
            'status' => 'pending'
        ]);

        // Test 2: Richiesta di acquisto
        $purchaseRequest = Request::create([
            'user_id' => $user->id,
            'request_type' => 'purchase_request',
            'item_name' => 'Monitor 4K Dell UltraSharp',
            'item_description' => 'Monitor professionale per sviluppo software con risoluzione 4K e USB-C',
            'item_category' => 'Monitor',
            'item_brand' => 'Dell',
            'estimated_cost' => 599.99,
            'supplier_info' => 'Amazon Business o Dell Direct',
            'justification' => 'Necessario per migliorare la produttività del team di sviluppo con doppio monitor setup',
            'start_date' => '2025-09-01',
            'end_date' => '2025-12-31',
            'reason' => 'Upgrade postazione di lavoro per efficienza',
            'priority' => 'high',
            'quantity_requested' => 2,
            'status' => 'pending'
        ]);

        $this->command->info("✅ Richiesta existing_item creata: ID {$existingItemRequest->id}");
        $this->command->info("✅ Richiesta purchase_request creata: ID {$purchaseRequest->id}");
        $this->command->info("🎯 Dual Request System test completato!");
    }
}
