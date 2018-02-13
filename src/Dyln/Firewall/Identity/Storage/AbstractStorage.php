<?php

namespace Dyln\Firewall\Identity\Storage;

use Dyln\Firewall\Identity\IdentityInterface;

abstract class AbstractStorage
{
    abstract public function getIdentity();

    abstract public function saveIdentity(IdentityInterface $identity);

    abstract public function clearIdentity();
}
