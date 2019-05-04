<?php

namespace SlackWidget\Service;

class Widget extends \SlackWidget\System\Service
{

    /*
     * Разбор параметров запроса для отображения виджета
     */
    public static function _init()
    {

        if (empty($_REQUEST['id'])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            exit('Slack Widget install error: parameter id not found');
        }

        if (strlen($_REQUEST['id']) !== 24) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            exit('Slack Widget install error: parameter id not valid (length missing)');
        }

        $peer = \SlackWidget\Repository\Peer::find_by_id($_REQUEST['id']);
        if (empty($peer['_id'])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            exit('Slack Widget install error: widget not found');
        }

        return $peer;
    }
}