<?php

namespace Dyln\Slim\Http;

use Slim\Http\Response;

class JsonResponse extends Response
{
    protected $isError = false;
    public $id;

    public function withSuccess(array $payload = [])
    {
        $this->isError = false;

        $response = $this->withJson([
            'success' => true,
            'payload' => $payload,
        ]);

        return $response;
    }

    public function withError($message = null, $code = 0)
    {
        $this->isError = true;

        $response = $this->withJson([
            'success' => false,
            'message' => $message,
            'code'    => $code,
        ]);

        return $response;
    }

    public function isError()
    {
        return $this->isError;
    }
}