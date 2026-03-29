<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * trait DownloadFileTrait
 */
trait DownloadFileTrait
{
    /**
     * @param $filename
     * @param $directory
     * @return BinaryFileResponse
     */
    public function downloadFile ($filename, $directory=null): BinaryFileResponse
    {
        if($directory == null){
            $path = storage_path('app/public/' . $filename);
        }else{
            $path = storage_path('app/public/'.$directory.'/' . $filename);
        }

        if (!file_exists($path)) {
            abort(404,'Le document n\'existe pas');
        }

        return response()->download($path);
    }
}
