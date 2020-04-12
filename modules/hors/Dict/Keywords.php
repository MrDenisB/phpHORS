<?php

    class Keywords
    {

        public static $After            = array("через");
        public static $AfterPostfix     = array("спустя");
        public static $PreviousPostfix  = array("назад");
        public static $Next             = array("следующий", "будущий");
        public static $Previous         = array("прошлый", "прошедший", "предыдущий");
        public static $Current          = array("этот", "текущий", "нынешний");
        public static $CurrentNext      = array("ближайший", "грядущий");

        public static $Today            = array("сегодня");
        public static $Tomorrow         = array("завтра");
        public static $AfterTomorrow    = array("послезавтра");
        public static $Yesterday        = array("вчера");
        public static $BeforeYesterday  = array("позавчера");

        public static $Holiday          = array("выходной");

        public static $Second           = array("секунда", "сек");
        public static $Minute           = array("минута", "мин");
        public static $Hour             = array("час", "ч");
        
        public static $Day              = array("день");
        public static $Week             = array("неделя");
        public static $Month            = array("месяц", "мес");
        public static $Year             = array("год");

        public static $Noon             = array("полдень");
        public static $Morning          = array("утро");
        public static $Evening          = array("вечер");
        public static $Night            = array("ночь");

        public static $Half             = array("половина", "пол");
        public static $Quarter          = array("четверть");

        public static $DayInMonth       = array("число");
        public static $January          = array("январь", "янв");
        public static $February         = array("февраль", "фев");
        public static $March            = array("март", "мар");
        public static $April            = array("апрель", "апр");
        public static $May              = array("май", "мая");
        public static $June             = array("июнь", "июн");
        public static $July             = array("июль", "июл");
        public static $August           = array("август", "авг");
        public static $September        = array("сентябрь", "сен", "сент");
        public static $October          = array("октябрь", "окт");
        public static $November         = array("ноябрь", "ноя", "нояб");
        public static $December         = array("декабрь", "дек");

        public static $Monday           = array("понедельник", "пн");
        public static $Tuesday          = array("вторник", "вт");
        public static $Wednesday        = array("среда", "ср");
        public static $Thursday         = array("четверг", "чт");
        public static $Friday           = array("пятница", "пт");
        public static $Saturday         = array("суббота", "сб");
        public static $Sunday           = array("воскресенье", "вс");
        
        public static $DaytimeDay       = array("днём", "днем");
        public static $TimeFrom         = array("в", "с");
        public static $TimeTo           = array("до", "по");
        public static $TimeOn           = array("на");


        public static function Months()    // public static List<string[]> Months()
        {
            //return new List<string[]>
            //{
            //    January,February,March,April,May,June,July,August,September,October,November,December
            //};
            return array(self::$January, self::$February, self::$March, self::$April, self::$May, self::$June, self::$July, self::$August, self::$September, self::$October, self::$November, self::$December);
        }

        public static function DaysOfWeek()   // public static List<string[]> DaysOfWeek()
        {
            //return new List<string[]>
            //{
            //    Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday
            //};
            return array(self::$Monday, self::$Tuesday, self::$Wednesday, self::$Thursday, self::$Friday, self::$Saturday, self::$Sunday);
        }
        

        public function AllValues()   // public List<string> AllValues()
        {
          $summary=array();
          foreach (get_class_vars('Keywords') as $key=>$value){
              foreach($value as $val)
               $summary[]=$val;
          }
          return $summary;
//            var values = new List<string>();
//            GetType()
//                .GetFields(BindingFlags.Static | BindingFlags.Public)
//                .ToList()
//                .ForEach(f =>
//                {
//                    var words = (string[]) f.GetValue(null);
//                    words.ToList().ForEach(values.Add);
//                });    
//            return values;
        }
    }
?>
