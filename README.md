# majordomo-phphors

Class using.
You can find more info in README.md of modules/hors directory.
```
require_once(DIR_MODULES . 'hors/HorsTextParser.php');

if(isset($params['p1'])) $str = $params['p1'];  // ." ".$params['URL'];
if($str=='') $str='зав��а в 5 ���а �об��ие ми�ового ма���аба';
$base_date = new DateTime();

$HorsParser = new HorsTextParser();
$Result = $HorsParser->Parse($str ,$base_date, 3);

say('Source text: '.$Result->SourceText);
say('Result text: '.$Result->Text);
foreach($Result->Dates as $k=>$dat)
   if (isset($dat)) say ('{'.$k.'} : '.$dat->ToString());
```
