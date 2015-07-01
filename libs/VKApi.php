<?php

require_once('HttpQuery.php');

abstract class VKApi
{
    public static function getWallLastItem($owner, $count)
    {
        $url = "https://api.vk.com/method/wall.get?owner_id=-{$owner}&count={$count}&filter=owner";
        return HttpQuery::sendResultJson($url);
    }

    public static function parseWallItem($item, $owner)
    {
        if (empty($item) || !isset($item['response'])) {
            return null;
        }

        $item = $item['response'][1];
        //var_dump($item);

        $postId = $item['id'];
        $text = "Получено от: https://vk.com/wall-{$owner}_" . $postId . "\n\n";

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
        unset($item['attachments']);
        var_dump($item);

        $text = preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $text);
        return array(
            'text' => $text,
            'date' => $item['date']
        );
    }

    public static function getWallLastItemParsed($owner, $count) {
        $item = static::getWallLastItem($owner, $count);
        return static::parseWallItem($item, $owner);
    }
}