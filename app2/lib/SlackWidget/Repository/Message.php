<?php

namespace SlackWidget\Repository;

class Message extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_MESSAGE;
}