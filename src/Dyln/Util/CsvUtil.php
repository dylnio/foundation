<?php

namespace Dyln\Csv;

class CsvUtil
{
    static public function getDelimiter($handle)
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
}