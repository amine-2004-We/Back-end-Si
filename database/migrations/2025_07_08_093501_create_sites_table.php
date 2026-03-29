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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('site_id')->unique()->nullable();
            $table->string('name');
            $table->string('internal_code')->unique();
            $table->string('partner_reference_code')->nullable();
            $table->enum('type', allowed: ['Rural', 'Urbain', 'Semi-urbain']);

            $table->foreignId('commune_id')
                  ->constrained('communes')
                  ->onDelete('restrict'); 

            $table->foreignId('douar_id')
                  ->nullable()
                  ->constrained('douars')
                  ->onDelete('set null');
            $table->string('country');
            $table->date('start_date');
            $table->enum('status', ['Actif', 'Fermé', 'En pause', 'Archivé'])->default('Actif');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('local_operational_manager_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};