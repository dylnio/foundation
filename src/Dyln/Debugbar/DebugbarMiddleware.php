<?php

namespace Dyln\Debugbar;

use Dyln\AppEnv;
use Dyln\Slim\Http\JsonResponse;
use Slim\Http\Request;

class DebugbarMiddleware
{
    public function __invoke(Request $request, JsonResponse $response, $next)
    {
        /** @var JsonResponse $response */
        $response = $next($request, $response);
        if (AppEnv::isDebugBarEnabled()) {
            if (!$request->getAttribute('ignore_debug', false)) {
                $body = (string)$response->getBody();
                if (!$body) {
                    $body = '{}';
                }
                $data = json_decode($body, true);
                $data['debug'] = Debugbar::getData();
                $response = $response->withJson($data);
            }
        }

        return $response;
    }
}