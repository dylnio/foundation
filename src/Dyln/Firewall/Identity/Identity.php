<?php

namespace Dyln\Firewall\Identity;

class Identity implements IdentityInterface
{
    protected $data = [];

    /**
     * Identity constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getId()
    {
        return $this->data['_id'];
    }

    public function getDisplayName()
    {
        return $this->data['displayname'];
    }

    public function isLoggedIn()
    {
        return true;
    }

    public function hasRole($role)
    {
        $roles = $this->getRoles();

        return in_array($role, $roles);
    }

    public function getRoles()
    {
        return $this->data['roles'];
    }

    public function getSerializeIdentityData()
    {
        return $this->data;
    }
}