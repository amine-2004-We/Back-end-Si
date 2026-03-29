<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('class_resource_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_resource_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');  
            $table->json('old_values')->nullable(); 
            $table->json('new_values')->nullable(); 
            $table->string('change_description')->nullable();  
            $table->timestamps();

            // Indexes
            $table->index('class_resource_id');
            $table->index('class_id');
            $table->index('user_id');
            $table->index('created_at');

            // Foreign keys
            $table->foreign('class_resource_id')->references('id')->on('class_resources')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('class')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_resource_histories');
    }
};
