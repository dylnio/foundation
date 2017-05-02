<?php

namespace Dyln\Model;

interface ModelInterface
{
    public function getId();

    public function setProperty($fieldName, $value);

    public function getProperty($fieldName, $default = null);

    public function getChanges();

    public function commitChanges();

    public function populateWithArray($data = [], $dirty = false);

    public function preSave();

    public function hasProperty($property);
}