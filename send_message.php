<?php

include_once('base_loader.php');

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

$statement = $db->getConnect()->query('SELECT * FROM `bots`');
$bots = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($bots as &$bot) {
    $bot['token_encrypted'] = Encryption::decrypt($bot['token'], $config['secret_key']);
    $offset = $bot['last_update_id'];
    $userMsg = _request($bot['token_encrypted'], 'getUpdates', array('offset' => $offset));
    var_dump(_request($bot['token_encrypted'], 'getUpdates', array('offset' => $offset)));
    if (isset($userMsg['result'][0]['message']['text'])) {
        if ($userMsg['result'][0]['message']['text'] == '/get')
            _request($bot['token_encrypted'], 'sendMessage', array('chat_id' => $userMsg['result'][0]['message']['chat']['id'], 'text' => parseItem(getLastItem(29534144, 1))));
    }
    //todo: increment offset here
}


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
    var_dump($item);

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

    $text = preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $text);
    return $text;
}

//var_dump(_request($token, 'sendMessage', array('chat_id' => $chatId, 'text' => parseItem(getLastItem(29534144, 1)))));