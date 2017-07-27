<?php

namespace Dyln\Slim;

use Doctrine\Common\Cache\CacheProvider;
use FastRoute\RouteCollector;

class Router extends \Slim\Router
{
    protected $key;
    /** @var  CacheProvider */
    protected $cacheProvider;

    public function setCacheProvider(CacheProvider $cacheProvider, $key = null)
    {
        $this->cacheProvider = $cacheProvider;
        if (!$key) {
            $key = gethostname() . '_route_cache';
        }
        $this->key = $key;
    }

    protected function createDispatcher()
    {
        if ($this->dispatcher) {
            return $this->dispatcher;
        }

        $routeDefinitionCallback = function (RouteCollector $r) {
            foreach ($this->getRoutes() as $route) {
                $r->addRoute($route->getMethods(), $route->getPattern(), $route->getIdentifier());
            }
        };
        if ($this->cacheProvider) {
            $options = [
                'routeParser'    => 'FastRoute\\RouteParser\\Std',
                'dataGenerator'  => 'FastRoute\\DataGenerator\\GroupCountBased',
                'dispatcher'     => 'FastRoute\\Dispatcher\\GroupCountBased',
                'routeCollector' => 'FastRoute\\RouteCollector',
            ];

            $dispatchData = $this->cacheProvider->fetch($this->key);
            if (!$dispatchData) {
                $routeCollector = new $options['routeCollector'](
                    new $options['routeParser'], new $options['dataGenerator']
                );
                $routeDefinitionCallback($routeCollector);

                /** @var RouteCollector $routeCollector */
                $dispatchData = $routeCollector->getData();
                $this->cacheProvider->save($this->key, $dispatchData);
            }

            return new $options['dispatcher']($dispatchData);
        } else if ($this->cacheFile) {
            $this->dispatcher = \FastRoute\cachedDispatcher($routeDefinitionCallback, [
                'routeParser' => $this->routeParser,
                'cacheFile'   => $this->cacheFile,
            ]);
        } else {
            $this->dispatcher = \FastRoute\simpleDispatcher($routeDefinitionCallback, [
                'routeParser' => $this->routeParser,
            ]);
        }

        return $this->dispatcher;
    }
}