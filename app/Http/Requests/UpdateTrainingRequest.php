<?php


namespace App\Http\Requests;

use App\Enums\TrainingModuleFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gate it if you need
    }


    public function rules(): array
    {
        return [
            // ── Training (top level) — all optional on update
            'title'            => ['sometimes', 'string', 'max:255'],
            'training_type'    => ['sometimes', 'string', Rule::in(['initial','continuous','monthly'])],
            'responsible_id'   => ['sometimes', 'integer', 'exists:collaborators,id'],
            'start_date'       => ['sometimes', 'date'],
            'end_date'         => ['sometimes', 'date', 'after_or_equal:start_date'],
            'status'           => ['sometimes', 'string', Rule::in(['planned','in_progress','completed','cancelled'])],
            'target_audience'  => ['sometimes', 'string', Rule::in(['candidates','collaborators','externals'])],
            'cabinet_id'       => ['sometimes', 'nullable', 'integer', 'exists:cabinets,id'],
            'notes'            => ['sometimes', 'nullable', 'string'],


            // Attachments (add more files or remove some by path)
            'attachments'          => ['sometimes', 'array'],
            'attachments.*'        => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'removed_attachments'  => ['sometimes', 'array'],
            'removed_attachments.*'=> ['string'],


            // ── Modules upsert
            'modules'                                 => ['sometimes', 'array'],


            // delete/update/create flags
            'modules.*.id'                            => ['sometimes', 'integer', 'exists:modules,id'],
            'modules.*.deleted'                       => ['sometimes', 'boolean'],


            'modules.*.module_id'                     => ['sometimes', 'nullable', 'string', 'max:100'],
            'modules.*.title'                         => ['sometimes', 'string', 'max:255'],
            'modules.*.pedagogical_objectives'        => ['sometimes', 'nullable', 'string'],
            'modules.*.trainer_id'                    => ['sometimes', 'nullable', 'integer', 'exists:trainers,id'],
            'modules.*.competency_grid_id'            => ['sometimes', 'nullable', 'integer', 'exists:competency_grids,id'],
            'modules.*.total_duration'                => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'modules.*.formation_type'                => ['sometimes', 'nullable', Rule::in(TrainingModuleFormat::values())],
            'modules.*.evaluation_planned'            => ['sometimes', 'nullable', 'boolean'],
            'modules.*.status'                        => ['sometimes', 'nullable', 'string', Rule::in(['planned','in_progress','completed','cancelled'])],


            // keep existing + add new files + remove some by path
            'modules.*.pedagogical_supports'          => ['sometimes', 'array'],
            'modules.*.pedagogical_supports.*'        => ['nullable'],
            'modules.*.removed_supports'              => ['sometimes', 'array'],
            'modules.*.removed_supports.*'            => ['string'],


            // Sessions upsert per module
            'modules.*.sessions'                      => ['sometimes', 'array'],


            'modules.*.sessions.*.id'                 => ['sometimes', 'integer', 'exists:training_sessions,id'],
            'modules.*.sessions.*.deleted'            => ['sometimes', 'boolean'],


            'modules.*.sessions.*.session_identifier' => ['sometimes', 'nullable', 'string', 'max:100'],
            'modules.*.sessions.*.training_group_id'  => ['sometimes', 'nullable', 'integer', 'exists:training_groups,id'],
            'modules.*.sessions.*.animator_type'        => ['required','string','max:255'],
            'modules.*.sessions.*.animator_id'        => ['sometimes', 'nullable', 'integer'],
            'modules.*.sessions.*.session_date'       => ['sometimes', 'date'],
            'modules.*.sessions.*.start_time'         => ['sometimes', 'nullable'],
            'modules.*.sessions.*.end_time'           => ['sometimes', 'nullable'],
            'modules.*.sessions.*.planned_duration_hours' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'modules.*.sessions.*.site_id'            => ['sometimes', 'nullable', 'integer', 'exists:sites,id'],
            'modules.*.sessions.*.session_type'         => ['nullable','in:Theorique,Pratique,Evaluation'],
            'modules.*.sessions.*.presence_registered'=> ['sometimes', 'nullable', 'boolean'],
            'modules.*.sessions.*.observations'       => ['sometimes', 'nullable', 'string'],
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


    public function withValidator($validator): void
    {
        // Cross-field constraint for times (if both supplied)
        $validator->after(function ($v) {
            $data = $this->all();
            if (!empty($data['modules']) && is_array($data['modules'])) {
                foreach ($data['modules'] as $i => $m) {
                    if (!empty($m['sessions']) && is_array($m['sessions'])) {
                        foreach ($m['sessions'] as $j => $s) {
                            if (!empty($s['start_time']) && !empty($s['end_time'])) {
                                if (strtotime($s['end_time']) < strtotime($s['start_time'])) {
                                    $v->errors()->add("modules.$i.sessions.$j.end_time", 'L’heure de fin doit être postérieure ou égale à l’heure de début.');
                                }
                            }
                        }
                    }
                }
            }
        });
    }


    public function prepareForValidation(): void
    {
        $data = $this->all();


        // Arrays
        foreach (['attachments','removed_attachments','modules'] as $key) {
            if (!isset($data[$key])) $data[$key] = [];
        }


        // Normalize boolean flags in modules/sessions
        if (!empty($data['modules']) && is_array($data['modules'])) {
            foreach ($data['modules'] as $i => $m) {
                foreach (['deleted','evaluation_planned'] as $flag) {
                    if (isset($m[$flag])) {
                        $data['modules'][$i][$flag] =
                            filter_var($m[$flag], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    }
                }


                if (!isset($m['removed_supports'])) $data['modules'][$i]['removed_supports'] = [];
                if (!isset($m['pedagogical_supports'])) $data['modules'][$i]['pedagogical_supports'] = [];


                if (!isset($m['sessions']) || !is_array($m['sessions'])) {
                    $data['modules'][$i]['sessions'] = [];
                } else {
                    foreach ($m['sessions'] as $j => $s) {
                        if (isset($s['deleted'])) {
                            $data['modules'][$i]['sessions'][$j]['deleted'] =
                                filter_var($s['deleted'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        }
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

