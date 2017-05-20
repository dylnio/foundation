<?php

namespace Dyln\Slim\Http;

use Dyln\Debugbar\Debugbar;
use Slim\Http\Response;

class DebugResponse extends Response
{
    public function withJson($data, $status = null, $encodingOptions = 0)
    {
        $data['debug'] = Debugbar::getData();

        return parent::withJson($data, $status, $encodingOptions);
    }
}