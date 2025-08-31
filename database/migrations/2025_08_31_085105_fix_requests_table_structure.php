<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Questa migrazione assicura che la tabella requests abbia tutte le colonne necessarie
     * anche se le migrazioni precedenti sono fallite parzialmente.
     */
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Controllo se le colonne esistono già prima di aggiungerle
            if (!Schema::hasColumn('requests', 'request_type')) {
                $table->enum('request_type', ['existing_item', 'purchase_request'])->default('existing_item')->after('user_id');
            }

            if (!Schema::hasColumn('requests', 'item_name')) {
                $table->string('item_name')->nullable()->after('item_id');
            }

            if (!Schema::hasColumn('requests', 'item_description')) {
                $table->text('item_description')->nullable()->after('item_name');
            }

            if (!Schema::hasColumn('requests', 'item_category')) {
                $table->string('item_category')->nullable()->after('item_description');
            }

            if (!Schema::hasColumn('requests', 'item_brand')) {
                $table->string('item_brand')->nullable()->after('item_category');
            }

            if (!Schema::hasColumn('requests', 'estimated_cost')) {
                $table->decimal('estimated_cost', 10, 2)->nullable()->after('item_brand');
            }

            if (!Schema::hasColumn('requests', 'supplier_info')) {
                $table->string('supplier_info')->nullable()->after('estimated_cost');
            }

            if (!Schema::hasColumn('requests', 'justification')) {
                $table->text('justification')->nullable()->after('supplier_info');
            }
        });

        // Assicuriamoci che item_id sia nullable
        DB::statement('ALTER TABLE requests MODIFY COLUMN item_id BIGINT UNSIGNED NULL');

        // Aggiungi indice se non esiste
        if (!$this->indexExists('requests', 'requests_request_type_index')) {
            DB::statement('CREATE INDEX requests_request_type_index ON requests (request_type)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            if (Schema::hasColumn('requests', 'request_type')) {
                $table->dropColumn('request_type');
            }
            if (Schema::hasColumn('requests', 'item_name')) {
                $table->dropColumn('item_name');
            }
            if (Schema::hasColumn('requests', 'item_description')) {
                $table->dropColumn('item_description');
            }
            if (Schema::hasColumn('requests', 'item_category')) {
                $table->dropColumn('item_category');
            }
            if (Schema::hasColumn('requests', 'item_brand')) {
                $table->dropColumn('item_brand');
            }
            if (Schema::hasColumn('requests', 'estimated_cost')) {
                $table->dropColumn('estimated_cost');
            }
            if (Schema::hasColumn('requests', 'supplier_info')) {
                $table->dropColumn('supplier_info');
            }
            if (Schema::hasColumn('requests', 'justification')) {
                $table->dropColumn('justification');
            }
        });

        // Ripristina item_id come NOT NULL
        DB::statement('ALTER TABLE requests MODIFY COLUMN item_id BIGINT UNSIGNED NOT NULL');

        // Rimuovi indice
        if ($this->indexExists('requests', 'requests_request_type_index')) {
            DB::statement('DROP INDEX requests_request_type_index ON requests');
        }
    }

    /**
     * Check if an index exists
     */
    private function indexExists($table, $index): bool
    {
        $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))
            ->pluck('Key_name')
            ->toArray();

        return in_array($index, $indexes);
    }
};
