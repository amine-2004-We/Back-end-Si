<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This migration creates the `reports` table to store "Compte Rendu" objects.
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_id')->unique(); 
            $table->string('type');
            $table->unsignedBigInteger('task_id');
            $table->foreign('task_id')->references('id')->on('tasks');
            $table->string('title');
            $table->date('event_date');
            $table->unsignedBigInteger('author_id');
            $table->foreign('author_id')->references('id')->on('collaborators');
            $table->text('summary');
            $table->text('positive_points')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['Brouillon', 'Validé', 'Archivé']);
            $table->timestamps();
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('collaborators');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drops the `reports` table if the migration is rolled back.
        Schema::dropIfExists('reports');
    }
}
