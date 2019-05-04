<?php

namespace SlackWidget\Service;

class Slack extends \SlackWidget\System\Service
{
    private $oauth_token; // Токен бота
    private $http_info = array(); // информация о последнем curl запросе

    function __construct($oauth_token)
    {
        $this->oauth_token = $oauth_token; // для API нужен токен для обращения по API
    }

    /*
     * Поулчить информацию о последнем curl запросе
     */
    public function get_latest_http_info()
    {
        return $this->http_info;
    }

    /*
     * Расшарить картинку
     * slack api method: files.sharedPublicURL
     */
    public function shared_picture(&$file){
        $api_uri = 'https://slack.com/api/files.sharedPublicURL';
        $params = [
            'token' => $this->oauth_token,
            'file' => $file['id'],
        ];
        $result = $this->__post_query($api_uri, $params);

        $file['_public_share'] = $result;
    }

    /*
     * Получить список пользователей
     */
    public function get_users_list()
    {
        $api_uri = 'https://slack.com/api/users.list';
        $params = [
            'token' => $this->oauth_token,
        ];
        $result = $this->__post_query($api_uri, $params);

        return $result;
    }

    /*
     * Получить список стикеров
     */
    public function get_reactions_list($channel_id, $message_ts)
    {
        $params = [
            'token' => $this->oauth_token,
            'channel' => $channel_id,
            'full' => true,
            'timestamp' => $message_ts,
        ];
        $params = http_build_query($params);
        $api_uri = 'https://slack.com/api/reactions.get?' . $params;

        $result = $this->__get_query($api_uri);

        return $result;
    }

    /*
     * Получить список каналов
     */
    public function get_channels_list()
    {

        $api_uri = 'https://slack.com/api/channels.list';
        $params = [
            'token' => $this->oauth_token,
        ];
        $result = $this->__post_query($api_uri, $params);

        return $result;
    }

    /*
     * POST запрос к API
     */
    private function __post_query($url, $fields)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $json = curl_exec($ch);
        $this->http_info = curl_getinfo($ch);
        curl_close($ch);

        $result = json_decode($json, true);

