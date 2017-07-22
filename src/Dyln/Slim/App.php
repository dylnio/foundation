<?php

namespace Dyln\Slim;

use DI\ContainerBuilder;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Dyln\DI\Container;
use Dyln\Slim\Module\ModuleInterface;
use Dyln\Util\ArrayUtil;
use Interop\Container\ContainerInterface;
use SuperClosure\SerializableClosure;

class App extends \Slim\App
{
    /** @var ModuleInterface[] */
    protected $modules = [];

    public function __construct($params = [])
    {
        if (!defined('CACHED_SERVICES_FILE')) {
            define('CACHED_SERVICES_FILE', '/tmp/__services.cache.php');
        }
        $containerClass = $params['container_class'] ?? Container::class;
        $containerBuilder = new ContainerBuilder($containerClass);
        $config = $this->enhanceConfig($params);
        $services = ArrayUtil::getIn($config, ['services'], []);
        $params = array_merge($params, ArrayUtil::getIn($config, ['params'], []));

        $containerBuilder->addDefinitions($services);
        $cache = $this->getDiCache($params);
        $containerBuilder->setDefinitionCache($cache);
        $container = $containerBuilder->build();
        $container->set('app', $this);
        $container->set(App::class, $this);
        $container->set('app_params', $params);
        parent::__construct($container);
        $this->registerModules($container);
    }

    private function enhanceConfig($params = [])
    {
        $purge = $_REQUEST['purge'] ?? false;
        if ($purge && file_exists(CACHED_SERVICES_FILE)) {
            unlink(CACHED_SERVICES_FILE);
        }
        if (file_exists(CACHED_SERVICES_FILE)) {
            $serialized = file_get_contents(CACHED_SERVICES_FILE);
        } else {
            $modules = ArrayUtil::getIn($params, ['modules'], []);
            $merged = ModuleConfigSerializer::combineModuleConfig($modules);
            $serialized = serialize($merged);
            file_put_contents(CACHED_SERVICES_FILE, $serialized);
        }
        if (!$serialized) {
            $serialized = [];
        }

        $data = unserialize($serialized);
        foreach ($data['services'] as $key => $value) {
            if ($value instanceof SerializableClosure) {
                $data['services'][$key] = $value->getClosure();
            }
        }

        return $data;
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
        $modules = array_values(array_unique($modules));
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
