<?php

namespace App;

class Environment
{
    public static function isMac()
    {
        return PHP_OS === 'Darwin';
    }

    public static function toSystemPath(string $path)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public static function toSystemLineSeperators(string $text)
    {
        return str_replace("\n", PHP_EOL, $text);
    }
}
