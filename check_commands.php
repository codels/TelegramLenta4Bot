<?php

include_once('base_loader.php');

$bots = TelegramBot::getAllBots($db, $config);

while (true) {
    echo "\nstart scan bots commands";

    foreach ($bots as $bot) {
        if (!($bot instanceof TelegramBot)) {
            continue;
        }

        $lastUpdateId = intval($bot->getLastUpdateId());
        if (empty($lastUpdateId)) {
            $response = $bot->getApi()->getUpdates();
            if (!isset($response['result']) || !count($response['result'])) {
                continue;
            }
            $lastUpdate = end($response['result']);
            $bot->setLastUpdateId($lastUpdate['update_id'] + 1);
            continue;
        }

        $response = $bot->getApi()->getUpdates($lastUpdateId, 1, 50);
        var_dump($response);
        if (!isset($response['result']) || !isset($response['result'][0])) {
            echo "result not found";
            continue;
        }

        $result = $response['result'][0];
        $updateId = intval($result['update_id']);
        $chatId = $result['message']['chat']['id'];

        // no new messages
        if ($updateId < $lastUpdateId) {
            // nothing
            continue;
        }

        if (isset($result['message']['text'])) {
            $command = explode(' ', $result['message']['text']);
            // check commands
            switch ($command[0]) {
                case '/list':
                case '/список': { //список доступных ресурсов
                    $bot->sendResourceList($chatId);
                    break;
                }
                case '/my':
                case '/мое': {
                    $subscriptionsList = $bot->getUserSubscriptions($chatId);
                    if (!empty($subscriptionsList)) {
                        $text = "Ваши ресурсы: \n";
                        foreach ($subscriptionsList as $key => $resource) {
                            $text .= $key + 1 . ". " . $resource['name'] . "\n";
                        }
                        $bot->getApi()->sendMessage($chatId, $text);
                    } else {
                        $bot->getApi()->sendMessage($chatId,
                            "Сейчас вы ни на что не подписаны");
                    }
                    break;
                }
                case '/subscribe':
                case '/подписка': {
                    if (isset($command[1])) {
                        $bot->subscribeByResourceName($chatId, $command[1]);
                    } else {
                        $bot->getApi()->sendMessage($chatId,
                            "Использование команды: /subscribe [название ресурса]\n
                            Наберите /list, чтобы увидеть список доступных ресурсов");
                    }
                    break;
                }
                case '/unsubscribe':
                case '/отписка': {
                    if (isset($command[1])) {
                        $bot->unsubscribeByResourceName($chatId, $command[1]);
                    } else {
                        $bot->getApi()->sendMessage($chatId,
                            "Использование команды: /unsubscribe [название ресурса]\n
                            Наберите /list, чтобы увидеть список доступных ресурсов");
                    }
                    break;
                }
                case
                '/help':
                case '/помощь': {
                    $bot->getApi()->sendMessage($chatId,
                        "Вот список команд, которые я понимаю:\n
                    /list (/список) — Список доступных подписок
                    /my (/мое) — список ресурсов, на которые вы подписаны
                    /subscribe (/подписка) [название ресурса] — подписаться
                    /unsubscribe (/отписка) [название ресурса] — отписка от ресурса
                    /about (/бот) — Обо мне
                    /help (/помощь) — Справка (вы ее читаете прямо сейчас!)");
                    break;
                }
                case '/about':
                case '/бот': {
                    $bot->getApi()->sendMessage($chatId,
                        "Привет человекам! Я — Бот и я буду собирать новости и сообщения из интересных вам источников!\n");
                    break;
                }
                default: {
                $bot->getApi()->sendMessage($chatId,
                    "Прошу прощения, " . $result['message']['from']['first_name'] . ", но такой команды не существует.
                Используйте /help, чтобы получить список доступных команд");
                }
            }
        }

        //обновление счетчика
        $lastUpdateId++;
        var_dump($lastUpdateId);
        $bot->setLastUpdateId($lastUpdateId);
        $userMsg = null;
    }
    echo "\nsleep 1 seconds";
    sleep(1);
}
