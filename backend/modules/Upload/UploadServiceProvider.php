<?php

namespace Modules\Upload;

use App\Providers\ModuleServiceProvider;

class UploadServiceProvider extends ModuleServiceProvider
{
    function getNamespace()
    {
        return 'Upload\Controllers';
    }

    function getDir()
    {
        return __DIR__;
    }
}
