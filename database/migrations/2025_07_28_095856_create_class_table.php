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
        Schema::create('class_types',function (Blueprint $table){
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('class_status',function (Blueprint $table){
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('class', function (Blueprint $table) {
            $table->id();
            $table->string('class_id')->unique();
            $table->string('class_name');
            $table->string('class_code')->unique();
            $table->string('external_reference_code');
            $table->foreignId('class_type_id')
                ->constrained('class_types')
                ->restrictOnDelete();
            $table->integer('current_workforce'); //-> Effectif actuel
            $table->foreignId('activity_leader')->constrained('collaborators')->restrictOnDelete();
            $table->foreignId('local_pedagogical_coordinator')->constrained('collaborators')->restrictOnDelete();
            $table->foreignId('class_status_id')->constrained('class_status')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_types');
        Schema::dropIfExists('class_status');
        Schema::dropIfExists('class');
    }
};
