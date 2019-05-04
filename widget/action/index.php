<?php

define('RD', $_SERVER['DOCUMENT_ROOT']);

if(empty($_REQUEST['action']))
    exit('request error');

require_once RD . '/app2/vendor/autoload.php';
require_once RD . '/app2/loader_widget.php';

$peer = \SlackWidget\Service\Widget::_init();


switch ($_REQUEST['action']){
    case 'get_channel_info':

        if(empty($_REQUEST['channel_id']))
            exit();

        $channel = \SlackWidget\Repository\Channel::find_by_id($_REQUEST['channel_id']);

        $response = [];
        $response['name'] = $channel['name'];
        $response['descr'] = $channel['purpose']['value'];
        $response['members'] = count($channel['members']);

        echo json_encode($response);

        break;
}