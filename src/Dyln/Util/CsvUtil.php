<?php

namespace Dyln\Util;

class CsvUtil
{
    public static function getDelimiter($handle)
    {
        if (!is_resource($handle)) {
            throw new \InvalidArgumentException('$handle must be a resource');
        }
        $delimiters = [
            ',',
            "\t",
            ';',
            '|',
            ':',
        ];
        $count = 0;
        $results = [];
        while ($count < 10) {
            $line = fgets($handle, 4096);
            foreach ($delimiters as $delimiter) {
                $regExp = '/[' . $delimiter . ']/';
                $fields = preg_split($regExp, $line);
                if (count($fields) > 1) {
                    if (!empty($results[$delimiter])) {
                        $results[$delimiter]++;
                    } else {
                        $results[$delimiter] = 1;
                    }
                }
            }
            $count++;
        }
        $results = array_keys($results, max($results));

        return $results[0];
    }

    public static function head($handle, $lines = 200)
    {
        if (!is_resource($handle)) {
            throw new \InvalidArgumentException('$handle must be a resource');
        }
        $delimiter = self::getDelimiter($handle);
        $i = 1;
        $rows = [];
        while ($i <= $lines) {
            $row = fgetcsv($handle, 4096, $delimiter);
            $rows[] = $row;
        }

        return $rows;
    }
}
