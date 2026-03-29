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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_id')->unique();
            $table->string('name');
            $table->string('bic_swift', 11)->nullable();
            $table->string('country');
            $table->string('currency');
            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement("CREATE UNIQUE INDEX banks_name_unique ON banks (name) WHERE deleted_at IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS banks_name_unique");
        Schema::dropIfExists('banks');
    }
};
