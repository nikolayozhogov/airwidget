<?php

namespace SlackWidget\Service;

class InputData extends \SlackWidget\System\Service
{

    public static function val($val, $length = 25){

        $val = strip_tags($val);
        $val = trim($val);
        $val = mb_substr($val, 0, $length);

        return $val;
    }
}