<?php

namespace Dyln\ElasticSearch;

use Dyln\ElasticSearch\Enum\ElasticSearchEvents;
use Dyln\Event\Emitter;
use League\Event\EmitterAwareInterface;
use League\Event\EmitterInterface;

class Client extends \Elasticsearch\Client implements EmitterAwareInterface
{
    /** @var Emitter */
    protected $emitter;

    public function search($params = [])
    {
        $index = $this->extractArgument($params, 'index');
        $type = $this->extractArgument($params, 'type');
        $body = $this->extractArgument($params, 'body');
        /** @var callback $endpointBuilder */
        $endpointBuilder = $this->endpoints;
        /** @var \Elasticsearch\Endpoints\Search $endpoint */
        $endpoint = $endpointBuilder('Search');
        $endpoint->setIndex($index)
                 ->setType($type)
                 ->setBody($body);
        $endpoint->setParams($params);
        $start = microtime(true);
        $eventParams = [
            'command' => __FUNCTION__,
            'args'    => [
                'index' => $index,
                'type'  => $type,
                'body'  => $body,
            ],
            'start'   => $start,
        ];
        $this->emit(ElasticSearchEvents::BEFORE_COMMAND, $eventParams);
        $response = $endpoint->performRequest();
        $end = microtime(true);
        $this->emit(ElasticSearchEvents::AFTER_COMMAND, $eventParams + [
                'end'      => $end,
                'duration' => $end - $start,
            ]);

        return $endpoint->resultOrFuture($response);
    }

    public function setEmitter(EmitterInterface $emitter = null)
    {
        $this->emitter = $emitter;
    }

    public function getEmitter()
    {
        return $this->emitter;
    }

    public function emit($event, $args = [])
    {
        if ($this->getEmitter()) {
            $this->getEmitter()->emit($event, $args);
        }
    }
}
