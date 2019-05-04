<?php

namespace SlackWidget\Repository;

class Peer extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_PEER;
}