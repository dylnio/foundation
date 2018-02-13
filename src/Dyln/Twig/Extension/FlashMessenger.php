<?php

namespace Dyln\Twig\Extension;

use Dyln\FlashMessenger\Messenger;

class FlashMessenger extends \Twig_Extension
{
    /** @var  Messenger */
    protected $messenger;

    /**
     * FlashMessenger constructor.
     * @param Messenger $messenger
     */
    public function __construct(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }

    public function getName()
    {
        return 'flash';
    }

    public function getFunctions()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return [
            new \Twig_SimpleFunction('flash', [$this, 'flash']),
        ];
    }

    public function flash()
    {
        return $this->messenger;
    }
}
