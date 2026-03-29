<?php

namespace App\Exports;

use App\Models\Candidate;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 *class UninsuredExport
 */
class UninsuredExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder|Relation|Builder
     */
    public function query()
    {
        return Candidate::whereHas('assurances', function ($q) {
            $q->where('status', 'non traité');
        });
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return [
            'Nom Complet',
            'CIN',
            'Poste',
            'Date de formation',
            'status',
        ];
    }

    /**
     * @param $candidate
     * @return array
     */
    public function map($candidate): array
    {
        $status = $candidate->assurances()->latest()->first()?->status ?? 'non traité';

        return [
            $candidate->first_name . ' ' . $candidate->last_name,
            $candidate->cin,
            $candidate->jobPosting?->name ?? 'N/A',
            $candidate->created_at->format('Y-m-d'),
            $status,
        ];
    }

}
