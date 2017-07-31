<?php

namespace Dyln\Middleware;

use Dyln\AppEnv;
use Slim\Http\Request;
use Slim\Http\Response;

class PhpInfoMiddleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        if (AppEnv::isDebugEnabled() && $request->getUri()->getPath() == '/phpinfo') {
            phpinfo();
            die();
        }

        return $next($request, $response);
    }
}