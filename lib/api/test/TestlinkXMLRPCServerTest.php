<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: TestlinkXMLRPCServerTest.php,v 1.5 2009/01/23 20:28:27 asielb Exp $
 *
 * These tests require phpunit: http://www.phpunit.de/
 */
 
 /**
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * @link        http://testlink.org/api/
 */

require_once dirname(__FILE__) . '/../../../third_party/xml-rpc/class-IXR.php';
require_once dirname(__FILE__) . '/../APIErrors.php';
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
	protected $SERVER_URL = "http://localhost/testlink_trunk/lib/api/xmlrpc.php";
	
	function setUp()
	{	
		// This is the path to the server and will vary from machine to machine
		$this->client = $client = new IXR_Client($this->SERVER_URL);
		// run IXR_Client in debug mode showing verbose output
		$this->client->debug = true;
		//$this->setupTestMode();
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
		
		// Run specific individual tests
		$suite->addTest(new TestlinkXMLRPCServerTest('testSayHello'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithInvalidDevKey'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithoutDevKey'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithEmptyDevKey'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithoutTCID'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithInvalidTCID'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithoutNonIntTCID'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithoutTPID'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultRequestWithoutStatus'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultRequestWithInvalidStatus'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultRequestWithBlockedStatus'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultRequestWithPassedStatus'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultValidRequest'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithNoParams'));
		
		
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultRequestWithValidBuildID'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultNotGuessingBuildID'));
		
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithNotes'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testCreateBuildWithoutNotes'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testCreateBuildWithNotes'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testCreateBuildWithInvalidTPID'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testValidDevKeyWorks'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testGetProjects'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultRequestWithFailedStatus'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testCreateBuildWithInsufficientRights'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testGetProjectTestPlans'));
		
		
		$suite->addTest(new TestlinkXMLRPCServerTest('testGetTestCasesForTestSuite'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testGetTestCasesForTestSuiteDeepFalse'));
		
		$suite->addTest(new TestlinkXMLRPCServerTest('testGetTestCaseIDByName'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testGetTestCaseIDByNameWithInvalidName'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testRepeat'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testAbout'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testNonExistantMethod'));
		
		//NEW TESTS
		$suite->addTest(new TestlinkXMLRPCServerTest('testCreateBuildWithInsufficientRights'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithInsufficientRights'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testGetTestCasesForTestSuiteWithInsufficientRights'));
		$suite->addTest(new TestlinkXMLRPCServerTest('testGetTestCaseIDByNameWithInsufficientRights'));

        $suite->addTest(new TestlinkXMLRPCServerTest('testGetLastTestResult'));
		
		
		
		//INCOMPLETE
		
		
		//THERE IS NO RIGHTS ASSOCIATED WITH THIS YET - $suite->addTest(new TestlinkXMLRPCServerTest('testGetProjectsWithInsufficientRights'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithInvalidTCIDAndTPIDCombo'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testReportTCResultWithTimestamp'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testGetProjectTestPlansWithInvalidID'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testGetProjectTestPlansWithoutTestProjectID'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testGetTestSuitesForTestPlan'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testGetTestSuitesForTestPlanWithoutTestPlanID'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testGetTestCasesForTestSuiteWithInvalidSuiteID'));
		//$suite->addTest(new TestlinkXMLRPCServerTest('testGetTestCasesForTestSuiteWithoutSuiteID'));
		
		
		//		run all the tests		
		//$suite->addTestSuite('TestlinkXMLRPCServerTest');
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
	
	
	function testReportTCResultWithInsufficientRights()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::noRightsDevKey;
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["buildid"] = TestlinkXMLRPCServerTestData::testBuildID;
		// set the status to blocked
		$data["status"] = "b";		
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("INSUFFICIENT_RIGHTS");		
		$expectedResult[0]["message"] = constant("INSUFFICIENT_RIGHTS_STR");

		$result = $this->client->getResponse();
		$this->assertEquals($expectedResult, $result);	
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
		$expectedResult[0]["code"] = constant("NO_TCASEID");		
		$expectedResult[0]["message"] = constant("NO_TCASEID_STR");

		var_dump($expectedResult);

		var_dump($this->client->getResponse());

		$this->assertEquals($expectedResult, $this->client->getResponse());	
	}
	
	function testReportTCResultWithInvalidTCID()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["status"] = "f";
		$data["testcaseid"] = -100; // shouldn't have a negative number for TCID in db
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// build up an array contining the error that should come back
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("INVALID_TCASEID");		
		$expectedResult[0]["message"] = constant("INVALID_TCASEID_STR");

		$result = $this->client->getResponse();
		//print_r($result);
		//print_r($expectedResult);
		$this->assertEquals($expectedResult, $result);	
	}
	
	function testReportTCResultWithoutNonIntTCID()
	{				
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testcaseid"] = "notAnInt";
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// build up an array containing the errors that should come back
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("TCASEID_NOT_INTEGER");		
		$expectedResult[0]["message"] = constant("TCASEID_NOT_INTEGER_STR");
		$expectedResult[1]["code"] = constant("INVALID_TCASEID");		
		$expectedResult[1]["message"] = constant("INVALID_TCASEID_STR");		

		$result = $this->client->getResponse();
		
		$this->assertEquals($expectedResult, $this->client->getResponse());	
	}

	// TODO: Implement
	function testReportTCResultWithoutTPID()
	{				
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		// dependant on data in the sql file
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		// build up an array contining the error that should come back
		$expectedResult = array();		
		$expectedResult[0]["code"] = constant("NO_TPLANID");		
		$expectedResult[0]["message"] = constant("NO_TPLANID_STR");

		$response = $this->client->getResponse();
		$this->assertEquals($expectedResult, $response);	
	}	
	
	function testReportTCResultRequestWithoutStatus()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;		
		$data["buildid"] = TestlinkXMLRPCServerTestData::testBuildID;
		
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
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["buildid"] = TestlinkXMLRPCServerTestData::testBuildID;
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
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["buildid"] = TestlinkXMLRPCServerTestData::testBuildID;
		// set the status to blocked
		$data["status"] = "b";		
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		$response = $this->client->getResponse();
		var_dump($response);
		// Just check the size is good since we don't know the insert id
		$this->assertEquals(3, sizeof($response[0]));
	}
	
	function testReportTCResultRequestWithPassedStatus()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["buildid"] = TestlinkXMLRPCServerTestData::testBuildID;
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
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["buildid"] = TestlinkXMLRPCServerTestData::testBuildID;
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
		
	function testReportTCResultWithNoParams()
	{
		$data = array();		
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}		
		
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("NO_DEV_KEY");		
		$expectedResult[0]["message"] = constant("NO_DEV_KEY_STR");			
		$this->assertEquals($expectedResult, $this->client->getResponse());	
	}
	
	// TODO: Implement
	function testReportTCResultWithInvalidTCIDAndTPIDCombo()
	{
		// TCID_NOT_IN_TPID, TCID_NOT_IN_TPID_STR
		throw new PHPUnit_Framework_IncompleteTestError('This test is not yet implemented');	
	}	
	
	function testReportTCResultValidRequest()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["buildid"] = TestlinkXMLRPCServerTestData::testBuildID;
		$data["status"] = "p";		
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		$response = $this->client->getResponse();
		// Just check the size is good since we don't know the insert id
		$this->assertEquals(3, sizeof($response[0]));
	}

    function testGetLastTestResult()
	{
		//Setup a Known Response by reporting a block
        $data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["buildid"] = TestlinkXMLRPCServerTestData::testBuildID;
		$data["status"] = "b";

		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}

		$response = $this->client->getResponse();

