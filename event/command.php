<?php

define('RD', $_SERVER['DOCUMENT_ROOT']);
require_once RD . '/app2/vendor/autoload.php';
require_once RD . '/app2/loader.php';

function replace_arg($string, $replace){
    return str_replace('{text}', $replace, $string);
}

$error_share = 'I can\'t share `{text}`, because it\'s not a public channel.';
$error_unshare = 'I can\'t stop sharing `{text}`, because it\'s not a public channel.';
$error_default = 'I can\'t ';
$error_permission = 'Sorry, but only admins can use this command.';

// лог
\SlackWidget\Repository\LogCommand::insert($_POST);

// определяем команду
$peer = \SlackWidget\Repository\Peer::find_one([
    'team_id' => $_POST['team_id'],
]);
if (empty($peer['_id'])) {
    exit();
}

// инициализируем бота
$slackBot = new \SlackWidget\Service\SlackBot($peer['access_token']);

// пользователь должен обладать правами администратора
$user_find = \SlackWidget\Repository\SlackUser::find_one([
    'id' => $_POST['user_id'],
    '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
    'is_admin' => true,
]);
if (empty($user_find['_id'])) {
    $slackBot->send_post_ephemeral($channel['id'], $error_permission, $_POST['user_id']);
}

// обработчик команд
$type = '';
if (!empty($_POST['command']))
    $type = $_POST['command'];

