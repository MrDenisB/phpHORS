<?php

require_once './modules/hors/Dict/Keywords.php';
require_once './modules/hors/Dict/Morph.php';
require_once './modules/hors/Models/ParserModels.php';


    class ParserExtractors    // internal static class ParserExtractors
    {
        static function CreatePatternFromToken($token)    // internal static string CreatePatternFromToken(string token)
        {
            $t = trim( mb_ereg_replace( "[^0-9а-яё-]", "", mb_strtolower($token)));

            if (Morph::HasOneOfLemmas($t, Keywords::$Year)) return "Y";
            
            if (Morph::HasOneOfLemmas($t, Keywords::Months())) return "M";
            if (Morph::HasOneOfLemmas($t, Keywords::DaysOfWeek())) return "D";
            if (Morph::HasOneOfLemmas($t, Keywords::$PreviousPostfix)) return "b";
            if (Morph::HasOneOfLemmas($t, Keywords::$AfterPostfix)) return "l";
            if (Morph::HasOneOfLemmas($t, Keywords::$After)) return "i";
            if (Morph::HasOneOfLemmas($t, Keywords::$Holiday)) return "W";
            
            $p = self::PeriodFromToken($t);
            switch ($p)
            {
                case P_Minute:
                    return "e";
                case P_Hour:
                    return "h";
                case P_Day:
                    return "d";
                case P_Week:
                    return "w";
                case P_Month:
                    return "m";
            }

            $r = self::RelativeModeFromToken($t);
            switch ($r)
            {
                case RM_Previous:
                    return "s";
                case RM_Current:
                    return "u";
                case RM_CurrentNext:
                    return "y";
                case RM_Next:
                    return "x";
            }

            $n = self::NeighbourDaysFromToken($t);
            if ($n > -2000000)
            {
                $n+=4;
                return "$n";
            }

            $d = self::DaytimeFromToken($t);
            switch ($d)
            {
                case DT_Morning:
                    return "r";
                case DT_Noon:
                    return "n";
                case DT_Day:
                    return "a";
                case DT_Evening:
                    return "v";
                case DT_Night:
                    return "g";
            }

            $pt = self::PartTimeFromToken($t);
            switch ($pt)
            {
                case PT_Quarter:
                    return "Q";
                case PT_Half:
                    return "H";
            }
            
            if (is_numeric($t))
            {
                if ($t < 0 || $t > 9999) return "_";
                if ($t > 1900) return "1";
                return "0";
            }
            
            if (Morph::HasOneOfLemmas($t, Keywords::$TimeFrom)) return "f";
            if (Morph::HasOneOfLemmas($t, Keywords::$TimeTo)) return "t";
            if (Morph::HasOneOfLemmas($t, Keywords::$TimeOn)) return "o";
            if (Morph::HasOneOfLemmas($t, Keywords::$DayInMonth)) return "#";
            
            if ($t == "и") return "N";

            return "_";
        }
        
        private static function PartTimeFromToken($t)    // private static PartTime PartTimeFromToken(string t)
        {
            if (Morph::HasOneOfLemmas($t, Keywords::$Quarter)) return PT_Quarter;
            if (Morph::HasOneOfLemmas($t, Keywords::$Half)) return PT_Half;

            return PT_None;
        }

        private static function DaytimeFromToken($t)    // private static DayTime DaytimeFromToken(string t)
        {
            if (Morph::HasOneOfLemmas($t, Keywords::$Noon)) return DT_Noon;
            if (Morph::HasOneOfLemmas($t, Keywords::$Morning)) return DT_Morning;
            if (Morph::HasOneOfLemmas($t, Keywords::$Evening)) return DT_Evening;
            if (Morph::HasOneOfLemmas($t, Keywords::$Night)) return DT_Night;
            if (Morph::HasOneOfLemmas($t, Keywords::$DaytimeDay)) return DT_Day;

            return DT_None;
        }

        private static function PeriodFromToken($t)   // private static Period PeriodFromToken(string t)
        {
            if (Morph::HasOneOfLemmas($t, Keywords::$Year)) return P_Year;
            if (Morph::HasOneOfLemmas($t, Keywords::$Month)) return P_Month;
            if (Morph::HasOneOfLemmas($t, Keywords::$Week)) return P_Week;
            if (Morph::HasOneOfLemmas($t, Keywords::$Day)) return P_Day;
            if (Morph::HasOneOfLemmas($t, Keywords::$Hour)) return P_Hour;
            if (Morph::HasOneOfLemmas($t, Keywords::$Minute)) return P_Minute;

            return P_None;
        }

        private static function NeighbourDaysFromToken($t)    // private static int NeighbourDaysFromToken(string t)
        {
            if (Morph::HasOneOfLemmas($t, Keywords::$Tomorrow)) return 1;
            if (Morph::HasOneOfLemmas($t, Keywords::$Today)) return 0;
            if (Morph::HasOneOfLemmas($t, Keywords::$AfterTomorrow)) return 2;
            if (Morph::HasOneOfLemmas($t, Keywords::$Yesterday)) return -1;
            if (Morph::HasOneOfLemmas($t, Keywords::$BeforeYesterday)) return -2;
            
            return -2000000;
        }

        static function RelativeModeFromToken($t)     // internal static RelativeMode RelativeModeFromToken(string t)
        {
            if (Morph::HasOneOfLemmas($t, Keywords::$Current)) return RM_Current;
            if (Morph::HasOneOfLemmas($t, Keywords::$Next)) return RM_Next;
            if (Morph::HasOneOfLemmas($t, Keywords::$Previous)) return RM_Previous;
            if (Morph::HasOneOfLemmas($t, Keywords::$CurrentNext)) return RM_CurrentNext;

            return RM_None;
        }
    }
?>