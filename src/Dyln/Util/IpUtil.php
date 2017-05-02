<?php

namespace Dyln\Util;

class IpUtil
{
    static public function getLocationFromIp($ipAddress = false)
    {
        if (!$ipAddress) {
            $ipAddress = self::getRealIp();
        }

        $city = null;
        $country = null;

        $location = false;
        if (function_exists('geoip_record_by_name')) {
            $oe = set_error_handler('process_error_backtrace_null');
            $location = @geoip_record_by_name($ipAddress);
            set_error_handler($oe);
        }
        if ($location && !empty($location['city'])) {
            $city = $location['city'];
        }
        if ($location && !empty($location['country_name'])) {
            $country = $location['country_name'];
        }

        if ($city == '0') {
            $city = null;
        }
        if ($country == '0') {
            $country = null;
        }

        return [
            'country' => utf8_encode($country),
            'city'    => utf8_encode($city),
        ];
    }

    static public function getRealIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR_IP'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR_IP'];
        } elseif (isset($_SERVER['FORWARDED_FOR_IP'])) {
            $ip = $_SERVER['FORWARDED_FOR_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            return false;
        }
        $ip = explode(",", $ip);
        $ip = $ip[0];

        return $ip;
    }

    static public function isProxyUser()
    {
        if (isset($_SERVER['HTTP_VIA']) || isset($_SERVER['HTTP_X_FORWARDED_FOR']) || isset($_SERVER['HTTP_FORWARDED_FOR']) || isset($_SERVER['HTTP_X_FORWARDED']) || isset($_SERVER['HTTP_FORWARDED']) || isset($_SERVER['HTTP_CLIENT_IP']) || isset($_SERVER['HTTP_FORWARDED_FOR_IP']) || isset($_SERVER['VIA']) || isset($_SERVER['X_FORWARDED_FOR']) || isset($_SERVER['FORWARDED_FOR']) || isset($_SERVER['X_FORWARDED FORWARDED']) || isset($_SERVER['CLIENT_IP']) || isset($_SERVER['FORWARDED_FOR_IP']) || isset($_SERVER['HTTP_PROXY_CONNECTION'])
        ) {
            return true;
        } else {
            return false;
        }
    }

}