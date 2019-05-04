<?php

namespace SlackWidget\Service;

class SlackBot extends \SlackWidget\System\Service
{
    private $oauth_token; // Токен бота

    /*
     * Получить список пользователей
     * @param channel
     * @param text
     * @param user - who will recieve the message
     */
    public function send_post_ephemeral($channel, $text, $user)
    {
        $api_uri = 'https://slack.com/api/chat.postEphemeral';
        $params = [
            'token' => $this->oauth_token,
            'channel' => $channel,
            'text' => $text,
            'user' => $user,
        ];
        $result = $this->__post_query($api_uri, $params);

        $result['_request_fields'] = $params;

        \SlackWidget\Repository\BotMessage::insert($result);

        return $result;
    }

    /*
     * Отправить сообщение в канал
     */
    function send_message($channel_id, $text) {

        $api_uri = 'https://slack.com/api/chat.postMessage';
        $params = [
            'token' => $this->oauth_token,
            'channel' => $channel_id,
            'text' => $text,
        ];
        $result = $this->__post_query($api_uri, $params);

        \SlackWidget\Repository\BotMessage::insert($result);

        return $result;
    }

    function __construct($oauth_token)
    {
        $this->oauth_token = $oauth_token; // для API нужен токен для обращения по API
    }

    private function __post_query($url, $fields)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $json = curl_exec($ch);
        //$info = curl_getinfo($ch);

        curl_close($ch);

        $result = json_decode($json, true);

        return (array)$result;
    }

}