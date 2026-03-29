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
        Schema::create('financial_resources', function (Blueprint $table) {
            $table->id();
            $table->string('financial_resources_code');
            $table->string('financial_resources_type');
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->integer('slice');
            $table->integer('amount_received');
            $table->date('slice_date');
            $table->string('currency');
            $table->string('financial_type');
            $table->date('signature_date');
            $table->date('project_start_date');
            $table->date('project_end_date')->nullable();
            $table->string('financial_status');
            $table->string('attachment');
            $table->string('comments')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_resources');
    }
};
