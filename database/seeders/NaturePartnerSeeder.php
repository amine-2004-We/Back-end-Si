<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NaturePartner;

class NaturePartnerSeeder extends Seeder
{
    public function run(): void
    {
        $natures = [
            ['name' => 'Non-governmental organization'],
            ['name' => 'Public institution'],
            ['name' => 'Individual'],
        ];
        foreach ($natures as $nature) {
            NaturePartner::create($nature);
        }
    }
}