<?php

namespace Dyln\Slim;

use Interop\Container\ContainerInterface;
use RuntimeException;
use Slim\Interfaces\CallableResolverInterface;

class CallableResolver implements CallableResolverInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve toResolve into a closure that that the router can dispatch.
     *
     * If toResolve is of the format 'class:method', then try to extract 'class'
     * from the container otherwise instantiate it and then dispatch 'method'.
     *
     * @param mixed $toResolve
     *
     * @return callable
     *
     * @throws RuntimeException if the callable does not exist
     * @throws RuntimeException if the callable is not resolvable
     */
    public function resolve($toResolve)
    {
        $class = null;
        $method = null;
        if (!is_callable($toResolve) && is_string($toResolve)) {
            $callablePattern = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!'; // Controller:Action
            if (preg_match($callablePattern, $toResolve, $matches)) {
                $class = $matches[1];
                $method = $matches[2];
            } else {
                $class = $toResolve;
            }
            $obj = $this->container->get($class);
            if ($obj instanceof ContainerAwareInterface) {
                $obj->setContainer($this->container);
            }
            if ($method) {
                $resolved = [$obj, $method];
            } else {
                $resolved = $obj;
            }
            if (!is_callable($resolved)) {
                throw new RuntimeException(sprintf('%s is not resolvable', $toResolve));
            }
        } else {
            $resolved = $toResolve;
        }

        if (!is_callable($resolved)) {
            throw new RuntimeException(sprintf('%s is not resolvable', $toResolve));
        }

        return $resolved;
    }
}