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
            //
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('set null')->after('project_status_id');
            $table->foreignId('program_type_id')->nullable()->constrained('program_types')->onDelete('set null')->after('program_id');
            $table->dropColumn('partner_amount');
            //add zakoura_amount
            $table->decimal('zakoura_amount', 15, 2)->nullable()->after('zakoura_contribution');
            $table->decimal('zakoura_contribution', 15, 2)->nullable()->change();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            //
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
            $table->dropForeign(['program_type_id']);
            $table->dropColumn('program_type_id');
            $table->dropColumn('zakoura_amount');
            $table->decimal('partner_amount', 15, 2)->nullable()->after('zakoura_contribution');
            $table->decimal('zakoura_contribution', 15, 2)->nullable(false)->default(0)->change();
        });
    }
};
