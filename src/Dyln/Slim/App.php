<?php

namespace Dyln\Slim;

use DI\ContainerBuilder;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Dyln\AppEnv;
use Dyln\Config\Config;
use Dyln\DI\Container;
use Dyln\Slim\Module\ModuleInterface;
use Interop\Container\ContainerInterface;
use function Dyln\getin;

class App extends \Slim\App
{
    /** @var ModuleInterface[] */
    protected $modules = [];

    public function __construct($services = [])
    {
        if (!defined('CACHED_SERVICES_FILE')) {
            define('CACHED_SERVICES_FILE', '/tmp/__services.cache.php');
        }
        $containerClass = Config::get('di.container_class', Container::class);
        $containerBuilder = new ContainerBuilder($containerClass);
        $config = $this->mergeModuleConfigs();
        $services = array_merge($services, getin($config, ['services'], []));
        Config::merge(getin($config, ['params'], []));
        $containerBuilder->addDefinitions($services);
        $cache = $this->getDiCache(Config::get(['di.cache']));
        $containerBuilder->setDefinitionCache($cache);
        $container = $containerBuilder->build();
        $container->set('app', $this);
        $container->set(App::class, $this);
        parent::__construct($container);
        $this->registerModules($container);
    }

    private function mergeModuleConfigs()
    {
        $modules = Config::get('modules', []);
        $merged = ModuleConfigSerializer::combineModuleConfig($modules);

        return $merged;
    }

    private function getDiCache($params = [])
    {
        $adapter = $params['adapter'];
        if ($adapter == RedisCache::class) {
            $cache = new RedisCache();
            $redis = new \Redis();
            $redis->connect($params[RedisCache::class]['host'], $params[RedisCache::class]['port']);
            $redis->setOption(\Redis::OPT_SERIALIZER, defined('Redis::SERIALIZER_IGBINARY') ? \Redis::SERIALIZER_IGBINARY : \Redis::SERIALIZER_PHP);
            $redis->select($params[RedisCache::class]['db']);
            $cache->setRedis($redis);
            $cache->setNamespace($params[RedisCache::class]['prefix']);
        } elseif ($adapter == ApcuCache::class) {
            $cache = new ApcuCache();
        } elseif ($adapter == FilesystemCache::class) {
            $cache = new FilesystemCache($params[FilesystemCache::class]['dir']);
        } else {
            $cache = new ArrayCache();
        }
        $namespace = AppEnv::option('app_id', gethostname());
        $cache->setNamespace($namespace);

        return $cache;
    }

    private function registerModules(ContainerInterface $container)
    {
        $modules = array_values(array_unique(Config::get('modules', [])));
        foreach ($modules as $moduleClass) {
            /** @var ModuleInterface $module */
            $module = $container->get($moduleClass);
            $this->modules[] = $module;
        }
    }

    public function boot()
    {
        foreach ($this->modules as $module) {
            $module->init();
        }
        foreach ($this->modules as $module) {
            $module->boot();
        }
    }

    public function getGeneric($pattern, $actionClassName)
    {
        return $this->get($pattern, "{$actionClassName}:dispatch");
    }

    public function postGeneric($pattern, $actionClassName)
    {
        return $this->post($pattern, "{$actionClassName}:dispatch");
    }

    public function putGeneric($pattern, $actionClassName)
    {
        return $this->put($pattern, "{$actionClassName}:dispatch");
    }

    public function patchGeneric($pattern, $actionClassName)
    {
        return $this->patch($pattern, "{$actionClassName}:dispatch");
    }

    public function deleteGeneric($pattern, $actionClassName)
    {
        return $this->delete($pattern, "{$actionClassName}:dispatch");
    }
}
