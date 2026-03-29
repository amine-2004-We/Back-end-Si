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
    // 1. Drop old foreign key first
    Schema::table('groups', function (Blueprint $table) {
        if (Schema::hasColumn('groups', 'group_type_id')) {
            $table->dropForeign(['group_type_id']);
            $table->dropColumn('group_type_id');
        }
    });

    // 2. Add new column separately
    Schema::table('groups', function (Blueprint $table) {
        $table->foreignId('level_id')->nullable()->constrained('levels')->restrictOnDelete();
    });
}

public function down(): void
{
    // 1. Drop new foreign key first
    Schema::table('groups', function (Blueprint $table) {
        if (Schema::hasColumn('groups', 'level_id')) {
            $table->dropForeign(['level_id']);
            $table->dropColumn('level_id');
        }
    });

    // 2. Recreate old column separately
    Schema::table('groups', function (Blueprint $table) {
        $table->unsignedBigInteger('group_type_id')->nullable();
        $table->foreign('group_type_id')->references('id')->on('group_types')->restrictOnDelete();
    });
}

};
