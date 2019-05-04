<?php

namespace SlackWidget\Repository;

class Notify extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_NOTIFY;
}