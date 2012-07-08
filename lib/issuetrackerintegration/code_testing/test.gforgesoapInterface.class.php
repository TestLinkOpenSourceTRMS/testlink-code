<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.gforgesoapInterface.class.php
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
		"<username>testlink.api</username>\n" .
		"<password>testlinkapi</password>\n" .
		"<uribase>http://gforge.com/</uribase>\n" .
		"<uriwsdl>http://gforge.com/gf/xmlcompatibility/soap5/?wsdl</uriwsdl>\n" .
		"<uriview>http://gforge.com/</uriview>\n" .
		"<uricreate>http://gforge.com/</uricreate>\n" .
		"</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - gforgesoapInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

$its = new gforgesoapInterface(1,$cfg);
var_dump($its);

if( $its->isConnected() )
{
	echo '<b>Connected !</br></b>';
	// $issue=$its->getIssue(7091);
	$issue=$its->getIssue(8305);

	new dBug($issue);	

}
?>