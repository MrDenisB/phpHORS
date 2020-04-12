<?php

    class RelativeDateRecognizer extends Recognizer
    {
        protected function GetRegexPattern()
        {
            return "([usxy])([Ymwd])"; // [в/на] следующей/этой/предыдущей год/месяц/неделе/день
        }

        protected function ParseMatch($data, $match, $userDate)
        {
            $date = new AbstractPeriod();
            $direction = 0;

            // relative type
            switch ($match[1]['Value'])
            {
                case "y":
                case "x":
                    $direction = 1; // "next" or "closest next"
                    break;
                case "s":
                    $direction = -1;
                    break;
            }
            
            $newDate = clone $userDate;
            // time period type
            switch ($match[2]['Value'])
            {
                case "Y":

                    // date.Date = userDate.AddYears(direction);
                    if($direction<0)
                        $newDate->sub(new DateInterval('P1Y'));
                    else $newDate->add(new DateInterval('P1Y'));
                    $date->Date = $newDate;
                    $date->Fix(array(FixPeriod::FP_Year));
                    break;
                case "m":
                    // date.Date = userDate.AddMonths(direction);
                    if($direction<0)
                        $newDate->sub(new DateInterval('P1M'));
                    else $newDate->add(new DateInterval('P1M'));
                    $date->Date = $newDate;
                    $date->FixDownTo(FixPeriod::FP_Month);
                    break;
                case "w":
                    // date.Date = userDate.AddDays(direction * 7);
                    if($direction<0)
                        $newDate->sub(new DateInterval('P7D'));
                    else $newDate->add(new DateInterval('P7D'));
                    $date->Date = $newDate;
                    $date->FixDownTo(FixPeriod::FP_Week);
                    break;
                case "d":
                    // date.Date = userDate.AddDays(direction);
                    if($direction<0)
                        $newDate->sub(new DateInterval('P1D'));
                    else $newDate->add(new DateInterval('P1D'));
                    $date->Date = $newDate;
                    $date->FixDownTo(FixPeriod::FP_Day);
                    break;
            }

            // remove and insert
            // data.ReplaceTokensByDates(match.Index, match.Length, date);
            $data->ReplaceTokensByDates($match[0]['Index'], mb_strlen($match[0]['Value']), array($date));
            return true;
        }
    }

?>