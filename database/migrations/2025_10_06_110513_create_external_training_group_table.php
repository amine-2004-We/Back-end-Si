<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExternalTrainingGroupTable extends Migration
{
    public function up()
    {
        Schema::create('external_training_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_id')->constrained('externals')->onDelete('cascade');
            $table->foreignId('training_group_id')->constrained('training_groups')->onDelete('cascade');
            $table->enum('training_evaluation', ['Réussite', 'Echec', 'A revoir'])->nullable();
            $table->enum('satisfaction_evaluation', ['Oui', 'Non'])->nullable();
            $table->timestamps();

            $table->unique(['external_id', 'training_group_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('external_training_group');
    }
}
