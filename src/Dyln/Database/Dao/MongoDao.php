<?php

namespace Dyln\Database\Dao;

use Dyln\Database\Model\ModelInterface;
use MongoDB\Database;
use MongoDB\Driver\WriteConcern;

/**
 * Class MongoDao
 * @package Dyln\DaoInterface
 * @method Database getDbAdapter()
 */
class MongoDao extends AbstractDao
{
    /**
     * @param $id
     * @param array $fields
     * @return bool|array
     * @throws \Exception
     */
    public function fetch($id, $fields = [])
    {
        $condition = [
            $this->idFieldName => $id,
        ];
        $cursor = $this->fetchBy($condition, $fields, 1, 0);
        $result = $cursor->toArray();
        if (count($result) === 0) {
            return false;
        }

        return $result[0];
    }

    /**
     * @param array $condition
     * @param array $fields
     * @param null $limit
     * @param null $skip
     * @param null $sort
     * @return \MongoDB\Driver\Cursor
     * @throws \Exception
     */
    public function fetchBy($condition = [], $fields = [], $limit = null, $skip = null, $sort = null)
    {
        $options = [
            'projection' => $fields,
        ];
        if ($limit && is_numeric($limit)) {
            $options['limit'] = $limit;
        }
        if (is_numeric($skip)) {
            $options['skip'] = $skip;
        }
        if (is_array($sort) && !empty($sort)) {
            $options['sort'] = $sort;
        }
        $cursor = $this->getDbAdapter()->selectCollection($this->tableName)->find($condition, $options);

        return $cursor;
    }

    /**
     * @param ModelInterface $model
     * @param array $options
     * @return ModelInterface
     */
    public function save(ModelInterface $model, $options = [])
    {
        /**
         * Logic:
         * We assume that if $this->getIdFieldName() is set it can only be an update. This is incorrect theoretically, but works in practice.
         * If I want to insert a document with a specific id, this logic would stop me. Luckily it's rare.
         * To get past this limitation, we can set an attribute in the options array, which gets stripped here.
         * This parameter is 'forceInsert'
         *
         * This logic is also implemented in \Dyln\Service\AbstractService::save
         */
        $forceInsert = false;
        if (isset($options['forceInsert'])) {
            $forceInsert = $options['forceInsert'];
            unset($options['forceInsert']);
        }
        if (!isset($options['w'])) {
            $options['writeConcern'] = new WriteConcern(1);
        }
        $model->preSave();
        $id = $model->getId();
        if ($id && !$forceInsert) {
            return $this->update($model, $options);
        } else {
            $data = $model->getChanges();
            $data['upt'] = microtime(true);
            if (empty($data[$this->getIdFieldName()])) {
                unset($data[$this->getIdFieldName()]);
            }
            $result = $this->getDbAdapter()->selectCollection($this->getTableName())->insertOne($data, $options);
            $model->setProperty($this->getIdFieldName(), $result->getInsertedId());
            $model->commitChanges();

            return $model;
        }
    }

    /**
     * @param ModelInterface $model
     * @param array $options
     * @return ModelInterface
     */
    public function update(ModelInterface $model, $options = [])
    {
        if (!isset($options['w'])) {
            $options['writeConcern'] = new WriteConcern(1);
        }
        $model->preUpdate();
        $data = $model->getChanges();
        if (isset($data[$this->getIdFieldName()]) && $data[$this->getIdFieldName()] == $model->getProperty($this->getIdFieldName())) {
            unset($data[$this->getIdFieldName()]);
        }
        if (!empty($data)) {
            $data['upt'] = microtime(true);
            $condition = [$this->getIdFieldName() => $model->getProperty($this->getIdFieldName())];
            $operation = ['$set' => $data];
            $this->getDbAdapter()->selectCollection($this->getTableName())->updateOne($condition, $operation, $options);
        }
        $model->commitChanges();

        return $model;
    }

    /**
     * @param array $condition
     * @param array $options
     * @return int
     */
    public function count($condition = [], $options = [])
    {
        $result = $this->getDbAdapter()->selectCollection($this->getTableName())->count($condition, $options);

        return $result;
    }

    /**
     * @param $id
     * @param array $options
     * @return \MongoDB\DeleteResult
     */
    public function delete($id, $options = [])
    {
        if (!isset($options['w'])) {
            $options['writeConcern'] = new WriteConcern(1);
        }
        $condition = [$this->getIdFieldName() => $id];
        $result = $this->getDbAdapter()->selectCollection($this->getTableName())->deleteOne($condition, $options);

        return $result;
    }

    /**
     * @param $condition
     * @param array $options
     * @return \MongoDB\DeleteResult
     */
    public function deleteBy($condition, $options = [])
    {
        if (!isset($options['w'])) {
            $options['writeConcern'] = new WriteConcern(1);
        }
        $options['multi'] = true;
        $result = $this->getDbAdapter()->selectCollection($this->getTableName())->deleteMany($condition, $options);

        return $result;
    }
}
