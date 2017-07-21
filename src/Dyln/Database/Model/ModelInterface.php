<?php

namespace Dyln\Database\Model;

interface ModelInterface
{
    public function getId();

    public function setProperty($fieldName, $value);

    public function getProperty($fieldName, $default = null);

    public function getChanges();

    public function commitChanges();

    public function populateWithArray($data = [], $dirty = false);

    public function preSave();

    public function preUpdate();

    public function addTempData($field, $value);

    public function getTempData($field, $default = null);
}