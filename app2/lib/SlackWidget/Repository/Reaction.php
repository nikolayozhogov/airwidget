<?php

namespace SlackWidget\Repository;

class Reaction extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_REACTION;
}