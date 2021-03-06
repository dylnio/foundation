<?php

namespace Dyln\Firewall\Identity;

class Identity implements IdentityInterface
{
    protected $data = [];

    /**
     * Identity constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getId()
    {
        return $this->data['_id'];
    }

    public function getDisplayName()
    {
        return $this->data['display_name'] ?? $this->data['displayname'] ?? 'N/A';
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
        return isset($this->data['roles']) ? $this->data['roles'] : [];
    }

    public function getSerializeIdentityData($options = [])
    {
        return $this->data;
    }

    public function get($key)
    {
        return $this->data[$key] ?? null;
    }

    public function addRole($role)
    {
        $roles = $this->getRoles();
        $roles[] = $role;
        $this->data['roles'] = array_values(array_unique($roles));
    }
}
