<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeDepartement;

class TypeDepartementSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['id' => 5, 'name' => 'CONSEIL D’ADMINISTRATION', 'superior_type_id' => null],
            ['id' => 6, 'name' => 'PRÉSIDENCE', 'superior_type_id' => 5],
            ['id' => 1, 'name' => 'Direction générale', 'superior_type_id' => 6],
            ['id' => 3, 'name' => 'SECRÉTARIAT GÉNÉRAL', 'superior_type_id' => 6],
            ['id' => 2, 'name' => 'Direction', 'superior_type_id' => 1],
            ['id' => 4, 'name' => 'Département', 'superior_type_id' => 1],
            ['id' => 7, 'name' => 'CHARGÉE DE MISSION AUPRÈS DE LA DIRECTION GÉNÉRALE', 'superior_type_id' => 1],
            ['id' => 8, 'name' => 'Service', 'superior_type_id' => 2],
            ['id' => 9, 'name' => 'Administrative', 'superior_type_id' => null],
            ['id' => 10, 'name' => 'Technical', 'superior_type_id' => null],
        ];


        foreach ($data as $item) {
            TypeDepartement::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
