<?php

namespace Dyln\Sira;


use Dyln\Sira\Element\Element;

class Worker
{
    const EVENT_ON_NEW_MESSAGE = 'sira/on_new_message';
    /** @var  Client */
    protected $client;
    protected $eventHandlers = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function on($event, callable $handler)
    {
        $this->eventHandlers[$event][] = $handler;
    }

    private function triggerEvent($event, Element $element, \Exception $exception = null)
    {
        $handlers = $this->eventHandlers[$event] ?? [];
        foreach ($handlers as $handler) {
            $element = call_user_func_array($handler, [$element, $exception]);
        }

        return $element;
    }

    public function start()
    {
        while (true) {
            $element = $this->client->pop('test_queue');
            if ($element) {
                $element = $this->triggerEvent(self::EVENT_ON_NEW_MESSAGE, $element);
                if ($element->isError()) {
                    $this->client->fail($element);
                    if ($element->canRetry()) {
                        $this->client->requeueFailed($element);
                    }
                } else {
                    $this->client->success($element);
                }
            }
            sleep(1);
        }
    }
}