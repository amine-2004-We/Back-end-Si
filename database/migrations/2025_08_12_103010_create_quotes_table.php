<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();

            $table->string('quote_number')->unique();

            $table->foreignId('purchase_request_id')
                ->constrained('purchase_requests')
                ->restrictOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->foreignId('pack_id')->nullable()
                ->constrained('packs')
                ->restrictOnDelete();

            $table->date('quote_date');
            $table->date('valid_until')->nullable();

            $table->string('subject');
            $table->decimal('total_amount_ht', 15, 2);
            $table->decimal('vat_rate', 5, 2);
            $table->decimal('vat_amount', 15, 2);
            $table->decimal('total_amount_ttc', 15, 2);

            $table->unsignedInteger('estimated_delivery_days')->nullable();

            $table->enum('payment_terms', ['upon_receipt', '30d_end_month', 'other'])->nullable();

            $table->string('attachment_path');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');

            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('quote_date');
            $table->index('status');
            $table->index('supplier_id');
            $table->index(['purchase_request_id', 'supplier_id']); // <-- ajouté
        });

        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')
                ->constrained('quotes')
                ->cascadeOnDelete();

            $table->foreignId('article_id')
                ->constrained('articles')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 3); // <-- précision plus fine
            $table->decimal('unit_price_ht', 15, 2);

            $table->timestamps();

            $table->unique(['quote_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
    }
};
