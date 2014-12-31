<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.getIssue.jiraOnDeman.jirarestInterface.class.php
 * @author		Francisco Mancardi
 *
 * @internal revisions
 *
**/
require_once('../../../../../config.inc.php');
require_once('common.php');

$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();

// http://testlink.atlassian.net/rest/api/latest/user/search/?username=admin
$username = 'testlink.forum';
$password = 'forum';
// $password = '';
$uribase = 'https://testlink.atlassian.net/';
$uriapi = 'https://testlink.atlassian.net/rest/api/latest/';

$cfg =  "<issuetracker>\n" .
		    "<username>{$username}</username>\n" .
		    "<password>{$password}</password>\n" .
		    "<uribase>{$uribase}</uribase>\n" .
        "<uriapi>{$uriapi}</uriapi>\n" .
        "<projectkey>ZOFF</projectkey>\n" .
        "<issuetype>1</issuetype>\n" .
		    "</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - jirarestInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";
echo '<hr><br><br>';
echo 'Creating INTERFACE<br>';
$its = new jirarestInterface(7,$cfg);

echo 'Connection OK?<br>';
var_dump($its->isConnected());

if( $its->isConnected() )
{
  // Using RAW API
  
  /*
  $api = $its->getAPIClient();
  $zorro = $its->getAPIClient()->getUser($username);
  echo 'Test - Get Data about connected user<br>'; 
  echo '<pre>';
  var_dump($zorro);
  echo '</pre>';
 

  $targetIssue = 'ZOFF-129';
  echo 'Test - Get Data about Issue:' . $targetIssue . '<br>'; 
  $zorro = $its->getAPIClient()->getIssue($targetIssue);
  echo '<pre>';
  var_dump($zorro);
  echo '</pre>';
 */
  // $api = $its->getAPIClient();

  
  $zorro = $its->getIssueTypes();
  echo '<pre>';
  echo 'ISSUE TYPES<br>';
  var_dump($zorro);
  echo '</pre>';

  $zorro = $its->getIssueTypesForHTMLSelect();
  echo '<pre>';
  echo 'ISSUE TYPES<br>';
  var_dump($zorro);
  echo '</pre>';



  $zorro = $its->getPriorities();
  echo '<pre>';
  echo 'getPriorities<br>';
  var_dump($zorro);
  echo '</pre>';


  $zorro = $its->getVersions('ZOFF');
  echo '<pre>';
  var_dump($zorro);
  echo '</pre>';

  $zorro = $its->getComponents('ZOFF');
  echo '<pre>';
  var_dump($zorro);
  echo '</pre>';


}
