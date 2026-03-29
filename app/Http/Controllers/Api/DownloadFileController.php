<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\DownloadFileTrait;
use Illuminate\Http\Request;

class DownloadFileController extends Controller
{
    use DownloadFileTrait;
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request,$filename,$directory)
    {
        return $this->downloadFile($filename, $directory);
    }
}
