<?php

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
        Schema::table('provisional_acceptances', function (Blueprint $table) {
            //
             if (Schema::hasColumn('provisional_acceptances', 'partial_receipt_id')) {
                $table->dropForeign(['partial_receipt_id']);
                $table->dropColumn('partial_receipt_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provisional_acceptances', function (Blueprint $table) {
            //
            $table->foreignId('partial_receipt_id')
                  ->nullable()
                  ->constrained('partial_receipts')
                  ->onDelete('cascade');
        });
        
    }
};
