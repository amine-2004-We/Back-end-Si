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
        Schema::table('delivery_request', function (Blueprint $table) {
            $table->renameColumn('sales_manager', 'recipient');
            
            $table->string('recipient_contact')->nullable()->after('recipient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_request', function (Blueprint $table) {
            $table->dropColumn('recipient_contact');
            
            $table->renameColumn('recipient', 'sales_manager');
        });
    }
};