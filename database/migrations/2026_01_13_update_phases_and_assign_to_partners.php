<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Créer/mettre à jour des phases dédiées aux partenaires sans impacter les autres phases
        // On utilise des identifiers stables et le tag "partner" pour les distinguer

        $userId = DB::table('users')->min('id');

        if (!$userId) {
            // Pas d'utilisateur pour remplir created_by -> on évite d'insérer pour ne pas casser la contrainte FK
            return;
        }

        // Corriger le sequence Postgres si nécessaire pour éviter les collisions d'ID
        try {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SELECT setval(pg_get_serial_sequence('phases','id'), (SELECT COALESCE(MAX(id),0) FROM phases) + 1, false)");
            }
        } catch (\Throwable $e) {
            // On ignore si non pertinent (autres SGBD ou absence de séquence)
        }

        $partnerPhases = [
            [
                'phase_identifier' => 'PARTNER_PROSPECT',
                'name' => 'Prospect',
                'name_arabe' => null,
                'tag' => 'partner',
                'status' => 'En cours',
                'created_by' => $userId,
            ],
            [
                'phase_identifier' => 'PARTNER_VALIDATED',
                'name' => 'Validé',
                'name_arabe' => null,
                'tag' => 'partner',
                'status' => 'En cours',
                'created_by' => $userId,
            ],
            [
                'phase_identifier' => 'PARTNER_ACTIVE',
                'name' => 'Actif',
                'name_arabe' => null,
                'tag' => 'partner',
                'status' => 'En cours',
                'created_by' => $userId,
            ],
            [
                'phase_identifier' => 'PARTNER_CLOSED',
                'name' => 'Clôturé',
                'name_arabe' => null,
                'tag' => 'partner',
                'status' => 'En cours',
                'created_by' => $userId,
            ],
        ];

        foreach ($partnerPhases as $row) {
            DB::table('phases')->updateOrInsert(
                ['phase_identifier' => $row['phase_identifier']],
                array_merge($row, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }

        // Mise à jour ciblée: corriger les anciennes valeurs "prospect" mal orthographiées
        // et les faire pointer vers la phase PARTNER_PROSPECT, sans toucher au reste.
        $ids = DB::table('phases')
            ->whereIn('phase_identifier', ['PARTNER_PROSPECT', 'PARTNER_VALIDATED', 'PARTNER_ACTIVE', 'PARTNER_CLOSED'])
            ->pluck('id', 'phase_identifier');

        if (isset($ids['PARTNER_PROSPECT'])) {
            $oldProspectNames = [
                'Prospecting',
                'Prospectig',
                'Procepect',
                'Prospection',
                'En prospection',
                'Prospect',
            ];

            $partnerIdsToUpdate = DB::table('partners')
                ->whereIn('phase_id', function ($q) use ($oldProspectNames) {
                    $q->select('id')
                        ->from('phases')
                        ->whereIn('name', $oldProspectNames);
                })
                ->pluck('id')
                ->all();

            if (!empty($partnerIdsToUpdate)) {
                DB::table('partners')
                    ->whereIn('id', $partnerIdsToUpdate)
                    ->update(['phase_id' => $ids['PARTNER_PROSPECT']]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Avant suppression des phases dédiées, annuler proprement les références éventuelles côté partenaires
        $phaseIds = DB::table('phases')
            ->whereIn('phase_identifier', ['PARTNER_VALIDATED', 'PARTNER_ACTIVE', 'PARTNER_CLOSED'])
            ->pluck('id')
            ->all();

        if (!empty($phaseIds)) {
            DB::table('partners')->whereIn('phase_id', $phaseIds)->update(['phase_id' => null]);
        }

        DB::table('phases')->whereIn('phase_identifier', ['PARTNER_VALIDATED', 'PARTNER_ACTIVE', 'PARTNER_CLOSED'])->delete();
    }
};
