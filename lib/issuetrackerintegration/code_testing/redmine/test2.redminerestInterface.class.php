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

new dBug($it_mgr);



// 192.168.1.174
// 20130406


$apiKey['192.168.1.174']['admin'] = 'AAe6f1cbed7469528389554cffcb0e5aa4e0fa0bc8';
$apiKey['192.168.1.194']['user'] = 'a67dd9575e2b870c103ebb5aa04d5d85790069ca';

$projectidentifier['192.168.1.194'] = 'tl-one';

$user = 'user';
$redmineHost = '192.168.1.194';

//$authKey = $apiKey[$redmineHost]['user'];

//        "<attributes><fixed_version_id>2</fixed_version_id></attributes>\n" . 
//        "<estimated_hours>12.6</estimated_hours>\n" .

$cfg = "<issuetracker>\n" .
		   "<apikey>{$apiKey[$redmineHost]['user']}</apikey>\n" .
		   "<projectidentifier>{$projectidentifier[$redmineHost]}</projectidentifier>\n" .
       "<attributes><fixed_version_id>2</fixed_version_id><category_id>1</category_id>\n" .
       "<estimated_hours>12.6</estimated_hours>\n" .
       "</attributes>\n" . 
		   "<uribase>http://$redmineHost/redmine/</uribase>\n" .
		   "</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  Issue Tracker Integration - redminerestInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";
echo '<hr><br><br>';

echo 'Creating INTERFACE<br>';

$its[$user] = new redminerestInterface(15,$cfg);
// var_dump($its[$user]);

echo "<br>Connection OK? (using apikey for user: $user)<br>";
var_dump($its[$user]->isConnected());
if( $its[$user]->isConnected() )
{

  echo 'Try To CREATE ISSUE<br>';
  $xx = $its[$user]->addIssue('Internazionale di Milan', 'Who loves it?');
  echo '<pre>';
  var_dump($xx);
  echo '</pre>';
  
  // @20130406 
  // Without unset() working with bitnami redmine 2.3
  // Get AFTER create takes an eternity and FAILED
  unset($its[$user]);
  $its[$user] = new redminerestInterface(15,$cfg);

  echo 'Try To Get ISSUE <br>';
  $xx = $its[$user]->getIssue(7);
  echo '<pre>';
  var_dump($xx);
  echo '</pre>';

  /*
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
  */
}
?>