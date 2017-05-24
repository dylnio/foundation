<?php

namespace Dyln\Firewall;

use Dyln\Firewall\Enum\Roles;

class Firewall
{
    const ROUTE_LOGIN = 'login';
    const ROUTE_LOGOUT = 'logout';
    const ROUTE_DENIED = 'denied';
    /** @var  Auth */
    protected $auth;
    protected $rules = [];

    public function __construct(Auth $auth, $rules = [], $routes = [])
    {
        $this->auth = $auth;
        $this->rules = $rules;
        $this->routes = $routes ?: ['login' => null, 'logout' => null, 'denied' => null];
    }

    public function addRule($resource, $privilege, $callbackOrRoles = [])
    {
        $this->rules[$resource][$privilege] = $callbackOrRoles;
    }

    public function getRoute($type)
    {
        return $this->routes[$type];
    }

    public function setRoutes($routes = ['login' => '/login', 'logout' => '/logout', 'denied' => '/denied',])
    {
        $this->routes = $routes;
    }

    public function isLoggedIn()
    {
        return $this->auth->isLoggedIn();
    }

    public function getIdentity()
    {
        return $this->auth->getIdentity();
    }

    public function setRules($rules = [])
    {
        $this->rules = $rules;
    }

    public function isAuthorized($resource, $privilege, $params = [])
    {
        $authorized = false;
        $identity = $this->auth->getIdentity();
        $identityRoles = $identity->getRoles();
        if ($this->isLoggedIn()) {
            $identityRoles[] = Roles::GUEST;
        }
        $identityRoles = array_unique($identityRoles);
        $rule = $this->getRule($resource, $privilege);
        if ($rule instanceof \Closure) {
            $authorized = $rule($identity, $params);
        } else {
            foreach ($identityRoles as $identityRole) {
                if (in_array($identityRole, $rule)) {
                    $authorized = true;
                    break;
                }
            }
        }

        return $authorized;
    }

    private function getRule($resource, $privilege)
    {
        $rule = null;
        if (!isset($this->rules[$resource])) {
            throw new \Exception('Resource not found: ' . $resource);
        }
        if (!isset($this->rules[$resource][$privilege])) {
            if (isset($this->rules[$resource]['~'])) {
                $rule = $this->rules[$resource]['~'];
            } else {
                throw new \Exception('Privilage not found');
            }
        } else {
            $rule = $this->rules[$resource][$privilege];
        }

        return $rule;
    }

}