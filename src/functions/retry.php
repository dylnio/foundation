<?php

namespace Dyln;

if (!function_exists('Dyln\_retry')) {
    function _retry(callable $callable, $maxTry = 5, $sleep = 0.5)
    {
        while ($maxTry) {
            try {
                return $callable();
                break;
            } catch (\Exception $e) {
                sleep($sleep);
                $maxTry--;
                if (!$maxTry) {
                    throw $e;
                }
            }
        }
    }
}
