<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Prima aggiorniamo tutti gli items con status obsoleti
        DB::statement("UPDATE items SET status = 'available' WHERE status IN ('low_stock', 'out_of_stock')");

        // Poi aggiorniamo l'enum per rimuovere gli status obsoleti
        DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('available', 'not_available', 'maintenance', 'reserved') DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ripristina l'enum con tutti gli status
        DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('available', 'not_available', 'maintenance', 'reserved', 'low_stock', 'out_of_stock') DEFAULT 'available'");
    }
};
