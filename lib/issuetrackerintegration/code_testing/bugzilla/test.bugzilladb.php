<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 */
require_once('../../../../config.inc.php');
require_once('common.php');

$cfg = "<issuetracker>        \n" .
       "<dbhost>192.168.1.88</dbhost>\n" .
       "<dbname>bugzilla3</dbname>   \n" .
       "<dbschema>bugzilla3</dbschema>\n" .
       "<dbtype>mysql</dbtype>        \n" .
       "<dbuser>root</dbuser> \n" .
       "<dbpassword>mysqlroot</dbpassword>\n" .
       "<uricreate>http://192.168.1.88/bugzilla/</uricreate>\n" .
       "<uriview>http://192.168.1.88/bugzilla/show_bug.cgi?id=</uriview>\n" .
       "</issuetracker>";



echo '<hr><br>';
echo "<b>Testing  BTS Integration - bugzilladbInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

$its = new bugzilladbInterface(185,$cfg);
// var_dump($its);

$bug=20;
echo '<pre>';
var_dump($its->getIssue($bug));
echo '</pre>';

echo '<br>Does issue ' . $bug . ' exist? ' . ($its->checkBugIDExistence($bug) ? 'YES!!!' : 'Oh No!!!');
echo '<br>Does issue 999999 exist? ' . ($its->checkBugIDExistence(999999) ? 'YES!!!' : 'Oh No!!!');
/* */


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