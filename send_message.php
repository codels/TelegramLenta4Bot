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
    $url = "https://api.vk.com/method/wall.get?owner_id=-$owner&count=$count";

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
    $text = "Получено от: https://vk.com/oldlentach?w=wall-29534144_" . $postId . "\n\n";

    //Вставляем текст поста в сообщение, если он не умещается в превью. По каким признакам телеграм обрывает пост?
    if (trim(strlen($item['text']) > 120) || strpos($item['text'], "<br>") != false) {
        $text .= $item['text'] . "\n\n";
    }

    //Обработка аттачей
    if (isset($item['attachments']) && !empty($item['attachments'])) {
        $attachments = $item['attachments'];
        $photos = 0;
        $videos = 0;
        $audio = 0;
        foreach ($attachments as $attach) {
            if ($attach['type'] == 'page')
                $text .= "Подробнее в записи «" . $attach['page']['title'] . "» — " . $attach['page']['view_url'] . "\n";
            if ($attach['type'] == 'link')
                $text .= "Источник: " . $attach['link']['title'] . " — " . $attach['link']['url'] . "\n";
            if ($attach['type'] == 'video')
                $videos++;
            if ($attach['type'] == 'photo')
                $photos++;
            if ($attach['type'] == 'audio')
                $audio++;
        }
        if ($videos >= 1)
            $text .= "Содержит видеозаписи ($videos)\n";
        if ($photos >= 2)
            $text .= "Содержит изображения ($photos)\n";
        if ($audio >= 1)
            $text .= "Содержит аудиозаписи ($audio)\n";
    }

    $text = str_replace("<br>", "\n", $text);
    return $text;
}

var_dump(_request($token, 'sendMessage', array('chat_id' => $chatId, 'text' => parseItem(getLastItem(29534144, 1)))));