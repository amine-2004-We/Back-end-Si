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
        Schema::create('financial_receptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_installment_id')->constrained()->onDelete('cascade');
            
            $table->timestamp('reception_date')->nullable();
            
            $table->decimal('amount_received', 15, 2)->nullable();
            
            $table->string('reception_mode', 100)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_receptions');
    }
};
