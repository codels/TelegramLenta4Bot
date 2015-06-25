<?php

if (empty($_REQUEST['set_web_hook'])) {
    exit('web hook empty is empty');
}

include_once('base_loader.php');

$statement = $db->getConnect()->query('SELECT * FROM `bots`');
$bots = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($bots as &$bot) {
    $bot['api'] = new TelegramBotApi($bot['token'], $config);
    if (!($bot['api'] instanceof TelegramBotApi)) {
        continue;
    }

    var_dump($bot['api']->setWebHook($_REQUEST['set_web_hook']));
}