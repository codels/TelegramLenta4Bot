<?php

include_once('base_loader.php');

$statement = $db->getConnect()->query('SELECT * FROM `bots`');
$bots = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($bots as &$bot) {
    $bot['api'] = new TelegramBotApi($bot['token'], $config);
    if (!($bot['api'] instanceof TelegramBotApi)) {
        continue;
    }

    $offset = $bot['last_update_id'];

    $userMsg = $bot['api']->query('getUpdates', array('offset' => $offset));
    var_dump($userMsg);

    if (isset($userMsg['result'][0]['message']['text'])) {
        if ($userMsg['result'][0]['message']['text'] == '/get')
            $bot['api']->query('sendMessage', array(
                'chat_id' => $userMsg['result'][0]['message']['chat']['id'],
                'text' => parseItem(VKApi::getWallLastItem(29534144, 1)))
            );
    }
    //todo: increment offset here
}

echo '<br><br>';

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