<?php

namespace Cruxinator\ResponseModel;

use Cruxinator\Package\Package;
use Cruxinator\Package\PackageServiceProvider;
use Cruxinator\ResponseModel\Commands\ResponseModelCommand;

class ResponseModelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('responsemodels')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_responsemodels_table')
            ->hasCommand(ResponseModelCommand::class);
    }
}
