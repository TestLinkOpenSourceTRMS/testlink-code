<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.mantissoapInterface.class.php
 * @author		  Francisco Mancardi
 *
 * @internal revisions
 *
**/
require_once('../../../../config.inc.php');
require_once('common.php');

$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();

// last test ok: 20121210
$cfg = "<!-- Template mantissoapInterface -->\n" .
       "<issuetracker>\n" .
       "<username>administrator</username>\n" .
       "<password>mantis</password>\n" .
       "<uribase>http://testlinkopensource.mantishub.com</uribase>\n" .
       "<!-- Configure This if you want NON STANDARD BEHAIVOUR for considered issue resolved -->\n" .
       "<resolvedstatus>\n" .
       "<status><code>80</code><verbose>resolved</verbose></status>\n" .
       "<status><code>90</code><verbose>closed</verbose></status>\n" .
       "</resolvedstatus>\n".
       "</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  Issue Tracker Integration - mantissoapInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';
echo 'Creating INTERFACE<br>';

$its = new mantissoapInterface(5,$cfg);

echo 'Connection OK?<br>';
var_dump($its->isConnected());
if( $its->isConnected() )
{
	echo '<b>Connected !</br></b>';
	
	echo '<b>get resolved status configuration</br></b>';
	new dBug($its->getResolvedStatusCfg());
	
	echo '<b>get issue </br></b>';
	new dBug($its->getIssue(102575));
	new dBug($its->getIssue(102529));
	
}
?>