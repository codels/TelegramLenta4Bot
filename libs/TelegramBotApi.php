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

    public function getUpdates($offset = null, $limit = 100, $timeout = 0) {
        $options = array('limit' => $limit, 'timeout' => $timeout);
        if (!is_null($offset)) {
            $options['offset'] = $offset;
        }
        return static::query('getUpdates', $options);
    }

    public function sendMessage($chatId, $text, $disableWebPagePreview = false, $replyToMessageId = 0, $replyMarkup = null) {
        $options = array(
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => $disableWebPagePreview,
            'reply_to_message_id' => $replyToMessageId
        );
        if (!is_null($replyMarkup)) {
            $options['reply_markup'] = $replyMarkup;
        }
        return static::query('sendMessage', $options);
    }
}