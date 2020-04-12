<?php

    class PartOfDayRecognizer extends Recognizer
    {
        protected function GetRegexPattern()
        {
            return "(@)?f?([ravgdn])f?(@)?"; // (дата) (в/с) утром/днём/вечером/ночью (в/с) (дата)
        }

        protected function ParseMatch($data, $match, $userDate)
        {
            if ((isset($match[1]['Value']) && $match[1]['Value']!='') || (isset($match[3]['Value']) && $match[3]['Value']!=''))
            {
// echo "\$match=\n";
// var_dump($match);
                $hours = 0;
                switch ($match[2]['Value'])
                {
                    case "r": // morning 
                        $hours = 9;
                        break;
                    case "a": // day
                    case "d":
                    case "n": // noon
                        $hours = 12;
                        break;
                    case "v": // evening
                        $hours = 17;
                        break;
                    case "g": // night
                        $hours = 23;
                        break;
                }

                if ($hours != 0)
                {
                    $date = new AbstractPeriod();
                    $date->Time = new DateInterval('PT'.$hours.'H');
                    //$date->Time->setTime($hours, 0);
                    //  Time = new TimeSpan(hours, 0, 0)

                    $date->Fix(array(FixPeriod::FP_TimeUncertain));
                
                    // remove and insert
                    $startIndex = $match[0]['Index'];
                    $length = mb_strlen($match[0]['Value']) - 1; // skip date at the beginning or ar the end
                    if (isset($match[1]['Value']) && $match[1]['Value']!='')
                    {
                        // skip first date
                        $startIndex++;
                        if (isset($match[3]['Value']) && $match[3]['Value']!='')
                        {
                            // skip both dates at the beginning and at the end
                            $length--;
                        }
                    }
                    $data->ReplaceTokensByDates($startIndex, $length, array($date));

                    return true;
                }
            }

            return false;
        }
    }

?>
