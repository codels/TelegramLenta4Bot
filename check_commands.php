<?php

include_once('base_loader.php');

while (true) {
    echo "\nstart scan bots commands";

    $statement = $db->getConnect()->query('SELECT * FROM `bots`');
    $botsInfo = $statement->fetchAll(PDO::FETCH_ASSOC);
    $bots = array();
    foreach ($botsInfo as &$botInfo) {
        $bots[] = new TelegramBotApi($botInfo, $config);
    }

    foreach ($bots as $bot) {
        if (!($bot instanceof TelegramBotApi)) {
            continue;
        }

        $botInfo = $bot->getInfo();
        $offset = $botInfo['last_update_id'];

        if (!isset($offset) || empty($offset)) {
            $lastUpdate = $bot->getUpdates();
            $lastUpdate = end($lastUpdate['result']);
            $statement = $db->getConnect()->prepare("UPDATE `bots` SET `last_update_id`=? WHERE `id`=?");
            $offset = $lastUpdate['update_id'] + 1;
            $statement->execute(array($offset, $botInfo['id']));
        }

        $userMsg = $bot->getUpdates($offset, 1, 50);
        //var_dump($userMsg);
        //TODO: Отлавливание ошибок по 'ok'
        $userMsg = $userMsg['result'][0];

        if (isset($userMsg['message']['text'])) {
            // check commands
            switch ($userMsg['message']['text']) {
                case '/list':
                case '/список': { //список доступных ресурсов
                    $statement = $db->getConnect()->query('SELECT * FROM `resources`');
                    $availableResources = $statement->fetchAll(PDO::FETCH_ASSOC);
                    $text = "Доступные ресурсы: \n";
                    foreach ($availableResources as $key => $resource) {
                        $text .= $key + 1 . ". " . $resource['subscribe_name'] . "\n";
                    }
                    $bot->sendMessage($userMsg['message']['chat']['id'], $text);
                    break;
                }
                case '/subscribe':
                case '/подписка': { //подписка (пока без параметров)
                    $statement = $db->getConnect()->prepare('SELECT * FROM `subscribers` WHERE `chat_id`=? AND `resource_id`=?');
                    $statement->execute(array($userMsg['message']['chat']['id'], 29534144)); //hardcoded lentach id
                    if ($statement->rowCount()) {
                        $bot->sendMessage($userMsg['message']['chat']['id'], "Похоже, что подписка уже оформлена");
                    } else { //подписка
                        $statement = $db->getConnect()->prepare('INSERT INTO `subscribers` VALUES (null, ?,?,1);');
                        $statement->execute(array($userMsg['message']['chat']['id'], 29534144)); //hdrdcoded lentach id
                        if ($statement->rowCount()) {
                            $bot->sendMessage($userMsg['message']['chat']['id'], "Подписка успешно оформлена!");
                        } else {
                            $bot->sendMessage($userMsg['message']['chat']['id'], "Возникла ошибка при оформлении подписки!");
                        }
                    }
                    break;
                }
                case '/help':
                case '/помощь': {
                    $bot->sendMessage($userMsg['message']['chat']['id'],
                        "Вот список команд, которые я понимаю:\n
                    /list (/список) — Список доступных подписок
                    /subscribe (/подписка) — подписаться
                    /about (/бот) — Обо мне
                    /help (/помощь) — Справка (вы ее читаете прямо сейчас!)");
                    break;
                }
                case '/about':
                case '/бот': {
                    $bot->sendMessage($userMsg['message']['chat']['id'],
                        "Привет человекам! Я — Бот и я буду собирать новости и сообщения из интересных вам источников!\n");
                    break;
                }
                default: {
                $bot->sendMessage($userMsg['message']['chat']['id'],
                    "Прошу прощения, " . $userMsg['message']['from']['first_name'] . ", но такой команды не существует.
                Используйте /help, чтобы получить список доступных команд");
                }
            }
        }

        //обновление счетчика
        $statement = $db->getConnect()->prepare("UPDATE `bots` SET `last_update_id`=? WHERE `id`=?");
        $offset++;
        $statement->execute(array($offset, $botInfo['id']));
        $userMsg = null;
    }
    echo "\nsleep 1 seconds";
    sleep(5);
}
