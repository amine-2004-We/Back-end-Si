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
        Schema::table('beneficiaires', function (Blueprint $table) {
            if (Schema::hasColumn('beneficiaires', 'parent_phone')) {
                $table->dropColumn('parent_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            if (!Schema::hasColumn('beneficiaires', 'parent_phone')) {
                $table->string('parent_phone')->nullable();
            }
        });
    }
};
