<?php

namespace SlackWidget\Repository;

class SlackUser extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_SLACK_USER;
}