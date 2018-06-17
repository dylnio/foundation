<?php

namespace Dyln\Params;

interface Params
{
    public function set($field, $value);

    public function getAllParams();

    public function get($field, $default = null);

    public function withParam($name, $value);

    public function withParams($params = []);

    public function isValid();

    public function has($field);

    public function remove($field);

    public function isEmpty();

    public function isFieldEmpty($field);
}
