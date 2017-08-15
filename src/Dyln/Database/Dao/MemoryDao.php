<?php

namespace Dyln\Database\Dao;

use Dyln\Collection\Collection;
use Dyln\Database\Model\ModelInterface;

/**
 * Class MemoryDao
 * @package Dyln\DaoInterface
 * @method array getDbAdapter()
 */
class MemoryDao extends AbstractDao
{
    protected $mockMethods = [];
    protected $repo = [];

    private function addToRepo($row)
    {
        $this->repo[$row[$this->getIdFieldName()]] = $row;
    }

    public function fetchBy($condition = [], $fields = [], $limit = null, $skip = null, $sort = null)
    {
        $collection = new Collection();
        $buffer = $this->applyCondition($condition);
        if (!empty($buffer)) {
            if ($sort) {
                // not implemented
            }
            if ($limit) {
                if (!$skip) {
                    $skip = 0;
                }
                $buffer = array_slice($buffer, $skip, $limit);
            }
            foreach ($buffer as $id => $row) {
                $collection->add($row, $id);
            }
        }

        return $collection;
    }

    public function fetch($id, $fields = [])
    {
        if (isset($this->repo[$id])) {
            return $this->repo[$id];
        }

        return null;
    }

    public function count($condition = [])
    {
        $buffer = $this->applyCondition($condition);

        return count($buffer);
    }

    public function save(ModelInterface $model, $options = [])
    {
        $id = uniqid();
        $data = $model->getChanges();
        $data[$this->getIdFieldName()] = $id;
        $this->addToRepo($data);
        $model->commitChanges();
        $model->populateWithArray($data);

        return $model;
    }

    public function update(ModelInterface $model, $options = [])
    {
        $data = $model->getChanges();
        $data[$this->getIdFieldName()] = $model->getId();
        $this->addToRepo($data);
        $model->commitChanges();

        return $model;
    }

    public function delete($id)
    {
        unset($this->repo[$id]);
    }

    public function deleteBy($condition)
    {
        $buffer = $this->applyCondition($condition);
        foreach ($buffer as $id => $value) {
            unset($this->repo[$id]);
        }
    }

    private function applyCondition($condition)
    {
        $buffer = [];
        /** @var ModelInterface $row */
        foreach ($this->repo as $row) {
            $result = true;
            foreach ($condition as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $op => $co) {
                        switch ($op) {
                            case '$gte' :
                                if (isset($row[$key]) && $row[$key] >= $co) {
                                    $result = $result && true;
                                } else {
                                    $result = $result && false;
                                }
                                break;
                            case '$gt':
                                if (isset($row[$key]) && $row[$key] > $co) {
                                    $result = $result && true;
                                } else {
                                    $result = $result && false;
                                }
                                break;
                            case '$lte':
                                if (isset($row[$key]) && $row[$key] <= $co) {
                                    $result = $result && true;
                                } else {
                                    $result = $result && false;
                                }
                                break;
                            case '$lt':
                                if (isset($row[$key]) && $row[$key] < $co) {
                                    $result = $result && true;
                                } else {
                                    $result = $result && false;
                                }
                                break;
                            case '$in':
                                // not tested yet
                                if (isset($row[$key])) {
                                    if (!is_array($row[$key])) {
                                        if (in_array($row[$key], $co)) {
                                            $result = $result && true;
                                        } else {
                                            $result = $result && false;
                                        }
                                    } else {
                                        $val = $row[$key];
                                        foreach ($co as $c) {
                                            if (in_array($c, $val)) {
                                                $result = $result && true;
                                            } else {
                                                $result = $result && false;
                                            }
                                        }
                                    }
                                } else {
                                    $result = $result && false;
                                }
                                break;
                        }
                    }
                } else {
                    if (isset($row[$key])) {
                        if (is_array($row[$key])) {
                            $result = in_array($value, $row[$key]);
                        } else {
                            $result = $row[$key] == $value;
                        }
                        $result = $result && true;
                    } else {
                        $result = $result && false;
                    }
                }
            }
            if ($result) {
                $buffer[$row[$this->getIdFieldName()]] = $row;
            }
        }

        return $buffer;
    }
}