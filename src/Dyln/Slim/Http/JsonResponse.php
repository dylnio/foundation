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

    public function withError(array $messages = [])
    {
        return $this->withJson([
            'success' => false,
            'error'   => $messages,
        ]);
    }
}