<?php

namespace Dyln\Model;

abstract class AbstractModel implements ModelInterface
{
    protected $idField = '_id';
    protected $availableProperties = [];
    protected $defaults = [];
    protected $data = [];
    protected $dirty = [];
    protected $temp = [];
    protected $secureFields = [];

    public function __construct($config = [])
    {
        $this->availableProperties = array_merge($this->availableProperties, ['upt', '_id']);

        if (!isset($config['dirty'])) {
            $config['dirty'] = false;
        }
        if (isset($config['idFieldName'])) {
            $this->setIdFieldName($config['idFieldName']);
        }
        if (isset($config['availableProperties'])) {
            $this->availableProperties = $config['availableProperties'];
        }
        if (isset($config['defaults'])) {
            $this->defaults = $config['defaults'];
        }
        if (isset($config['data'])) {
            $this->populateWithArray($config['data'], $config['dirty']);
        }
        $this->setDefaults();
    }

    public function addTempData($field, $value)
    {
        $this->temp[$field] = $value;

        return $this;
    }

    public function getTempData($field, $default = null)
    {
        return isset($this->temp[$field]) ? $this->temp[$field] : $default;
    }

    public function setIdFieldName($idFieldName)
    {
        $this->idField = $idFieldName;
    }

    private function setDefaults()
    {
        foreach ($this->defaults as $field => $value) {
            if (!isset($this->data[$field]) && !isset($this->dirty[$field])) {
                $this->setProperty($field, $value);
            }
        }
    }

    public function setProperty($fieldName, $value)
    {
        $compare = $this->getProperty($fieldName);
        $compareTo = $value;
        if (is_object($compare) && method_exists($compare, '__toString')) {
            $compare = (string)$this->getProperty($fieldName);
        }
        if (is_object($compareTo) && method_exists($compareTo, '__toString')) {
            $compareTo = (string)$value;
        }
        if ($compare !== $compareTo) {
            $this->dirty[$fieldName] = $value;
        }

        return $this;
    }

    public function getProperty($fieldName, $default = null)
    {
        if (isset($this->dirty[$fieldName])) {
            return $this->dirty[$fieldName];
        }
        if (isset($this->data[$fieldName])) {
            return $this->data[$fieldName];
        }

        return $default;
    }

    public function populateWithArray($data = [], $dirty = false)
    {
        unset($data['__meta__']);
        if ($dirty) {
            foreach ($data as $field => $value) {
                $this->setProperty($field, $value);
            }
        } else {
            $this->data = array_merge($this->data, $data);
        }

        return $this;
    }

    public function isModified()
    {
        return !empty($this->dirty);
    }

    public function getId($asString = false)
    {
        $id = $this->getProperty($this->idField);
        if ($asString) {
            $id = (string)$id;
        }

        return $id;
    }

    public function isPartial()
    {
        foreach ($this->availableProperties as $fieldName) {
            if (!isset($this->data[$fieldName])) {
                return true;
            }
        }

        return false;
    }

    public function toArray($includeTemp = true, $secure = true)
    {
        $merged = array_merge($this->data, $this->dirty);
        if ($includeTemp) {
            $merged = array_merge($merged, $this->temp);
        }
        foreach ($merged as $key => $field) {
            if (in_array($key, $this->secureFields)) {
                unset($merged[$key]);
            } else {
                if ($field instanceof AbstractModel) {
                    $merged[$key] = $field->toArray($includeTemp, $secure);
                }
            }
        }
        $merged['__meta__'] = [
            '__object_hash__' => spl_object_hash($this),
            '__class__'       => $this->getClassName(),
        ];

        return $merged;
    }

    public function getChanges()
    {
        $changes = $this->dirty;
        foreach ($changes as $field => $value) {
            $changes[$field] = $value;
        }

        return $changes;
    }

    public function commitChanges()
    {
        foreach ($this->getChanges() as $field => $value) {
            $this->data[$field] = $value;
        }
        $this->dirty = [];
    }

    public function getCreationTime()
    {
        return $this->getProperty('CreatedTime');
    }

    public function getClassName()
    {
        return get_class($this);
    }

    public function preSave()
    {

    }

    public function hasProperty($property)
    {
        return in_array($property, $this->availableProperties);
    }

    public function __set($name, $value)
    {
        $this->setProperty($name, $value);
    }
}