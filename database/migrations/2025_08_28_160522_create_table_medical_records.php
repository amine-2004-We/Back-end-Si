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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->constrained('collaborators')->onDelete('restrict');
            $table->string('medical_records_types');
            $table->date('consultation_date');
            $table->date('filing_date');
            $table->date('sent_insurance_date');
            $table->string('document_issuer')->nullable();
            $table->string('declaration_number');
            $table->string('processing_status');
            $table->string('attachment');
            $table->string('comment')->nullable();
            $table->string('refusal_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
