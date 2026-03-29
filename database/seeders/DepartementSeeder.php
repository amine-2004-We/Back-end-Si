<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departement;

class DepartementSeeder extends Seeder
{
    public function run()
    {
        $departements = [
            ['id' => 11, 'name' => 'CONSEIL D’ADMINISTRATION', 'type_departement_id' => 5, 'parent_departement_id' => null],
            ['id' => 1, 'name' => 'PRÉSIDENCE', 'type_departement_id' => 6, 'parent_departement_id' => 11],
            ['id' => 2, 'name' => 'DIRECTION GÉNÉRALE', 'type_departement_id' => 1, 'parent_departement_id' => 1],
            ['id' => 3, 'name' => 'DIRECTION DU DÉVELOPPEMENT & PARTENARIAT', 'type_departement_id' => 2, 'parent_departement_id' => 2],
            ['id' => 4, 'name' => 'DIRECTION DE LA FORMATION (ZAKOURA ACADEMY)', 'type_departement_id' => 2, 'parent_departement_id' => 2],
            ['id' => 5, 'name' => 'DIRECTION DES OPÉRATIONS', 'type_departement_id' => 2, 'parent_departement_id' => 2],
            ['id' => 6, 'name' => 'DÉPARTEMENT COMMUNICATION & MARKETING', 'type_departement_id' => 4, 'parent_departement_id' => 2],
            ['id' => 7, 'name' => 'DIRECTION DES RESSOURCES DES HUMAINES', 'type_departement_id' => 2, 'parent_departement_id' => 2],
            ['id' => 8, 'name' => 'DIRECTION ADMINISTRATIVE & FINANCIÈRE', 'type_departement_id' => 2, 'parent_departement_id' => 2],
            ['id' => 9, 'name' => 'DÉPARTEMENT ACHAT LOGISTIQUE & IT', 'type_departement_id' => 4, 'parent_departement_id' => 2],
            ['id' => 10, 'name' => 'SECRÉTARIAT GÉNÉRAL', 'type_departement_id' => 3, 'parent_departement_id' => 1],
            ['id' => 12, 'name' => 'CHARGÉE DE MISSION AUPRÈS DE LA DIRECTION GÉNÉRALE', 'type_departement_id' => 7, 'parent_departement_id' => 2],
            ['id' => 13, 'name' => 'Comptabilité', 'type_departement_id' => 8, 'parent_departement_id' => 8],
            ['id' => 14, 'name' => 'Contrôle de Gestion', 'type_departement_id' => 8, 'parent_departement_id' => 8],
            ['id' => 15, 'name' => 'Gestion de trésorerie règlement', 'type_departement_id' => 8, 'parent_departement_id' => 8],
        ];

        foreach ($departements as $departement) {
            Departement::updateOrCreate(['id' => $departement['id']], $departement);
        }
    }
}
