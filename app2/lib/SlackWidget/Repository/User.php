<?php

namespace SlackWidget\Repository;

class User extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_USER;
}