<?php

namespace Dyln\Database\Model\Formatter;

use Dyln\Database\Model\Formatter\Exception\InvalidFieldException;
use Dyln\Database\Model\Formatter\Rules\Rules;

abstract class AbstractFormatter implements Formatter
{
    /** @var Rules */
    protected $rules;
    protected $requestedFields = [];

    public function __construct(Rules $rules)
    {
        $this->rules = $rules;
    }

    public function format()
    {
        $formatted = [];
        foreach ($this->getRulesForRequestedFields() as $field => $value) {
            if (is_callable($value)) {
                $formatted[$field] = $value();
            } else {
                $formatted[$field] = $value;
            }
        }

        return $formatted;
    }

    public function setRequestedFields($fields = [])
    {
        $this->requestedFields = $fields;
    }

    private function getRulesForRequestedFields()
    {
        $rules = $this->rules->getRules();
        if (empty($this->requestedFields)) {
            return $rules;
        }
        $filtered = [];
        foreach ($this->requestedFields as $field) {
            if (!isset($rules[$field])) {
                throw new InvalidFieldException($field);
            }
            $filtered[$field] = $rules[$field];
        }

        return $filtered;
    }
}
