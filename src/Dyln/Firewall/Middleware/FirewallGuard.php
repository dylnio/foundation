<?php

namespace Dyln\Firewall\Middleware;

use Dyln\Firewall\Exception\RouteNotFoundException;
use Dyln\Firewall\Firewall;
use Dyln\Session\Session;
use Dyln\Slim\CallableResolver;
use FastRoute\Dispatcher;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;
use Slim\Router;

class FirewallGuard
{
    /** @var  Firewall */
    protected $firewall;
    /** @var  Router */
    protected $router;
    /** @var Session */
    protected $session;

    public function __construct(Firewall $firewall, Router $router, Session $session)
    {
        $this->firewall = $firewall;
        $this->router = $router;
        $this->session = $session;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $authorized = $this->authorized($request);
        if ($authorized === true || $authorized === null) {
            return $next($request, $response, $this);
        }
        if ($request->isGet()) {
            $url = $request->getUri()->getPath();
            if ($request->getQueryParams()) {
                $url .= '?' . http_build_query($request->getQueryParams());
            }
            $this->session->set('_redirectafterlogin', $url);
        }
        $url = $this->firewall->getRoute(Firewall::ROUTE_LOGIN);
        if ($url) {
            return $response->withRedirect($url);
        }

        return $response->withStatus(401);
    }

    private function authorized(Request $request)
    {
        $route = $this->getRoute($request);
        if (!$route) {
            throw new RouteNotFoundException('Route not found: ' . $request->getUri()->getPath());
        }
        /** @var CallableResolver $callable */
        $callable = $route->getCallable();
        if ($callable instanceof \Closure) {
            $resource = $route->getName();
            $privilege = '~';
        } else if (is_array($callable)) {
            $resource = get_class($callable[0]);
            $privilege = $callable[1];
        } else {
            list($resource, $privilege) = explode(':', $callable);
        }
        if ($this->firewall->isAuthorized($resource, $privilege)) {
            return true;
        }

        return false;
    }

    private function getRoute(Request $request)
    {
        /** @var Route $route */
        $route = $request->getAttribute('route');
        if (!$route) {
            $routeInfo = $this->router->dispatch($request);
            if ($routeInfo[0] === Dispatcher::FOUND) {
                $route = $this->router->lookupRoute($routeInfo[1]);
            }
        }

        return $route;
    }
}