<?php

namespace Dyln\ApiClient\Exception;

use Dyln\Message\Message;
use Throwable;

class ApiException extends \Exception
{
    /** @var  Message */
    protected $response;

    public function setApiResponse(Message $response)
    {
        $this->response = $response;
    }

    public function getApiResponse(): Message
    {
        return $this->response;
    }

    public static function factory(Message $response, $message = "", $code = 0, Throwable $previous = null)
    {
        if (!$message) {
            $message = $response->getErrorMessage();
        }
        if (!$code) {
            $code = $response->getErrorCode();
        }
        $exception = new static($message, $code, $previous);
        $exception->setApiResponse($response);

        return $exception;
    }
}
