<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ImageCompressor
{
    public function store(UploadedFile $file, string $directory): string
    {
        $source = $this->open($file->getRealPath() ?: '');
        if ($source === null) {
            return $file->store($directory, 'public');
        }

        [$image, $width, $height] = $source;
        $max = 1600;
        $scale = min(1, $max / max($width, 1));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);

        $relative = trim($directory, '/').'/'.Str::uuid()->toString().'.jpg';
        $absolute = Storage::disk('public')->path($relative);
        if (! is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0755, true);
        }
        imagejpeg($canvas, $absolute, 75);
        imagedestroy($canvas);

        return $relative;
    }

    /**
     * @return array{0: \GdImage, 1: int, 2: int}|null
     */
    private function open(string $path): ?array
    {
        if ($path === '' || ! function_exists('imagecreatetruecolor') || ! is_file($path)) {
            return null;
        }

        $info = @getimagesize($path);
        if (! is_array($info)) {
            return null;
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if ($image === false) {
            return null;
        }

        return [$image, (int) $info[0], (int) $info[1]];
    }
}
