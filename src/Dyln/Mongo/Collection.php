<?php

namespace Dyln\Mongo;

use Dyln\DI\EventEmitterAwareInterface;
use Dyln\Event\Emitter;
use Dyln\Mongo\Enum\CollectionEvents;

class Collection extends \MongoDB\Collection implements EventEmitterAwareInterface
{
    /** @var Emitter */
    protected $emitter;

    public function setEmitter(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    public function getEmitter()
    {
        return $this->emitter;
    }

    public function bulkWrite(array $operations, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::bulkWrite($operations, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function count($filter = [], array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::count($filter, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function createIndex($key, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::createIndex($key, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function createIndexes(array $indexes, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::createIndexes($indexes, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function deleteMany($filter, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::deleteMany($filter, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function deleteOne($filter, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::deleteOne($filter, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function distinct($fieldName, $filter = [], array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::distinct($fieldName, $filter, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function drop(array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::drop($options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function dropIndex($indexName, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::dropIndex($indexName, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function dropIndexes(array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::dropIndexes($options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function find($filter = [], array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::find($filter, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function findOne($filter = [], array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::findOne($filter, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function findOneAndDelete($filter, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::findOneAndDelete($filter, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function findOneAndReplace($filter, $replacement, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::findOneAndDelete($filter, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function findOneAndUpdate($filter, $update, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::findOneAndUpdate($filter, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function insertMany(array $documents, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::insertMany($documents, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function insertOne($document, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::insertOne($document, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function listIndexes(array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::listIndexes($options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function replaceOne($filter, $replacement, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::replaceOne($filter, $replacement, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function updateMany($filter, $update, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::updateMany($filter, $update, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    public function updateOne($filter, $update, array $options = [])
    {
        $start = microtime(true);
        $reflector = new \ReflectionClass(__CLASS__);
        $parameters = $reflector->getMethod(__FUNCTION__)->getParameters();
        $args = [];
        $funcArgs = func_get_args();
        foreach ($parameters as $index => $param) {
            $args[$param->name] = $funcArgs[$index] ?? null;
        }
        $eventParams = [
            'command'    => __FUNCTION__,
            'database'   => $this->getDatabaseName(),
            'collection' => $this->getCollectionName(),
            'args'       => $args,
            'start'      => $start,
        ];
        $this->emit(CollectionEvents::BEFORE_COMMAND, $eventParams);
        $res = parent::updateOne($filter, $update, $options);
        $end = microtime(true);
        $this->emit(CollectionEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $res;
    }

    protected function emit($event, $args = [])
    {
        if ($this->emitter) {
            $this->emitter->emit($event, $args);
        }
    }
}
