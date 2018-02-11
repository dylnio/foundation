<?php

namespace Dyln\Mongo;

class MongofyKeys
{
    public static function safe($array = [])
    {
        $replacements = [
            '.' => '{_DOT_}',
            '$' => '{_DOLLAR_}',
        ];
        foreach ($array as $k => $v) {
            $k = str_replace("\0", '', $k);
            foreach ($replacements as $badKey => $substitute) {
                if (strpos($k, $badKey) !== false) {
                    $k2 = str_replace($badKey, $substitute, $k);
                    $array[$k2] = $v;
                    unset($array[$k]);
                    $k = $k2;
                }
            }
            if (is_array($v)) {
                $array[$k] = self::safe($v);
            }
        }

        return $array;
    }

    public static function unsafe($array = [])
    {
        $replacements = [
            '.' => '{_DOT_}',
            '$' => '{_DOLLAR_}',
        ];
        foreach ($array as $k => $v) {
            foreach ($replacements as $badKey => $substitute) {
                if (strpos($k, $substitute) !== false) {
                    $k2 = str_replace($substitute, $badKey, $k);
                    $array[$k2] = $v;
                    unset($array[$k]);
                    $k = $k2;
                }
            }
            if (is_array($v)) {
                $array[$k] = self::unsafe($v);
            }
        }

        return $array;
    }
}
