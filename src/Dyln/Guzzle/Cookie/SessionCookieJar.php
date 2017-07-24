<?php

namespace Dyln\Guzzle\Cookie;

use Dyln\Session\Session;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class SessionCookieJar extends CookieJar
{
    protected $key;
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->key = $this->session->id();
        $this->load();
    }

    public function __destruct()
    {
        $this->save();
    }

    public function save()
    {
        $json = [];
        foreach ($this as $cookie) {
            /** @var SetCookie $cookie */
            if (CookieJar::shouldPersist($cookie, true)) {
                $json[] = $cookie->toArray();
            }
        }

        $jsonStr = json_encode($json);
        $res = $this->session->getSegment('__cookiejar')->set($this->key, $jsonStr);
        if (!$res) {
            throw new \RuntimeException("Unable to save session {$this->key}");
        }
    }

    public function load()
    {
        $json = $this->session->getSegment('__cookiejar')->get($this->key, '');
        if (false === $json) {
            throw new \RuntimeException("Unable to load key {$this->key}");
        } elseif ($json === '') {
            return;
        }

        $data = json_decode($json, true);
        if (is_array($data)) {
            foreach ($data as $cookie) {
                $this->setCookie(new SetCookie($cookie));
            }
        } elseif (strlen($data)) {
            throw new \RuntimeException("Invalid cookie key: {$this->key}");
        }
    }
}