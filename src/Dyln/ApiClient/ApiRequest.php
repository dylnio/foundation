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

    static public function fromArray($array = [])
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
        $clone->priority = (int)$priority;

        return $clone;
    }

    public function withStopOnError($stopOnError)
    {
        $clone = clone $this;
        $clone->stopOnError = BooleanUtil::getBool($stopOnError);

        return $clone;
    }

    public function toArray()
    {
        return [
            'id'          => $this->getId(),
            'path'        => $this->getPath(),
            'query'       => $this->getQuery(),
            'body'        => $this->getBody(),
            'method'      => $this->getMethod(),
            'priority'    => $this->getPriority(),
            'stopOnError' => $this->isStopOnError(),
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
    public function getMethod(): string
    {
        return $this->method ?: 'GET';
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query ?: [];
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body ?: [];
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return (int)$this->priority;
    }

    /**
     * @return bool
     */
    public function isStopOnError(): bool
    {
        return BooleanUtil::getBool($this->stopOnError);
    }

}