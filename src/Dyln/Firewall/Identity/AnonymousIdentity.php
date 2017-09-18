<?php

namespace Dyln\Firewall\Identity;

use Dyln\Firewall\Enum\Roles;

class AnonymousIdentity extends Identity
{
    /**
     * Identity constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->data = [
            '_id'          => null,
            'display_name' => null,
            'roles'        => [Roles::GUEST],
        ];
    }

    public function isLoggedIn()
    {
        return false;
    }

    public function getSerializeIdentityData($options = [])
    {
        return [
            '_id'       => (string)$this->getId(),
            'roles'     => $this->getRoles(),
            '__class__' => get_class($this),
        ];
    }
}
