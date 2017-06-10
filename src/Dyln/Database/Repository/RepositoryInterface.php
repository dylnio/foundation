<?php

namespace Dyln\Database\Repository;


use Dyln\Database\Dao\DaoInterface;
use Dyln\Database\Model\ModelInterface;

interface RepositoryInterface
{
    public function getDao($key = 'default');

    public function fetch($id, $fields = [], $daoKey = 'default');

    public function fetchBy($condition = [], $fields = [], $limit = null, $skip = null, $sort = null, $daoKey = 'default');

    public function fetchOneBy($condition = [], $fields = [], $daoKey = 'default');

    public function save(ModelInterface $model, $options = [], $daoKey = 'default');

    public function count($condition = [], $daoKey = 'default');

    public function fetchByMultiId($ids = [], $fields = [], $daoKey = 'default');

    public function delete($id, $daoKey = 'default');

    public function deleteByCondition($condition = [], $daoKey = 'default');

    public function addDao($key, DaoInterface $dao);

}