<?php

namespace Dyln\Util;

use Html2Text\Html2Text;
use Stringy\StaticStringy;

class StringUtil
{
    static protected $canonicalNamesReplacements = ['-' => '', '_' => '', ' ' => '', '\\' => '', '/' => '', ',' => ''];

    static public function canonicalizeName($name)
    {
        return strtolower(strtr($name, self::$canonicalNamesReplacements));
    }

    public static function random($length = 16)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = static::randomBytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    public static function randomBytes($length = 16)
    {
        if (PHP_MAJOR_VERSION >= 7) {
            $bytes = random_bytes($length);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length, $strong);
            if ($bytes === false || $strong === false) {
                throw new \RuntimeException('Unable to generate random string.');
            }
        } else {
            throw new \RuntimeException('OpenSSL extension is required for PHP 5 users.');
        }

        return $bytes;
    }

    public static function slug($title, $separator = '-')
    {
        $title = static::ascii($title);
        // Convert all dashes/underscores into separator
        $flip = $separator == '-' ? '_' : '-';
        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);
        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));
        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    public static function ascii($value)
    {
        return (string)StaticStringy::toAscii($value);
    }

    public static function explodeCamelCase($string)
    {
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $string, $matches);

        return reset($matches);
    }

    public static function xmlToArray(\SimpleXMLElement $xml, $options = [])
    {
        $defaults = [
            'namespaceSeparator' => ':',//you may want this to be something other than a colon
            'attributePrefix'    => '@',   //to distinguish between attributes and nodes with the same name
            'alwaysArray'        => [],   //array of xml tag names which should always become arrays
            'autoArray'          => true,        //only create arrays for tags which appear more than once
            'textContent'        => '_v_',       //key used for the text content of elements
            'autoText'           => true,         //skip textContent key if node has no attributes or child nodes
            'keySearch'          => false,       //optional search and replace on tag and attribute names
            'keyReplace'         => false       //replace values for above search values (as passed to str_replace())
        ];
        $options = array_merge($defaults, $options);
        $namespaces = $xml->getNamespaces(true);
        $namespaces[''] = null; //add base (empty) namespace
        //get attributes from all namespaces
        $attributesArray = [];
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                //replace characters in attribute name
                if ($options['keySearch']) {
                    $attributeName = str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                }
                $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }
        //get child nodes from all namespaces
        $tagsArray = [];
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                //recurse into child nodes
                $childArray = self::xmlToArray($childXml, $options);
                list($childTagName, $childProperties) = each($childArray);
                //replace characters in tag name
                if ($options['keySearch']) {
                    $childTagName = str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                }
                //add namespace prefix, if any
                if ($prefix) {
                    $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
                }
                if (!isset($tagsArray[$childTagName])) {
                    //only entry with this key
                    //test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                            ? [$childProperties] : $childProperties;
                } elseif (
                    is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                    === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    //key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    //key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = [$tagsArray[$childTagName], $childProperties];
                }
            }
        }
        //get text content of node
        $textContentArray = [];
        $plainText = trim((string)$xml);
        if ($plainText !== '') {
            $textContentArray[$options['textContent']] = $plainText;
        }
        //stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        //return node as array
        return [
            $xml->getName() => $propertiesArray,
        ];
    }

    public static function isAssoc($arr)
    {
        if (!is_array($arr)) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function getInitials($name)
    {
        $bits = explode(' ', $name);
        $initals = '';
        foreach ($bits as $bit) {
            if ($bit) {
                $initals .= strtoupper($bit[0]) . '. ';
            }
        }

        return trim($initals);
    }

    static public function makeValidUTF8($string)
    {
        $string = mb_convert_encoding($string, "UTF-8", "UTF-8");
        $string = str_replace("\xc2\xa0", ' ', $string);

        return preg_replace('!\p{C}!u', '', $string);
    }

    static public function render($template, $vars = [])
    {
        $formatted = [];
        foreach ($vars as $field => $value) {
            $formatted['{{' . $field . '}}'] = $value;
        }

        return str_replace(array_keys($formatted), array_values($formatted), $template);
    }

    static public function htmlToText($document)
    {
        $html = new Html2Text($document);

        return $html->getText();
    }
}