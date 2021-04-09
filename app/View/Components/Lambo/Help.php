<?php

namespace App\View\Components\Lambo;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Help extends Component
{
    protected $newOptions;
    protected $commonOptions;

    protected $indent = 30;

    public function __construct(array $newOptions, array $commonOptions)
    {
        $this->newOptions = $newOptions;
        $this->commonOptions = $commonOptions;
    }

    public function render(): View
    {
        return view('components.help', $this->data());
    }

    public function commonOptions()
    {
        return $this->createOptions($this->commonOptions);
    }

    public function lamboNewOptions()
    {
        return $this->createOptions($this->newOptions);
    }

    private function createOptions($options)
    {
        return collect($options)->reduce(function ($carry, $option) {
            if (isset($option['short'])) {
                $flag = '-' . $option['short'] . ', --' . $option['long'];
            } else {
                $flag = '    --' . $option['long'];
            }

            if (isset($option['param_description'])) {
                $flag .= '=' . $option['param_description'];
            }

            return "{$carry}   <info>{$flag}</info>{$this->getSpaces($flag)}{$option['cli_description']}\n";
        });
    }

    private function getSpaces(string $flag): string
    {
        return str_repeat(' ', $this->indent - strlen($flag));
    }
}
