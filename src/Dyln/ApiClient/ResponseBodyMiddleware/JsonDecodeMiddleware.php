<?php

namespace Dyln\ApiClient\ResponseBodyMiddleware;

class JsonDecodeMiddleware implements ResponseBodyMiddlewareInterface
{
    public function execute($body)
    {
        if (!$body) {
            return $body;
        }
        $body = json_decode($body, true);
        $body = $body ?: [];

        return $body;
    }
}