<?php

namespace Dyln\ApiClient\ResponseBodyMiddleware;

use Dyln\Message\MessageFactory;
use Dyln\Util\ArrayUtil;

class ConvertToMessageMiddleware implements ResponseBodyMiddlewareInterface
{
    public function execute($body)
    {
        if (!$body) {
            return MessageFactory::error(['message' => 'Empty Response Body']);
        }
        $success = ArrayUtil::getIn($body, ['success'], true);
        if ($success) {
            return MessageFactory::success($body);
        }
        $message = ArrayUtil::getIn($body, 'message');
        $code = ArrayUtil::getIn($body, 'code');
        $extra = ArrayUtil::getIn($body, 'extra', []);

        return MessageFactory::error(['message' => $message, 'code' => $code, 'extra' => $extra]);
    }
}
