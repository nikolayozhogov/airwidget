<?php

namespace SlackWidget\Repository;

class Channel extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_CHANNEL;

    const ID_MAX_LENGTH = 50;
}