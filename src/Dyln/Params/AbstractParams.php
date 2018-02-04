<?php

namespace Dyln\Params;

use Dyln\Message\Message;
use Dyln\Message\MessageFactory;
use Dyln\Util\ArrayUtil;
use Dyln\Validator\Validator;

abstract class AbstractParams implements Params
{
    protected $params = [];
    protected $validators = [];
    protected $filters = [];
    protected $defaultFilters = [];
    /** @var  Message */
    protected $validation;

    public function __construct($params = [])
    {
        $params = (array) $params;
        $this->params = $params;
        $this->init();
        $this->setDefaultFilters();
        $this->filter();
        $this->validate();
    }

    protected function init()
    {
        $this->validators = [];
        $this->filters = [];
    }

    private function setDefaultFilters()
    {
        $this->defaultFilters = [
            function ($value) {
                if (is_string($value)) {
                    return trim($value);
                }

                return $value;
            },
        ];
    }

    private function filter()
    {
        foreach ($this->params as $field => $value) {
            foreach ($this->defaultFilters as $filter) {
                $this->params[$field] = $filter($value);
            }
        }
        foreach ($this->filters as $field => $filters) {
            if ($this->has($field)) {
                if (!is_array($filters)) {
                    $filters = [$filters];
                }
                foreach ($filters as $filter) {
                    $this->params[$field] = $filter($this->get($field));
                }
            }
        }
    }

    private function validate()
    {
        foreach ($this->validators as $field => $validators) {
            $value = $this->get($field);
            if (!is_array($validators)) {
                $validators = [$validators];
            }
            foreach ($validators as $validator) {
                if ($validator instanceof Validator) {
                    /** @var Message $result */
                    $result = $validator->isValid($value);
                } else if (is_callable($validator)) {
                    /** @var Message $result */
                    $result = $validator($value);
                } else {
                    $result = MessageFactory::error(['message' => 'Invalid Validator']);
                }
                if ($result->isError()) {
                    $this->validation = MessageFactory::error(['message' => $result->getErrorMessage(), 'extra' => ['field' => $field]]);

                    return;
                }
            }
        }
        $this->validation = MessageFactory::success();

        return;
    }

    public function get($field, $default = null)
    {
        return ArrayUtil::getIn($this->params, $field, $default);
    }

    public function set($field, $value)
    {
        $this->params[$field] = $value;
    }

    public function withParam($name, $value)
    {
        $clone = clone $this;
        $clone->params[$name] = $value;
        $clone->filter();
        $clone->validate();

        return $clone;
    }

    public function withParams($params = [])
    {
        $clone = clone $this;
        $clone->params = $params;
        $clone->filter();
        $clone->validate();

        return $clone;
    }

    public function isValid()
    {
        return !$this->getValidationResult()->isError();
    }

    public function getValidationResult()
    {
        return $this->validation;
    }

    public function getAllParams()
    {
        return $this->params;
    }

    public function has($field)
    {
        return array_key_exists($field, $this->params);
    }

    public function remove($field)
    {
        unset($this->params[$field]);

        return $this;
    }

    public function isEmpty()
    {
        return count($this->params) == 0;
    }

    public function isFieldSet($field)
    {
        return isset($this->params[$field]);
    }

    public function isFieldEmpty($field)
    {
        return empty($this->params[$field]);
    }
}