//Now Building our get last test result
        $data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		if(!$this->client->query('tl.getLastTestResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
        $response = $this->client->getResponse();
        // Just check the size is good since we don't know the insert id
        print_r($response);
        $this->assertEquals(9, sizeof($response[0]));
        $this->assertEquals('b', $response[0]['status']);
		
	}

	function testReportTCResultRequestWithValidBuildID()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
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
		
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
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

	function testReportTCResultWithTimestamp()
	{
		throw new PHPUnit_Framework_IncompleteTestError('This test is not yet implemented');	
	}
	
	function testReportTCResultWithNotes()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testcaseid"] = TestlinkXMLRPCServerTestData::testTCID;
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["status"] = "p";
		$data["buildid"] = TestlinkXMLRPCServerTestData::testBuildID;
		$data["notes"] = "this is a note about the test";
		
		if(!$this->client->query('tl.reportTCResult', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		$response = $this->client->getResponse();
		// Just check the size is good since we don't know the insert id
		var_dump($response);
		
		$this->assertEquals(3, sizeof($response[0]));				
	}
	
	function testCreateBuildWithInsufficientRights()
	{	
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::noRightsDevKey;
		$data["buildname"] = "Another test build from " . strftime("%c");
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		
		if(!$this->client->query('tl.createBuild', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}		
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("INSUFFICIENT_RIGHTS");		
		$expectedResult[0]["message"] = constant("INSUFFICIENT_RIGHTS_STR");

		$result = $this->client->getResponse();
		$this->assertEquals($expectedResult, $result);		
	}		
	
	function testCreateBuildWithoutNotes()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;		
		$data["buildname"] = "Another test build from " . strftime("%c");
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		
		if(!$this->client->query('tl.createBuild', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}		
		$response = $this->client->getResponse();
		var_dump($response);
		$this->assertEquals(3, sizeof($response[0]));
	}
	
	function testCreateBuildWithNotes()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;		
		$data["buildname"] = "Another notes test build from " . strftime("%c");
		$data["testplanid"] = TestlinkXMLRPCServerTestData::testTPID;
		$data["buildnotes"] = "Some notes from the build created at " . strftime("%c");
		
		if(!$this->client->query('tl.createBuild', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}		
		$response = $this->client->getResponse();
		$this->assertEquals(3, sizeof($response[0]));
	}
	
	function testCreateBuildWithInvalidTPID()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;		
		$data["buildname"] = "Another test build from " . strftime("%c");
		$data["testplanid"] = -1;
		$data["buildnotes"] = "Some notes from the build created at " . strftime("%c");
		
		if(!$this->client->query('tl.createBuild', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}		
		
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("INVALID_TPLANID");		
		$expectedResult[0]["message"] = sprintf(constant("INVALID_TPLANID_STR"), $data["testplanid"]);

		$result = $this->client->getResponse();
		//print_r($expectedResult);
        //print_r($result);

        $this->assertEquals($expectedResult, $result);
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
	
	function testGetProjects()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		
		if(!$this->client->query('tl.getProjects', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		$expectedResult = array();
		
		$expectedResult[0]["id"] = "1";
		$expectedResult[0]["notes"] = "<p>A project for testing</p>";
		
		$expectedResult[0]["color"] = "";
		
		$expectedResult[0]["active"] = "1";
		$expectedResult[0]["option_reqs"] = "1";
		$expectedResult[0]["option_priority"] = "1";		
		$expectedResult[0]["prefix"] = "";
		$expectedResult[0]["tc_counter"] = "0";
		$expectedResult[0]["option_automation"] = "0";
		$expectedResult[0]["name"] = "Test Project";
		
		$response = $this->client->getResponse();
		
		var_dump($response);
		var_dump($expectedResult);
		
		$this->assertEquals($expectedResult, $response);		
	}
	
	function testGetProjectsWithInsufficientRights()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::noRightsDevKey;
		
		if(!$this->client->query('tl.getProjects', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("INSUFFICIENT_RIGHTS");		
		$expectedResult[0]["message"] = constant("INSUFFICIENT_RIGHTS_STR");

		$result = $this->client->getResponse();
		$this->assertEquals($expectedResult, $result);	
		
				
	}
	
	function testGetProjectTestPlansWithInvalidID()
	{
		//TODO: Implement
		throw new PHPUnit_Framework_IncompleteTestError('This test is not yet implemented');
	}
	function testGetProjectTestPlansWithoutTestProjectID()
	{
		//TODO: Implement
		throw new PHPUnit_Framework_IncompleteTestError('This test is not yet implemented');
	}
	
	function testGetProjectTestPlans()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testprojectid"] = 1;
		
		if(!$this->client->query('tl.getProjectTestPlans', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}				
						
		$expectedResult = array();
		$testplanID = 2;
		$expectedResult[$testplanID]["id"] = $testplanID;
		$expectedResult[$testplanID]["name"] = "A test plan for testing";
		// characters like <p> are stripped
		$expectedResult[$testplanID]["notes"] = "<p>A description of a test plan for testing</p>";		
		$expectedResult[$testplanID]["active"] = "1";
		$expectedResult[$testplanID]["testproject_id"] = "1";				

        $expectedResult = array($expectedResult);


		$response = $this->client->getResponse();
		//print_r($expectedResult);
        //print_r($response);
		
		$this->assertEquals($expectedResult, $response);
	}
	
	function testGetTestSuitesForTestPlan()
	{
		//TODO: Implement
		throw new PHPUnit_Framework_IncompleteTestError('This test is not yet implemented');
	}

	function testGetTestSuitesForTestPlanWithoutTestPlanID()
	{
		//TODO: Implement
		throw new PHPUnit_Framework_IncompleteTestError('This test is not yet implemented');
	}	
	
	function testGetTestCasesForTestSuite()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testsuiteid"] = 3;
		
		if(!$this->client->query('tl.getTestCasesForTestSuite', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}				
		
		$expectedResult = array();
		$expectedResult[0]["id"] = 11;
		$expectedResult[0]["name"] = "test case in child suite";
		$expectedResult[0]["parent_id"] = 10;
		$expectedResult[0]["node_type_id"] = 3;
		$expectedResult[0]["node_order"] = 100;
		$expectedResult[1]["id"] = 4;
		$expectedResult[1]["name"] = "First test case version 3";
		$expectedResult[1]["parent_id"] = 3;
		$expectedResult[1]["node_type_id"] = 3;
		$expectedResult[1]["node_order"] = 100;
		$expectedResult[2]["id"] = 6;
		$expectedResult[2]["name"] = "Another test case";
		$expectedResult[2]["parent_id"] = 3;
		$expectedResult[2]["node_type_id"] = 3;
		$expectedResult[2]["node_order"] = 100;
		
		$response = $this->client->getResponse();
		
		$this->assertEquals($expectedResult, $response, "arrays do not match");		
	}
	
	function testGetTestCasesForTestSuiteWithInsufficientRights()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::noRightsDevKey;
		$data["testsuiteid"] = 3;
		
		if(!$this->client->query('tl.getTestCasesForTestSuite', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}				
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("INSUFFICIENT_RIGHTS");		
		$expectedResult[0]["message"] = constant("INSUFFICIENT_RIGHTS_STR");

		$result = $this->client->getResponse();
		$this->assertEquals($expectedResult, $result);		
	}

	function testGetTestCasesForTestSuiteDeepFalse()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testsuiteid"] = 3;
		$data["deep"] = false;
		
		if(!$this->client->query('tl.getTestCasesForTestSuite', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}		
				
		$expectedResult = array();
		$expectedResult[0]["id"] = 4;
		$expectedResult[0]["name"] = "First test case version 3";
		$expectedResult[0]["parent_id"] = 3;
		$expectedResult[0]["node_type_id"] = 3;
		$expectedResult[0]["node_order"] = 100;
		$expectedResult[1]["id"] = 6;
		$expectedResult[1]["name"] = "Another test case";
		$expectedResult[1]["parent_id"] = 3;
		$expectedResult[1]["node_type_id"] = 3;
		$expectedResult[1]["node_order"] = 100;
		
		$response = $this->client->getResponse();
		//print_r($response);				
		$this->assertEquals($expectedResult, $response, "arrays do not match");				
	}
	
	function testGetTestCasesForTestSuiteWithoutSuiteID()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testsuiteid"] = 3;
		
		if(!$this->client->query('tl.getTestCasesForTestSuite', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}				
		
		$response = $this->client->getResponse();
		//print_r($response);
		
		//TODO: Implement
		throw new PHPUnit_Framework_IncompleteTestError('This test is not yet implemented');
	}

	function testGetTestCasesForTestSuiteWithInvalidSuiteID()
	{
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testsuiteid"] = 2000;
		
		if(!$this->client->query('tl.getTestCasesForTestSuite', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}				
		
		$response = $this->client->getResponse();
		//print_r($response);
		
		//TODO: Implement
		throw new PHPUnit_Framework_IncompleteTestError('This test is not yet implemented');
	}


	function testGetTestCaseIDByName()
	{
		$tcName = "First test case version 3";
		
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testcasename"] = $tcName;
		
		if(!$this->client->query('tl.getTestCaseIDByName', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}				
						
		$expectedResult = array();		
		$expectedResult[0]["id"] = TestlinkXMLRPCServerTestData::testTCID;
		$expectedResult[0]["name"] = $tcName;
        $expectedResult[0]["parent_id"] = "1";
        $expectedResult[0]["tsuite_name"] = "Top Level Suite";
        $expectedResult[0]["tc_external_id"] = "0";
		
		$response = $this->client->getResponse();
		//print_r($response);
        //print_r($expectedResult);
		
		$this->assertEquals($expectedResult, $response);
	}		
	
	function testGetTestCaseIDByNameWithInsufficientRights()
	{
		$tcName = "First test case version 3";
		
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::noRightsDevKey;
		$data["testcasename"] = $tcName;
		
		if(!$this->client->query('tl.getTestCaseIDByName', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}				
				$expectedResult = array();
		$expectedResult[0]["code"] = constant("INSUFFICIENT_RIGHTS");		
		$expectedResult[0]["message"] = constant("INSUFFICIENT_RIGHTS_STR");

		$result = $this->client->getResponse();
		$this->assertEquals($expectedResult, $result);	
	}

	function testGetTestCaseIDByNameWithInvalidName()
	{
		$tcName = "A Test case that does not exist";
		
		$data = array();
		$data["devKey"] = TestlinkXMLRPCServerTestData::testDevKey;
		$data["testcasename"] = $tcName;
		
		if(!$this->client->query('tl.getTestCaseIDByName', $data)) {
			echo "\n" . $this->getName() . " >> something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}				
				
		$expectedResult = array();
		$expectedResult[0]["code"] = constant("NO_TESTCASE_BY_THIS_NAME");		
		$expectedResult[0]["message"] = "(getTestCaseIDByName) - " . constant("NO_TESTCASE_BY_THIS_NAME_STR");
		
		$response = $this->client->getResponse();
		//print_r($expectedResult);
        //print_r($response);
		
		$this->assertEquals($expectedResult, $response);
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
	
	function testAbout()
	{
		if(!$this->client->query('tl.about', null))
		{
			echo "\n\n" . $this->getName() . "something went really wrong - " . $this->client->getErrorCode() .
					$this->client->getErrorMessage();
		}
		else
		{
			echo "success!";
		}
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
