<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.redmineInterface.class.php
 * @author		  Francisco Mancardi
 *
 * @internal revisions
 *
**/
require_once('../../../config.inc.php');
require_once('common.php');

$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();

// 192.168.1.174
$cfg = "<issuetracker>\n" .
		   "<apikey>AAe6f1cbed7469528389554cffcb0e5aa4e0fa0bc8</apikey>\n" .
		   "<projectidentifier>public01</projectidentifier>\n" .
		   "<uribase>http://192.168.1.2/</uribase>\n" .
		   "</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  Issue Tracker Integration - redminerestInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";
echo '<hr><br><br>';

echo 'Creating INTERFACE<br>';

$user = 'admin';
$its[$user] = new redminerestInterface(15,$cfg);

echo 'Connection OK?<br>';
var_dump($its[$user]->isConnected());
if( $its[$user]->isConnected() )
{

  echo 'Try To Get ISSUE FROM PUBLIC PROJECT with ADMIN <br>';
  $xx = $its[$user]->getIssue(23);
  echo '<pre>';
  var_dump($xx);
  echo '</pre>';

  echo 'Try To Get ISSUE FROM PUBLIC PROJECT with ADMIN <br>';
  $xx = $its[$user]->getIssue(26);
  echo '<pre>';
  var_dump($xx);
  echo '</pre>';


  echo 'Try To Get ISSUE FROM PPRIVATE PROJECT with ADMIN <br>';
  $xx = $its[$user]->getIssue(3);
  echo '<pre>';
  var_dump($xx);
  echo '</pre>';
  
  echo 'Try To CREATE ISSUE ON PRIVATE PROJECT with ADMIN <br>';
  
  $xx = $its[$user]->addIssue('Inter', 'non vince');
  echo '<pre>';
  var_dump($xx);
  echo '</pre>';
  
  
  
}

$cfg = "<issuetracker>\n" .
		   "<apikey>8530912c68e5dd52416452b0b3881acb7de94944</apikey>\n" .
		   "<projectidentifier>public01</projectidentifier>\n" .
		   "<uribase>http://192.168.1.174/</uribase>\n" .
		   "</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - redminerestInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";
echo '<hr><br><br>';

$user= 'testlink.forum4git';
$its[$user] = new redminerestInterface(15,$cfg);

if( $its[$user]->isConnected() )
{

  echo "Try To Get ISSUE FROM PUBLIC PROJECT with $user <br>";
  $xx = $its[$user]->getIssue(23);
  echo '<pre>';
  var_dump($xx);
  echo '</pre>';

  echo "Try To Get ISSUE FROM ***Private*** PROJECT with $user THAT HAS NO ACCESS <br>";
  $xx = $its[$user]->getIssue(3);
  echo '<pre>';
  var_dump($xx);
  echo '</pre>';


  echo "Try To Get ISSUE FROM ***Private*** PROJECT with $user THAT HAS ACCESS OK <br>";
  $xx = $its[$user]->getIssue(24);
  echo '<pre>';
  var_dump($xx);
  echo '</pre>';

}


?>