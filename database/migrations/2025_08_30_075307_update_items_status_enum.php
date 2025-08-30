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
        // In MySQL, dobbiamo usare DB::statement per modificare un ENUM
        DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('available', 'not_available', 'maintenance', 'reserved', 'low_stock', 'out_of_stock') DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ripristina l'enum originale
        DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('available', 'not_available', 'maintenance', 'reserved') DEFAULT 'available'");
    }
};
