<?php

namespace SlackWidget\Repository;

class BotMessage extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_BOT_MESSAGE;
}