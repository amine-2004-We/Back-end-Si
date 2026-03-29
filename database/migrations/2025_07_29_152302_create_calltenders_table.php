<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calltenders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calltender_id')->unique();
            $table->string('supplier'); 
            $table->text('subject');
            $table->text('purchase_order_refs')->nullable();
            $table->string('calltender_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_amount', 15, 2);
            $table->enum('currency', ['MAD', 'EUR', 'USD']);
            $table->string('conditions_path');
            $table->foreignId('responsible_id')->constrained('users');
            $table->enum('status', [
                'En préparation',
                'Signé',
                'Résilié',
                'Clôturé'
            ])->default('En préparation');
            $table->date('signature_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calltenders');
    }
};