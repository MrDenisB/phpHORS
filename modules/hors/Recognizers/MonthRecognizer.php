<?php

require_once './modules/hors/Models/AbstractPeriod.php';
require_once './modules/hors/Dict/Keywords.php';

    class MonthRecognizer extends Recognizer
    {
        protected function GetRegexPattern()
        {
            return "([usxy])?M"; // [в] (прошлом|этом|следующем) марте
        }

        protected function ParseMatch($data, $match, $userDate)
        {
            $year = $userDate->format('Y');
            $yearFixed = false;

            // parse month
            $mStr = $data->Tokens[$match[0]['Index'] + mb_strlen($match[1]['Value'])]->Value;
            $month = ParserUtils::FindIndex($mStr, Keywords::Months()) + 1;
            if ($month == 0) $month = $userDate->format('m');

            $monthPast = $month < $userDate->format('m');
            $monthFuture = $month > $userDate->format('m');
            
            // check if relative
            // if (match.Groups[1].Success)
            if (isset($match[1]['Value']) && $match[1]['Value']!='')
            {
                switch ($match[1]['Value'])
                {
                    case "s": // previous
                        if (!$monthPast) $year--;
                        break;
                    case "u": // current
                        break;
                    case "y": // current-next
                        if ($monthPast) $year++;
                        break;
                    case "x": // next
                        if (!$monthFuture) $year++;
                        break;
                }

                $yearFixed = true;
            }
            
            // create date
            $date = new AbstractPeriod();
            $date->Date = new DateTime();
            $date->Date->setDate($year, $month, 1);
            
            // fix month and maybe year
            $date->Fix(array(FixPeriod::FP_Month));
            if ($yearFixed) $date->Fix(array(FixPeriod::FP_Year));
            
            // remove and insert
            // data.ReplaceTokensByDates(match.Index, match.Length, date);
            $data->ReplaceTokensByDates($match[0]['Index'], mb_strlen($match[0]['Value']), array($date));

            return true;
        }
    }

?>