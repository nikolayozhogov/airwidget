<?php

if (php_sapi_name() == 'cli') {

    define('RD', realpath(__DIR__ . '/../..'));

    require_once RD . '/app2/vendor/autoload.php';
    require_once RD . '/app2/loader_widget.php';
    cli_lock_file();

    $messages = \SlackWidget\Repository\Message::find([
        'files' => ['$exists' => true],
        '_peer_id' => \SlackWidget\System\Mongo::id('5bab33f971f3614429117c10'),
    ]);
    echo 'messages: ', count($messages), PHP_EOL;

    $enable_file_types = ['png', 'jpg'];

    foreach ($messages as $message){

        if(count($message['files']) == 0)
            continue;

        $peer = \SlackWidget\Repository\Peer::find_one([
            '_id' => \SlackWidget\System\Mongo::id($message['_peer_id']),
        ]);
        echo $peer['team_name'], ' ';

        foreach ($message['files'] as $file) {

            if (!in_array($file['filetype'], $enable_file_types))
                continue;

            $name = '/upload/' . $file['id'] . '.' . $file['filetype'];

            if(file_exists(RD . $name)){
                echo 'exists ', $name, PHP_EOL;
                continue;
            }

            $add_headers = [
                'Authorization: Bearer ' . (string)$peer['access_token'],
            ];

            $fp = fopen (RD . $name, 'wb');
            $url = $file['url_private'];

            $ch = curl_init(str_replace(" ","%20", $url));
            curl_setopt($ch, CURLOPT_FILE, $fp);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $add_headers);

            $data = curl_exec($ch);

            curl_close($ch);

            echo 'saved ', $name, PHP_EOL;
        }
    }

}