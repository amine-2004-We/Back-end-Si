<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etbac_files', function (Blueprint $table) {
            $table->id();
            $table->string('etbac_code')->unique()->comment('ETB-[Année]-[N° fichier]');
            $table->date('issue_date')->comment('Date de création système');
            $table->foreignId('bank_account_id')->constrained('project_bank_accounts');
            $table->foreignId('beneficiary_id')->constrained('invoices');
            $table->decimal('amount', 12, 2)->default(0);
            $table->enum('status', ['prepared', 'sent', 'rejected', 'executed'])->default('prepared');
            $table->timestamps();
            $table->softDeletes();

            $table->index('etbac_code');
            $table->index('status');
            $table->index('issue_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etbac_files');
    }
};