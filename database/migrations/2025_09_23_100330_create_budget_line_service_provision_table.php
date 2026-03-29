<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_line_service_provision', function (Blueprint $table) {
            $table->primary(['budget_line_id', 'service_provision_id']);
            $table->foreignId('budget_line_id')->constrained('budget_lines')->onDelete('cascade');
            $table->foreignId('service_provision_id')->constrained('service_provisions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_line_service_provision');
    }
};
