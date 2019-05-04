<?php

$start = microtime(true);
define('RD', $_SERVER['DOCUMENT_ROOT']);

require_once RD . '/app2/vendor/autoload.php';
require_once RD . '/app2/loader_widget.php';

$peer = \SlackWidget\Service\Widget::_init();

$count_messages = 50;
$enable_file_types = ['png', 'jpg'];
$subtype = ['$ne' => ['$in' => ['channel_join', 'bot_message']]];

if (
    !empty($_REQUEST['channel_id']) &&
    !empty($_REQUEST['type'])
) {

    // история сообщений
    if($_REQUEST['type'] == 'history'){

        // возможно, виджет пуст и в ленте появилось первое сообщение, а может быть в ленте уже были сообщения
        $ts = 0;
        if(!empty($_REQUEST['ts']))
            $ts = $_REQUEST['ts']; // id последнего сообщения в ленте (может быть пустым)

        // канал
        $channel = \SlackWidget\Repository\Channel::find_one([
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
            '_id' => \SlackWidget\System\Mongo::id($_REQUEST['channel_id'], \SlackWidget\Repository\Channel::ID_MAX_LENGTH),
            'hide_from_widget' => ['$ne' => true],
        ]);
        if(empty($channel['_id']))
            exit();

        $messages = \SlackWidget\Repository\Message::find([
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
            '_channel_id' => $channel['id'],
            'thread_ts' => ['$exists' => false], // вложенные сообщения не показываем
            'subtype' => $subtype, // типы сообщений
            'ts' => ['$lt' => $ts], // меньше указанного id
            'bot_id' => ['$exists' => false],
        ], [
            'sort' => ['ts' => -1],
            'limit' => $count_messages,
        ]);

        if(count($messages) > 0){

            $messages = array_reverse($messages);
            $message_array_ts = \SlackWidget\Service\Message::prepare_content($messages);

            // вложенные сообщения

            if(count($message_array_ts) > 0) {

                $submessages = \SlackWidget\Repository\Message::find([
                    '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
                    '_channel_id' => $channel['id'],
                    'thread_ts' => ['$in' => $message_array_ts],
                    'subtype' => $subtype, // типы сообщений
                    'bot_id' => ['$exists' => false],
                ], [
                    'sort' => ['ts' => -1],
                ]);
                $submessages = array_reverse($submessages);

                \SlackWidget\Service\Message::prepare_content($submessages, true);
                \SlackWidget\Service\Message::group_by_parent_ts($submessages);
            }

            include_once 'template_messages.php';
        }
    }

    // лента последних сообщений
    if($_REQUEST['type'] == 'latest'){

        // канал
        $channel = \SlackWidget\Repository\Channel::find_one([
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
            '_id' => \SlackWidget\System\Mongo::id($_REQUEST['channel_id'], \SlackWidget\Repository\Channel::ID_MAX_LENGTH),
            'hide_from_widget' => ['$ne' => true],
        ]);
        if(empty($channel['_id']))
            exit();

        // сообщения

        $messages = \SlackWidget\Repository\Message::find([
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
            '_channel_id' => $channel['id'],
            'subtype' => $subtype, // типы сообщений
            'parent_user_id' => ['$exists' => false],
            'bot_id' => ['$exists' => false],
        ], [
            'sort' => ['ts' => -1],
            'limit' => $count_messages,
        ]);
        $messages = array_reverse($messages);

        $message_array_ts = \SlackWidget\Service\Message::prepare_content($messages);

        // вложенные сообщения

        $submessages = [];
        if(count($message_array_ts) > 0) {

            $submessages = \SlackWidget\Repository\Message::find([
                '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
                '_channel_id' => $channel['id'],
                'thread_ts' => ['$in' => $message_array_ts],
                'parent_user_id' => ['$exists' => true],
                'bot_id' => ['$exists' => false],
            ], [
                'sort' => ['ts' => -1],
            ]);
            $submessages = array_reverse($submessages);

            \SlackWidget\Service\Message::prepare_content($submessages, true);
            \SlackWidget\Service\Message::group_by_parent_ts($submessages);
        }

        include_once 'template_messages.php';
    }

    // новые сообщение в ленте
    if($_REQUEST['type'] == 'new'){

        // возможно, виджет пуст и в ленте появилось первое сообщение, а может быть в ленте уже были сообщения
        $ts = 0;
        if(!empty($_REQUEST['ts']))
            $ts = $_REQUEST['ts']; // id последнего сообщения в ленте (может быть пустым)

        // последняя метка с датой
        if(!empty($_REQUEST['last_day']))
            $last_day = $_REQUEST['last_day'];

        // канал
        $channel = \SlackWidget\Repository\Channel::find_one([
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
            '_id' => \SlackWidget\System\Mongo::id($_REQUEST['channel_id'], \SlackWidget\Repository\Channel::ID_MAX_LENGTH),
            'hide_from_widget' => ['$ne' => true],
        ]);
        if(empty($channel['_id']))
            exit();

        $messages = \SlackWidget\Repository\Message::find([
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
            '_channel_id' => $channel['id'],
            'thread_ts' => ['$exists' => false], // вложенные сообщения не показываем
            'subtype' => $subtype, // типы сообщений
            'ts' => ['$gt' => $ts], // больше указанного id
            'bot_id' => ['$exists' => false],
        ], [
            'sort' => ['ts' => -1],
            'limit' => $count_messages,
        ]);

        if(count($messages) > 0){

            $messages = array_reverse($messages);
            $message_array_ts = \SlackWidget\Service\Message::prepare_content($messages);

            // вложенные сообщения

            if(count($message_array_ts) > 0) {

                $submessages = \SlackWidget\Repository\Message::find([
                    '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
                    '_channel_id' => $channel['id'],
                    'thread_ts' => ['$in' => $message_array_ts],
                    'subtype' => $subtype, // типы сообщений
                    'bot_id' => ['$exists' => false],
                ], [
                    'sort' => ['ts' => -1],
                ]);
                $submessages = array_reverse($submessages);

                \SlackWidget\Service\Message::prepare_content($submessages, true);
                \SlackWidget\Service\Message::group_by_parent_ts($submessages);
            }

            include_once 'template_messages.php';

        }
        echo file_get_contents('ajax.html');
    }

} else {

    $channels = \SlackWidget\Service\Channel::_init($peer);
    include_once 'template.php';
}