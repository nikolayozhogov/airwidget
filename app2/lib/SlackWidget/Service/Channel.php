<?php

namespace SlackWidget\Service;

class Channel extends \SlackWidget\System\Service
{
    /*
     * Получить список каналов для показа в виджете
     */
    public static function _init($peer)
    {
        $channels = \SlackWidget\Repository\Channel::find([
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
            'hide_from_widget' => ['$ne' => true],
            'is_archived' => ['$ne' => true],
            'is_private' => ['$ne' => true],
        ]);

        $result = [];
        if(count($channels)){
            foreach ($channels as $channel) {
                $result[(string)$channel['_id']] = $channel;
            }
        }

        return $result;
    }
}