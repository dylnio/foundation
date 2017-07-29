<?php

namespace Dyln\Http\Header;

use Slim\Http\Request;
use Slim\Http\Response;

class ExtraHeaderMiddleware
{
    public static $headers = [];

    public function __invoke(Request $request, Response $response, $next)
    {
        $response = $next($request, $response);
        foreach (self::$headers as $key => $value) {
            $key = '__' . $key;
            $response = $response->withHeader($key, $value);
        }

        return $response;
    }
}