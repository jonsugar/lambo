<?php

namespace App\Commands;

use App\Actions\CustomizeDotEnv;
use App\Actions\DisplayHelpScreen;
use App\Actions\DisplayLamboWelcome;
use App\Actions\GenerateAppKey;
use App\Actions\InitializeGitRepo;
use App\Actions\InstallNpmDependencies;
use App\Actions\OpenInBrowser;
use App\Actions\OpenInEditor;
use App\Actions\RunLaravelInstaller;
use App\Actions\ValetSecure;
use App\Actions\VerifyDependencies;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class NewCommand extends Command
{
    protected $signature = 'new
        {projectName? : Name of the Laravel project}
    ';

    protected $description = 'Creates a fresh Laravel application';

    public function handle()
    {
        $this->setConfig();

        app(DisplayLamboWelcome::class)();

        if (! $this->argument('projectName')) {
            app(DisplayHelpScreen::class)();
        }

        $this->alert('Creating a Laravel app ' . $this->argument('projectName'));

        app(VerifyDependencies::class)();
        app(RunLaravelInstaller::class)();
        app(OpenInEditor::class)();
        app(CustomizeDotEnv::class)();
        app(GenerateAppKey::class)();
        app(InitializeGitRepo::class)();
        app(InstallNpmDependencies::class)();
        app(ValetSecure::class)();
        app(OpenInBrowser::class)();
        // @todo cd into it
    }

    public function setConfig()
    {
        app()->bind('console', function () {
            return $this;
        });

        $tld = $this->getTld();

        config()->set('lambo.store', [
            'install_path' => getcwd(),
            'tld' => $tld,
            'project_name' => $this->argument('projectName'),
            'project_path' => getcwd() . '/' . $this->argument('projectName'),
            'project_url' => $this->argument('projectName') . '.' . $tld,
        ]);
    }

    public function getTld()
    {
        $home = config('user_home_dir');

        if (File::exists($home . '/.config/valet/config.json')) {
            return json_decode(File::get($home . '/.config/valet/config.json'))->tld;
        }

        return json_decode(File::get($home . '/.valet/config.json'))->domain;
    }
}