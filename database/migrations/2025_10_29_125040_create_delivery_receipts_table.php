<?php

use App\Enums\DeliveryReceiptStatusEnum;
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
        Schema::create('delivery_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_identifier')->unique();

            // $table->foreignId('order_id')
            //       ->constrained('delivery_orders')
            //       ->onDelete('cascade');

            $table->date('reception_date');

            $table->foreignId('receiver_id')->constrained('users');

            $table->string('storage_location')->nullable();

            $table->enum('status', DeliveryReceiptStatusEnum::values());

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
        Schema::dropIfExists('delivery_receipts');
    }
};
