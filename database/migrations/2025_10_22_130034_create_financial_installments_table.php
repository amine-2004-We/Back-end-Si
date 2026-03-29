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
        Schema::create('financial_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convention_id')->constrained()->onDelete('cascade');
                        $table->unsignedSmallInteger('installment_number');
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->text('trigger_condition');
            $table->enum('status', [
                'Prévue',
                'En attente',
                'Reçue',
                'Retardée'
            ])->default('Prévue');
            $table->boolean('is_ttc')->default(false);
            $table->string('proof_document')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_installments');
    }
};
