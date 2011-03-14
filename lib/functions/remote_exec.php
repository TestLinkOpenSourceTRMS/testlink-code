<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource	remote_exec.php
 * @author		Francisco Mancardi <francisco.mancardi@gmail.com>
 *
 * @internal revisions
 * 20110308 - franciscom - refactoring 
 */
require_once("../../config.inc.php");
require_once (TL_ABS_PATH . 'third_party'. DIRECTORY_SEPARATOR . 'xml-rpc/class-IXR.php');

/**
* Initiate the execution of a testcase through XML Server RPCs.
* All the object instantiations are done here.
* XML-RPC Server Settings need to be configured using the custom fields feature.
* Three fields each for testcase level and testsuite level are required.
* The fields are: server_host, server_port and server_path.
* Precede 'tc_' for custom fields assigned to testcase level.
*
* @param $tcaseInfo: 
* @param $serverCfg:
* @param $context
*
* @return map:
*         keys: 'result','notes','message'
*         values: 'result' -> (Pass, Fail or Blocked)
*                 'notes' -> Notes text
*                 'message' -> Message from server
*/
function executeTestCase($tcaseInfo,$serverCfg,$context)
{
	// system: to give info about conection to remote execution server
	// execution:
	// 	scheduled: domain 'now', 'future'
	//			   caller will use this attribute to write exec result (only if now)
	//	timestampISO: can be used by server to say the scheduled time.
	//				  To be used only if scheduled = 'future'
	//
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

	
	$ret = array('system' => array('status' => 'ok', 'msg' => 'ok'),
				 'execution' => array('scheduled' => '', 
				 					  'result' => '',
				 					  'resultVerbose' => '',
				 					  'notes' => '',
				 					  'timestampISO' => '') );
  

	$labels = init_labels(array('remoteExecServerConfigProblems' => null,
						 		'remoteExecServerConnectionFailure' => null));
						  
	
	$do_it = (!is_null($serverCfg) && !is_null($serverCfg["url"]) );
	if(!$do_it)
	{ 
		$ret['system']['status'] = 'configProblems';
		$ret['system']['msg'] = $labels['remoteExecServerConfigProblems'];						
	}
	
  	if($do_it)
  	{
		$xmlrpcClient = new IXR_Client($serverCfg["url"]);
		if( is_null($xmlrpcClient) )
		{
			$do_it = false;
			$ret['system']['status'] = 'connectionFailure';
			$ret['system']['msg'] = $labels['remoteExecServerConnectionFailure'];						
		}
	}
	
 	if($do_it)
  	{
  		$args4call = array();
  		
  		// Execution Target
  		$args4call['testCaseName'] = $tcaseInfo['name'];
  		$args4call['testCaseID'] = $tcaseInfo['id'];
  		$args4call['testCaseVersionID'] = $tcaseInfo['version_id'];
  		
  		// Context
  		$args4call['testProjectID'] = $context['tproject_id'];
  		$args4call['testPlanID'] = $context['tplan_id'];
  		$args4call['platformID'] = $context['platform_id'];
  		$args4call['buildID'] = $context['build_id'];
  		$args4call['executionMode'] = 'now'; // domain: deferred,now
		
		$xmlrpcClient->query('executeTestCase',$args4call);
		$response = $xmlrpcClient->getResponse();

		if( is_null($response) )
		{
			// Houston we have a problem!!! (Apollo 13)
			$ret['system']['status'] = 'connectionFailure';
			$ret['system']['msg'] = $labels['remoteExecServerConnectionFailure'];						
			$ret['execution'] = null;
		}
		else
		{
			$ret['execution'] = $response;
			$ret['execution']['resultVerbose'] = '';
			
			if(!is_null($response['result']))
			{	
				$code = trim($response['result']);
				if( $code != '')
				{
					$resultsCfg = config_get('results');
					$codeStatus = array_flip($resultsCfg['status_code']);
					$dummy = trim($codeStatus[$code]);
					$ret['execution']['resultVerbose'] = lang_get($resultsCfg['status_label'][$dummy]);
				}
			}
		}
  	} 

	return $ret;
} // function end
?>