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

        Schema::table('contact_people', function (Blueprint $table) {
            // Rendre nullable
            $table->string('last_name')->nullable()->change();
            $table->string('first_name')->nullable()->change();
            $table->string('phone')->nullable()->change();

            // Rendre required (enlever le nullable s'il existait)
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_people', function (Blueprint $table) {
            // On remet comme c'était avant (ajuster selon l'état initial)
            $table->string('last_name')->nullable(false)->change();
            $table->string('first_name')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
           
        });
    }
};
