<?php

namespace Dyln\Debugbar;

use Clockwork\Clockwork;
use Clockwork\DataSource\XdebugDataSource;
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
     *
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
        $userLog = getin($data, 'UserLog', []);
        if ($mongo) {
            $databaseSource = $this->getDatabaseSource();
            foreach ($mongo as $row) {
                $params = [];
                if (!empty($row['fieldName'])) {
                    $params[] = is_array($row['fieldName']) ? json_encode($row['fieldName']) : $row['fieldName'];
                }
                if (!empty($row['key'])) {
                    $params[] = is_array($row['key']) ? json_encode($row['key']) : $row['key'];
                }
                if (!empty($row['indexName'])) {
                    $params[] = is_array($row['indexName']) ? json_encode($row['indexName']) : $row['indexName'];
                }
                if (!empty($row['indexes'])) {
                    $params[] = json_encode($row['indexes']);
                }
                if (!empty($row['document'])) {
                    $params[] = json_encode($row['document']);
                }
                if (!empty($row['documents'])) {
                    $params[] = json_encode($row['documents']);
                }
                if (!empty($row['filter'])) {
                    $params[] = json_encode($row['filter']);
                }
                if (!empty($row['update'])) {
                    $params[] = json_encode($row['update']);
                }
                if (!empty($row['replacement'])) {
                    $params[] = json_encode($row['replacement']);
                }
                if (!empty($row['operation'])) {
                    $params[] = json_encode($row['operation']);
                }
                if (!empty($row['options'])) {
                    $params[] = json_encode($row['options']);
                }
                $text = $row['command'] . '(' . implode(', ', $params) . ')';
                $text = str_replace('[]', '{}', $text);
                if (!empty($row['app'])) {
                    $text = ' (' . $row['app'] . ') ' . $text;
                }
                $timeline->addEvent(uniqid(), '[MONGO] ' . $text, $row['start'], $row['end']);
                $databaseSource->addMongoQuery($text, $row['start'], $row['end']);
            }
        }
        if ($elastic) {
            $databaseSource = $this->getDatabaseSource();
            foreach ($elastic as $row) {
                $text = 'GET ' . $row['args']['index'] . '/' . $row['args']['type'] . " " . json_encode($row['args']['body']);
                if (!empty($row['app'])) {
                    $text = ' (' . $row['app'] . ') ' . $text;
                }
                $timeline->addEvent(uniqid(), '[ELASTIC] ' . $text, $row['start'], $row['end']);
                $databaseSource->addElasticQuery($text, $row['start'], $row['end']);
            }
        }
        if ($redis) {
            $databaseSource = $this->getDatabaseSource();
            foreach ($redis as $row) {
                $params = [];
                if (!empty($row['args'])) {
                    $params[] = json_encode($row['args']);
                }
                $text = $row['command'] . '(' . implode(', ', $params) . ')';
                $text = str_replace('[]', '{}', $text);
                if (!empty($row['app'])) {
                    $text = ' (' . $row['app'] . ') ' . $text;
                }
                $timeline->addEvent(uniqid(), '[REDIS] ' . $text, $row['start'], $row['end']);
                $databaseSource->addRedisQuery($text, $row['start'], $row['end']);
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
                $body = json_decode($row['body'], true);
                if (isset($body['debug'])) {
                    unset($body['debug']);
                }
                $row['body'] = json_encode($body);
                $this->clockwork->log(LogLevel::INFO, $row);
            }
        }
        if ($userLog) {
            foreach ($userLog as $row) {
                $this->clockwork->log($row['level'] ?? LogLevel::INFO, $row['message'], $row['context']);
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

        if (extension_loaded('xdebug')) {
            $this->clockwork->addDataSource(new XdebugDataSource());
        }

        return $found;
    }
}
