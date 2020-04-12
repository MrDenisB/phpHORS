<?php

require_once './modules/hors/Models/AbstractPeriod.php';
require_once './modules/hors/Dict/Keywords.php';


    class RelativeDayRecognizer extends Recognizer
    {
        protected function GetRegexPattern()
        {
            return "[2-6]"; // позавчера, вчера, сегодня, завтра, послезавтра
        }

        protected function ParseMatch($data, $match, $userDate)
        {
            if (!is_numeric($match[0]['Value'])) return false;
            else $relativeDay = (int) $match[0]['Value'];
            $relativeDay -= 4;
// echo "\nIn RelativeDayRecognizer \$relativeDay=$relativeDay\n";
            // create date
            $date = new AbstractPeriod();
            $userDateTmp = clone $userDate;
            ///////$date->Date = new DateTime();
            if($relativeDay < 0)
             $date->Date = $userDateTmp->sub(new DateInterval('P'.-$relativeDay.'D'));
            else
             $date->Date = $userDateTmp->add(new DateInterval('P'.$relativeDay.'D'));
            $date->FixDownTo(FixPeriod::FP_Day);
            
// var_dump($date->Date);
            
            // remove and insert
            //$data->ReplaceTokensByDates(match.Index, match.Length, date);
            $data->ReplaceTokensByDates($match[0]['Index'], mb_strlen($match[0]['Value']), array($date));

            return true;
        }
    }

?>