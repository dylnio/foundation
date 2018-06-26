<?php

namespace Dyln\Database\Model;

abstract class AbstractModel implements ModelInterface
{
    protected $idField = '_id';
    protected $availableProperties = [];
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
        if (isset($config['data'])) {
            $this->populateWithArray($config['data'], $config['dirty']);
        }
    }

    public function setIdFieldName($idFieldName)
    {
        $this->idField = $idFieldName;
    }

    public function populateWithArray($data = [], $dirty = false)
    {
        unset($data['__meta__']);
        if ($dirty) {
            foreach ($data as $field => $value) {
                if (substr($field, 0, 2) == '__') {
                    $this->addTempData($field, $value);
                } else {
                    $this->setProperty($field, $value);
                }
            }
        } else {
            foreach ($data as $field => $value) {
                if (substr($field, 0, 2) == '__') {
                    $this->addTempData($field, $value);
                } else {
                    $this->data[$field] = $value;
                }
            }
        }

        return $this;
    }

    public function setProperty($fieldName, $value = null, $force = false)
    {
        if ($force) {
            $this->dirty[$fieldName] = $value;

            return $this;
        }
        $compare = serialize($this->getProperty($fieldName));
        $compareTo = serialize($value);
        if ($compare !== $compareTo) {
            $this->dirty[$fieldName] = $value;
        }

        return $this;
    }

    public function getProperty($fieldName, $default = null)
    {
        if (array_key_exists($fieldName, $this->dirty)) {
            return $this->dirty[$fieldName];
        }
        if (array_key_exists($fieldName, $this->data)) {
            return $this->data[$fieldName];
        }
        if ($default instanceof \Closure) {
            return $default();
        }

        return $default;
    }

    public function hasProperty($fieldName)
    {
        if (array_key_exists($fieldName, $this->dirty)) {
            return true;
        }
        if (array_key_exists($fieldName, $this->data)) {
            return true;
        }

        return false;
    }

    public static function fromArray(array $data = [], $dirty = false)
    {
        return new static([
            'dirty' => $dirty,
            'data'  => $data,
        ]);
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

    public function isModified()
    {
        return !empty($this->dirty);
    }

    public function toArray($includeTemp = true, $secure = true)
    {
        $merged = array_merge_recursive($this->data, $this->dirty);
        if ($includeTemp) {
            $merged = array_merge_recursive($merged, $this->temp);
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

    public function getClassName()
    {
        return get_class($this);
    }

    public function commitChanges()
    {
        foreach ($this->getChanges() as $field => $value) {
            $this->data[$field] = $value;
        }
        $this->dirty = [];
    }

    public function getChanges()
    {
        $changes = $this->dirty;
        foreach ($changes as $field => $value) {
            $changes[$field] = $value;
        }
        $changes[$this->idField] = $this->getId();

        return $changes;
    }

    public function isFieldChanged($field)
    {
        return array_key_exists($field, $this->dirty);
    }

    public function getId($asString = false)
    {
        $id = $this->getProperty($this->idField);
        if ($asString) {
            $id = (string) $id;
        }

        return $id;
    }

    public function getCreationTime()
    {
        return $this->getProperty('CreatedTime');
    }

    public function preSave()
    {
    }

    public function preUpdate()
    {
    }

    public function __set($name, $value)
    {
        $this->setProperty($name, $value);
    }

    public function makeAllDirty()
    {
        $this->dirty = $this->data;

        return $this;
    }

    public function with($field, $value)
    {
        return $this->setProperty($field, $value);
    }
}
