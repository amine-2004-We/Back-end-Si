<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('label');
            $table->enum('type', ['Fonctionnement', 'Investissement']);
            $table->string('budgetary_area');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement("CREATE UNIQUE INDEX budget_categories_code_unique ON budget_categories (code) WHERE deleted_at IS NULL");
        DB::statement("CREATE UNIQUE INDEX budget_categories_label_unique ON budget_categories (label) WHERE deleted_at IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS budget_categories_code_unique");
        DB::statement("DROP INDEX IF EXISTS budget_categories_label_unique");

        Schema::dropIfExists('budget_categories');
    }
};
