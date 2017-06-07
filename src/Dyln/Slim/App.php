<?php

namespace Dyln\Slim;

use DI\ContainerBuilder;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Dyln\DI\Container;
use Dyln\Slim\Module\ModuleInterface;
use Dyln\Util\ArrayUtil;
use Interop\Container\ContainerInterface;

class App extends \Slim\App
{
    /** @var ModuleInterface[] */
    protected $modules = [];

    public function __construct($config, $params = [])
    {
        $containerBuilder = new ContainerBuilder(Container::class);
        $cache = $this->getDiCache($params);
        $containerBuilder->setDefinitionCache($cache);
        $config = $this->enhanceConfig($config, $params, $cache);
        $containerBuilder->addDefinitions($config);
        $container = $containerBuilder->build();
        $container->set('app', $this);
        $container->set(App::class, $this);
        $container->set('app_params', $params);
        parent::__construct($container);
        $this->registerModules($container);
    }

    private function enhanceConfig($config = [], $params = [], CacheProvider $cache = null)
    {
        $merged = null;
        $key = 'enhanced_di_config';
        if ($cache) {
            $merged = $cache->fetch($key);
        }
        if (!$merged) {
            $merged = $config;
            $modules = ArrayUtil::getIn($params, ['modules'], []);
            foreach ($modules as $moduleName) {
                $moduleConfig = call_user_func($moduleName . '::getConfig');
                if ($moduleConfig) {
                    $merged = array_merge($merged, $moduleConfig);
                }
            }
            if ($cache) {
                $cache->save($key, $merged);
            }
        }

        return $merged;
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
