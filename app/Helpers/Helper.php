<?php

namespace App\Helpers;

use App\Models\Employee;
use Carbon\Carbon;
use Exception;
use Faker\Provider\Uuid;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;

class Helper
{

    public static function uploadImage(string $filePath, UploadedFile $file)
    {

        $image = $file;
        $imageName = Uuid::uuid() . '.' . $image->extension();

        $imagePath = public_path('storage/images/' . $filePath . '/');
        if (!file_exists($imagePath)) {
            mkdir($imagePath, 0755, true);
        }

        $image = Image::read($file);

        $image->scale(250)->save($imagePath . '/' . $imageName);

        return 'storage/images/' . $filePath . '/' . $imageName;
    }

    public static function deleteFile(string $filePath)
    {
        try {
            if (str_contains($filePath, env('APP_URL'))) {
                $filePath = str_replace(env('APP_URL') . '/', '', $filePath);
            }
            if (File::exists(public_path($filePath))) {
                File::delete(public_path($filePath));
            } else {
                return 'No such file in directory !';
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
