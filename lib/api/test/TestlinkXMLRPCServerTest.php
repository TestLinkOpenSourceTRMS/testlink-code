<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: TestlinkXMLRPCServerTest.php,v 1.1 2007/11/26 14:33:33 franciscom Exp $
 *
 * These tests require phpunit: http://www.phpunit.de/
 */
 
 /**
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * @link        http://testlink.org/api/
 */

require_once dirname(__FILE__) . '/../../third_party/xml-rpc/class-IXR.php';
require_once dirname(__FILE__) . '/../TestlinkXMLRPCServerErrors.php';
require_once dirname(__FILE__) . '/TestlinkXMLRPCServerTestData.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

/**
 * Unit tests for the Testlink API
 * 
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net> 
 * @since 		Class available since Release 1.8.0
 * @version 	1.0
 */
class TestlinkXMLRPCServerTest extends PHPUnit_Framework_TestCase
{			
	protected $client;
	protected $SERVER_URL = "http://localhost/testlink/api/xmlrpc.php";
	
	function setUp()
	{	
		// This is the path to the server and will vary from machine to machine
		$this->client = $client = new IXR_Client($this->SERVER_URL);
		// run IXR_Client in debug mode showing verbose output
		$this->client->debug = true;
		$this->setupTestMode();
	}
	
	private function setupTestMode()
	{		
		$data["testmode"] = true;
		
		if(!$this->client->query('tl.setTestMode', $data)) {
			echo "\n" . $this->getName() . " >> problem setting testMode - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		$this->assertEquals(true, $this->client->getResponse());
	}
	
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite;
		// Run specific indivdual tests
		//$suite->addTest(new TestlinkXMLRPCServerTest('testSayHello'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithInvalidDevKey'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithoutTCID'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithInvalidTCID'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithoutTPID'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultNotGuessingBuildID'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testValidReportTCResultRequest'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testValidReportTCResultRequestWithBuildID'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultRequestWithInvalidStatus'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultRequestWithoutStatus'));
		// run all the tests		
		$suite->addTestSuite('TestlinkXMLRPCServerTest');
		return $suite;
	}

	function testSayHello()
	{		
		if (!$this->client->query('tl.sayHello')) {
				die('big time problem ' . $this->client->getErrorCode() . ' : ' . 
					$this->client->getErrorMessage());
			}
	
		$this->assertEquals('Hello!', $this->client->getResponse());		
	}
	
	/*
	function testReportTCResult()
	{
		$data = array();
		$data["test"] = "test string";
		//$data["result"] = "fail";
		//$data["notes"] = "something bad happended during testing";
						
		
		if (!$this->client->query('tl.reportTCResult', $data)) {
			echo 'problem ' . $this->client->getErrorCode() . ' : ' . $this->client->getErrorMessage();
		}
		
		print_r($data);
		
		$this->assertEquals('Blah', $this->client->getResponse());
		
	}*/
	
	function testReportTCResultWithInvalidDevKey()
	{				
		$data = array();
		$data["devKey"] = "wrongKey";
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// build up an array contining the error that should come back
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("INVALID_AUTH");		
		$expectedResult[0]["message"] = constant("INVALID_AUTH_STR");

		$this->assertEquals($expectedResult, $this->client->getResponse());	
	}
	
	function testReportTCResultWithoutDevKey()
	{
		$data = array();		
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// build up an array contining the error that should come back
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("NO_DEV_KEY");		
		$expectedResult[0]["message"] = constant("NO_DEV_KEY_STR");

		$this->assertEquals($expectedResult, $this->client->getResponse());	
	}
	
	function testReportTCResultWithEmptyDevKey()
	{
		$data = array();		
		$data["devKey"] = "";
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// build up an array contining the error that should come back
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("INVALID_AUTH");		
		$expectedResult[0]["message"] = constant("INVALID_AUTH_STR");

		$this->assertEquals($expectedResult, $this->client->getResponse());	
	}
	
	function testReportTCResultWithoutTCID()
	{				
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// build up an array contining the error that should come back
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("NO_TCID");		
		$expectedResult[0]["message"] = constant("NO_TCID_STR");

		$this->assertEquals($expectedResult, $this->client->getResponse());	
	}
	
	function testReportTCResultWithInvalidTCID()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["tpid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["status"] = "f";
		$data["tcid"] = -100; // shouldn't have a negative number for TCID in db
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// build up an array contining the error that should come back
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("INVALID_TCID");		
		$expectedResult[0]["message"] = constant("INVALID_TCID_STR");

		$result = $this->client->getResponse();
		//print_r($result);		
		$this->assertEquals($expectedResult, $result);	
	}
	
	function testReportTCResultWithoutNonIntTCID()
	{				
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["tcid"] = "notAnInt";
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// build up an array contining the errors that should come back
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("TCID_NOT_INTEGER");		
		$expectedResult[0]["message"] = constant("TCID_NOT_INTEGER_STR");
		$expectedResult[1]["code"] = constant("INVALID_TCID");		
		$expectedResult[1]["message"] = constant("INVALID_TCID_STR");		

		$result = $this->client->getResponse();

		$this->assertEquals($expectedResult, $this->client->getResponse());	
	}

	// TODO: Implement
	function testReportTCResultWithoutTPID()
	{				
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		// dependant on data in the sql file
		$data["tcid"] = TestlinkXMLRPCServerTestData::testTCID;
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// build up an array contining the error that should come back
		$expectedResult = array();		
		$expectedResult[0]["code"] = constant("NO_TPID");		
		$expectedResult[0]["message"] = constant("NO_TPID_STR");

		$response = $this->client->getResponse();
		$this->assertEquals($expectedResult, $response);	
	}	
	
