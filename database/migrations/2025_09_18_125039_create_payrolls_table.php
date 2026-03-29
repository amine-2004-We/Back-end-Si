<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_code')->unique();
            $table->foreignId('collaborator_id')
                  ->constrained('collaborators')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->date('period');
            $table->unsignedInteger('unjustified_absences_days')->default(0);
            $table->unsignedInteger('justified_absences_days')->default(0);
            $table->unsignedInteger('maternity_days')->default(0);
            $table->unsignedInteger('backpay_days')->default(0);
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->json('benefits')->nullable();
            $table->decimal('benefits_total', 12, 2)->default(0.00);
            $table->json('deductions')->nullable();
            $table->decimal('deductions_total', 12, 2)->default(0.00);
            $table->decimal('net_amount', 12, 2);
            $table->string('status')->default('Préparée');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['collaborator_id', 'period']);
            $table->index('period');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
