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
        //drop program type maps table
        Schema::dropIfExists('program_type_maps');
        Schema::table('program_types', function (Blueprint $table) {
            //
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_types', function (Blueprint $table) {
            //
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });
    }
};
