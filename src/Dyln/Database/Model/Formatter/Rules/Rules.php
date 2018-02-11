<?php

namespace Dyln\Database\Model\Formatter\Rules;

use Dyln\Database\Model\ModelInterface;

interface Rules
{
    public function __construct(ModelInterface $model);

    public function getRules();

    public function getModel();
}
