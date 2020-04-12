<?php

require_once './modules/hors/Models/AbstractPeriod.php';
require_once './modules/hors/Dict/Keywords.php';

    class DaysMonthRecognizer extends Recognizer
    {
        protected function GetRegexPattern()  // protected string
        {
            return "((0N?)+)(M|#)"; // 24, 25, 26... и 27 января/числа
        }

        //protected override bool ParseMatch(DatesRawData data, Match match, DateTime userDate)
        protected function ParseMatch($data, $match, $userDate)
        {
            // var dates = new List<AbstractPeriod>();
            $dates = [];
            $monthFixed = false;

            // parse month
            // $mStr = $data->Tokens[match.Index + match.Groups[1].Length].Value;
            $mStr = $data->Tokens[$match[0]['Index'] + mb_strlen($match[1]['Value'])]->Value;
            //echo "\n\$mStr=$mStr\n";
            $month = ParserUtils::FindIndex($mStr, Keywords::Months()) + 1;
            //echo "\n\$month=$month\n";
            if ($month == 0) $month = $userDate->format('m'); // # instead M
            else $monthFixed = true;
            
            // create dates
            for ($i = $match[0]['Index']; $i < $match[0]['Index'] + mb_strlen($match[1]['Value']); $i++)
            {
                $t = $data->Tokens[$i];
                $day = (int) $t->Value;
                if ($day <= 0) continue; // this is "AND" or other token
                
                // current token is number, store it as a day
                $period = new AbstractPeriod();
                $period->Date = new DateTime();
                $period->Date->setDate($userDate->format('Y'), $month, ParserUtils::GetDayValidForMonth($userDate->format('Y'), $month, $day));
                
                // fix from week to day, and year/month if it was
                $period->Fix(array(FixPeriod::FP_Week, FixPeriod::FP_Day));
                if ($monthFixed) $period->Fix(array(FixPeriod::FP_Month));
                
                // store
                $dates[] = $period;
                
                // compare with last if month not fixed
                if (!$monthFixed && count($dates) > 0 && end($dates)->Date < $period->Date)
                {
                    $period->Date = new DateTime();
                    $period->Date->setDate($userDate->format('Y'),
                        $month + 1,
                        ParserUtils::GetDayValidForMonth($userDate->format('Y'), $month + 1, $day));
                }
            }
            //echo "\$dates[]=\n";
            //var_dump($dates);
            // replace all scanned tokens
            // data.ReplaceTokensByDates(match.Index, match.Length, dates.ToArray());
            $data->ReplaceTokensByDates($match[0]['Index'], mb_strlen($match[0]['Value']), $dates);
            
            return true;
        }
    }

?>