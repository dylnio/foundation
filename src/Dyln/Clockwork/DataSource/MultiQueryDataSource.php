<?php

namespace Dyln\Clockwork\DataSource;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;

class MultiQueryDataSource extends DataSource
{
    protected $queries = [];

    public function addMongoQuery($query, $start, $end)
    {
        $this->queries[] = [
            'query'    => str_replace('[]', '{}', $query),
            'duration' => ($end - $start) * 1000,
            'model'    => 'MONGO',
        ];
    }

    public function addElasticQuery($query, $start, $end)
    {
        $this->queries[] = [
            'query'    => $query,
            'duration' => ($end - $start) * 1000,
            'model'    => 'ELASTIC',
        ];
    }

    public function addRedisQuery($query, $start, $end)
    {
        $this->queries[] = [
            'query'    => $query,
            'duration' => ($end - $start) * 1000,
            'model'    => 'REDIS',
        ];
    }

    public function addApiRequest($query, $start, $end)
    {
        $this->queries[] = [
            'query'    => $query,
            'duration' => ($end - $start) * 1000,
            'model'    => 'API REQUEST',
        ];
    }

    public function resolve(Request $request)
    {
        $request->databaseQueries = $this->queries;

        return $request;
    }
}
