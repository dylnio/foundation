<?php

namespace Dyln\Wrike\Client;

use Dyln\Message\MessageFactory;
use GuzzleHttp\Client as HttpClient;

class Client
{
    const BASE_URI = 'https://www.wrike.com/api/v3/';
    /**
     * @var HttpClient
     */
    protected $httpClient;
    protected $token;

    /**
     * Client constructor.
     * @param $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    public function makeCall($path, $data = null, $method = 'GET')
    {
        try {
            $method = strtoupper($method);
            $options = [
                'headers' => $this->getHeaders(),
                'body'    => json_encode($data),
            ];
            if ($method == 'POST') {
                $result = $this->getHttpClient()->request('POST', $this->prepareUrl($path), $options);
            } elseif ($method == 'PUT') {
                $result = $this->getHttpClient()->request('PUT', $this->prepareUrl($path), $options);
            } elseif ($method == 'GET') {
                $result = $this->getHttpClient()->request('GET', $this->prepareUrl($path), $options);
            } elseif ($method == 'DELETE') {
                $result = $this->getHttpClient()->request('DELETE', $this->prepareUrl($path), $options);
            } else {
                throw new \Exception('Method ' . $method . ' has not been implemented yet');
            }
            $body = json_decode($result->getBody(), true);
            $payload = [
                'body' => $body,
            ];

            return MessageFactory::success($payload);
        } catch (\Exception $e) {
            return MessageFactory::error(['message' => $e->getMessage(), 'code' => $e->getCode()]);
        }
    }

    private function prepareUrl($path)
    {
        $path = trim(trim($path), '/');

        return self::BASE_URI . $path;
    }

    private function getHeaders()
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->token,
        ];

        return $headers;
    }

    private function getHttpClient()
    {
        if (!$this->httpClient) {
            $config = [
                'verify'          => false,
                'allow_redirects' => true,
                'headers'         => [
                    'User-Agent'                => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/602.4.8 (KHTML, like Gecko) Version/10.0.3 Safari/602.4.8',
                    'DNT'                       => '1',
                    'Accept-Encoding'           => 'gzip, deflate, sdch',
                    'Accept-Language'           => 'en-GB,en;q=0.8,en-US;q=0.6,tr;q=0.4',
                    'Upgrade-Insecure-Requests' => '1',
                    'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Cache-Control'             => 'max-age=0',
                    'Connection'                => 'keep-alive',
                    'Content-Type'              => 'application/json; charset=utf-8',
                ],
            ];
            $this->httpClient = new HttpClient($config);
        }

        return $this->httpClient;
    }
}
