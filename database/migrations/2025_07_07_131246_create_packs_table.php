<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pack_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->index('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('article_pack', function (Blueprint $table) {
            $table->unsignedBigInteger('pack_id');
            $table->unsignedBigInteger('article_id');
            $table->integer('quantity')->default(1);
            $table->timestamps();
            $table->primary(['pack_id', 'article_id']); // clé primaire composite
            $table->foreign('pack_id')->references('id')->on('packs');
            $table->foreign('article_id')->references('id')->on('articles');
        });

        DB::statement("create unique index packs_name_unique on packs (name) where deleted_at is null");
    }

    public function down()
    {
        Schema::dropIfExists('article_pack');
        DB::statement("DROP INDEX IF EXISTS packs_name_unique");
        Schema::dropIfExists('packs');
    }
};
