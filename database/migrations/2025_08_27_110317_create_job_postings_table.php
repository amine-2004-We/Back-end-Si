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
            Schema::create('job_postings', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->foreignId('project_id')->constrained('projects');
                $table->foreignId('position_id')->constrained('position');
                $table->date('launch_date');
                $table->date('closing_date');
                $table->enum('type', ['Interne', 'Externe', 'Interne et Externe']);
                $table->enum('status', ['En préparation', 'Publié', 'Clôturé','Annulé']);
                $table->softDeletes();
                 $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
