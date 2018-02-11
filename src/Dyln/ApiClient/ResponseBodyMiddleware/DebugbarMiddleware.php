<?php

namespace Dyln\ApiClient\ResponseBodyMiddleware;

use Dyln\Debugbar\Debugbar;
use Dyln\Util\ArrayUtil;

class DebugbarMiddleware implements ResponseBodyMiddlewareInterface
{
    public function execute($body)
    {
        if (!$body) {
            return $body;
        }
        $debugInfo = ArrayUtil::getIn($body, ['debug'], []);
        if ($debugInfo) {
            unset($body['debug']);
        }
        Debugbar::addBulk($debugInfo);

        return $body;
    }
}
