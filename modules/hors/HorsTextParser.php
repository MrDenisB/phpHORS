<?php

require_once './modules/hors/Models/HorsParseResult.php';
require_once './modules/hors/Models/DatesRawData.php';
require_once './modules/hors/Utils/ParserExtractors.php';
require_once './modules/hors/Recognizers/Recognizer.php';
require_once './modules/hors/Recognizers/HolidaysRecognizer.php';
require_once './modules/hors/Recognizers/DatesPeriodRecognizer.php';
require_once './modules/hors/Recognizers/DaysMonthRecognizer.php';
require_once './modules/hors/Recognizers/MonthRecognizer.php';
require_once './modules/hors/Recognizers/RelativeDayRecognizer.php';
require_once './modules/hors/Recognizers/TimeSpanRecognizer.php';
require_once './modules/hors/Recognizers/YearRecognizer.php';
require_once './modules/hors/Recognizers/RelativeDateRecognizer.php';
require_once './modules/hors/Recognizers/DayOfWeekRecognizer.php';
require_once './modules/hors/Recognizers/TimeRecognizer.php';
require_once './modules/hors/Recognizers/PartOfDayRecognizer.php';
require_once './modules/hors/Recognizers/CCCRecognizer.php';


    class HorsTextParser
    {
//        private readonly List<Recognizer> _recognizers = DefaultRecognizers();
        private $_recognizers = [];
//        private readonly Random _random = new Random();
        private $_random;

        public function Parse($text, DateTime $userDate, $collapseDistance = 4) // (string text, DateTime userDate, int collapseDistance = 4) HorsParseResult return
        {
            mb_internal_encoding("UTF-8");
            $this->_recognizers = $this->DefaultRecognizers();
            //$_random = new Random();
            $pattern = "/[^а-яА-ЯёЁa-zA-Z0-9-]+/u";
            $tokens = preg_split($pattern, $text);
// echo"Tokens=";
// var_dump($tokens);
            $spTok = preg_split($pattern, $text, 0, PREG_SPLIT_OFFSET_CAPTURE);
            $splitTokens=array();

            $sp = 0; $spold=0;$i=0;
//            for($i=0; $i<count($tokens); )
            foreach($tokens as $idx => $tok){
                // $txt=mb_substr($text,$sp);
                $sp = ($tok=='') ? $sp : mb_strpos($text, $tok, $sp);
                $splitTokens[$i] = array($sp, ($dp=mb_strlen($tok)));                  //array($spTok[$idx][1], mb_strlen($tok));
                $tlen=$sp-$spold;
                if($i>0 && $splitTokens[$i-1][1]==0) $splitTokens[$i-1][1]=$splitTokens[$i][0]-$splitTokens[$i-1][0];
                if($i==count($tokens)-1) $splitTokens[$i][1]= mb_strlen($text) - $splitTokens[$i][0];
// echo "$sp|$dp|$tlen|$tok\n";$i++;
                $spold=$sp;
                // if($i>0) $splitTokens[$i-1]
                $sp+=$dp;
            }
// echo"splitTokens=";var_dump($splitTokens);
            
            //$offs=3; $lens=3;
            ////$slen=mb_strlen($text);
            //$sr=mb_substr($text,0,$offs).mb_substr($text,$offs+$lens,mb_strlen($text)-$offs-$lens);
            ////$sr=$sb.$fb;
            //echo $sr."\n";

            return $this->DoParse($tokens, $userDate, $collapseDistance, $text, $splitTokens);
        }
/*
        public HorsParseResult Parse(IEnumerable<string> tokensList, DateTime userDate, int collapseDistance = 4)
        {
            return DoParse(tokensList, userDate, collapseDistance);
        }
*/

        private function DoParse(array $tokensList, $userDate, 
            $collapseDistance, $sourceText = null, array $splitTokens = null)   //HorsParseResult DoParse(IEnumerable<string> tokensList, DateTime userDate, int collapseDistance, string sourceText = null, List<(int, int)> splitTokens = null)
        {
            // var tokens = tokensList.ToList();
            $tokens=$tokensList;
 // echo "\ntokensList => \$tokens\n";
 // var_dump($tokens);
            //var data = new DatesRawData
            //{
            //    Dates = new List<AbstractPeriod>(Enumerable.Repeat<AbstractPeriod>(null, tokens.Count)),
            //    Pattern = string.Join("", tokens.Select(ParserExtractors.CreatePatternFromToken)),
            //};
            //data.CreateTokens(tokens);
            $data = new DatesRawData;
            $data->Dates = array_fill(0, count($tokens), null);

            $data->Text = $sourceText;
            
            foreach($tokens as $tok)
                $data->Pattern.= ParserExtractors::CreatePatternFromToken($tok);
            $data->CreateTokens($tokens);
// echo "\nCreateTokens => \$data\n";
// var_dump($data);
//            echo "===============================================\n";

            // do work
            // _recognizers.ForEach(r => r.ParseTokens(data, userDate));
            foreach($this->_recognizers as $k=>$v){
//                echo 'Applying '.get_class($v)."\n";
//                echo "-----------------------------------------------\n";
                if($v->ParseTokens($data, $userDate)) {
                    //echo "-------------------- RESULT -------------------\n";
                    //echo "\$data\n";
                    //var_dump($data);
//                    foreach($data->Dates as $k=>$d) {
//                        if (isset($d))
//                            echo $k.'=>'.$d->ToString()."\n";
//                    }
//                    echo "==============================================\n";
                }
//              echo "-----------------------------------------------\n";
            }
            //echo "Dates in data=>\n";
            // var_dump($data);
/*
            foreach($data->Dates as $k=>$d) {
                if (isset($d))
                    echo $k.'=>'.$d->ToString()."\n";
            }
            echo "==============================================\n";
*/

//            echo "Appling collapse date start period pattern rev.\n";
            // collapse dates first batch
            $startPeriodsPattern = "(?<!(t))(@)(?=((N?[fo]?)(@)))";
            $endPeriodsPattern = "(?<=(t))(@)(?=((N?[fot]?)(@)))";
            
            // all start periods
            // Recognizer.ForAllMatches(data.GetPattern, startPeriodsPattern, m => CollapseDates(m, data, userDate), true);
            $cccr = new CCCRecognizer();
            $cccr->ForAllMatches('GetPattern', $startPeriodsPattern, 'CollapseDates', $data, $userDate, $foo, true, false);

            // echo "Dates in data after CollapseDates (start)=>\n";
            // var_dump($data);
//            foreach($data->Dates as $k=>$d) {
//                if (isset($d))
//                    echo $k.'=>'.$d->ToString()."\n";
//            }
//            echo "==============================================\n";

//            echo "Appling collapse date end period pattern rev.\n";
            // all end periods
//            Recognizer.ForAllMatches(data.GetPattern, endPeriodsPattern, m => CollapseDates(m, data, userDate), true);
            $cccr->ForAllMatches('GetPattern', $endPeriodsPattern, 'CollapseDates', $data, $userDate, $foo, true, false);
            // echo "Dates in data after CollapseDates (end)=>\n";
            // var_dump($data);
//            foreach($data->Dates as $k=>$d) {
//                if (isset($d))
//                    echo $k.'=>'.$d->ToString()."\n";
//            }
//            echo "==============================================\n";

//            echo "Appling TakeFromAdj end period pattern rev.\n";
            // take values from neighbours
            // all end periods
            // Recognizer.ForAllMatches(data.GetPattern, endPeriodsPattern, m => TakeFromAdjacent(m, data, userDate), true );
            $cccr->ForAllMatches('GetPattern', $endPeriodsPattern, 'TakeFromAdjacent', $data, $userDate, $foo, true, false);
            //echo "Dates in data after TakeFromAdjacent (start)=>\n";
            // var_dump($data);
//            foreach($data->Dates as $k=>$d) {
//                if (isset($d))
//                    echo $k.'=>'.$d->ToString()."\n";
//            }
//            echo "==============================================\n";

//            echo "Appling TakeFromAdj start period pattern rev.\n";
            // all start periods
            // Recognizer.ForAllMatches(data.GetPattern, startPeriodsPattern, m => TakeFromAdjacent(m, data, userDate), true           );
            $cccr->ForAllMatches('GetPattern', $startPeriodsPattern, 'TakeFromAdjacent', $data, $userDate, $foo, true, false);
            // echo "Dates in data after TakeFromAdjacent (start)=>\n";
            // var_dump($data);
//            foreach($data->Dates as $k=>$d) {
//                if (isset($d))
//                    echo $k.'=>'.$d->ToString()."\n";
//            }
//            echo "==============================================\n";

//            echo "Appling CollapseClosest itself pattern rev.\n";
//            echo '$data->Pattern='.$data->Pattern."\n";
            // collapse closest dates
            if ($collapseDistance > 0)
            {
                $pattern = "(@)[^@t]{1," . $collapseDistance . "}(?=(@))";
                // Recognizer.ForAllMatches(data.GetPattern, pattern, m => CollapseClosest(m, data, userDate), true);
                $cccr->ForAllMatches('GetPattern', $pattern, 'CollapseClosest', $data, $userDate, $foo, true, false);
            }
            // var_dump($data);
//            foreach($data->Dates as $k=>$d) {
//                if (isset($d))
//                    echo $k.'=>'.$d->ToString()."\n";
//            }
//            echo "==============================================\n";

            // find periods
            // finalPeriods = new List<DateTimeToken>();

//echo "data |||||||||||||||||||||||  CREATE  |||||||||||||||||||||||||| \n";
//var_dump($data);
//echo "data ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ \n";

//            echo "Creating final periods\n";
//echo "data ------------------------  DATA  -------------------------- \n";
//var_dump($data);
//echo "data ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ \n";
            $finalPeriods = array();
            // Recognizer.ForAllMatches(data.GetPattern,  "(([fo]?(@)t(@))|([fo]?(@)))", m => CreateDatePeriod(m, data, userDate, finalPeriods));
            $pattern="(([fo]?(@)t(@))|([fo]?(@)))";
            $cccr->ForAllMatches('GetPattern', $pattern, 'CreateDatePeriod', $data, $userDate, $finalPeriods, false, false);
//            foreach($data->Dates as $k=>$d) {
//                if (isset($d))
//                    echo $k.'=>'.$d->ToString()."\n";
//            }
//            echo "==============================================\n";

//            echo 'Text: '.$data->Text."\n";
//            $Str='';
//            foreach($data->Tokens as $k=>$t) {
//                if (isset($t))
//                    $Str.=$t->Value.' ';
//            }
//            echo 'Str: '.$Str."\n";
//            var_dump($finalPeriods);
//            echo 'Pattern: '.$data->Pattern."\n";
//            foreach($finalPeriods as $k=>$fp) {
//                if (isset($fp))
//                    echo $k.'=>'.$fp->ToString()."\n";
//            }
/*
echo "==============================================================================================================\n";

            echo "===ДО ФИКС ОВЕРЛЭПА =====================================================\n";

            echo "Tokens dump  :\n";
            var_dump($data->Tokens);
            echo "Periods dump :\n";
            var_dump($finalPeriods);
            echo "========================================================================================================================\n";
*/
            // if any dates overlap in source string, stretch them
//            echo "Appling FixOverlap\n";
            self::FixOverlap($finalPeriods);

/*            echo "\n==============================================\n";
            foreach($finalPeriods as $k=>$fp) {
                if (isset($fp))
                    echo $k.'=>'.$fp->ToString()."\n";
            }

            echo "========ДО ФИКС ИДЕКСА=====================================================================================\n";

            echo "Tokens dump  :\n";
            var_dump($data->Tokens);
            echo "Periods dump :\n";
            var_dump($finalPeriods);
            echo "========================================================================================================================\n";
*/
            // fix indexes because tokens between words may have length > 1
//            echo "Appling FixIndexes\n";
            self::FixIndexes($finalPeriods, $splitTokens);

/*            echo "\n==============================================\n";
            foreach($finalPeriods as $k=>$fp) {
                if (isset($fp))
                    echo $k.'=>'.$fp->ToString()."\n";
            }
            echo "\n==============================================================================================================\n";
*/

//            $srcText = implode(' ', $tokens);
//            echo 'Text       : '.$data->Text."\n";
//            echo 'TextFromT  : '.$srcText."\n";
//            $Str='';
//            $Tok = array();
//            foreach($data->Tokens as $k=>$t) {
//                if (isset($t)) {
//                    $Str.=$t->Value.' ';
//                    $Tok[]=$t->Value;
//                }
//            }
//            echo 'Tokens     : '.$Str."\n";
//            echo "Periods    :\n";
//            foreach($finalPeriods as $k=>$fp) {
//                if (isset($fp))
//                    echo $k.'=>'.$fp->ToString()."\n";
//            }

//            echo "Tokens dump  :\n";
//            var_dump($data->Tokens);
//            echo "Periods dump :\n";
//            var_dump($finalPeriods);
//            echo "========================================================================================================================\n";

/*            // return result
            var srcText = sourceText ?? string.Join(" ", tokens);
            $srcText = 
            return new HorsParseResult(srcText, data.Tokens.Select(t => t.Value).ToList(), finalPeriods);
*/
            $Result = new HorsParseResult($sourceText, $Tok, $finalPeriods);
            return $Result;
        }

        // private void FixIndexes(List<DateTimeToken> finalPeriods, List<(int, int)> splitTokens)
        private function FixIndexes($finalPeriods, $splitTokens)
        {

            if ($splitTokens == null) return;
            
            // foreach (var splitToken in splitTokens)
            foreach ($splitTokens as $st=>$splitToken)
            {
// echo "splitToken [$st] = ";
// var_dump($splitToken);
                
                foreach ($finalPeriods as $pk=>$period)
                {
//echo "period [$pk]\n";
// echo 'Old $period->StartIndex='.$period->StartIndex."\n";
// echo 'Old $period->EndIndex='.$period->EndIndex."\n";
                    if ($period->StartIndex > $splitToken[0])
                    {
                        $period->StartIndex += $splitToken[1] - 1;
                        $period->EndIndex += $splitToken[1] - 1;
                    }
                    else if ($period->StartIndex < $splitToken[0] && $period->EndIndex > $splitToken[0])
                    {
                        $period->EndIndex += $splitToken[1] - 1;
                    }
// echo 'New $period->StartIndex='.$period->StartIndex."\n";
// echo 'New $period->EndIndex='.$period->EndIndex."\n";
                }
            }

        }

        // private bool CreateDatePeriod(Match match, DatesRawData data, DateTime userDate, List<DateTimeToken> finalPeriods)
        public static function CreateDatePeriod($data, $match, $userDate, &$finalPeriods)
        {

            //DateTimeToken dateToSave;
            $dateToSave;
            
            // check matches
            if (isset($match[3]['Value']) && $match[3]['Value']!='' && isset($match[4]['Value']) && $match[4]['Value']!='')
            {
// echo "FROM - TO DATE\n";
                // this is the period "from" - "to"
//                var (fromDate, toDate) = TakeFromAdjacent(
//                    data, match.Groups[3].Index, match.Groups[4].Index);
// $t1=$match[3]['Index'];
// $t2=$match[4]['Index'];
//echo "Begin of CreateDatePeriod from $t1 and $t2\n";
                list ($fromDate, $toDate) = self::TakeFromAdjacentII($data, $match[3]['Index'], $match[4]['Index'], true);
                // final dates

//echo 'fromDate = '.$fromDate->ToString()."\n";
//echo 'toDate   = '.$toDate->ToString()."\n";

// echo "---------------------------\n";
// echo "CreateDatePeriod ConvertToToken for \$fromDate\n";
// var_dump($fromDate);
// echo $fromDate->ToString()."\n";
                
                $fromToken = self::ConvertToToken($fromDate, $userDate);

// echo "CreateDatePeriod ConvertToToken for \$toDate\n";
// var_dump($toDate);
// echo 'fromDate (after ConvertToken) = '.$fromDate->ToString()."\n";
//echo "---------------------------\n";

                $toToken = self::ConvertToToken($toDate, $userDate);
                $dateTo = $toToken->DateTo;
//echo 'toDate (after ConvertToken)   = '.$toDate->ToString()."\n";

                // correct period if end less than start
                $resolution = $toDate->MaxFixed();
                while ($dateTo < $fromToken->DateFrom)
                {
                    switch ($resolution)
                    {
                        case FixPeriod::FP_Time:
                            // $dateTo = $dateTo.AddDays(1);
                            $dateTo = $dateTo->add(new DateInterval('P1D'));
                            break;
                        case FixPeriod::FP_Day:
                            //dateTo = dateTo.AddDays(7);
                            $dateTo = $dateTo->add(new DateInterval('P7D'));
                            break;
                        case $FixPeriod::FP_Week:
                            $dateTo = $dateTo->add(new DateInterval('P1M'));
                            break;
                        case FixPeriod::FP_Month:
                            // dateTo = dateTo.AddYears(1);
                            $dateTo = $dateTo->add(new DateInterval('P1Y'));
                            break;
                    }
                }

                $dateToSave = new DateTimeToken();
                    $dateToSave->DateFrom = $fromToken->DateFrom;
                    $dateToSave->DateTo = $dateTo;
                    $dateToSave->Type = DateTimeTokenType::Period;
                    $dateToSave->HasTime = $fromToken->HasTime || $toToken->HasTime;
            }
            else
            {
// echo "SINGLE DATE\n";
                // this is single date
                $singleDate = $data->Dates[$match[6]['Index']];
// echo 'CreateDatePeriod $singleDate='.$singleDate->ToString()."\n";
 // var_dump($singleDate);
                $dateToSave = new DateTimeToken();
                $dateToSave = self::ConvertToToken($singleDate, $userDate);
 // echo "\$dateToSave=\n";
 // var_dump($dateToSave);
            }
// echo 'dateToSave (after convert)='.$dateToSave->ToString()."\n";
            // set period start and end indexes in source string
            // dateToSave.SetEdges(
            //    data.EdgesByIndex(match.Index).Start,
            //    data.EdgesByIndex(match.Index + match.Length - 1).End);
// echo "\$data->EdgesByIndex(\$match[0]['Index'])->Start = ".$data->EdgesByIndex($match[0]['Index'])->Start."\n";
// echo "\$data->EdgesByIndex(\$match[0]['Index'] + mb_strlen(\$match[0]['Value']) - 1)->End = ".$data->EdgesByIndex($match[0]['Index'] + mb_strlen($match[0]['Value']) - 1)->End."\n";
            $dateToSave->SetEdges( $data->EdgesByIndex($match[0]['Index'])->Start,
                $data->EdgesByIndex($match[0]['Index'] + mb_strlen($match[0]['Value']) - 1)->End);
// echo 'dateToSave (after setedges)='.$dateToSave->ToString()."\n";
            // save it to data
            $nextIndex = count($finalPeriods);
//echo "dateToSave ======>> \n";
//var_dump($dateToSave);
//echo "\n";
            $finalPeriods[] = $dateToSave;
// echo ">>>>>>>>>>>>>>>>>>>>finalPeriods ======>> \n";
// var_dump($finalPeriods);
// echo "\n";
            // fix final pattern
            $tmp1 = mb_substr($data->Pattern, 0, $match[0]['Index']);
            $tmp2 = mb_substr($data->Pattern, $match[0]['Index'] + mb_strlen($match[0]['Value']));

// echo "data->Pattern before cut =>>";
// var_dump($data->Pattern);
// echo "\n";
// $from=$match[0]['Index'];
// $count=mb_strlen($match[0]['Value']);
// $strl=mb_strlen($data->Pattern);
// echo "cut from=$from\n";
// echo "cut count=$count\n";
// echo "pattern len=$strl\n";
// echo "tmp1=|$tmp1|\n";
// echo "tmp2=|$tmp2|\n";

                                                                          // $"{data.Pattern.Substring(0, match.Index)}" +
            $tm = $tmp1;
            $tm.= ($match[0]['Index'] + mb_strlen($match[0]['Value']) <= mb_strlen($data->Pattern)) ?
                    '$'.$tmp2 : '';                           // $"${data.Pattern.Substring(match.Index + match.Length)}" : ""
            $data->Pattern = $tm;
// echo "data->Pattern after  cut =>>";
// var_dump($data->Pattern);
// echo "\n";
// echo 'dateToSave='.$dateToSave->ToString()."\n";
            $data->Tokens[$match[0]['Index']] = new TextToken('{'.$nextIndex.'}', $dateToSave->StartIndex, $dateToSave->EndIndex);
                $data->Tokens[$match[0]['Index']]->Value = '{'.$nextIndex.'}';
                $data->Tokens[$match[0]['Index']]->Start = $dateToSave->StartIndex;
                $data->Tokens[$match[0]['Index']]->End = $dateToSave->EndIndex;
            
            $data->Dates[$match[0]['Index']] = null;
            
// echo "data |||||||||||||||||||||||||BEFORE |||||||||||||||||||||||||| \n";
// $from=$match[0]['Index'] + 1;
// $count=mb_strlen($match[0]['Value']) - 1;
// var_dump($from);
// var_dump($count);
// var_dump($data);
// echo "data ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ \n";
            if (mb_strlen($match[0]['Value']) > 1)
            {
// $idx=$match[0]['Index'] + 1;
// $cnt=mb_strlen($match[0]['Value']) - 1;
// echo "REMOVING from $idx $cnt elements\n";
                // $data->Tokens->RemoveRange($match[0]['Index'] + 1, mb_strlen($match[0]['Value']) - 1);
                // $data->Dates->RemoveRange($match[0]['Index'] + 1, mb_strlen($match[0]['Value']) - 1);
                $data->RemoveRangeT($match[0]['Index'] + 1, mb_strlen($match[0]['Value']) - 1);
                $data->RemoveRangeD($match[0]['Index'] + 1, mb_strlen($match[0]['Value']) - 1);
            }

// echo "data |||||||||||||||||||||||  AFTER  |||||||||||||||||||||||||| \n";
// var_dump($data);
// echo "data ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ \n";
            
            // we always modify initial object so always return true
            return true;

        }



        // private DateTimeToken ConvertToToken(AbstractPeriod datePeriod, DateTime userDate)
        private static function ConvertToToken($datePeriod, $userDate)
        {
            // fill gaps
// echo "ConvertToToken \$datePeriod=\n";
// var_dump($datePeriod);
// echo "\n";
            $minFixed = $datePeriod->MinFixed();
// echo"ConvertToToken-\$minFixed=$minFixed\n";
            $datePeriod->FixDownTo($minFixed);
// echo "ConvertToToken \$datePeriod=\n";
// var_dump($datePeriod);
// echo "\n";
// echo $datePeriod->ToString()."\n";
// echo "ConvertToToken \$userDate=\n";
// var_dump($userDate);
// echo "\n";
            
            switch ($minFixed)
            {
                case FixPeriod::FP_Month:
// echo "In MIN FP_Month\n";
                    $DP = new DateTime();
                    $DP->setDate($userDate->format('Y'), $datePeriod->Date->format('m'), $datePeriod->Date->format('d'));
                    $DP->setTime(0, 0, 0);
                    $datePeriod->Date = $DP;
// echo "ConvertToToken IN FP_Month \$datePeriod=\n";
// var_dump($datePeriod);
// echo "\n";
// echo $datePeriod->ToString()."\n";
                    if ($userDate > $datePeriod->Date)
                    {
                        // take next year
                        $DP = new DateTime();
                        $DP->setDate($userDate->format('Y') + 1, $datePeriod->Date->format('m'), $datePeriod->Date->format('d'));
                        $DP->setTime(0,0,0);
                        $datePeriod->Date = $DP;
// echo "ConvertToToken IN FP_Month YEAR++ \$datePeriod=\n";
// var_dump($datePeriod);
// echo "\n";
// echo $datePeriod->ToString()."\n";
                        //    userDate.Year + 1, datePeriod.Date.Month,datePeriod.Date.Day);
                    }
                    break;
                case FixPeriod::FP_Day:
// echo "In MIN FP_Day\n";
                    // day of week fixed, take closest next
                    $userDow = (int)$userDate->format('N');  // DayOfWeek;
                    if ($userDow == 0) $userDow = 7;
                    $dateDow = (int)$datePeriod->Date->format('N'); // DayOfWeek;
                    if ($dateDow == 0) $dateDow = 7;
                    $dowDiff = $dateDow - $userDow;
                    if ($dowDiff <= 0)
                    {
                        $dowDiff += 7;
                    }

                    $newDate = clone $userDate;
                    $newDate->add(new DateInterval('P'.$dowDiff.'D'));                  // AddDays(dowDiff);
                    $datePeriod->Date = new DateTime();
                    $datePeriod->Date->setDate($newDate->format('Y'), $newDate->format('m'), $newDate->format('d'));
                    $datePeriod->Date->setTime(0,0,0);
                    break;
                case FixPeriod::FP_TimeUncertain:
                case FixPeriod::FP_Time:
// echo "In MIN FP_Time and TimeUnc\n";
                    $datePeriod->Date = $userDate;
                    break;
            }

            if ($datePeriod->IsFixed(FixPeriod::FP_Time) || $datePeriod->IsFixed(FixPeriod::FP_TimeUncertain))
            {
// echo "In === FP_Time and TimeUnc\n";
                $DT = new DateTime();
                $DT->setDate($datePeriod->Date->format('Y'), $datePeriod->Date->format('m'), $datePeriod->Date->format('d'));
                $DT->setTime($datePeriod->Time->h, $datePeriod->Time->i, 0);
                   // 0, 0  );
                $datePeriod->Date = $DT;
            }
            else
            {
// echo "In !!! FP_Time and TimeUnc\n";
                $DT = new DateTime();
                $DT->setDate($datePeriod->Date->format('Y'), $datePeriod->Date->format('m'), $datePeriod->Date->format('d'));
                $DT->setTime(0, 0, 0);
                    // datePeriod.Date.Year, datePeriod.Date.Month, datePeriod.Date.Day, 0, 0, 0, 0);
                $datePeriod->Date = $DT;
            }
            
            // determine period type and dates  
            $token = new DateTimeToken();
                $token->Type = DateTimeTokenType::Fixed;
                $token->StartIndex = $datePeriod->Start;
                $token->EndIndex = $datePeriod->End;

// echo 'In ConvertToToken $datePeriod = '.$datePeriod->ToString()."\n";
// echo 'In ConvertToToken $token = '.$token->ToString()."\n";

            $token->SetDuplicateGroup($datePeriod->DuplicateGroup);

            // set period dates by resolution
            $maxFixed = $datePeriod->MaxFixed();
 // echo"ConvertToToken-\$maxFixed=$maxFixed\n";

            switch ($maxFixed)
            {
                case FixPeriod::FP_Year:
// echo "In MAX FP_Year\n";
                    $token->Type = DateTimeTokenType::Period;
                    $token->DateFrom = new DateTime();
                    $token->DateFrom->setDate($datePeriod->Date->format('Y'), 1, 1);
                    $token->DateFrom->setTime(0,0,0);
                    $token->DateTo = new DateTime();
                    $token->DateTo->setDate($datePeriod->Date->format('Y'), 12, 31);   //  datePeriod.Date.Year,
                    $token->DateTo->setTime(23, 59, 59);
                        // 12, 31, 23, 59, 59, 999 );
                    break;
                case FixPeriod:FP_Month:
// echo "In MAX FP_Month\n";
                    $token->Type = DateTimeTokenType::Period;
                    $token->DateFrom = new DateTime();
                    $token->DateFrom->setDate($datePeriod->Date->format('Y'), $datePeriod->Date->format('m'),
                        date('t', mktime(0, 0, 0, $datePeriod->Date->format('n'), 1, $datePeriod->Date->format('Y'))));
                    $token->DateFrom->setTime(23, 59, 59);
                    // token.DateTo = new DateTime(
                       // datePeriod.Date.Year, datePeriod.Date.Month,  DateTime.DaysInMonth(datePeriod.Date.Year, datePeriod.Date.Month),
                       // 23, 59, 59, 999 );
                    break;
                case FixPeriod::FP_Week:
// echo "In MAX FP_Week\n";
                    $dayOfWeek = (int) $datePeriod->Date->format('N');  //.DayOfWeek;
                    if ($dayOfWeek == 0) $dayOfWeek = 7;
                    $token->Type = DateTimeTokenType::Period;
                    //$TmpDate = new DateTime();
                    $TmpDate1 = clone $datePeriod->Date;
                    if((1 - $dayOfWeek)<0)
                        $token->DateFrom = $TmpDate1->sub(new DateInterval('P'.-(1 - $dayOfWeek).'D'));
                    else $token->DateFrom = $TmpDate1->add(new DateInterval('P'.(1 - $dayOfWeek).'D'));
                    $TmpDate2 = clone $datePeriod->Date;
                    $t=7-$dayOfWeek;
                    $token->DateTo = $TmpDate2->add(new DateInterval('P'.$t.'D'));
                    $token->DateTo->setTime(23,59,59);
                                   // + new TimeSpan(0, 23, 59, 59, 999);
                    break;
                case FixPeriod::FP_Day:
// echo "In MAX FP_Day\n";
                    $token->Type = DateTimeTokenType::Fixed;
                    $token->DateFrom = $datePeriod->Date;
                    $token->DateTo = clone $datePeriod->Date;
                    $token->DateTo->setTime(23, 59, 59);
                                   // + new TimeSpan(0, 23, 59, 59, 999);
                    break;
                case FixPeriod::FP_TimeUncertain:
                case FixPeriod::FP_Time:
// echo "In MAX FP_Time and TimeUnc\n";
                    $token->Type = DateTimeTokenType::Fixed;
                    $token->DateFrom = $datePeriod->Date;
                    $token->DateTo = clone $datePeriod->Date;
                    $token->HasTime = true;
                    break;
            }

            if ($datePeriod->SpanDirection != 0)
            {
                $token->Type = ($datePeriod->SpanDirection == 1) ?
                    DateTimeTokenType::SpanForward :
                    DateTimeTokenType::SpanBackward;
                $token->Span = $datePeriod->Span;
            }
//echo "ConvertToToken \$token=\n";
//var_dump($token);
//echo "\n";

            return $token;
        }

        // private static bool CollapseDates(Match match, DatesRawData data, DateTime userDate)
        public static function CollapseDates($data, $match, $userDate, $isLinked)
        {
            $firstDate = $data->Dates[$match[2]['Index']];
            $secondDate = $data->Dates[$match[5]['Index']];

//var_dump($firstDate);
//var_dump($secondDate);

            if (!AbstractPeriod::CanCollapse($firstDate, $secondDate))
            {
// echo "NOT Collapsed\n";
                return false;
            }
// echo 'firstDate-minfix='.$firstDate->MinFixed()."\n";
// echo 'secondDate-minfix='.$secondDate->MinFixed()."\n";
            if ($firstDate->MinFixed() < $secondDate->MinFixed())
            {
                AbstractPeriod::CollapseTwo($secondDate, $firstDate, $isLinked);
                $secondDate->Start = $firstDate->Start;
                $data->RemoveRange($match[2]['Index'], mb_strlen($match[2]['Value']) + mb_strlen($match[4]['Value']));
// echo 'Removing from '.$match[2]['Index'].' '.mb_strlen($match[2]['Value']) + mb_strlen($match[4]['Value'])."tokens\n";
            }
            else
            {
                AbstractPeriod::CollapseTwo($firstDate, $secondDate, $isLinked);
                $firstDate->End = $secondDate->End;
                $data->RemoveRange($match[3]['Index'], mb_strlen($match[3]['Value']));
// echo 'Removing from '.$match[3]['Index'].' '.mb_strlen($match[3]['Value'])."tokens\n";
            }
            
            return true;
        }
        
        // private bool CollapseClosest(Match match, DatesRawData data, DateTime userDate)
        public static function CollapseClosest($data, $match, $userDate, $isLinked)
        {
            $firstDate = $data->Dates[$match[1]['Index']];
            $secondDate = $data->Dates[$match[2]['Index']];
// echo ">>>CollapseClosest for ".$match[1]['Index']."=\n";
// echo $firstDate->ToString()."\n";
// var_dump($data->Dates[$match[1]['Index']]);
// echo "and ".$match[2]['Index']."=\n";
// echo $secondDate->ToString()."\n";
// var_dump($data->Dates[$match[2]['Index']]);

            if (AbstractPeriod::CanCollapse($firstDate, $secondDate))
            {
// echo"МОЖНО КОЛЛАПСИТЬ\n";
                // var (firstStart, firstEnd, secondStart, secondEnd) 
                //     = (firstDate.Start, firstDate.End, secondDate.Start, secondDate.End);
                $firstStart = $firstDate->Start;
                $firstEnd = $firstDate->End;
                $secondStart = $secondDate->Start;
                $secondEnd = $secondDate->End;
                
                if ($firstDate->MinFixed() > $secondDate->MinFixed())
                {
// echo "first is base, second - cover\n";
                    AbstractPeriod::CollapseTwo($firstDate, $secondDate, $isLinked);
                }
                else
                {
// echo "second is base, first - cover\n";
                    AbstractPeriod::CollapseTwo($secondDate, $firstDate, $isLinked);
                }

                // $duplicateGroup;
                if ($firstDate->DuplicateGroup != -1)
                {
                    $duplicateGroup = $firstDate->DuplicateGroup;
                }
                else if ($secondDate->DuplicateGroup != -1)
                {
                    $duplicateGroup = $secondDate->DuplicateGroup;
                }
                else
                {
                    // $duplicateGroup = _random.Next(int.MaxValue);
                    $duplicateGroup = rand(0,65536);
                }

                // mark same as
                $secondDate->DuplicateGroup = $duplicateGroup;
                $firstDate->DuplicateGroup = $duplicateGroup;
                
                // return indexes
                $firstDate->Start = $firstStart;
                $firstDate->End = $firstEnd;
                $secondDate->Start = $secondStart;
                $secondDate->End = $secondEnd;
            }

            return false;
        }

        // private bool TakeFromAdjacent(Match match, DatesRawData data, DateTime userDate)
        public static function TakeFromAdjacent($data, $match, $userDate, $isLinked)
        {
            $ret = self::TakeFromAdjacentII($data, $match[2]['Index'], $match[5]['Index'], $isLinked);

            // this method doesn't modify tokens or array
            return false;
        }

        // private (AbstractPeriod firstDate, AbstractPeriod secondDate) TakeFromAdjacent(
        //     DatesRawData data, int firstIndex, int secondIndex)
        private static function TakeFromAdjacentII($data, $firstIndex, $secondIndex, $isLinked)
        {
            $firstDate = $data->Dates[$firstIndex];
            $secondDate = $data->Dates[$secondIndex];

            // $firstCopy = clone $firstDate;  // .CopyOf();
            // if(isset($firstCopy->Date)) $firstCopy->Date = clone $firstDate->Date;
            // if(isset($firstCopy->Time)) $firstCopy->Time = clone $firstDate->Time;
            // if(isset($firstCopy->Span)) $firstCopy->Span = clone $firstDate->Span;
            $firstCopy = $firstDate->CopyOf();
            
            //$secondCopy = secondDate.CopyOf();
            //$secondCopy = clone $secondDate;   // .CopyOf();
            //if(isset($secondCopy->Date)) $secondCopy->Date = clone $secondDate->Date;
            //if(isset($secondCopy->Time)) $secondCopy->Time = clone $secondDate->Time;
            //if(isset($secondCopy->Span)) $secondCopy->Span = clone $secondDate->Span;
            $secondCopy = $secondDate->CopyOf();
// echo 'firstDate ='.$firstDate->ToString()."\n";
// echo 'secondDate='.$secondDate->ToString()."\n";
// echo 'firstCopy ='.$firstCopy->ToString()."\n";
// echo 'secondCopy='.$secondCopy->ToString()."\n";
// echo "Logical ops =>\n";
           
            $firstCopy->Fixed &= ~$secondDate->Fixed;
            $secondCopy->Fixed &= ~$firstDate->Fixed;

// echo 'firstDate ='.$firstDate->ToString()."\n";
//  var_dump($firstDate);
// echo 'secondDate='.$secondDate->ToString()."\n";
//  var_dump($secondDate);
// echo 'firstCopy ='.$firstCopy->ToString()."\n";
//  var_dump($firstCopy);
// echo 'secondCopy='.$secondCopy->ToString()."\n";
//  var_dump($secondCopy);
            if ($firstDate->MinFixed() > $secondCopy->MinFixed())
            {
// echo "first minfixed> firstD-base secondC-cover\n";
                AbstractPeriod::CollapseTwo($firstDate, $secondCopy, $isLinked);
//echo '  firstDate = '.$firstDate->ToString()."\n";
//echo '  secondCopy   = '.$secondCopy->ToString()."\n";
            }
            else
            {
// echo "first minfixed< (else) secondC-base firstD-cover\n";
                AbstractPeriod::CollapseTwo($secondCopy, $firstDate, $isLinked);
                $data->Dates[$firstIndex] = $secondCopy;
                $secondCopy->Start = $firstDate->Start;
                $secondCopy->End = $firstDate->End;
//echo '  secondCopy   = '.$secondCopy->ToString()."\n";
//echo '  firstDate = '.$firstDate->ToString()."\n";
            }

            if ($secondDate->MinFixed() > $firstCopy->MinFixed())
            {
// echo "second minfixed> secondD-base firstC-cover\n";
                AbstractPeriod::CollapseTwo($secondDate, $firstCopy, $isLinked);
//echo '  secondDate = '.$secondDate->ToString()."\n";
//echo '  firstCopy = '.$firstCopy->ToString()."\n";
            }
            else
            {
// echo "second minfixed< (else) firstC-base secondD-cover\n";
                AbstractPeriod::CollapseTwo($firstCopy, $secondDate, $isLinked);
                $data->Dates[$secondIndex] = $firstCopy;
                $firstCopy->Start = $secondDate->Start;
                $firstCopy->End = $secondDate->End;
//echo '  secondCopy = '.$secondDate->ToString()."\n";
//echo '  firstCopy = '.$firstCopy->ToString()."\n";
            }

//echo 'firstIndexDate = '.$data->Dates[$firstIndex]->ToString()."\n";
//echo 'secondIndexDate   = '.$data->Dates[$secondIndex]->ToString()."\n";

            return array($data->Dates[$firstIndex], $data->Dates[$secondIndex]);
        }

        private function DefaultRecognizers()   // private static List<Recognizer> DefaultRecognizers()
        {
            return array   //new List<Recognizer>
            (
                new HolidaysRecognizer(),
                new DatesPeriodRecognizer(),
                new DaysMonthRecognizer(),
                new MonthRecognizer(),
                new RelativeDayRecognizer(),
                new TimeSpanRecognizer(),
                new YearRecognizer(),
                new RelativeDateRecognizer(),
                new DayOfWeekRecognizer(),
                new TimeRecognizer(),
                new PartOfDayRecognizer()
            );
        }

/*        public void SetRecognizers(params Recognizer[] recognizers)
        {
            _recognizers.Clear();
            AddRecognizers(recognizers);
        }

        public void AddRecognizers(params Recognizer[] recognizers)
        {
            _recognizers.AddRange(recognizers);
        }

        public void RemoveRecognizers(params Type[] typesToRemove)
        {
            _recognizers.RemoveAll(r => typesToRemove.Any(t => t == r.GetType()));
        }
*/

        //private void FixOverlap(List<DateTimeToken> finalPeriods)
        private function FixOverlap($finalPeriods)
        {
            //$skippedDates = new HashSet<DateTimeToken>();
            $skippedDates = [];
            foreach ($finalPeriods as $k => $period)   // (var period in finalPeriods)
            {
// echo"For $k period=".$period->ToString()."\n";
                if (!in_array($period, $skippedDates))  // if (!skippedDates.Contains(period))
                {
// echo"Period in cycle in FixOverlap\n";
// var_dump($period);
// echo "NOT in skippedDates yet\n";
                    foreach($finalPeriods as $kp => $vp)
                        if($vp->OvelappingWith($period) && !in_array($vp, $skippedDates))    // var overlapPeriods = finalPeriods
                            $overlapPeriods[] = $vp;               // .Where(p => p.OvelappingWith(period) && !skippedDates.Contains(p))
                                                        // .ToList();
                    // $minIndex = overlapPeriods.Select(p => p.StartIndex).Min();
                    $minIndex=65536;
                    foreach($overlapPeriods as $kop => $vop)
                        if(isset($vop) && $vop->StartIndex < $minIndex) $minIndex = $vop->StartIndex;
                    
                    // $maxIndex = overlapPeriods.Select(p => p.EndIndex).Max();
                    $maxIndex=0;
                    foreach($overlapPeriods as $kop => $vop)
                        if(isset($vop) && $vop->EndIndex > $maxIndex) $maxIndex = $vop->EndIndex;

                    // foreach (var p in overlapPeriods)
                    foreach ($overlapPeriods as $kop => $vop)
                    {
// echo "HELLO $kop\n";
                        $vop->StartIndex = $minIndex;
                        $vop->EndIndex = $maxIndex;
                        $skippedDates[] = $vop;
// echo "Skipped Date:".$vop->ToString()."\n";
                    }
                }
                // else echo "In skippedDates\n";
            }
        }

    }

?>