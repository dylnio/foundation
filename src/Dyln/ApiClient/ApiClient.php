<?php

namespace Dyln\ApiClient;

use Dyln\ApiClient\ResponseBodyMiddleware\ConvertToMessageMiddleware;
use Dyln\ApiClient\ResponseBodyMiddleware\DebugbarMiddleware;
use Dyln\ApiClient\ResponseBodyMiddleware\JsonDecodeMiddleware;
use Dyln\ApiClient\ResponseBodyMiddleware\ResponseBodyMiddlewareInterface;
use Dyln\AppEnv;
use Dyln\Guzzle\Cookie\SessionCookieJar;
use Dyln\Message\MessageFactory;
use Dyln\Util\ArrayUtil;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class ApiClient
{
    /** @var  Client */
    protected $httpClient;
    /** @var  SessionCookieJar */
    protected $cookieJar;
    /** @var  string */
    protected $baseUrl;
    protected $defaultHeaders = [
        'User-Agent'   => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
        'Content-Type' => 'application/json',
        'Accept'       => 'application/json',
    ];
    /** @var ResponseBodyMiddlewareInterface[] */
    protected $responseBodyMiddlewares = [];

    /**
     * ApiService constructor.
     * @param $baseUrl
     * @param CookieJarInterface|null $cookieJar
     * @param array $options
     */
    public function __construct($baseUrl, CookieJarInterface $cookieJar = null, array $options = [])
    {
        $this->baseUrl = $baseUrl;
        $this->defaultHeaders = array_merge($this->defaultHeaders, ArrayUtil::getIn($options, ['headers'], []));
        $this->cookieJar = $cookieJar;
        $this->addResponseBodyMiddleware(new JsonDecodeMiddleware());
        $this->addResponseBodyMiddleware(new DebugbarMiddleware());
    }

    public function call($path, array $query = null, array $data = null, $method = 'GET', $options = [])
    {
        $this->addResponseBodyMiddleware(new ConvertToMessageMiddleware());
        if (AppEnv::isDebugEnabled()) {
            $query['XDEBUG_SESSION_START'] = 'PHPSTORM';
        }
        $headers = array_merge($this->defaultHeaders, ArrayUtil::getIn($options, ['headers'], []));
        $requestOptions = [
            'headers' => $headers,
        ];
        if ($query) {
            $requestOptions['query'] = $query;
        }
        if ($data) {
            $requestOptions['body'] = json_encode($data);
        }
        try {
            $res = $this->request($method, $path, $requestOptions);
            $body = (string)$res->getBody();
            $body = $this->applyResponseBodyMiddlewares($body);

            return $body;
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $responseBody = json_decode($responseBody, true);
            if (!$responseBody) {
                if ($e->getCode() == 401) {
                    $responseBody = ['message' => '401 Unauthorized'];
                } else if ($e->getCode() == 403) {
                    $responseBody = ['message' => '403 Unauthorized'];
                } else {
                    $responseBody = ['message' => 'Unkown error'];
                }
            }
            $message = $responseBody['message'];
            $extra = [
                'exception' => $responseBody['exception'] ?? null,
            ];

            return MessageFactory::error(['message' => $message, 'extra' => $extra]);
        } catch (ServerException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $responseBody = json_decode($responseBody, true);
            $message = $responseBody['message'];
            $extra = [
                'exception' => $responseBody['exception'] ?? null,
            ];

            return MessageFactory::error(['message' => $message, 'extra' => $extra]);
        } catch (\Exception $e) {
            return MessageFactory::error(['message' => $e->getMessage()]);
        }
    }

    public function addResponseBodyMiddleware(ResponseBodyMiddlewareInterface $middleware)
    {
        $this->responseBodyMiddlewares[get_class($middleware)] = $middleware;
    }

    private function getHttpClient()
    {
        if (!$this->httpClient) {
            $this->httpClient = new Client([]);
        }

        return $this->httpClient;
    }

    private function prepareUri($path)
    {
        $baseUrl = rtrim(str_replace(['http://', 'https://'], '', $this->baseUrl), '/');
        $path = ltrim($path, '/');

        return $baseUrl . '/' . $path;
    }

    private function getCookieJar()
    {
        return $this->cookieJar;
    }

    private function request($method, $path, $options)
    {
        $options['cookies'] = $this->getCookieJar();
        $res = $this->getHttpClient()->request($method, $this->prepareUri($path), $options);
        $cookieString = $res->getHeaderLine('Set-Cookie');
        $cookie = SetCookie::fromString($cookieString);
        $cookie->setDomain('0');
        $this->getCookieJar()->setCookie($cookie);

        return $res;
    }

    private function applyResponseBodyMiddlewares($body)
    {
        foreach ($this->responseBodyMiddlewares as $middleware) {
            $body = $middleware->execute($body);
        }

        return $body;
    }
}   