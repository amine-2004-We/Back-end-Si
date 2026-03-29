<?php

namespace Database\Seeders;

use App\Models\StructurePartner;
use Illuminate\Database\Seeder;

class StructurePartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $structures = [
            ['name' => 'Public'],
            ['name' => 'Private'],
            ['name' => 'Association'],
            ['name' => 'Cooperative'],
        ];
        foreach ($structures as $structure) {
            StructurePartner::create($structure);
        }
    }
}