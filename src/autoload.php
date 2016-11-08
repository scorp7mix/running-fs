<?php

spl_autoload_register(function ($class) {
    if (preg_match('~^Running~', $class)) {
        $file = __DIR__ . str_replace('\\', '/', preg_replace('~^Running~', '', $class)) . '.php';
        if (is_readable($file)) {
            require $file;
        }
    }
});