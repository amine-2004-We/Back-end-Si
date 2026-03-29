<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\ConsultationsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ConsultationExportController extends Controller
{
    public function export()
    {
        return Excel::download(new ConsultationsExport, 'consultations.xlsx');
    }
}
