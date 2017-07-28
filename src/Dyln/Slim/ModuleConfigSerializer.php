<?php

namespace Dyln\Slim;

use Dyln\Util\ArrayUtil;
use SuperClosure\SerializableClosure;

class ModuleConfigSerializer
{
    static public function combineModuleConfig($moduleClasses = [], $doSerialize = true)
    {
        $services = [];
        $params = [];
        /** @noinspection PhpIncludeInspection */
        $services = array_merge($services, include ROOT_DIR . '/app/config/services.php', include ROOT_DIR . '/app/config/config.php');
        /** @noinspection PhpIncludeInspection */
        $params = array_merge($params, include ROOT_DIR . '/app/config/params.php');
        foreach ($moduleClasses as $moduleClass) {
            $bits = explode('\\', $moduleClass);
            unset($bits[count($bits) - 1]);
            $dir = ROOT_DIR . '/app/src/Modules/' . implode('/', array_slice($bits, 2));
            $configFile = $dir . '/_config.php';
            if (file_exists($configFile)) {
                /** @noinspection PhpIncludeInspection */
                $config = include $configFile;
                if ($config) {
                    $moduleServices = ArrayUtil::getIn($config, ['services'], []);
                    $moduleParams = ArrayUtil::getIn($config, ['params'], []);
                    $services = array_merge($services, $moduleServices);
                    $params = array_merge($params, $moduleParams);
                }
            }
        }
        if ($doSerialize) {
            foreach ($services as $key => $value) {
                if ($value instanceof \Closure) {
                    $services[$key] = new SerializableClosure($value);
                }
            }
        }

        return ['services' => $services, 'params' => $params];
    }
}