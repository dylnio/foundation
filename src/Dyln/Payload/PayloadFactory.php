<?php

namespace Dyln\Payload;

class PayloadFactory implements PayloadFactoryInterface
{
    static public function createSuccessPayload(array $data = [])
    {
        return new Payload($data, false, []);
    }

    static public function createErrorPayload(array $messages = [])

    {
        return new Payload([], true, $messages);
    }

    static public function createFromArray($array = [])
    {
        $payload = new Payload();
        $payload->fromArray($array);

        return $payload;
    }
}