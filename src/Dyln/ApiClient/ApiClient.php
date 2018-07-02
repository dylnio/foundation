<?php

namespace Dyln\ApiClient;

use Doctrine\Common\Cache\CacheProvider;
use Dyln\ApiClient\Enum\Events;
use Dyln\ApiClient\ResponseBodyMiddleware\ConvertToMessageMiddleware;
use Dyln\ApiClient\ResponseBodyMiddleware\DebugbarMiddleware;
use Dyln\ApiClient\ResponseBodyMiddleware\JsonDecodeMiddleware;
use Dyln\ApiClient\ResponseBodyMiddleware\ResponseBodyMiddlewareInterface;
use Dyln\AppEnv;
use Dyln\Collection\Collection;
use Dyln\Config\Config;
use Dyln\Debugbar\Debugbar;
use Dyln\Event\Emitter;
use Dyln\Guzzle\Cookie\SessionCookieJar;
use Dyln\Http\Header\ExtraHeaderMiddleware;
use Dyln\Message\Message;
use Dyln\Message\MessageFactory;
use Dyln\Util\ArrayUtil;
use Dyln\Util\IpUtil;
use Dyln\Util\Timer;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use Namshi\Cuzzle\Formatter\CurlFormatter;
use function Dyln\getin;

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
    protected $clientToken = null;
    protected $clientSecret = null;
    protected $userToken = null;
    /**
     * @var Emitter|null
     */
    protected $emitter;
    /** @var CacheProvider */
    protected $cacheProvider;

    /**
     * ApiService constructor.
     * @param $baseUrl
     * @param CookieJarInterface|null $cookieJar
     * @param Emitter|null $emitter
     * @param array $options
     */
    public function __construct(
        $baseUrl,
        CookieJarInterface $cookieJar = null,
        Emitter $emitter = null,
        array $options = [],
        CacheProvider $cacheProvider = null
    ) {
        $this->baseUrl = $baseUrl;
        $this->defaultHeaders = array_merge($this->defaultHeaders, ArrayUtil::getIn($options, ['headers'], []));
        $this->cookieJar = $cookieJar;
        $this->addResponseBodyMiddleware(new JsonDecodeMiddleware());
        $this->addResponseBodyMiddleware(new DebugbarMiddleware());
        $this->clientToken = getin($options, 'client.token');
        $this->clientSecret = getin($options, 'client.secret');
        $this->userToken = getin($options, 'user.token');
        $this->emitter = $emitter;
        $this->cacheProvider = $cacheProvider;
    }

    public function call($path, array $query = null, array $data = null, $method = 'GET', $options = []) : Message
    {
        $eventParams = ['path' => $path, 'query' => $query, 'data' => $data, 'method' => $method, 'options' => $options];
        $this->emitter->emit(Events::CALL_BEGIN, $eventParams);
        if (!$query) {
            $query = [];
        }
        $this->addResponseBodyMiddleware(new ConvertToMessageMiddleware());
        if (AppEnv::isXdebugEnabled()) {
            $query['XDEBUG_SESSION_START'] = 'PHPSTORM';
        }
        if (AppEnv::isDebugEnabled()) {
            $query['debug'] = Config::get('app.debug.url_key');
        }
        if (AppEnv::isCacheResetEnabled()) {
            $query['reset'] = Config::get('app.debug.url_key');
        }
        $headers = array_merge($this->defaultHeaders, ArrayUtil::getIn($options, ['headers'], []));
        $headers['x-SHOPCADE-USER-IP'] = IpUtil::getRealIp();
        $headers['x-SHOPCADE-USER-AGENT'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        if ($this->clientToken) {
            $headers['X-SHOPCADE-CLIENT-TOKEN'] = $this->clientToken;
            $headers['X-SHOPCADE-CLIENT-SIGNATURE'] = $this->calculateSecret($path, $method, $this->clientSecret);
            $headers['X-SHOPCADE-USER-TOKEN'] = $this->userToken;
        }
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
            $responseHeaders = $res->getHeaders();
            foreach ($responseHeaders as $key => $value) {
                if (strpos($key, '__') === 0) {
                    $key .= '_API';
                    ExtraHeaderMiddleware::$headers[$key] = $value[0];
                }
            }
            $body = (string) $res->getBody();
            $body = $this->applyResponseBodyMiddlewares($body);

            return $body;
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $responseBody = json_decode($responseBody, true);
            if (!$responseBody) {
                if ($e->getCode() == 401) {
                    $responseBody = ['message' => '401 Unauthorized'];
                } elseif ($e->getCode() == 403) {
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
            $response = json_decode($responseBody, true);
            if (!is_array($response)) {
                $message = $responseBody;
            } else {
                $message = $response['message'];
            }
            $extra = [
                'exception' => $response['exception'] ?? $response['error'] ?? null,
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

    private function prepareUri($path, $query = [])
    {
        $baseUrl = rtrim(str_replace(['http://', 'https://'], '', $this->baseUrl), '/');
        $path = ltrim($path, '/');
        if ($query) {
            $path .= '?' . http_build_query($query);
        }

        return $baseUrl . '/' . $path;
    }

    private function getCookieJar()
    {
        return $this->cookieJar;
    }

    private function request($method, $path, $options)
    {
        $options['cookies'] = $this->getCookieJar();
        $query = $options['query'] ?? [];
        Timer::start();
        $request = new Request($method, $this->prepareUri($path, $query), $options['headers'] ?? [], $options['body'] ?? null);
        $res = $this->getHttpClient()->send($request);
        $time = Timer::result();
        Debugbar::add('ApiRequest', [
            'curl'  => (new CurlFormatter(9999))->format($request, $options),
            'start' => Timer::getStart(),
            'end'   => Timer::getEnd(),
            'time'  => $time,
        ]);
//        $body = (string) $res->getBody();
//        Debugbar::add('ApiResponse', [
//            'body' => $body,
//        ]);
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

    public function bulkCall($calls = []) : Message
    {
        if (!$calls) {
            throw new \Exception('Empty calls');
        }
        $request = [];
        $cachedResponses = [];
        /** @var ApiRequest $call */
        foreach ($calls as $call) {
            if ($call->isCacheable()) {
                $cachedResponse = $this->getResponseFromCache($call->getId());
                if ($cachedResponse) {
                    $cachedResponses[$call->getId()] = $cachedResponse;
                    continue;
                }
            }
            $request[] = $call->toArray();
        }
        if ($request) {
            $response = $this->call('/', null, ['requests' => $request], 'POST', ['headers' => ['X-SHOPCADE-MULTI' => true]]);
        } else {
            $response = MessageFactory::success(['payload' => []]);
        }
        if ($response->isSuccess()) {
            $payload = $response->getData()['payload'];
            foreach ($cachedResponses as $id => $cachedResponse) {
                $payload[$id] = $cachedResponse;
            }
            $bulkResponse = new Collection();
            foreach ($payload as $id => $_payload) {
                if (!isset($_payload['success'])) {
                    $_payload['success'] = false;
                }
                $call = Collection::create($calls)->find(function (ApiRequest $request) use ($id) {
                    return $request->getId() == $id;
                });
                if ($call && $call->isCacheable()) {
                    $this->saveResponseToCache($call->getId(), $_payload, $call->getCacheLifeTime());
                }
                if ($_payload['success']) {
                    $bulkResponse->add(MessageFactory::success($_payload), (string) $id);
                } else {
                    $bulkResponse->add(MessageFactory::error($_payload), (string) $id);
                }
            }
            $response->addData('bulk_response', $bulkResponse);
        }

        return $response;
    }

    protected function isBulkCall($body = [])
    {
        return isset($body['requests']);
    }

    private function calculateSecret($path, $method, $secret)
    {
        $time = time();
        $nonce = random_int(10000, 90000);
        $message = $method . '+' . urlencode(urldecode(trim($path, '/'))) . '+' . (string) $time . '+' . (string) $nonce;
        $digest = hash_hmac('sha256', $message, $secret) . ':' . $time . ':' . $nonce;

        return $digest;
    }

    private function getResponseFromCache($key)
    {
        if (!$this->cacheProvider) {
            return null;
        }

        return $this->cacheProvider->fetch($key);
    }

    private function saveResponseToCache($key, $data, $lifeTime = 0)
    {
        if ($this->cacheProvider) {
            $this->cacheProvider->save($key, $data, $lifeTime);
        }
    }
}
