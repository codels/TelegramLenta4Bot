<?php

date_default_timezone_set('Europe/Moscow');
mb_internal_encoding('UTF-8');

require_once('libs/DB.php');
require_once('libs/Encryption.php');
require_once('libs/HttpQuery.php');
require_once('libs/TelegramBotApi.php');

$config = include('config.php');
$db = new DB($config['data_base']);