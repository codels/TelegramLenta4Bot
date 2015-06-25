<?php

date_default_timezone_set('Europe/Moscow');
mb_internal_encoding('UTF-8');

require_once('config.php');

if (!$debug) {
    exit('debug mode off');
}

$db = new PDO("{$dataBaseType}:host={$dataBaseHost};dbname={$dataBaseBaseName}", $dataBaseUser, $dataBasePassword);
$db->query("SET NAMES {$dataBaseEncode}");

if (empty($_REQUEST['bot_name'])) {
    exit('bot name is empty');
}

if (empty($_REQUEST['token'])) {
    exit('token is empty');
}

$botName = $_REQUEST['bot_name'];
$token = $_REQUEST['token'];

$statementSearch = $db->prepare('SELECT 1 FROM `bots` WHERE `bot_name` = ?');
$statementSearch->execute(array($botName));
if ($statementSearch->rowCount()) {
    exit('bot already exists');
}

// Encrypt Function
function mc_encrypt($encrypt, $key){
    $encrypt = serialize($encrypt);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
    $key = pack('H*',  sprintf('%u', CRC32($key)));
    $mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
    $passCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
    $encoded = base64_encode($passCrypt).'|'.base64_encode($iv);
    return $encoded;
}

$tokenCrypt = mc_encrypt($token, $secretKey);

$statement = $db->prepare('INSERT INTO `bots` (`bot_name`, `token`) VALUES (?, ?)');
$statement->execute(array($botName, $tokenCrypt));
if ($statement->rowCount()) {
    exit('ok');
} else {
    exit('fail');
}
