<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provisional_acceptance_partial_receipt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provisional_acceptance_id')->constrained('provisional_acceptances')->onDelete('cascade');
            $table->foreignId('partial_receipt_id')->constrained('partial_receipts')->onDelete('cascade');
            $table->timestamps();
            $table->unique(
                ['provisional_acceptance_id', 'partial_receipt_id'],
                'uniq_prov_acc_partial_receipt'
            );});
    }

    public function down(): void
    {
        Schema::dropIfExists('provisional_acceptance_partial_receipt');
    }
};
