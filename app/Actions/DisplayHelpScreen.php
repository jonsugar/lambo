<?php

namespace App\Actions;

use App\Options;

class DisplayHelpScreen
{
    public function __invoke()
    {
        $help = component('lambo.help', [
            'newOptions' => (new Options())->all(),
            'commonOptions' => (new Options())->common(),
        ])->render();

        app('console-writer')->text($help);
    }
}
