<?php

require_once './modules/hors/Models/AbstractPeriod.php';
require_once './modules/hors/Dict/Keywords.php';

    class CCCRecognizer extends Recognizer
    {
        protected function GetRegexPattern()
        {
            return "";
        }

        protected function ParseMatch($data, $match, $userDate)
        {

            return true;
        }
    }

?>