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

    public function uploadToS3($file, $acl = 'public-read', $region = null, $bucket = null) : Message
    {
        if (!$bucket) {
            $bucket = $this->defaultBucket;
        }
        if (!$region) {
            $region = $this->defaultRegion;
        }
        $filename = $file['tmp_name']; // path inclusive
        $niceName = $file['name'];
        $contentType = $file['type'];
        $size = $file['size'];
        if (!$contentType === null) {
            $contentType = mime_content_type($file);
        }
        try {
            $response = $this->s3Client->putObject([
                'Bucket'        => $bucket,
                'Key'           => $niceName,
                'Body'          => fopen($filename, 'rb'),
                'ContentType'   => $contentType,
                'ContentLength' => $size,
                'ACL'           => $acl,
                '@region'       => $region,
            ]);
        } catch (\Exception $e) {
            return MessageFactory::error(['message' => $e->getMessage()]);
        }

        return MessageFactory::success([$response]);
    }
}
