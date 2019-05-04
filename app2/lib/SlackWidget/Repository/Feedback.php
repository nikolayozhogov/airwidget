<?php

namespace SlackWidget\Repository;

class Feedback extends \SlackWidget\System\Repository
{
    static $db = DB;
    static $collection = CL_FEEDBACK;
}