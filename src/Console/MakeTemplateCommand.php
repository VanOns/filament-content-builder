<?php

namespace VanOns\FilamentContentBuilder\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeTemplateCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:template';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new template class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Template';

    /**
     * Execute the console command.
     */
    public function handle(): bool
    {
        if (parent::handle() === false) {
            return false;
        }

        return true;
    }


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/template.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     *
     * @return string
     */
    protected function resolveStubPath($stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . '/../..' . $stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return is_dir(app_path('View\Templates')) ? $rootNamespace . '\\View\\Templates' : $rootNamespace;
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => [
                'What should the '.strtolower($this->type).' be named?',
                'E.g. Text',
            ],
        ];
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'].'/View/Templates/'.str_replace('\\', '/', $name).'.php';
    }
}
