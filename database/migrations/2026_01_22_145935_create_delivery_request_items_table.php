<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_request_id')->constrained('delivery_request')->onDelete('cascade');
            $table->foreignId('article_id')->constrained('articles');
            $table->integer('quantity_requested');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_request_items');
    }
};
