<?php
require_once './modules/hors/Models/ParserModels.php';

    class AbstractPeriod implements IHasEdges
    {
        public $Date;   //DateTime Date
        public $Time;            //TimeSpan Time
        public $Fixed;       //byte Fixed
        public $Span;            //TimeSpan Span
        public $SpanDirection;

        public $Start;           // { get; set; }
        public $End;             // { get; set; }

        public $DuplicateGroup = -1;  // { get; set; } = -1;
        public $FixDayOfWeek = false; // { get; set; } = false;

        private static $_maxPeriod = -1;  // int

        public function Fix($fixes)    // void Fix(params FixPeriod[] fixes)
        {
            //echo "\nFIX => \$fixes=\n";
            //var_dump($fixes);
            //echo 'Old value '.$this->Fixed."\n";
            foreach ($fixes as $k=>$f){
                //echo 'OR value '. $f."\n";
                $this->Fixed |= $f;
            }
            //echo 'New value '.$this->Fixed."\n";
        }
        
        public function UnFix($time)    // void UnFix(FixPeriod time)
        {
            $this->Fixed &= ~$time;
        }

        public function FixDownTo($period)   // void FixDownTo(FixPeriod period)
        {
            for ($i = $this->GetMaxPeriod(); $i >= 0; $i--) {
                $toFix = pow(2, $i); //(FixPeriod)
                if ($toFix < $period)
                    return;

                $this->Fix(array($toFix));
            }
        }

        private static function GetMaxPeriod()       // private static int GetMaxPeriod()
        {
            if (self::$_maxPeriod == -1)
            {
                $maxVal = 0;
                //var_dump(FixPeriod::$available);
                foreach(FixPeriod::$available as $k=>$p)
                    if ($p > $maxVal)
                        $maxVal = $p;
                self::$_maxPeriod = (int) log($maxVal, 2);
            }

            return self::$_maxPeriod;
        }

        //public function AbstractPeriod CopyOf()
        public function CopyOf()
        {
            $apn = new AbstractPeriod();
            //$apn->Date = new DateTime();
            if(isset($this->Date)) $apn->Date = clone $this->Date;
            //$ap->Date;

            if(isset($this->Time)) {
                $apn->Time = new DateInterval('P0D');
                $DT1 = new DateTime();
                $DT2 = clone $DT1;
                $DT1->add($this->Time);
                $apn->Time = $DT1->diff($DT2);
            }

            // $apn->Span = $ap->Span;
            if(isset($this->Span)) {
                $apn->Span = new DateInterval('P0D');
                $DT1 = new DateTime();
                $DT2 = clone $DT1;
                $DT1->add($this->Span);
                $apn->Span = $DT1->diff($DT2);
            }

            $apn->Fixed = $this->Fixed;
            $apn->SpanDirection = $this->SpanDirection;
            $apn->Start = $this->Start;
            $apn->End = $this->End;
            $apn->DuplicateGroup = $this->DuplicateGroup;
            $apn->FixDayOfWeek = $this->FixDayOfWeek;
            return $apn;
        }

        public function MinFixed()     //public FixPeriod MinFixed()
        {
            for ($i = self::GetMaxPeriod(); $i >= 0; $i--)
            {
                $p = pow(2, $i); // (FixPeriod)
                if ($this->IsFixed($p))
                    return $p;
            }

            return FixPeriod::FP_None;
        }

        public function MaxFixed()    // public FixPeriod MaxFixed()
        {
            for ($i = 0; $i <= self::GetMaxPeriod(); $i++)
            {
                $p = pow(2, $i); // (FixPeriod)
                if ($this->IsFixed($p))
                    return $p;
            }

            return FixPeriod::FP_None;
        }

        public function IsFixed($period)     //public bool IsFixed(FixPeriod period)
        {
            return ($this->Fixed & $period) > 0;
        }

        public function ToString()     // public override string ToString()
        {
            if (isset($this->Date)) $D = $this->Date->format('Y-m-d'); else $D = 'N/A';
            if (isset($this->Date)) $T = $this->Date->format('H:i'); else $T = 'N/A';
            if (isset($this->Time)) $TI = $this->Time->format('%H:%I'); else $TI = 'N/A';
            if (isset($this->Span)) $S = $this->Span->format('%H:%I'); else $S = 'N/A';
            if (isset($this->Fixed)) $F = base_convert($this->Fixed, 10, 2); else $F = 'N/A';
            return '[Date='.$D.', Time='.$T.', Interval='.$TI.', Span='.$S.', Fixed='.$F.', S='.$this->Start.', E='.$this->End.', DG='.$this->DuplicateGroup.']';                                                         // УТОЧНИТЬ !!!
        }

        // public static bool CanCollapse(AbstractPeriod basePeriod, AbstractPeriod coverPeriod)
        public static function CanCollapse($basePeriod, $coverPeriod)
        {
            if (($basePeriod->Fixed & $coverPeriod->Fixed) != 0) return false;
            return $basePeriod->SpanDirection != -$coverPeriod->SpanDirection || $basePeriod->SpanDirection == 0;
        }

        // public static bool CollapseTwo(AbstractPeriod basePeriod, AbstractPeriod coverPeriod)
        public static function CollapseTwo($basePeriod, $coverPeriod, $isLinked)
        {
            if (!self::CanCollapse($basePeriod, $coverPeriod)) return false;
//echo "Begin of CollapseTwo\n";
//var_dump($basePeriod);
//var_dump($coverPeriod);

            // if span
            if ($basePeriod->SpanDirection != 0)
            {
                if ($coverPeriod->SpanDirection != 0)
                {
                    // if another date is Span, just add spans together
                    $basePeriod->Span += $coverPeriod->Span;
                }
            }
            
            // take year if it is not here, but is in other date
            // if (!basePeriod.IsFixed(FixPeriod.Year) && coverPeriod.IsFixed(FixPeriod.Year))
            if (!$basePeriod->IsFixed(FixPeriod::FP_Year) && $coverPeriod->IsFixed(FixPeriod::FP_Year))
            {
// echo "In FP_Year\n";
                // basePeriod.Date = new DateTime(coverPeriod.Date.Year, basePeriod.Date.Month, basePeriod.Date.Day);
                $DT = new DateTime();
                $DT->setDate($coverPeriod->Date->format('Y'), $basePeriod->Date->format('m'), $basePeriod->Date->format('d'));
                $DT->setTime(0,0,0);
                $basePeriod->Date = $DT;
                //echo $base_date->format('Y-m-d')."\n";
                $basePeriod->Fix(array(FixPeriod::FP_Year));
            }

            // take month if it is not here, but is in other date
            if (!$basePeriod->IsFixed(FixPeriod::FP_Month) && $coverPeriod->IsFixed(FixPeriod::FP_Month))
            {
// echo "In FP_Month\n";
                // basePeriod.Date = new DateTime(basePeriod.Date.Year, coverPeriod.Date.Month, basePeriod.Date.Day);
                $DT = new DateTime();
                $DT->setDate($basePeriod->Date->format('Y'), $coverPeriod->Date->format('m'), $basePeriod->Date->format('d'));
                $DT->setTime(0,0,0);
                $basePeriod->Date = $DT;
                $basePeriod->Fix(array(FixPeriod::FP_Month));
            }

            // week and day
            // if (!basePeriod.IsFixed(FixPeriod.Week) && coverPeriod.IsFixed(FixPeriod.Week))
            if (!$basePeriod->IsFixed(FixPeriod::FP_Week) && $coverPeriod->IsFixed(FixPeriod::FP_Week))
            {
// echo "In FP_Week\n";
                // the week is in another date, check where is a day
                if ($basePeriod->IsFixed(FixPeriod::FP_Day))
                {
                    // set day of week, take date
                    $basePeriod->Date = self::TakeDayOfWeekFrom($coverPeriod->Date, $basePeriod->Date);
                    $basePeriod->Fix(array(FixPeriod::FP_Week));
                }
                else if (!$coverPeriod->IsFixed(FixPeriod::FP_Day))
                {
                    // only week here, take it by taking a day
                    $DT = new DateTime();
                    $DT->setDate($basePeriod->Date->format('Y'), $basePeriod->Date->format('m'), $coverPeriod->Date->format('d'));
                    $DT->setTime(0,0,0);
                    $basePeriod->Date = $DT;
                    //(basePeriod.Date.Year, basePeriod.Date.Month, coverPeriod.Date.Day);
                    $basePeriod->Fix(array(FixPeriod::FP_Week));
                }
            }
            else if ($basePeriod->IsFixed(FixPeriod::FP_Week) && $coverPeriod->IsFixed(FixPeriod::FP_Day))
            {
// echo "In FP_Week and Day\n";
                // here is a week, but day of week in other date
                $DT = new DateTime();
                $DT = self::TakeDayOfWeekFrom($basePeriod->Date, $coverPeriod->Date);
                $basePeriod->Date = $DT;
                $basePeriod->Fix(array(FixPeriod::FP_Week, FixPeriod::FP_Day));
            }
            
            // day
            if (!$basePeriod->IsFixed(FixPeriod::FP_Day) && $coverPeriod->IsFixed(FixPeriod::FP_Day))
            {
// echo "In FP_Day\n";
                if ($coverPeriod->FixDayOfWeek)
                {
                    // take only day of week from cover
                    $nd = new DateTime();
                    $nd->setDate($basePeriod->Date->format('Y'), $basePeriod->Date->format('m'), $basePeriod->IsFixed(FixPeriod::FP_Week) ?
                        $basePeriod->Date->format('d') : 1);
                    $basePeriod->Date = self::TakeDayOfWeekFrom($nd, $coverPeriod->Date, !$basePeriod->IsFixed(FixPeriod::FP_Week));
                    $basePeriod->Fix(array(FixPeriod::FP_Week, FixPeriod::FP_Day));
                }
                else
                {
                    // take day from cover
                    $DT = new DateTime();
                    $DT->setDate($basePeriod->Date->format('Y'), $basePeriod->Date->format('m'), $coverPeriod->Date->format('d'));
                    $DT->setTime(0,0,0);
                    $basePeriod->Date = $DT;
                    // (basePeriod.Date.Year, basePeriod.Date.Month, coverPeriod.Date.Day);
                    $basePeriod->Fix(array(FixPeriod::FP_Week, FixPeriod::FP_Day));
                }
            }
            
            // time
            $timeGot = false;
            
// echo "In CollapseTwo base=".$basePeriod->ToString()."\n";
// echo "In CollapseTwo cover=".$coverPeriod->ToString()."\n";
            if (!$basePeriod->IsFixed(FixPeriod::FP_Time) && $coverPeriod->IsFixed(FixPeriod::FP_Time))
            {
// echo "In FP_Time\n";
                $basePeriod->Fix(array(FixPeriod::FP_Time));
                if (!$basePeriod->IsFixed(FixPeriod::FP_TimeUncertain))
                    $basePeriod->Time = $coverPeriod->Time;
                else
                    if ($basePeriod->Time->h <= 12 && $coverPeriod->Time->h > 12){
                        if(!$isLinked) {
                            $ti1 = new DateInterval('PT12H');
                            $dt1 = new DateTime();
                            $dt2 = clone $dt1;
                            $dt1->add($basePeriod->Time);
                            $dt1->add($ti1);
                            $ti1 = $dt1->diff($dt2, true);
                            // $basePeriod->Time += new TimeSpan(12, 0, 0);
                            $basePeriod->Time = $ti1;
                        }
// echo "In FP_Time added\n";
                    }
                $timeGot = true;
            }
//echo "After FP_Time\n";
//var_dump($basePeriod);
//echo $basePeriod->ToString()."\n";
//var_dump($coverPeriod);
//echo $coverPeriod->ToString()."\n";
            
            if (!$basePeriod->IsFixed(FixPeriod::FP_TimeUncertain) && $coverPeriod->IsFixed(FixPeriod::FP_TimeUncertain))
            {
// echo "In FP_TimeUncertain\n";
                $basePeriod->Fix(array(FixPeriod::FP_TimeUncertain));
                if ($basePeriod->IsFixed(FixPeriod::FP_Time))
                {
                    // take time from cover, but day part from base
                    $offset = $coverPeriod->Time->h <= 12 && $basePeriod->Time->h > 12 ? 12 : 0;
                    // basePeriod.Time = new TimeSpan(coverPeriod.Time.Hours + offset, coverPeriod.Time.Minutes, 0);
// echo "\$offset=$offset\n";                    
                    $dth = ($coverPeriod->Time->h + $offset != 0) ? $coverPeriod->Time->h + $offset.'H' :'';
                    $dti = ($coverPeriod->Time->i != 0) ? $coverPeriod->Time->i.'M' : '';
                    
                    if($dth != '' || $dti != '')
                     $basePeriod->Time = new DateInterval('PT'.$dth.$dti);
                    else {
                        $DT = new DateTime(); // Нулевой интервал
                        $DI = $DT->diff($DT);
                        $basePeriod->Time = $DI;
                    }
// echo "In FP_TU added\n";
                }
                else
                {
                    $basePeriod->Time = $coverPeriod->Time;
                    $timeGot = true;
                }
            }
//echo "After FP_Uncert_Time\n";
//var_dump($basePeriod);
//var_dump($coverPeriod);

            // if this date is Span and we just got time from another non-span date, add this time to Span
            if ($timeGot && $basePeriod->SpanDirection != 0 && $coverPeriod->SpanDirection == 0)
            {
                $DT1 = new DateTime();
                $DT2 = clone($da);
                $DT1->add($basePeriod->Span);
                if($basePeriod->SpanDirection == 1)
                    $DT1->add($basePeriod->Time);
                else $DT1->sub($basePeriod->Time);
                $basePeriod->Span = $DT2->diff($DT1, true);

                // $basePeriod->Span += $basePeriod->SpanDirection == 1 ? $basePeriod->Time : -$basePeriod->Time;
            }

            // set tokens edges
            $basePeriod->Start = min($basePeriod->Start, $coverPeriod->Start);
            $basePeriod->End = max($basePeriod->End, $coverPeriod->End);

//echo "After Collapse\n";
//var_dump($basePeriod);
//echo $basePeriod->ToString()."\n";
//var_dump($coverPeriod);
//echo $coverPeriod->ToString()."\n";

            return true;
        }


        
        // public static DateTime TakeDayOfWeekFrom(DateTime currentDate, DateTime takeFrom, bool onlyForward = false)
        public static function TakeDayOfWeekFrom($currentDate, $takeFrom, $onlyForward = false)
        {
            $needDow = $takeFrom->format('N');
//echo "\$needDow = $needDow\n";
            if ($needDow == 0) $needDow = 7;
            $currentDow = $currentDate->format('N');
//echo "\$currentDow = $currentDow\n";
            if ($currentDow == 0) $currentDow = 7;
            $diff = $needDow - $currentDow;
            if ($onlyForward && $diff < 0) $diff += 7;
            if($diff < 0)
                $currentDate->sub(new DateInterval('P'. -$diff .'D'));
            else $currentDate->add(new DateInterval('P'.$diff.'D'));
            return $currentDate;
        }

        public function SetEdges($startIndex, $endIndex)    // public void SetEdges(int $startIndex, int $endIndex)
        {
            $this->Start = $startIndex;
            $this->End = $endIndex;
        }

        function setStart($s)
        {
         $this->Start = $s;  
        }

        function setEnd($e)
        {
         $this->End = $e;  
        }


    }
?>