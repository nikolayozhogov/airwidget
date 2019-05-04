<?php

if (php_sapi_name() == 'cli') {

    define('RD', realpath(__DIR__ . '/../..'));

    require_once RD . '/app2/vendor/autoload.php';
    require_once RD . '/app2/loader_cli.php';
    cli_lock_file();

    $peers = \SlackWidget\Repository\Peer::find();
    if (count($peers) == 0)
        exit();

    foreach ($peers as $peer) {
        $result = \SlackWidget\Service\Slack::get_updates($peer, true);
        print_r($result);
    }
}