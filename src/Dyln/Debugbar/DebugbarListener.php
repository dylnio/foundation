<?php

namespace Dyln\Debugbar;

use Dyln\AppEnv;
use Dyln\Doctrine\Common\Cache\Enum\RedisCacheEvents;
use Dyln\Mongo\Enum\CollectionEvents;
use League\Event\EventInterface;
use League\Event\ListenerInterface;
use function Dyln\getin;

class DebugbarListener implements ListenerInterface
{
    public function handle(EventInterface $event, $args = [])
    {
        if (AppEnv::isDebugEnabled()) {
            switch ($event->getName()) {
                case CollectionEvents::AFTER_COMMAND:
                    Debugbar::add('Mongo', $this->parseForMongo($args));
                    break;
                case RedisCacheEvents::AFTER_COMMAND:
                    Debugbar::add('Redis', $this->parseForRedis($args));
                    break;
            }
        }
    }

    private function parseForRedis($args = [])
    {
        $bt = [];
        $parsed = [];
        $traces = array_reverse(debug_backtrace());
        foreach ($traces as $trace) {
            $bt[] = [
                'file'     => isset($trace['file']) ? $trace['file'] : false,
                'line'     => isset($trace['line']) ? $trace['line'] : false,
                'function' => isset($trace['function']) ? $trace['function'] : false,
            ];
        }
        $parsed['command'] = "{$args['command']}";
        $parsed['time'] = $args['duration'];
        $parsed['start'] = $args['start'];
        $parsed['end'] = $args['end'];
        $parsed['duration'] = $args['duration'];
        $parsed['args'] = getin($args, 'args', []);

        return $parsed;
    }

    private function parseForMongo($args = [])
    {
        $bt = [];
        $parsed = [];
        $traces = array_reverse(debug_backtrace());
        foreach ($traces as $trace) {
            $bt[] = [
                'file'     => isset($trace['file']) ? $trace['file'] : false,
                'line'     => isset($trace['line']) ? $trace['line'] : false,
                'function' => isset($trace['function']) ? $trace['function'] : false,
            ];
        }
        $parsed['command'] = "{$args['database']}.{$args['collection']}.{$args['command']}";
        $parsed['time'] = $args['duration'];
        $parsed['start'] = $args['start'];
        $parsed['end'] = $args['end'];
        $parsed['duration'] = $args['duration'];
        $parsed['filter'] = getin($args, 'args.filter', []);
        $parsed['update'] = getin($args, 'args.update', []);
        $parsed['options'] = getin($args, 'args.options', []);
        $parsed['replacement'] = getin($args, 'args.replacement', []);
        $parsed['document'] = getin($args, 'args.document', []);
        $parsed['documents'] = getin($args, 'args.documents', []);
        $parsed['fieldName'] = getin($args, 'args.fieldName', []);
        $parsed['key'] = getin($args, 'args.key', []);
        $parsed['indexName'] = getin($args, 'args.indexName', []);
        $parsed['indexes'] = getin($args, 'args.indexes', []);

        return $parsed;
    }

    public function isListener($listener)
    {
        return true;
    }
}
