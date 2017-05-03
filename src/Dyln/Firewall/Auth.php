<?php

namespace Dyln\Firewall;

use Dyln\Firewall\Identity\IdentityInterface;
use Dyln\Firewall\Identity\Storage\AbstractStorage;

class Auth
{
    /** @var  AbstractStorage */
    protected $storage;

    public function __construct(AbstractStorage $storage)
    {
        $this->storage = $storage;
    }

    public function setStorage(AbstractStorage $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    public function writeIdentity(IdentityInterface $identity)
    {
        $this->storage->saveIdentity($identity);
    }

    public function id()
    {
        return $this->getIdentity()->getId();
    }

    /**
     * @return IdentityInterface
     */
    public function getIdentity()
    {
        return $this->storage->getIdentity();
    }

    public function logout()
    {
        $this->storage->clearIdentity();
    }

    public function isLoggedIn()
    {
        return $this->getIdentity()->isLoggedIn();
    }

    public function refreshIdentity(IdentityInterface $identity)
    {
        $this->storage->saveIdentity($identity);
    }

}