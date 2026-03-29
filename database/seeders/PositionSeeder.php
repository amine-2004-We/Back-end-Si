<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $positions = [
            // DÉPARTEMENT ACHAT LOGISTIQUE & IT (ID: 9)
            ['id' => 1, 'title' => 'Admin SI', 'department_id' => 9],
            ['id' => 2, 'title' => 'API / Formulaire Web', 'department_id' => 9],
            ['id' => 3, 'title' => 'Chargé Achat', 'department_id' => 9],
            ['id' => 4, 'title' => 'Comité réception', 'department_id' => 9],
            ['id' => 5, 'title' => 'IT', 'department_id' => 9],
            ['id' => 6, 'title' => 'Responsable Achat', 'department_id' => 9],
            ['id' => 7, 'title' => 'Utilisateur SI', 'department_id' => 9],

            // DIRECTION DE LA FORMATION (ZAKOURA ACADEMY) (ID: 4)
            ['id' => 8, 'title' => 'Animatrice', 'department_id' => 4],
            ['id' => 9, 'title' => 'Animateur', 'department_id' => 4],
            ['id' => 10, 'title' => 'Éducatrice', 'department_id' => 4],
            ['id' => 55, 'title' => 'Éducateur', 'department_id' => 4],
            ['id' => 12, 'title' => 'Évaluateur', 'department_id' => 4],
            ['id' => 13, 'title' => 'Facilitatrice', 'department_id' => 4],
            ['id' => 14, 'title' => 'Facilitateur', 'department_id' => 4],
            ['id' => 15, 'title' => 'Resp. formation', 'department_id' => 4],
            ['id' => 16, 'title' => 'Responsable pédagogique', 'department_id' => 4],
            ['id' => 17, 'title' => 'Responsable pédagogique local', 'department_id' => 4],
            ['id' => 18, 'title' => 'Responsable pédagogique national', 'department_id' => 4],
            ['id' => 19, 'title' => 'Responsable pédagogique régional', 'department_id' => 4],
            ['id' => 20, 'title' => 'ZA (Zone d\'Activité / Administration de la Formation)', 'department_id' => 4],

            // DIRECTION DES OPÉRATIONS (ID: 5)
            ['id' => 21, 'title' => 'Chef de projet', 'department_id' => 5],
            ['id' => 22, 'title' => 'Coordinateur', 'department_id' => 5],
            ['id' => 23, 'title' => 'Directeur des opérations (DO)', 'department_id' => 5],
            ['id' => 24, 'title' => 'Responsable local', 'department_id' => 5],
            ['id' => 25, 'title' => 'Responsable national', 'department_id' => 5],
            ['id' => 26, 'title' => 'Responsable opérationnel', 'department_id' => 5],
            ['id' => 27, 'title' => 'Responsable opérationnel local', 'department_id' => 5],
            ['id' => 28, 'title' => 'Responsable opérationnel national', 'department_id' => 5],
            ['id' => 29, 'title' => 'Responsable régional', 'department_id' => 5],
            ['id' => 30, 'title' => 'Superviseur', 'department_id' => 5],

            // DIRECTION ADMINISTRATIVE & FINANCIÈRE (ID: 8)
            ['id' => 31, 'title' => 'Assistante', 'department_id' => 8],
            ['id' => 32, 'title' => 'DAF (Directeur Administratif et Financier)', 'department_id' => 8],
            ['id' => 33, 'title' => 'Direction Financière', 'department_id' => 8],
            ['id' => 34, 'title' => 'Finance', 'department_id' => 8],
            ['id' => 35, 'title' => 'Paie', 'department_id' => 8],
            ['id' => 36, 'title' => 'RAF (Responsable Administratif et Financier)', 'department_id' => 8],
            ['id' => 37, 'title' => 'Responsable administratif', 'department_id' => 8],

            // Comptabilité (Service under DAF) (ID: 13)
            ['id' => 38, 'title' => 'Comptabilité', 'department_id' => 13],
            ['id' => 39, 'title' => 'Responsable Comptabilité', 'department_id' => 13],

            // Contrôle de Gestion (Service under DAF) (ID: 14)
            ['id' => 40, 'title' => 'Contrôle de gestion (CG)', 'department_id' => 14],
            ['id' => 41, 'title' => 'Reporting financier', 'department_id' => 14],

            // Gestion de trésorerie règlement (Service under DAF) (ID: 15)
            ['id' => 42, 'title' => 'Trésorerie', 'department_id' => 15],

            // DIRECTION DES RESSOURCES HUMAINES (ID: 7)
            ['id' => 43, 'title' => 'Comité de recrutement', 'department_id' => 7],
            ['id' => 44, 'title' => 'Médecin du travail', 'department_id' => 7],
            ['id' => 45, 'title' => 'Responsable de recrutement', 'department_id' => 7],
            ['id' => 46, 'title' => 'RH (Ressources Humaines)', 'department_id' => 7],

            // DIRECTION DU DÉVELOPPEMENT & PARTENARIAT (ID: 3)
            ['id' => 47, 'title' => 'Chargé de Partenariat', 'department_id' => 3],
            ['id' => 48, 'title' => 'Responsable Partenariat', 'department_id' => 3],
            ['id' => 49, 'title' => 'Responsable partenariat et développement', 'department_id' => 3],

            // DÉPARTEMENT COMMUNICATION & MARKETING (ID: 6)
            ['id' => 50, 'title' => 'Communication', 'department_id' => 6],

            // DIRECTION GÉNÉRALE (ID: 2)
            ['id' => 51, 'title' => 'Comité de validation', 'department_id' => 2],
            ['id' => 52, 'title' => 'Directeur', 'department_id' => 2],
            ['id' => 53, 'title' => 'Direction Générale (DG)', 'department_id' => 2],
            ['id' => 54, 'title' => 'Super Admin', 'department_id' => 2],
        ];

        foreach ($positions as $position) {
            // Using updateOrCreate to avoid duplicates if the seeder is run multiple times.
            // It will find a position by 'id' and update it, or create it if it doesn't exist.
            Position::updateOrCreate(['id' => $position['id']], $position);
        }
    }
}
