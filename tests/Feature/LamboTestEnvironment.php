<?php

namespace Tests\Feature;

use App\Environment;
use Illuminate\Support\Facades\File;

trait LamboTestEnvironment
{

    protected function withValetTld($tld = 'test'): void
    {
        $valetConfig = Environment::toSystemPath(config('home_dir') . '/.config/valet/config.json');

        File::shouldReceive('isFile')
            ->with($valetConfig)
            ->andReturnTrue();

        File::shouldReceive('get')
            ->with($valetConfig)
            ->andReturn(sprintf('{"tld": "%s"}', $tld));
    }
}
