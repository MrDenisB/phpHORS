<?php

    class HolidaysRecognizer extends Recognizer
    {
        // protected override string GetRegexPattern()
        protected function GetRegexPattern()
        {
            return "W";
        }

        // protected override bool ParseMatch(DatesRawData data, Match match, DateTime userDate)
        protected function ParseMatch($data, $match, $userDate)
        {
            // var token = data.Tokens[match.Index];
            $token = $data->Tokens[$match[0]['Index']];
            // data.RemoveRange(match.Index, 1);
            $data->RemoveRange($match[0]['Index'], 1);
            
            // if (Morph.HasLemma(token.Value, Keywords.Holiday[0], Morph.LemmaSearchOptions.OnlySingular))
            if (Morph::HasLemma($token->Value, Keywords::$Holiday[0], LSO_OnlySingular))
            {
                // singular
                $saturday = new TextToken(Keywords::$Saturday[0], $token->Start, $token->End);
                //$saturday->TextToken(Keywords::$Saturday[0], $token->Start, $token->End);
                //{
                //    $saturday->Start = $token->Start;
                //    $saturday->End = $token->End;
                //};
                $data->ReturnTokens($match[0]['Index'], "D", $saturday);
            }
            else
            {
                // plural
//                var holidays = new[] {Keywords.Saturday[0], Keywords.TimeTo[0], Keywords.Sunday[0]}
//                    .Select(k => new TextToken(k, token.Start, token.End))
//                    .ToArray();
                $holidays[] = new TextToken(Keywords::$Saturday[0], $token->Start, $token->End);
                $holidays[] = new TextToken(Keywords::$TimeTo[0], $token->Start, $token->End);
                $holidays[] = new TextToken(Keywords::$Sunday[0], $token->Start, $token->End);

                // $data->ReturnTokens(match.Index, "DtD", $holidays);
// var_dump($match[0]['Index']);
                $data->ReturnTokens($match[0]['Index'], "DtD", $holidays);
            }

            return true;
        }
    }

?>