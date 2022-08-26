<?php

namespace Cruxinator\ResponseModel;

use Cruxinator\LaravelPackage\Package;
use Cruxinator\LaravelPackage\PackageServiceProvider;
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
