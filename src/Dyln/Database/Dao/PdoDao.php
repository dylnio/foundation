<?php

namespace Dyln\Dao;

use Dyln\Collection\Collection;
use Dyln\Model\ModelInterface;

/**
 * Class PdoDao
 * @package Dyln\DaoInterface
 * @method \PDO getDbAdapter()
 */
class PdoDao extends AbstractDao
{
    public function fetch($id, $fields = [])
    {
        $collection = new Collection();
        if (empty($fields)) {
            $fields = ['*' => 1];
        }
        $fields = implode(',', array_keys($fields));
        $stmt = $this->getDbAdapter()->prepare('SELECT ' . $fields . ' FROM ' . $this->tableName . ' WHERE ' . $this->idFieldName . '=:id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($result) {
            foreach ($result as $row) {
                $collection->add($row, $row[$this->getIdFieldName()]);
            }
        }

        return $collection;
    }

    public function fetchBy($condition = [], $fields = [], $limit = null, $skip = null, $sort = null)
    {
        $collection = new Collection();
        if (empty($fields)) {
            $fields = ['*' => 1];
        }
        $fields = implode(',', array_keys($fields));
        if (empty($condition)) {
            $condition = [
                'condition' => '1=1',
                'params'    => [],
            ];
        }
        $sql = 'SELECT ' . $fields . ' FROM ' . $this->tableName . ' WHERE ' . $condition['condition'];
        if ($limit) {
            $sql .= ' LIMIT :limit';
        }
        if ($skip) {
            $sql .= ' OFFSET :skip';
        }
        if (!empty($sort)) {
            $sql .= ' ORDER BY ' . $sort;
        }

        $stmt = $this->getDbAdapter()->prepare($sql);
        if ($limit) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }
        if ($skip) {
            $stmt->bindValue(':skip', $skip, \PDO::PARAM_INT);
        }
        foreach ($condition['params'] as $key => $value) {
            if (!is_array($value)) {
                $value = [$value, \PDO::PARAM_STR];
            }
            $stmt->bindParam($key, $value[0], $value[1]);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($result) {
            foreach ($result as $row) {
                $collection->add($row, $row[$this->getIdFieldName()]);
            }
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
         * This logic is also implemented in \Dyln\Service\AbstractService::save
         */
        $forceInsert = false;
        if (isset($options['forceInsert'])) {
            $forceInsert = $options['forceInsert'];
            unset($options['forceInsert']);
        }

        $id = $model->getId();
        if ($id && !$forceInsert) {
            return $this->update($model, $options);
        } else {
            $data = $model->getChanges();
            if (!empty($data[$this->getIdFieldName()])) {
                unset($data[$this->getIdFieldName()]);
            }
            $columns = array_keys($data);
            $values = [];
            $i = 1;
            foreach ($data as $value) {
                $values[':param' . $i] = $value;
                $i++;
            }
            $params = array_keys($values);
            $sql = 'INSERT INTO ' . $this->tableName . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $params) . ')';
            $stmt = $this->getDbAdapter()->prepare($sql);
            $stmt->execute($values);
            $model->setProperty($this->getIdFieldName(), (int)$this->getDbAdapter()->lastInsertId());
            $model->commitChanges();

            return $model;
        }
    }

    public function update(ModelInterface $model, $options = [])
    {
        $model->setProperty('upt', microtime(true));
        $data = $model->getChanges();
        $condition = $this->getIdFieldName() . '=' . $model->getId();
        $sql = 'UPDATE ' . $this->tableName . ' SET ';
        $values = [];
        $i = 1;
        foreach ($data['set'] as $key => $value) {
            $values[':param' . $i] = $value;
            $sql .= $key . '=' . ':param' . $i . ',';
            $i++;
        }
        foreach ($data['unset'] as $key => $value) {
            $values[':param' . $i] = null;
            $sql .= $key . '=' . ':param' . $i . ',';
            $i++;
        }

        $sql = rtrim($sql, ',');
        $sql .= ' WHERE ' . $condition;
        $stmt = $this->getDbAdapter()->prepare($sql);
        foreach ($values as $name => $value) {
            $stmt->bindValue($name, $value);
        }
        $stmt->execute();

        $model->commitChanges();

        return $model;
    }

    public function count($condition = [])
    {
        if (empty($condition)) {
            $condition = [
                'condition' => '1=1',
                'params'    => [],
            ];
        }
        $stmt = $this->getDbAdapter()->prepare('SELECT COUNT(*) AS c FROM ' . $this->tableName . ' WHERE ' . $condition['condition']);
        foreach ($condition['params'] as $key => $value) {
            if (!is_array($value)) {
                $value = [$value, SQLITE3_TEXT];
            }
            $stmt->bindParam($key, $value[0], $value[1]);
        }
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            $result = ['c' => 0];
        }

        return (int)$result['c'];
    }

    public function delete($id)
    {
        $stmt = $this->getDbAdapter()->prepare('DELETE FROM ' . $this->tableName . ' WHERE ' . $this->idFieldName . '=' . (int)$id);
        $stmt->execute();
    }

    public function deleteBy($condition)
    {
        if (empty($condition)) {
            $condition = [
                'condition' => '1=1',
                'params'    => [],
            ];
        }
        $stmt = $this->getDbAdapter()->prepare('DELETE FROM ' . $this->tableName . ' WHERE ' . $condition['condition']);
        foreach ($condition['params'] as $key => $value) {
            if (!is_array($value)) {
                $value = [$value, SQLITE3_TEXT];
            }
            $stmt->bindParam($key, $value[0], $value[1]);
        }
        $stmt->execute();
    }
}