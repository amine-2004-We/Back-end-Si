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
        Schema::create('presence_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->date('event_date');
            
            $table->json('participants'); 

            $table->foreignId('declared_by')->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes(); // <-- ADD THIS LINE

            $table->unique(['task_id', 'event_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presence_sheets');
    }
};