<?php

require_once './modules/hors/Utils/ParserUtils.php';
require_once './modules/hors/Models/AbstractPeriod.php';

    class DatesPeriodRecognizer extends Recognizer
    {
        protected function GetRegexPattern()   // protected override string GetRegexPattern()
        {
            return "f?(0)[ot]0(M|#)"; // с 26 до 27 января/числа
        }

        // protected override bool ParseMatch(DatesRawData data, Match match, DateTime userDate)
        protected function ParseMatch($data, $match, $userDate)
        {
            $monthFixed = false;

            // parse month
            //var mStr = data.Tokens[match.Groups[2].Index].Value;
            $mStr = $data->Tokens[$match[2]['Index']]->Value;
            echo "\nDatesPeriodRecognizer=>\n".$mStr."\n";
            $month = ParserUtils::FindIndex($mStr, Keywords::Months()) + 1;
            if ($month == 0) $month = $userDate->format('n'); // # instead M
            else $monthFixed = true;
            
            // set for first date same month as for second, ignore second (will parse further)
            $t = $data->Tokens[$match[1]['Index']];
            $day=(int)$t->Value;
            
            // current token is number, store it as a day
            $period = new AbstractPeriod();
            $period->Date = new DateTime();
            $period->Date->setDate($userDate->format('Y'), $month, ParserUtils::GetDayValidForMonth($userDate->format('Y'), $month, $day));   
            
            // fix from week to day, and year/month if it was
            // period.Fix(FixPeriod.Week, FixPeriod.Day);
            $period->Fix(array(FixPeriod::FP_Week, FixPeriod::FP_Day));
            if ($monthFixed) $period->Fix(array(FixPeriod::FP_Month));

            // replace
            // data.ReplaceTokensByDates(match.Groups[1].Index, 1, period);
            $data->ReplaceTokensByDates($match[1]['Index'], 1, array($period));

            return true;
        }
    }

?>