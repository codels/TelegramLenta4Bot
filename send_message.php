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

    $userMsg = $bot['api']->getUpdates($offset);
    var_dump($userMsg);

    if (isset($userMsg['result'][0]['message']['text'])) {
        if ($userMsg['result'][0]['message']['text'] == '/get')
            $bot['api']->sendMessage(
                $userMsg['result'][0]['message']['chat']['id'],
                VKApi::getWallLastItemParsed(29534144, 1)
            );
    }
    //todo: increment offset here
}
