<?php
require_once '../../../third_party/fogbugz-php-api/lib/api.php';
$email = 'francisco.mancardi@gmail.com';
$password = 'testlink.fogbugz';
$url = 'https://testlink.fogbugz.com';
$fogbugz = new FogBugz($email,$password,$url);

// fogbugz will throw exceptions, so we catch them here
try 
{
  $fogbugz->logon();

  // You can call any FogBugz API method directly by using it's
  // name as a method name on the $fogbugz object.
  // It will turn the method name in to the command,
  // ?cmd={method_name} and it will add the array to the
  // get request automatically
  
  /*
  $xml = $fogbugz->listProjects();
  foreach ($xml->projects->children() as $item) 
  {
  	print "<b>Project:" . (string)$item->sProject;
  	print "</b><br>";
  	var_dump($item);
  	print "<br>======================<br>";
  }
  */

  // Go for an issue	
  $xml = $fogbugz->search(array('q' => 3,'cols' => 'sTitle,sStatus'));
  //$xml = $fogbugz->search(array('q' => 3));
  
  echo (string)$xml->description . '<br>';
  echo (int)$xml->cases['count'] . '<br>';
  // var_dump($xml->cases);
  foreach($xml->cases->children() as $item)
  {
  	echo (int)$item['ixBug'] . '<br>';
  	echo (string)$item->sTitle . '<br>';
  	echo (string)$item->sStatus . '<br>';
  } 

}
catch (Exception $e) 
{
  print sprintf("FogBugz Error : [Code %d] %s\n",$e->getCode(),$e->getMessage());
}
?>