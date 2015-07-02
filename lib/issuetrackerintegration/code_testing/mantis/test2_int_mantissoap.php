<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test_int_mantissoap.php
 * @author		Francisco Mancardi
 *
 * @internal revisions
 *
**/
require_once('../../../../config.inc.php');
require_once('common.php');

$g_interface_bugs = 'MANTISSOAP';
require_once('../int_bugtracking.php');

define('BUG_TRACK_USERNAME', 'testlink.helpme');
define('BUG_TRACK_PASSWORD', 'testlink.helpme');

define('BUG_TRACK_HREF', "http://www.mantisbt.org/");
define('BUG_TRACK_SOAP_HREF', BUG_TRACK_HREF . "bugs/api/soap/mantisconnect.php?wsdl");
define('BUG_TRACK_SHOW_ISSUE_HREF', BUG_TRACK_HREF . "bugs/view.php?id="); 
define('BUG_TRACK_ENTER_ISSUE_HREF', BUG_TRACK_HREF . "bugs/");
/*
<issuetracker>
<username>testlink.helpme</username>
<password>testlink.helpme</password>
<uribase>http://www.mantisbt.org/</uribase>
<uriwsdl>http://www.mantisbt.org/bugs/api/soap/mantisconnect.php?wsdl</uriwsdl>
<uriview>http://www.mantisbt.org/bugs/view.php?id=</uriview>
<uricreate>http://www.mantisbt.org/bugs/</uricreate>
</issuetracker>


*/

echo '<hr><br>';
echo "<b>Testing  BST Integration :{$g_interface_bugs} </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "username:" . BUG_TRACK_USERNAME  . '<br>';
echo "password:" . BUG_TRACK_PASSWORD . '<br>';
echo "BUG_TRACK_HREF:" . BUG_TRACK_HREF . '<br>';
echo "BUG_TRACK_SOAP_HREF:" . BUG_TRACK_SOAP_HREF . '<br>';
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
	$tc=1;
    foreach($issue2check as $elem)
    {
    	$issue = $elem['issue'];
    	$msg = $elem['exists'] ? "<br>Ask info about EXISTENT ISSUE:{$issue}<br>" :
								 "<br>Ask info about <b>INEXISTENT ISSUE:{$issue}</b><br>";

		echo $msg;
		foreach($methods as $call)
		{
			$x = $if->$call($issue);
			echo '<br><b>Test Case #' . $tc . '</b><br>';
			echo "<br>\$if->$call($issue) => " . $x .'<br><br>'; 
			$tc++;
		}
    }	  					  
} 
?>