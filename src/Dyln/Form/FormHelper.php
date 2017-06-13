<?php

namespace Dyln\Form;

use Dyln\Session\Session;
use Dyln\Util\ArrayUtil;

class FormHelper
{
    protected $values = [];
    protected $errors = [];
    /**
     * @var Session
     */
    protected $session;
    protected $id;

    /**
     * FormHelper constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->id = uniqid();
    }


    public function setValues($values = [])
    {
        $this->values = $values;

        return $this;
    }

    public function setError($field, $error)
    {
        $this->errors[$field] = $error;
    }

    public function setErrors($errors = [])
    {
        $this->errors = $errors;

        return $this;
    }

    public function getValue($field, $default = null)
    {
        return ArrayUtil::getIn($this->values, [$field], $default);
    }

    public function getError($field)
    {
        return ArrayUtil::getIn($this->errors, [$field], null);
    }

    public function hasError($field)
    {
        return isset($this->errors[$field]);
    }

    public function isValid()
    {
        return count($this->errors) === 0;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function save($name = 'form')
    {
        $this->session->getSegment('form')->set($name, $this);
    }
}