switch ($type) {

    case '/widget_feedback':

        \SlackWidget\Service\Notify::add('new feedback (from id ' . $peer['_id'] . '): ' . trim($_POST['text']));

        \SlackWidget\Repository\Feedback::insert([
            'text' => trim($_POST['text']),
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
        ]);
        $slackBot->send_post_ephemeral($_POST['channel_id'], "Thanks for the feedback! Our team will make sure to review it.", $_POST['user_id']);

        break;
    case '/widget_code':

        $slackBot->send_post_ephemeral($_POST['channel_id'],
            '```<iframe style="width:900px; height:450px; border-radius: 10px; box-shadow: 0px 5px 30px 5px #d2d2d2" src="https://event.airwidget.app/widget/?id=' . $peer['_id'] . '" frameborder="0" scrolling="no" horizontalscrolling="no" verticalscrolling="no" async></iframe>```',
            $_POST['user_id']);

        break;
    case '/widget_commands':

        $slackBot->send_post_ephemeral($_POST['channel_id'], SLASH_COMMANDS, $_POST['user_id']);

        break;
    case '/share_all':

        $channels = \SlackWidget\Repository\Channel::find([
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
            'is_archived' => ['$ne' => true],
            'is_private' => ['$ne' => true],
            'hide_from_widget' => true,
        ]);
        if(count($channels)){
            foreach ($channels as $channel){

                \SlackWidget\Repository\Channel::update($channel['_id'], [
                    'hide_from_widget' => false,
                ]);
                //$slackBot->send_message($channel['id'], 'Now I’m sharing all messages from #' . $channel['name']);
                if(count($channel['members'])){
                    foreach ($channel['members'] as $member) {
                        $slackBot->send_post_ephemeral($channel['id'], 'Now I’m sharing all messages from #' . $channel['name'], $member);
                    }
                }
            }
        }

        break;
    case '/share':

        if(!empty($_POST['text'])){
            $name = str_replace('#', '', trim($_POST['text']));
        } else {
            $name = $_POST['channel_name'];
        }

        $list = explode(' ', $name);
        if (count($list)) {
            foreach ($list as $name) {
                $channel = \SlackWidget\Repository\Channel::find_one([
                    '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
                    'name' => $name,
                    'is_archived' => ['$ne' => true],
                    'is_private' => ['$ne' => true],
                ]);
                if (!empty($channel['_id'])) {
                    \SlackWidget\Repository\Channel::update($channel['_id'], [
                        'hide_from_widget' => false,
                    ]);
                    //$slackBot->send_message($channel['id'], 'Now I’m sharing all messages from #' . $channel['name']);

                    if(count($channel['members'])){
                        foreach ($channel['members'] as $member) {
                            $slackBot->send_post_ephemeral($channel['id'], 'Now I’m sharing all messages from #' . $channel['name'], $member);
                        }
                    }
                    //$slackBot->send_post_ephemeral($channel['id'], 'Now I’m sharing all messages from #' . $channel['name'], $_POST['user_id']);

                } else {
                    $slackBot->send_post_ephemeral($channel['id'], replace_arg($error_share, $name), $_POST['user_id']);
                }
            }
        }

        break;
    case '/unshare':

        if(!empty($_POST['text'])){
            $name = str_replace('#', '', trim($_POST['text']));
        } else {
            $name = $_POST['channel_name'];
        }

        $list = explode(' ', $name);
        if (count($list)) {
            foreach ($list as $name) {
                $channel = \SlackWidget\Repository\Channel::find_one([
                    '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
                    'name' => $name,
                    'is_archived' => ['$ne' => true],
                    'is_private' => ['$ne' => true],
                ]);
                if (!empty($channel['_id'])) {
                    \SlackWidget\Repository\Channel::update($channel['_id'], [
                        'hide_from_widget' => true,
                    ]);
                    //$slackBot->send_message($channel['id'], 'I stopped sharing all messages from #' . $channel['name']);
                    if(count($channel['members'])){
                        foreach ($channel['members'] as $member) {
                            $slackBot->send_post_ephemeral($channel['id'], 'I stopped sharing all messages from #' . $channel['name'], $member);
                        }
                    }

                } else {
                    $slackBot->send_post_ephemeral($channel['id'], replace_arg($error_unshare, $name), $_POST['user_id']);
                }
            }
        }

        break;
    case '/default':

        if(!empty($_POST['text'])){
            $name = str_replace('#', '', trim($_POST['text']));
        } else {
            $name = $_POST['channel_name'];
        }

        $channel = \SlackWidget\Repository\Channel::find_one([
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
            'name' => $name,
            'is_archived' => ['$ne' => true],
            'is_private' => ['$ne' => true],
        ]);
        if (!empty($channel['_id'])) {

            \SlackWidget\Repository\Peer::update($peer['_id'], [
                'default_channel_id' => \SlackWidget\System\Mongo::id($channel['_id']),
            ]);
            // если до этого канал был скрыт, то уведомляем об его открытии
            if($channel['hide_from_widget']){

                \SlackWidget\Repository\Channel::update([
                    '_id' => \SlackWidget\System\Mongo::id($channel['_id']),
                ], [
                    'hide_from_widget' => false,
                ]);
                //$slackBot->send_message($channel['id'], 'Now I’m sharing all messages from #' . $channel['name']);
                if(count($channel['members'])){
                    foreach ($channel['members'] as $member) {
                        $slackBot->send_post_ephemeral($channel['id'], 'Now I’m sharing all messages from #' . $channel['name'], $member);
                    }
                }
            }
            $slackBot->send_post_ephemeral($channel['id'], 'Ok. Now channel #' . $channel['name'] . ' is a default channel in your widget.', $_POST['user_id']);

        } else {
            $slackBot->send_post_ephemeral($channel['id'], replace_arg($error_default, $name), $_POST['user_id']);
        }

        break;
    case '/unshare_all':

        $channels = \SlackWidget\Repository\Channel::find([
            '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
            'is_archived' => ['$ne' => true],
            'is_private' => ['$ne' => true],
            'hide_from_widget' => false,
        ]);
        if(count($channels)){
            foreach ($channels as $channel){

                \SlackWidget\Repository\Channel::update($channel['_id'], [
                    'hide_from_widget' => true,
                ]);
                //$slackBot->send_message($channel['id'], 'I stopped sharing all messages from #' . $channel['name']);

                if(count($channel['members'])){
                    foreach ($channel['members'] as $member) {
                        $slackBot->send_post_ephemeral($channel['id'], 'I stopped sharing all messages from #' . $channel['name'], $member);
                    }
                }
            }
        }

        break;
}