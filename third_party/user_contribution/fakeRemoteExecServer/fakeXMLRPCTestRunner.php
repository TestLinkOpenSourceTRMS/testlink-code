<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: fakeXMLRPCTestRunner.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2011/01/25 21:49:07 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
require_once dirname(__FILE__) . '../../../xml-rpc/class-IXR.php';

function executeTestCase($args) 
{

	$retVal = array('result' => '', 'notes' => '', 'status' => 'scheduled');

	if( isset($args['executionMode']) )
	{	
		$retVal['status'] = $args['executionMode'];
	}
	
	switch($args['testCaseName'])
	{
		case 'sayPassed':
			$retVal = array('result' => 'p', 'notes' => 'Test Case PASSED', 'status' => 'now');
		break;
		
		case 'sayBlocked':
			$retVal = array('result' => 'b', 'notes' => 'Test Case Blocked', 'status' => 'now');
		break;

		case 'sayFailed':
			$retVal = array('result' => 'f', 'notes' => 'Test Case Failed', 'status' => 'now');
		break;

		case 'sayScheduled':
			$retVal = array('result' => '', 'notes' => 'Test Case Scheduled for EXECUTION Failed', 
							'status' => 'scheduled');
		break;
	}
	
	return $retVal;
}


function getTime($args) 
{
    return date('H:i:s');
}


$methods = array('executeTestCase' => 'executeTestCase', 'getTime' => 'getTime');
$server = new IXR_Server($methods);
?>

