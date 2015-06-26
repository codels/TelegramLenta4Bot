<?php

require_once('TelegramBotApi.php');
require_once('DB.php');

class TelegramBot
{

    private $_api = null;
    private $_info = null;
    private $_db = null;

    public function __construct(DB &$db, $config, $info)
    {
        $this->_info = $info;
        $this->_api = new TelegramBotApi($this->_info, $config);
        $this->_db = $db;
    }

    public function setLastUpdateId($id = null)
    {
        if (is_null($id)) {
            $id = $this->_info['last_update_id'] + 1;
        }
        $statement = $this->_db->getConnect()->prepare("UPDATE `bots` SET `last_update_id`=? WHERE `id`=?");
        $statement->execute(array($id, $this->_info['id']));
        $this->_info['last_update_id'] = $id;
    }

    public function getLastUpdateId()
    {
        return $this->_info['last_update_id'];
    }

    /**
     * @return null|TelegramBotApi
     */
    public function getApi()
    {
        return $this->_api;
    }

    public static function getAllBots(DB &$db, $config)
    {
        $statement = $db->getConnect()->query('SELECT * FROM `bots`');
        $botsInfo = $statement->fetchAll(PDO::FETCH_ASSOC);
        $bots = array();
        foreach ($botsInfo as &$botInfo) {
            $bots[] = new TelegramBot($db, $config, $botInfo);
        }
        return $bots;
    }

    public function sendResourceList($chatId)
    {
        $statement = $this->_db->getConnect()->query('SELECT * FROM `resources`');
        $availableResources = $statement->fetchAll(PDO::FETCH_ASSOC);
        $text = "Доступные ресурсы: \n";
        foreach ($availableResources as $key => $resource) {
            $text .= $key + 1 . ". " . $resource['subscribe_name'] . "\n";
        }
        $this->_api->sendMessage($chatId, $text);
    }

    public function getResourceIdByName($name)
    {
        $statement = $this->_db->getConnect()->prepare('SELECT `id` FROM `resources` WHERE `subscribe_name` = ?');
        if (!$statement->execute(array($name))) {
            throw new Exception('Sql query not executed');
        }
        if (!$statement->rowCount()) {
            return null;
        }
        return intval($statement->fetchColumn());
    }

    public function subscribeByResourceId($chatId, $resourceId, $isDisplayPreview = true)
    {
        if ($this->checkExistsSubscribe($chatId, $resourceId)) {
            $this->getApi()->sendMessage($chatId, "Похоже, что подписка уже оформлена");
        } else {
            //подписка
            $statement = $this->_db->getConnect()->prepare('INSERT INTO `subscribers` (`chat_id`, `resource_id`, `is_display_preview`) VALUES (?, ?, ?);');
            $statement->execute(array($chatId, $resourceId, $isDisplayPreview ? 1 : 0));
            if ($statement->rowCount()) {
                $this->getApi()->sendMessage($chatId, "Подписка успешно оформлена!");
            } else {
                $this->getApi()->sendMessage($chatId, "Возникла ошибка при оформлении подписки!");
            }
        }
    }

    public function getResourceById($id)
    {
        $statement = $this->_db->getConnect()->prepare('SELECT * FROM `resources` WHERE `id` = ? LIMIT 1');
        if (!$statement->execute(array($id))) {
            throw new Exception('Sql query not executed');
        }
        if (!$statement->rowCount()) {
            return null;
        }
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function subscribeByResourceName($chatId, $resourceName)
    {
        $resourceId = $this->getResourceIdByName($resourceName);
        if (!$resourceId) {
            $this->getApi()->sendMessage($chatId, "Произошла ошибка: Не найден ресурс для подписки.");
        }
        $this->subscribeByResourceId($chatId, $resourceId);
    }

    public function checkExistsSubscribe($chatId, $resourceId)
    {
        $statement = $this->_db->getConnect()->prepare('SELECT 1 FROM `subscribers` WHERE `chat_id` = ? AND `resource_id` = ? LIMIT 1');
        if (!$statement->execute(array($chatId, $resourceId))) {
            throw new Exception('Sql query not executed');
        }
        return $statement->rowCount() > 0;
    }
}
