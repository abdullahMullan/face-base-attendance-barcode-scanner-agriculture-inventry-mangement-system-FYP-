<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_type ENUM('cash', 'partial', 'credit') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("UPDATE sales SET payment_type = 'credit' WHERE payment_type = 'partial'");
        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_type ENUM('cash', 'credit') NOT NULL");
    }
};
