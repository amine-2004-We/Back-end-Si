<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('article_pack');
        Schema::dropIfExists('packs');

        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->string('pack_id')->unique(); // identifiant métier
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->index('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_pack', function (Blueprint $table) {
            $table->unsignedBigInteger('pack_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->primary(['pack_id', 'product_id']);
            $table->foreign('pack_id')->references('id')->on('packs');
            $table->foreign('product_id')->references('id')->on('products');
        });

        // Index unique conditionnel si besoin
         DB::statement("CREATE UNIQUE INDEX packs_name_unique ON packs (name) WHERE deleted_at IS NULL");
    }

    public function down()
    {
        // Supprimer d’abord les tables dépendantes
        Schema::dropIfExists('product_pack');
        Schema::dropIfExists('packs');
    }
};
