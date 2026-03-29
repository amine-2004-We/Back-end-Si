<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->enum('priority', ['Haute', 'Moyenne', 'Basse'])->nullable()->after('observations');
           $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('priority');
            $table->enum('status', ['draft', 'pending', 'completed', 'rejected'])->default('pending')->change();
        });
    }
};
