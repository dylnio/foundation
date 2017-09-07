<?php

namespace Dyln;

if (!function_exists('Dyln\debug_var')) {
    function dgvar($original, $debug, $currentEnv = null, $productionEnv = 'production')
    {
        if (!$currentEnv) {
            $currentEnv = AppEnv::getAppEnv();
        }
        if ($currentEnv == $productionEnv) {
            return $original;
        }

        return $debug;
    }
}
