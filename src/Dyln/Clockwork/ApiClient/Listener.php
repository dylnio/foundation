<?php

namespace Dyln\Clockwork\ApiClient;

use Clockwork\Clockwork;
use Dyln\ApiClient\Enum\Events;
use Dyln\Clockwork\DataSource\MultiQueryDataSource;
use Dyln\Debugbar\Debugbar;
use League\Event\EventInterface;
use League\Event\ListenerInterface;
use function Dyln\getin;

class Listener implements ListenerInterface
{
    /** @var Clockwork */
    protected $clockwork;

    /**
     * Listener constructor.
     * @param Clockwork $clockwork
     */
    public function __construct(Clockwork $clockwork)
    {
        $this->clockwork = $clockwork;
    }

    public function handle(EventInterface $event, $args = [])
    {
        $eventName = 'Api Call (' . $args['path'] . ') ' . uniqid();
        $description = 'Api Call for (' . $args['method'] . ' ' . $args['path'] . ')';
        $timeline = $this->clockwork->getTimeline();
        switch ($event->getName()) {
            case Events::BEFORE_CALL_SEND:
                $timeline->startEvent($eventName, $description, null, $args);
                break;
            case Events::AFTER_CALL_SEND:
                $timeline->endEvent($eventName);
                $data = Debugbar::getData();
                $mongo = getin($data, 'Mongo', []);
                $elastic = getin($data, 'Elastic', []);
                $redis = getin($data, 'Redis', []);
                if ($mongo) {
                    $databaseSource = $this->getDatabaseSource();
                    foreach ($mongo as $row) {
                        $timeline->addEvent(uniqid(), '[MONGO] ' . $row['command'] . '(' . $row['query'] . ',' . $row['options'] . ')', $row['start'], $row['end']);
                        $databaseSource->addMongoQuery($row['command'] . '(' . $row['query'] . ',' . $row['options'] . ')', $row['start'], $row['end']);
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
                break;
        }
        $this->clockwork->setTimeline($timeline);
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

    public function isListener($listener)
    {
        return true;
    }
}
