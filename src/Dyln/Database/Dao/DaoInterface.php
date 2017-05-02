<?php

namespace Dyln\Dao;

use Dyln\Model\ModelInterface;

interface DaoInterface
{
    public function fetch($id, $fields = []);

    public function fetchBy($condition = [], $fields = [], $limit = null, $skip = null, $sort = null);

    public function save(ModelInterface $model, $options = []);

    public function update(ModelInterface $model, $options = []);

    public function count($condition = []);

    public function delete($id);

    public function deleteBy($condition);

    public function getDbAdapter();

    public function getTableName();
}