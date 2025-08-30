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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->enum('status', ['available', 'not_available', 'maintenance', 'reserved'])->default('available');
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('serial_number')->nullable()->unique();
            $table->string('location')->nullable(); // Posizione nel magazzino
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable(); // Per foto dell'articolo
            $table->timestamps();

            // Indici per migliorare le performance
            $table->index(['category', 'status']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
