<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VaccinTest;
use Illuminate\Http\Request;

class VaccinTestController extends Controller
{
    public function show($beneficiaryId)
    {
        $vaccinTest = VaccinTest::where('beneficiaire_id', $beneficiaryId)->first();
        return response()->json($vaccinTest);
    }

    public function upsert(Request $request, $beneficiaryId)
    {
        $validated = $request->validate([
            'is_up_to_date' => 'nullable|boolean',
            'missed_vaccine' => 'nullable|boolean',
            'refer_to_center'    => 'required|boolean',
            'observations' => 'nullable|string',
        ],[
            'is_up_to_date.required' => 'Veuillez préciser si l enfant se plaint régulièrement d avoir mal aux oreilles',
            'missed_vaccine.required' => 'Veuillez préciser si vous pensez que l enfant n entend pas suffisamment bien',
            'refer_to_center.required' => 'Veuillez préciser si l’enfant doit être référé.',
            'observations.required' => 'Veuillez saisir une observation.',
        ]);

        $vaccinTest = VaccinTest::updateOrCreate(
            ['beneficiaire_id' => $beneficiaryId],
            $validated + [
                'beneficiaire_id' => $beneficiaryId,
                'consultation_date' => now(),
                'created_by' => auth()->id(),
            ],
        );

        return response()->json($vaccinTest, 200);
    }
}
