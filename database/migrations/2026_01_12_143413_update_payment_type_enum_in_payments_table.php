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
        // For PostgreSQL, we need to alter the enum type
        DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_type_check");
        DB::statement("ALTER TABLE payments ALTER COLUMN payment_type TYPE VARCHAR(50)");
        DB::statement("UPDATE payments SET payment_type = 'expense_report' WHERE payment_type = 'expense_note'");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_payment_type_check CHECK (payment_type IN ('invoice', 'expense_report'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_type_check");
        DB::statement("UPDATE payments SET payment_type = 'expense_note' WHERE payment_type = 'expense_report'");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_payment_type_check CHECK (payment_type IN ('invoice', 'expense_note'))");
    }
};
