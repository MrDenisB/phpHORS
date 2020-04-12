<?php
        
    class ParserUtils  // internal static
    {
        // internal static int FindIndex(string t, IList<string[]> list)
        static function FindIndex($t, $list)
        {
            for ($i = 0; $i < count($list); $i++)
            {
                if (Morph::HasOneOfLemmas($t, $list[$i]))
                    return $i;
            }

            return -1;
        }

        // internal static int GetYearFromNumber(int n)
        static function GetYearFromNumber($n)
        {
            // this is number less than 1000, what year it can be?
                
            if ($n >= 70 && $n < 100)
            {
                // for numbers from 70 to 99 this will be 19xx year
                return 1900 + $n;
            }

            if ($n < 1000)
            {
                // for other numbers 20xx or 2xxx year
                return 2000 + $n;
            }
            
            return $n;
        }

        static function GetDayValidForMonth($year, $month, $day)
        {
            // var daysInMonth = DateTime.DaysInMonth(year, month);
            $daysInMonth = $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
            return max(1, min($day, $daysInMonth));
        }
    }

?>