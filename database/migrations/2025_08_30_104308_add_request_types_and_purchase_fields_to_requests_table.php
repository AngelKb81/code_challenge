<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Aggiungere tipo di richiesta
            $table->enum('request_type', ['existing_item', 'purchase_request'])->default('existing_item')->after('user_id');

            // Rendere item_id nullable per purchase_request
            $table->unsignedBigInteger('item_id')->nullable()->change();

            // Campi per purchase request
            $table->string('item_name')->nullable()->after('item_id');
            $table->text('item_description')->nullable()->after('item_name');
            $table->string('item_category')->nullable()->after('item_description');
            $table->string('item_brand')->nullable()->after('item_category');
            $table->decimal('estimated_cost', 10, 2)->nullable()->after('item_brand');
            $table->string('supplier_info')->nullable()->after('estimated_cost');
            $table->text('justification')->nullable()->after('supplier_info'); // Giustificazione per l'acquisto

            // Aggiungere indice per request_type
            $table->index('request_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Rimuovere i campi aggiunti
            $table->dropColumn([
                'request_type',
                'item_name',
                'item_description',
                'item_category',
                'item_brand',
                'estimated_cost',
                'supplier_info',
                'justification'
            ]);

            // Ripristinare item_id come non nullable
            $table->unsignedBigInteger('item_id')->nullable(false)->change();

            // Rimuovere indice
            $table->dropIndex(['request_type']);
        });
    }
};
