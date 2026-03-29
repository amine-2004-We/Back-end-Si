<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->string('parent_id')->unique();
            $table->string('last_name');
            $table->string('first_name');
            $table->enum('gender', ['male', 'female']);
            $table->enum('legal_role', ['father', 'mother', 'legal_guardian']);
            $table->string('primary_phone');
            $table->string('secondary_phone')->nullable();
            $table->text('address');
            $table->string('cin');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('CREATE UNIQUE INDEX parents_cin_unique ON parents (cin) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS parents_cin_unique');
        Schema::dropIfExists('parents');
    }
};
