<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;


trait UploadFileTrait
{

    /**
     * @param UploadedFile|null $file
     * @param string $directory
     * @return string|null
     */
    public function uploadPublicFile(?UploadedFile $file, string $directory): ?string
    {
        if (!$file) {
            return null;
        }

        try {
            $originalName = $file->getClientOriginalName();
            $uniqueName = Str::random(10) . '_' . $originalName;

            return Storage::disk('public')->putFileAs(
                $directory,
                $file,
                $uniqueName
            );
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l’upload : ' . $e->getMessage());
        }
    }
   /**
     * Deletes a file from the 'public' disk.
     *
     * @param string|null $filePath 
     * @return bool 
     */
    public function deletePublicFile(?string $filePath): bool
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            try {
                return Storage::disk('public')->delete($filePath);
            } catch (Exception $e) { 
                Log::error("Failed to delete public file: " . $e->getMessage(), [
                    'filePath' => $filePath,
                    'exception' => $e
                ]);
                return false;
            }
        }
        return false;
    }
}
