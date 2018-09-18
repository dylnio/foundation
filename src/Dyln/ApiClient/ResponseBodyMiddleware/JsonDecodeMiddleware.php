<?php

namespace Dyln\ApiClient\ResponseBodyMiddleware;

use Dyln\ApiClient\Exception\InvalidJsonException;

class JsonDecodeMiddleware implements ResponseBodyMiddlewareInterface
{
    public function execute($body)
    {
        if (!$body) {
            return $body;
        }
        $decoded = json_decode($body, true);
        $jsonErrorCode = json_last_error();
        if ($jsonErrorCode !== 0) {
            $jsonErrorMessage = json_last_error_msg();
            $exception = new InvalidJsonException('json_decode error: ' . $jsonErrorMessage);
            $exception->setJsonString($body);
            throw $exception;
        }
        $decoded = $decoded ?: [];

        return $decoded;
    }
}
