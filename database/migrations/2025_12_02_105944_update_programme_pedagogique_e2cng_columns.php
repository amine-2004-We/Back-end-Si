<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programme_pedagogique_e2cng', function (Blueprint $table) {
            $table->string('ateliers')->nullable()->change();
            $table->string('project_pedagogique')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('programme_pedagogique_e2cng')) {
            Schema::table('programme_pedagogique_e2cng', function (Blueprint $table) {
                $table->json('ateliers')->nullable()->change();
            });
        }
    }
};
