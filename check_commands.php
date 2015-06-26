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
            // check commands
            switch ($result['message']['text']) {
                case '/list':
                case '/список': { //список доступных ресурсов
                    $bot->sendResourceList($chatId);
                    break;
                }
                case '/subscribe':
                case '/подписка': { //подписка (пока без параметров)
                    $bot->subscribeByResourceName($chatId, 'Lenta4');
                    break;
                }
                case '/help':
                case '/помощь': {
                    $bot->getApi()->sendMessage($chatId,
                        "Вот список команд, которые я понимаю:\n
                    /list (/список) — Список доступных подписок
                    /subscribe (/подписка) — подписаться
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
