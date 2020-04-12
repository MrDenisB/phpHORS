<?php

require_once('./modules/hors/Models/MMEnumTypes.php');

    class LemmaSearchOptions extends BaseEnum
    {
        const LSO_All           = 0;
        const LSO_OnlySingular  = 1;
        const LSO_OnlyPlural    = 2;
        public static $available = array(self::LSO_All, self::LSO_OnlySingular, self::LSO_OnlyPlural);
    };


    class Morph  // static
    {
        // private static readonly Dictionary<string, (string normalForm, byte plural)> Storage = new Dictionary<string, (string, byte)>();
        private static $Storage = [];
        private static $_loaded;  // bool
//        private static string _lastNormalForm = "";
        
        public static function Load()   // void Load()
        {
//            var assembly = Assembly.GetCallingAssembly();
//            var file = assembly.GetManifestResourceStream("Hors.Dict.time_words.txt");
//            if (file != null)
//            {
//                LoadFromStreamReader(new StreamReader(file, Encoding.UTF8));
//            }

            $content=file_get_contents('Dict/time_words.txt');
            $contents = explode("\n",$content);

            foreach($contents as $str){
            //  echo $str."\n";
                if($str == '') { $lastNormalForm=''; continue; }
                else {
                    $tokens = explode('|', $str);
                    if($lastNormalForm == '') $lastNormalForm = $tokens[0];
                }
                self::$Storage[$tokens[0]] = array($lastNormalForm, $tokens[1]);
            }

            $_loaded = true;
        }

/*        public static void AddDictionary(string fileName)
        {
            using (var file = new StreamReader(fileName))
            {
                LoadFromStreamReader(file);
            }
        }

        private static void LoadFromStreamReader(StreamReader file)
        {
            while (!file.EndOfStream)
            {
                LoadLine(file.ReadLine());
            }
        }

        private static void LoadLine(string line)
        {
            if (line == string.Empty)
            {
                // new normal form
                _lastNormalForm = "";
                return;
            }
                    
            // read line data
            var tokens = line.Split('|');
            var word = tokens[0];
            var plural = byte.Parse(tokens[1]);

            if (_lastNormalForm == "")
            {
                _lastNormalForm = word;
            }

            Storage[word] = (_lastNormalForm, plural);

        }
*/

        public static function GetNormalForm($rawWord, $option = LSO_All)  // public static string GetNormalForm(string rawWord, LemmaSearchOptions option = LemmaSearchOptions.All)
        {
            if (!$_loaded) self::Load();
            if (!array_key_exists($rawWord, self::$Storage)) return null;

            $normalForm = self::$Storage[$rawWord][0];
            $plural = self::$Storage[$rawWord][1];
            if (   $option == LSO_All || $plural == 0
                || $option == LSO_OnlySingular && $plural == 1
                || $option == LSO_OnlyPlural && $plural == 2 )
            {
                return $normalForm;
            }

            return null;
        }

        public static function HasLemma($rawWord, $rawLemma, $option = LSO_All) // public static bool HasLemma(string rawWord, string rawLemma, LemmaSearchOptions option = LemmaSearchOptions.All)
        {
            if (mb_strtolower($rawWord) == $rawLemma) return true;
            $lemma = self::GetNormalForm($rawWord, $option);
            ///     echo "\n[".$rawWord."]=>[".$lemma."]\n";
            return $lemma != null && $lemma == $rawLemma;
        }

        public static function HasOneOfLemmas($rawWord, array $lemmas) //public static bool HasOneOfLemmas(string rawWord, params string[] lemmas)
        {
            $arr=array();
            if(is_array($lemmas[0])){
                //echo "It's array.\n";
                foreach($lemmas as $lem)
                    $arr=array_merge($arr,$lem);
            } else  $arr=$lemmas;

            $flag = false;
            foreach($arr as $val)
                if(self::HasLemma($rawWord, $val)) { $flag = true; break; }
            // return lemmas.Any(x => HasLemma(rawWord, x));
            return $flag;
        }
/*        
        public static bool HasOneOfLemmas(string rawWord, params string[][] lemmas)
        {
            return lemmas.Any(x => HasOneOfLemmas(rawWord, x));
        }

        public enum LemmaSearchOptions
        {
            All,
            OnlySingular,
            OnlyPlural
        }
*/
    }
?>