<?php

namespace Modules\Ocr\Console;

use Illuminate\Console\Command;
use Modules\Ocr\TruckBuffer;

class PlateMatcher extends Command
{
    protected $signature = 'plate-container:match';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        TruckBuffer::runBuffer();
    }
}
