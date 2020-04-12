<?php

    class Helpers
    {
        /// <summary>
        /// TrimPunctuation from start and end of string.
        /// </summary>
        // internal static string TrimPunctuation(string value, bool leaveValidSymbols = true)
        static function TrimPunctuation($value, $leaveValidSymbols = true)
        {
            // Count start punctuation.
            $strLen = mb_strlen($value);
            $removeFromStart = 0;
            $validStart = "#{[{`\"'";
            // foreach (var c in value) {
            //     if (char.IsPunctuation(c) && (!leaveValidSymbols || !validStart.Contains(c.ToString()))) {
            //         $removeFromStart++;
            //     } else {
            //         break;
            //     }
            // }
            
            for($i=0;$i<strLen;$i++){
                $c = mb_substr($value,$i,1);
                if(mb_ereg_match("[\p{P} ]", $c) && (!$leaveValidSymbols || !strstr($validStart,$c))) {
                    $removeFromStart++;
                } else {
                    break;
                }
            }

            // Count end punctuation.
            $removeFromEnd = 0;
            $validEnd = "!.?…)]}%\"'`";
            // foreach (var c in value.Reverse()) {
            //     if (char.IsPunctuation(c) && (!leaveValidSymbols || !validEnd.Contains(c.ToString()))) {
            //         removeFromEnd++;
            //     } else {
            //         break;
            //     }
            // }
            for($i=strLen-1;$i>1;$i--){
                $c = mb_substr($value,$i,1);
                if(mb_ereg_match("[\p{P} ]", $c) && (!$leaveValidSymbols || !strstr($validEnd,$c))) {
                    $removeFromEnd++;
                } else {
                    break;
                }
            }
            
            // No characters were punctuation.
            if ($removeFromStart == 0 && $removeFromEnd == 0)
            {
                return $value;
            }
            // All characters were punctuation.
            if ($removeFromStart == $strLen && $removeFromEnd == $strLen)
            {
                return "";
            }
            // Substring.
            // return value.Substring(removeFromStart, value.Length - removeFromEnd - removeFromStart);
            return mb_substr($vale, $removeFromStart, $strLen - $removeFromEnd - $removeFromStart);
        }

        // internal static void SwapTwo<T>(this List<T> list, int firstIndex, int secondIndex)
        static function SwapTwo(&$list, $firstIndex, $secondIndex)
        {
            $tmp = $list[$firstIndex];
            $list[$firstIndex] = $list[$secondIndex];
            $list[$secondIndex] = $tmp;
        }
    }

?>