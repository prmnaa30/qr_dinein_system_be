<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageUploadService
{
    /**
     * Upload, Resize and Convert Image to WebP
     */
    public function uploadImage(UploadedFile $file, string $directory = 'products')
    {
        $filename = uniqid() . '.webp';

        $image = Image::read($file)
            ->scaleDown(800)
            ->toWebp(80);

        $path = (string) $directory . '/' . $filename;
        Storage::disk('public')->put($path, (string) $image);

        return $path;
    }

    /**
     * Delete Old Image
     */
    public function deleteImage(?string $path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
