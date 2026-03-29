<?php

use Barryvdh\DomPDF\Facade\Pdf;

$data = [
    'company_name' => 'FONDATION ZAKOURA',
    'company_rib' => '310780100312270488230147',
    'account_title' => 'Compte Principal',
    'emission_date' => '12/01/2026',
    'virements' => [
        [
            'beneficiary_name' => 'ABDELAZIZ DAROUICHI',
            'beneficiary_rib' => '007780000696530040002229',
            'invoice_number' => 'F-2026-001',
            'amount' => 3000.00
        ],
        [
            'beneficiary_name' => 'HICHAM BOUDAR',
            'beneficiary_rib' => '181825211114626037002183',
            'invoice_number' => 'F-2026-002',
            'amount' => 3000.00
        ]
    ],
    'total_count' => 2,
    'total_amount' => 6000.00
];

$pdf = Pdf::loadView('pdf.ordre-virement-tgr', $data);
$pdf->setPaper('A4', 'portrait');

$output = $pdf->output();
file_put_contents(storage_path('app/test-tgr.pdf'), $output);

echo "PDF created: " . filesize(storage_path('app/test-tgr.pdf')) . " bytes\n";
echo "Path: " . storage_path('app/test-tgr.pdf') . "\n";
