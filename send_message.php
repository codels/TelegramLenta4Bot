<?php

date_default_timezone_set('Europe/Moscow');
mb_internal_encoding('UTF-8');

include_once('config.php');

function _request($token, $command, $params = null)
{
    $url = "https://api.telegram.org/bot$token/$command";

    $resource = curl_init($url);
    curl_setopt_array($resource, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => array(
            'Host: api.telegram.org',
            'Content-Type: application/x-www-form-urlencoded'
        ),
        CURLOPT_POSTFIELDS => $params ? http_build_query($params) : null,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_CONNECTTIMEOUT => 600,
        CURLOPT_SSL_VERIFYPEER => false
    ));

    $body = curl_exec($resource);

    $errorNumber = curl_errno($resource);
    if ($errorNumber) {
        $errorMessage = curl_error($resource);
        error_log("error #{$errorNumber}: {$errorMessage}");
    }

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

//var_dump(_request($token, 'getUpdates'));
echo '<br><br>';
//var_dump(_request($token, 'sendMessage', array('chat_id' => $chatId, 'text' => 'test message')));

function getLastItem($owner, $count)
{
    $url = "https://api.vk.com/method/wall.get?owner_id=-$owner&count=$count&offset=5";

    $resource = curl_init($url);
    curl_setopt_array($resource, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => array(
            'Host: api.vk.com',
            'Content-Type: application/x-www-form-urlencoded'
        ),
        CURLOPT_TIMEOUT => 0,
        CURLOPT_CONNECTTIMEOUT => 600,
        CURLOPT_SSL_VERIFYPEER => false
    ));

    $body = curl_exec($resource);

    $errorNumber = curl_errno($resource);
    if ($errorNumber) {
        $errorMessage = curl_error($resource);
        error_log("error #{$errorNumber}: {$errorMessage}");
    }

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

function parseItem($item)
{
    if (empty($item)) {
        return null;
    }

    $item = $item['response'][1];

    $postId = $item['id'];
    $text = "Получено от: https://vk.com/oldlentach?w=wall-29534144_" . $postId . "\n";

    //Вставляем текст поста в сообщение, если он не умещается в превью. По каким признакам телеграм обрывает пост?
    if (trim(strlen($item['text']) > 120) || strpos($item['text'], "<br>") != false) {
        $text = $item['text']."\n\n".$text;
    }

    //Обработка аттачей
    if (isset($item['attachments']) && !empty($item['attachments'])) {
        $attachments = $item['attachments'];
        foreach ($attachments as $attach) {
            if ($attach['type'] == 'page')
                $text .= "Подробнее в записи «" . $attach['page']['title'] . "» — " . $attach['page']['view_url'] . "\n";
            if ($attach['type'] == 'link')
                $text .= "Источник: " . $attach['link']['title'] . " — " . $attach['link']['url'] . "\n";
        }
    }

    return $text;
}

var_dump(_request($token, 'sendMessage', array('chat_id' => $chatId, 'text' => parseItem(getLastItem(29534144, 1)))));