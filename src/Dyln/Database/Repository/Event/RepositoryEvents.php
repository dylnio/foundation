<?php

namespace Dyln\Database\Repository\Event;

use Dyln\Enum;

class RepositoryEvents extends Enum
{
    const BEFORE_MODEL_CREATED = 'repository_events/before_model_created';
    const AFTER_MODEL_CREATED = 'repository_events/after_model_created';
    const BEFORE_MODEL_UPDATED = 'repository_events/before_model_updated';
    const AFTER_MODEL_UPDATED = 'repository_events/after_model_updated';
}
