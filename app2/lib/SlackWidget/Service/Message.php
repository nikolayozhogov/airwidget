<?php

namespace SlackWidget\Service;

class Message extends \SlackWidget\System\Service
{
    /*
     * Сгруппировать вложенные сообщения по "ts сообщения-родителя"
     */
    public static function group_by_parent_ts(&$messages)
    {
        if (count($messages) == 0)
            return [];

        $group_messages = [];

        foreach ($messages as $message){

            $parent_ts = $message['thread_ts'];

            if(!isset($group_messages[$parent_ts]))
                $group_messages[$parent_ts] = [];

            $group_messages[$parent_ts][] = $message;
        }

        $messages = $group_messages;
    }

    /*
     * 1. Подготовить информацию о пользователях
     * 2. Заменить в тексте <@U6KM6TSN9> на имена пользователей
     * 3. Убираем символы < и > в сообщениях <http://abc.ru>
     * 3.1 Заменяем :kissing_heart: на картинку emoji
     * 4. Отдать список идентификаторов сообщений для получения вложенных сообщений
     * 5. Формируем ассоциативный массив из сообщений
     */
    public static function prepare_content(&$messages, $it_is_submessage = false)
    {
        if (count($messages) == 0)
            return [];

        // превращаем в ассоциативный массив
        $new_messages = [];

        // получаем всех участников пира
        $slack_users = \SlackWidget\Repository\SlackUser::find([
            '_peer_id' => \SlackWidget\System\Mongo::id($messages[0]['_peer_id']),
        ]);
        $new_slack_users = [];
        foreach ($slack_users as $slack_user) {
            $new_slack_users[$slack_user['id']] = $slack_user;
        }

        // список идентификаторов сообщений
        $message_array_ts = [];


        foreach ($messages as &$message) {

            //$message['text'] = htmlentities($message['text']);

            // 1. Подготовить информацию о пользователях

            if (!empty($message['user'])) {
                $user_id = $message['user'];
                if (!empty($new_slack_users[$user_id]))
                    $message['user'] = $new_slack_users[$user_id];
            }

            // 2. Заменить в тексте <@U6KM6TSN9> на имена пользователей

            if (count($slack_users) > 0 && !empty($message['text'])) {
                preg_match_all('/<@([A-Z0-9]{9}+)>/', $message['text'], $matches);
                if (isset($matches[1]) && count($matches[1]) > 0) {
                    foreach ($matches[1] as $i => $match) {
                        if (isset($new_slack_users[$match]))
                            $message['text'] = str_replace($matches[0][$i], $new_slack_users[$match]['real_name'], $message['text']);
                    }
                }
            }

            // 3. Убираем символы < и > в сообщениях <http://abc.ru>
/*
            if(
                mb_substr($message['text'], 0, 1) == '<'
                && mb_substr($message['text'], mb_strlen($message['text'])-1, 1) == '>'
                //&& substr_count($message['text'], ' ') == 0
                //&& substr_count($message['text'], "\n") == 0
            ){
                $message['text'] = mb_substr($message['text'], 1, mb_strlen($message['text'])-2);
                $message['_only_link'] = true;
            }
*/
            $message['text'] = str_replace('<', '', $message['text']);
            $message['text'] = str_replace('>', '', $message['text']);

            // 3.1 Заменяем :kissing_heart: на картинку emoji

            preg_match_all('/:([a-z0-9_\+\-]+):/', $message['text'], $matches);
            if(!empty($matches[1])){
                foreach ($matches[1] as $match_i => $match){

                    $emoji_filename = '/assets/emoji/' . $match . '.png';
                    if(file_exists(RD . $emoji_filename)){
                        $message['text'] = str_replace(
                            $matches[0][$match_i],
                            '<img class="emoji_img" src="' . $emoji_filename . '" title="Emoji :' . $match .':">',
                            $message['text']);
                    }
                }
            }


            // 4. Отдать список идентификаторов сообщений для получения вложенных сообщений

            if($it_is_submessage == false && isset($message['thread_ts']))
                $message_array_ts[] = $message['thread_ts'];


            $new_messages[] = $message;
        }

        $messages = $new_messages;

        return $message_array_ts;
    }
}