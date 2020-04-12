<?php

require_once './modules/hors/Utils/Helpers.php';

    class HorsParseResult
    {

        public $SourceText;
        public $Tokens;
        public $Text;
        public $Dates;
        
        private $_textWithTokens;
        private $_fullDates;         // readonly List<DateTimeToken> _fullDates;
        private $_tokensToRemove;    // readonly HashSet<string> _tokensToRemove = new HashSet<string>();

        //public HorsParseResult(string sourceText, List<string> tokens, List<DateTimeToken> dates)
        public function HorsParseResult($sourceText, $tokens, $dates)
        {
            $this->SourceText = $sourceText;
            $this->_fullDates = $dates;
            $this->Dates = $this->CreateDates($dates);
            //$this->_tokensToRemove = array();
            
            
            $this->Tokens = array();
// echo '_tokensToRemove=';
// var_dump($this->_tokensToRemove);
            foreach($tokens as $k=>$v) {
                $flag = in_array($v, $this->_tokensToRemove);
// echo $v." "; var_dump($flag); echo "\n";
                if(! $flag) $this->Tokens[] = $v;      // tokens.Where(t => !_tokensToRemove.Contains(t)).ToList();
            }
// echo "Tokens list = \n";
// var_dump($this->Tokens);
            $this->Text = trim(Helpers::TrimPunctuation($this->CreateText(true)));
            
            // $this->Text = implode(' ', $this->Tokens);
        }

        // private List<DateTimeToken> CreateDates(List<DateTimeToken> dates)
        private function CreateDates($dates)
        {
            $duplicateSeen = array();    // $duplicateSeen = new HashSet<double>();
            $datesOut = array();

// echo "input dates=\n";
// var_dump($dates);
            for ($i = 0; $i < count($dates); $i++)
            {
                $date = $dates[$i];
                // var_dump($date);
                if ($date->GetDuplicateGroup() == -1)
                {
                    $datesOut[] = $date;
                }
                else if (!in_array($date->GetDuplicateGroup(), $duplicateSeen))    // else if (!duplicateSeen.Contains(date.GetDuplicateGroup()))
                {
                    $duplicateSeen[] = $date->GetDuplicateGroup();  // duplicateSeen.Add(date.GetDuplicateGroup());
                    $datesOut[] = $date;
                }
                else
                {
// echo 'Token to REMOVE {'.$i."}\n";
                    $this->_tokensToRemove[] = '{'.$i.'}';
                }
            }
// echo "DatesOut=\n";
// var_dump($datesOut);
            return $datesOut;
        }

        // private string CreateText(bool insertTokens)
        private function CreateText($insertTokens)
        {
            $text = $this->SourceText;
            $skippedDates = array();    // $skippedDates = new HashSet<DateTimeToken>();
// echo "SourceText=$text\n";
/*            // loop dates from last to first
            for ($i = count($this->_fullDates) - 1; $i >= 0; $i--)
            {
                $date = $this->_fullDates[$i];
                echo "CreateText -> $i => \$date = ".$date->ToString();
                if (in_array($date, $skippedDates)) { echo 'skipped'."\n"; continue; }
                echo 'not skipped'."\n";
                //var sameDates = _fullDates.Where(d => d.StartIndex == date.StartIndex && !skippedDates.Contains(d)).ToList();
                $sameDates = array();   
                foreach($this->_fullDates as $k=>$d){
                    echo "i=$i date=> ".$date->ToString()."\n";
                    echo "k=$k d=> ".$d->ToString()."\n";
                    if($d->StartIndex == $date->StartIndex && !in_array($d, $skippedDates)) { $sameDates[$k]=$d; echo"added\n";}
                }
                $tokensToInsert = array();    // $tokensToInsert = new List<string>();
                var_dump($sameDates);
                foreach ($sameDates as $k=>$oDate)
                {
                    $skippedDates[] = $oDate;                  // $skippedDates[] = Add(oDate);
                    $indexInList = $k;   // $indexInList = $this->_fullDates.IndexOf(oDate);
                    $tokensToInsert[] = '{'.$k.'}';
                }
 echo "Tokens to insert =";
 var_dump($tokensToInsert);
                
                // text = text.Substring(0, date.StartIndex)
                //        + (insertTokens && Dates.Contains(date) ? string.Join(" ", tokensToInsert) : "")
                //        + (date.EndIndex < text.Length ? text.Substring(date.EndIndex) : "");
                $str1 = ( $insertTokens && in_array($date, $this->Dates) ) ? implode(' ', $tokensToInsert) : '';
 echo "str1=$str1\n";
                $str2 = ($date->EndIndex < mb_strlen($text)) ? mb_substr($text, $date->EndIndex) : '';
 echo "str2=$str2\n";
                $text1 = mb_substr($text, 0, $date->StartIndex).$str1.$str2;
                       
            }
*/

            for ($i = count($this->_fullDates) - 1; $i >= 0; $i--)
            {
                $date = $this->_fullDates[$i];
                if (in_array($date, $skippedDates)) continue;
                
                // var sameDates = _fullDates.Where(d => d.StartIndex == date.StartIndex && !skippedDates.Contains(d)).ToList();
                foreach($this->_fullDates as $k=>$v)
                    if(($v->StartIndex == $date->StartIndex) && !in_array($v, $skippedDates)) $sameDates[]=$v;
                    
                // var tokensToInsert = new List<string>();
                $tokensToInsert = array();
                
                foreach ($sameDates as $k=>$oDate)     // (var oDate in sameDates)
                {
                    $skippedDates[] = $oDate;
                    $indexInList = array_search($oDate, $this->_fullDates, true);
                    $tokensToInsert[]='{'.$indexInList.'}';
                }

                $str1 = ($insertTokens && in_array($date, $this->Dates)) ? implode(" ", $tokensToInsert) : "";
//echo "str1=$str1\n";
                $str2 = ($date->EndIndex < mb_strlen($text)) ? mb_substr($date->EndIndex) : "";
//echo "str2=$str2\n";
                $text_r = mb_substr($text, 0, $date->StartIndex).
                       $str1.$str2;
                       
            }


            // return Regex.Replace(text.Trim(), @"\s{2,}", " ");
 echo "text_r= $text_r \n";
            $Text2 = mb_ereg_replace("\s{2,}", ' ', trim($text_r));
 echo "TEXT= $Text2 \n";
            return $Text2;
        }






/*
        // public string CleanTextWithTokens => string.Join(" ", Tokens);

        public string TextWithTokens
        {
            get
            {
                if (string.IsNullOrEmpty(_textWithTokens))
                {
                    _textWithTokens = CreateText(true);
                }

                return _textWithTokens;
            }
        }

        public override string ToString()
        {
            return $"{CleanTextWithTokens} | {string.Join("; ", Dates.Select(d => d.ToString()))}";
        }
*/
    }

?>