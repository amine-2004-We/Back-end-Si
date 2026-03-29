<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programme_pedagogique_e2cng', function (Blueprint $table) {
            // New Fields
            $table->string('type')->after('class_id')->nullable();
            $table->string('professional_option')->after('type')->nullable();

            $table->date('date_prevue')->after('observation')->nullable();
            $table->date('real_start_date')->after('date_prevue')->nullable();
            $table->date('real_end_date')->after('real_start_date')->nullable();

            $table->string('project_pedagogique')->nullable()->change();
            $table->string('metier')->nullable()->change();
            $table->string('ateliers')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('programme_pedagogique_e2cng')) {
            Schema::table('programme_pedagogique_e2cng', function (Blueprint $table) {
                $table->dropColumn(['type', 'professional_option', 'date_prevue', 'real_start_date', 'real_end_date']);
            });
        }
    }
};
