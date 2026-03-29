<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collaborators', function (Blueprint $table) {
            $table->dropColumn([
                'birth_region',
                'birth_province',
                'residence_region',
                'residence_province',
                'assigned_region',
                'assigned_province',
                'bank',
                'organizational_unit',
                'position',
            ]);
        });
        Schema::table('collaborators', function (Blueprint $table) {
            // Add new foreign keys
            $table->foreignId('birth_region_id')->constrained('provinces')->restrictOnDelete();
            $table->foreignId('birth_province_id')->constrained('provinces')->restrictOnDelete();
            $table->foreignId('residence_region_id')->constrained('provinces')->restrictOnDelete();
            $table->foreignId('residence_province_id')->constrained('provinces')->restrictOnDelete();
            $table->foreignId('assigned_region_id')->constrained('provinces')->restrictOnDelete();
            $table->foreignId('assigned_province_id')->constrained('provinces')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departements')->restrictOnDelete();
            $table->foreignId('position_id')->constrained('position')->restrictOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('collaborators', function (Blueprint $table) {
            // Drop new foreign keys
            $table->dropForeign(['birth_province_id']);
            $table->dropForeign(['residence_province_id']);
            $table->dropForeign(['assigned_province_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropColumn([
                'birth_province_id',
                'residence_province_id',
                'assigned_province_id',
                'department_id',
                'position_id'
            ]);
            // Restore old dropped fields
            $table->string('organizational_unit');
            $table->string('position');
            $table->string('birth_region');
            $table->string('residence_region');
            $table->string('assigned_region');
            $table->string('birth_province');
            $table->string('residence_province');
            $table->string('assigned_province');
        });
    }
};
