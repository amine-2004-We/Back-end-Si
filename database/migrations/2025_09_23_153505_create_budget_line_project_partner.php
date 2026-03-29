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
        Schema::create('budget_line_project_partner', function (Blueprint $table) {
            $table->id();
            $table->decimal('allocated_amount', 15, 2);
            $table->foreignId('budget_line_project_id')->constrained('budget_line_project')->onDelete('cascade');
            $table->foreignId('partner_id')->constrained('partners')->onDelete('cascade');
            $table->unique(['budget_line_project_id', 'partner_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_line_project_partner');
    }
};
