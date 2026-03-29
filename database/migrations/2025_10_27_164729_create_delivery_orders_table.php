<?php

use Illuminate\Container\Attributes\DB;
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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders');
           $table->foreignId('supplier_id')->constrained('suppliers');
           $table->foreignId('quote_id')->nullable()->constrained('quotes');
           $table->string('delivery_address');
          $table->date('expected_delivery_date');
            $table->text('comments')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });

       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
