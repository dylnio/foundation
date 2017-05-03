<?php

namespace Dyln\Firewall\Identity\Storage;

use Dyln\Firewall\Identity\AnonymousIdentity;
use Dyln\Firewall\Identity\IdentityInterface;

class MemoryStorage extends AbstractStorage
{
    protected $identity;

    public function __construct()
    {
        $this->identity = new AnonymousIdentity();
    }

    public function getIdentity()
    {
        return $this->identity;
    }

    public function hasIdentity()
    {
        return $this->identity instanceof IdentityInterface;
    }

    public function clearIdentity()
    {
        $this->saveIdentity(new AnonymousIdentity());
    }

    public function saveIdentity(IdentityInterface $identity)
    {
        $this->identity = $identity;
    }
}