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
        Schema::create('status_history', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 50);
            $table->unsignedBigInteger('object_id');
            $table->string('object_type', 50); // partner, candidate, training
            $table->string('status', 50);
            $table->unsignedBigInteger('user_id');
            $table->timestamp('changed_at')->useCurrent();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_history');
    }
};
