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
        Schema::create('cabinets', function (Blueprint $table) {
            $table->id();
            $table->string('cabinet_id')->unique();
            $table->string('name');
            $table->foreignId('responsible_id')
            ->constrained('collaborators');
            $table->string('contact_phone');
            $table->string('contact_email');
            $table->string('address');
            $table->string('contract')
                ->nullable();
            $table->string('average_rating');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabinets');
    }
};
