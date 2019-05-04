<?php

// для теста программно расшарим один файл

if (php_sapi_name() == 'cli') {

    define('RD', realpath(__DIR__ . '/../..'));

    require_once RD . '/app2/vendor/autoload.php';
    require_once RD . '/app2/loader_widget.php';
    cli_lock_file();

    $peer = \SlackWidget\Repository\Peer::find_one([
        'team_id' => 'TCD67K15F',
    ]);
    print_r($peer);

    $message = \SlackWidget\Repository\Message::find_one([
        '_id' => \SlackWidget\System\Mongo::id('5bc8c2e271f3615c4c71819c'),
    ]);

    $slack = new \SlackWidget\Service\Slack($peer['access_token']);
    $slack->shared_picture($message['files'][0]);

    print_r($message);
}