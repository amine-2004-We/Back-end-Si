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
        Schema::table('externals', function (Blueprint $table) {
            $table->dropColumn('attachments');
            // $table->foreignId('group_id')->constrained('training_groups')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('externals', function (Blueprint $table) {
            $table->json('attachments')->nullable(); 
        });
    }
};
