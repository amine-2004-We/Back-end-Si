<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ChequeStatusEnum;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->string('cheque_id')->unique();
            $table->string('number');
            $table->date('emission_date');
            $table->decimal('amount', 15, 2);
            
            $table->foreignId('beneficiary_id')->constrained('beneficiaires');

            $table->foreignId('project_bank_account_id')->constrained('project_bank_accounts');

            $table->string('status')->default(ChequeStatusEnum::ISSUED->value);

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};