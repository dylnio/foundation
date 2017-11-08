<?php

namespace Dyln\ElasticSearch\Enum;

use Dyln\Enum;

class ElasticSearchEvents extends Enum
{
    const BEFORE_COMMAND = 'dyln_elasticsearch_client/before_command';
    const AFTER_COMMAND = 'dyln_elasticsearch_client/after_command';
}
