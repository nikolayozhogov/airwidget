<?php

namespace SlackWidget\Repository;

class LogCommand extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_LOG_COMMAND;
}