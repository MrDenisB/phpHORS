<?php
require_once './modules/hors/Models/IHasEdges.php';


    class TextToken implements IHasEdges
    {
        public $Value;   // string Value;
        public $Start;   // { get; set; }   // int
        public $End;     // { get; set; }  // int
        
//        public function TextToken($value)   // string value
//        {
//            $Value = $value;
//        }

        public function TextToken($value, $startIndex = NULL, $endIndex = NULL)   // TextToken(string value, int startIndex, int endIndex)
        {
            $this->Value = $value;
            if($startIndex) $this->Start = $startIndex;
            if($endIndex) $this->End = $endIndex;
        }

        public function setStart($s)   // TextToken(string value, int startIndex, int endIndex)
        {
            $this->Start = $s;
        }

        public function setEnd($e)   // TextToken(string value, int startIndex, int endIndex)
        {
            $this->End = $e;
        }
    }
?>