	function testReportTCResultRequestWithoutStatus()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["tcid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["tpid"] = TestlinkXMLRPCServerTestData::testTPID;		
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}

		// build up an array contining the error that should come back
		$expectedResult = array();		
		$expectedResult[0]["code"] = constant("NO_STATUS");		
		$expectedResult[0]["message"] = constant("NO_STATUS_STR");

		$response = $this->client->getResponse();
		$this->assertEquals($expectedResult, $response);	
	}
	
	function testReportTCResultRequestWithInvalidStatus()
	{
				$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["tcid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["tpid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["status"] = "invalidStatus";
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}

		// build up an array contining the error that should come back
		$expectedResult = array();		
		$expectedResult[0]["code"] = constant("INVALID_STATUS");		
		$expectedResult[0]["message"] = constant("INVALID_STATUS_STR");

		$response = $this->client->getResponse();
		$this->assertEquals($expectedResult, $response);	
	}
	
	function testReportTCResultRequestWithBlockedStatus()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["tcid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["tpid"] = TestlinkXMLRPCServerTestData::testTPID;
		// set the status to blocked
		$data["status"] = "b";		
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		$response = $this->client->getResponse();
		// Just check the size is good since we don't know the insert id
		$this->assertEquals(3, sizeof($response[0]));
	}
	
	function testReportTCResultRequestWithPassedStatus()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["tcid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["tpid"] = TestlinkXMLRPCServerTestData::testTPID;
		// set the status to passed
		$data["status"] = "p";		
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		$response = $this->client->getResponse();
		// Just check the size is good since we don't know the insert id
		$this->assertEquals(3, sizeof($response[0]));
	}			
	
	function testReportTCResultRequestWithFailedStatus()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["tcid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["tpid"] = TestlinkXMLRPCServerTestData::testTPID;
		// set the status to failed
		$data["status"] = "f";		
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		$response = $this->client->getResponse();
		// Just check the size is good since we don't know the insert id
		$this->assertEquals(3, sizeof($response[0]));
	}	
	
	function testValidReportTCResultRequest()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["tcid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["tpid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["status"] = "p";		
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		$response = $this->client->getResponse();
		// Just check the size is good since we don't know the insert id
		$this->assertEquals(3, sizeof($response[0]));
	}
	
	function testValidReportTCResultRequestWithBuildID()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["tcid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["tpid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["buildid"] = TestlinkXMLRPCServerTestData::testBuildID;
		$data["guess"] = false;
		$data["status"] = "f";
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		//$expectedResult[0]["message"] = constant("GENERAL_SUCCESS_STR");
		//$expectedResult[0]["status"] = true;
		
		$response = $this->client->getResponse();
		// Just check the size is good since we don't know the insert id
		$this->assertEquals(3, sizeof($response[0]));
	}
		
	
	function testReportTCResultNotGuessingBuildID()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		
		$data["tcid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["tpid"] = TestlinkXMLRPCServerTestData::testTPID;
		// don't allow guessing the build id
		$data["guess"] = false;
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// build up an array contining the error that should come back
		$expectedResult = array();		
		$expectedResult[0]["code"] = constant("BUILDID_NOGUESS");		
		$expectedResult[0]["message"] = constant("BUILDID_NOGUESS_STR");
		$expectedResult[1]["code"] = constant("NO_BUILDID");		
		$expectedResult[1]["message"] = constant("NO_BUILDID_STR");
		
		$response = $this->client->getResponse();
		$this->assertEquals($expectedResult, $response);	
	}
		
	function testValidDevKeyWorks()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// The response should not have any errors related to the devKey
		$response = $this->client->getResponse();
				
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("INVALID_AUTH");		
		$expectedResult[0]["message"] = constant("INVALID_AUTH_STR");		
		$this->assertNotEquals($expectedResult, $this->client->getResponse());			
	
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("NO_DEV_KEY");		
		$expectedResult[0]["message"] = constant("NO_DEV_KEY_STR");			
		$this->assertNotEquals($expectedResult, $this->client->getResponse());
	}
	
	// TODO: Implement
	function testReportTCResultWithNoParams()
	{
		throw new PHPUnit_Framework_IncompleteTestError('This test is not yet implemented');	
	}
	// TODO: Implement
	function testReportTCResultWithInvalidTCIDAndTPIDCombo()
	{
		// TCID_NOT_IN_TPID, TCID_NOT_IN_TPID_STR
		throw new PHPUnit_Framework_IncompleteTestError('This test is not yet implemented');	
	}
	

	function testRepeat()
	{		
		$data = array();
		$data["str"] = "I like to talk to myself";
				
		if(!$this->client->query('tl.repeat', $data)) 
		{
			echo "\n\n" . $this->getName() . "something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();			
		}
		
		$this->assertEquals("You said: " . $data["str"], $this->client->getResponse());
	}
	
	function testReportTCResultWithTimestamp()
	{
		
	}
	
	function testReportTCResultWithNotes()
	{
		
	}	
		
	function testNonExistantMethod()
	{
		$this->assertFalse($this->client->query('tl.noSuchMethodExists'));				
	}
	
}

// this is only necessary if you want to run "php FileName.php"
// otherwise you can just run "phpunit FileName.php" and it should work
//PHPUnit_TextUI_TestRunner::run(TestlinkXMLRPCServerTest::suite());

?>
