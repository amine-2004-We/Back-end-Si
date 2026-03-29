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
        Schema::create('calls_for_projects', function (Blueprint $table) {
                      $table->id();

          
            $table->string('title');
            $table->text('description');
            $table->foreignId('responsible_id')->nullable()->constrained('collaborators');
            $table->date('debut_date');
            $table->date('end_date');
             $table->decimal('estimated_budget', 15, 2);
            $table->string('status')->default('Ouvert');
            $table->string('registration_link')->nullable();
            $table->string('type')->nullable();
            $table->text('selection_criteria');
             $table->json('required_documents')->nullable();
            $table->string('offer_type')->nullable();
             $table->foreignId('sponsor_id')->nullable()->constrained('partners');
             $table->date('submission_deadline');
            $table->date('submission_date');
            $table->text('submission_indicators')->nullable();
             $table->text('expertise_areas')->nullable();
            $table->string('target_audience');
            $table->unsignedSmallInteger('project_duration');
               $table->text('potential_profiles')->nullable();
            $table->text('required_resources')->nullable();
          $table->text('work_plan')->nullable();
          $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls_for_projects');
    }
};
