<?php

namespace App\Observers;

use App\Models\Program;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Exception;

class ProgramObserver
{
      /**
     * Handle the Program "creating" event.
     * Generates a unique program_id in the format PRG-{code}-{sequence}
     * where sequence is unique for the given code.
     */
    public function creating(Program $program): void
    {
        
        // Log::info('Attempting to generate program_id for new Program', [
        //     'program_name' => $program->name ?? 'N/A',
        //     'provided_id' => $program->program_id,
        //     'program_code' => $program->code ?? 'N/A'
        // ]);

     
        if (empty($program->program_id) && !empty($program->code)) {
            try {
                $code = $program->code;
                $prefix = 'PRG-' . $code . '-';
                $sequenceLength = 4;

                $latestProgram = Program::withTrashed()
                                        ->where('program_id', 'like', $prefix . '%')
                                        ->orderBy('program_id', 'desc')
                                        ->first();
                
                $sequence = 1;

                if ($latestProgram) {
                  
                    $lastId = $latestProgram->program_id;
                  
                    $lastSequence = (int) substr($lastId, strrpos($lastId, '-') + 1);
                    $sequence = $lastSequence + 1;
                }

             
                $program->program_id = $prefix . str_pad($sequence, $sequenceLength, '0', STR_PAD_LEFT);
                
                Log::info('Generated new program_id', ['program_id' => $program->program_id]);

            } catch (Exception $e) {
                Log::error('Error generating program_id in observer for code ' . ($program->code ?? 'N/A') . ': ' . $e->getMessage());
                
                $program->program_id = 'PRG-ERR-' . uniqid();
            }
        }
        
    }
}