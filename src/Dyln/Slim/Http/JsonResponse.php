<?php

namespace Dyln\Slim\Http;

use Slim\Http\Response;

class JsonResponse extends Response
{
    public function withSuccess(array $payload = [])
    {
        return $this->withJson([
            'success' => true,
            'payload' => $payload,
        ]);
    }

    public function withError($message = null, $code = 0)
    {
        return $this->withJson([
            'success' => false,
            'message' => $message,
            'code'    => $code,
        ]);
    }
}