<?php

require_once('HttpQuery.php');
require_once('Encryption.php');

class TelegramBotApi
{
    private $_token = null;

    public function __construct($tokenCrypt, $config)
    {
        $this->_token = Encryption::decrypt($tokenCrypt, $config['secret_key']);
    }

    public function query($command, $params) {
        $url = "https://api.telegram.org/bot{$this->_token}/$command";
        return HttpQuery::sendResultJson($url, $params);
    }
}