<?php

namespace Dyln\Slim\Module;

abstract class AbstractModule implements ModuleInterface
{

    public function init($params = [])
    {
    }

    public function boot()
    {
    }

    static public function getConfig()
    {
        $dir = dirname((new \ReflectionClass(get_called_class()))->getFileName());
        $configFile = $dir . '/services.php';

        return @include($configFile);
    }
}
