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
        Schema::table('payments', function (Blueprint $table) {
            // Drop the foreign key constraint first if it exists
            if (Schema::hasColumn('payments', 'invoice_id')) {
                try {
                    $table->dropForeign('payments_invoice_id_foreign');
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->dropColumn('invoice_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
        });
    }
};