        return (array)$result;
    }

    /*
     * GET запрос к API
     */
    private function __get_query($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $json = curl_exec($ch);
        $this->http_info = curl_getinfo($ch);
        curl_close($ch);

        $result = json_decode($json, true);

        return (array)$result;
    }

    /*
     * Получить список сообщений
     */
    public function get_messages_list($channel_id, $count = 1000, $newer_than = null, $older_than = null)
    {
        $api_uri = 'https://slack.com/api/channels.history';
        $params = [
            'token' => $this->oauth_token,
            'channel' => $channel_id,
            //'oldest' => ($newer_than == null) ? 0 : $newer_than,
            //'latest' => ($older_than == null) ? 0 : $older_than,
            //'inclusive' => false,
            'count' => $count,
        ];
        $result = $this->__post_query($api_uri, $params);

        return $result;
    }

    /*
     * Получить пользователя
     */
    public function get_user_profile($user_id)
    {
        $params = [
            'token' => $this->oauth_token,
            'user' => $user_id,
        ];
        $params = http_build_query($params);
        //$api_uri = 'https://slack.com/api/users.profile.get?' . $params;
        $api_uri = 'https://slack.com/api/users.info?' . $params;

        $result = $this->__get_query($api_uri);

        return $result;
    }

    /*
     * Получить все обновления для пира
     * @param peer
     */
    public static function get_updates($peer, $print = false)
    {
        $cnt = [
            'channels_save' => 0,
            'channels_update' => 0,
            'users_save' => 0,
            'users_update' => 0,
            'messages_save' => 0,
            'messages_update' => 0,
        ];

        $cnt['_team_name'] = $peer['team_name'];
        $cnt['_id'] = (string)$peer['_id'];

        /*
         * Список каналов пира
         * По умолчанию все новые каналы скрыты
         */
        $slack = new \SlackWidget\Service\Slack($peer['access_token']);
        $response = $slack->get_channels_list();
        if (empty($response['ok'])) {
            $response['_method'] = 'get_channels_list';
            $response['_http_info'] = $slack->get_latest_http_info();
            return $response;
        }

        if($print)
            echo 'channels: ' . count($response['channels']), PHP_EOL;

        if (count($response['channels'])) {
            foreach ($response['channels'] as $channel) {

                $result_find = \SlackWidget\Repository\Channel::find_one([
                    '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
                    'id' => $channel['id'],
                ]);
                $channel['_peer_id'] = \SlackWidget\System\Mongo::id($peer['_id']);

                if($print)
                    echo $channel['name'];

                if (empty($result_find['_id'])) {

                    $channel['hide_from_widget'] = true;
                    \SlackWidget\Repository\Channel::insert($channel);
                    $cnt['channels_save']++;
                    if($print)
                        echo ' save', PHP_EOL;
                } else {
                    \SlackWidget\Repository\Channel::update([
                        '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
                        'id' => $channel['id'],
                    ], $channel);
                    $cnt['channels_update']++;
                    if($print)
                        echo ' update', PHP_EOL;
                }
            }
        }

        /*
         * Сипсок пользователей пира
         */
        $response = $slack->get_users_list();
        if (empty($response['ok'])) {
            $response['_method'] = 'get_users_list';
            $response['_http_info'] = $slack->get_latest_http_info();
            return $response;
        }

        if($print)
            echo 'users: ' . count($response['members']), PHP_EOL;

        if (count($response['members'])) {
            foreach ($response['members'] as $member) {

                $result_find = \SlackWidget\Repository\SlackUser::find_one([
                    '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
                    'id' => $member['id'],
                ]);
                $member['_peer_id'] = \SlackWidget\System\Mongo::id($peer['_id']);

                if (empty($result_find['_id'])) {
                    \SlackWidget\Repository\SlackUser::insert($member);
                    $cnt['users_save']++;
                } else {
                    \SlackWidget\Repository\SlackUser::update([
                        '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
                        'id' => $member['id'],
                    ], $member);
                    $cnt['users_update']++;
                }
            }
        }

        /*
         * Для каждого канала этого пира
         */
        $channels = \SlackWidget\Repository\Channel::find([
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
        ]);
        if (count($channels) > 0) {
            foreach ($channels as $channel) {
                $channel_id = $channel['id'];

                if($print)
                    echo PHP_EOL, 'channel: ' . $channel['name'], ' ', $channel['id'], PHP_EOL;

                /*
                 * Список сообщений
                 * Зачем обновлять сообщения, если приходят callback-апдейты?
                 * - что бы обновлять reactions для сообщения
                 */
                $response = $slack->get_messages_list($channel_id);
                if (empty($response['ok'])) {
                    $response['_method'] = 'get_messages_list';
                    $response['_http_info'] = $slack->get_latest_http_info();
                    return $response;
                }

                if($print)
                    echo 'messages: ' . count($response['messages']), PHP_EOL;

                if (count($response['messages'])) {
                    foreach ($response['messages'] as $message) {

                        $result_find = \SlackWidget\Repository\Message::find_one([
                            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
                            '_channel_id' => $channel_id,
                            'ts' => $message['ts'],
                        ]);
                        $message['_peer_id'] = \SlackWidget\System\Mongo::id($peer['_id']);
                        $message['_channel_id'] = $channel_id;

                        if (empty($result_find['_id'])) {
                            \SlackWidget\Repository\Message::insert($message);
                            $cnt['messages_save']++;
                            echo ' save ';
                        } else {
                            \SlackWidget\Repository\Message::update([
                                '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
                                '_channel_id' => $channel_id,
                                'ts' => $message['ts'],
                            ], $message);
                            $cnt['messages_update']++;
                            if($print)
                                echo ' update ';
                        }
                    }
                    if($print)
                        echo PHP_EOL, PHP_EOL;
                }

            }
        }

        return $cnt;
    }
}