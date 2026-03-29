<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('budget_line_project', 'engaged_amount')) {
            Schema::table('budget_line_project', function (Blueprint $table) {
                $table->decimal('engaged_amount', 15, 2)
                    ->after('unit_amount')
                    ->default(0)
                    ->comment('Montant engagé pour ce budget sur ce projet');
            });
        }
    }

    public function down()
    {
        Schema::table('budget_line_project', function (Blueprint $table) {
            $table->dropColumn('engaged_amount');
        });
    }
};
