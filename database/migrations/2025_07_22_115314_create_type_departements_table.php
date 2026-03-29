<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('type_departements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('superior_type_id')
                  ->nullable()
                  ->constrained('type_departements')
                  ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("CREATE UNIQUE INDEX type_departements_name_unique ON type_departements (name) WHERE deleted_at IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS type_departements_name_unique");
        Schema::dropIfExists('type_departements');
    }
};
