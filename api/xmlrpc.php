<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: xmlrpc.php,v 1.2 2007/11/25 18:56:18 franciscom Exp $
 */
 
/**
 * The Testlink API makes it possible to interact with Testlink {@link http://testlink.org} 
 * using external applications and services. This makes it possible to report test results 
 * directly from automation frameworks as well as other features.
 * 
 * See examples for additional detail
 * @example ../sample_clients/java/org/testlink/api/client/sample/TestlinkAPIXMLRPCClient.java java client sample
 * @example ../sample_clients/php/clientSample.php php client sample
 * @example ../sample_clients/ruby/clientSample.rb ruby client sample
 * @example ../sample_clients/python/clientSample.py python client sample
 * 
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * @link        http://testlink.org/api/
 * 

 */

/** 
 * IXR is the class used for the XML-RPC server 
 */
require_once(dirname(__FILE__) . "/../third_party/xml-rpc/class-IXR.php");
require_once("TestlinkXMLRPCServerErrors.php");
require_once(dirname(__FILE__) . "/../config.inc.php");
require_once(dirname(__FILE__) . "/../lib/functions/common.php");
require_once("api.const.inc.php");

/**
 * The entry class for serving XML-RPC Requests
 * 
 * See examples for additional detail
 * @example ../sample_clients/java/org/testlink/api/client/sample/TestlinkAPIXMLRPCClient.java java client sample
 * @example ../sample_clients/php/clientSample.php php client sample
 * @example ../sample_clients/ruby/clientSample.rb ruby client sample
 * @example ../sample_clients/python/clientSample.py python client sample
 * 
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI 
 * @since 		Class available since Release 1.8.0
 * @version 	1.0
 */
class TestlinkXMLRPCServer extends IXR_Server
{
	public static $version = "1.0 Beta";
	/**
	 * The DB object used throughout the class
	 * 
	 * @access private
	 */
	private $dbObj = null;
	/** Whether the server will run in a testing mode */
	private  $testMode = false;
	/** userID associated with the devKey provided */
	private $userID = null;
	/** array where all the args are stored for requests */
	private $args = null;	
	/** array where error codes and messages are stored */
	private $errors = array();
	/** The api key being used to make a request */
	private $devKey = null;
	/** The version of a test case that is being used */
	private $tcVersionID = null;
	
	/**#@+
	 * string for parameter names are all definied statically
	 * @static
 	 */
	public static $devKeyParamName = "devKey";
	public static $tcidParamName = "tcid";
	public static $tpidParamName = "tpid";
	public static $statusParamName = "status";
	public static $buildidParamName = "buildid";
	public static $noteParamName = "note";
	public static $timeStampParamName = "timestamp";
	public static $guessParamName = "guess";
	public static $testModeParamName = "testmode";
	public static $buildNameParamName = "buildname";
	public static $automatedParamName = "automated";
	/**#@-*/
	
	/**
	 * An array containing strings for valid statuses 
	 */
	public static $validStatusList = array("p", "f", "b");

	
	/**
	 * Constructor sets up the IXR_Server and db connection
	 */
	public function __construct()
	{		
		$this->dbObj = new database(DB_TYPE);
		$this->_connectToDB();

		$this->methods = array(
			'tl.reportTCResult' 		=> 'this:reportTCResult',
			'tl.createBuild'			=> 'this:createBuild',
			'tl.about'					=> 'this:about',
			'tl.setTestMode'			=> 'this:setTestMode',
			// ping is an alias for sayHello
			'tl.ping'					=> 'this:sayHello', 
			'tl.sayHello' 				=> 'this:sayHello',
			'tl.repeat'					=> 'this:repeat'
		);				
		
		$this->IXR_Server($this->methods);		
	}	
	
	private function _setArgs($args)
	{
		// TODO: should escape args
		$this->args = $args;
	}
	
	/**
	 * Set the BuildID from one place
	 * 
	 * @param int $buildID
	 * @access private
	 */
	private function _setBuildID($buildID)
	{		
		if(GENERAL_ERROR_CODE != $buildID)
		{			
			$this->args[self::$buildidParamName] = $buildID;			
			return true;
		}
		else
		{
			$this->errors[] = new IXR_Error(INVALID_BUILDID, INVALID_BUILDID_STR);
			return false;
		}	
	}
	
	/**
	 * Helper method set the buildID based on the tpid
	 * 
	 * @return boolean
	 * @access private
	 */ 
	private function _setBuildIDFromTPID()
	{
		$result = $this->_setBuildID($this->getLatestBuildForTestPlan($this->args[self::$tpidParamName]));
		return $result;
	}	
		
