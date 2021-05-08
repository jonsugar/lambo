<?php

namespace App\Configuration;

use App\Commands\NewCommand;
use App\ConsoleWriter;
use App\Environment;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetConfig
{
    protected $consoleWriter;
    protected $fullFlags = [
        LamboConfiguration::CREATE_DATABASE,
        LamboConfiguration::MIGRATE_DATABASE,
        LamboConfiguration::VALET_LINK,
        LamboConfiguration::VALET_SECURE,
    ];

    private $commandLineConfiguration;
    private $savedConfiguration;
    private $shellConfiguration;

    public function __construct(
        CommandLineConfiguration $commandLineConfiguration,
        SavedConfiguration $savedConfiguration,
        ShellConfiguration $shellConfiguration,
        ConsoleWriter $consoleWriter
    ) {
        $this->commandLineConfiguration = $commandLineConfiguration;
        $this->savedConfiguration = $savedConfiguration;
        $this->shellConfiguration = $shellConfiguration;
        $this->consoleWriter = $consoleWriter;
    }

    public function __invoke($defaultConfiguration)
    {
        foreach ($defaultConfiguration as $configurationKey => $default) {
            $methodName = 'get' . Str::of($configurationKey)->studly();
            if (method_exists($this, $methodName)) {
                config(["lambo.store.{$configurationKey}" => $this->$methodName($configurationKey, $default)]);
            } else {
                config(["lambo.store.{$configurationKey}" => $this->get($configurationKey, $default)]);
            }
        }

        // If we're in the "new" command, generate a few config items which
        // require others to be set above first.
        if (config('lambo.store.command') === NewCommand::class) {
            $projectPath = config('lambo.store.root_path') . DIRECTORY_SEPARATOR . config('lambo.store.project_name');
            config(['lambo.store.project_path' => $projectPath]);
            config(['lambo.store.project_url' => $this->getProjectURL()]);
        }

        if (config('lambo.store.full')) {
            foreach ($this->fullFlags as $fullFlag) {
                config(["lambo.store.{$fullFlag}" => true]);
            }
        }
    }

    private function get(string $configurationKey, $default)
    {
        if (isset($this->commandLineConfiguration->$configurationKey)) {
            return $this->commandLineConfiguration->$configurationKey;
        }

        if (isset($this->savedConfiguration->$configurationKey)) {
            return $this->savedConfiguration->$configurationKey;
        }

        if (isset($this->shellConfiguration->$configurationKey)) {
            return $this->shellConfiguration->$configurationKey;
        }

        return $default;
    }

    private function getTld(): string
    {
        $valetConfig = Environment::toSystemPath(config('home_dir') . '/.config/valet/config.json');
        $legacyValetConfig = Environment::toSystemPath(config('home_dir') . '/.valet/config.json');

        if (File::isFile($valetConfig)) {
            return json_decode(File::get($valetConfig))->tld;
        }

        if (File::isFile($legacyValetConfig)) {
            return json_decode(File::get($legacyValetConfig))->domain;
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            $this->consoleWriter->newLine();
            $this->consoleWriter->note(implode(PHP_EOL, [
                'Unable to determine the top-level-domain you use in development.',
                '',
                ' No Valet configuration located at either of the following locations:',
                "   - {$valetConfig}",
                "   - {$legacyValetConfig}",
                '',
                ' Setting top-level-domain to <comment>.test</comment>',
            ]));
        }
        if (PHP_OS_FAMILY !== 'Darwin') {
            $this->consoleWriter->newLine();
            $this->consoleWriter->note(implode(PHP_EOL, [
                'Unable to determine the top-level-domain you use in development.',
                '',
                ' Setting top-level-domain to <comment>.test</comment>',
            ]));
        }
        return 'test';
    }

    private function getRootPath(string $key, $default)
    {
        $configuredKeyValue = $this->get($key, $default);

        return ($configuredKeyValue === $default)
            ? $default
            : str_replace('~', config('home_dir'), $configuredKeyValue);
    }

    private function getDatabaseName(string $key, $default)
    {
        return str_replace('-', '_', $this->get($key, $default));
    }

    private function getProjectURL(): string
    {
        return sprintf(
            'http%s://%s.%s',
            config('lambo.store.valet_secure') ? 's' : '',
            config('lambo.store.project_name'),
            config('lambo.store.tld')
        );
    }

    private function getMigrateDatabase(string $key, $default)
    {
        if ($this->commandLineConfiguration->inertia || $this->commandLineConfiguration->livewire) {
            return true;
        }

        return $this->get($key, $default);
    }

    private function getWithOutput(string $key, $default): bool
    {
        if ($this->consoleWriter->getVerbosity() > SymfonyStyle::VERBOSITY_NORMAL) {
            return true;
        }

        return $this->get($key, $default);
    }

    private function getGithub(string $key, $default)
    {
        $gh = app('console')->option('github');
        if ($gh === 'disabled') {
            return false;
        }

        return $gh ?? '--private';
    }
}
