<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // 1. Add this line

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchase_lists', function (Blueprint $table) {
            // 2. Add this line to delete all existing data first
            DB::table('purchase_lists')->truncate();

            // The rest of your schema change can now run without errors
            $table->dropColumn('emitting_department');

            $table->foreignId('emitting_department')
                  ->constrained('departements')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::table('purchase_lists', function (Blueprint $table) {
            // The down method remains the same
            $table->dropForeign(['emitting_department']);
            $table->dropColumn('emitting_department');
            $table->string('emitting_department');
        });
    }
};
