<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_line_partner', function (Blueprint $table) {
            $table->id();
            $table->decimal('allocated_amount', 15, 2);
            $table->foreignId('budget_line_id')->constrained('budget_lines')->onDelete('restrict');
            $table->foreignId('partner_id')->constrained('partners')->onDelete('restrict');
            $table->unique(['budget_line_id', 'partner_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_line_partner');
    }
};
