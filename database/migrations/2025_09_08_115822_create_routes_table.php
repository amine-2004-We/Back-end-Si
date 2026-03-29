<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_code')->unique();

            $table->string('departure_location_name'); 
            $table->decimal('departure_latitude', 10, 7)->nullable();
            $table->decimal('departure_longitude', 10, 7)->nullable();

            $table->string('arrival_location_name'); 
            $table->decimal('arrival_latitude', 10, 7)->nullable();
            $table->decimal('arrival_longitude', 10, 7)->nullable();

            $table->string('transport_mode'); 
            $table->decimal('rate', 8, 2);
            $table->decimal('scale_price', 8, 2);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};