<?php

namespace Dyln\Slim\Http;

use Dyln\Debugbar\Debugbar;

class DebugResponse extends JsonResponse
{
    public function withJson($data, $status = null, $encodingOptions = 0)
    {
        $data['debug'] = Debugbar::getData();

        return parent::withJson($data, $status, $encodingOptions);
    }
}