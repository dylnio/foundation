<?php

namespace Dyln\Slim\Http;

use Slim\Http\Response;

class JsonResponse extends Response
{
    protected $isError = false;

    public function withSuccess(array $payload = [])
    {
        $this->isError = false;

        return $this->withJson([
            'success' => true,
            'payload' => $payload,
        ]);
    }

    public function withError($message = null, $code = 0)
    {
        $this->isError = false;

        return $this->withJson([
            'success' => false,
            'message' => $message,
            'code'    => $code,
        ]);
    }

    public function isError()
    {
        return $this->isError;
    }
}