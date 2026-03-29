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
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'unit_id')) {
                $table->dropColumn('unit_id');
            }
            if (!Schema::hasColumn('articles', 'unit')) {
                $table->enum('unit', [
                    'mètre',
                    'unité',
                    'ramette',
                    'boîte',
                    'pièce',
                    'litre',
                    'bidon',
                    'session',
                    'jour',
                    'Autre'
                ])->nullable()->after('reference_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('unit');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
        });
    }
};
