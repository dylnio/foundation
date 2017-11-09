<?php

namespace Dyln\Debugbar;

use Dyln\AppEnv;
use Slim\Http\Request;
use Slim\Http\Response;

class DebugbarDumpMiddleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        $next($request, $response);
        if (AppEnv::isDebugEnabled()) {
            if (AppEnv::isUrlKeyMatch('dump', 1)) {
                die(var_dump(Debugbar::getData()));
            }
        }

        return $response;
    }
}
