<?php

namespace App\Actions;

use App\Options;

class DisplayHelpScreen
{
    public function __invoke()
    {
        app('console-writer')->text(
            component('lambo.help', [
                'newOptions' => (new Options())->all(),
                'commonOptions' => (new Options())->common(),
            ])->resolveView()
        );
    }
}
