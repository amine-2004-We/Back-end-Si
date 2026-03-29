<?php

namespace App\Imports;

use App\Models\Candidate;
use App\Models\Assurance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

/**
 *class AssurancesImport
 */
class AssurancesImport implements ToCollection, WithHeadingRow
{

    /**
     * @param Collection $rows
     * @return void
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (!isset($row['cin']) || !isset($row['status'])) {
                continue;
            }

            $candidate = Candidate::where('cin', trim($row['cin']))->first();

            if ($candidate) {
                $status = strtolower(trim($row['status']));

                $assurance = Assurance::where('personne_assuree_type', Candidate::class)
                    ->where('personne_assuree_id', $candidate->id)
                    ->withTrashed()
                    ->first();

                if ($status === 'assuree' || $status === 'assurée' || $status === 'traité') {
                    if ($assurance) {
                        $assurance->update(['status' => 'traité']);

                        if ($assurance->trashed()) {
                            $assurance->restore();
                        }
                    } else {
                        Assurance::create([
                            'personne_assuree_type' => Candidate::class,
                            'personne_assuree_id'   => $candidate->id,
                            'insurance_type'        => 'CNSS',
                            'insurance_organization'=> 'CNSS',
                            'affiliation_date'      => now(),
                            'status'                => 'traité',
                        ]);
                    }
                } elseif ($status === 'non assuree' || $status === 'non assurée' || $status === 'non traité') {
                    if ($assurance) {
                        // ✅ NEW LOGIC: Update the status column directly to 'non traité'
                        $assurance->update(['status' => 'non traité']);
                    }
                }
            } else {
                \Illuminate\Support\Facades\Log::warning('Assurance Import: Candidate with CIN ' . $row['cin'] . ' not found.');
            }
        }
    }
}
