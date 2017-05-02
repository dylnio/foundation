<?php

namespace Dyln\Guzzle\Cookie;

use Dyln\Session\Session;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class SessionCookieJar extends CookieJar
{
    protected $key;
    /**
     * @var bool
     */
    private $storeSessionCookies;
    /**
     * @var Session
     */
    private $session;

    /**
     * RedisCookieJar constructor.
     * @param Session $session
     * @param null $key
     * @param bool $storeSessionCookies
     */
    public function __construct(Session $session, $key = null, $storeSessionCookies = false)
    {
        $this->session = $session;
        if (!$key) {
            $key = session_id();
        }
        $this->key = $key;
        $this->storeSessionCookies = $storeSessionCookies;
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
            if (CookieJar::shouldPersist($cookie, $this->storeSessionCookies)) {
                $json[] = $cookie->toArray();
            }
        }

        $jsonStr = \GuzzleHttp\json_encode($json);
        $res = $this->session->getSegment('__cookiejar')->set($this->key, $jsonStr);
        if (!$res) {
            throw new \RuntimeException("Unable to save redis {$this->key}");
        }
    }

    /**
     * Load cookies from a JSON formatted file.
     *
     * Old cookies are kept unless overwritten by newly loaded ones.
     *
     * @throws \RuntimeException if the file cannot be loaded.
     */
    public function load()
    {
        $json = $this->session->getSegment('__cookiejar')->get($this->key, '');
        if (false === $json) {
            throw new \RuntimeException("Unable to load key {$this->key}");
        } elseif ($json === '') {
            return;
        }

        $data = \GuzzleHttp\json_decode($json, true);
        if (is_array($data)) {
            foreach (json_decode($json, true) as $cookie) {
                $this->setCookie(new SetCookie($cookie));
            }
        } elseif (strlen($data)) {
            throw new \RuntimeException("Invalid cookie key: {$this->key}");
        }
    }
}