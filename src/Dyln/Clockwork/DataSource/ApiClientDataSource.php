<?php

namespace Dyln\Clockwork\DataSource;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;

class ApiClientDataSource extends DataSource
{

    public function resolve(Request $request)
    {
        $request->userData[] = [
            'name' => 'API REQUEST',
        ];
        return $request;
    }
}
