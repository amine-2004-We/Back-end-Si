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
        Schema::create('delivery_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_receipt_id')
                  ->constrained('delivery_receipts')
                  ->onDelete('cascade');

            $table->foreignId('article_id')->constrained('articles');

            $table->unsignedInteger('quantity_received');

            $table->timestamps();

            $table->unique(['delivery_receipt_id', 'article_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_receipt_items');
    }
};
