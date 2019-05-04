<?php

namespace SlackWidget\Repository;

class Session extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_SESSION;
}