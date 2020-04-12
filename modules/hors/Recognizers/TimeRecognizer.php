<?php

    class TimeRecognizer extends Recognizer
    {
        protected function GetRegexPattern()
        {
            return "([rvgd])?([fot])?(Q|H)?(h|(0)(h)?)((0)e?)?([rvgd])?"; // (в/с/до) (половину/четверть) час/9 (часов) (30 (минут)) (утра/дня/вечера/ночи)
        }

        protected function ParseMatch($data, $match, $userDate)
        {
            // determine if it is time
            if (
                (isset($match[5]['Value']) && $match[5]['Value']!='') // во фразе есть число
                || (isset($match[6]['Value']) && $match[6]['Value']!='') // во фразе есть "часов"
                || (isset($match[4]['Value']) && $match[4]['Value']!='') // во фразе есть "час"
                || (isset($match[1]['Value']) && $match[1]['Value']!='') // во начале есть "утра/дня/вечера/ночи"
                || (isset($match[9]['Value']) && $match[9]['Value']!='') // то же самое в конце
            )
            {
                if (!isset($match[5]['Value']) || $match[5]['Value']=='')
                {
                    // no number in phrase
                    $partOfDay = (isset($match[9]['Value']) && $match[9]['Value']!='') 
                        ? $match[9]['Value'] 
                        : (isset($match[1]['Value']) && $match[1]['Value']!='') 
                            ? $match[1]['Value'] 
                            : '';
                    
                    // no part of day AND no "from" token in phrase, quit
                    if ($partOfDay != "d" && $partOfDay != "g" && (!isset($match[2]['Value']) || $match[2]['Value']==''))
                    {
                        return false;
                    }
                }
                
                // hours and minutes
                $hours = (isset($match[5]['Value']) && $match[5]['Value']!='') ? (int) $data->Tokens[$match[5]['Index']]->Value : 1;
                if ($hours >= 0 && $hours <= 23)
                {
                    // try minutes
                    $minutes = 0;
                    if (isset($match[8]['Value']) && $match[8]['Value']!='')
                    {
                        $m = (int) $data->Tokens[$match[8]['Index']]->Value;
                        if ($m >= 0 && $m <= 59) $minutes = $m;
                    }
                    else if (isset($match[3]['Value']) && $match[3]['Value']!='' && $hours > 0)
                    {
                        switch ($match[3]['Value'])
                        {
                            case "Q": // quarter
                                $hours--;
                                $minutes = 15;
                                break;
                            case "H": // half
                                $hours--;
                                $minutes = 30;
                                break;
                        }
                    }

                    // create time
                    $date = new AbstractPeriod();
                    $date->Fix(array(FixPeriod::FP_TimeUncertain));
                    if ($hours > 12) $date->Fix(array(FixPeriod::FP_Time));

                    // correct time
                    if ($hours <= 12)
                    {
                        $part = "d"; // default
                        if ( (isset($match[9]['Value']) && $match[9]['Value']!='') || (isset($match[1]['Value']) && $match[1]['Value']!=''))
                        {
                            // part of day
                            $part = $match[1]['Value']!='' ? $match[1]['Value'] : $match[9]['Value'];
                            $date->Fix(array(FixPeriod::FP_Time));
                        }
                        else
                        {
                            $date->Fix(array(FixPeriod::FP_TimeUncertain));
                        }
                        
                        switch ($part)
                        {
                            case "d": // day
                                if ($hours <= 4) $hours += 12;
                                break;
                            case "v": // evening
                                if ($hours <= 11) $hours += 12;
                                break;
                            case "g": // night
                                if ($hours >= 10) $hours += 12;
                                break;
                        }

                        if ($hours == 24) $hours = 0;
                    }

                    // $date->Time = new TimeSpan(hours, minutes, 0);
                    //$date->Time = new DateTime();
                    //$date->Time->setTime($hours, $minutes);
                    $date->Time = new DateInterval('PT'.$hours.'H'.$minutes.'M');
// var_dump($date->Time);
                    // remove and insert
                    $toTime = $data->Tokens[$match[0]['Index']];
                    $data->ReplaceTokensByDates($match[0]['Index'], mb_strlen($match[0]['Value']), array($date));

                    if (isset($match[2]['Value']) && $match[2]['Value'] == "t")
                    {
                        // return "to" to correct period parsing
                        $data->ReturnTokens($match[0]['Index'], "t", array($toTime));
                    }

                    return true;
                }
            }

            return false;
        }
    }

?>