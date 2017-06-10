<?php

namespace Dyln\Database\Dao;

use Dyln\Database\Model\ModelInterface;

abstract class AbstractDao implements DaoInterface
{
    protected $dbAdapter = null;
    protected $tableName = null;
    protected $idFieldName = null;

    private function __construct($dbAdapter, $tableName, $idFieldName)
    {
        $this->dbAdapter = $dbAdapter;
        $this->tableName = $tableName;
        $this->idFieldName = $idFieldName;
    }

    static public function factory($dbAdapter, $tableName, $idFieldName)
    {
        $static = new static($dbAdapter, $tableName, $idFieldName);

        return $static;
    }

    public function getDbAdapter()
    {
        return $this->dbAdapter;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getIdFieldName()
    {
        return $this->idFieldName;
    }

    abstract public function fetch($id, $fields = []);

    abstract public function fetchBy($condition = [], $fields = [], $limit = null, $skip = null, $sort = null);

    abstract public function save(ModelInterface $model, $options = []);

    abstract public function update(ModelInterface $model, $options = []);

    abstract public function count($condition = []);

    abstract public function delete($id);

    abstract public function deleteBy($condition);
}