<?php

namespace Dyln;

if (!function_exists('Dyln\_load_env_')) {
    function _load_env_($dir, $prefix = null)
    {
        if (is_string($dir)) {
            $autodetect = ini_get('auto_detect_line_endings');
            ini_set('auto_detect_line_endings', '1');
            $lines = file($dir, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            ini_set('auto_detect_line_endings', $autodetect);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line) {
                    list($name, $value) = explode('=', $line);
                    $name = trim($name);
                    _setEnv($name, $value, $prefix);
                }
            }
        } elseif (is_array($dir)) {
            foreach ($dir as $key => $value) {
                _setEnv($key, $value, $prefix);
            }
        }
    }

    function _setEnv($name, $value, $prefix = null)
    {
        if ($prefix) {
            $name = $prefix . '.' . $name;
        }
        $value = trim($value);
        if (function_exists('apache_getenv') && function_exists('apache_setenv') && apache_getenv($name)) {
            apache_setenv($name, $value);
        }

        if (function_exists('putenv')) {
            putenv("$name=$value");
        }

        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}