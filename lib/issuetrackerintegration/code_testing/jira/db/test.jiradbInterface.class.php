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
require_once('../../../../config.inc.php');
require_once('common.php');

$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();
$systems = $it_mgr->getSystems();
new dBug($itt);
new dBug($systems);



// last test ok: 
$cfg =  "<issuetracker>\n" .
				"<dbhost>192.168.1.201</dbhost>\n" .
				"<dbname>jiradb</dbname>\n" .
				"<dbtype>mysql</dbtype>\n" .
				"<dbuser>root</dbuser>\n" .
				"<dbpassword>mysqlroot</dbpassword>\n" .
    		"<uribase>http://testlink.atlassian.net/</uribase>\n" .
    		"<uriview>http://testlink.atlassian.net/browse/</uriview>\n" .
    		"<uricreate>http://testlink.atlassian.net/secure/CreateIssue!default.jspa</uricreate>\n" .
    		"</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BTS Integration - jiradbInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

// $safe_cfg = str_replace("\n",'',$cfg);
// echo $safe_cfg;
echo 'Creating INTERFACE<br>';

// @20121215 -> 6 => jiradbInterface
$its = new jiradbInterface(6,$cfg);

echo 'Connection OK?<br>';
var_dump($its->isConnected());

if( $its->isConnected() )
{
	echo '<b>Connected !</br></b>';

	echo '<pre>';
	var_dump($its->getStatusDomain());
	echo '</pre>';
  echo 'Get Issue<br>';
  new dBug($its->getIssue('DEMO-2'));
  
  echo 'Get Issue Summary<br>';
  
	echo($its->getIssueSummary('DEMO-2'));
  echo '<br>';
  
	// echo($its->getIssueSummary('ZOFF-8'));

}
?>