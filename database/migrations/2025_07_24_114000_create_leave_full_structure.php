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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->softDeletes();
            $table->timestamps();

        });
        Schema::create('leave', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->constrained('collaborators')->restrictOnDelete();
            $table->string('reason');
            $table->foreignId('leave_type_id')->constrained('leave_types')->restrictOnDelete();
            $table->date('start_date');
            $table->time('start_time');
            $table->date('end_date');
            $table->time('end_time');
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * 'collaborator_id',
     * 'motif',
     * 'leave_type_id',
     * 'start_date',
     * 'start_time',
     * 'end_date',
     * 'end_time',
     */
    public function down(): void
    {
        Schema::dropIfExists('leave');
        Schema::dropIfExists('leave_types');
    }
};
