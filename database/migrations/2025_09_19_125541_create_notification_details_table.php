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
        Schema::create('notification_details', function (Blueprint $table) {
            $table->id();
             $table->string('title');
            $table->text('text');
            $table->foreignId('sender_id')->constrained('collaborators')->onDelete('cascade'); 
            $table->json('target_url_data'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_details');
    }
};
