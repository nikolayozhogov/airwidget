<?php

namespace SlackWidget\Service;

class Notify extends \SlackWidget\System\Service
{
    public static function add($message)
    {
        \SlackWidget\Repository\Notify::insert([
            'text' => (string)$message,
        ]);
    }
}