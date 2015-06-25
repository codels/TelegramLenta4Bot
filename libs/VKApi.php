<?php

require_once('HttpQuery.php');

abstract class VKApi
{
    public static function getWallLastItem($owner, $count)
    {
        $url = "https://api.vk.com/method/wall.get?owner_id=-{$owner}&count={$count}";
        return HttpQuery::sendResultJson($url);
    }
}