<?php

namespace Dyln\Sentry;

use Dyln\AppEnv;
use Dyln\Config\Config;
use Dyln\Firewall\Exception\RouteNotFoundException;
use Dyln\Sentry\Exception\NotLoggableException;

class Sentry
{
    protected static $instance;
    /** @var  \Raven_Client */
    protected $client;
    protected $enabled = false;

    private function __construct(\Raven_Client $client, $enabled = true)
    {
        $this->client = $client;
        $this->enabled = $enabled;
    }

    public static function getInstance()
    {
        $sentryUrl = Config::get('sentry.url');
        if (!self::$instance) {
            $client = new \Raven_Client($sentryUrl);
            self::$instance = new self($client, $sentryUrl ? true : false);
        }

        return self::$instance;
    }

    public static function addExtraContext($key, $value)
    {
        if (self::getInstance()->enabled) {
            self::getInstance()->client->extra_context([
                $key => $value,
            ]);
        }
    }

    public static function message($message, $params = [], $data = [], $stack = true, $vars = null, $tags = [])
    {
        if (self::getInstance()->enabled) {
            array_walk($params, function (&$value) {
                $value = (string) $value;
            });
            $data['tags'] = $tags;
            self::getInstance()->client->captureMessage($message, $params, $data, $stack, $vars);
        }
    }

    public static function info($message, $params = [], $data = [], $stack = true, $vars = null, $tags = [])
    {
        $data['level'] = \Raven_Client::INFO;
        self::getInstance()->message($message, $params, $data, $stack, $vars, $tags);
    }

    public static function debug($message, $params = [], $data = [], $stack = true, $vars = null, $tags = [])
    {
        $data['level'] = \Raven_Client::DEBUG;
        self::getInstance()->message($message, $params, $data, $stack, $vars, $tags);
    }

    public static function error($message, $params = [], $data = [], $stack = true, $vars = null, $tags = [])
    {
        $data['level'] = \Raven_Client::ERROR;
        self::getInstance()->message($message, $params, $data, $stack, $vars, $tags);
    }

    public static function fatal($message, $params = [], $data = [], $stack = true, $vars = null, $tags = [])
    {
        $data['level'] = \Raven_Client::FATAL;
        self::getInstance()->message($message, $params, $data, $stack, $vars, $tags);
    }

    public static function exception($e)
    {
        if ($e instanceof NotLoggableException || $e instanceof RouteNotFoundException) {
            return;
        }
        if (self::getInstance()->enabled) {
            self::getInstance()->client->captureException($e);
        }
    }

    public function register($version, $userContext = [])
    {
        global $argv;
        $instance = self::getInstance();
        if (self::getInstance()->enabled) {
            $client = $instance->client;
            $client->setRelease($version);
            if ($userContext) {
                $client->user_context($userContext);
            }
            $client->setEnvironment(AppEnv::getAppEnv());
            $client->extra_context([
                'argv'   => $argv ? implode(' ', $argv) : null,
                'env'    => $_ENV,
                'server' => $_SERVER,
            ]);
            if (php_sapi_name() == 'cli') {
                $client->tags_context(['browser' => 'cli']);
            }
            $client->install();
        }
    }
}
