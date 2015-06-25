<?php

date_default_timezone_set('Europe/Moscow');
mb_internal_encoding('UTF-8');

// console version
$isCLI = ( php_sapi_name() == 'cli' );
if ($isCLI) {
    set_time_limit(0);
}

require_once('libs/DB.php');
require_once('libs/Encryption.php');
require_once('libs/HttpQuery.php');
require_once('libs/TelegramBotApi.php');
require_once('libs/VKApi.php');

$config = include('config.php');
$db = new DB($config['data_base']);