<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_list_id')->constrained('purchase_lists')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->unique(['purchase_list_id','article_id']);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('purchase_list_items');
    }
};
