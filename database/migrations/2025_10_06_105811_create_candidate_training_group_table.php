<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCandidateTrainingGroupTable extends Migration
{
    public function up()
    {
        Schema::create('candidate_training_group', function (Blueprint $table) {
            $table->id();

            $table->foreignId('training_group_id')
                ->constrained('training_groups')
                ->onDelete('cascade');

            $table->foreignId('candidate_id')
                ->constrained('candidates')
                ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['training_group_id', 'candidate_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('candidate_training_group');
    }
}
