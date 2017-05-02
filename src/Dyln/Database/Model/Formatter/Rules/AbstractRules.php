<?php

namespace Dyln\Model\Formatter\Rules;

use Dyln\Model\ModelInterface;

abstract class AbstractRules implements Rules
{
    /** @var  ModelInterface */
    protected $model;
    protected $rules = [];
    protected $extra = [];

    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setExtra($extra = [])
    {
        $this->extra = $extra;
    }

    public function getExtra()
    {
        return $this->extra;
    }
}