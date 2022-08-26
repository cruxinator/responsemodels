<?php

namespace Cruxinator\ResponseModel\Commands;

use Illuminate\Console\Command;

class ResponseModelCommand extends Command
{
    public $signature = 'responsemodels';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
