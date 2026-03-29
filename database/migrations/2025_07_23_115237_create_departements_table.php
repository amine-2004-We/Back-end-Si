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
        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->foreignId('type_departement_id')
                  ->constrained('type_departements')
                  ->onDelete('restrict');
            $table->foreignId('departement_id')
                  ->nullable()
                  ->constrained('departements')
                  ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement("CREATE UNIQUE INDEX departements_name_unique ON departements (name) WHERE deleted_at IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         DB::statement("DROP INDEX IF EXISTS departements_name_unique");
        Schema::dropIfExists('departements');

    }
};
