<?php

namespace SlackWidget\Repository;

class LogEvent extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_LOG_EVENT;
}