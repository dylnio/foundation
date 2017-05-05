<?php

namespace Dyln\Firewall\Middleware;

use Dyln\Firewall\Firewall;
use Dyln\Session\Session;
use Dyln\Slim\App;
use Dyln\Slim\CallableResolver;
use FastRoute\Dispatcher;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
    /** @var App */
    protected $app;

    public function __construct(Firewall $firewall, Router $router, Session $session, App $app)
    {
        $this->firewall = $firewall;
        $this->router = $router;
        $this->session = $session;
        $this->app = $app;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, $next)
    {
        $authorized = $this->authorized($request);
        if ($authorized === true || $authorized === null) {
            return $next($request, $response, $this);
        }
        if ($request->isXhr()) {
            return $response->withStatus(401);
        }
        $accept = $request->getHeader('Accept');
        if ($accept && $accept[0] == 'application/json') {
            return $response->withStatus(401);
        }
        $location = $this->firewall->getRoute(Firewall::ROUTE_DENIED);
        if (!$this->firewall->isLoggedIn()) {
            $target = (string)$request->getUri();
            $this->session->getSegment('redirect')->set('url', $target);
            $location = $this->firewall->getRoute(Firewall::ROUTE_LOGIN);
        }
        $headers['Content-type'] = 'application/x-www-form-urlencoded';
        $response = $this->app->subRequest('GET', $location, '', $headers, [], '', new Response());

        return $response;
    }

    private function authorized(RequestInterface &$request)
    {
        $route = $this->getRoute($request);
        if ($route) {
            /** @var CallableResolver $callable */
            $callable = $route->getCallable();
            list($resource, $privilege) = explode(':', $callable);
            if ($this->firewall->isAuthorized($resource, $privilege)) {
                return true;
            }
        }

        return false;
    }

    private function getRoute(RequestInterface &$request)
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