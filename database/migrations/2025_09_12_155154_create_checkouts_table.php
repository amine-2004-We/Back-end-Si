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
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('operation_type');
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->decimal('initiale_amount', 8, 2);
            $table->decimal('used_amount', 8, 2);
            $table->decimal('available_balance');
            $table->foreignId('collaborator_id')->constrained('collaborators')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
