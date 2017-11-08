<?php

namespace Dyln\Debugbar;

use Dyln\AppEnv;
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
            }
        }
    }

    private function parseForMongo($args = [])
    {
        $bt = [];
        $traces = array_reverse(debug_backtrace());
        foreach ($traces as $trace) {
            $bt[] = [
                'file'     => isset($trace['file']) ? $trace['file'] : false,
                'line'     => isset($trace['line']) ? $trace['line'] : false,
                'function' => isset($trace['function']) ? $trace['function'] : false,
            ];
        }
        switch (strtolower($args['command'])) {
            case 'find':
                $args['command'] = "{$args['database']}.{$args['collection']}.find";
                break;
            case 'findone':
                $args['command'] = "{$args['database']}.{$args['collection']}.findOne";
                break;
        }
        $args['time'] = $args['duration'];
        $args['filter'] = getin($args, 'args.filter', []);
        $args['options'] = getin($args, 'args.options', []);

        return $args;
    }

    public function isListener($listener)
    {
        return true;
    }
}
