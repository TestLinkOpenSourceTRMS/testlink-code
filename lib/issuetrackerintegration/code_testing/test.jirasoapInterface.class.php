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
require_once('../../../config.inc.php');
require_once('common.php');

$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();

$cfg =  "<issuetracker>\n" .
		"<username>testlink.helpme</username>\n" .
		"<password>jira</password>\n" .
		"<uribase>http://testlink.atlassian.net/</uribase>\n" .
		"<uriwsdl>http://testlink.atlassian.net/rpc/soap/jirasoapservice-v2?wsdl</uriwsdl>\n" .
		"<uriview>http://testlink.atlassian.net/browse/</uriview>\n" .
		"<uricreate>http://testlink.atlassian.net/secure/CreateIssue!default.jspa</uricreate>\n" .
		"</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - jirasoapInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

// $safe_cfg = str_replace("\n",'',$cfg);
// echo $safe_cfg;
$its = new jirasoapInterface($itt['JIRASOAP'],$cfg);
// var_dump($its);

if( $its->isConnected() )
{
	echo '<b>Connected !</br></b>';
	var_dump($its->getStatusDomain());

	/*
	$issue2check = array( array('issue' => 'TLJIRASOAPINTEGRATION-1', 'exists' => true),
	  					  array('issue' => 'TLJIRASOAPINTEGRATION-199999', 'exists' => false));

	$methods = array('getBugSummaryString','getBugStatus','getBugStatusString',
					 'checkBugID_existence','buildViewBugLink');
	*/


	
	
}
?>