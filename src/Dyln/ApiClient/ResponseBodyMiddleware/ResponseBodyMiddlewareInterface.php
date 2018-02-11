<?php

namespace Dyln\ApiClient\ResponseBodyMiddleware;

interface ResponseBodyMiddlewareInterface
{
    public function execute($body);
}
