<?php

namespace App\Services;

use App\Models\Collaborator;
use App\Models\ContractTypes;
use App\Models\Position;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\File;

class ContractService
{
    /***
     * @param Collaborator $collaborator
     * @return string
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     *
     */
    public function generateContract(Collaborator $collaborator): string
    {
        $position = Position::findOrFail($collaborator->position_id);
        $contract = ContractTypes::findOrFail($collaborator->contract_type_id);

        $positionInfoIsNull = empty($position->description)
            && empty($position->main_mission)
            && empty($position->key_activities)
            && empty($position->required_skills)
            && empty($position->link_with_function);

        $templatePath = match ($contract->type) {
            'CDI' => public_path('templates/CDI.docx'),
            'CDD' => $positionInfoIsNull
                ? public_path('templates/CDD_NUL_INFO_POSITION.docx')
                : public_path('templates/CDD.docx'),
            'CTD' => public_path('templates/CTD.docx'),
            default => throw new \Exception("Unsupported contract type: {$contract->type}"),
        };

        $template = new TemplateProcessor($templatePath);

        $template->setValue('title', $collaborator->title);
        $template->setValue('last_name', $collaborator->last_name);
        $template->setValue('first_name', $collaborator->first_name);
        $template->setValue('birth_date', $collaborator->birth_date ?? '');
        $template->setValue('cin', $collaborator->cin);
        $template->setValue('residence_address', $collaborator->residence_address);
        $template->setValue('gross_salary', $collaborator->gross_salary ?? '0');
        $template->setValue('transportation', $collaborator->transportation ?? '0');
        $template->setValue('entry_date', $collaborator->entry_date ?? '');
        $template->setValue('position', $position->title);
        $template->setValue('cart', $collaborator->cart ?? '0');
        $template->setValue('trial_period', $collaborator->trial_period ?? '0');
        $template->setValue('notice_period', $collaborator->notice_period ?? '0');
        $template->setValue('exit_date', $collaborator->exit_date ?? '');
        $template->setValue('description', $position->description ?? '');
        $template->setValue('main_mission', $position->main_mission ?? '');
        $template->setValue('key_activities', $position->key_activities ?? '');
        $template->setValue('required_skills', $position->required_skills ?? '');
        $template->setValue('link_with_function', $position->link_with_function ?? '');

        $generatedPath = storage_path('app/generated');
        if (!File::exists($generatedPath)) {
            File::makeDirectory($generatedPath, 0755, true);
        }

        $fileName = 'Contract_' . $collaborator->last_name . '.docx';
        $path = $generatedPath . '/' . $fileName;

        $template->saveAs($path);

        return $path;
    }
}
