<?php

namespace Dyln\Repository;


use Dyln\Model\ModelInterface;

interface RepositoryInterface
{
    public function getDao();

    public function fetch($id, $fields = []);

    public function fetchBy($condition = [], $fields = [], $limit = null, $skip = null, $sort = null);

    public function fetchOneBy($condition = [], $fields = []);

    public function save(ModelInterface $model, $options = []);

    public function count($condition = []);

    public function fetchByMultiId($ids = [], $fields = []);

    public function delete($id);
}