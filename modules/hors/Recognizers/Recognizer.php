<?php

    abstract class Recognizer
    {
        public function ParseTokens($data, $userDate)    // void ParseTokens(DatesRawData data, DateTime userDate)
        {
            // ForAllMatches(data.GetPattern, pattern: GetRegexPattern(), action: m => ParseMatch(data, m, userDate));
            return Recognizer::ForAllMatches('GetPattern', $this->GetRegexPattern(), 'ParseMatch', $data, $userDate, $foo, false, false);
        }

        //public static void ForAllMatches(Func<string> input, string pattern, Predicate<Match> action, bool reversed = false)
        public function ForAllMatches($input, $pattern, $action, $data, $userDate, &$finalPeriods, $reversed, $isLinked)
        {
            //var matches = Regex.Matches(input.Invoke(), pattern);
            $text = $data->$input();
//            echo"0->\$text=$text\n";
//            echo"0->\$pattern=$pattern\n";
            preg_match_all('/'.$pattern.'/', $text, $phpmatches, PREG_OFFSET_CAPTURE);                       //  INPUT.INVOKE()
            foreach($phpmatches as $mkey => $mval)
                foreach($mval as $vkey => $vval){
                  $matches[$vkey][$mkey]['Value'] = $phpmatches[$mkey][$vkey][0];
                  $matches[$vkey][$mkey]['Index'] = $phpmatches[$mkey][$vkey][1];
                }
            $matches_num = count($matches);
            // if ($matches == 0)
            if (!$matches_num)
            {
//                echo "PATTERN NOT FOUND\n";
                return false;
            }
//            echo "PATTERN FOUND\n";
//            echo "+++++++++++++++++++++++++++++++++++++++++++++++\n";
            // var match = reversed ? matches[matches.Count - 1] : matches[0];
            $match = $reversed ? $matches[$matches_num - 1] : $matches[0];
            $m = $reversed ? ($matches_num - 1) : 0;

            // var indexesToSkip = new HashSet<int>();
            $indexesToSkip = [];

            // while (match != null && match.Success)
            while (isset($match))
            {
                // var text = input.Invoke();
                $text = $data->$input();                                                                               //  INPUT.INVOKE()
// echo"1->\$text=$text\n";
// echo"1->\$pattern=$pattern\n";
// echo"1->\$matches=\n";
// var_dump($matches);
        // echo"\n\$match=";
        // var_dump($match);
                // var matchIndex = reversed ? text.Length - match.Index : match.Index;
                $matchIndex = $reversed ? mb_strlen($text) - $match[0]['Index'] : $match[0]['Index'];
                
                // if (!action.Invoke(match))
                if($action=='ParseMatch') $res = $this->$action($data, $match, $userDate);
                if($action=='CollapseDates' || $action=='TakeFromAdjacent' || $action=='CollapseClosest')
                    $res = HorsTextParser::$action($data, $match, $userDate, $isLinked);
                if($action=='CreateDatePeriod')
                    $res = HorsTextParser::$action($data, $match, $userDate, $finalPeriods);
                
                if (!$res)                                                         //  ACTION.INVOKE()
                {
                    $indexesToSkip[] = $matchIndex;
                    //echo "matchIndex to skip added $matchIndex\n";
                    //var_dump($indexesToSkip);
                }

                unset($match);
                unset($matches);
                // text = input.Invoke();
                $text = $data->$input();
                // matches = Regex.Matches(text, pattern);
                preg_match_all('/'.$pattern.'/', $text, $phpmatches, PREG_OFFSET_CAPTURE);
                foreach($phpmatches as $mkey => $mval)
                    foreach($mval as $vkey => $vval){
                        $matches[$vkey][$mkey]['Value'] = $phpmatches[$mkey][$vkey][0];
                        $matches[$vkey][$mkey]['Index'] = $phpmatches[$mkey][$vkey][1];
                    }
// echo"\nN->\$text=$text\n";
////echo"\n->\$match=";
////var_dump($match);
// echo"N->\$pattern=$pattern\n";
// echo"N->\$matches=";
// var_dump($matches);
                $matches_num = count($matches);
                for ($i = 0; $i < $matches_num; $i++)
                {
                    $index = $reversed ? $matches_num - $i - 1 : $i;
                    //echo "\$index=$index\n";
                    // matchIndex = reversed ? text.Length - matches[index].Index : matches[index].Index;
                    $matchIndex = $reversed ? mb_strlen($text) - $matches[$index][0]['Index'] : $matches[$index][0]['Index'];
                    //echo "\$matchIndex=$matchIndex\n";
                    // if (!indexesToSkip.Contains(matchIndex))
                    if (!in_array($matchIndex, $indexesToSkip))
                    {
                        //echo "Setting current match to index $index\n";
                        $match = $matches[$index];
                        break;
                    }
                }
            }
            return true;
        }

        // protected abstract string GetRegexPattern();
        protected abstract function GetRegexPattern();

        // protected abstract bool ParseMatch(DatesRawData data, Match match, DateTime userDate);
        protected abstract function ParseMatch($data, $match, $userDate); 
    }
?>
