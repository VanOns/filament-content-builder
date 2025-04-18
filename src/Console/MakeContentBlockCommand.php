<?php

namespace VanOns\FilamentContentBuilder\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use VanOns\FilamentContentBuilder\Facade\FilamentContentBuilder;
use VanOns\FilamentContentBuilder\Helpers\FileHelper;

class MakeContentBlockCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:content-block
                            {name : The name of the content block}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new content block with all required files';

    /**
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => 'What is the name of the content block?',
        ];
    }

    /**
     * Execute the console command.
     *
     * @throws \Throwable
     */
    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $blockName = $name . 'Block';

        if (FilamentContentBuilder::blockExists($blockName)) {
            $this->fail('The block already exists.');
        }

        $this->line('Creating block...');

        $filePath = FileHelper::makeBlock(
            name: $blockName,
            title: $name,
        );

        if (!File::exists(config_path('filament-content-builder.php'))) {
            $this->publishConfigFile();
        }

        $this->updateConfigFile($blockName);

        $this->info("Block created successfully at $filePath.");

        return self::SUCCESS;
    }

    protected function publishConfigFile(): void
    {
        $this->line('Publishing config file...');

        $this->callSilent('vendor:publish', [
            '--tag' => 'filament-content-builder-config',
            '--force' => true,
        ]);

        $this->info('Config file published successfully.');
    }

    protected function updateConfigFile(string $blockName, string $namespace = 'App\\Blocks\\'): void
    {
        $this->line('Adding block to config file...');

        $blocks = config('filament-content-builder.blocks', []);
        $blocks[] = $namespace . $blockName;
        $blocks = collect($blocks)->map(fn ($block) => "\\$block::class")->toArray();

        $configPath = config_path('filament-content-builder.php');
        $configContent = File::get($configPath);
        $configContent = preg_replace(
            '/\'blocks\' => \[(.*?)\],/s',
            "'blocks' => [\n\t\t" . implode(",\n\t\t", $blocks) . ",\n\t],",
            $configContent
        );
        File::put($configPath, $configContent);

        $this->info('Block added to config file successfully.');
    }
}
