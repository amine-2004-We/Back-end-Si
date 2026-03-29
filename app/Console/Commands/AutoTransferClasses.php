<?php

namespace App\Console\Commands;

use App\Services\ClassOperationalTrackingService;
use Illuminate\Console\Command;

class AutoTransferClasses extends Command
{
    protected $signature = 'class:auto-transfer {--dry-run : Afficher les changements sans les appliquer}';

    protected $description = 'Transférer automatiquement les classes preschool/INDH à l\'AREF après 2 ans';

    public function __construct(
        protected ClassOperationalTrackingService $trackingService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Mode simulation (--dry-run)');
        }

        try {
            $this->info('Début du transfert automatique des classes...');
            
            $transferredClasses = $this->trackingService->autoTransferClasses();
            
            if ($transferredClasses->isEmpty()) {
                $this->info('Aucune classe à transférer.');
                return self::SUCCESS;
            }

            $this->info("Classes transférées : {$transferredClasses->count()}");
            
            foreach ($transferredClasses as $class) {
                $this->line("  - Classe ID {$class->id} ({$class->class_name}) → Projet ID {$class->transfer_to_project_id}");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erreur lors du transfert automatique : ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
