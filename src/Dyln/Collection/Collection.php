<?php

namespace Dyln\Collection;

use Dyln\Util\ArrayUtil;

class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @var array|null
     */
    protected $data = [];

    /**
     * @param array|null $items
     * @return Collection
     */
    static public function create($items = null)
    {
        return new self($items);
    }

    /**
     * @param null|array $items
     */
    public function __construct($items = null)
    {
        if ($items !== null) {
            if (is_array($items)) {
                $this->data = $items;
            } else if ($items instanceof Collection) {
                $this->data = $items->toArray();
            }
        }
    }

    /**
     * @param $item
     * @param $key
     */
    public function add($item, $key = null)
    {
        if (!is_null($key)) {
            $this->data[$key] = $item;
        } else {
            $this->data[] = $item;
        }
    }

    /**
     * @param $key
     */
    public function remove($key)
    {
        if (is_callable($key)) {
            foreach ($this->data as $k => $v) {
                if ($key($v) === true) {
                    unset($this->data[$k]);
                }
            }
        } else {
            if ($this->exists($key)) {
                unset($this->data[$key]);
            }
        }
    }

    /**
     * @return bool|mixed
     */
    public function first()
    {
        if ($this->count() > 0) {
            $data = array_values($this->data);

            return array_shift($data);
        }

        return null;
    }

    /**
     * @return bool|mixed
     */
    public function last()
    {
        if ($this->count() > 0) {
            $data = array_values($this->data);

            return array_pop($data);
        }

        return null;
    }

    public function isLast($item)
    {
        $arrayKeys = array_keys($this->data);
        if (array_search($item->Label, $arrayKeys) == $this->count() - 1) {
            return true;
        }

        return false;
    }

    public function getNth($n)
    {
        $data = array_values($this->data);

        return $data[$n];
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing Iterator or
     *       Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function merge(Collection $collection)
    {
        foreach ($collection as $key => $value) {
            $this->add($value, $key);
        }

        return $this;
    }

    public function unique()
    {
        return new self(array_values(array_unique($this->data)));
    }


    public function removeLast()
    {
        array_pop($this->data);
    }

    /**
     * @param callable|null $callback
     * @return Collection
     */
    public function filter(callable $callback = null): Collection
    {
        if ($callback) {
            $return = [];
            foreach ($this->data as $key => $value) {
                if ($callback($value, $key)) {
                    $return[$key] = $value;
                }
            }

            return new static($return);
        }

        return new static(array_filter($this->data));
    }

    public function find(callable $callback = null)
    {
        if ($callback) {
            foreach ($this->data as $key => $value) {
                if ($callback($value, $key)) {
                    return $value;
                }
            }

            return null;
        }

        return null;
    }

    public function findKey(callable $callback = null)
    {
        if ($callback) {
            foreach ($this->data as $key => $value) {
                if ($callback($value, $key)) {
                    return $key;
                }
            }

            return null;
        }

        return null;
    }

    public function map(callable $callback = null): Collection
    {
        $return = [];
        if ($callback) {
            foreach ($this->data as $key => $value) {
                $return[$key] = $callback($value, $key);
            }
        } else {
            $return = $this->data;
        }

        return new static($return);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function sortAsc($key)
    {
        $data = $this->data;
        usort($data, function ($a, $b) use ($key) {
            $valA = ArrayUtil::getIn($a, $key);
            $valB = ArrayUtil::getIn($b, $key);
            if ($valA == $valB) return 0;

            return $valA > $valB ? -1 : 1;
        });

        return new static($data);
    }

    public function sortDesc($key)
    {
        $data = $this->data;
        usort($data, function ($a, $b) use ($key) {
            $valA = ArrayUtil::getIn($a, $key);
            $valB = ArrayUtil::getIn($b, $key);
            if ($valA == $valB) return 0;

            return $valA > $valB ? 1 : -1;
        });

        return new static($data);
    }

    public function sort(callable $callable)
    {
        $data = $this->data;
        usort($data, $callable);

        return new static($data);
    }

    public function group($key)
    {
        $grouped = [];
        foreach ($this->data as $item) {
            $val = ArrayUtil::getIn($item, $key);
            if (isset($grouped[$val])) {
                $grouped[$val][] = $item;
            } else {
                $grouped[$val] = [$item];
            }
        }

        return self::create($grouped);
    }

    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->data, $callback, $initial);
    }

    public function toArrayValues()
    {
        return array_values($this->toArray());
    }

    public function trim()
    {
        $data = [];
        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }
            $data[$key] = $value;
        }

        return self::create($data);
    }

    public function some(callable $callback): bool
    {
        foreach ($this->data as $key => $value) {
            if ($callback($value, $key)) {
                return true;
            }
        }

        return false;
    }
}
