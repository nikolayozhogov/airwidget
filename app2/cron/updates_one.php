<?php

// slackwidget team 5bab33f971f3614429117c10

if (php_sapi_name() == 'cli') {

    define('RD', realpath(__DIR__ . '/../..'));

    require_once RD . '/app2/vendor/autoload.php';
    require_once RD . '/app2/loader_cli.php';
    cli_lock_file();

    $peer = \SlackWidget\Repository\Peer::find_one([
        '_id' => \SlackWidget\System\Mongo::id($argv[1]),
    ]);
    if (empty($peer))
        exit('peer not found');

    $result = \SlackWidget\Service\Slack::get_updates($peer, true);
    print_r($result);
}