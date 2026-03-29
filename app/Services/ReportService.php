<?php

namespace App\Services;
use App\Models\Report;
use App\Repositories\ReportRepository;
use App\Traits\UploadFileTrait;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\UploadedFile ;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile as FileUploadedFile;
use Illuminate\Support\Facades\Storage;
use PgSql\Lob;
use Termwind\Components\Hr;

class ReportService
{
    use UploadFileTrait;
    protected ReportRepository $reportRepository;
    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    public function getFilteredReports(array $filters){
        return $this->reportRepository->getFilteredReports($filters);
    }
    public function create(array $data): Report
    {

        if (isset($data['attachment_path']) && $data['attachment_path'] instanceof FileUploadedFile) {
            $filePath = $this->uploadPublicFile($data['attachment_path'], 'attachment_path');
            $data['attachment_path'] = $filePath;
        } else {
            $data['attachment_path'] = null; 
        }
        $data['creator_id'] = auth()->id(); 
       return $this->reportRepository->create($data);
    }
   
public function update(int $reportId, array $data): Report
{
    $report = $this->reportRepository->show($reportId);
    FacadesLog::info($data);
    // Handle document removal
    if (isset($data['remove_existing_file']) && $data['remove_existing_file'] === 'true') {
        if ($report->attachment_path) {
            Storage::disk('public')->delete($report->attachment_path);
        }
        $data['attachment_path'] = null;
    }


        if (isset($data['attachment_path']) && $data['attachment_path'] instanceof UploadedFile) {
            if ($report->attachment_path) {
                $this->deletePublicFile($report->attachment_path);
            }
            $filePath = $this->uploadPublicFile($data['attachment_path'], 'attachment_path');
            $data['attachment_path'] = $filePath;
        } elseif (array_key_exists('attachment_path', $data) && is_null($data['attachment_path'])) {
            if ($report->attachment_path) {
                $this->deletePublicFile($report->attachment_path);
            }
        } else {
            unset($data['attachment_path']);
        }

    return $this->reportRepository->update($reportId, $data);
}    public function delete( $reportId): ?bool
    {
        return $this->reportRepository->delete($reportId);
    }
    public function bulkDelete(array $ids): void
    {
        $this->reportRepository->bulkDelete($ids);
    }

    public function restore(int $reportId): Report
    {
        return $this->reportRepository->restore($reportId);
    }
    public function show(int $reportId ): Report
    {
        return $this->reportRepository->show($reportId);
    }

}