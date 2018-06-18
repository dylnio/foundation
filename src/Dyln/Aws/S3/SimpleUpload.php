<?php

namespace Dyln\Aws\S3;

use Aws\S3\S3ClientInterface;
use Aws\S3\S3MultiRegionClient;
use Dyln\Message\Message;
use Dyln\Message\MessageFactory;

class SimpleUpload
{
    /** @var S3MultiRegionClient $s3Client */
    protected $s3Client;
    protected $defaultBucket;
    protected $defaultRegion;

    public function __construct(S3ClientInterface $s3Client, $defaultBucket = null, $defaultRegion = null)
    {
        $this->s3Client = $s3Client;
        $this->defaultBucket = $defaultBucket;
        $this->defaultRegion = $defaultRegion;
    }

    public function uploadToS3(SimpleUploadFile $file, $acl = 'public-read', $region = null, $bucket = null) : Message
    {
        if (!$bucket) {
            $bucket = $this->defaultBucket;
        }
        if (!$region) {
            $region = $this->defaultRegion;
        }
        try {
            $response = $this->s3Client->putObject([
                'Bucket'        => $bucket,
                'Key'           => $file->getNewFileName() ?? $file->getFileName(),
                'Body'          => fopen($file->getFile(), 'rb'),
                'ContentType'   => $file->getContentType(),
                'ContentLength' => $file->getSize(),
                'ACL'           => $acl,
                '@region'       => $region,
            ]);
        } catch (\Exception $e) {
            return MessageFactory::error(['message' => $e->getMessage()]);
        }

        return MessageFactory::success([$response]);
    }
}
