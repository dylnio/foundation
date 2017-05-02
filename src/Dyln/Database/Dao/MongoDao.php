<?php

namespace Dyln\Dao;

use Dyln\AppEnv;
use Dyln\Debugbar\Debugbar;
use Dyln\Model\ModelInterface;
use Dyln\Util\Timer;
use MongoDB\BSON\ObjectID;
use MongoDB\Database;

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
     */
    public function fetch($id, $fields = [])
    {
        $condition = [
            $this->idFieldName => $id,
        ];
        $options = [
            'projection' => $fields,
        ];
        Timer::start();
        $result = $this->getDbAdapter()->selectCollection($this->tableName)->findOne($condition, $options);
        $time = Timer::result();
        if (class_exists('Dyln\Debugbar\Debugbar')) {
            if (AppEnv::isDebugEnabled()) {
                $bt = [];
                $traces = debug_backtrace();
                for ($i = 15; $i > 0; $i--) {
                    if (isset($traces[$i])) {
                        $t = $traces[$i];
                        $bt[] = [
                            'file'     => isset($t['file']) ? $t['file'] : false,
                            'line'     => isset($t['line']) ? $t['line'] : false,
                            'function' => isset($t['function']) ? $t['function'] : false,
                        ];
                    }
                }
                Debugbar::add('Mongo', [
                    'command'   => $this->getDbAdapter()->getDatabaseName() . '.' . $this->tableName . '.findOne',
                    'options'   => json_encode($options),
                    'query'     => json_encode($condition),
                    'time'      => $time,
                    'backtrace' => $bt,
                ]);
            }
        }
        if ($result) {
            return $result;
        }

        return false;
    }

    /**
     * @param array $condition
     * @param array $fields
     * @param null $limit
     * @param null $skip
     * @param null $sort
     * @return \MongoDB\Driver\Cursor
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
        Timer::start();
        $cursor = $this->getDbAdapter()->selectCollection($this->tableName)->find($condition, $options);
        $time = Timer::result();
        if (class_exists('Dyln\Debugbar\Debugbar')) {
            if (AppEnv::isDebugEnabled()) {
                $bt = [];
                $traces = debug_backtrace();
                for ($i = 15; $i > 0; $i--) {
                    if (isset($traces[$i])) {
                        $t = $traces[$i];
                        $bt[] = [
                            'file'     => isset($t['file']) ? $t['file'] : false,
                            'line'     => isset($t['line']) ? $t['line'] : false,
                            'function' => isset($t['function']) ? $t['function'] : false,
                        ];
                    }
                }
                Debugbar::add('Mongo', [
                    'command'   => $this->getDbAdapter()->getDatabaseName() . '.' . $this->tableName . '.find',
                    'options'   => json_encode($options),
                    'query'     => json_encode($condition),
                    'time'      => $time,
                    'backtrace' => $bt,
                ]);
            }
        }

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

        if (!isset($options['w'])) {
            $options['w'] = 1;
        }

        $model->preSave();
        $id = $model->getId();
        if ($id && !$forceInsert) {
            return $this->update($model, $options);
        } else {
            $data = $model->getChanges();
            $data['upt'] = microtime(true);
            if (empty($data[$this->getIdFieldName()]) || !$data[$this->getIdFieldName()] instanceof ObjectID) {
                unset($data['_id']);
            }
            Timer::start();
            $result = $this->getDbAdapter()->selectCollection($this->getTableName())->insertOne($data, $options);
            $time = Timer::result();
            if (class_exists('Dyln\Debugbar\Debugbar')) {
                if (AppEnv::isDebugEnabled()) {
                    $bt = [];
                    $traces = debug_backtrace();
                    for ($i = 15; $i > 0; $i--) {
                        if (isset($traces[$i])) {
                            $t = $traces[$i];
                            $bt[] = [
                                'file'     => isset($t['file']) ? $t['file'] : false,
                                'line'     => isset($t['line']) ? $t['line'] : false,
                                'function' => isset($t['function']) ? $t['function'] : false,
                            ];
                        }
                    }
                    Debugbar::add('Mongo', [
                        'command'   => $this->getDbAdapter()->getDatabaseName() . '.' . $this->tableName . '.insertOne',
                        'options'   => json_encode($options),
                        'query'     => json_encode($data),
                        'time'      => $time,
                        'backtrace' => $bt,
                    ]);
                }
            }
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
            $options['w'] = 1;
        }
        $data = $model->getChanges();
        if (!empty($data)) {
            $data['upt'] = microtime(true);
            $condition = [$this->getIdFieldName() => $model->getProperty($this->getIdFieldName())];
            $operation = ['$set' => $data];
            Timer::start();
            $this->getDbAdapter()->selectCollection($this->getTableName())->updateOne($condition, $operation, $options);
            $time = Timer::result();
            if (class_exists('Dyln\Debugbar\Debugbar')) {
                if (AppEnv::isDebugEnabled()) {
                    $bt = [];
                    $traces = debug_backtrace();
                    for ($i = 15; $i > 0; $i--) {
                        if (isset($traces[$i])) {
                            $t = $traces[$i];
                            $bt[] = [
                                'file'     => isset($t['file']) ? $t['file'] : false,
                                'line'     => isset($t['line']) ? $t['line'] : false,
                                'function' => isset($t['function']) ? $t['function'] : false,
                            ];
                        }
                    }
                    Debugbar::add('Mongo', [
                        'command'   => $this->getDbAdapter()->getDatabaseName() . '.' . $this->tableName . '.updateOne',
                        'options'   => json_encode($options),
                        'query'     => json_encode($condition),
                        'operation' => json_encode($operation),
                        'time'      => $time,
                        'backtrace' => $bt,
                    ]);
                }
            }
        }
        $model->commitChanges();

        return $model;
    }

    /**
     * @param array $condition
     * @return int
     */
    public function count($condition = [])
    {
        Timer::start();
        $result = $this->getDbAdapter()->selectCollection($this->getTableName())->count($condition);
        $time = Timer::result();
        if (class_exists('Dyln\Debugbar\Debugbar')) {
            if (AppEnv::isDebugEnabled()) {
                $bt = [];
                $traces = debug_backtrace();
                for ($i = 15; $i > 0; $i--) {
                    if (isset($traces[$i])) {
                        $t = $traces[$i];
                        $bt[] = [
                            'file'     => isset($t['file']) ? $t['file'] : false,
                            'line'     => isset($t['line']) ? $t['line'] : false,
                            'function' => isset($t['function']) ? $t['function'] : false,
                        ];
                    }
                }
                Debugbar::add('Mongo', [
                    'command'   => $this->getDbAdapter()->getDatabaseName() . '.' . $this->tableName . '.count',
                    'options'   => json_encode([]),
                    'query'     => json_encode($condition),
                    'operation' => json_encode([]),
                    'time'      => $time,
                    'backtrace' => $bt,
                ]);
            }
        }

        return $result;
    }

    /**
     * @param $id
     * @return \MongoDB\DeleteResult
     */
    public function delete($id)
    {
        Timer::start();
        $condition = [$this->getIdFieldName() => $id];
        $result = $this->getDbAdapter()->selectCollection($this->getTableName())->deleteOne($condition);
        $time = Timer::result();
        if (class_exists('Dyln\Debugbar\Debugbar')) {
            if (AppEnv::isDebugEnabled()) {
                $bt = [];
                $traces = debug_backtrace();
                for ($i = 15; $i > 0; $i--) {
                    if (isset($traces[$i])) {
                        $t = $traces[$i];
                        $bt[] = [
                            'file'     => isset($t['file']) ? $t['file'] : false,
                            'line'     => isset($t['line']) ? $t['line'] : false,
                            'function' => isset($t['function']) ? $t['function'] : false,
                        ];
                    }
                }
                Debugbar::add('Mongo', [
                    'command'   => $this->getDbAdapter()->getDatabaseName() . '.' . $this->tableName . '.deleteOne',
                    'options'   => json_encode([]),
                    'query'     => json_encode($condition),
                    'operation' => json_encode([]),
                    'time'      => $time,
                    'backtrace' => $bt,
                ]);
            }
        }

        return $result;
    }

    /**
     * @param $condition
     * @return \MongoDB\DeleteResult
     */
    public function deleteBy($condition)
    {
        Timer::start();
        $options = ['multi' => true];
        $result = $this->getDbAdapter()->selectCollection($this->getTableName())->deleteMany($condition, $options);
        $time = Timer::result();
        if (class_exists('Dyln\Debugbar\Debugbar')) {
            if (AppEnv::isDebugEnabled()) {
                $bt = [];
                $traces = debug_backtrace();
                for ($i = 15; $i > 0; $i--) {
                    if (isset($traces[$i])) {
                        $t = $traces[$i];
                        $bt[] = [
                            'file'     => isset($t['file']) ? $t['file'] : false,
                            'line'     => isset($t['line']) ? $t['line'] : false,
                            'function' => isset($t['function']) ? $t['function'] : false,
                        ];
                    }
                }
                Debugbar::add('Mongo', [
                    'command'   => $this->getDbAdapter()->getDatabaseName() . '.' . $this->tableName . '.deleteMany',
                    'options'   => json_encode($options),
                    'query'     => json_encode($condition),
                    'operation' => json_encode([]),
                    'time'      => $time,
                    'backtrace' => $bt,
                ]);
            }
        }

        return $result;
    }
}
