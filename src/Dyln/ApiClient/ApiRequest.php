<?php

namespace Dyln\ApiClient;

use Dyln\Util\BooleanUtil;

class ApiRequest
{
    protected $id;
    protected $path;
    protected $method = 'GET';
    protected $query = [];
    protected $body = [];
    protected $priority = 100;
    protected $stopOnError = true;
    protected $dependsOn = null;
    protected $cacheable = false;
    protected $cacheLifeTime = 0;

    /**
     * @param array $array
     * @return ApiRequest
     */
    public static function fromArray($array = [])
    {
        $request = new static();
        if (isset($array['id'])) {
            $request = $request->withId($array['id']);
        }
        if (isset($array['path'])) {
            $request = $request->withPath($array['path']);
        }
        if (isset($array['method'])) {
            $request = $request->withMethod($array['method']);
        }
        if (isset($array['query'])) {
            $request = $request->withQuery($array['query']);
        }
        if (isset($array['body'])) {
            $request = $request->withBody($array['body']);
        }
        if (isset($array['priority'])) {
            $request = $request->withPriority($array['priority']);
        }
        if (isset($array['stopOnError'])) {
            $request = $request->withStopOnError($array['stopOnError']);
        }
        if (isset($array['dependsOn'])) {
            $request = $request->withDependsOn($array['dependsOn']);
        }
        if (isset($array['cacheable'])) {
            $request = $request->withCacheable($array['cacheable']);
        }
        if (isset($array['cache_lifetime'])) {
            $request = $request->withCacheLifeTime($array['cache_lifetime']);
        }

        return $request;
    }

    public function withId($id)
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function withPath($path)
    {
        $clone = clone $this;
        $clone->path = $path;

        return $clone;
    }

    public function withMethod($method)
    {
        $clone = clone $this;
        $clone->method = $method;

        return $clone;
    }

    public function withQuery($query)
    {
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    public function withBody($body)
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    public function withPriority($priority)
    {
        $clone = clone $this;
        $clone->priority = (int) $priority;

        return $clone;
    }

    public function withStopOnError($stopOnError)
    {
        $clone = clone $this;
        $clone->stopOnError = BooleanUtil::getBool($stopOnError);

        return $clone;
    }

    public function withDependsOn($requestId)
    {
        $clone = clone $this;
        $clone->dependsOn = $requestId;

        return $clone;
    }

    public function withCacheable($cacheable)
    {
        $clone = clone $this;
        $clone->cacheable = BooleanUtil::getBool($cacheable);

        return $clone;
    }

    public function toArray()
    {
        return [
            'id'             => $this->getId(),
            'path'           => $this->getPath(),
            'query'          => $this->getQuery(),
            'body'           => $this->getBody(),
            'method'         => $this->getMethod(),
            'priority'       => $this->getPriority(),
            'stopOnError'    => $this->isStopOnError(),
            'dependsOn'      => $this->getDependsOn(),
            'cacheable'      => $this->isCacheable(),
            'cache_lifetime' => $this->getCacheLifeTime(),
        ];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id ?: uniqid();
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path ?: '/';
    }

    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method ?: 'GET';
    }

    /**
     * @return array
     */
    public function getQuery() : array
    {
        return $this->query ?: [];
    }

    /**
     * @return array
     */
    public function getBody() : array
    {
        return $this->body ?: [];
    }

    /**
     * @return int
     */
    public function getPriority() : int
    {
        return (int) $this->priority;
    }

    /**
     * @return bool
     */
    public function isStopOnError() : bool
    {
        return BooleanUtil::getBool($this->stopOnError);
    }

    /**
     * @return string
     */
    public function getDependsOn()
    {
        return $this->dependsOn;
    }

    /**
     * @return bool
     */
    public function isCacheable()
    {
        return $this->cacheable;
    }
    
    public function withCacheLifeTime($lifeTime = 0)
    {
        $clone = clone $this;
        $clone->cacheLifeTime = (int) $lifeTime;

        return $clone;
    }

    public function getCacheLifeTime()
    {
        return (int) $this->cacheLifeTime;
    }
}
