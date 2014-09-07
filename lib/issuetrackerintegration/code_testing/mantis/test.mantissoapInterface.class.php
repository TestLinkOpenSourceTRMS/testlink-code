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
/*
$cfg = "<!-- Template mantissoapInterface -->\n" .
       "<issuetracker>\n" .
       "<username>u0113</username>\n" .
       "<password>tesi</password>\n" .
       "<uribase>http://localhost:8080/development/closet/mantisbt-1.2.11/</uribase>\n" .
       "<!-- Configure This if you want NON STANDARD BEHAIVOUR for considered issue resolved -->\n" .
       "<resolvedstatus>\n" .
       "<status><code>80</code><verbose>resolved</verbose></status>\n" .
       "<status><code>90</code><verbose>closed</verbose></status>\n" .
       "</resolvedstatus>\n".
       "</issuetracker>\n";
*/

$username='administrator';
$password='root';
$uribase='http://localhost/development/mantis/mantisbt-1.2.15/';


$cfg = "<!-- Template mantissoapInterface -->\n" .
       "<issuetracker>\n" .
       "<username>$username</username>\n" .
       "<password>$password</password>\n" .
       "<uribase>$uribase</uribase>\n" .
       "<project>Project ONE</project>\n" .
       "<category>YUMO</category>" .
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

$issueID = 12;
$issueNote = 'SONO UNA NOTAwww!!!';
if( $its->isConnected() )
{
  echo '<b>Connected !</br></b>';
  $xx = $its->addNote($issueID,$issueNote);	
  var_dump($xx);
}