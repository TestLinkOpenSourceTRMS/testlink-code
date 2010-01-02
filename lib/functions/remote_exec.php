<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource $RCSfile: remote_exec.php,v $
 * @version $Revision: 1.3 $ $Author: franciscom $
 * @modified $Date: 2010/01/02 16:54:34 $
 * @author 	Martin Havlat, Chad Rosen
 *
 * ----------------------------------------------------------------------------------- */

require_once("../../config.inc.php");

// Contributed code - manish
$phpxmlrpc = TL_ABS_PATH . 'third_party'. DIRECTORY_SEPARATOR . 'phpxmlrpc' . 
             DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
require_once($phpxmlrpc . 'xmlrpc.inc');
require_once($phpxmlrpc . 'xmlrpcs.inc');
require_once($phpxmlrpc . 'xmlrpc_wrappers.inc');

/**
* Initiate the execution of a testcase through XML Server RPCs.
* All the object instantiations are done here.
* XML-RPC Server Settings need to be configured using the custom fields feature.
* Three fields each for testcase level and testsuite level are required.
* The fields are: server_host, server_port and server_path.
* Precede 'tc_' for custom fields assigned to testcase level.
*
* @param $testcase_id: The testcase id of the testcase to be executed
* @param $tree_manager: The tree manager object to read node values and testcase and parent ids.
* @param $cfield_manager: Custom Field manager object, to read the XML-RPC server params.
* @return map:
*         keys: 'result','notes','message'
*         values: 'result' -> (Pass, Fail or Blocked)
*                 'notes' -> Notes text
*                 'message' -> Message from server
*/
function executeTestCase($testcase_id,$tree_manager,$cfield_manager)
{

	//Fetching required params from the entire node hierarchy
	$server_params = $cfield_manager->getXMLServerParams($testcase_id);

  $ret=array('result'=>AUTOMATION_RESULT_KO,
             'notes'=>AUTOMATION_NOTES_KO, 'message'=>'');

	$server_host = "";
	$server_port = "";
	$server_path = "";
  $do_it=false;
  
	if( ($server_params != null) or $server_params != ""){
		$server_host = $server_params["xml_server_host"];
		$server_port = $server_params["xml_server_port"];
		$server_path = $server_params["xml_server_path"];
	
	  if( !is_null($server_host) ||  !is_null($server_path) )
	  {
	      $do_it=true;
	  }    
	}

  if($do_it)
  {
  	// Make an object to represent our server.
  	// If server config objects are null, it returns an array with appropriate values
  	// (-1 for executions results, and fault code and error message for message.
  	$xmlrpc_client = new xmlrpc_client($server_path,$server_host,$server_port);

  	$tc_info = $tree_manager->get_node_hierarchy_info($testcase_id);
  	$testcase_name = $tc_info['name'];

  	//Create XML-RPC Objects to pass on to the the servers
  	$myVar1 = new xmlrpcval($testcase_name,'string');
  	$myvar2 = new xmlrpcval($testcase_id,'string');

  	$messageToServer = new xmlrpcmsg('ExecuteTest', array($myVar1,$myvar2));
  	$serverResp = $xmlrpc_client->send($messageToServer);

  	$myResult=AUTOMATION_RESULT_KO;
  	$myNotes=AUTOMATION_NOTES_KO;

  	if(!$serverResp) {
  		$message = lang_get('test_automation_server_conn_failure');
  	} elseif ($serverResp->faultCode()) {
  		$message = lang_get("XMLRPC_error_number") . $serverResp->faultCode() . ": ".$serverResp->faultString();
  	}
  	else {
  		$message = lang_get('test_automation_exec_ok');
  		$arrayVal = $serverResp->value();
  		$myResult = $arrayVal->arraymem(0)->scalarval();
  		$myNotes = $arrayVal->arraymem(1)->scalarval();
  	}
  	$ret = array('result'=>$myResult, 'notes'=>$myNotes, 'message'=>$message);
  } //$do_it

	return $ret;
} // function end
?>
