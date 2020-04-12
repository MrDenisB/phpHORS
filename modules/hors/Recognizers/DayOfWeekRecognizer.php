<?php

require_once './modules/hors/Models/AbstractPeriod.php';
require_once './modules/hors/Dict/Keywords.php';

    class DayOfWeekRecognizer extends Recognizer
    {
        protected function GetRegexPattern()
        {
            return "([usxy])?(D)"; // [в] (следующий/этот/предыдущий) понедельник
        }

        protected function ParseMatch($data, $match, $userDate)
        {
            $date = new AbstractPeriod();
            
            // day of week
            $dayOfWeek = ParserUtils::FindIndex($data->Tokens[$match[2]['Index']]->Value, Keywords::DaysOfWeek()) + 1;
            $tmp=$data->Tokens[$match[2]['Index']]->Value;
// echo "Day of week Token is - $tmp\n";
// echo "Day of week is - $dayOfWeek\n";
            $userDayOfWeek = (int) $userDate->format('N');          //DayOfWeek;
            if ($userDayOfWeek == 0) $userDayOfWeek = 7; // starts from Monday, not Sunday
            $diff = $dayOfWeek - $userDayOfWeek;

            $tmpDate = clone $userDate;
            if (isset($match[1]['Value']) && $match[1]['Value']!='')
            {
                switch ($match[1]['Value'])
                {
                    case "y": // "closest next"
                        if ($diff < 0) $diff += 7;
                        // $date->Date = userDate.AddDays(diff);
                        $date->Date = $tmpDate->add(new DateInterval('P'. $diff .'D'));            // надо проверять знаки...
                        break;
                    case "x": // "next"
                        // date.Date = userDate.AddDays(diff + 7);
                        $val = $diff + 7;
                        $date->Date = $tmpDate->add(new DateInterval('P'. $val .'D'));
                        break;
                    case "s": // "previous"
                        $val = -$diff + 7;
                        $date->Date = $tmpDate->sub(new DateInterval('P'. $val .'D'));
                        break;
                    case "u": // "current"
                        if($diff<0)
                            $date->Date = $tmpDate->sub(new DateInterval('P'. -$diff .'D'));       // должна работать привязка к текущей неделе....
                        else $date->Date = $tmpDate->add(new DateInterval('P'. $diff .'D'));
                        break;
                }
                $date->FixDownTo(FixPeriod::FP_Day);
                //echo "FixDownTo FP_Day\n";
            }
            else
            {
                if($diff<0)
                    $date->Date = $tmpDate->sub(new DateInterval('P'. -$diff .'D'));       // должна работать привязка к текущей неделе....
                else $date->Date = $tmpDate->add(new DateInterval('P'. $diff .'D'));
                //$date->Date = $tmpDate->add(new DateInterval('P'.$diff.'D'));
                $date->Fix(array(FixPeriod::FP_Day));
                $date->FixDayOfWeek = true;
                //echo "FixDDayOfWeek=true\n";
            }
            
            // remove and insert
            $data->ReplaceTokensByDates($match[0]['Index'], mb_strlen($match[0]['Value']), array($date));

            return true;
        }
    }
?>