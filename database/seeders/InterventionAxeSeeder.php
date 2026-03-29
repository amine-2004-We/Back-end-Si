<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InterventionAxeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        DB::table('intervention_axes')->insert([
            [
                'name' => 'Education',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Empowerment',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}