	/**
	 * connect to the db and set up the db object 
	 *
	 * @access private
	 */		
	private function _connectToDB()
	{
		if(true == $this->testMode)
		{
			return $this->dbObj->connect(TEST_DSN, TEST_DB_HOST, TEST_DB_USER, TEST_DB_PASS, TEST_DB_NAME);
		}
		else
		{
			return $this->dbObj->connect(DSN, DB_HOST, DB_USER, DB_PASS, DB_NAME);
		}					
	}

	/**
	 * authenticates a user based on the devKey provided 
	 * 
	 * This is the only method that should really be used directly to authenticate
	 *
	 * @return boolean
	 * @access private
	 */
    protected function authenticate()
    {        	
		// check that the key was given as part of the args
		if(!$this->_isDevKeyPresent())
		{
			$this->errors[] = new IXR_ERROR(NO_DEV_KEY, NO_DEV_KEY_STR);
			return false;
		}
		else
		{
			$this->devKey = $this->args[self::$devKeyParamName];
		}
		// make sure the key we have is valid
		if(!$this->_isDevKeyValid($this->devKey))
		{
			$this->errors[] = new IXR_Error(INVALID_AUTH, INVALID_AUTH_STR);
			return false;			
		}
		else
		{
			return true;
		}				
    }
    
	/**
	 * Helper method to see if the status provided is valid 
	 * 
	 * This is the only method that should be called directly to check the status
	 * 	
	 * @return boolean
	 * @access private
	 */    
    protected function checkStatus()
    {
		if(!$this->_isStatusPresent())
		{
			$this->errors[] = new IXR_Error(NO_STATUS, NO_STATUS_STR);
			return false;
		}
		$status = $this->args[self::$statusParamName];
		if(!$this->_isStatusValid($status))
		{
			$this->errors[] = new IXR_Error(INVALID_STATUS, INVALID_STATUS_STR);
			return false;
		}    	
		return true;
    }       
    
	/**
	 * Helper method to see if the tcid provided is valid 
	 * 
	 * This is the only method that should be called directly to check the tcid
	 * 	
	 * @return boolean
	 * @access private
	 */    
    protected function checkTCID()
    {
		if(!$this->_isTCIDPresent())
		{
			$this->errors[] = new IXR_Error(NO_TCID, NO_TCID_STR);
			return false;
		}
		$tcid = $this->args[self::$tcidParamName];
		if(!$this->_isTCIDValid($tcid))
		{
			$this->errors[] = new IXR_Error(INVALID_TCID, INVALID_TCID_STR);
			return false;
		}    	
		return true;
    }
    
	/**
	 * Helper method to see if the TPID provided is valid
	 * 
	 * This is the only method that should be called directly to check the TPID
	 * 	
	 * @return boolean
	 * @access private
	 */    
    protected function checkTPID()
    {
    	if(!$this->_isTPIDPresent())
    	{
    		$this->errors[] = new IXR_Error(NO_TPLANID, NO_TPLANID_STR);
    		return false;
    	}
    	else
    	{    		
    		// See if this TPID exists in the db
			$tpid = mysql_escape_string($this->args[self::$tpidParamName]);
        	$query = "SELECT id FROM testplans WHERE id={$tpid}";
        	$result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");         	
        	if(null == $result)
        	{
        		$this->errors[] = new IXR_Error(INVALID_TPLANID, INVALID_TPLANID_STR);
        		return false;        		
        	}
			// tpid exists and its valid
        	else
        	{
        		// try to guess the buildid if it isn't already set
		    	if(!$this->_isBuildIDPresent())
		    	{
			    	// can only set the build id for the test plan if guessing is enabled
    				if(true == $this->checkGuess())
    				{
    					$result = $this->_setBuildIDFromTPID();
    					return $result;    						
    				}
		    	}
        		return true;
        	}    		    		    	
    	}
    } 

	/**
	 * Helper method to see if the guess is set
	 * 
	 * This is the only method that should be called directly to check the guess param
	 * 
	 * Guessing is set to true by default
	 * @return boolean
	 * @access private
	 */    
    protected function checkGuess()
    {    	
    	// if guess is set return its value otherwise return true to guess by default
    	return($this->_isGuessPresent() ? $this->args[self::$guessParamName] : true);	
    }   	
    
