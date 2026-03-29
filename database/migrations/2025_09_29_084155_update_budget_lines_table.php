<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budget_lines', function (Blueprint $table) {
            $table->dropColumn('consumed_amount');
            $table->string('status')->default('Créé')->change();
                    DB::statement('ALTER TABLE budget_lines DROP CONSTRAINT IF EXISTS budget_lines_status_check;');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
