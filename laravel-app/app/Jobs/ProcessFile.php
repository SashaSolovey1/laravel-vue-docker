<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ProcessFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;
    protected $fileName;

    public function __construct($file, $fileName)
    {
        $this->file = $file;
        $this->fileName = $fileName;
    }

    public function handle()
    {
        $file = $this->file;

        // Check if the file is an image
        if (substr($file->getMimeType(), 0, 5) == 'image') {
            $image = Image::make($file);

            // Resize the image to a maximum of 320x240
            $image->resize(320, 240, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Save the image to storage
            $filePath = 'comments/' . $this->fileName;
            $image->save(storage_path('app/public/' . $filePath));
        } else {
            // Save the file without processing
            $filePath = 'comments/' . $this->fileName;
            Storage::putFileAs('public/comments', $file, $this->fileName);
        }
    }
}
