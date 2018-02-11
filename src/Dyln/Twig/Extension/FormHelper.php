<?php

namespace Dyln\Twig\Extension;

use Dyln\Session\Session;

class FormHelper extends \Twig_Extension
{
    /** @var  Session */
    protected $session;
    /** @var  \Dyln\Form\FormHelper */
    protected $helper;

    /**
     * FormHelper constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getName()
    {
        return 'formhelper';
    }

    public function getFunctions()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return [
            new \Twig_SimpleFunction('getFormFieldValue', [$this, 'getValue']),
            new \Twig_SimpleFunction('getFormFieldError', [$this, 'getError']),
            new \Twig_SimpleFunction('isFormValid', [$this, 'isValid']),
            new \Twig_SimpleFunction('getFormValues', [$this, 'getValues']),
        ];
    }

    private function getHelper($formname)
    {
        if (!$this->helper) {
            $this->helper = $this->session->getSegment('form')->get($formname, null, true);
        }

        return $this->helper;
    }

    public function getValue($field, $default = null, $formname = 'form')
    {
        $formHelper = $this->getHelper($formname);
        if ($formHelper) {
            return $formHelper->getValue($field, $default);
        }

        return $default;
    }

    public function getValues($default = [], $formname = 'form')
    {
        $formHelper = $this->getHelper($formname);
        if ($formHelper) {
            return $formHelper->getValues();
        }

        return $default;
    }

    public function getError($field, $formname = 'form')
    {
        $formHelper = $this->getHelper($formname);
        if ($formHelper) {
            return $formHelper->getError($field);
        }

        return null;
    }

    public function isValid($formname = 'form')
    {
        $formHelper = $this->getHelper($formname);
        if ($formHelper) {
            return $formHelper->isValid();
        }

        return false;
    }
}
