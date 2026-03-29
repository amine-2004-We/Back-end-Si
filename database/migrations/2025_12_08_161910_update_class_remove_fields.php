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
        Schema::table('class', function(Blueprint $table){
            $table->dropForeign(['activity_leader']);
            $table->dropColumn(['activity_leader','start_date','end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class', function(Blueprint $table){
            $table->foreignId('activity_leader')->constrained('collaborators')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
        });
    }
};
