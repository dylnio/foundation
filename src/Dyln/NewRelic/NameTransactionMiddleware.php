<?php

namespace Dyln\NewRelic;

use FastRoute\Dispatcher;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;
use Slim\Router;

class NameTransactionMiddleware
{
    /** @var  Router */
    protected $router;

    /**
     * NameTransactionMiddleware constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        if (extension_loaded('newrelic')) {
            $route = $this->getRoute($request);
            if ($route) {
                newrelic_name_transaction($route->getPattern());
            }
        }

        return $next($request, $response);
    }

    private function getRoute(Request $request)
    {
        /** @var Route $route */
        $route = $request->getAttribute('route');
        if (!$route) {
            $routeInfo = $this->router->dispatch($request);
            if ($routeInfo[0] === Dispatcher::FOUND) {
                $routeArguments = [];
                foreach ($routeInfo[2] as $k => $v) {
                    $routeArguments[$k] = urldecode($v);
                }

                $route = $this->router->lookupRoute($routeInfo[1]);
            }
        }

        return $route;
    }
}