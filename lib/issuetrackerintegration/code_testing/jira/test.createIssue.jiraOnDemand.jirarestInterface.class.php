<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.createIssue.jiraOnDeman.jirarestInterface.class.php
 * @author		Francisco Mancardi
 *
 * @internal revisions
 *
**/
require_once('../../../../config.inc.php');
require_once('common.php');

$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();

// http://testlink.atlassian.net/rest/api/latest/user/search/?username=admin
$cfg =  "<issuetracker>\n" .
		"<username>testlink.forum</username>\n" .
		"<password>forum</password>\n" .
		"<host>testlink.atlassian.net</host>\n" .
        "<projectkey>ZOFF</projectkey>\n" .
        "<issuetype>1</issuetype>\n" .
		"</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - jirasoapInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";
echo '<hr><br><br>';
echo 'Creating INTERFACE<br>';
$its = new jirarestInterface(5,$cfg);

echo 'Connection OK?<br>';
var_dump($its->isConnected());

if( $its->isConnected() )
{
 
  $zorro = $its->getAPIClient()->getUser('testlink.forum');
  echo '<pre>';
  var_dump($zorro);
  echo '</pre>';

  /*	
  $today = date("Y-m-d H:i:s");	
  $issue = array('summary' => 'Issue Via API' . $today,'description' => 'Do Androids Dream of Electric Sheep?');
  $zorro = $its->addIssue($issue['summary'],$issue['description']);
  echo '<pre>';
  var_dump($zorro);
  echo '</pre>';
  */
}
