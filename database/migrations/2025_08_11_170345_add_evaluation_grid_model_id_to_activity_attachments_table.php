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
        Schema::table('activity_attachments', function (Blueprint $table) {
            $table->foreignId('evaluation_grid_model_id')->nullable()->constrained('evaluation_grid')->onDelete('restrict');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_attachments', function (Blueprint $table) {
            //
        });
    }
};
