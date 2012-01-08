<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 */
require_once('../../../config.inc.php');
require_once('common.php');

$g_interface_bugs = 'BUGZILLAXMLRPC';
require_once('../int_bugtracking.php');
require_once 'sample.inc.php';

echo '<hr><br>';
echo "<b>Testing  BST Integration :{$g_interface_bugs} </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "username:" . BUG_TRACK_USERNAME  . '<br>';
echo "password:" . BUG_TRACK_PASSWORD . '<br>';
echo "BUG_TRACK_HREF:" . BUG_TRACK_HREF . '<br>';
echo "BUG_TRACK_XMLRPC_HREF:" . BUG_TRACK_XMLRPC_HREF . '<br>';
echo "BUG_TRACK_SHOW_ISSUE_HREF:" . BUG_TRACK_SHOW_ISSUE_HREF . '<br>';
echo "BUG_TRACK_ENTER_ISSUE_HREF:" . BUG_TRACK_ENTER_ISSUE_HREF .'<br>';
echo '<hr><br><br>';

$op = config_get('bugInterfaceOn');
echo 'Connection Status:' . ( $op ? 'OK' : 'KO Oohhh!') . '<br><br>';

if($op)
{
	$issue2check = array( array('issue' => 11776, 'exists' => true),
	  					  array('issue' => 99999, 'exists' => false));
	  	
	$methods = array('getBugSummaryString','getBugStatus','getBugStatusString',
					 'checkBugID_existence','buildViewBugLink');

	$if = config_get('bugInterface');
	new dBug($if);
	// $xx = $if->getIssue(281579);

	// echo 'Does issue 999999 exist? ' . ($if->checkBugID_existence(999999) ? 'YES!!!' : 'Oh No!!!');
	// echo 'Does issue 281579 exist? ' . ($if->checkBugID_existence(281579) ? 'YES!!!' : 'Oh No!!!');

	//$xx = $if->getIssue(999999);
	//new dBug($xx);
	$xx = $if->getBugStatus(281579);
	new dBug($xx);

	$xx = $if->getBugSummaryString(281579);
	new dBug($xx);

}

?>