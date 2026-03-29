<?php


namespace App\Http\Requests;

use App\Enums\TrainingModuleFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gate it if you need
    }


    public function rules(): array
{
    return [
        // TRAINING FIELDS
        'title'            => ['required','string','max:255'],
        'training_type'    => ['required','in:initial,continuous,monthly'],
        'responsible_id'   => ['required','integer','exists:collaborators,id'],
        'start_date'       => ['required','date'],
        'end_date'         => ['required','date','after_or_equal:start_date'],
        'status'           => ['nullable','in:planned,in_progress,completed,cancelled'],
        'target_audience'  => ['required','in:candidates,collaborators,externals'],
        'cabinet_id'       => ['nullable','integer','exists:cabinets,id'],
        'notes'            => ['nullable','string'],
        'attachments'      => ['nullable','array'],
        'attachments.*'    => ['file','mimes:pdf,doc,docx,jpg,jpeg,png','max:5120'],


        // MODULES (optional, multiple)
        'modules'                         => ['nullable','array'],
        'modules.*.id'                    => ['nullable','integer','exists:modules,id'],
        // DO NOT accept modules.*.module_id (identifier is auto-generated)
        'modules.*.title'                 => ['required','string','max:255'],
        'modules.*.pedagogical_objectives'=> ['nullable','string'],
        'modules.*.trainer_id'            => ['nullable','integer','exists:trainers,id'],
        'modules.*.competency_grid_id'    => ['nullable','integer','exists:competency_grids,id'],
        'modules.*.total_duration'        => ['required','numeric','min:0'],
        'modules.*.formation_type'        => ['required', Rule::in(TrainingModuleFormat::values())],
        'modules.*.evaluation_planned'    => ['required','boolean'],
        'modules.*.status'                => ['nullable','in:planned,in_progress,completed,cancelled'],
        'modules.*.removed_supports'      => ['nullable','array'],
        'modules.*.removed_supports.*'    => ['string'],
        'modules.*.pedagogical_supports'  => ['nullable','array'],
        'modules.*.pedagogical_supports.*'=> ['file','mimes:pdf,doc,docx,jpg,jpeg,png','max:5120'],


        // SESSIONS under each module (optional, multiple)
        'modules.*.sessions'                        => ['nullable','array'],
        'modules.*.sessions.*.id'                   => ['nullable','integer','exists:training_sessions,id'],
        // DO NOT accept sessions.*.session_identifier (auto)
        // DO NOT require sessions.*.training_group_id (service auto-fills)
        'modules.*.sessions.*.animator_type'        => ['required','string','max:255'],
        'modules.*.sessions.*.animator_id'          => ['required','integer'],
        'modules.*.sessions.*.session_date'         => ['required','date'],
        'modules.*.sessions.*.start_time'           => ['nullable','date_format:H:i'],
        'modules.*.sessions.*.end_time'             => ['nullable','date_format:H:i'],
        'modules.*.sessions.*.planned_duration_hours'=> ['required','integer','min:1'],
        'modules.*.sessions.*.site_id'              => ['required','integer','exists:sites,id'],
        'modules.*.sessions.*.session_type'         => ['nullable','in:Theorique,Pratique,Evaluation'],
        'modules.*.sessions.*.presence_registered'  => ['nullable','boolean'],
        'modules.*.sessions.*.observations'         => ['nullable','string'],
        'modules.*.sessions.*.status'               => ['nullable','in:Planifiée,Réalisée,Reportée,Annulée'],
    ];
}


    public function attributes(): array
    {
        return [
            'modules.*.title' => 'titre du module',
            'modules.*.sessions.*.session_date' => 'date de séance',
        ];
    }


    public function prepareForValidation(): void
    {
        // Ensure arrays exist (so array rules don’t fail on null)
        $data = $this->all();


        foreach (['attachments','modules'] as $key) {
            if (!isset($data[$key])) $data[$key] = [];
        }


        // Normalize booleans inside modules/sessions
        if (!empty($data['modules']) && is_array($data['modules'])) {
            foreach ($data['modules'] as $i => $m) {
                if (isset($m['evaluation_planned'])) {
                    $data['modules'][$i]['evaluation_planned'] = filter_var($m['evaluation_planned'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                }
                if (!isset($m['sessions']) || !is_array($m['sessions'])) {
                    $data['modules'][$i]['sessions'] = [];
                } else {
                    foreach ($m['sessions'] as $j => $s) {
                        if (isset($s['presence_registered'])) {
                            $data['modules'][$i]['sessions'][$j]['presence_registered'] =
                                filter_var($s['presence_registered'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        }
                    }
                }
            }
        }


        $this->replace($data);
    }
}

