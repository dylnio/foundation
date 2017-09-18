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

    public function withError($message = null, $code = 0, $extra = [])
    {
        $this->isError = true;
        if (is_array($message)) {
            $code = $message['code'] ?? null;
            $extra = $message['extra'] ?? [];
            $message = $message['message'] ?? null;
        }
        $response = $this->withJson([
            'success' => false,
            'message' => $message,
            'code'    => $code,
            'extra'   => $extra,
        ]);

        return $response;
    }

    public function isError()
    {
        return $this->isError || ($this->getStatusCode() == 500);
    }

    public function getErrorMessage()
    {
        $body = json_decode((string)$this->body, true);
        if ($this->getStatusCode() == 500) {
            return $body['message'] ?? 'No error message';
        }

        return $body['message'] ?? null;
    }

    public function getErrorCode()
    {
        $body = json_decode((string)$this->body, true);
        if ($this->getStatusCode() == 500) {
            return 500;
        }

        return $body['code'] ?? null;
    }

    public function getErrorExtra()
    {
        $body = json_decode((string)$this->body, true);
        if ($this->getStatusCode() == 500) {
            return $body['error'] ?? $body['exception'] ?? [];
        }

        return $body['extra'] ?? [];
    }
}