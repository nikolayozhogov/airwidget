<?php

namespace SlackWidget\Service;

class User extends \SlackWidget\System\Service
{

    public static function _init()
    {
        session_start();

        if (empty($_COOKIE['hash']) or empty($_COOKIE['id']))
            return false;

        $user = \SlackWidget\Repository\User::find_one([
            '_id' => \SlackWidget\System\Mongo::id($_COOKIE['id']),
            'hash' => trim($_COOKIE['hash']),
        ]);

        session_abort();

        return $user;
    }
}