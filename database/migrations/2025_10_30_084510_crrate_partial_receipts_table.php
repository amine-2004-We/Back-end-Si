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
        Schema::create('partial_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('pv_partial_id')->unique()->nullable();  
            $table->foreignId('delivery_receipt_id')->constrained('delivery_receipts')->onDelete('cascade');
            $table->date('received_at');
            $table->text('observations')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partial_receipts');
    }
};
