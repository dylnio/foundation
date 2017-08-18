<?php

namespace Dyln\Database\Repository;

use Doctrine\Common\Cache\CacheProvider;
use Dyln\Collection\Collection;
use Dyln\Database\Dao\DaoInterface;
use Dyln\Database\Model\ModelInterface;
use Dyln\Doctrine\Common\Cache\CollectionCache;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;

abstract class AbstractRepository implements RepositoryInterface
{
    /** @var  DaoInterface[] */
    protected $daos = [];
    protected $entityClassName;
    /** @var CacheProvider */
    protected $cache;

    private function __construct(array $daos, $entityClassName, $cache = null)
    {
        $aliases = [];
        foreach ($daos as $key => $dao) {
            if (is_string($dao)) {
                $aliases[$key] = $dao;
            } else {
                $this->daos[$key] = $dao;
            }
        }
        foreach ($aliases as $key => $target) {
            $this->daos[$key] = $this->daos[$target];
        }
        $this->entityClassName = $entityClassName;
        $this->cache = ($cache) ?: new CollectionCache();
    }

    static public function factory($daos, $entityClassName)
    {
        if (!is_array($daos)) {
            $daos = [
                'default' => $daos,
            ];
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

    public function fetch($id, $fields = [], $daoKey = 'default')
    {
        $condition = ['_id' => $id];

        return $this->fetchBy($condition, $fields, 1, 0, null, $daoKey)->first();
    }

    public function getById($id, $fields = [], $daoKey = 'default')
    {
        return $this->fetch($id, $fields, $daoKey);
    }

    public function fetchBy($condition = [], $fields = [], $limit = null, $skip = null, $sort = null, $daoKey = 'default')
    {
        $key = json_encode(func_get_args());
        $key = md5($key);
        if ($this->isInCache($key)) {
            return $this->getFromCache($key);
        }
        /** @var Cursor $cursor */
        $cursor = $this->getDao($daoKey)->fetchBy($condition, $fields, $limit, $skip, $sort);

        $result = $this->hydrateCursor($cursor);
        $this->saveToCache($key, $result);

        return $result;
    }

    public function fetchOneBy($condition = [], $fields = [], $daoKey = 'default')
    {
        return $this->fetchBy($condition, $fields, 1, 0, null, $daoKey)->first();
    }

    public function count($condition = [], $daoKey = 'default')
    {
        return (int)$this->getDao($daoKey)->count($condition);
    }

    public function hydrate($data)
    {
        $model = new $this->entityClassName([
            'dirty' => false,
            'data'  => $data,
        ]);

        return $model;
    }

    public function hydrateCursor($cursor = null)
    {
        $collection = new Collection();
        if (!$cursor) {
            return $collection;
        }
        foreach ($cursor as $row) {
            if ($row instanceof BSONDocument) {
                $row = $row->getArrayCopy();
            }
            $collection->add($this->hydrate($row));
        }

        return $collection;
    }

    public function save(ModelInterface $model, $options = [], $daoKey = 'default')
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
            $model = $this->getDao($daoKey)->update($model, $options);
        } else {
            $model = $this->getDao($daoKey)->save($model, $options);
        }

        return $model;
    }

    public function fetchByMultiId($ids = [], $fields = [], $daoKey = 'default')
    {
        $collection = new Collection();
        if (empty($ids)) {
            return $collection;
        }
        $ids = Collection::create($ids)->trim()->filter()->map(function ($id) {
            return new ObjectID($id);
        })->toArrayValues();
        $cursor = $this->getDao($daoKey)->fetchBy(['_id' => ['$in' => $ids]], $fields);

        return $this->hydrateCursor($cursor);
    }

    public function delete($id, $daoKey = 'default')
    {
        $this->getDao($daoKey)->delete($id);
    }

    public function deleteByCondition($condition = [], $daoKey = 'default')
    {
        if (!$condition) {
            throw new \Exception('Empty condition is not allowed');
        }
        $this->getDao($daoKey)->deleteBy($condition);
    }

    private function isInCache($key)
    {
        if ($this->cache) {
            return $this->cache->contains($key);
        }

        return false;
    }

    private function getFromCache($key)
    {
        if ($this->cache) {
            return $this->cache->fetch($key);
        }

        return null;
    }

    private function saveToCache($key, $data)
    {
        if ($this->cache) {
            $this->cache->save($key, $data);
        }
    }
}
