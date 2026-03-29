<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_provisions', function (Blueprint $table) {
            $table->dropColumn('reference');
            $table->dropColumn('supplier');

            $table->dropForeign(['budget_line_id']);
            $table->dropColumn('budget_line_id');

            $table->nullableMorphs('personneable');

        });
    }

    public function down(): void
    {
        Schema::table('service_provisions', function (Blueprint $table) {
            $table->string('reference')->unique()->after('id');
            $table->string('supplier')->after('amount');
            $table->foreignId('budget_line_id')->constrained('budget_lines')->onDelete('restrict')->after('reference');
            $table->nullableMorphs('personneable');
        });
    }
};
