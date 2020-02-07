<?php

namespace Tests\Feature;

use App\Actions\VerifyPathAvailable;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VerifyPathAvailableTest extends TestCase
{
    /** @test */
    function it_checks_if_the_required_directories_are_available()
    {
        $this->fakeLamboConsole();

        Config::set('lambo.store.root_path', '/some/filesystem/path');
        File::shouldReceive('isDirectory')
            ->with('/some/filesystem/path')
            ->once()
            ->andReturn(true);

        Config::set('lambo.store.project_path', '/some/filesystem/path/my-project');
        File::shouldReceive('isDirectory')
            ->with('/some/filesystem/path/my-project')
            ->once()
            ->andReturn(false);

        (new VerifyPathAvailable)();
    }

    /** @test */
    function it_throws_an_exception_if_the_root_path_is_not_available()
    {
        $this->fakeLamboConsole();

        Config::set('lambo.store.root_path', '/non/existent/filesystem/path');
        File::shouldReceive('isDirectory')
            ->with('/non/existent/filesystem/path')
            ->once()
            ->andReturn(false);

        $this->expectException(Exception::class);

        (new VerifyPathAvailable)();
    }

    /** @test */
    function it_throws_an_exception_if_the_project_path_already_exists()
    {
        $this->fakeLamboConsole();

        Config::set('lambo.store.root_path', '/some/filesystem/path');
        File::shouldReceive('isDirectory')
            ->with('/some/filesystem/path')
            ->once()
            ->andReturn(true);

        Config::set('lambo.store.project_path', '/some/filesystem/path/existing-directory');
        File::shouldReceive('isDirectory')
            ->with('/some/filesystem/path/existing-directory')
            ->once()
            ->andReturn(true);

        $this->expectException(Exception::class);

        (new VerifyPathAvailable)();
    }
}
