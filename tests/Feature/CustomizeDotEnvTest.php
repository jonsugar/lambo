<?php

namespace Tests\Feature;

use App\Actions\CustomizeDotEnv;
use App\Environment;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CustomizeDotEnvTest extends TestCase
{
    /** @test */
    function it_saves_the_customized_dot_env_files()
    {
        config(['lambo.store.project_name' => 'my-project']);
        config(['lambo.store.database_name' => 'my_project']);
        config(['lambo.store.project_url' => 'http://my-project.example.com']);
        config(['lambo.store.database_username' => 'username']);
        config(['lambo.store.database_password' => 'password']);

        $this->assertEquals(
            Environment::toSystemLineSeperators(File::get(base_path('tests/Feature/Fixtures/.env.customized'))),
            app(CustomizeDotEnv::class)->customize(File::get(base_path('tests/Feature/Fixtures/.env.original')))
        );
    }

    /** @test */
    function it_replaces_static_strings()
    {
        config()->set('lambo.store.database_username', 'root');

        $customizeDotEnv = app(CustomizeDotEnv::class);
        $contents = 'DB_USERNAME=previous';
        $contents = $customizeDotEnv->customize($contents);
        $this->assertEquals('DB_USERNAME=root', $contents);
    }

    /** @test */
    function un_targeted_lines_are_unchanged()
    {
        config()->set('lambo.store.database_username', 'root');

        $customizeDotEnv = app(CustomizeDotEnv::class);
        $contents = "DB_USERNAME=previous\nDONT_TOUCH_ME=cant_touch_me";
        $contents = $customizeDotEnv->customize($contents);
        $this->assertEquals(Environment::toSystemLineSeperators("DB_USERNAME=root\nDONT_TOUCH_ME=cant_touch_me"), $contents);
    }

    /** @test */
    function lines_with_no_equals_are_unchanged()
    {
        $customizeDotEnv = app(CustomizeDotEnv::class);
        $contents = "SOME_VALUE=previous\nABCDEFGNOEQUALS";
        $contents = $customizeDotEnv->customize($contents);
        $this->assertEquals(
            Environment::toSystemLineSeperators("SOME_VALUE=previous\nABCDEFGNOEQUALS"),
            $contents
        );
    }

    /** @test */
    function line_breaks_remain()
    {
        $customizeDotEnv = app(CustomizeDotEnv::class);
        $contents = "A=B\n\nC=D";
        $contents = $customizeDotEnv->customize($contents);
        $this->assertEquals(
            Environment::toSystemLineSeperators("A=B\n\nC=D"),
            $contents
        );
    }
}
