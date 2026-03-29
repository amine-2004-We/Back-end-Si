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
        Schema::table('programs', function (Blueprint $table) {
            //
             $table->foreignId('intervention_axis_id')
                  ->nullable() 
                  ->after('title')
                  ->constrained('intervention_axes'); 
            $table->dropColumn('type');
            $table->foreignId('program_type_id')->nullable()->constrained('program_types');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            //
            $table->dropForeign(['intervention_axis_id']);
            $table->dropColumn('intervention_axis_id');
            $table->string('type')->nullable();
            $table->dropForeign(['program_type_id']);
            $table->dropColumn('program_type_id');
        });
    }
};
