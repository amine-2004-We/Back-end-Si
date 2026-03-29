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
        Schema::create('purchase_request_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('budget_category_id')->nullable()->constrained('budget_categories');
            $table->foreignId('budget_line_id')->nullable()->constrained('budget_lines');
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity');
            $table->text('technical_justification')->nullable();
            $table->decimal('estimated_total', 12, 2); // unit_price * quantity
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_lines');
    }
};
