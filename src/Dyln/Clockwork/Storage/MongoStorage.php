<?php

namespace Dyln\Clockwork\Storage;

use Clockwork\Request\Request;
use Clockwork\Storage\Storage;
use MongoDB\Database;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Manager;

class MongoStorage extends Storage
{

    /** @var \MongoDB\Collection */
    protected $collection;
    protected $expiration;

    /**
     * MongoStorage constructor.
     * @param $host
     * @param $dbName
     * @param $collectionName
     * @param null $expiration
     * @param array $uriOptions
     * @param array $driverOptions
     */
    public function __construct($host, $dbName, $collectionName, $expiration = null, $uriOptions = [], $driverOptions = [])
    {
        $manager = new Manager($host, $uriOptions, $driverOptions);
        $db = new Database($manager, $dbName, [
            'typeMap' => [
                'array'    => 'array',
                'document' => 'array',
                'root'     => 'array',
            ],
        ]);
        $this->collection = $db->selectCollection($collectionName);
        $this->expiration = $expiration === null ? 60 * 24 * 7 : $expiration;
    }

    public function all()
    {
        $rows = $this->collection->find();
        return $this->resultsToRequests($rows);
    }

    public function find($id)
    {
        $condition = ['id' => $id];
        return $this->resultsToRequests($this->collection->find($condition));
    }

    public function latest()
    {
        $condition = [];
        $options = [
            'sort'  => ['id' => -1],
            'limit' => 1,
        ];
        return $this->resultsToRequests($this->collection->find($condition, $options));
    }

    public function previous($id, $count = null)
    {
        if (!$count) {
            $count = 1;
        }
        $condition = [
            'id' => ['$lt' => $id],
        ];
        $options = [
            'sort'  => ['id' => -1],
            'limit' => $count,
        ];
        return $this->resultsToRequests($this->collection->find($condition, $options));
    }

    public function next($id, $count = null)
    {
        if (!$count) {
            $count = 1;
        }
        $condition = [
            'id' => ['$gt' => $id],
        ];
        $options = [
            'sort'  => ['id' => 1],
            'limit' => $count,
        ];
        return $this->resultsToRequests($this->collection->find($condition, $options));
    }

    public function store(Request $request)
    {
        $data = $this->applyFilter($request->toArray());
        $this->collection->insertOne($data);
        $this->cleanup();
    }

    public function cleanup()
    {
        if ($this->expiration === false) return;
        $this->collection->deleteMany(['time' => ['$lt' => time() - ($this->expiration * 60)]]);
    }

    protected function resultsToRequests($rows)
    {
        if ($rows instanceof Cursor) {
            $rows = $rows->toArray();
        }
        return array_map(function ($data) {
            unset($data['_id']);
            return $this->dataToRequest($data);
        }, $rows);
    }

    // Returns a Request instance from a single database record
    protected function dataToRequest($data)
    {
        return new Request($data);
    }
}
