<?php

namespace Dyln\ElasticSearch;

use Elasticsearch\Transport;

class ClientBuilder extends \Elasticsearch\ClientBuilder
{
    public function instantiate(Transport $transport, callable $endpoint)
    {
        return new Client($transport, $endpoint);
    }
}
