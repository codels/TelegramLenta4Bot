<?php

abstract class HttpQuery
{
    public static function sendResultJson($url, $params = null)
    {
        $body = static::send($url, $params);

        if (empty($body)) {
            return null;
        }

        $decodedBody = json_decode($body, true);
        if (is_null($decodedBody) || $decodedBody === false) {
            return null;
        }

        return $decodedBody;
    }

    public static function send($url, $params = null)
    {
        $resource = curl_init($url);
        $urlInfo = parse_url($url);

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => array(
                'Host: ' . $urlInfo['host'],
                'Content-Type: application/x-www-form-urlencoded'
            ),
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CONNECTTIMEOUT => 600,
            CURLOPT_SSL_VERIFYPEER => false
        );

        if (!is_null($params)) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($params);
        }

        curl_setopt_array($resource, $options);

        $body = curl_exec($resource);

        $errorNumber = curl_errno($resource);
        if ($errorNumber) {
            $errorMessage = curl_error($resource);
            error_log("error #{$errorNumber}: {$errorMessage}");
            return null;
        }

        curl_close($resource);
        return $body;
    }
}