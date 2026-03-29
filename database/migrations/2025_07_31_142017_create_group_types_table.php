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
        Schema::create('group_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement('CREATE UNIQUE INDEX group_types_name_unique ON group_types (name) WHERE deleted_at IS NULL');
        DB::table('group_types')->insert([
            ['name' => 'Âge homogène'],
            ['name' => 'Mixte'],
            ['name' => 'Niveau différencié'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS group_types_name_unique');
        Schema::dropIfExists('group_types');
    }
};
