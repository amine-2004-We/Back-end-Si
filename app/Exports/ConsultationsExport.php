<?php

namespace App\Exports;

use App\Models\Beneficiary;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ConsultationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    private const DEFAULT_STYLE = [
        'font' => [
            'size' => 10,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     * * Remplacer FromCollection par FromQuery pour un meilleur usage de la mémoire.
     * La méthode collection() est remplacée par query().
     */
    public function query()
    {
        return Beneficiary::query()
            ->where(function ($q) {
                $q->has('orlTests')
                    ->orHas('pediatreTests')
                    ->orHas('visionTests')
                    ->orHas('dentaireTests');
            })
            ->with([
                'latestOrlTest.creator',
                'latestPediatreTest.creator',
                'latestVisionTest.creator',
                'latestDentaireTest.creator',
                'group.class.unit.site.commune.province.region',
                'group.class.level',
            ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:AS' . $sheet->getHighestRow())->applyFromArray(self::DEFAULT_STYLE);

        $sheet->getStyle('A1:AS1')->applyFromArray(array_merge(self::DEFAULT_STYLE, [
            'font' => [
                'bold' => true,
                'size' => 13,
                'color' => ['argb' => 'FF000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFE0E0E0'],
            ],
        ]));

        $sheet->getStyle('A2:AS2')->applyFromArray(array_merge(self::DEFAULT_STYLE, [
            'font' => [
                'bold' => false,
            ],
        ]));

        $orangeColumns = [
            'O',
            'P',
            'W',
            'X',
            'AE',
            'AF',
            'AM',
            'AN',
        ];

        foreach ($orangeColumns as $col) {
            $sheet->getStyle("{$col}2")
                  ->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFffe6cc'));

            $sheet->getStyle("{$col}2")->getFont()->setBold(true);
        }

        $sheet->getStyle('A1:AS' . $sheet->getHighestRow())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        $thick_right_borders = [
            'M', 'U', 'AC', 'AK'
        ];

        foreach ($thick_right_borders as $col) {
            $sheet->getStyle("{$col}1:{$col}" . $sheet->getHighestRow())->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF000000'));
        }

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(25);
    }

    public function headings(): array
    {
        return
        [
            [
                'Informations Générales', '', '', '', '', '', '', '', '','', '', '', '',
                'ORL', '', '', '', '', '', '', '',
                'Pédiatrie', '', '', '', '', '', '', '',
                'Ophtalmologie', '', '', '', '', '', '', '',
                'Dentaire', '', '', '', '', '', '', '',
            ],
            [
                'Nom Région',
                'Nom Province',
                'Nom Commune',
                'ID Douar',
                'Nom Douar',
                'Nom UP',
                'Nom Section',

                'ID Enfant',
                'ID Inscription',
                'Nom Enfant',
                'Prénom Enfant',
                'Sexe',
                'Date de Naissance',

                'ID Consultation ORL', 'Est-ce que l’enfant se plaint régulièrement d avoir mal aux oreilles ?', 'Est-ce que vous pensez que l’enfant n’entend pas suffisament bien ?',
                'Referer centre ORL', 'Observations ORL', 'Date Consultation ORL', 'Date de création ORL', 'Créé Par ORL',

                'ID Consultation Pédiatre', 'Taille (cm)', 'Poids (kg)', 'Referer centre Pédiatre',
                'Observations Pédiatre', 'Date Consultation Pédiatre', 'Date de création Pédiatre', 'Créé Par Pédiatre',

                'ID Consultation Vue', 'Acuité Oeil Droit', 'Acuité Oeil Gauche', 'Referer centre Vue',
                'Observations Vue', 'Date Consultation Vue', 'Date de création Vue', 'Créé Par Ophtalmologue',

                'ID Consultation Dentaire', 'Est-ce que l’enfant a la dent de 6 ans ?', 'Est-ce que la dent 6 ans est cariée ?', 'Referer centre Dentaire',
                'Observations Dentaire', 'Date Consultation Dentaire', 'Date de création Dentaire', 'Créé Par Dentiste',
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:M1');
                $sheet->mergeCells('N1:U1');
                $sheet->mergeCells('V1:AC1');
                $sheet->mergeCells('AD1:AK1');
                $sheet->mergeCells('AL1:AS1');

                // Parcours sûr de toutes les colonnes existantes
                foreach ($sheet->getColumnIterator() as $column) {
                    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                }

                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(25);
            },
        ];
    }

    protected function formatYesNo($value): string
    {
        return is_null($value) ? '' : ($value ? 'Oui' : 'Non');
    }

    protected function formatValue($value)
    {
        return $value ?? '';
    }

    public function map($beneficiary): array
    {
        //
        $orlTest = $beneficiary->latestOrlTest;
        $pediatreTest = $beneficiary->latestPediatreTest;
        $visionTest = $beneficiary->latestVisionTest;
        $dentaireTest = $beneficiary->latestDentaireTest;

        $group = $beneficiary->group;
        $class = $group?->class ?? $group?->classe;
        $unit = $class?->unit ?? $class?->unite;
        $level = $class?->level;
        $site = $unit?->site;
        $commune = $site?->commune;
        $province = $commune?->province;
        $region = $province?->region;
        $douar = $site?->douar;

        $public_id = $beneficiary->id;
        $publicId = $beneficiary->beneficiary_id ?? $beneficiary->beneficiaire_id ?? $beneficiary->id;

        return [
            $this->formatValue($region?->name ?? $region?->nom ?? null),
            $this->formatValue($province?->name ?? $province?->nom ?? null),
            $this->formatValue($commune?->name ?? $commune?->nom ?? null),
            $this->formatValue($douar?->id ?? null),
            $this->formatValue($douar?->name ?? $douar?->nom ?? null),
            $this->formatValue($unit?->name ?? $unit?->nom ?? null),
            $this->formatValue($level?->title ?? $level?->titre ?? null),

            $publicId,
            $public_id,
            $this->formatValue($beneficiary->last_name ?? $beneficiary->nom ?? null),
            $this->formatValue($beneficiary->first_name ?? $beneficiary->prenom ?? null),
            $this->formatValue($beneficiary->gender ?? $beneficiary->sexe ?? null),
            $this->formatValue($beneficiary->date_of_birth ?? $beneficiary->date_naissance ?? null),

            $this->formatValue($orlTest?->id),
            $this->formatYesNo($orlTest?->ear_pain_regularly ?? $orlTest?->plainte_oreilles ?? null),
            $this->formatYesNo($orlTest?->hearing_problem ?? $orlTest?->pense_pas_entendre ?? null),
            $this->formatYesNo($orlTest?->refer_to_center ?? null),
            $this->formatValue($orlTest?->observations),
            $this->formatValue($orlTest?->consultation_date),
            $this->formatValue($orlTest?->created_at),
            $this->formatValue($orlTest?->creator?->name),

            $this->formatValue($pediatreTest?->id),
            $this->formatValue($pediatreTest?->height ?? $pediatreTest?->taille_cm ?? null),
            $this->formatValue($pediatreTest?->weight ?? $pediatreTest?->poids_kg ?? null),
            $this->formatYesNo($pediatreTest?->refer_to_center ?? $pediatreTest?->a_referer ?? null),
            $this->formatValue($pediatreTest?->observations),
            $this->formatValue($pediatreTest?->consultation_date ?? $pediatreTest?->date_consultation ?? null),
            $this->formatValue($pediatreTest?->created_at),
            $this->formatValue($pediatreTest?->creator?->name),

            $this->formatValue($visionTest?->id),
            $this->formatValue($visionTest?->right_eye ?? $visionTest?->oeil_droit ?? null),
            $this->formatValue($visionTest?->left_eye ?? $visionTest?->oeil_gauche ?? null),
            $this->formatYesNo($visionTest?->refer_to_center ?? null),
            $this->formatValue($visionTest?->observations),
            $this->formatValue($visionTest?->consultation_date),
            $this->formatValue($visionTest?->created_at),
            $this->formatValue($visionTest?->creator?->name),

            $this->formatValue($dentaireTest?->id),
            $this->formatYesNo($dentaireTest?->has_six_year_molar ?? $dentaireTest?->dent_6_ans ?? null),
            $this->formatYesNo($dentaireTest?->six_year_molar_cariee ?? $dentaireTest?->dent_6_ans_cariee ?? null),
            $this->formatYesNo($dentaireTest?->refer_to_center ?? null),
            $this->formatValue($dentaireTest?->observations),
            $this->formatValue($dentaireTest?->consultation_date),
            $this->formatValue($dentaireTest?->created_at),
            $this->formatValue($dentaireTest?->creator?->name),
        ];
    }
}
