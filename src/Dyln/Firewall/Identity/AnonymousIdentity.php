<?php

namespace Dyln\Firewall\Identity;

use Dyln\Firewall\Enum\Roles;

class AnonymousIdentity implements IdentityInterface
{
    protected $uuid;

    public function __construct()
    {
        $this->uuid = uniqid('_x_', true);
    }

    public function getDisplayName()
    {
        return '~ANON~';
    }

    public function isLoggedIn()
    {
        return false;
    }

    public function hasRole($role)
    {
        if (in_array($role, $this->getRoles())) {
            return true;
        }

        return false;
    }

    public function getRoles()
    {
        return [Roles::GUEST];
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getEmailAddress()
    {
        return 'anon@identity';
    }

    public function getAuthToken()
    {
        return null;
    }

    public function getSerializeIdentityData()
    {
        return [
            '_id'       => (string)$this->getId(),
            'roles'     => $this->getRoles(),
            '__class__' => get_class($this),
        ];
    }

    public function getId()
    {
        return null;
    }
}