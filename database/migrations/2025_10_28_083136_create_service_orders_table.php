<?php

use App\Enums\ServiceOrderStatusEnum;
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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('service_order_identifier')->unique();

            $table->foreignId('purchase_order_id')->constrained('purchase_orders');

            $table->text('subject');

            $table->date('start_date');

            $table->date('estimated_end_date')->nullable();

            $table->foreignId('supplier_id')->constrained('suppliers');

            $table->foreignId('supervisor_id')->constrained('users');

            $table->string('signed_document_path');

            $table->enum('status', ServiceOrderStatusEnum::values());

            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
