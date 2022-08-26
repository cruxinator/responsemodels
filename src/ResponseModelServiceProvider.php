<?php

namespace Cruxinator\ResponseModel;

use Cruxinator\Package\Package;
use Cruxinator\Package\PackageServiceProvider;
use Cruxinator\ResponseModel\Console\ResponseModelMakeCommand;

class ResponseModelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('responsemodels')
            ->hasViews()
            ->hasCommand(ResponseModelMakeCommand::class);
    }
}
