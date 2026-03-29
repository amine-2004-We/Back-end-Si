<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
             if (Schema::hasColumn('projects', 'region')) {
                $table->dropColumn('region');
            }
             $table->foreignId('region_id')->nullable()->constrained('regions')->restrictOnDelete();
             $table->foreignId('province_id')->nullable()->constrained('provinces')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
             $table->dropForeign(['region_id']);
            $table->dropForeign(['province_id']);
            $table->string('region')->nullable();
        });
    }
};
