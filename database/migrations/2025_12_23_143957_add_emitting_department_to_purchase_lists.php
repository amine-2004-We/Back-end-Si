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
        Schema::table('purchase_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_lists', 'emitting_department')) {
                $table->foreignId('emitting_department')
                    ->nullable()
                    ->constrained('departements');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_lists', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_lists', 'emitting_department')) {
                $table->dropForeign(['emitting_department']);
                $table->dropColumn('emitting_department');
            }
        });
    }
};
