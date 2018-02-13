<?php

namespace Dyln\Firewall\Middleware;

use Dyln\Util\ArrayUtil;
use Dyln\Util\IpUtil;
use M6Web\Component\Firewall\Firewall as IpFirewall;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class IpFirewallMiddleware
{
    /** @var  IpFirewall */
    protected $ipFirewall;
    protected $unsecurePaths = [];

    public function __construct($params = [])
    {
        $whitelist = ArrayUtil::getIn($params, ['ip_firewall', 'whitelist'], []);
        $blacklist = ArrayUtil::getIn($params, ['ip_firewall', 'blacklist'], []);
        $this->unsecurePaths = ArrayUtil::getIn($params, ['ip_firewall', 'unsecure_paths'], []);
        $this->ipFirewall = new IpFirewall();
        $this->ipFirewall
            ->setDefaultState(false)
            ->addList($whitelist, 'white', true)
            ->addList($blacklist, 'black', false)
            ->setIpAddress(IpUtil::getRealIp());
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, $next)
    {
        if (in_array($request->getUri()->getPath(), $this->unsecurePaths)) {
            $connAllowed = true;
        } else {
            $connAllowed = $this->ipFirewall->handle();
        }
        if (!$connAllowed) {
            die('Not allowed: ' . $this->ipFirewall->getIpAddress());
        }

        return $next($request, $response);
    }
}
