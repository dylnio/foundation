<?php

namespace Dyln\Sentry\Middleware;

use Dyln\Sentry\Sentry;
use Slim\Http\Request;
use Slim\Http\Response;

class RequestInfoMiddleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        if (php_sapi_name() !== 'cli') {
            Sentry::addExtraContext('_url_', $_SERVER['REQUEST_URI']);
            Sentry::addExtraContext('_query_', json_encode($request->getQueryParams()));
            $body = (string) $request->getBody();
            Sentry::addExtraContext('_body_', $body);
        }

        return $next($request, $response);
    }
}
