<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: fakeXMLRPCTestRunner.php,v $
 *
 * @version $Revision: 1.1.2.2 $
 * @modified $Date: 2011/02/10 21:27:09 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * Simple and silly remote execution server that can be used to do some simple
 * test on the remote execute TestLink Feature
 *
 * rev: 
 */
require_once dirname(__FILE__) . '../../../xml-rpc/class-IXR.php';

function executeTestCase($args) 
{

	
//   Complete date plus hours, minutes and seconds:
//      YYYY-MM-DDThh:mm:ssTZD (eg 1997-07-16T19:20:30+01:00)
//
// where:
//
//     YYYY = four-digit year
//     MM   = two-digit month (01=January, etc.)
//     DD   = two-digit day of month (01 through 31)
//     hh   = two digits of hour (00 through 23) (am/pm NOT allowed)
//     mm   = two digits of minute (00 through 59)
//     ss   = two digits of second (00 through 59)
//     TZD  = time zone designator (Z or +hh:mm or -hh:mm)

//function iso8601($time=false) {
//    if ($time === false) $time = time();
//    $date = date('Y-m-d\TH:i:sO', $time);
//    return (substr($date, 0, strlen($date)-2).':'.substr($date, -2));
//}

//  function tstamptotime($tstamp) {
//        // converts ISODATE to unix date
//        // 1984-09-01T14:21:31Z
//       sscanf($tstamp,"%u-%u-%uT%u:%u:%uZ",$year,$month,$day,
//        $hour,$min,$sec);
//        $newtstamp=mktime($hour,$min,$sec,$month,$day,$year);
//        return $newtstamp;
//    }
#  $now = time();  
#     //Get the date one week from now as a UNIX timestamp  
#     $nextweek = $now + (3600 * 24 * 7);  
#     //Get the date last week as a UNIX timestamp  
#     $nextweek = $now + (3600 * 24 * 7);  
#
#     //The following date could be any date in  
#     //ISO 8601, RFC 2822 and even non ISO  
#     //or RFC standards  
#     $date = date('Y-m-d h:i:s');  
#     //Get the UNIX timestamp  
#     $timestamp = strtotime($date);                      


	$retVal = array('result' => '', 'notes' => '', 
					'scheduled' => 'scheduled', 'timestampISO' => '');

	if( isset($args['executionMode']) )
	{	
		$retVal['status'] = $args['executionMode'];
	}

	$now = time();  
	//Get the date one week from now as a UNIX timestamp  
	$nextweek = $now + (3600 * 24 * 7); 

	$tsISONow = date('c',$now);
	$tsISOFuture = date('c',$nextweek);

	
	switch($args['testCaseName'])
	{
		case 'sayPassed':
			$retVal = array('result' => 'p', 'notes' => 'Test Case PASSED', 
							'scheduled' => 'now', 'timestampISO' => $tsISONow);
		break;
		
		case 'sayBlocked':
			$retVal = array('result' => 'b', 'notes' => 'Test Case Blocked',
							'scheduled' => 'now', 'timestampISO' => $tsISONow);
		break;

		case 'sayFailed':
			$retVal = array('result' => 'f', 'notes' => 'Test Case Failed',
							'scheduled' => 'now', 'timestampISO' => $tsISONow);
		break;

		case 'sayScheduled':
			$retVal = array('result' => '', 'notes' => 'Test Case Scheduled for EXECUTION', 
							'scheduled' => 'scheduled', 'timestampISO' => $tsISOFuture);
		break;
	}
	
	// debug - write to file
	// for debug - file_put_contents('d:\request.txt', serialize($_REQUEST));     
	return $retVal;
}


function getTime($args) 
{
    return date('H:i:s');
}


$methods = array('executeTestCase' => 'executeTestCase', 'getTime' => 'getTime');
$server = new IXR_Server($methods);
?>

