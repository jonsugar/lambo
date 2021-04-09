<?php

use Illuminate\Support\Str;
use Illuminate\View\Component;

if (! function_exists('component')) {
    function component(string $class, array $parameters = []): Component
    {
        if (Str::contains($class, '.')) {
            $class = 'App\\View\\Components\\' . Str::of(str_replace('.', '\\', $class))->title();
        }

        if (! class_exists($class)) {
            throw new InvalidArgumentException("'{$class}' is not a class that can be instantiated'");
        }

        $object = app($class, $parameters);
        if (! $object instanceof Component) {
            throw new InvalidArgumentException("'{$class}' is not an \Illuminate\View\Component class'");
        }

        return $object ;
    }
}
