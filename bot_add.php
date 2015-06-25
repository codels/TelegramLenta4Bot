<?php

if (empty($_REQUEST['bot_name'])) {
    exit('bot name is empty');
}

if (empty($_REQUEST['token'])) {
    exit('token is empty');
}

include_once('base_loader.php');

if (!$config['debug']) {
    exit('debug mode off');
}

$botName = $_REQUEST['bot_name'];
$token = $_REQUEST['token'];

$statementSearch = $db->getConnect()->prepare('SELECT 1 FROM `bots` WHERE `bot_name` = ?');
$statementSearch->execute(array($botName));
if ($statementSearch->rowCount()) {
    exit('bot already exists');
}

$tokenCrypt = Encryption::encrypt($token, $config['secret_key']);

$statement = $db->getConnect()->prepare('INSERT INTO `bots` (`bot_name`, `token`) VALUES (?, ?)');
$statement->execute(array($botName, $tokenCrypt));
if ($statement->rowCount()) {
    exit('ok');
} else {
    exit('fail');
}
