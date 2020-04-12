<?php

    class YearRecognizer extends Recognizer
    {
        protected function GetRegexPattern()
        {
            return "(1)Y?|(0)Y"; // [в] 15 году/2017 (году)
        }

        protected function ParseMatch($data, $match, $userDate)
        {
            // just year number
            $n = is_numeric($data->Tokens[$match[0]['Index']]->Value) ? $data->Tokens[$match[0]['Index']]->Value : 0;
            $year = ParserUtils::GetYearFromNumber($n);

            // insert date
            $date = new AbstractPeriod();
            $date->Date = new DateTime();
            $date->Date->setDate($year, 1, 1);

            $date->Fix(array(FixPeriod::FP_Year));
            
            // remove and insert
            //data.ReplaceTokensByDates(match.Index, match.Length, date);
            $data->ReplaceTokensByDates($match[0]['Index'], mb_strlen($match[0]['Value']), array($date));

            return true;
        }
    }

?>