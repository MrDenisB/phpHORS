<?php

    class TimeSpanRecognizer extends Recognizer
    {
        protected function GetRegexPattern()
        {
            return "(i)?((0?[Ymwdhe]N?)+)([bl])?"; // (через) год и месяц и 2 дня 4 часа 10 минут (спустя/назад)
        }

        protected function ParseMatch($data, $match, $userDate)
        {
            if ((isset($match[1]['Value']) && $match[1]['Value']!='') xor (isset($match[4]['Value']) && $match[4]['Value']!=''))
            {
                // if "after" of "before", but not both and not neither
                // var letters = match.Groups[2].Value.Select(s => s.ToString()).ToList();
                $letters = str_split($match[2]['Value']);
                $lastNumber = 1;
                $tokenIndex = $match[2]['Index'];
                $direction = 1; // moving to the future
                if (isset($match[4]['Value']) && $match[4]['Value'] == "b")
                {
                    $direction = -1; // "before"
                }
                
                $date = new AbstractPeriod();
                $date->SpanDirection = $direction;
                
                // save current day to offser object
                //offset = new DateTimeOffset(userDate);
                $offset = clone $userDate;

                    //echo "\nIn TimeSpanRecognizer\n";
                    //var_dump($letters);
                
                foreach($letters as $k=>$l)
                {
                    //echo "\$l=$l\n";
                    switch ($l)
                    {
                        case "N": // "and", skip it
                            break;
                        case "0": // number, store it
                            // int.TryParse(data.Tokens[tokenIndex].Value, out lastNumber);
                            $lastNumber = is_numeric($data->Tokens[$tokenIndex]->Value) ? (int)$data->Tokens[$tokenIndex]->Value : 0;
                            break;
                        case "Y": // year(s)
                            // offset = offset.AddYears(direction * lastNumber);
                            if($direction<0)
                                $offset->sub(new DateInterval('P'.$lastNumber.'Y'));
                            else $offset->add(new DateInterval('P'.$lastNumber.'Y'));
                            $years=$lastNumber;
                            $date->FixDownTo(FixPeriod::FP_Month);
                            $lastNumber = 1;
                            break;
                        case "m": // month(s)
                            //offset = offset.AddMonths(direction * lastNumber);
                            if($direction<0)
                                $offset->sub(new DateInterval('P'.$lastNumber.'M'));
                            else $offset->add(new DateInterval('P'.$lastNumber.'M'));
                            $month=$lastNumber;
                            $date->FixDownTo(FixPeriod::FP_Week);
                            $lastNumber = 1;
                            break;
                        case "w": // week(s)
                            // offset = offset.AddDays(7 * direction * lastNumber);
                            if($direction<0)
                                $offset->sub(new DateInterval('P'. 7 * $lastNumber.'D'));
                            else $offset->add(new DateInterval('P'. 7 * $lastNumber.'D'));
                            $weeks=$lastNumber;
                            $date->FixDownTo(FixPeriod::FP_Day);
                            $lastNumber = 1;
                            break;
                        case "d": // day(s)
                            // offset = offset.AddDays(direction * lastNumber);
                            if($direction<0)
                                $offset->sub(new DateInterval('P'.$lastNumber.'D'));
                            else $offset->add(new DateInterval('P'.$lastNumber.'D'));
                            $days=$lastNumber;
                            $date->FixDownTo(FixPeriod::FP_Day);
                            $lastNumber = 1;
                            break;
                        case "h": // hour(s)
                            // offset = offset.AddHours(direction * lastNumber);
                            if($direction<0)
                                $offset->sub(new DateInterval('PT'.$lastNumber.'H'));
                            else $offset->add(new DateInterval('PT'.$lastNumber.'H'));
                            $hours=$lastNumber;
                            $date->FixDownTo(FixPeriod::FP_Time);
                            $lastNumber = 1;
                            break;
                        case "e": // minute(s)
                            // offset = offset.AddMinutes(direction * lastNumber);
                            if($direction<0)
                                $offset->sub(new DateInterval('PT'.$lastNumber.'M'));
                            else $offset->add(new DateInterval('PT'.$lastNumber.'M'));
                            $minutes=$lastNumber;
                            $date->FixDownTo(FixPeriod::FP_Time);
                            break;
                    }
                    //var_dump($offset);

                    $tokenIndex++;
                }
                
                // set date
                   // date.Date = new DateTime(offset.DateTime.Year, offset.DateTime.Month, offset.DateTime.Day);
                   // if (date.IsFixed(FixPeriod.Time)) 
                   //   date.Time = new TimeSpan(offset.DateTime.Hour, offset.DateTime.Minute, 0);
                   // date.Span = offset - userDate;


                $date->Date = new DateTime();
                // $date->Date->setDate(offset.DateTime.Year, offset.DateTime.Month, offset.DateTime.Day);
                $date->Date->setDate($offset->format('Y'), $offset->format('m'), $offset->format('d'));
                if ($date->IsFixed(FixPeriod::FP_Time))
                    $date->Time = new DateInterval('PT'.$offset->format('G').'H'. $offset->format('i').'M');
                    // $date->Time = (new DateTime())->setTime($offset->format('G'), $offset->format('i'), 0);
                $date->Span = $offset->diff($userDate, true);
                
                // remove and insert
                // data.ReplaceTokensByDates(match.Index, match.Length, date);
                $data->ReplaceTokensByDates($match[0]['Index'], mb_strlen($match[0]['Value']), array($date));

                return true;
            }

            return false;
        }
    }

?>