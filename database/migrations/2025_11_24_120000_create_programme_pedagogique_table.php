<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programme_pedagogique', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('class_id')->nullable()->comment('Related class (project class)');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Educator / collaborator');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('subjects')->nullable()->comment('JSON list of selected subjects');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('class_id')->references('id')->on('class')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('programme_pedagogique', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('programme_pedagogique');
    }
};
