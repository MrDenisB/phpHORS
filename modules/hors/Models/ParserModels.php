<?php
require_once('./modules/hors/Models/MMEnumTypes.php');

    class FixPeriod extends BaseEnum
    {
        const FP_None          = 0;
        const FP_Time          = 1;
        const FP_TimeUncertain = 2;
        const FP_Day           = 4;
        const FP_Week          = 8;
        const FP_Month         = 16;
        const FP_Year          = 32;
        public static $available = array(self::FP_None, self::FP_Time, self::FP_TimeUncertain, self::FP_Day, self::FP_Week, self::FP_Month, self::FP_Year);
        public static $_available = array(0,1,2,4,8,16,32);
    }
//        define (FP_Available, array(0,1,2,4,8,16,32));

    class DateTimeTokenType extends BaseEnum
    {
        const Fixed        = 0;
        const Period       = 1;
        const SpanForward  = 2;
        const SpanBackward = 3;
        public static $available = array(self::Fixed, self::Period, self::SpanForward, self::SpanBackward);
    }

    class PartTime extends BaseEnum
    {
        const PT_None    = 0;
        const PT_Quarter = 1;
        const PT_Half    = 2;
        public static $available = array(self::PT_None, self::PT_Quarter, self::PT_Half);
    }

    class RelativeMode extends BaseEnum
    {
        const RM_None        = 0;
        const RM_Next        = 1;
        const RM_Previous    = 2;
        const RM_Current     = 3;
        const RM_CurrentNext = 4;
        public static $available = array(self::RM_None, self::RM_Next, self::RM_Previous, self::RM_Current, self::RM_CurrentNext);
    }

    class Period extends BaseEnum
    {
        const P_Minute = 0;
        const P_Hour   = 1;
        const P_Day    = 2;
        const P_Week   = 3;
        const P_Month  = 4;
        const P_Year   = 5;
        const P_None   = 6;

        public static $available = array(self::Minute, self::Hour, self::Day, self::Week, self::Month, self::Year, self::None);
    }

    class DayTime extends BaseEnum
    {
        const DT_None    = 0;
        const DT_Morning = 1;
        const DT_Noon    = 2;
        const DT_Day     = 3;
        const DT_Evening = 4;
        const DT_Night   = 5;

        public static $available = array(self::DT_None, self::DT_Morning, self::DT_Noon, self::DT_Day, self::DT_Evening, self::DT_Night);
    }


    class DateTimeToken
    {
        public $Type;                      // DateTimeTokenType  { get; set; }
        public $DateFrom;                  // DateTime  { get; set; }
        public $DateTo;                    // DateTime { get; set; }
        public $Span;                      // { get; set; }   //  TimeSpan Span // long ???
        public $HasTime;                   // { get; set; }  // bool
        
        public $StartIndex;                // { get; set; } // int
        public $EndIndex;                  // { get; set; }    // int

        private $_duplicateGroup = -1;     // int

        public function ToString()    // override string ToString
        {
// var_dump($this);
            $type='';
            if(isset ($this->Type)) {
                if($this->Type == 0) $type='FIXED';
                if($this->Type == 1) $type='PERIOD';
                if($this->Type == 2) $type='SPANF';
                if($this->Type == 3) $type='SPANB';
            }
            if($type=='') $type='N/A';
            $ht = (isset($this->HasTime) && $this->HasTime == true)? $ht = 'YES':'NO';
            $df = (isset($this->DateFrom)) ? $this->DateFrom->format('Y-m-d H:i') : 'N/A';
            $dt = (isset($this->DateTo)) ? $this->DateTo->format('Y-m-d H:i') : 'N/A';
            $sp = (isset($this->Span)) ? $this->Span->format('%a.%H:%I') : 'N/A';
            $si = (isset($this->StartIndex)) ? $this->StartIndex : '-';
            $ei = (isset($this->EndIndex)) ? $this->EndIndex : '-';
            return '[Type='.$type.', '.
                   'DateFrom='. $df .', '.
                   'DateTo='. $dt .', '.
                   'Span='. $sp .', '.
                   'HasTime='.$ht.', '.
                   'StartIndex='.$si.', '.
                   'EndIndex='.$ei.', '.
                   'DG='.$this->_duplicateGroup.']';
//            return $"[Type={Type}, " +
//                   $"DateFrom={DateFrom.ToString(CultureInfo.CurrentCulture)}, " +
//                   $"DateTo={DateTo.ToString(CultureInfo.CurrentCulture)}, " +
//                   $"Span={Span.ToString()}, " +
//                   $"HasTime={HasTime}, " +
//                   $"StartIndex={StartIndex}, " +
//                   $"EndIndex={EndIndex}]";
        }

        public function SetEdges($start, $end)   // void SetEdges(int start, int end)
        {
// echo "in SetEdges=>".$start.''.$end."\n";
            $this->StartIndex = $start;
            $this->EndIndex = $end;
        }

        public function OvelappingWith($other)  // bool OvelappingWith(DateTimeToken other)
        {
            return $this->StartIndex >= $other->StartIndex && $this->StartIndex <= $other->EndIndex
                   || $this->EndIndex >= $other->StartIndex && $this->EndIndex <= $other->EndIndex;
        }

        function SetDuplicateGroup($d)  // internal void SetDuplicateGroup(int d)
        {
            $this->_duplicateGroup = $d;
        }

        public function GetDuplicateGroup()   // internal int GetDuplicateGroup()
        {
            return $this->_duplicateGroup;
        }
    }

?>