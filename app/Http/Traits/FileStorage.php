<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;

trait FileStorage
{
    private function uploadFile($request, $folder, $record = null, $uploadedFileName = 'file', $recordFileName = 'file')
    {
        if (!$record) {
            if (!$request->hasFile($uploadedFileName)) {
                return null;
            }
            return Storage::putFile($folder, $request->file($uploadedFileName));
        }

        $path = $record->$recordFileName;
        if (!$request->hasFile($uploadedFileName)) {
            return (!$path || strpos($path, 'https://ui-avatars.com') !== false) ? null : explode('uploads/', $path, 2)[1];
        }

        if ($path) {
            $this->deleteFile($path);
        }
        return Storage::putFile($folder, $request->file($uploadedFileName));
    }

    /*-----------------------------------------------------------------------------------------------*/

    private function uploadMultipleFiles($request, $folder, $uploadedFilesName = 'files')
    {
        $paths = [];
        if ($request->$uploadedFilesName) {
            foreach ($request->file($uploadedFilesName) as $file) {
                $path = Storage::putFile($folder, $file);
                array_push($paths, $path);
            }
        }

        return $paths;
    }

    /*-----------------------------------------------------------------------------------------------*/

    private function deleteFile($path)
    {
        if ($path && strpos($path, 'https://ui-avatars.com') === false && strpos($path, '/defaults/') === false) {
            $file = explode('uploads/', $path, 2)[1];
            Storage::delete($file);
        }
    }
}
