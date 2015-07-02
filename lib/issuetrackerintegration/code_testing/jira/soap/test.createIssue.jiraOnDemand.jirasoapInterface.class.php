<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.jirasoapInterface.class.php
 * @author		Francisco Mancardi
 *
 * @internal revisions
 *
**/
require_once('../../../../../config.inc.php');
require_once('common.php');


$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();

// <id>10101</id>
$cfg =  "<issuetracker>\n" .
		"<username>testlink.forum</username>\n" .
		"<password>forum</password>\n" .
		"<uribase>http://testlink.atlassian.net/</uribase>\n" .
		"<uriwsdl>http://testlink.atlassian.net/rpc/soap/jirasoapservice-v2?wsdl</uriwsdl>\n" .
		"<uriview>http://testlink.atlassian.net/browse/</uriview>\n" .
		"<uricreate>http://testlink.atlassian.net/secure/CreateIssue!default.jspa</uricreate>\n" .
        "<projectkey>ZOFF</projectkey>\n" .
        "<issuetype>1</issuetype>\n" .

		"<attributes><components><id>10100</id><id>10101</id></components>\n" .
		"</attributes>\n" .
		
		"</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - jirasoapInterface 'Do Androids Dream of Electric Sheep?' </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";
echo '<hr><br><br>';
echo 'Creating INTERFACE<br>';
$its = new jirasoapInterface(5,$cfg);

echo 'Connection OK?<br>';
var_dump($its->isConnected());

if( $its->isConnected() )
{
  $today = date("Y-m-d H:i:s");	
  $issue = array('summary' => 'Issue Via API' . $today,'description' => 'Do Androids Dream of Electric Sheep?');
  $zorro = $its->addIssue($issue['summary'],$issue['description']);
  echo '<pre>';
  var_dump($zorro);
  echo '</pre>';
}
