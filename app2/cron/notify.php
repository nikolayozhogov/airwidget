<?php

if (php_sapi_name() == 'cli') {

    define('RD', realpath(__DIR__ . '/../..'));

    require_once RD . '/app2/vendor/autoload.php';
    require_once RD . '/app2/loader_widget.php';
    cli_lock_file();

    $notifications = \SlackWidget\Repository\Notify::find([], [
        'limit' => 20,
        'sort' => ['created_at' => 1],
    ]);

    echo 'count: ' . count($notifications) . PHP_EOL;

    foreach ($notifications as $notify) {

        $url = 'https://api.telegram.org/' . TG_NOTIFY_BOT_KEY . '/sendmessage?chat_id=' . TG_NOTIFY_GROUP_ID . '&text=' . $notify['text'];

        sleep(1);
        $json = file_get_contents($url);
        $response = json_decode($json, true);

        \SlackWidget\Repository\Notify::remove($notify['_id']);

        echo 'sended', PHP_EOL;
    }
}