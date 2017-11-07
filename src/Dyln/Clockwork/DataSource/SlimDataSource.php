<?php

namespace Dyln\Clockwork\DataSource;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use FastRoute\Dispatcher;
use Slim\App;
use Slim\Route;
use Slim\Router;

class SlimDataSource extends DataSource
{
    /** @var App */
    protected $app;

    public function __construct(App $slim)
    {
        $this->app = $slim;
    }

    public function resolve(Request $request)
    {
        $request->method = $this->getRequestMethod();
        $request->uri = $this->getRequestUri();
        $request->controller = $this->getController();
        $request->headers = $this->getRequestHeaders();
        $request->responseStatus = $this->getResponseStatus();
        return $request;
    }

    /**
     * Return textual representation of current route's controller
     */
    protected function getController()
    {
        /** @var \Slim\Http\Request $req */
        $req = $this->app->getContainer()->get(\Slim\Http\Request::class);
        /** @var Route $route */
        $route = $req->getAttribute('route');
        if (!$route) {
            $routeInfo = $this->app->getContainer()->get(Router::class)->dispatch($req);
            if ($routeInfo[0] === Dispatcher::FOUND) {
                $route = $this->app->getContainer()->get(Router::class)->lookupRoute($routeInfo[1]);
            }
        }
        if (!$route) {
            return null;
        }
        $controller = $route->getCallable();
        if ($controller instanceof \Closure) {
            $controller = 'anonymous function';
        } else if (is_object($controller)) {
            $controller = 'instance of ' . get_class($controller);
        } else if (is_array($controller) && count($controller) == 2) {
            if (is_object($controller[0])) {
                $controller = get_class($controller[0]) . '->' . $controller[1];
            } else {
                $controller = $controller[0] . '::' . $controller[1];
            }
        } else if (!is_string($controller)) {
            $controller = null;
        }
        return $controller;
    }

    /**
     * Return headers
     */
    protected function getRequestHeaders()
    {
        /** @var \Slim\Http\Request $req */
        $req = $this->app->getContainer()->get(\Slim\Http\Request::class);
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) !== 'HTTP_') continue;
            $header = substr($key, 5);
            $header = str_replace('_', ' ', $header);
            $header = ucwords(strtolower($header));
            $header = str_replace(' ', '-', $header);
            $value = $req->getHeader($header);
            if (!isset($headers[$header])) {
                $headers[$header] = [$value];
            } else {
                $headers[$header][] = $value;
            }
        }
        ksort($headers);
        return $headers;
    }

    /**
     * Return request method
     */
    protected function getRequestMethod()
    {
        /** @var \Slim\Http\Request $req */
        $req = $this->app->getContainer()->get(\Slim\Http\Request::class);
        return $req->getMethod();
    }

    /**
     * Return request URI
     */
    protected function getRequestUri()
    {
        /** @var \Slim\Http\Request $req */
        $req = $this->app->getContainer()->get(\Slim\Http\Request::class);
        return $req->getUri();
    }

    /**
     * Return response status code
     */
    protected function getResponseStatus()
    {
        /** @var \Slim\Http\Response $res */
        $res = $this->app->getContainer()->get(\Slim\Http\Response::class);
        return $res->getStatusCode();
    }
}
