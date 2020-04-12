<?php

require_once './modules/hors/Models/TextToken.php';

    class DatesRawData
    {
        public $Text;
        public $Tokens;              // List<TextToken> Tokens
        public $Pattern;                      // string Pattern
        public $Dates;          // List<AbstractPeriod> Dates

        public function GetPattern()       // string GetPattern()
        {
            return $this->Pattern;
        }

        public function RemoveRangeT($start, $count)     // void RemoveRange(int start, int count)
        {
            array_splice($this->Tokens, $start, $count);
        }

        public function RemoveRangeD($start, $count)     // void RemoveRange(int start, int count)
        {
            array_splice($this->Dates, $start, $count);
        }
        
        public function RemoveRange($start, $count)     // void RemoveRange(int start, int count)
        {
            // Tokens.RemoveRange(start, count);
            array_splice($this->Tokens, $start, $count);
            // Dates.RemoveRange(start, count);
            array_splice($this->Dates, $start, $count);
            // Pattern = Pattern.Remove(start, count);
            //echo "\nRemoveRange=>>\n";
            //echo '1-'.mb_substr($this->Pattern, 0, $start)."\n2-".mb_substr($this->Pattern, $start+$count, mb_strlen($this->Pattern)-$start-$count)."\n";
            
            $this->Pattern = mb_substr($this->Pattern, 0, $start).mb_substr($this->Pattern, $start+$count, mb_strlen($this->Pattern)-$start-$count);
        }
        
        private function InsertDates($index, $dates)  // void InsertDates(int index, params AbstractPeriod[] dates)
        {
            if (count($dates) == 0) return;
            $this->InsertData($index, "@", "{}", $dates);
        }

        public function ReplaceTokensByDates($start, $removeCount, $dates)    // void ReplaceTokensByDates(int start, int removeCount, params AbstractPeriod[] dates)
        {
            $startIndex = $this->Tokens[$start]->Start;
            $endIndex = $this->Tokens[$start + $removeCount - 1]->End;
            foreach ($dates as $date)
            {
                if ($date->End == 0)
                    $date->SetEdges($startIndex, $endIndex);
            }
            
            $this->RemoveRange($start, $removeCount);
            $this->InsertDates($start, $dates);
        }

        public function ReturnTokens($index, $pattern, $tokens)     // void ReturnTokens(int index, string pattern, params TextToken[] tokens)
        {
            //echo 'index->';
            //var_dump($index);
            //echo 'pattern->';
            //var_dump($pattern);
            //echo 'tokens->';
            //var_dump($tokens);
            // Dates.InsertRange($index, Enumerable.Repeat<AbstractPeriod>(null, tokens.Length));  // Создаем массив
            array_splice($this->Dates, $index, 0, array_fill(0, count($tokens), null));
            // Tokens.InsertRange(index, tokens);
            array_splice($this->Tokens, $index, 0, $tokens);

            //echo 'Pattern->';
            //var_dump($this->Pattern);
            // var prefix = Pattern.Substring(0, $index);
            $prefix = mb_substr($this->Pattern, 0, $index);
            // var suffix = index < Pattern.Length ? Pattern.Substring(index) : "";
            $suffix = $index < mb_strlen($this->Pattern) ? mb_substr($this->Pattern, $index) : '';
            // Pattern = $"{prefix}{pattern}{suffix}";
            //echo 'prefix->';
            //var_dump($prefix);
            //var_dump($pattern);
            //echo 'suffix->';
            //var_dump($suffix);
            $this->Pattern = $prefix.$pattern.$suffix;
        }

        public function InsertData($index, $pattern, $token, $dates)  //void InsertData(int index, string pattern, string token, params AbstractPeriod[] dates)
        {
            // Dates.InsertRange($index, $dates);
            array_splice($this->Dates, $index, 0, $dates);
            // Tokens.InsertRange(index, Enumerable.Repeat(new TextToken(token), dates.Length));
            array_splice($this->Tokens, $index, 0, array_fill(0, count($dates), null));

            // var prefix = Pattern.Substring(0, index);
            $prefix = mb_substr($this->Pattern, 0, $index);
            // var patterns = string.Join("", Enumerable.Repeat(pattern, dates.Length));
            $patterns = implode("", array_fill(0, count($dates), $pattern));
            // var suffix = index < Pattern.Length ? Pattern.Substring(index) : "";
            $suffix = $index < mb_strlen($this->Pattern) ? mb_substr($this->Pattern, $index) : '';
            // Pattern = $"{prefix}{patterns}{suffix}";
            $this->Pattern = $prefix.$patterns.$suffix;
        }

        private function FixZeros()  // void FixZeros()
        {
            $i = 0;
            while ($i < count($this->Tokens) - 1)
            {
                //if (Tokens[i].Value == "0" && Tokens[i + 1].Value.Length == 1 && int.TryParse(Tokens[i + 1].Value, out _))
                if ($this->Tokens[$i]->Value == "0" && mb_strlen($this->Tokens[$i + 1]->Value) == 1 && is_numeric($this->Tokens[$i + 1]->Value))
                {
                    // this in zero and number after it, delete zero
                    $this->RemoveRange($i, 1);
                }
                else
                {
                    $i++;
                }
            }
        }

        public function CreateTokens($tokens)  // void CreateTokens(List<string> tokens)
        {
            //$Tokens = new List<TextToken>(); это походу не нужно ваще
            $this->Tokens = array();
            $len = 0;
            foreach ($tokens as $currentToken)
            {
                $token = new TextToken($currentToken);
                {
                    $token->Start = $len;
                    // End = len + currentToken.Length
                    $token->End = $len + mb_strlen($currentToken);
                };
                
                // Tokens.Add(token);
                $this->Tokens[] = $token;
                // len += currentToken.Length + 1; // +1 for separator symbol
                $len += mb_strlen($currentToken) + 1; // +1 for separator symbol
            }
            
            $this->FixZeros();
        }

        public function EdgesByIndex($tokenIndex)    // IHasEdges EdgesByIndex(int tokenIndex)
        {
            $date = $this->Dates[$tokenIndex];
            if ($date == null)
            {
                return $this->Tokens[$tokenIndex];
            }

            return $date;
        }
    }
?>