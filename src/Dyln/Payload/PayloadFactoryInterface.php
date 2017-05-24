<?php

namespace Dyln\Payload;

interface PayloadFactoryInterface
{
    static public function createSuccessPayload(array $data = []);

    static public function createErrorPayload(array $messages = []);

    static public function createFromArray($array = []);
}