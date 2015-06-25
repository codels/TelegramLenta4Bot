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

    public function query($command, $params = null) {
        $url = "https://api.telegram.org/bot{$this->_token}/$command";
        return HttpQuery::sendResultJson($url, $params);
    }

    public function getUpdates($offset = null, $limit = null, $timeout = null) {
        $options = array();
        if (!is_null($offset)) {
            $options['offset'] = $offset;
        }
        if (!is_null($limit)) {
            $options['limit'] = $limit;
        }
        if (!is_null($timeout)) {
            $options['timeout'] = $timeout;
        }
        return static::query('getUpdates', $options);
    }

    public function sendMessage($chatId, $text, $disableWebPagePreview = null, $replyToMessageId = null, $replyMarkup = null) {
        // required
        $options = array(
            'chat_id' => $chatId,
            'text' => $text
        );
        // optional
        if (!is_null($disableWebPagePreview)) {
            $options['disable_web_page_preview'] = $disableWebPagePreview;
        }
        if (!is_null($replyToMessageId)) {
            $options['reply_to_message_id'] = $replyToMessageId;
        }
        if (!is_null($replyMarkup)) {
            $options['reply_markup'] = $replyMarkup;
        }
        return static::query('sendMessage', $options);
    }

    public function getMe() {
        return static::query('getMe');
    }

    public function forwardMessage($chatId, $fromChatId, $messageId) {
        return static::query('forwardMessage', array(
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId
        ));
    }

    public function sendPhoto($chatId, $photo, $caption = null, $replyToMessageId = null, $replyMarkup = null) {
        // required
        $options = array(
            'chat_id' => $chatId,
            'photo' => $photo
        );
        // optional
        if (!is_null($caption)) {
            $options['caption'] = $caption;
        }
        if (!is_null($replyToMessageId)) {
            $options['reply_to_message_id'] = $replyToMessageId;
        }
        if (!is_null($replyMarkup)) {
            $options['reply_markup'] = $replyMarkup;
        }
        return static::query('sendPhoto', $options);
    }

    public function sendAudio($chatId, $audio, $replyToMessageId = null, $replyMarkup = null) {
        // required
        $options = array(
            'chat_id' => $chatId,
            'audio' => $audio
        );
        // optional
        if (!is_null($replyToMessageId)) {
            $options['reply_to_message_id'] = $replyToMessageId;
        }
        if (!is_null($replyMarkup)) {
            $options['reply_markup'] = $replyMarkup;
        }
        return static::query('sendAudio', $options);
    }

    public function sendDocument($chatId, $document, $replyToMessageId = null, $replyMarkup = null) {
        // required
        $options = array(
            'chat_id' => $chatId,
            'document' => $document
        );
        // optional
        if (!is_null($replyToMessageId)) {
            $options['reply_to_message_id'] = $replyToMessageId;
        }
        if (!is_null($replyMarkup)) {
            $options['reply_markup'] = $replyMarkup;
        }
        return static::query('sendDocument', $options);
    }

    public function sendSticker($chatId, $sticker, $replyToMessageId = null, $replyMarkup = null) {
        // required
        $options = array(
            'chat_id' => $chatId,
            'sticker' => $sticker
        );
        // optional
        if (!is_null($replyToMessageId)) {
            $options['reply_to_message_id'] = $replyToMessageId;
        }
        if (!is_null($replyMarkup)) {
            $options['reply_markup'] = $replyMarkup;
        }
        return static::query('sendSticker', $options);
    }

    public function sendVideo($chatId, $video, $replyToMessageId = null, $replyMarkup = null) {
        // required
        $options = array(
            'chat_id' => $chatId,
            'video' => $video
        );
        // optional
        if (!is_null($replyToMessageId)) {
            $options['reply_to_message_id'] = $replyToMessageId;
        }
        if (!is_null($replyMarkup)) {
            $options['reply_markup'] = $replyMarkup;
        }
        return static::query('sendVideo', $options);
    }

    public function sendLocation($chatId, $latitude, $longitude, $replyToMessageId = null, $replyMarkup = null) {
        // required
        $options = array(
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude
        );
        // optional
        if (!is_null($replyToMessageId)) {
            $options['reply_to_message_id'] = $replyToMessageId;
        }
        if (!is_null($replyMarkup)) {
            $options['reply_markup'] = $replyMarkup;
        }
        return static::query('sendLocation', $options);
    }

    public function sendChatAction($chatId, $action) {
        return static::query('sendChatAction', array(
            'chat_id' => $chatId,
            'action' => $action,
        ));
    }

    public function getUserProfilePhotos($userId, $offset = null, $limit = null) {
        // required
        $options = array(
            'user_id' => $userId
        );
        // optional
        if (!is_null($offset)) {
            $options['offset'] = $offset;
        }
        if (!is_null($limit)) {
            $options['limit'] = $limit;
        }
        return static::query('getUserProfilePhotos', $options);
    }
}