
<?php

$ret = new stdClass();
// $ret->description = 'ghsagfgasjhf';
// $ret->description = '%%EXECATT:ghsagfgasjhf';
$ret->description = '%%EXECATT:ghsagfgasjhf%%';

echo 'Original:' . $ret->description . '<br>';

$target['value'] = '%%EXECATT:';
$target['len'] = strlen($target['value']);
$doIt = true;
$loops = 0;
while($doIt)
{
  $mx = strpos($ret->description,$target['value']);
  $doIt = !($mx === FALSE);

  echo '$mx:' . $mx . '<br>';
  echo '$doIt:' . $doIt . '<br>';
  if( ($doIt = !($mx === FALSE) ) )
  {
    // look for closing symbol
    echo 'LOOK FOR CLOSURE' . '<br>';
    $offset = $mx+$target['len'];
    echo "Skip: $offset <br>";
    $cx = strpos($ret->description,'%%',$offset);
    
    if($cx === FALSE)
    {
      // chaos! => abort
      $doIt = false;
      break;
    }  
    
    // replace

    echo $target['value'] . '<br>';
    $old = substr($ret->description,$mx,$cx-$mx+2);
    echo $old . '<br>';
    $new = str_replace($target['value'],'lnl.php?id=',$old);
    $new = str_replace('%%','&apikey=cccc',$new);
    $ret->description = str_replace($old,$new,$ret->description);
    
    echo $new . '<br>';
    


  }  
  $loops++;
  $doIt = $doIt && ($loops < 10);
}  
echo 'Now:' . $ret->description . '<br>';
