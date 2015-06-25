<?php

date_default_timezone_set('Europe/Moscow');
mb_internal_encoding('UTF-8');

include_once('config.php');

$db = new PDO("{$dataBaseType}:host={$dataBaseHost};dbname={$dataBaseBaseName}", $dataBaseUser, $dataBasePassword);
$db->query("SET NAMES {$dataBaseEncode}");

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


// Decrypt Function
function mc_decrypt($decrypt, $key){
    $decrypt = explode('|', $decrypt.'|');
    $decoded = base64_decode($decrypt[0]);
    $iv = base64_decode($decrypt[1]);
    if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
    $key = pack('H*', sprintf('%u', CRC32($key)));
    $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
    $mac = substr($decrypted, -64);
    $decrypted = substr($decrypted, 0, -64);
    $calcMac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
    if($calcMac!==$mac){ return false; }
    $decrypted = unserialize($decrypted);
    return $decrypted;
}


function _request($token, $command, $params = null)
{
    $url = "https://api.telegram.org/bot$token/$command";

    $resource = curl_init($url);
    curl_setopt_array($resource, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => array(
            'Host: api.telegram.org',
            'Content-Type: application/x-www-form-urlencoded'
        ),
        CURLOPT_POSTFIELDS => $params ? http_build_query($params) : null,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_CONNECTTIMEOUT => 600,
        CURLOPT_SSL_VERIFYPEER => false
    ));

    $body = curl_exec($resource);

    $errorNumber = curl_errno($resource);
    if ($errorNumber) {
        $errorMessage = curl_error($resource);
        error_log("error #{$errorNumber}: {$errorMessage}");
    }

    curl_close($resource);
    if (empty($body)) {
        return null;
    }
    $decodedBody = json_decode($body, true);
    if (is_null($decodedBody) || $decodedBody === false) {
        return null;
    }
    return $decodedBody;
}

$statement = $db->query('SELECT * FROM `bots`');
$bots = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($bots as &$bot) {
    $bot['token_encrypted'] = mc_decrypt($bot['token'], $secretKey);
    var_dump(_request($bot['token_encrypted'], 'getUpdates'));
}


echo '<br><br>';
//var_dump(_request($token, 'sendMessage', array('chat_id' => $chatId, 'text' => 'test message')));

function getLastItem($owner, $count)
{
    $url = "https://api.vk.com/method/wall.get?owner_id=-$owner&count=$count";

    $resource = curl_init($url);
    curl_setopt_array($resource, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => array(
            'Host: api.vk.com',
            'Content-Type: application/x-www-form-urlencoded'
        ),
        CURLOPT_TIMEOUT => 0,
        CURLOPT_CONNECTTIMEOUT => 600,
        CURLOPT_SSL_VERIFYPEER => false
    ));

    $body = curl_exec($resource);

    $errorNumber = curl_errno($resource);
    if ($errorNumber) {
        $errorMessage = curl_error($resource);
        error_log("error #{$errorNumber}: {$errorMessage}");
    }

    curl_close($resource);
    if (empty($body)) {
        return null;
    }
    $decodedBody = json_decode($body, true);
    if (is_null($decodedBody) || $decodedBody === false) {
        return null;
    }
    return $decodedBody;
}

function parseItem($item)
{
    if (empty($item)) {
        return null;
    }

    $item = $item['response'][1];

    $postId = $item['id'];
    $text = "Получено от: https://vk.com/oldlentach?w=wall-29534144_" . $postId . "\n\n";

    //Вставляем текст поста в сообщение, если он не умещается в превью. По каким признакам телеграм обрывает пост?
    if (trim(strlen($item['text']) > 120) || strpos($item['text'], "<br>") != false) {
        $text .= $item['text'] . "\n\n";
    }

    //Обработка аттачей
    if (isset($item['attachments']) && !empty($item['attachments'])) {
        $attachments = $item['attachments'];
        $photos = 0;
        $videos = 0;
        $audio = 0;
        foreach ($attachments as $attach) {
            if ($attach['type'] == 'page')
                $text .= "Подробнее в записи «" . $attach['page']['title'] . "» — " . $attach['page']['view_url'] . "\n";
            if ($attach['type'] == 'link')
                $text .= "Источник: " . $attach['link']['title'] . " — " . $attach['link']['url'] . "\n";
            if ($attach['type'] == 'video')
                $videos++;
            if ($attach['type'] == 'photo')
                $photos++;
            if ($attach['type'] == 'audio')
                $audio++;
        }
        if ($videos >= 1)
            $text .= "Содержит видеозаписи ($videos)\n";
        if ($photos >= 2)
            $text .= "Содержит изображения ($photos)\n";
        if ($audio >= 1)
            $text .= "Содержит аудиозаписи ($audio)\n";
    }

    $text = str_replace("<br>", "\n", $text);
    return $text;
}

//var_dump(_request($token, 'sendMessage', array('chat_id' => $chatId, 'text' => parseItem(getLastItem(29534144, 1)))));