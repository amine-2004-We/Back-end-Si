<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('beneficiary_parent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained('beneficiaires');
            $table->foreignId('parent_id')->constrained('parents');
            $table->enum('legal_role', ['father','mother','legal_guardian'])->nullable();
            $table->timestamps();
            $table->unique(['beneficiary_id','parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiary_parent');
    }
};
