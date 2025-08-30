<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;

class UpdateItemsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:update-status {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of all items based on current quantity and active requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info('🔄 Analyzing item statuses...');

        $items = Item::all();
        $updatedCount = 0;
        $totalCount = $items->count();

        $this->withProgressBar($items, function ($item) use (&$updatedCount, $isDryRun) {
            $currentStatus = $item->status;
            $calculatedStatus = $item->calculateStatus();
            $availableQuantity = $item->getAvailableQuantity();

            if ($currentStatus !== $calculatedStatus) {
                if (!$isDryRun) {
                    $item->update(['status' => $calculatedStatus]);
                }

                $this->newLine();
                $this->info("📦 {$item->name}:");
                $this->line("   Quantità totale: {$item->quantity}");
                $this->line("   Quantità disponibile: {$availableQuantity}");
                $this->line("   Status: {$currentStatus} → {$calculatedStatus}");

                if ($isDryRun) {
                    $this->warn("   [DRY RUN] Aggiornamento simulato");
                } else {
                    $this->info("   ✅ Aggiornato");
                }

                $updatedCount++;
            }
        });

        $this->newLine(2);

        if ($isDryRun) {
            $this->info("🔍 DRY RUN completato:");
            $this->line("   📊 {$updatedCount}/{$totalCount} articoli necessitano aggiornamento");
            $this->line("   💡 Esegui senza --dry-run per applicare le modifiche");
        } else {
            $this->info("✅ Aggiornamento completato:");
            $this->line("   📊 {$updatedCount}/{$totalCount} articoli aggiornati");

            if ($updatedCount === 0) {
                $this->info("   🎉 Tutti gli status erano già corretti!");
            }
        }

        return Command::SUCCESS;
    }
}
