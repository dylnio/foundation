<?php

namespace Dyln\Slim;

use DI\ContainerBuilder;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Dyln\DI\Container;
use Dyln\Slim\Module\ModuleInterface;
use Interop\Container\ContainerInterface;

class App extends \Slim\App
{
    /** @var ModuleInterface[] */
    protected $modules = [];
    protected $config;

    public function __construct($config, $params = [])
    {
        $this->config = $config;
        $containerBuilder = new ContainerBuilder(Container::class);
        $cache = $this->getDiCache($params);
        $containerBuilder->setDefinitionCache($cache);
        $containerBuilder->addDefinitions($this->config);
        $container = $containerBuilder->build();
        $container->set('app', $this);
        $container->set(App::class, $this);
        $container->set('app_params', $params);
        parent::__construct($container);
        $this->registerModules($container);
    }

    private function getDiCache($params = [])
    {
        $adapter = $params['di']['cache']['adapter'];
        if ($adapter == RedisCache::class) {
            $cache = new RedisCache();
            $redis = new \Redis();
            $redis->connect($params['di']['cache'][RedisCache::class]['host'], $params['di']['cache'][RedisCache::class]['port']);
            $redis->setOption(\Redis::OPT_SERIALIZER, defined('Redis::SERIALIZER_IGBINARY') ? \Redis::SERIALIZER_IGBINARY : \Redis::SERIALIZER_PHP);
            $redis->select($params['di']['cache'][RedisCache::class]['db']);
            $cache->setRedis($redis);
            $cache->setNamespace($params['di']['cache'][RedisCache::class]['prefix']);

            return $cache;
        } elseif ($adapter == FilesystemCache::class) {
            return new FilesystemCache($params['di']['cache'][FilesystemCache::class]['dir']);
        } else {
            return new ArrayCache();
        }
    }

    private function registerModules(ContainerInterface $container)
    {
        $modules = $container->get('app_params')['modules'];
        foreach ($modules as $moduleClass) {
            /** @var ModuleInterface $module */
            $module = $container->get($moduleClass);
            $this->modules[] = $module;
        }
    }

    public function boot($params = [])
    {
        foreach ($this->modules as $module) {
            $module->init($params);
        }
        foreach ($this->modules as $module) {
            $module->boot();
        }
    }

    public function getGeneric($pattern, $actionClassName)
    {
        return $this->get($pattern, $actionClassName . ':dispatch');
    }

    public function postGeneric($pattern, $actionClassName)
    {
        return $this->post($pattern, $actionClassName . ':dispatch');
    }

    public function putGeneric($pattern, $actionClassName)
    {
        return $this->put($pattern, $actionClassName . ':dispatch');
    }

    public function deleteGeneric($pattern, $actionClassName)
    {
        return $this->delete($pattern, $actionClassName . ':dispatch');
    }
}
