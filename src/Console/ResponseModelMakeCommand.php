<?php

namespace Cruxinator\ResponseModel\Console;

use Illuminate\Console\GeneratorCommand;
use Cruxinator\Package\Strings\MyStr;
use Symfony\Component\Console\Input\InputOption;

class ResponseModelMakeCommand extends GeneratorCommand
{
    protected $name = 'make:response-model';

    protected $description = 'Create a new ResponseModel class';

    protected $type = 'ResponseModel';

    public function handle(): ?bool
    {
        if (parent::handle() === false) {
            if (! $this->option('force')) {
                return null;
            }
        }
    }

    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/DummyViewModel.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        if ($this->isCustomNamespace()) {
            return $rootNamespace;
        }

        return $rootNamespace.'\ViewModels';
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the response-model already exists'],
        ];
    }

    protected function isCustomNamespace(): bool
    {
        return MyStr::contains($this->argument('name'), '/');
    }
}
