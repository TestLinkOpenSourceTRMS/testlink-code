<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: TestlinkXMLRPCServerTestData.php,v 1.3 2008/10/03 05:02:12 asielb Exp $
 */
 
/** 
 * Test data for running TestlinkXMLRPCServer unit tests
 * 
 * This is data required to run some of the tests
 * Run the file in your browser and it will print the SQL to insert
 * 
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * @link        http://testlink.org/testlinkWebServices
 */
 
 /**
  * Alternate class method of generating necessary test data
  * @deprecated The SQL file should be used since this isn't maintained 
  */
class TestlinkXMLRPCServerTestData
{
	// dependant on data in the sql file
	const testDevKey = "validTestDevKey";
	const noRightsDevKey = "devKeyWithNoRights";
	const testTPID = 2; 
	const testTCID = 4;
	const testBuildID = 1;
	
	
	public function __construct()
	{
		echo $this->_getMessage();
		echo $this->_getTestDeveloperKeys();
	}
	
	private function _getTestDeveloperKeys()
	{
		$str = "";
		$str .= "INSERT INTO `api_developer_keys` (`id` ,`developer_key` ,`user_id`) VALUES (NULL , '" . self::testDevKey . "', '1');";
		$str .= "<br>";
		return $str;
	}
	
	private function _getNewTCID()
	{
		$str = "";
		$str .= "@tcid := SELECT MAX()";
		//$str .= "INSERT INTO "
	}
	
	private function _getNewTPID()
	{
		
	}
	
	private function _getMessage()
	{
		$str = "";
		$str .= "-- Copy this SQL text and insert it into your testlink database";
		$str .= "<br>";
		return $str;
	}
}

// don't output the data when running from cli i.e. gets included in phpUnit test run
if(php_sapi_name() != "cli")
{
	$data = new TestlinkXMLRPCServerTestData();	
}

?>