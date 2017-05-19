<?php

namespace Dyln\Repository;


use Dyln\Dao\DaoInterface;
use Dyln\Model\ModelInterface;

interface RepositoryInterface
{
    public function getDao($key = 'default');

    public function fetch($id, $fields = []);

    public function fetchBy($condition = [], $fields = [], $limit = null, $skip = null, $sort = null);

    public function fetchOneBy($condition = [], $fields = []);

    public function save(ModelInterface $model, $options = []);

    public function count($condition = []);

    public function fetchByMultiId($ids = [], $fields = []);

    public function delete($id);

    public function addDao($key, DaoInterface $dao);

}