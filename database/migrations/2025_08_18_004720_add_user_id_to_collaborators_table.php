<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('collaborators', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->constrained('users');
        });
        Schema::table('partners', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->constrained('users');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type')->nullable(); // 'collaborator' | 'partner'
        });
    }
    public function down(): void {
        Schema::table('collaborators', fn (Blueprint $t) => $t->dropConstrainedForeignId('user_id'));
        Schema::table('partners', fn (Blueprint $t) => $t->dropConstrainedForeignId('user_id'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('account_type'));
    }
};
