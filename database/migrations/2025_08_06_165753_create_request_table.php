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
        Schema::create('request', function (Blueprint $table) {
            $table->id();
            $table->string('pattern')->nullable();
            $table->integer('amount')->nullable();
            $table->integer('month')->nullable();
            $table->foreignId('collaborator_id')->constrained('collaborators')->restrictOnDelete();
            $table->foreignId('request_type_id')->constrained('request_type')->restrictOnDelete();
            $table->string('request_status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request');
    }
};
