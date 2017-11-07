<?php

namespace Dyln\Clockwork\ApiClient;

use Clockwork\Clockwork;
use Dyln\ApiClient\Enum\Events;
use League\Event\EventInterface;
use League\Event\ListenerInterface;
use Psr\Log\LogLevel;

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
                $this->clockwork->log(LogLevel::INFO, $args);
                break;
            case Events::AFTER_CALL_SEND:
                $timeline->endEvent($eventName);
                $this->clockwork->log(LogLevel::INFO, $args);
                break;
            case Events::CALL_END:
                $this->clockwork->log(LogLevel::INFO, $args);
                break;
        }
        $this->clockwork->setTimeline($timeline);
    }

    public function isListener($listener)
    {
        return true;
    }
}
