<?php

namespace Dyln\Slim;

use Dyln\Util\ArrayUtil;
use SuperClosure\SerializableClosure;

class ModuleConfigSerializer
{
    static public function combineModuleConfig($moduleClasses = [])
    {
        $services = [];
        $params = [];
        /** @noinspection PhpIncludeInspection */
        $services = array_merge($services, include ROOT_DIR . '/app/config/services.php');
        /** @noinspection PhpIncludeInspection */
        $services = array_merge($services, include ROOT_DIR . '/app/config/config.php');
        /** @noinspection PhpIncludeInspection */
        $params = array_merge($params, include ROOT_DIR . '/app/config/params.php');
        foreach ($moduleClasses as $moduleClass) {
            $reflectionClass = new \ReflectionClass($moduleClass);
            $file = $reflectionClass->getFileName();
            $dir = dirname($file);
            $configFile = $dir . '/_config.php';
            if (file_exists($configFile)) {
                /** @noinspection PhpIncludeInspection */
                $config = @include $configFile;
                $moduleServices = ArrayUtil::getIn($config, ['services'], []);
                $moduleParams = ArrayUtil::getIn($config, ['params'], []);
                $services = array_merge($services, $moduleServices);
                $params = array_merge($params, $moduleParams);
            }
        }
        foreach ($services as $key => $value) {
            if ($value instanceof \Closure) {
                $services[$key] = new SerializableClosure($value);
            }
        }

        return ['services' => $services, 'params' => $params];
    }
}