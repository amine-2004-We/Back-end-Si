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
        if (Schema::hasTable('status_history')) {
            Schema::table('status_history', function (Blueprint $table) {

                if (!Schema::hasColumn('status_history', 'domain')) {
                    $table->string('domain', 50)->after('id');
                }

                if (!Schema::hasColumn('status_history', 'object_id')) {
                    $table->unsignedBigInteger('object_id');
                }

                if (!Schema::hasColumn('status_history', 'object_type')) {
                    $table->string('object_type', 50)->comment('partner, candidate, training');
                }

                if (!Schema::hasColumn('status_history', 'status')) {
                    $table->string('status', 50);
                }

                if (!Schema::hasColumn('status_history', 'user_id')) {
                    $table->unsignedBigInteger('user_id');
                }

                if (!Schema::hasColumn('status_history', 'changed_at')) {
                    $table->timestamp('changed_at')->useCurrent();
                }

                if (!Schema::hasColumn('status_history', 'comment')) {
                    $table->text('comment')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('status_history')) {
            Schema::table('status_history', function (Blueprint $table) {
                $columns = ['domain', 'object_id', 'object_type', 'status', 'user_id', 'changed_at', 'comment'];
                $table->dropColumn($columns);
            });
        }
    }
};
