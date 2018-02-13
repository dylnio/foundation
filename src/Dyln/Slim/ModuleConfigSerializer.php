<?php

namespace Dyln\Slim;

use function Dyln\getin;

class ModuleConfigSerializer
{
    public static function combineModuleConfig($moduleClasses = [])
    {
        $services = [];
        $params = [];
        foreach ($moduleClasses as $moduleClass) {
            $ref = new \ReflectionClass($moduleClass);
            $dir = dirname($ref->getFileName());
            $configFile = $dir . '/_config.php';
            if (file_exists($configFile)) {
                /** @noinspection PhpIncludeInspection */
                $config = include $configFile;
                if ($config) {
                    $moduleServices = getin($config, ['services'], []);
                    $moduleParams = getin($config, ['params'], []);
                    $services = array_merge($services, $moduleServices);
                    $params = array_merge($params, $moduleParams);
                }
            }
        }

        return [
            'services' => $services,
            'params'   => $params,
        ];
    }
}
