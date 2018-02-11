<?php

namespace Dyln\Firewall\Identity;

interface IdentityInterface
{
    public function getRoles();

    public function getId();

    public function getDisplayName();

    public function isLoggedIn();

    public function hasRole($role);

    public function getSerializeIdentityData($options = []);
}