	/**
	 * Helper method to see if the buildID provided is valid
	 * 
	 * This is the only method that should be called directly to check the buildID
	 * 	
	 * @return boolean
	 * @access private
	 */    
    protected function checkBuildID()
    {
	   	// buildid isn't already set
	   	if(!$this->_isBuildIDPresent())
	   	{
			// this means we aren't supposed to guess the buildid
			if(false == $this->checkGuess())   		
			{
				$this->errors[] = new IXR_Error(BUILDID_NOGUESS, BUILDID_NOGUESS_STR);
				$this->errors[] = new IXR_Error(NO_BUILDID, NO_BUILDID_STR);				
    			return false;
			}
			else
			{
				$setBuildResult = $this->_setBuildIDFromTPID();
				if(false == $setBuildResult)
				{
					$this->errors[] = new IXR_Error(NO_BUILD_FOR_TPLANID, NO_BUILD_FOR_TPLANID_STR);
					return false;
				}
			}
	   	}
	   	
	   	// actually check that the buildID thats set is valid
	   	$buildID = mysql_escape_string($this->args[self::$buildidParamName]);
        $query = "SELECT id FROM builds WHERE id='{$buildID}'";
        $result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");         	
        return (null == $result ? false : true);
    }
        
    private function _isStatusValid($status)
    {
    	return(in_array($status, self::$validStatusList));
    }           

    /**
	 * Helper method to see if a timestamp is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */    
    private function _isTimeStampPresent()
    {
    	return (isset($this->args[self::$timeStampParamName]) ? true : false);
    }

    /**
	 * Helper method to see if a buildID is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */    
    private function _isBuildIDPresent()
    {
    	return (isset($this->args[self::$buildidParamName]) ? true : false);
    }
    
    /**
	 * Helper method to see if a note is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */    
    private function _isNotePresent()
    {
    	return (isset($this->args[self::$noteParamName]) ? true : false);
    }        
    
    /**
	 * Helper method to see if a TPID is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */    
    private function _isTPIDPresent()
    {    	
    	return (isset($this->args[self::$tpidParamName]) ? true : false);    	
    }
    
    /**
	 * Helper method to see if automated is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */    
    private function _isAutomatedPresent()
    {    	
    	return (isset($this->args[self::$automatedParamName]) ? true : false);    	
    }        
    
    /**
	 * Helper method to see if testMode is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */    
    private function _isTestModePresent()
    {
    	return (isset($this->args[self::$testModeParamName]) ? true : false);      
    }
    
    /**
	 * Helper method to see if a devKey is given as one of the arguments 
	 * 	 
	 * @return boolean
	 * @access private
	 */
    private function _isDevKeyPresent()
    {
    	return (isset($this->args[self::$devKeyParamName]) ? true : false);
    }
    
    /**
	 * Helper method to see if a tcid is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */
    private function _isTCIDPresent()
    {
		return (isset($this->args[self::$tcidParamName]) ? true : false);
    }  
    
	/**
	 * Helper method to see if the guess param is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */
    private function _isGuessPresent()
    {
		return (isset($this->args[self::$guessParamName]) ? true : false);
    }  
    
	/**
	 * Helper method to see if the status param is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */
    private function _isStatusPresent()
    {
		return (isset($this->args[self::$statusParamName]) ? true : false);
    }      
    
	/**
	 * Helper method to see if the tcid provided is valid 
	 * 	
	 * @param struct $tcid	 
	 * @return boolean
	 * @access private
	 */
    private function _isTCIDValid($tcid)
    {
    	if(!is_int($tcid))
    	{
    		$this->errors[] = new IXR_Error(TCID_NOT_INTEGER, TCID_NOT_INTEGER_STR);
    		return false;
    	}
    	$tcid = mysql_escape_string($tcid);
    	// the tcid must be of type 'testcase' and show up in the nodes_hierarchy    	
		$query = "SELECT nodes_hierarchy.id AS id FROM nodes_hierarchy, node_types " .
				"WHERE nodes_hierarchy.id={$tcid} AND node_type_id=node_types.id " .
				"AND node_types.description='testcase'";
		$result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");
		if(null == $result)
    	{
    		return false;        		
    	}
    	else
    	{
    		return true;
    	}    	
    }    
    
