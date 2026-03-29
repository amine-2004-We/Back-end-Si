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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id')->unique();
            $table->string('name');
            $table->string('internal_code')->unique();
            $table->string('partner_reference_code')->nullable();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->enum('type', ['Préscolaire', 'École', 'Regroupement', 'Centre', 'Communautaire']);
            $table->integer('number_of_classes');
            $table->enum('status', ['Active', 'Fermée', 'En pause', 'Archivée']);
            $table->foreignId('educator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};