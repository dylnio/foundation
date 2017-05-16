<?php

namespace Dyln\Queue\Client;

use Aws\Sqs\SqsClient;
use Dyln\Util\ArrayUtil;

class AmazonSqsClient
{
    protected $key;
    protected $secret;
    protected $region;
    /** @var  SqsClient */
    protected $sqs;
    private $version;

    /**
     * AmazonSqsClient constructor.
     * @param $key
     * @param $secret
     * @param $region
     * @param $version
     */
    public function __construct($key, $secret, $region, $version)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->region = $region;
        $this->version = $version;
        $this->sqs = new SqsClient([
            'credentials'       => [
                'key'    => $this->key,
                'secret' => $this->secret,
            ],
            'region'            => $this->region,
            'version'           => $this->version,
            'debug'             => false,
            'signature_version' => 'v4',
        ]);
    }

    public function publish($queueUrl, $message = [], $options = [])
    {
        $delay = ArrayUtil::getIn($options, 'delay', 0);
        if (!$delay) {
            $delay = 0;
        }
        if ($delay > 900) {
            $delay = 900;
        }
        $attributes = ArrayUtil::getIn($options, 'MessageAttributes', []);
        $this->sqs->sendMessage([
            'QueueUrl'          => $queueUrl,
            'MessageBody'       => json_encode($message),
            'DelaySeconds'      => $delay,
            'MessageAttributes' => $attributes,
        ]);
    }

    public function delete($msg, $queueUrl)
    {
        $this->sqs->deleteMessage([
            'QueueUrl'      => $queueUrl,
            'ReceiptHandle' => $msg['ReceiptHandle'],
        ]);
    }

    public function receiveMessage($args = [])
    {
        return $this->sqs->receiveMessage($args);
    }
}
