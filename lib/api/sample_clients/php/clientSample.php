<?php
 /**
 * A sample client implementation in php
 * 
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * @link        http://testlink.org/api/
 */
 
 /** 
  * Need the IXR class for client
  */
require_once dirname(__FILE__) . '/../../../third_party/xml-rpc/class-IXR.php';

// substitute your server URL Here
define("SERVER_URL", "http://qa/testlink_sandbox/lib/api/xmlrpc.php");

// substitute your Dev Key Here
define("DEV_KEY", "f2a979d533cdd9761434bba60a88e4d8");

function reportResult($tcid, $tpid, $status)
{

	$client = new IXR_Client(SERVER_URL);

	$data = array();
	$data["devKey"] = constant("DEV_KEY");
	$data["tcid"] = $tcid;
	$data["tpid"] = $tpid;
	$data["status"] = $status;

	if(!$client->query('tl.reportTCResult', $data))
	{
		echo "something went wrong - " . $client->getErrorCode() . " - " .
			$client->getErrorMessage();			
	}
	else
	{
		return $client->getResponse();
	}
}
// Substitute for tcid and tpid that apply to your project
$response = reportResult(1132, 56646, "f");
echo "result was: ";
// Typically you'd want to validate the result here and probably do something more useful with it
print_r($response);