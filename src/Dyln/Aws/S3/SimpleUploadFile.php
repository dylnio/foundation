<?php

namespace Dyln\Aws\S3;

class SimpleUploadFile
{
    protected $file;
    protected $newName;

    public function __construct($file, $newName = null)
    {
        $this->file = $file;
        $this->newName = $newName;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getSize()
    {
        return filesize($this->file);
    }

    public function getContentType()
    {
        return mime_content_type($this->file);
    }

    public function getFileName()
    {
        return basename($this->file);
    }

    public function getNewFileName()
    {
        return $this->newName;
    }
}
