<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_id')->unique();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('class_id')->constrained('class');
            $table->foreignId('group_type_id')->constrained('group_types');
            $table->foreignId('educator_id')->nullable()->constrained('users');
            $table->integer('current_headcount')->default(0);
            $table->integer('target_capacity')->nullable();
            $table->enum('status', ['active', 'closed', 'paused'])->default('active');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->foreignId('created_by')->constrained('users');
            $table->softDeletes();
        });

        DB::statement('CREATE UNIQUE INDEX groups_group_name_unique ON groups (name) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS groups_group_name_unique');
        Schema::dropIfExists('groups');
    }
};
