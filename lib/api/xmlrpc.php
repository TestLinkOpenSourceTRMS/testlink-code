<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Filename $RCSfile: xmlrpc.php,v $
 *
 * @version $Revision: 1.18 $
 * @modified $Date: 2008/05/19 06:44:38 $ by $Author: franciscom $
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * 
 * The Testlink API makes it possible to interact with Testlink  
 * using external applications and services. This makes it possible to report test results 
 * directly from automation frameworks as well as other features.
 * 
 * See examples for additional detail
 * @example ../sample_clients/java/org/testlink/api/client/sample/TestlinkAPIXMLRPCClient.java java client sample
 * @example ../sample_clients/php/clientSample.php php client sample
 * @example ../sample_clients/ruby/clientSample.rb ruby client sample
 * @example ../sample_clients/python/clientSample.py python client sample
 * 
 *
 * rev :
 * 		  20080409 - azl - implement using the testsuitename param with the getTestCaseIDByName method
 *      20080309 - sbouffard - contribution - BUGID 1420: added getTestCasesForTestPlan (refactored by franciscom)
 *      20080307 - franciscom - now is possible to use test case external or internal ID
 *                              when calling reportTCResult()
 *      20080306 - franciscom - BUGID 1421
 *      20080305 - franciscom - minor code refactoring
 *      20080103 - franciscom - fixed minor bugs due to refactoring
 * 		  20080115 - havlatm - 0001296: API table refactoring 
 */

/** 
 * IXR is the class used for the XML-RPC server 
 */
