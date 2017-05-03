<?php

namespace Dyln\Firewall\Identity\Storage;

use Dyln\Firewall\Identity\AnonymousIdentity;
use Dyln\Firewall\Identity\Identity;
use Dyln\Firewall\Identity\IdentityInterface;
use Dyln\Session\Session;

class SessionStorage extends AbstractStorage
{
    const SEGMENT_NAME = '__AUTH__';
    const IDENTITY_SESSION_KEY = '__IDENTITY__';
    /** @var Session */
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getIdentity()
    {
        if (!$this->hasIdentity()) {
            return new AnonymousIdentity();
        }

        return $this->hydrate();
    }

    public function saveIdentity(IdentityInterface $identity)
    {
        $this->getSessionSegment()->set(self::IDENTITY_SESSION_KEY, serialize($identity->getSerializeData()));
    }

    public function clearIdentity()
    {
        $this->saveIdentity(new AnonymousIdentity());
    }

    private function hasIdentity()
    {
        return $this->hydrate()->isLoggedIn();
    }

    private function getSessionSegment()
    {
        return $this->session->getSegment(self::SEGMENT_NAME);
    }

    private function hydrate()
    {
        $identity = new AnonymousIdentity();
        $data = $this->getSessionSegment()->get(self::IDENTITY_SESSION_KEY);
        if ($data) {
            $data = unserialize($data);
            if ($data['id']) {
                $identity = new Identity(['data' => $data]);
            }
        }

        return $identity;
    }
}