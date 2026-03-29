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
        Schema::table('prospections', function (Blueprint $table) {
            // Enfants totaux par groupe d'âge
            $table->integer('children_0_5_total')->nullable()->after('children_0_5_g');
            $table->integer('children_6_12_total')->nullable()->after('children_6_12_g');
            $table->integer('children_13_18_total')->nullable()->after('children_13_18_g');
            $table->integer('children_total')->nullable()->after('children_13_18_total');
            
            // Jeunes totaux
            $table->integer('youth_total')->nullable()->after('youth_f');
            
            // Adultes totaux
            $table->integer('adults_total')->nullable()->after('adults_f');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospections', function (Blueprint $table) {
            $table->dropColumn([
                'children_0_5_total',
                'children_6_12_total',
                'children_13_18_total',
                'children_total',
                'youth_total',
                'adults_total',
            ]);
        });
    }
};
