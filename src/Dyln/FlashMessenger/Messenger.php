<?php

namespace Dyln\FlashMessenger;

use Dyln\Session\Segment;
use Dyln\Session\Session;
use Dyln\Util\ArrayUtil;

class Messenger
{
    const SUCCESS_KEY = 'success';
    const ERROR_KEY = 'error';
    const WARNING_KEY = 'warning';
    /** @var  Session */
    protected $session;
    /** @var  Segment */
    protected $segment;

    /**
     * Messenger constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function addError($message)
    {
        return $this->addMessage(self::ERROR_KEY, $message);
    }

    public function addSuccess($message)
    {
        return $this->addMessage(self::SUCCESS_KEY, $message);
    }

    public function addMessage($key, $message)
    {
        $availableKeys = array_flip((new \ReflectionClass(Messenger::class))->getConstants());
        if (in_array($key, $availableKeys)) {
            throw new \InvalidArgumentException('Invalid Key: ' . $key . '. Available keys are: ' . implode(',', array_keys($availableKeys)));
        }
        $messages = $this->getSegment()->get('messages', []);
        $messages[$key][] = $message;
        $this->getSegment()->set('messages', $messages);

        return $this;
    }

    public function getMessages($key, $asJson = false)
    {
        $messages = $this->getSegment()->get('messages', []);
        $keyMessages = ArrayUtil::getIn($messages, [$key], []);
        unset($messages[$key]);
        $this->getSegment()->set('messages', $messages);

        return $asJson ? json_encode($keyMessages) : $keyMessages;
    }


    private function getSegment()
    {
        if (!$this->segment) {
            $this->segment = $this->session->getSegment('__flash__');
        }

        return $this->segment;
    }
}