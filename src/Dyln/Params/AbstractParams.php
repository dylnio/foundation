<?php

namespace Dyln\Params;

use Dyln\Payload\Payload;
use Dyln\Payload\PayloadFactory;
use Dyln\Util\ArrayUtil;
use Dyln\Validator\Validator;

abstract class AbstractParams implements Params
{
    protected $params = [];
    protected $validators = [];
    protected $filters = [];
    protected $defaultFilters = [];
    /** @var  Payload */
    protected $validation;

    public function __construct($params = [])
    {
        $params = (array)$params;
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
            if (isset($this->params[$field])) {
                if (!is_array($filters)) {
                    $filters = [$filters];
                }
                foreach ($filters as $filter) {
                    $this->params[$field] = $filter(ArrayUtil::getIn($this->params, [$field]));
                }
            }
        }
    }

    private function validate()
    {
        foreach ($this->validators as $field => $validators) {
            $value = ArrayUtil::getIn($this->params, [$field]);
            if (!is_array($validators)) {
                $validators = [$validators];
            }
            foreach ($validators as $validator) {
                if ($validator instanceof Validator) {
                    /** @var Payload $result */
                    $result = $validator->isValid($value);
                } elseif (is_callable($validator)) {
                    /** @var Payload $result */
                    $result = $validator($value);
                } else {
                    $result = PayloadFactory::createErrorPayload(['generic' => ['msg' => 'Invalid Validator']]);
                }
                if ($result->isError()) {
                    $msgs = $result->getMessages();
                    $this->validation = PayloadFactory::createErrorPayload([$field => [
                        'msg'  => array_shift($msgs),
                        'code' => null,
                    ]]);

                    return;
                }
            }
        }

        $this->validation = PayloadFactory::createSuccessPayload();

        return;
    }

    public function get($field, $default = null)
    {
        return ArrayUtil::getIn($this->params, [$field], $default);
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
        return $this->getValidationResult()->isSuccess();
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
        return array_key_exists($this->params, $field);
    }

    public function remove($field)
    {
        unset($this->params[$field]);

        return $this;
    }
}