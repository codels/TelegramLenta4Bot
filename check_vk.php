<?php

include_once('base_loader.php');

$statement = $db->getConnect()->query('SELECT * FROM `bots`');
$botsInfo = $statement->fetchAll(PDO::FETCH_ASSOC);
$bots = array();
foreach ($botsInfo as &$botInfo) {
    $bots[] = new TelegramBotApi($botInfo, $config);
}

while(true) {
    echo "\nstart scan bots resource";
    foreach ($bots as $bot) {
        // get resource

        // scan new message on resource

        /*
     * if new message
      foreach subscribers ...
            $bot->sendMessage(
                $userMsg['result'][0]['message']['chat']['id'],
                VKApi::getWallLastItemParsed(29534144, 1)
            );
     */
    }
    echo "\nsleep 1 seconds";
    sleep(1);

}