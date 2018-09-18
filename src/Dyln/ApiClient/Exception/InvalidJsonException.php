<?php

namespace Dyln\ApiClient\Exception;

class InvalidJsonException extends \Exception
{
    protected $jsonString;

    /**
     * @return mixed
     */
    public function getJsonString()
    {
        return $this->jsonString;
    }

    /**
     * @param mixed $jsonString
     */
    public function setJsonString($jsonString)
    {
        $this->jsonString = $jsonString;
    }
}
