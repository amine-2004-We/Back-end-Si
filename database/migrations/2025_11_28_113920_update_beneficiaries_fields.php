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
        Schema::table('beneficiaires', function (Blueprint $table) {
            //
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->string('fr_grade_s1')->nullable();
            $table->string('fr_grade_s2_minus_1')->nullable();
            $table->string('fr_grade_s2')->nullable();
            $table->string('maths_grade_s2_minus_1')->nullable();
            $table->string('maths_grade_s1')->nullable();
            $table->string('maths_grade_s2')->nullable();
            $table->string('insurance_status')->nullable()->default('Non assuré');
            $table->boolean('is_validated')->nullable()->default(false);
            






            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            //
            $table->dropColumn('country_id');
            $table->dropForeign(['country_id']);
            $table->dropColumn('fr_grade_s1');
            $table->dropColumn('fr_grade_s2_minus_1');
            $table->dropColumn('fr_grade_s2');
            $table->dropColumn('maths_grade_s2_minus_1');
            $table->dropColumn('maths_grade_s1');
            $table->dropColumn('maths_grade_s2');
            $table->dropColumn('insurance_status');
            $table->dropColumn('is_validated');
        });
    }
};
