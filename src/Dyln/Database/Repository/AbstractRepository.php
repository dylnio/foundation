<?php

namespace Dyln\Repository;

use Dyln\Collection\Collection;
use Dyln\Dao\DaoInterface;
use Dyln\Model\ModelInterface;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;

abstract class AbstractRepository implements RepositoryInterface
{
    /** @var  DaoInterface[] */
    protected $daos = [];
    protected $entityClassName;

    private function __construct(array $daos, $entityClassName)
    {
        foreach ($daos as $key => $dao) {
            $this->daos[$key] = $dao;
        }
        $this->entityClassName = $entityClassName;
    }

    static public function factory($daos, $entityClassName)
    {
        if (!is_array($daos)) {
            $daos = ['default' => $daos];
        }

        return new static($daos, $entityClassName);
    }

    public function getDao($key = 'default')
    {
        return $this->daos[$key];
    }

    public function addDao($key, DaoInterface $dao)
    {
        $this->daos[$key] = $dao;
    }

    public function fetch($id, $fields = [])
    {
        /** @var BSONDocument $result */
        $result = $this->getDao()->fetch($id, $fields);
        if ($result) {
            if ($result instanceof BSONDocument) {
                $result = $result->getArrayCopy();
            }

            return $this->hydrate($result);
        }

        return null;
    }

    public function getById($id, $fields = [])
    {
        return $this->fetch($id, $fields);
    }

    public function fetchBy($condition = [], $fields = [], $limit = null, $skip = null, $sort = null)
    {
        $collection = new Collection();
        /** @var Cursor $result */
        $result = $this->getDao()->fetchBy($condition, $fields, $limit, $skip, $sort);
        if ($result) {
            foreach ($result as $row) {
                if ($row instanceof BSONDocument) {
                    $row = $row->getArrayCopy();
                }
                $collection->add($this->hydrate($row));
            }
        }

        return $collection;
    }

    public function fetchOneBy($condition = [], $fields = [])
    {
        /** @var Cursor $result */
        $result = $this->getDao()->fetchBy($condition, $fields, 1, 0, null);
        $result = iterator_to_array($result);
        if (!empty($result)) {
            /** @var BSONDocument $row */
            $row = array_shift($result);
            if ($row instanceof BSONDocument) {
                $row = $row->getArrayCopy();
            }

            return $this->hydrate($row);
        }

        return null;
    }

    public function count($condition = [])
    {
        return (int)$this->getDao()->count($condition);
    }

    public function hydrate($data)
    {
        $model = new $this->entityClassName([
            'dirty' => false,
            'data'  => $data,
        ]);

        return $model;
    }

    public function hydrateCursor($cursor)
    {
        $collection = new Collection();
        foreach ($cursor as $row) {
            $collection->add($this->hydrate($row));
        }

        return $collection;
    }

    public function save(ModelInterface $model, $options = [])
    {
        /**
         * Logic:
         * We assume that if _id is set it can only be an update. This is incorrect theoretically, but works in practice.
         * If I want to insert a document with a specific id, this logic would stop me. Luckily it's rare.
         * To get past this limitation, we can set an attribute in the options array, which gets stripped here.
         * This parameter is 'forceInsert'
         *
         * This logic is also implemented in \Dyln\DaoInterface\DaoInterface::save
         */
        $forceInsert = false;
        if (isset($options['forceInsert'])) {
            $forceInsert = $options['forceInsert'];
        }
        if ($model->getId() && !$forceInsert) {
            $model = $this->getDao()->update($model, $options);
        } else {
            $model = $this->getDao()->save($model, $options);
        }

        return $model;
    }

    public function fetchByMultiId($ids = [], $fields = [])
    {
        $collection = new Collection();
        if (empty($ids)) {
            return $collection;
        }
        $ids = array_unique($ids);
        $result = $this->getDao()->fetchBy(['_id' => ['$in' => array_values($ids)]], $fields);
        $data = [];
        foreach ($result as $row) {
            $data[(string)$row["_id"]] = $row;
        }

        foreach ($ids as $id) {
            if (isset($data[(string)$id])) {
                $collection->add($this->hydrate($data[(string)$id]), (string)$id);
            }
        }

        return $collection;
    }

    public function delete($id)
    {
        $this->getDao()->delete($id);
    }

    public function deleteByCondition($condition = [])
    {
        if (!$condition) {
            throw new \Exception('Empty condition is not allowed');
        }
        $this->getDao()->deleteBy($condition);
    }
}
