<?php

namespace Dyln;

function _retry(callable $callable, $maxTry = 5)
{
    while ($maxTry) {
        try {
            return $callable();
            break;
        } catch (\Exception $e) {
            sleep(0.5);
            $maxTry--;
            if (!$maxTry) {
                throw $e;
            }
        }
    }
}