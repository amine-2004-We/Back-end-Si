<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Soft deletes sur purchase_lists
        Schema::table('purchase_lists', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Soft deletes sur purchase_list_request
        Schema::table('purchase_list_request', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Soft deletes sur purchase_list_items
        Schema::table('purchase_list_items', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_lists', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('purchase_list_request', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('purchase_list_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
