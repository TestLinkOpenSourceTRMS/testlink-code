<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 */
require_once('../../../../config.inc.php');
require_once('common.php');

$cfg =  "<issuetracker>\n" .
		"<username>testlink.helpme@gmail.com</username>\n" .
		"<password>testlink.helpme</password>\n" .
		"<uribase>http://bugzilla.mozilla.org/</uribase>\n" .
		"</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - bugzillaxmlrpcInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

$its = new bugzillaxmlrpcInterface(185,$cfg);
// var_dump($its);
echo '<br>Does issue 281579 exist? ' . ($its->checkBugIDExistence(281579) ? 'YES!!!' : 'Oh No!!!');
echo '<br>Does issue 999999 exist? ' . ($its->checkBugIDExistence(999999) ? 'YES!!!' : 'Oh No!!!');



/*
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
*/

?>