<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlanType;

class PlanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planTypes = [
            // -----------------------------
            // Phase 1: Conception
            // -----------------------------
            [
                'phase_id' => 1,
                'task_name' => 'Elaboration du concept',
                'previous_phases_id' => null,
                'order' => 1,
                'responsible_title' => ['Responsable Développement et Partenariat'],
                'duration' => 7,
            ],
            [
                'phase_id' => 1,
                'task_name' => 'Elaboration du budget prévisionnel',
                'previous_phases_id' => null,
                'order' => 1,
                'responsible_title' => ['Responsable Contrôle de Gestion', 'Responsable Administratif et Paie'],
                'duration' => 7,
            ],
            [
                'phase_id' => 1,
                'task_name' => 'Elaboration de projet de convention',
                'previous_phases_id' => null,
                'order' => 2,
                'responsible_title' => ['Responsable Développement et Partenariat', 'Chargé de partenariats'],
                'duration' => 7,
            ],

            // -----------------------------
            // Phase 2: Préparation
            // -----------------------------
            [
                'phase_id' => 2,
                'task_name' => "Prospection pour l'identification du site",
                'previous_phases_id' => 1,
                'order' => 2,
                'responsible_title' => ['Superviseur'],
                'duration' => 7,
            ],
            [
                'phase_id' => 2,
                'task_name' => "Conception du modèle pédagogique",
                'previous_phases_id' => 1,
                'order' => 2,
                'responsible_title' => ['Responsable Développement et Partenariat'],
                'duration' => 21,
            ],
            [
                'phase_id' => 2,
                'task_name' => 'Demande des devis pour les besoins en achats',
                'previous_phases_id' => 1,
                'order' => 2,
                'responsible_title' => ['Responsable Achat'],
                'duration' => 21,
            ],
            [
                'phase_id' => 2,
                'task_name' => 'Lancement du sourcing pour les profils RH adaptés',
                'previous_phases_id' => 1,
                'order' => 2,
                'responsible_title' => ['Directeur des Ressources Humaines', 'Responsable Recrutement'],
                'duration' => 21,
            ],
            [
                'phase_id' => 2,
                'task_name' => 'Préparation du contenu de la formation et sourcing des formateurs et estimation volume horaire formation',
                'previous_phases_id' => 1,
                'order' => 99,
                'responsible_title' => ['Directeur Zakoura Academy', 'Responsable de Formation'],
                'duration' => 21,
            ],
            [
                'phase_id' => 2,
                'task_name' => "Ouverture d'un compte bancaire",
                'previous_phases_id' => 1,
                'order' => 4,
                'responsible_title' => ['Responsable Trésorerie'], // Updated to match DB ID 80
                'duration' => 7,
            ],
            [
                'phase_id' => 2,
                'task_name' => 'Finalisation du budget',
                'previous_phases_id' => 1,
                'order' => 4,
                'responsible_title' => ['Responsable Contrôle de Gestion', 'Responsable Comptable'], // Updated to match DB ID 78
                'duration' => 7,
            ],
            [
                'phase_id' => 2,
                'task_name' => 'Elaboration de la convention finale',
                'previous_phases_id' => 1,
                'order' => 4,
                'responsible_title' => ['Responsable Développement et Partenariat', 'Chargé de partenariats'],
                'duration' => 7,
            ],

            // -----------------------------
            // Phase 3: Concrétisation
            // -----------------------------
            [
                'phase_id' => 3,
                'task_name' => 'Signature de la convention',
                'previous_phases_id' => 2,
                'order' => 4,
                'responsible_title' => ['Responsable Développement et Partenariat'],
                'duration' => 7,
            ],
            [
                'phase_id' => 3,
                'task_name' => 'Communication flash news',
                'previous_phases_id' => 2,
                'order' => 4,
                'responsible_title' => ['Responsable Communication'],
                'duration' => 7,
            ],

            // -----------------------------
            // Phase 4: Opérationnalisation des process
            // -----------------------------
            [
                'phase_id' => 4,
                'task_name' => 'Lancement du recrutement effectif',
                'previous_phases_id' => 3,
                'order' => 4,
                'responsible_title' => ['Responsable Recrutement', 'Directeur des Ressources Humaines'],
                'duration' => 7,
            ],
            [
                'phase_id' => 4,
                'task_name' => 'Lancement des achats effectif',
                'previous_phases_id' => 3,
                'order' => 4,
                'responsible_title' => ['Responsable Achat'],
                'duration' => 21,
            ],
            [
                'phase_id' => 4,
                'task_name' => 'Organisation de la formation',
                'previous_phases_id' => 3,
                'order' => 100,
                'responsible_title' => ['Responsable de Formation', 'Responsable Achat'],
                'duration' => 14,
            ],

            // -----------------------------
            // Phase 5: Déploiement opérationnel
            // -----------------------------
            [
                'phase_id' => 5,
                'task_name' => "Démarrage opérationnel (Réunion d'ouverture)",
                'previous_phases_id' => 4,
                'order' => 101,
                'responsible_title' => ['Éducatrice', 'Animateur'], // Updated to match DB ID 10
                'duration' => 7,
            ],
            [
                'phase_id' => 5,
                'task_name' => "Inscription des bénéficiaires et procéder à l'assurance",
                'previous_phases_id' => 4,
                'order' => 101,
                'responsible_title' => ['Éducatrice', 'Animateur'],
                'duration' => 35,
            ],
            [
                'phase_id' => 5,
                'task_name' => 'Démarrage des activités du programme',
                'previous_phases_id' => 4,
                'order' => 3,
                'responsible_title' => ['Éducatrice', 'Animateur'],
                'duration' => null,
            ],
            [
                'phase_id' => 5,
                'task_name' => 'Rapport moral de démarrage',
                'previous_phases_id' => 4,
                'order' => 102,
                'responsible_title' => ['Chef de Projet', 'Responsable régional', 'Responsable Opérationnel'], // Updated accent for régional (ID 29)
                'duration' => 7,
            ],
            [
                'phase_id' => 5,
                'task_name' => 'Evaluation des bénéficiaires',
                'previous_phases_id' => 4,
                'order' => 5,
                'responsible_title' => ['Éducatrice', 'Animateur'],
                'duration' => null,
            ],
            [
                'phase_id' => 5,
                'task_name' => "Visites de suivi et d'encadrement",
                'previous_phases_id' => 4,
                'order' => 6,
                'responsible_title' => ['Superviseur', 'Responsable Opérationnel', 'Responsable Pédagogique', 'Responsable régional', 'Responsable Pédagogique National', 'Responsable Opérationnel National'],
                'duration' => null,
            ],
            [
                'phase_id' => 5,
                'task_name' => "Rapport d'activité",
                'previous_phases_id' => 4,
                'order' => 7,
                'responsible_title' => ['Chef de Projet', 'Responsable régional'],
                'duration' => null,
            ],
            [
                'phase_id' => 5,
                'task_name' => 'Rapport Final (Moral et financier)',
                'previous_phases_id' => 4,
                'order' => 8,
                'responsible_title' => ['Chef de Projet', 'Responsable régional', 'Responsable Contrôle de Gestion', 'Responsable Administratif et Paie'],
                'duration' => null,
            ],
        ];

        foreach ($planTypes as $index => $planType) {
            $id = $index + 1;
            PlanType::updateOrCreate(
                ['id' => $id],
                $planType
            );
        }
    }
}
