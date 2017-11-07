<?php

namespace Dyln\Debugbar;

use Clockwork\Clockwork;
use Dyln\Clockwork\DataSource\MultiQueryDataSource;
use Psr\Log\LogLevel;
use Slim\Http\Request;
use Slim\Http\Response;
use function Dyln\getin;

class ClockworkMiddleware
{
    /** @var Clockwork */
    protected $clockwork;

    /**
     * ClockworkMiddleware constructor.
     * @param Clockwork $clockwork
     */
    public function __construct(Clockwork $clockwork)
    {
        $this->clockwork = $clockwork;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $response = $next($request, $response);
        $timeline = $this->clockwork->getTimeline();
        $data = Debugbar::getData();
        $mongo = getin($data, 'Mongo', []);
        $elastic = getin($data, 'Elastic', []);
        $redis = getin($data, 'Redis', []);
        $apiRequest = getin($data, 'ApiRequest', []);
        $apiResponses = getin($data, 'ApiResponse', []);
        if ($mongo) {
            $databaseSource = $this->getDatabaseSource();
            foreach ($mongo as $row) {
                $timeline->addEvent(uniqid(), '[MONGO] ' . $row['command'] . '(' . $row['query'] . ',' . $row['options'] . ')', $row['start'], $row['end']);
                if (!empty($row['operation'])) {
                    $databaseSource->addMongoQuery($row['command'] . '(' . $row['query'] . ',' . $row['operation'] . ',' . $row['options'] . ')', $row['start'], $row['end']);
                } else {
                    $databaseSource->addMongoQuery($row['command'] . '(' . $row['query'] . ',' . $row['options'] . ')', $row['start'], $row['end']);
                }
            }
        }
        if ($elastic) {
            $databaseSource = $this->getDatabaseSource();
            foreach ($elastic as $row) {
                $timeline->addEvent(uniqid(), '[ELASTIC] ' . $row['command'] . '(' . $row['query'] . ',' . $row['options'] . ')', $row['start'], $row['end']);
                $databaseSource->addElasticQuery($row['command'] . ' (' . $row['query'] . ')', $row['start'], $row['end']);
            }
        }
        if ($redis) {
            $databaseSource = $this->getDatabaseSource();
            foreach ($redis as $row) {
                $timeline->addEvent(uniqid(), '[REDIS] ' . $row['command'] . '(' . $row['query'] . ')', $row['start'], $row['end']);
                $databaseSource->addRedisQuery($row['command'] . ' (' . $row['query'] . ')', $row['start'], $row['end']);
            }
        }
        if ($apiRequest) {
            $databaseSource = $this->getDatabaseSource();
            foreach ($apiRequest as $row) {
                $timeline->addEvent(uniqid(), '[API REQUEST] ' . $row['curl'], $row['start'], $row['end']);
                $databaseSource->addApiRequest($row['curl'], $row['start'], $row['end']);
            }
        }
        if ($apiResponses) {
            foreach ($apiResponses as $row) {
                $this->clockwork->log(LogLevel::INFO, $row);
            }
        }
        return $response;
    }

    private function getDatabaseSource()
    {
        $sources = $this->clockwork->getDataSources();
        $found = null;
        foreach ($sources as $source) {
            if ($source instanceof MultiQueryDataSource) {
                $found = $source;
            }
        }
        if (!$found) {
            $found = new MultiQueryDataSource();
            $this->clockwork->addDataSource($found);
        }
        return $found;
    }
}
