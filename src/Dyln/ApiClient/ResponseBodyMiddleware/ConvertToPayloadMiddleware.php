<?php

namespace Dyln\ApiClient\ResponseBodyMiddleware;

use Dyln\Payload\PayloadFactory;
use Dyln\Util\ArrayUtil;

class ConvertToPayloadMiddleware implements ResponseBodyMiddlewareInterface
{
    public function execute($body)
    {
        if (!$body) {
            return PayloadFactory::createErrorPayload([['message' => 'Empty Response Body']]);
        }
        $success = ArrayUtil::getIn($body, ['success'], true);
        if ($success) {
            return PayloadFactory::createSuccessPayload($body);
        }
        $message = ArrayUtil::getIn($body, 'message');
        $code = ArrayUtil::getIn($body, 'code');

        return PayloadFactory::createErrorPayload([['message' => $message, 'code' => $code]]);
    }
}