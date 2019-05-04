<?php

// при добавлении бота в компанию этот файл получает запрос с параметром code

if (empty($_REQUEST['code']))
    exit('param code is null');

define('RD', $_SERVER['DOCUMENT_ROOT']);
require_once RD . '/app2/vendor/autoload.php';
require_once RD . '/app2/loader.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/oauth.access');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'client_id' => SLACK_CLIENT_ID,
    'code' => $_REQUEST['code'],
    'client_secret' => SLACK_CLIENT_SECRET,
    'single_channel' => true,
]);

$json = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

$result = json_decode($json, true);

if (!empty($result['ok']) && !empty($result['access_token'])) {

    // перевыпуск токена

    $peer = \SlackWidget\Repository\Peer::find_one([
        'team_id' => $result['team_id'],
    ]);
    if (!empty($peer['_id'])) {

        // обновляем данные для пира
        $result['_code'] = $_REQUEST['code'];
        \SlackWidget\Repository\Peer::update($peer['_id'], $result);
        $result['_id'] = $peer['_id'];

    } else {

        // добавляем новый пир
        $result['_id'] = \SlackWidget\System\Mongo::id();
        $result['_code'] = $_REQUEST['code'];
        \SlackWidget\Repository\Peer::insert($result);
    }

    // отправляем приветственное сообщение пользователю, который добавил бота

    $peer = \SlackWidget\Repository\Peer::find_one([
        'team_id' => $result['team_id'],
    ]);
    $slackBot = new \SlackWidget\Service\SlackBot($peer['access_token']);
    $slackBot->send_message($result['user_id'], WELCOME_MESSAGE . SLASH_COMMANDS);


    // получаем все данные по пиру
    \SlackWidget\Service\Slack::get_updates($peer);

    // делаем все каналы пира скрытыми
    \SlackWidget\Repository\Channel::update([
        '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
    ], [
        'hide_from_widget' => true,
    ], [
        \SlackWidget\System\Repository::MULTIPLE => true,
    ]);

    $url = 'https://airwidget.app/code?id=' . (string)$peer['_id'];

    \SlackWidget\Service\Notify::add('new widget ' . $url);

    header('Location: ' . $url);
    exit();

} else {

    echo INTERNAL_ERROR;
    \SlackWidget\Service\Notify::add('callback error: ' . serialize($result));
}