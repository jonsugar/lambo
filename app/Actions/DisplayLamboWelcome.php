<?php

namespace App\Actions;

class DisplayLamboWelcome
{
    public function __invoke()
    {
        $welcome = view('lambo.welcome', ['version' => config('app.version')]);
        app('console-writer')->write($welcome);
    }
}
