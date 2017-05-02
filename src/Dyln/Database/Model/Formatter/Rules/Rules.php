<?php

namespace Dyln\Model\Formatter\Rules;

use Dyln\Model\ModelInterface;

interface Rules
{
    public function __construct(ModelInterface $model);

    public function getRules();

    public function getModel();
}