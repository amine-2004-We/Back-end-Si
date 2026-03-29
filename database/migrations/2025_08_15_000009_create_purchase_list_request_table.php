<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::dropIfExists('purchase_list_request');
        Schema::create('purchase_list_request', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_list_id')->constrained('purchase_lists')->cascadeOnDelete();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->unique(['purchase_list_id','purchase_request_id']);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('purchase_list_request');
    }
};
