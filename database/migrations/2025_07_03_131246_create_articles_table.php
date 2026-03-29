<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id')->unique();
            $table->string('name');
            $table->text('specifications')->nullable();
            $table->string('brand')->nullable();
            $table->decimal('reference_price', 10, 2)->nullable();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                ->references('product_id')
                ->on('products');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("create unique index articles_name_unique on articles (name) where deleted_at is null");
    }

    public function down()
    {
        Schema::dropIfExists('articles');
        DB::statement("DROP INDEX IF EXISTS articles_name_unique");
    }
};
