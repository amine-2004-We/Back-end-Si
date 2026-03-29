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
        Schema::create('delivery_request', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->restrictOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('applicant')->constrained('collaborators')->restrictOnDelete();
            $table->foreignId('sales_manager')->constrained('collaborators')->restrictOnDelete();
            $table->date('request_date');
            $table->string('request_purpose');
            $table->string('delivery_location');
            $table->date('delivery_date')->nullable();
            $table->string('priority')->nullable();
            $table->string('status');
            $table->string('observations')->nullable();
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
        Schema::dropIfExists('delivery_request');
    }
};
