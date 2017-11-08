<?php

namespace Dyln\Mongo\Enum;

use Dyln\Enum;

class CollectionEvents extends Enum
{
    const BEFORE_COMMAND = 'dyln_mongo_collection\before_command';
    const AFTER_COMMAND = 'dyln_mongo_collection\after_command';
}
