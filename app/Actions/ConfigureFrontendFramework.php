<?php

namespace App\Actions;

use App\ConsoleWriter;
use App\Shell;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class ConfigureFrontendFramework
{
    use AbortsCommands;

    protected $shell;
    protected $consoleWriter;

    public function __construct(Shell $shell, ConsoleWriter $consoleWriter)
    {
        $this->shell = $shell;
        $this->consoleWriter = $consoleWriter;
    }

    public function __invoke()
    {
        $configuredFrontend = config('lambo.store.frontend');

        if ($configuredFrontend === 'none') {
            return;
        }

        $this->consoleWriter->logStep("Installing {$configuredFrontend} UI scaffolding");

        $this->ensureJetstreamInstalled();

        $process = $this->shell->execInProject(sprintf(
            "php artisan jetstream:install %s%s%s",
            $configuredFrontend,
            config('lambo.store.teams') ? ' --teams' : '',
            config('lambo.store.with_output') ? '' : ' --quiet'
        ));

        $this->abortIf(! $process->isSuccessful(), "Installation of {$configuredFrontend} UI scaffolding did not complete successfully.", $process);


        // START temporary workaround ------------------ @jonsugar (11-Sep-2020)
        // @TODO Remove manual dependency injection when App\ConsoleWriter and
        //       App\Shell are being bound into the container.

        if ($configuredFrontend === 'inertia') {
            app(InstallNpmDependencies::class, [
                'shell' => $this->shell,
                'consoleWriter' => $this->consoleWriter
            ])();

            app(CompileAssets::class, [
                'shell' => $this->shell,
                'consoleWriter' => $this->consoleWriter
            ])();
        }

        app(MigrateDatabase::class, [
            'shell' => $this->shell,
            'consoleWriter' => $this->consoleWriter
        ])();

        // END temporary workaround -------------------- @jonsugar (11-Sep-2020)

        $this->consoleWriter->verbose()->success("{$configuredFrontend} UI scaffolding installed.");
    }

    public function ensureJetstreamInstalled()
    {
        $composerConfig = json_decode(File::get(config('lambo.store.project_path') . '/composer.json'), true);
        if (Arr::has($composerConfig, 'require.laravel/jetstream')) {
            return;
        }

        $this->consoleWriter->verbose()->note('Installing required composer package laravel/jetstream.');

        $process = $this->shell->execInProject('composer require laravel/jetstream' . (config('lambo.store.with_output') ? '' : ' --quiet'));

        $this->abortIf(! $process->isSuccessful(), "Installation of laravel/jetstream did not complete successfully.", $process);

        $this->consoleWriter->verbose()->success('laravel/jetstream installed.');
    }
}
