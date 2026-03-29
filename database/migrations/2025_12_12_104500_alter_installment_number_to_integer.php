<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('financial_installments', function (Blueprint $table) {
            try {
                $table->integer('installment_number')->nullable(false)->change();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE financial_installments ALTER COLUMN installment_number TYPE INTEGER USING installment_number::integer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('financial_installments', function (Blueprint $table) {
            try {
                $table->smallInteger('installment_number')->nullable(false)->change();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE financial_installments ALTER COLUMN installment_number TYPE SMALLINT USING installment_number::smallint');
            }
        });
    }
};