    /**
	 * Helper method to see if a devKey is valid 
	 * 	
	 * @param string $devKey	 
	 * @return boolean
	 * @access private
	 */    
    private function _isDevKeyValid($devKey)
    {    	       	        
        if(null == $devKey || "" == $devKey)
        {
            return false;
        }
        else
        {        	                		
        	$devKey = mysql_escape_string($devKey);
        	$query = "SELECT id FROM api_developer_keys WHERE developer_key='{$devKey}'";
        	$result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");         	
        	if(null == $result)
        	{
        		return false;        		
        	}
        	else
        	{
        		$this->devKey = $devKey;
        		// set the userID based on this valid devKey
        		$query = "SELECT user_id FROM api_developer_keys WHERE developer_key='{$devKey}'";
        		$this->userID = $this->dbObj->fetchFirstRowSingleColumn($query, "user_id");
        		         	
        		return true;
        	}
        }                    	
    }    

    /**
	 * Helper method to set the tcVersion
	 * 
	 * 		 
	 * @return boolean
	 * @access private
	 */        
    private function _setTCVersion()
    {
		// TODO: Implement
    }
    
    /**
	 * Helper method to See if the tcid and tpid are valid together 
	 * 
	 * @return boolean
	 * @access private
	 */            
    private function _checkTCIDAndTPIDValid()
    {  	
    	$tpid = $this->args[self::$tpidParamName];
    	$tcid = $this->args[self::$tcidParamName];
    	
    	// get all versions of the testcase in the nodes_hierarchy    	
    	$query = "SELECT nodes_hierarchy.id AS id FROM nodes_hierarchy, node_types " .
    			"WHERE nodes_hierarchy.parent_id=$tcid AND node_type_id=node_types.id " .
    			"AND node_types.description='testcase_version'";
    	$result = $this->dbObj->fetchColumnsIntoArray($query, "id");
    	// make sure we don't have an empty array
    	if(count($result) > 0)
    	{
	    	// determine which version if any is part of the test plan 
	    	$versionQuery = "SELECT tcversion_id FROM `testplan_tcversions` WHERE tcversion_id IN(" . 
	    				implode(",", $result) . ") AND testplan_id=$tpid";
	    	$versionResult = $this->dbObj->fetchFirstRowSingleColumn($versionQuery, "tcversion_id");			      	
	    	if(null == $versionResult)
	    	{
	    		$this->errors[] = new IXR_Error(TCID_NOT_IN_TPLANID, TCID_NOT_IN_TPLANID_STR);
	    		return false;        		
	    	}
	    	else
	    	{
	    		$this->tcVersionID = $versionResult;
	    		return true;
	    	}
    	}
    	else
    	{
    		// this should not ever happen unless the db is in a messed up state
    		$this->errors[] = new IXR_Error(INVALID_TCID, INVALID_TCID_STR);
    		return false;	
    	}    	
    }

	/**
	 * Run all the necessary checks to see if the request is valid
	 *  
	 * @return boolean
	 * @access private
	 */
	private function _checkReportTCResultRequest()
	{		
		if(!$this->authenticate())
		{
			return false;
		}
		if(!$this->checkTCID())
		{
			return false;
		}					
		if(!$this->checkTPID())
		{
			return false;
		}	
		if(!$this->checkBuildID())
		{
			return false;	
		}	
		if(!$this->checkStatus())
		{
			return false;
		}
		if(!$this->_checkTCIDAndTPIDValid())
		{			
			return false;
		}	
		else
		{
			// Hurray the request is valid!			
			return true;
		}
	}

 	/**
	 * Gets the latest build by date for a specific test plan 
	 *
	 * @param int $tpid
	 * @return int
	 * @access private
	 */		
	protected function getLatestBuildForTestPlan($tpid)
	{     	                		
    	// TODO: fix to be db independent (not mysql only)
    	$devKey = mysql_escape_string($tpid);
    	$query = "SELECT max(id) AS id FROM `builds` WHERE testplan_id='$tpid'";
    	$result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");         	
    	if(null == $result)
    	{
    		// Return generic error code signifying no build
    		return GENERAL_ERROR_CODE;        		
    	}
    	else
    	{
    		return $result;
    	}              		 
	}

 	/**
	 * Adds the result to the database 
	 *
	 * @return int
	 * @access private
	 */			
	private function _insertResultToDB()
	{
		$build_id = 	$this->args[self::$buildidParamName];
		$tester_id = 	$this->userID;
		$status = 		$this->args[self::$statusParamName];
		$testplan_id =	$this->args[self::$tpidParamName];
		$tcversion_id =	$this->tcVersionID;
		// TODO: set the automated flag correctly
		$automated = 1;
		
		$query = "INSERT INTO executions (build_id, tester_id, execution_ts, status, " .
				"testplan_id, tcversion_id, automated) VALUES(" .
				$build_id . "," .
				$tester_id . "," .
				"NOW()," . 
				"'" . $status . "'," .
				$testplan_id . "," .
				$tcversion_id . "," .
				$automated .
			")";
		$this->dbObj->exec_query($query);
		return $this->dbObj->insert_id();		
	}

