<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::dropIfExists('purchase_list_request');
        Schema::dropIfExists('purchase_list_items');
        Schema::dropIfExists('purchase_lists');
        Schema::create('purchase_lists', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_list_id')->unique();           // LA-YYYY-0001
            $table->foreignId('created_by')->constrained('users');  // user consolidateur
            $table->string('emitting_department');                  // ex: "Sales"
            $table->decimal('total_quantity_requested', 12, 2)->default(0);
            $table->enum('priority', ['High','Medium','Low'])->nullable();
            $table->text('observations')->nullable();
            $table->enum('status', ['in_progress', 'sent_for_quote', 'closed'])->default('in_progress');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('purchase_lists');
    }
};
