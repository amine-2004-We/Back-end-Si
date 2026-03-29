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
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'zakoura_contribution')) {
                $table->decimal('zakoura_contribution', 15, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('projects', 'partner_amount')) {
                $table->decimal('partner_amount', 15, 2)->nullable()->default(0);
            }
            if (!Schema::hasColumn('projects', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('projects', 'current_phase')) {
                $table->string('current_phase')->default('Pre-projet');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            //
        });
    }
};
