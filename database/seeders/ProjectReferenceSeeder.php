<?php

namespace Database\Seeders;

use App\Models\InterventionAxis;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectReferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $axes = [
            ['name' => 'Éducation', 'code' => 'EDU'],
            ['name' => 'Employabilité', 'code' => 'EMP'],
            ['name' => 'Entreprenariat', 'code' => 'ENT'],
            ['name' => 'Action Sociale', 'code' => 'SOC'],
        ];

        foreach ($axes as $axis) {
            InterventionAxis::firstOrCreate(
                ['code' => $axis['code']],
                ['name' => $axis['name']]
            );
        }
    }
}
