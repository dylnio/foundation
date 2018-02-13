<?php

namespace Dyln\Util;

class MimeUtil
{
    public static function getFileMimeType($file)
    {
        $fi = finfo_open(FILEINFO_MIME, null);
        if (file_exists($file)) {
            return finfo_file($fi, $file, FILEINFO_MIME_TYPE);
        }

        return false;
    }

    public static function getExtensionFromMimeType($mimeType)
    {
        switch (strtolower($mimeType)) {
            case "image/jpg":
            case "image/jpeg":
            case "image/pjpeg":
                return 'jpg';
            case "image/png":
            case "image/x-png":
                return 'png';
            case "image/gif":
                return 'gif';
            default:
                return false;
        }
    }

    public static function returnMIMEType($filename)
    {
        $filename = self::removeQuerystringVar($filename, 'v');
        preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);
        if (isset($fileSuffix[1])) {
            switch (strtolower($fileSuffix[1])) {
                case "js":
                    return "application/x-javascript";
                case "json":
                    return "application/json";
                case "jpg":
                case "jpeg":
                case "jpe":
                    return "image/jpeg";
                case "png":
                case "gif":
                case "bmp":
                case "tiff":
                    return "image/" . strtolower($fileSuffix[1]);
                case "css":
                    return "text/css";
                case "xml":
                    return "application/xml";
                case "doc":
                case "docx":
                    return "application/msword";
                case "xls":
                case "xlt":
                case "xlm":
                case "xld":
                case "xla":
                case "xlc":
                case "xlw":
                case "xll":
                    return "application/vnd.ms-excel";
                case "ppt":
                case "pps":
                    return "application/vnd.ms-powerpoint";
                case "rtf":
                    return "application/rtf";
                case "pdf":
                    return "application/pdf";
                case "html":
                case "htm":
                case "php":
                    return "text/html";
                case "txt":
                    return "text/plain";
                case "mpeg":
                case "mpg":
                case "mpe":
                    return "video/mpeg";
                case "mp3":
                    return "audio/mpeg3";
                case "wav":
                    return "audio/wav";
                case "aiff":
                case "aif":
                    return "audio/aiff";
                case "avi":
                    return "video/msvideo";
                case "wmv":
                    return "video/x-ms-wmv";
                case "mov":
                    return "video/quicktime";
                case "zip":
                    return "application/zip";
                case "tar":
                    return "application/x-tar";
                case "swf":
                    return "application/x-shockwave-flash";
                case "svg":
                    return "image/svg+xml";
                case "woff":
                    return "application/x-woff";
                case "eot":
                    return "application/vnd.ms-fontobject";
                case "otf":
                case "ttf":
                    return "application/octet-stream";
                default:
                    return "unknown/" . trim($fileSuffix[1], ".");
            }
        } else {
            return "unknown/unknown";
        }
    }

    public static function removeQuerystringVar($url, $key)
    {
        $url = preg_replace('/(?<=&|\?)' . $key . '(=[^&]*)?(&|$)/', '', $url . '&');
        $url = substr($url, 0, -1);

        return $url;
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    public static function getFileExtension($fileName)
    {
        return strtolower(substr(strrchr($fileName, '.'), 1));
    }

    /**
     * @param string $url
     *
     * @return mixed
     */
    public static function getRemoteMimeType($url)
    {
        $url = str_replace(' ', '%20', trim($url));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_exec($ch);
        $ret = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return $ret;
    }

    /**
     * @param string $url
     * @param array $allowedMimeTypes
     * @param bool $cleanForImages
     *
     * @return array
     */
    public static function getRemoteMimeTypeAndContent($url, $allowedMimeTypes = [], $cleanForImages = true)
    {
        $url = str_replace(' ', '%20', trim($url));
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10000);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1");
            if (strpos($url, 'gilt.com') !== false) {
                curl_setopt($ch, CURLOPT_USERAGENT, "curl/7.43.0");
            }
            $arrayCiphers = [
                'DHE-RSA-AES256-SHA',
                'DHE-DSS-AES256-SHA',
                'AES256-SHA:KRB5-DES-CBC3-MD5',
                'KRB5-DES-CBC3-SHA',
                'EDH-RSA-DES-CBC3-SHA',
                'EDH-DSS-DES-CBC3-SHA',
                'DES-CBC3-SHA:DES-CBC3-MD5',
                'DHE-RSA-AES128-SHA',
                'DHE-DSS-AES128-SHA',
                'AES128-SHA:RC2-CBC-MD5',
                'KRB5-RC4-MD5:KRB5-RC4-SHA',
                'RC4-SHA:RC4-MD5:RC4-MD5',
                'KRB5-DES-CBC-MD5',
                'KRB5-DES-CBC-SHA',
                'EDH-RSA-DES-CBC-SHA',
                'EDH-DSS-DES-CBC-SHA:DES-CBC-SHA',
                'DES-CBC-MD5:EXP-KRB5-RC2-CBC-MD5',
                'EXP-KRB5-DES-CBC-MD5',
                'EXP-KRB5-RC2-CBC-SHA',
                'EXP-KRB5-DES-CBC-SHA',
                'EXP-EDH-RSA-DES-CBC-SHA',
                'EXP-EDH-DSS-DES-CBC-SHA',
                'EXP-DES-CBC-SHA',
                'EXP-RC2-CBC-MD5',
                'EXP-RC2-CBC-MD5',
                'EXP-KRB5-RC4-MD5',
                'EXP-KRB5-RC4-SHA',
                'EXP-RC4-MD5:EXP-RC4-MD5',
            ];
            curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, implode(':', $arrayCiphers));

            $content = curl_exec($ch);
            if ($content === false) {
                throw new \Exception("Failed curl request: " . "Url: " . var_export($url, true) . "\n");
            }
            $mimeType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            if (empty($mimeType)) {
                throw new \Exception("Failed getting mime type:" . "Url: " . var_export($url, true) . "\n" . "Received Mime type: " . var_export($mimeType, true) . "\n" . "in array of allowed: " . var_export($allowedMimeTypes, true));
            }
            //cleanup the mime type a bit. We are interested in the first part of it
            if ($cleanForImages) {
                if (strpos($mimeType, ";") !== false) {
                    $parts = explode(";", $mimeType);
                    foreach ($parts as $part) {
                        if (strpos($part, "image") !== false) {
                            //this is the image part
                            $mimeType = $part;
                            continue;
                        }
                    }
                }
                if (strpos($mimeType, ",") !== false) {
                    $parts = explode(",", $mimeType);
                    foreach ($parts as $part) {
                        if (strpos($part, "image") !== false) {
                            //this is the image part
                            $mimeType = $part;
                            continue;
                        }
                    }
                }
            }

            $httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpReturnCode >= 400) {
                throw new \Exception("Failed curling url: " . var_export($url, true));
            }
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($content, $headerSize);
            curl_close($ch);
            if (!empty($allowedMimeTypes)) {
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    $tempFile = tempnam("/tmp/", "image-queue-");
                    file_put_contents($tempFile, $body);
                    $mimeType = mime_content_type($tempFile);
                    unlink($tempFile);
                    if (!in_array($mimeType, $allowedMimeTypes)) {
                        throw new \Exception("Failed matching mime type: \n" . "Url: " . var_export($url, true) . "\n" . "Received Mime type: " . var_export($mimeType, true) . "\n" . "in array of allowed: " . var_export($allowedMimeTypes, true));
                    }
                }
            }
        } catch (\Exception $e) {
            if (isset($ch) && is_resource($ch)) {
                curl_close($ch);
            }

            return [];
        }

        $ret = ['content' => $body, 'mimeType' => $mimeType, 'extension' => self::getExtensionFromMimeType($mimeType)];

        return $ret;
    }
}
