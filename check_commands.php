<?php

include_once('base_loader.php');

$statement = $db->getConnect()->query('SELECT * FROM `bots`');
$bots = $statement->fetchAll(PDO::FETCH_ASSOC);
$botsInfo = $statement->fetchAll(PDO::FETCH_ASSOC);
$bots = array();
foreach ($botsInfo as &$botInfo) {
    $bots[] = new TelegramBotApi($botInfo, $config);
}

while(true) {
    echo "\nstart scan bots commands";
    foreach ($bots as $bot) {
        if (!($bot instanceof TelegramBotApi)) {
            continue;
        }

        $botInfo = $bot->getInfo();
        $offset = $bot['last_update_id'];

        $userMsg = $bot->getUpdates($offset, 1, 50);
        var_dump($userMsg);

        if (isset($userMsg['result'][0]['message']['text'])) {
            // check commands
        }
        //todo: increment offset here
    }
    echo "\nsleep 1 seconds";
    sleep(1);
}