require_once(dirname(__FILE__) . "/../../third_party/xml-rpc/class-IXR.php");
require_once("api.const.inc.php");
require_once("APIErrors.php");
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once(dirname(__FILE__) . "/../functions/common.php");
require_once(dirname(__FILE__) . "/../functions/testproject.class.php");
require_once(dirname(__FILE__) . "/../functions/testcase.class.php");
require_once(dirname(__FILE__) . "/../functions/testsuite.class.php");

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
	public static $version = "1.0 Beta 3";

  const   OFF=false;
  const   ON=true;
  const   BUILD_GUESS_DEFAULT_MODE=OFF;
	
	private $nodes_hierarchy_table="nodes_hierarchy";
  private $node_types_table="node_types";
  private $testplans_table="testplans";
  private $testprojects_table="testprojects";
  private $testsuites_table="testsuites";
  private $builds_table="builds";
  private $executions_table="executions";  
  private $testplan_tcversions_table="testplan_tcversions";
  
	
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
	
	private $tcaseMgr=null;
	
	/**#@+
	 * string for parameter names are all defined statically
	 * @static
 	 */
	public static $devKeyParamName = "devKey";
	public static $testCaseIDParamName = "testcaseid";
	public static $testCaseExternalIDParamName = "testcaseexternalid";
	public static $testPlanIDParamName = "testplanid";
	public static $testProjectIDParamName = "testprojectid";
	public static $testSuiteIDParamName = "testsuiteid";
	public static $statusParamName = "status";
	public static $buildIDParamName = "buildid";
	public static $noteParamName = "notes";
	public static $timeStampParamName = "timestamp";
	public static $guessParamName = "guess";
	public static $deepParamName = "deep";
	public static $testModeParamName = "testmode";
	public static $buildNameParamName = "buildname";
	public static $buildNotesParamName = "buildnotes";
	public static $automatedParamName = "automated";
	public static $testCaseNameParamName = "testcasename";
	public static $keywordIDParamName = "keywordid";
	public static $executedParamName = "executed";
	public static $assignedToParamName = "assignedto";
	public static $executeStatusParamName = "executestatus";
	public static $testSuiteNameParamName = "testsuitename";
	public static $testProjectNameParamName = "testprojectname";
	public static $testCasePrefixParamName = "testcaseprefix";

	
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
		$this->dbObj->db->SetFetchMode(ADODB_FETCH_ASSOC);
		$this->_connectToDB();

		$this->tcaseMgr=new testcase($this->dbObj);
		$this->tprojectMgr=new testproject($this->dbObj);

		$this->methods = array(
			'tl.reportTCResult' 			=> 'this:reportTCResult',
			'tl.getProjects'				=> 'this:getProjects',
			'tl.getProjectTestPlans'		=> 'this:getProjectTestPlans',
			'tl.createBuild'				=> 'this:createBuild',
			'tl.getTestSuitesForTestPlan' 	=> 'this:getTestSuitesForTestPlan',
			'tl.getTestCasesForTestSuite'	=> 'this:getTestCasesForTestSuite',
			'tl.getTestCasesForTestPlan' 	=> 'this:getTestCasesForTestPlan',
			'tl.getTestCaseIDByName'		=> 'this:getTestCaseIDByName',
			'tl.createTestCase'				=> 'this:createTestCase',
			'tl.createTestProject'				=> 'this:createTestProject',
			'tl.about'						=> 'this:about',
			'tl.setTestMode'				=> 'this:setTestMode',
			// ping is an alias for sayHello
			'tl.ping'						=> 'this:sayHello', 
			'tl.sayHello' 					=> 'this:sayHello',
			'tl.repeat'						=> 'this:repeat'
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
			$this->args[self::$buildIDParamName] = $buildID;			
			return true;
		}
		else
		{
			$this->errors[] = new IXR_Error(INVALID_BUILDID, INVALID_BUILDID_STR);
			return false;
		}	
	}
	
	
	/**
	 * Set test case internal ID
	 * 
	 * @param int $buildID
	 * @access private
	 */
	private function _setTestCaseID($tcaseID)
	{		
			$this->args[self::$testCaseIDParamName] = $tcaseID;			
	}
	
	/**
	 * Helper method set the buildID based on the tplanid
	 * 
	 * @return boolean
	 * @access private
	 */ 
	private function _setBuildIDFromTPID()
	{
		$result = $this->_setBuildID($this->getLatestBuildForTestPlan($this->args[self::$testPlanIDParamName]));
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
	 * Helper method to see if the testcasename provided is valid 
	 * 
	 * This is the only method that should be called directly to check the testcasename
	 * 	
	 * @return boolean
	 * @access private
	 */        
    protected function checkTestCaseName()
    {
        $status=true;
    	  if(!$this->_isTestCaseNamePresent())
    	  {
    	  	$this->errors[] = new IXR_Error(NO_TESTCASENAME, NO_TESTCASENAME_STR);
    	  	$status=false;
    	  }
    	  else
    	  {
    	      $testCaseName = $this->args[self::$testCaseNameParamName];
    	      if(!is_string($testCaseName))
    	      {
    	      	$this->errors[] = new IXR_Error(TESTCASENAME_NOT_STRING, TESTCASENAME_NOT_STRING_STR);
    	      	$status=false;
    	      }
    	  }
    	  return $status;
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
		    if( ($status=$this->_isStatusPresent()) )
		    {
		        if( !($status=$this->_isStatusValid($this->args[self::$statusParamName])))
		        {
		        	$this->errors[] = new IXR_Error(INVALID_STATUS, INVALID_STATUS_STR);
		        }    	
        }
        else
        {
            $this->errors[] = new IXR_Error(NO_STATUS, NO_STATUS_STR);
        }
        return $status;
    }       
    
	/**
	 * Helper method to see if the tcid provided is valid 
	 * 
	 * This is the only method that should be called directly to check the tcid
	 * 	
	 * @return boolean
	 * @access private
	 */    
    protected function checkTestCaseID()
    {
		    if(!$this->_isTestCaseIDPresent())
		    {
		    	$this->errors[] = new IXR_Error(NO_TCASEID, NO_TCASEID_STR);
		    	return false;
		    }
		    $tcaseid = $this->args[self::$testCaseIDParamName];
		    if(!$this->_isTestCaseIDValid($tcaseid))
		    {
		    	$this->errors[] = new IXR_Error(INVALID_TCASEID, INVALID_TCASEID_STR);
		    	return false;
		    }    	
		    return true;
    }
    
	/**
	 * Helper method to see if the tplanid provided is valid
	 * 
	 * This is the only method that should be called directly to check the tplanid
	 * 	
	 * @return boolean
	 * @access private
	 */    
    protected function checkTestPlanID()
    {
        $status=true;
    	  if(!$this->_isTestPlanIDPresent())
    	  {
    	  	$this->errors[] = new IXR_Error(NO_TPLANID, NO_TPLANID_STR);
    	  	$status=false;
    	  }
    	  else
    	  {    		
    	  	  // See if this TPID exists in the db
			      $tplanid = $this->dbObj->prepare_int($this->args[self::$testPlanIDParamName]);
          	$query = "SELECT id FROM {$this->testplans_table} WHERE id={$tplanid}";
          	$result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");         	
          	if(null == $result)
          	{
          		  $this->errors[] = new IXR_Error(INVALID_TPLANID, INVALID_TPLANID_STR);
          		  $status=false;        		
          	}
			      // tplanid exists and its valid
          	else
          	{
          		  // try to guess the buildid if it isn't already set
		      	    if(!$this->_isBuildIDPresent())
		      	    {
			      	      // can only set the build id for the test plan if guessing is enabled
    	  			      if(true == $this->checkGuess())
    	  			      {
    	  			      	$status = $this->_setBuildIDFromTPID();
    	  			      }
		      	    }
		      	    else
		      	      $status=true;
          	}    		    		    	
    	  }
    	  return $status;
    } 
    
	/**
	 * Helper method to see if the TestProjectID provided is valid
	 * 
	 * This is the only method that should be called directly to check the TestProjectID
	 * 	
	 * @return boolean
	 * @access private
	 */    
    protected function checkTestProjectID()
    {
    	if(!($status=$this->_isTestProjectIDPresent()))
    	{
    		  $this->errors[] = new IXR_Error(NO_TESTPROJECTID, NO_TESTPROJECTID_STR);
    	}
    	else
    	{    		
    		  // See if this Test Project ID exists in the db
			    $testprojectid = $this->dbObj->prepare_int($this->args[self::$testProjectIDParamName]);
        	$query = "SELECT id FROM {$this->testprojects_table} WHERE id={$testprojectid}";
        	$result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");         	
        	if(null == $result)
        	{
        		$this->errors[] = new IXR_Error(INVALID_TESTPROJECTID, INVALID_TESTPROJECTID_STR);
        		$status=false;        		
        	}
    	}
    	return $status;
    }  

	/**
	 * Helper method to see if the TestSuiteID provided is valid
	 * 
	 * This is the only method that should be called directly to check the TestSuiteID
	 * 	
	 * @return boolean
	 * @access private
	 */    
    protected function checkTestSuiteID()
    {
    	if(!($status=$this->_isTestSuiteIDPresent()))
    	{
    		$this->errors[] = new IXR_Error(NO_TESTSUITEID, NO_TESTSUITEID_STR);
    	}
    	else
    	{    		
    		  // See if this Test Suite ID exists in the db
			    $testsuiteid = $this->dbObj->prepare_int($this->args[self::$testSuiteIDParamName]);
        	$query = "SELECT id FROM {$this->testsuites_table} WHERE id={$testsuiteid}";
        	$result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");         	
        	if(null == $result)
        	{
        		$this->errors[] = new IXR_Error(INVALID_TESTSUITEID, INVALID_TESTSUITEID_STR);
        		$status=false;
        	}
    	}
      return $status;
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
    	return($this->_isGuessPresent() ? $this->args[self::$guessParamName] : self::BUILD_GUESS_DEFAULT_MODE);	
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
	   	$status=true;
	   	$try_again=false;
      
	   	if(!$this->_isBuildIDPresent())
	   	{
         $try_again=true;
			   if($this->_isBuildNamePresent())
			   {
			      $tplanMgr = new testplan($this->dbObj);  
            $buildInfo=$tplanMgr->get_build_by_name($this->args[self::$testPlanIDParamName],
                                                    trim($this->args[self::$buildNameParamName])); 
            if( !is_null($buildInfo) )
            {
                $this->args[self::$buildIDParamName]=$buildInfo['id'];
                $try_again=false;
            }
			   }
			}
	   	
	   	if($try_again)
	   	{
			    // this means we aren't supposed to guess the buildid
			    if(false == $this->checkGuess())   		
			    {
			    	  $this->errors[] = new IXR_Error(BUILDID_NOGUESS, BUILDID_NOGUESS_STR);
			    	  $this->errors[] = new IXR_Error(NO_BUILDID, NO_BUILDID_STR);				
    	    		$status=false;
			    }
			    else
			    {
			    	$setBuildResult = $this->_setBuildIDFromTPID();
			    	if(false == $setBuildResult)
			    	{
			    		$this->errors[] = new IXR_Error(NO_BUILD_FOR_TPLANID, NO_BUILD_FOR_TPLANID_STR);
			    		$status=false;
			    	}
			    }
	   	}
	   	
	   	if( $status)
	   	{
	   	    // actually check that the buildID thats set is valid
	   	    $buildID = $this->dbObj->prepare_int($this->args[self::$buildIDParamName]);
          $query = "SELECT id FROM {$this->builds_table} WHERE id={$buildID}";
          $result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");         	
          if( is_null($result) )
          {
			    	  $this->errors[] = new IXR_Error(INVALID_BUILDID, INVALID_BUILDID_STR);				
			    	  $status=false;
          }
          
      }
      
      return $status;
    }
     

    /**
	 * Helper method to see if a param is present
	 * 	
	 * @return boolean
	 * @access private
	 */  	     
	 private function _isParamPresent($pname)
	 {
		    return (isset($this->args[$pname]) ? true : false);
	 }

    /**
	 * Helper method to see if the status provided is valid 
	 * 	
	 * @return boolean
	 * @access private
	 */  	     
    private function _isStatusValid($status)
    {
    	return(in_array($status, self::$validStatusList));
    }           

    /**
	 * Helper method to see if a testcasename is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */          
	 private function _isTestCaseNamePresent()
	 {
		    return (isset($this->args[self::$testCaseNameParamName]) ? true : false);
	 }

    /**
	 * Helper method to see if a testcasename is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */          
	 private function _isTestCaseExternalIDPresent()
	 {
	      $status=isset($this->args[self::$testCaseExternalIDParamName]) ? true : false;
		    return $status;
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
    	return (isset($this->args[self::$buildIDParamName]) ? true : false);
    }
    
	/**
	 * Helper method to see if a buildname is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */    
    private function _isBuildNamePresent()
    {                                   
      $status=isset($this->args[self::$buildNameParamName]) ? true : false;
    	return $status;
    }
    
	/**
	 * Helper method to see if build notes are given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */    
    private function _isBuildNotePresent()
    {
    	return (isset($this->args[self::$buildNotesParamName]) ? true : false);
    }
    
	/**
	 * Helper method to see if testsuiteid is given as one of the arguments
	 * 	
	 * @return boolean
	 * @access private
	 */    
	private function _isTestSuiteIDPresent()
	{
		return (isset($this->args[self::$testSuiteIDParamName]) ? true : false);
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
	 * Helper method to see if a tplanid is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */    
    private function _isTestPlanIDPresent()
    {    	
    	return (isset($this->args[self::$testPlanIDParamName]) ? true : false);    	
    }

    /**
	 * Helper method to see if a TestProjectID is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */    
    private function _isTestProjectIDPresent()
    {    	
    	return (isset($this->args[self::$testProjectIDParamName]) ? true : false);    	
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
    private function _isTestCaseIDPresent()
    {
		return (isset($this->args[self::$testCaseIDParamName]) ? true : false);
    }  
    
	/**
	 * Helper method to see if the guess param is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */
    private function _isGuessPresent()
    {
      $status=isset($this->args[self::$guessParamName]) ? true : false;
		  return $status;
    }
    
    /**
	 * Helper method to see if the testsuitename param is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */
    private function _isTestSuiteNamePresent()
    {
		return (isset($this->args[self::$testSuiteNameParamName]) ? true : false);
    }    
    
	/**
	 * Helper method to see if the deep param is given as one of the arguments 
	 * 	
	 * @return boolean
	 * @access private
	 */
    private function _isDeepPresent()
    {
		return (isset($this->args[self::$deepParamName]) ? true : false);
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
	 * @param struct $tcaseid	 
	 * @return boolean
	 * @access private
	 */
    private function _isTestCaseIDValid($tcaseid)
    {
      
    	if(!is_int($tcaseid))
    	{
    		$this->errors[] = new IXR_Error(TCID_NOT_INTEGER, TCID_NOT_INTEGER_STR);
    		return false;
    	}
    	$tcaseid = $this->dbObj->prepare_int($tcaseid);
    	
    	// the tcid must be of type 'testcase' and show up in the nodes_hierarchy    	
		  $query = "SELECT nodes_hierarchy.id AS id " .
		           "FROM {$this->nodes_hierarchy_table}, {$this->node_types_table} " .
				       "WHERE nodes_hierarchy.id={$tcaseid} AND node_type_id=node_types.id " .
				       "AND node_types.description='testcase'";
		  
		  $result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");
		  
		  $status = is_null($result) ? false : true; 
  		return $status;
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
        	$this->userID = null;
        	$this->devKey = $this->dbObj->prepare_string($devKey);
        	$query = "SELECT id FROM users WHERE script_key='{$this->devKey}'";
        	$this->userID = $this->dbObj->fetchFirstRowSingleColumn($query, "id");         	
        	if(null == $this->userID)
        	{
        		return false;        		
        	}
        	else
        	{
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
	 * Helper method to See if the tcid and tplanid are valid together 
	 * 
	 * @return boolean
	 * @access private
	 */            
    private function _checkTCIDAndTPIDValid()
    {  	
    	$tplanid = $this->args[self::$testPlanIDParamName];
    	$tcaseid = $this->args[self::$testCaseIDParamName];
    	
    	// get all versions of the testcase in the nodes_hierarchy    	
    	$query = " SELECT nodes_hierarchy.id AS id " .
    	         " FROM {$this->nodes_hierarchy_table}, {$this->node_types_table} " .
    			     " WHERE nodes_hierarchy.parent_id={$tcaseid} AND node_type_id=node_types.id " .
    			     " AND node_types.description='testcase_version'";
    	$result = $this->dbObj->fetchColumnsIntoArray($query, "id");

    	// make sure we don't have an empty array
    	if(count($result) > 0)
    	{
	    	// determine which version if any is part of the test plan 
	    	$versionQuery = "SELECT tcversion_id " .
	    	                " FROM {$this->testplan_tcversions_table} WHERE tcversion_id IN(" . 
	    				          implode(",", $result) . ") AND testplan_id=$tplanid";
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
    		$this->errors[] = new IXR_Error(INVALID_TCASEID, INVALID_TCASEID_STR);
    		return false;	
    	}    	
    }

	/**
	 * Run all the necessary checks to see if the reportTCResult request is valid
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

		// if(!$this->checkTestCaseID())
		// {
		// 	return false;
		// }					
		   
		if(!$this->checkTestCaseIdentity())
		{
			return false;
		}					
		   
		   
		if(!$this->checkTestPlanID())
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
	 * Run all the necessary checks to see if the createBuild request is valid
	 *  
	 * @return boolean
	 * @access private
	 */
	private function _checkCreateBuildRequest()
	{		
		if(!$this->authenticate())
		{
			return false;
		}
		if(!$this->checkTestPlanID())
		{
			return false;
		}		
		if(!$this->_isBuildNamePresent())
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
	 * Run all the necessary checks to see if the getProjectTestPlans request is valid
	 *  
	 * @return boolean
	 * @access private
	 */	
	private function _checkGetProjectTestPlansRequest()
	{
		if(!$this->authenticate())
		{
			return false;			
		}
		if(!$this->checkTestProjectID())
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * Run all the necessary checks to see if the getTestCaseByName request is valid
	 *  
	 * @return boolean
	 * @access private
	 */	
	private function _checkGetTestCaseByIDNameRequest()
	{
		if(!$this->authenticate())
		{
			return false;			
		}
		if(!$this->checkTestCaseName())
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * Run all the necessary checks to see if ...
	 *  
	 * @return boolean
	 * @access private
	 */
	private function _checkGetTestCasesForTestSuiteRequest()
	{
		if(!$this->authenticate())
		{
			return false;			
		}
		if(!$this->checkTestSuiteID())
		{
			return false;
		}
		else
		{
			return true;
		}
	}

 	/**
	 * Gets the latest build by date for a specific test plan 
	 *
	 * @param int $tplanid
	 * @return int
	 * @access private
	 */		
	protected function getLatestBuildForTestPlan($tplan_id)
	{     	                		
    	$devKey = $this->dbObj->prepare_int($tplan_id);
    	$query = "SELECT max(id) AS id FROM {$this->builds_table} WHERE testplan_id={$tplan_id}";
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
		$build_id = 	$this->args[self::$buildIDParamName];
		$tester_id = 	$this->userID;
		$status = 		$this->args[self::$statusParamName];
		$testplan_id =	$this->args[self::$testPlanIDParamName];
		$tcversion_id =	$this->tcVersionID;
		$db_now=$this->dbObj->db_now();
		
		$notes='';
    $notes_field="";
    $notes_value="";  

		if($this->_isNotePresent())
		{
			$notes = $this->dbObj->prepare_string($this->args[self::$noteParamName]);
		}
		
		if( strlen(trim($notes)) > 0 )
		{
		    $notes_field=",notes";
		    $notes_value=", '{$notes}'";  
		}
		
		$execution_type = constant("TESTCASE_EXECUTION_TYPE_AUTO");

		$query = "INSERT INTO {$this->executions_table} " .
		         "(build_id, tester_id, execution_ts, status, testplan_id, tcversion_id, " .
		         " execution_type {$notes_field} ) " .
				     "VALUES({$build_id},{$tester_id},{$db_now},'{$status}',{$testplan_id}," .
				     "{$tcversion_id},{$execution_type} {$notes_value})";

		$this->dbObj->exec_query($query);
		return $this->dbObj->insert_id();		
	}
	
 	/**
	 * Adds the build to the database 
	 *
	 * @return int
	 * @access private
	 */			
	private function _insertBuildToDB()
	{		
		$name = 		$this->args[self::$buildNameParamName];		
		$testplan_id =	$this->args[self::$testPlanIDParamName];		
		if($this->_isBuildNotePresent())
		{			
			$notes = $this->dbObj->prepare_string($this->args[self::$buildNotesParamName]);
		}
		else
		{
			$notes = "";
		}		
		// TODO: set the active and is_open flags		
		
		$query = "INSERT INTO {$this->builds_table} (testplan_id, name, notes) " .
				     "VALUES(" . $testplan_id . "," . "'" . $name . "'," .	"'" . $notes . "')";
				
		$this->dbObj->exec_query($query);
		return $this->dbObj->insert_id();		
	}	

	/**
	 * Performs a deep search for test cases within a test suite
	 * 
	 * Uses testsuite->get_testcases_deep method
	 * 
	 * @param int $testSuiteID
	 * @return struct
	 * @access private
	 */
	private function _getDeepTestCasesForSuite($testSuiteID)
	{		
		$testSuiteObj = new testsuite($this->dbObj);
		$result = $testSuiteObj->get_testcases_deep($testSuiteID);

		// these are the keys we want (everything but "node_table")
		$wantedKeysArray = array(
						"id" => null, 
						"name" => null, 
						"parent_id" => null,
						"node_type_id" => null,
						"node_order" => null
					);

		$filteredResult = array();
		
		foreach($result as $row)
		{
			// perform the filter based on array key comparison					
			$filteredResult[] = array_intersect_key($row, $wantedKeysArray);			
		}
		
		return $filteredResult;
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
		$this->_setArgs($args);
		$str = " Testlink API Version: " . self::$version . " written by Asiel Brumfield\n" .
		       " contribution by TestLink development Team";
		return $str;				
	}
	
	/**
	 * Creates a new build for a specific test plan
	 *
	 * @param struct $args
	 * @param string $args["devKey"]
	 * @param int $args["tplanid"]
	 * @param string $args["buildname"];
	 * @return mixed $resultInfo
	 * 				
	 * @access public
	 */		
	public function createBuild($args)
	{
		// TODO: look into switching to use $testplan->create_build method
		$this->_setArgs($args);
		if($this->_checkCreateBuildRequest($this->args))
		{
			$insertID = $this->_insertBuildToDB();			
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
	 * Gets a list of all projects
	 * @param struct $args
	 * @param string $args["devKey"]
	 * @return mixed $resultInfo			
	 * @access public
	 */		
	public function getProjects($args)
	{
		$this->_setArgs($args);		
		if($this->authenticate())
		{
			$testProjectObj = new testproject($this->dbObj);
			return $testProjectObj->get_all();	
		}
		else
		{
			return $this->errors;
		}
		
		// query that only gets active (the testproject method gets everything)
		//$query = "SELECT nodes_hierarchy.id AS id, nodes_hierarchy.name AS name, testprojects.notes AS " .
		//		"notes FROM `testprojects`, `nodes_hierarchy`, node_types WHERE " .
		//		"nodes_hierarchy.node_type_id=node_types.id AND node_types.description='testproject' " .
		//		"AND testprojects.active=1 AND testprojects.id=nodes_hierarchy.id";		
	}
	
	/**
	 * Gets a list of test plans within a project
	 *
	 * @param struct $args
	 * @param string $args["devKey"]
	 * @param int $args["testprojectid"]
	 * @return mixed $resultInfo
	 * 				
	 * @access public
	 */		
	public function getProjectTestPlans($args)
	{
		$this->_setArgs($args);
		// check the tplanid
		if($this->_checkGetProjectTestPlansRequest())
		{
			$testProjectObj = new testproject($this->dbObj);
			$testProjectID = $this->args[self::$testProjectIDParamName];
			return $testProjectObj->get_all_testplans($testProjectID);	
		}
		else
		{
			return $this->errors;
		} 
	}
	
	// 20080518 - franciscom
	public function createTestProject($args)
	{
	    $this->_setArgs($args);
	    $checkRequestMethod='_check' . ucfirst(__FUNCTION__) . 'Request';
	
	    if( $this->$checkRequestMethod() )
	    {
	        return true;
	    }
	    else
	    {
	        return $this->errors;
	    }    
      
	}
	
  // 20080518 - franciscom
  private function _checkCreateTestProjectRequest()
	{
      $status_ok=$this->authenticate();
      $name=$this->args[self::$testProjectNameParamName];
      $prefix=$this->args[self::$testCasePrefixParamName];
      
      if( $status_ok )
      {
          $check_op=$this->tprojectMgr->checkNameSintax($name);
          $status_ok=$check_op['status_ok'];     
          if(!$status_ok)
          {     
	           $this->errors[] = new IXR_Error(TESTPROJECTNAME_SINTAX_ERROR, $check_op['msg']);
          }
      }
      
      if( $status_ok ) 
      {
          $check_op=$this->tprojectMgr->checkNameExistence($name);
          $status_ok=$check_op['status_ok'];     
          if(!$status_ok)
          {     
	           $this->errors[] = new IXR_Error(TESTPROJECTNAME_EXISTS, $check_op['msg']);
          }
      }

      if( $status_ok ) 
      {
          $status_ok=!empty($prefix);
          if(!$status_ok)
          {     
	           $this->errors[] = new IXR_Error(TESTPROJECT_TESTCASEPREFIX_IS_EMPTY, $check_op['msg']);
          }
      }

       
  	  return $status_ok;
	}

	
	
	/**
	 * List test suites within a test plan
	 * 
	 * @param struct $args
	 * @param string $args["devKey"]
	 * @param int $args["testplanid"]
	 * @return mixed $resultInfo
	 */
	 public function getTestSuitesForTestPlan($args)
	 {
	 	// TODO: Implement
	 }

	/**
	 * List test cases within a test suite
	 * 
	 * By default test cases that are contained within child suites 
	 * will be returned. Set the deep flag to false if you only want
	 * test cases in the test suite provided and no child test cases.
	 *  
	 * @param struct $args
	 * @param string $args["devKey"]
	 * @param int $args["testsuiteid"]
	 * @param boolean $args["deep"] - optional (default is true)
	 * @return mixed $resultInfo
	 */
	 public function getTestCasesForTestSuite($args)
	 {
		$this->_setArgs($args);
		if($this->_checkGetTestCasesForTestSuiteRequest())
		{		
			$testSuiteID = $this->args[self::$testSuiteIDParamName];
				
			if(!$this->_isDeepPresent())
			{
				// go deep by default (return test cases in child suites)
				return $this->_getDeepTestCasesForSuite($testSuiteID);
			}	
			// deep has been set
			else
			{
				if(false == $this->args[self::$deepParamName])
				{					
					// TODO: add method with this functionality to testsuite.class.php								
					$query=	"SELECT nodes_hierarchy.*" .
		              "FROM {$this->nodes_hierarchy_table}, {$this->node_types_table} " .
							"WHERE nodes_hierarchy.parent_id={$testSuiteID} AND " .
							"node_type_id=node_types.id AND " .
							"node_types.description='testcase' ORDER BY node_order,id";

					$resultMap = $this->dbObj->fetchArrayRowsIntoMap($query, "id");
					// reformat the result to look just like testsuite->get_testcases_deep() 
					// with node_table filtered
					$newResult = array();
					foreach($resultMap as $result)
					{
						foreach($result as $item)
						{
							$newResult[] = $item;
						}
					}
					return $newResult;
				}
				else
				{
					return $this->_getDeepTestCasesForSuite($testSuiteID);
				}
			}
		}
		else
		{
			return $this->errors;
		}
	 }

	/**
	 * Find a test case by its name
	 * 
	 * <b>Searching is case sensitive.</b> The test case will only be returned if there is a definite match.
	 * If possible also pass the string for the test suite name. No results will be returned if there
	 * are test cases with the same name that match the criteria provided.  
	 * 
	 * @param struct $args
	 * @param string $args["devKey"]
	 * @param string $args["testcasename"]
	 * @param string $args["testsuitename"] - optional
	 * @return mixed $resultInfo
	 */
	 public function getTestCaseIDByName($args)
	 {
		$this->_setArgs($args);		
		if($this->_checkGetTestCaseByIDNameRequest())
		{			
			$testCaseName = $this->args[self::$testCaseNameParamName];

	 		$testCaseObj = new testcase($this->dbObj);
	 		// see if we are using the testsuitename param
	 		if($this->_isTestSuiteNamePresent())
	 		{
				$testSuiteName = $this->args[self::$testSuiteNameParamName];
		 		$result = $testCaseObj->get_by_name($testCaseName, $testSuiteName);
			}
			else
			{
		 		$result = $testCaseObj->get_by_name($testCaseName);
	 		}
	 			 		
			if(0 == sizeof($result))
			{
				$this->errors[] = new IXR_ERROR(NO_TESTCASE_BY_THIS_NAME, NO_TESTCASE_BY_THIS_NAME_STR);
				return $this->errors;
			}
			else
			{
				return $result;
			}		 			 	
		}
		else
		{
			return $this->errors;
		}
	 }
	 
	 /**
	  * Create a new test case 
	  */
	 public function createTestCase($args)
	 {
	 	// should be able to use this function in the testcase class
		//	 	function create_tcase_only($parent_id,$name,$order=TC_DEFAULT_ORDER,$id=TC_AUTOMATIC_ID,
		//                           $check_duplicate_name=0,
		//                           $action_on_duplicate_name='generate_new')
	 }	
	 
	 /**
	  * Update an existing test case
	  */
	 public function updateTestCase($args)
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
	 * @param int $args["tplanid"] 
   * @param string $args["status"] - status is {@link $validStatusList}
   * @param int $args["buildid"] - optional
   * @param string $args["notes"] - optional
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
	
	
	/**
	 * Helper method to see if the testcase identity provided is valid 
	 * Identity can be specified in one of these modes:
	 *
	 * test case internal id
	 * test case external id  (PREFIX-NNNN) 
	 * 
	 * This is the only method that should be called directly to check test case identoty
	 * 	
	 * If everything OK, test case internal ID is setted.
	 *
	 * @return boolean
	 * @access private
	 */    
    protected function checkTestCaseIdentity()
    {
        $try_again=false;
        $status=true;
        $tcaseID=0;
        $my_errors=array();

		    if($this->_isTestCaseIDPresent())
		    {
		      $tcaseID = $this->args[self::$testCaseIDParamName];
		    }
		    else
		    {  
		    	$my_errors[] = new IXR_Error(NO_TCASEID, NO_TCASEID_STR);
		    	$try_again=true;
		    	$status=false;
		    }

        if($try_again)
        {
            if($this->_isTestCaseExternalIDPresent())
		        {
		            $tcaseExternalID = $this->args[self::$testCaseExternalIDParamName]; 
		            $tcaseCfg=config_get('testcase_cfg');
		            $glueCharacter=$tcaseCfg->glue_character;
		            $tcaseID=$this->tcaseMgr->getInternalID($tcaseExternalID,$glueCharacter);
                $status = $tcaseID > 0 ? true : false;
                if( !status )
                {
                    $my_errors[] = new IXR_Error(INVALID_TESTCASE_EXTERNAL_ID, 
                                                 INVALID_TESTCASE_EXTERNAL_ID_STR);                  
                } 
		        } 
		        else
		        {  
		        	$my_errors[] = new IXR_Error(NO_TCASEEXTERNALID, NO_TCASEEXTERNALID_STR);
		        	$status=false;
		        }
        }		    
	    
		    if( $status )
		    {
		
		        $my_errors=null;
		        if($this->_isTestCaseIDValid($tcaseID))
		        {
		            $this->_setTestCaseID($tcaseID);  
		        }  
		        else
		        {  
		        	  $this->errors[] = new IXR_Error(INVALID_TCASEID, INVALID_TCASEID_STR);
		        	  $status=false;
		        }    	
		    }
		    else
		    {
		        foreach($my_errors as $error_msg)
		        {
		            $this->errors[] = $error_msg; 
		        }    
		    }
		    return $status;
    }

	 /**
	 * getTestCasesForTestPlan
	 * List test cases linked to a test plan
	 * 
	 * @param struct $args
	 * @param string $args["devKey"]
	 * @param int $args["testplanid"]
	 * @param int $args["testcaseid"] - optional
	 * @param int $args["buildid"] - optional
	 * @param int $args["keywordid"] - optional
	 * @param boolean $args["executed"] - optional
	 * @param int $args["$assignedto"] - optional
	 * @param string $args["executestatus"] - optional
	 * @return mixed $resultInfo
	 */
	 public function getTestCasesForTestPlan($args)
	 {

    // Optional parameters
    $opt=array(self::$testCaseIDParamName => null,
               self::$buildIDParamName => null,
               self::$keywordIDParamName => null,
               self::$executedParamName => null,
               self::$assignedToParamName => null,
               self::$executeStatusParamName => null,);
	 	
   	$this->_setArgs($args);
   	$this->errors[]=$opt;
		//echo "<pre>debug 20080310 - \ - " . __FUNCTION__ . " --- "; print_r($this); echo "</pre>";
		//die();
		
		
		// Test Case ID, Build ID are checked if present
		if(!$this->_checkGetTestCasesForTestPlanRequest())
		{
			return $this->errors;
		}
		
		$tplanid=$this->args[self::$testPlanIDParamName];
		foreach($opt as $key => $value)
		{
		    if($this->_isParamPresent($key))
		    {
		        $opt[$key]=$this->args[$key];      
		    }   
		}
		$testplan = new testplan($this->dbObj);
		$recordset=$testplan->get_linked_tcversions($tplanid,
		                                            $opt[self::$testCaseIDParamName],
                                                $opt[self::$keywordIDParamName],
		                                            $opt[self::$executedParamName],
                                                $opt[self::$assignedToParamName],
                                                $opt[self::$executeStatusParamName],
	 	                                            $opt[self::$buildIDParamName]);
		return $recordset;
	 }


	/**
	 * Run all the necessary checks to see if ...
	 *  
	 * @return boolean
	 * @access private
	 */
	private function _checkGetTestCasesForTestPlanRequest()
	{
		$status=$this->authenticate();
		
		if($status)
		{
	    $status &=$this->checkTestPlanID();
	    
	    if($status && $this->_isTestCaseIDPresent())
	    {
	        $status &=$this->_checkTCIDAndTPIDValid();
	    }
	    if($status && $this->_isBuildIDPresent())  
	    {
	        $status &=$this->checkBuildID();
	    }
		}
		return $status;
	}



} // class end

/**
 * Where the Server object is initialized
 * 
 * @see __construct()
 */
$XMLRPCServer = new TestlinkXMLRPCServer();
?>