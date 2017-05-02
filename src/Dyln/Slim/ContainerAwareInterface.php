<?php

namespace Dyln\Slim;

use Interop\Container\ContainerInterface;

interface ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container);

    public function getContainer(): ContainerInterface;
}