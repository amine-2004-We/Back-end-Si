<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            $table->string('po_number')->unique();

            $table->foreignId('quote_id')
                  ->constrained('quotes')
                  ->restrictOnDelete();

            $table->foreignId('supplier_id')
                  ->constrained('suppliers')
                  ->restrictOnDelete();

            $table->foreignId('issuer_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->text('subject');
            $table->date('issue_date');

            $table->decimal('total_amount_ttc', 15, 2)->default(0);

            $table->enum('currency', ['MAD', 'EUR', 'USD']);
            $table->enum('payment_method', ['transfer', 'check', 'cash']);
            $table->unsignedInteger('delivery_lead_time_days')->nullable();
            $table->string('terms_file_path')->nullable();

            $table->enum('status', ['draft', 'in_review', 'approved', 'cancelled'])
                  ->default('draft');

            $table->boolean('validated_by_procurement_manager')->default(false);
            $table->boolean('validated_by_controlling')->default(false);
            $table->boolean('validated_by_board')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['issue_date']);
            $table->index(['status']);
            $table->index(['supplier_id']);
        });

        Schema::create('purchase_order_purchase_request', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                  ->constrained('purchase_orders')
                  ->cascadeOnDelete();

            $table->foreignId('purchase_request_id')
                  ->constrained('purchase_requests')
                  ->restrictOnDelete();

            $table->timestamps();

            $table->unique(['purchase_order_id', 'purchase_request_id'], 'po_pr_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_purchase_request');
        Schema::dropIfExists('purchase_orders');
    }
};
