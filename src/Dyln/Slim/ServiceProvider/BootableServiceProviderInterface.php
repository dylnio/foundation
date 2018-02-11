<?php

namespace Dyln\Slim\ServiceProvider;

use Interop\Container\ContainerInterface;

interface BootableServiceProviderInterface extends ServiceProviderInterface
{
    public function boot(ContainerInterface $container);
}
