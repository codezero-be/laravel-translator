<?php

namespace CodeZero\Translator\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class FormatLangFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:format {path? : The path to the language files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Format the PHP syntax in language files.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = $this->argument('path') ?: resource_path('lang');
        $fixer = __DIR__.'/../../vendor/bin/php-cs-fixer';
        $rules = '{"@Symfony": true, "@PSR2": true, "array_syntax": {"syntax": "short"}}';
        $command = "php {$fixer} fix {$path} --rules='{$rules}' --using-cache='no'";

        $process = new Process($command);
        $process->run();

        if ( ! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->info('The language files have been formatted.');
    }
}
