<?php

namespace App\Actions;

class DisplayLamboWelcome
{
    public function __invoke()
    {
        app('console-writer')->write(
            view('lambo.welcome', ['version' => config('app.version')])
        );
    }
}
