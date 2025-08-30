<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateItemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:create 
                            {--name= : The name of the item}
                            {--category= : The category of the item}
                            {--quantity=1 : The quantity of the item (default: 1)}
                            {--brand= : The brand of the item (optional)}
                            {--description= : The description of the item (optional)}
                            {--status=available : The status of the item (default: available)}
                            {--location= : The location of the item (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new item in the warehouse inventory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏭 Warehouse Item Creator');
        $this->info('========================');

        // Validazione e raccolta parametri
        $data = $this->gatherItemData();

        if (!$data) {
            return Command::FAILURE;
        }

        // Conferma prima di creare
        if (!$this->confirmCreation($data)) {
            $this->info('Creation cancelled.');
            return Command::SUCCESS;
        }

        // Creazione item
        try {
            $item = Item::create($data);

            $this->info('');
            $this->info('✅ Item created successfully!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $item->id],
                    ['Name', $item->name],
                    ['Category', $item->category],
                    ['Brand', $item->brand ?: 'N/A'],
                    ['Quantity', $item->quantity],
                    ['Status', $item->status],
                    ['Location', $item->location ?: 'N/A'],
                    ['Created', $item->created_at->format('Y-m-d H:i:s')]
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error creating item: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Gather and validate item data
     */
    private function gatherItemData(): ?array
    {
        $data = [];

        // Nome (obbligatorio)
        $data['name'] = $this->option('name') ?: $this->ask('Item name (required)');
        if (empty($data['name'])) {
            $this->error('Item name is required!');
            return null;
        }

        // Categoria (obbligatoria)
        $data['category'] = $this->option('category') ?: $this->askForCategory();
        if (empty($data['category'])) {
            $this->error('Item category is required!');
            return null;
        }

        // Quantità
        $quantity = $this->option('quantity') ?: $this->ask('Quantity', '1');
        if (!is_numeric($quantity) || $quantity < 1) {
            $this->error('Quantity must be a positive number!');
            return null;
        }
        $data['quantity'] = (int) $quantity;

        // Brand (opzionale)
        $data['brand'] = $this->option('brand') ?: $this->ask('Brand (optional)', '');

        // Description (opzionale)
        $data['description'] = $this->option('description') ?: $this->ask('Description (optional)', '');

        // Status
        $status = $this->option('status') ?: $this->choice(
            'Item status',
            ['available', 'maintenance', 'retired'],
            'available'
        );
        $data['status'] = $status;

        // Location (opzionale)
        $data['location'] = $this->option('location') ?: $this->ask('Location (optional)', '');

        // Genera SKU automaticamente se non specificato
        $data['sku'] = $this->generateSku($data['name'], $data['category']);

        return $data;
    }

    /**
     * Ask for category with suggestions
     */
    private function askForCategory(): string
    {
        // Ottieni categorie esistenti per suggerimenti
        $existingCategories = Item::distinct('category')
            ->whereNotNull('category')
            ->pluck('category')
            ->toArray();

        if (!empty($existingCategories)) {
            $this->info('Existing categories: ' . implode(', ', $existingCategories));
        }

        return $this->ask('Category (required)') ?: '';
    }

    /**
     * Show confirmation before creating item
     */
    private function confirmCreation(array $data): bool
    {
        $this->info('');
        $this->info('📋 Item Preview:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $data['name']],
                ['Category', $data['category']],
                ['Brand', $data['brand'] ?: 'N/A'],
                ['Quantity', $data['quantity']],
                ['Status', $data['status']],
                ['Location', $data['location'] ?: 'N/A'],
                ['SKU', $data['sku']],
                ['Description', Str::limit($data['description'] ?: 'N/A', 50)]
            ]
        );

        return $this->confirm('Create this item?', true);
    }

    /**
     * Generate SKU for the item
     */
    private function generateSku(string $name, string $category): string
    {
        $namePrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        $categoryPrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $category), 0, 3));
        $timestamp = \Carbon\Carbon::now()->format('ymd');
        $random = strtoupper(Str::random(3));

        return "{$categoryPrefix}-{$namePrefix}-{$timestamp}-{$random}";
    }
}
