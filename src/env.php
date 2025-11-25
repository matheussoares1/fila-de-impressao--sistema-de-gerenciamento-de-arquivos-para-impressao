<?php

function loadEnv($path)
{
    if (!file_exists($path)) {
        throw new Exception(".env file not found at: $path");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Ignora comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Divide KEY=VALUE
        list($key, $value) = explode('=', $line, 2);

        $key   = trim($key);
        $value = trim($value);

        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
