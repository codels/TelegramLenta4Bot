<?php

include_once('config.php');

function _request($token, $command, $params = null)
{
    $url = "https://api.telegram.org/bot$token/$command";

    $resource = curl_init($url);
    curl_setopt_array($resource, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => array('Host: api.telegram.org'),
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_TIMEOUT => 0,
        CURLOPT_CONNECTTIMEOUT => 600,
        CURLOPT_SSL_VERIFYPEER => false
    ));

    $body = curl_exec($resource);
    //$errorNumber = curl_errno($resource);
    //$errorMessage = curl_error($resource);
    //var_dump(array($errorMessage, $errorNumber));
    curl_close($resource);
    if (empty($body)) {
        return null;
    }
    $decodedBody = json_decode($body, true);
    if (is_null($decodedBody) || $decodedBody === false) {
        return null;
    }
    return $decodedBody;
}

var_dump(_request($token, 'getUpdates'));