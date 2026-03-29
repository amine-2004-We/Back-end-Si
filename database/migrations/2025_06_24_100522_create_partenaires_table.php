<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('partner_name')->unique();
            $table->string('abbreviation', 5);
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->enum('partner_type', ['National', 'International']);
            $table->foreignId('nature_partner_id')->constrained('nature_partners');
            $table->foreignId('structure_partner_id')->constrained('structure_partners');
            $table->foreignId('status_id')->constrained('status_partners');
            $table->text('actions')->nullable();
            $table->text('address')->nullable();
            $table->string('country')->nullable();
            $table->text('note')->nullable();
            $table->string('partner_logo')->nullable();
            $table->foreignId('created_by_id')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};