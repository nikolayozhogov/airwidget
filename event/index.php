<?php

$entityBody = file_get_contents('php://input');
if (empty($entityBody))
    exit('empty body');

define('RD', $_SERVER['DOCUMENT_ROOT']);
require_once RD . '/app2/vendor/autoload.php';
require_once RD . '/app2/loader.php';

$event = json_decode($entityBody, true);
\SlackWidget\Repository\LogEvent::insert($event);

$type = '';
if (!empty($event['event']['subtype']))
    $type = $event['event']['subtype'];

switch ($type) {

    case 'file_share':

        $peer = \SlackWidget\Repository\Peer::find_one([
            'team_id' => $event['team_id'],
        ]);

        $document = $event['event'];

        if($document['subtype'] == 'file_share' && !empty($document['files'])){

            foreach ($document['files'] as &$file) {

                $name = '/upload/' . $file['id'] . '.' . $file['filetype'];
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
            }
        }

        $document['_peer_id'] = \SlackWidget\System\Mongo::id($peer['_id']);
        $document['_channel_id'] = $document['channel'];

        $result = \SlackWidget\Repository\Message::insert($document);

        break;

    case 'message_deleted':

        $result = \SlackWidget\Repository\Message::remove([
            'ts' => $event['event']['previous_message']['ts'],
            '_channel_id' => $event['event']['channel'],
        ]);

        break;

    case 'message_changed':

        $result = \SlackWidget\Repository\Message::update([
            'ts' => $event['event']['previous_message']['ts'],
            '_channel_id' => $event['event']['channel'],
        ], [
            'text' => $event['event']['message']['text'],
        ]);

        break;

    case '':

        if (
            !empty($event['event']['type'])
            && $event['event']['type'] == 'channel_created'
        ) {
            $peer = \SlackWidget\Repository\Peer::find_one([
                'team_id' => $event['team_id'],
            ]);
            if (!empty($peer['_id'])) {

                $channel = $event['event']['channel'];
                $channel['_peer_id'] = \SlackWidget\System\Mongo::id($peer['_id']);
                $channel['hide_from_widget'] = true;

                $result = \SlackWidget\Repository\Channel::insert($channel);
            }
        }

        if (
            !empty($event['event']['type'])
            && $event['event']['type'] == 'message'
        ) {

            if($event['event']['channel_type'] == 'channel') {
                // message to channel

                $peer = \SlackWidget\Repository\Peer::find_one([
                    'team_id' => $event['team_id'],
                ]);
                $document = $event['event'];
                $document['_peer_id'] = \SlackWidget\System\Mongo::id($peer['_id']);
                $document['_channel_id'] = $document['channel'];

                $result = \SlackWidget\Repository\Message::insert($document);
            }
        }

        break;
}