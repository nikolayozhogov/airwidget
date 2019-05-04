<?php

if (php_sapi_name() == 'cli') {

    define('RD', realpath(__DIR__ . '/../..'));

    require_once RD . '/app2/vendor/autoload.php';
    require_once RD . '/app2/loader_cli.php';
    cli_lock_file();

    $peer = \SlackWidget\Repository\Peer::find_one([
        'team_id' => 'TCXDJE116',
    ]);

    if(empty($peer['_id']))
        exit('peer is null');

    $users = \SlackWidget\Repository\SlackUser::find([
        '_peer_id' => \SlackWidget\System\Mongo::id($peer['_id']),
        'is_bot' => false,
    ]);

    // тест от бота

    $slackBot = new \SlackWidget\Service\SlackBot($peer['access_token']);

    foreach ($users as $user) {
        echo $user['name'], PHP_EOL;
        $result = $slackBot->send_message($user['id'], 'Test spam message');
        print_r($result);
    }
}