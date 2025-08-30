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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_use', 'returned', 'overdue'])->default('pending');
            $table->text('reason')->nullable(); // Motivo della richiesta
            $table->text('notes')->nullable(); // Note aggiuntive
            $table->integer('quantity_requested')->default(1);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Admin che ha approvato
            $table->text('rejection_reason')->nullable(); // Motivo del rifiuto
            $table->timestamp('returned_at')->nullable(); // Data di restituzione effettiva
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->timestamps();

            // Indici per migliorare le performance
            $table->index(['user_id', 'status']);
            $table->index(['item_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
