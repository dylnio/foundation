<?php

namespace Dyln\Slim;

use SuperClosure\SerializableClosure;

class ModuleConfigSerializer
{
    static public function combineAndSerialize($moduleClasses = [])
    {
        $combined = [];
        /** @noinspection PhpIncludeInspection */
        $combined = array_merge($combined, include ROOT_DIR . '/app/config/services.php');
        /** @noinspection PhpIncludeInspection */
        $combined = array_merge($combined, include ROOT_DIR . '/app/config/config.php');
        foreach ($moduleClasses as $moduleClass) {
            $reflectionClass = new \ReflectionClass($moduleClass);
            $file = $reflectionClass->getFileName();
            $dir = dirname($file);
            $servicesFile = $dir . '/services.php';
            if (file_exists($servicesFile)) {
                /** @noinspection PhpIncludeInspection */
                $services = @include $servicesFile;
                if (is_array($services)) {
                    $combined = array_merge($combined, $services);
                }
            }
        }
        foreach ($combined as $key => $value) {
            if ($value instanceof \Closure) {
                $combined[$key] = new SerializableClosure($value);
            }
        }
        $serialized = serialize($combined);

        return $serialized;
    }
}