<?php

include_once('base_loader.php');

$bots = TelegramBot::getAllBots($db, $config);

$statementUpdateLastMessageTime = $db->getConnect()->prepare('UPDATE `resources` SET `last_monitoring_info` = ? WHERE `id` = ?');
$statementGetAllResources = $db->getConnect()->prepare('SELECT * FROM `resources`');
$statementGetSubscribers = $db->getConnect()->prepare('SELECT * FROM `subscribers` WHERE `resource_id` = ?');

$statementGetAllResources->execute();
$resources = $statementGetAllResources->fetchAll(PDO::FETCH_ASSOC);

while(true) {
    echo "start scan bots resource\n";
    foreach ($bots as $bot) {
        if (!($bot instanceof TelegramBot)) {
            continue;
        }

        // get resource
        $botId = $bot->getId();
        echo "start scan bot {$botId}\n";
        foreach ($resources as &$resource) {
            if (empty($resource['bot_id']) || $resource['bot_id'] != $botId) {
                continue;
            }
            echo "start scan resource {$resource['id']}\n";
            $result = VKApi::getWallLastItemParsed($resource['subscribe_id'], 1);
            if ($result['date'] != $resource['last_monitoring_info']) {
                echo "resource {$resource['id']} send new message!!!\n";
                // update last monitoring info
                $statementUpdateLastMessageTime->execute(array($result['date'], $resource['id']));
                $resource['last_monitoring_info'] = $result['date'];

                // send message
                $statementGetSubscribers->execute(array($resource['id']));
                $subscribers = $statementGetSubscribers->fetchAll(PDO::FETCH_ASSOC);
                foreach($subscribers as $subscriber) {
                    $bot->getApi()->sendMessage($subscriber['chat_id'], $result['text']);
                }
            }
        }
    }
    echo "sleep 1 seconds\n";
    sleep(1);

}