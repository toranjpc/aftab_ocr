<?php

namespace Modules\Upload\Controllers;

use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class UploadFileController extends Controller
{
    public function store(Request $request)
    {
        $file = $request->file('file');

        $type = $request->type;

        if (!isset($file)) {
            return response(
                ['message' => 'فایل ارسال نشده مجدد تلاش کنید'],
                Response::HTTP_BAD_REQUEST
            );
        }


        $fileName = time() . '.' . $file->extension();

        $filePath = 'uploaded/' . now()->year . '/' . now()->month . '/' . now()->day . '/' . $type;

        $saveFiles = $filePath . '/' . $fileName;

        $file->move(
            public_path($filePath . '/'),
            $fileName
        );

        return response(
            ['message' => 'ذخیره شد', 'link' => $saveFiles],
            Response::HTTP_OK
        );
    }

    private function resize($image, $filePath)
    {
        function compress($source, $destination, $quality)
        {
            $info = getimagesize($source);
            if ($info['mime'] == 'image/jpeg')
                $image = imagecreatefromjpeg($source);
            elseif ($info['mime'] == 'image/gif')
                $image = imagecreatefromgif($source);
            elseif ($info['mime'] == 'image/png')
                $image = imagecreatefrompng($source);
            imagejpeg($image, $destination, $quality);
            return $destination;
        }

        $img = Image::make($image->path())->encode('jpg');
        $img->resize(500, null, function ($const) {
            $const->aspectRatio();
        })->save($filePath);
        compress($filePath, $filePath, 50);
        return $img;
    }
}
