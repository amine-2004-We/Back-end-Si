<?php

namespace Database\Seeders;

use App\Models\StatusPartner;
use Illuminate\Database\Seeder;

class StatusPartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Prospect'],
            ['name' => 'En cours'],
            ['name' => 'Partenaire actif'],
            ['name' => 'Clôturé'],
        ];
        foreach ($statuses as $status) {
            StatusPartner::create($status);
        }
    }
}