	/**
	 * Lets you see if the server is up and running
	 *  
	 * @param struct not used	
	 * @return string "Hello!"
	 * @access public
	 */
	public function sayHello($args)
	{
		return 'Hello!';
	}

	/**
	 * Repeats a message back 
	 *
	 * @param struct $args should contain $args['str'] parameter
	 * @return string
	 * @access public
	 */	
	public function repeat($args)
	{
		$this->_setArgs($args);
		$str = "You said: " . $this->args['str'];
		return $str;
	}

	/**
	 * Gives basic information about the API
	 *
	 * @param struct not used
	 * @return string
	 * @access public
	 */	
	public function about($args)
	{
		$str = " Testlink API Version: " . self::$version . " written by Asiel Brumfield\n" .
				"See http://testlink.org/api/ for additional information";  
	}
	
	/**
	 * Creates a new build for a specific test plan
	 *
	 * @param struct $args
	 * @param int $args["tpid"]
	 * @param string $args["buildname"];
	 * @return mixed $resultInfo
	 * 				
	 * @access public
	 */		
	public function createBuild($args)
	{
		// TODO: Implement 
	}
	
	/**
	 * Gets a list of all active projects
	 *
	 * @return mixed $resultInfo			
	 * @access public
	 */		
	public function getProjects($args)
	{
		// TODO: Implement 
	}
	
	/**
	 * Gets a list of test plans within a project
	 *
	 * @param struct $args
	 * @param int $args["projectid"]
	 * @return mixed $resultInfo
	 * 				
	 * @access public
	 */		
	public function getProjectTestPlans($args)
	{
		// TODO: Implement 
	}
	

	 /**
	 * Reports a result for a single test case
	 *
	 * See examples for additional detail
	 * @example ../sample_clients/java/org/testlink/api/client/sample/TestlinkAPIXMLRPCClient.java java client sample
	 * @example ../sample_clients/php/clientSample.php php client sample
	 * @example ../sample_clients/ruby/clientSample.rb ruby client sample
	 * @example ../sample_clients/python/clientSample.py python client sample
	 * 
	 * @param struct $args
	 * @param string $args["devKey"]
	 * @param int $args["tcid"]
	 * @param int $args["tpid"] 
     * @param string $args["status"] - status is {@link $validStatusList}
     * @param int $args["buildid"] - optional
     * @param string $args["note"] - optional
     * @param bool $args["guess"] - optional definiing whether to guess optinal params or require them 
     * 								explicitly default is true (guess by default)
	 * @return mixed $resultInfo 
	 * 				[status]	=> true/false of success
	 * 				[id]		=> result id or error code
	 * 				[message]	=> optional message for error message string
	 * @access public
	 */
	public function reportTCResult($args)
	{		
		$this->_setArgs($args);
		// Verify that we have everything we need to create a new execution
		if($this->_checkReportTCResultRequest($this->args))
		{			
			$insertID = $this->_insertResultToDB();			
			$resultInfo = array();
			$resultInfo[0]["status"] = true;
			$resultInfo[0]["id"] = $insertID;	
			$resultInfo[0]["message"] = GENERAL_SUCCESS_STR;
			return $resultInfo;
		}
		else
		{
			return $this->errors;			
		}
	}
	
	/**
	 * turn on/off testMode
	 *
	 * This method is meant primarily for testing and debugging during development
	 * @param struct $args
	 * @return boolean
	 * @access private
	 */	
	public function setTestMode($args)
	{
		$this->_setArgs($args);
		
		if(!$this->_isTestModePresent())
		{
			$this->errors[] = new IXR_ERROR(NO_TEST_MODE, NO_TEST_MODE_STR);
			return false;
		}
		else
		{
			// TODO: should probably validate that this is a bool or t/f string
			$this->testMode = $this->args[self::$testModeParamName];
			return true;			
		}
	}	
	
}
/**
 * Where the Server object is initialized
 * 
 * @see __construct()
 */
$XMLRPCServer = new TestlinkXMLRPCServer();
?>