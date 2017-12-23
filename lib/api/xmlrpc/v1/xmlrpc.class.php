<?php
/**
 * TestLink Open Source Project  http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * @filesource  xmlrpc.class.php
 *
 * @author    Asiel Brumfield <asielb@users.sourceforge.net>
 * @package   TestlinkAPI
 * 
 * Testlink API makes it possible to interact with Testlink  
 * using external applications and services. This makes it possible to report test results 
 * directly from automation frameworks as well as other features.
 * 
 * See examples for additional detail
 * @example sample_clients/java/org/testlink/api/client/sample/TestlinkAPIXMLRPCClient.java java client sample
 * @example sample_clients/php/clientSample.php php client sample
 * @example sample_clients/ruby/clientSample.rb ruby client sample
 * @example sample_clients/python/clientSample.py python client sample
 * 
 */

/** 
 * IXR is the class used for the XML-RPC server 
 */
define ("TL_APICALL",'XML-RPC');

require_once("../../../../config.inc.php");
require_once("common.php");
require_once("xml-rpc/class-IXR.php");
require_once("api.const.inc.php");
require_once("APIErrors.php");

/**
 * The entry class for serving XML-RPC Requests
 * 
 * See examples for additional detail
 * @example sample_clients/java/org/testlink/api/client/sample/TestlinkAPIXMLRPCClient.java java client sample
 * @example sample_clients/php/clientSample.php php client sample
 * @example sample_clients/ruby/clientSample.rb ruby client sample
 * @example sample_clients/python/clientSample.py python client sample
 * 
 * @author    Asiel Brumfield <asielb@users.sourceforge.net>
 * @package   TestlinkAPI 
 * @since     Class available since Release 1.8.0
 */
class TestlinkXMLRPCServer extends IXR_Server
{
  public static $version = "1.1";
 
    
  const OFF=false;
  const ON=true;
  const BUILD_GUESS_DEFAULT_MODE=OFF;
  const SET_ERROR=true;
  const CHECK_PUBLIC_PRIVATE_ATTR=true;
 
  /**
   * The DB object used throughout the class
   * 
   * @access protected
   */
  protected $dbObj = null;
  protected $tables = null;

  protected $tcaseMgr =  null;
  protected $tprojectMgr = null;
  protected $tplanMgr = null;
  protected $tplanMetricsMgr = null;
  protected $reqSpecMgr = null;
  protected $reqMgr = null;
  protected $platformMgr = null;
  protected $itsMgr = null;


  /** Whether the server will run in a testing mode */
  protected $testMode = false;

  /** userID associated with the devKey provided */
  protected $userID = null;
  
  /** UserObject associated with the userID */
  protected $user = null;

  /** array where all the args are stored for requests */
  protected $args = null;  

  /** array where error codes and messages are stored */
  protected $errors = array();

  /** The api key being used to make a request */
  protected $devKey = null;
  
  /** boolean to allow a method to invoke another method and avoid double auth */
  protected $authenticated = false;

  /** The version of a test case that is being used */
  /** This value is setted in following method:     */
  /** _checkTCIDAndTPIDValid()                      */
  protected $tcVersionID = null;
  protected $versionNumber = null;

  /** Mapping bewteen external & internal test case ID */
  protected $tcaseE2I = null;

  /** needed in order to manage logs */
  protected $tlLogger = null;
  
  
  /**#@+
   * string for parameter names are all defined statically
   * PLEASE define in DICTIONARY ORDER
   * @static
    */

  public static $actionOnDuplicatedNameParamName = "actiononduplicatedname";
  public static $actionParamName = "action";
  public static $activeParamName = "active";
  public static $assignedToParamName = "assignedto";
  public static $automatedParamName = "automated";
  public static $authorLoginParamName = "authorlogin";

  public static $bugIDParamName = "bugid";    
  public static $buildIDParamName = "buildid";
  public static $buildNameParamName = "buildname";
  public static $buildNotesParamName = "buildnotes";

  public static $checkDuplicatedNameParamName = "checkduplicatedname";
  public static $contentParamName = "content";
  public static $customFieldNameParamName = "customfieldname";
  public static $customFieldsParamName = "customfields";

  public static $deepParamName = "deep";
  public static $descriptionParamName = "description";
  public static $detailsParamName = "details";
  public static $devKeyParamName = "devKey";

  public static $executionIDParamName = "executionid";
  public static $executionOrderParamName = "executionorder";
  public static $executedParamName = "executed";
  public static $executeStatusParamName = "executestatus";
  public static $executionTypeParamName = "executiontype";
  public static $expectedResultsParamName = "expectedresults";

  public static $fileNameParamName = "filename";
  public static $fileTypeParamName = "filetype";
  public static $foreignKeyIdParamName = "fkid";
  public static $foreignKeyTableNameParamName = "fktable";

  public static $guessParamName = "guess";
  public static $getStepsInfoParamName = "getstepsinfo";
  public static $getKeywordsParamName = "getkeywords";
  
  public static $importanceParamName = "importance";
  public static $internalIDParamName = "internalid";
  public static $keywordIDParamName = "keywordid";
  public static $keywordNameParamName = "keywords";
  
  public static $linkIDParamName = "linkid";

  public static $nodeIDParamName = "nodeid";
  public static $nodeTypeParamName = "nodetype";
  public static $noteParamName = "notes";

  public static $openParamName = "open";  
  public static $optionsParamName = "options";
  public static $orderParamName = "order";
  public static $overwriteParamName = "overwrite";
  public static $parentIDParamName = "parentid";    
  public static $platformNameParamName = "platformname";
  public static $platformIDParamName = "platformid";
  public static $preconditionsParamName = "preconditions";
  public static $publicParamName = "public";

  public static $releaseDateParamName = "releasedate";
  public static $requirementsParamName = "requirements";
  public static $requirementIDParamName = "requirementid";
  public static $requirementDocIDParamName = "requirementdocid";
  public static $reqSpecIDParamName = "reqspecid";
  
  public static $scopeParamName = "scope";
  public static $summaryParamName = "summary";
  public static $statusParamName = "status";
  public static $stepsParamName = "steps";

  public static $testCaseIDParamName = "testcaseid";
  public static $testCaseExternalIDParamName = "testcaseexternalid";
  public static $testCaseNameParamName = "testcasename";
  public static $testCasePathNameParamName = "testcasepathname";
  public static $testCasePrefixParamName = "testcaseprefix";
  public static $testModeParamName = "testmode";
  public static $testPlanIDParamName = "testplanid";
  public static $testPlanNameParamName = "testplanname";
  public static $testProjectIDParamName = "testprojectid";
  public static $testProjectNameParamName = "testprojectname";
  public static $testSuiteIDParamName = "testsuiteid";
  public static $testSuiteNameParamName = "testsuitename";
  public static $timeStampParamName = "timestamp";
  public static $titleParamName = "title";


  public static $urgencyParamName = "urgency";
  public static $userParamName = "user";
  public static $userIDParamName = "userid";
  public static $versionNumberParamName = "version";
  public static $estimatedExecDurationParamName = "estimatedexecduration";
  public static $executionDurationParamName = "execduration";

  public static $prefixParamName = "prefix";
  public static $testCaseVersionIDParamName = "tcversionid";
  
  public static $itsNameParamName = "itsname";
  public static $itsEnabledParamName = "itsenabled";
  public static $copyTestersFromBuildParamName = "copytestersfrombuild";

  /**#@-*/
  
  /**
   * An array containing strings for valid statuses 
   * Will be initialized using user configuration via config_get()
   */
  public $statusCode;
  public $codeStatus;
  
  
  /**
   * Constructor sets up the IXR_Server and db connection
   */
  public function __construct($callbacks = array())
  {    
    $this->dbObj = new database(DB_TYPE);
    $this->dbObj->db->SetFetchMode(ADODB_FETCH_ASSOC);
    $this->_connectToDB();
    
    global $g_tlLogger;
    $this->tlLogger = &$g_tlLogger;
    $this->tlLogger->setDB($this->dbObj);
 
    // This close the default transaction that is started
    // when logger.class.php is included.    
    $this->tlLogger->endTransaction();

    $this->tcaseMgr = new testcase($this->dbObj);
    $this->tprojectMgr = new testproject($this->dbObj);
    $this->tplanMgr = new testplan($this->dbObj);
    $this->tplanMetricsMgr = new tlTestPlanMetrics($this->dbObj);
    $this->reqSpecMgr = new requirement_spec_mgr($this->dbObj);
    $this->reqMgr = new requirement_mgr($this->dbObj);
    
    $this->tprojectMgr->setAuditEventSource('API-XMLRPC');
      

    $this->tables = $this->tcaseMgr->getDBTables();
    
    $resultsCfg = config_get('results');
    foreach($resultsCfg['status_label_for_exec_ui'] as $key => $label )
    {
      $this->statusCode[$key]=$resultsCfg['status_code'][$key];  
    }
    
    if( isset($this->statusCode['not_run']) )
    {
      unset($this->statusCode['not_run']);  
    }   
    $this->codeStatus=array_flip($this->statusCode);
      
    $this->initMethodYellowPages();
    $this->methods += $callbacks;

    $this->IXR_Server($this->methods);    
  }  
  
  /**
   *
   */
  protected function _setArgs($args,$opt=null)
  {
    // TODO: should escape args
    $this->args = $args;

    if( isset($this->args[self::$testProjectNameParamName]) && 
        !isset($this->args[self::$testProjectIDParamName])
      )
    {
       $tprojMgr = new testproject($this->dbObj);
       $name = trim($this->args[self::$testProjectNameParamName]);
       $info = current($this->tprojectMgr->get_by_name($name));
       $this->args[self::$testProjectIDParamName] = $info['id'];
    }  
  }
  
  /**
   * Set the BuildID from one place
   * 
   * @param int $buildID
   * @access protected
   */
  protected function _setBuildID($buildID)
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
   * @param int $tcaseID
   * @access protected
   */
  protected function _setTestCaseID($tcaseID)
  {    
    $this->args[self::$testCaseIDParamName] = $tcaseID;      
  }
  
  /**
   * Set Build Id to latest build id (if test plan has builds)
   * 
   * @return boolean
   * @access protected
   */ 
  protected function _setBuildID2Latest()
  {
    $tplan_id=$this->args[self::$testPlanIDParamName];
    $maxbuildid = $this->tplanMgr->get_max_build_id($tplan_id);
    $status_ok=($maxbuildid >0);
    if($status_ok)
    {
      $this->_setBuildID($maxbuildid);  
    } 
    return $status_ok;
  }  
    
  /**
   * connect to the db and set up the db object 
   *
   * @access protected
   *
   * @internal revisions:
   *  20100731 - asimon - BUGID 3644 (additional fix for BUGID 2607)
   *  20100711 - franciscom - BUGID 2607 - UTF8 settings for MySQL
   */    
  protected function _connectToDB()
  {
    if(true == $this->testMode)
    {
        $this->dbObj->connect(TEST_DSN, TEST_DB_HOST, TEST_DB_USER, TEST_DB_PASS, TEST_DB_NAME);
    }
    else
    {
        $this->dbObj->connect(DSN, DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }
    // asimon - BUGID 3644 & 2607 - $charSet was undefined here
    $charSet = config_get('charset');
    if((DB_TYPE == 'mysql') && ($charSet == 'UTF-8'))
    {
        $this->dbObj->exec_query("SET CHARACTER SET utf8");
        $this->dbObj->exec_query("SET collation_connection = 'utf8_general_ci'");
    }
  }

  /**
   * authenticates a user based on the devKey provided 
   * 
   * This is the only method that should really be used directly to authenticate
   *
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */
    protected function authenticate($messagePrefix='')
    {   
      // check that the key was given as part of the args
      if(!$this->_isDevKeyPresent())
      {
        $this->errors[] = new IXR_ERROR(NO_DEV_KEY, $messagePrefix . NO_DEV_KEY_STR);
        $this->authenticated = false;
        return false;
      }
      else
      {
        $this->devKey = $this->args[self::$devKeyParamName];
      }
      
      // make sure the key we have is valid
      if(!$this->_isDevKeyValid($this->devKey))
      {
        $this->errors[] = new IXR_Error(INVALID_AUTH, $messagePrefix . INVALID_AUTH_STR);
        $this->authenticated = false;
        return false;      
      }
      else
      {
        // Load User
        $this->user = tlUser::getByID($this->dbObj,$this->userID);  
        $this->authenticated = true; 

        $this->tlLogger->startTransaction('DEFAULT',null,$this->userID);
        return true;
      }        
    }
    
    
  /**
   * checks if a user has requested right on test project, test plan pair.
   * 
   * @param string $rightToCheck  one of the rights defined in rights table
   * @param boolean $checkPublicPrivateAttr (optional)
   * @param map $context (optional)
   *            keys testprojectid,testplanid  (both are also optional)
   *
   * @return boolean
   * @access protected
   *
   */
  protected function userHasRight($rightToCheck,$checkPublicPrivateAttr=false,
                                  $context=null)
  {
    $status_ok = true;
    $tprojectid = intval(isset($context[self::$testProjectIDParamName]) ? 
                  $context[self::$testProjectIDParamName] : 0);

    if($tprojectid == 0 && isset($this->args[self::$testProjectIDParamName]))
    {
      $tprojectid = $this->args[self::$testProjectIDParamName];
    }  

    if(isset($context[self::$testPlanIDParamName]))
    {
      $tplanid = $context[self::$testPlanIDParamName];
    } 
    else
    {
      $tplanid = isset($this->args[self::$testPlanIDParamName]) ? 
                 $this->args[self::$testPlanIDParamName] : null;
    } 

    $tprojectid = intval($tprojectid);
    $tplanid = !is_null($tplanid) ? intval($tplanid) : -1;

    if( $tprojectid <= 0 && $tplanid > 0 )
    {
      // get test project from test plan
      $ox = array('output' => 'minimun'); 
      $dummy = $this->tplanMgr->get_by_id($tplanid,$ox);  
      $tprojectid = intval($dummy['tproject_id']);
    }

    if(!$this->user->hasRight($this->dbObj,$rightToCheck,
                              $tprojectid, $tplanid,$checkPublicPrivateAttr))
    {
      $status_ok = false;
      $msg = sprintf(INSUFFICIENT_RIGHTS_STR,$this->user->login,
                     $rightToCheck,$tprojectid,$tplanid);
      $this->errors[] = new IXR_Error(INSUFFICIENT_RIGHTS, $msg);
    }

    if( isset($context['updaterID']) )
    {
      $updUser = tlUser::getByID($this->dbObj,intval($context['updaterID']));
      
      
      $sk = $updUser->hasRight($this->dbObj,$rightToCheck,
                               $tprojectid, $tplanid,$checkPublicPrivateAttr);
      if( !$sk )
      {
        $status_ok = false;
        $msg = sprintf(UPDATER_INSUFFICIENT_RIGHTS_STR,$updUser->login,
                       $rightToCheck,$tprojectid,$tplanid);
        $this->errors[] = new IXR_Error(UPDATER_INSUFFICIENT_RIGHTS, $msg);
      } 
    }

    return $status_ok;
  }

  /**
   * Helper method to see if the testcasename provided is valid 
   * 
   * This is the only method that should be called directly to check the testcasename
   *   
   * @return boolean
   * @access protected
   */        
    protected function checkTestCaseName()
    {
        $status = true;
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
   * @access protected
   */    
    protected function checkStatus()
    {
      if( ($status=$this->_isStatusPresent()) )
      {
        if( !($status=$this->_isStatusValid($this->args[self::$statusParamName])))
        {
          $msg = sprintf(INVALID_STATUS_STR,$this->args[self::$statusParamName]);
          $this->errors[] = new IXR_Error(INVALID_STATUS, $msg);
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
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */    
    protected function checkTestCaseID($messagePrefix='')
    {
      $msg = $messagePrefix;
      $status_ok=$this->_isTestCaseIDPresent();
      if( $status_ok)
      {
        $tcaseid = $this->args[self::$testCaseIDParamName];
        if(!$this->_isTestCaseIDValid($tcaseid))
        {
          $this->errors[] = new IXR_Error(INVALID_TCASEID, $msg . INVALID_TCASEID_STR);
          $status_ok=false;
        }
      }      
      else
      {
        $this->errors[] = new IXR_Error(NO_TCASEID, $msg . NO_TCASEID_STR);
      }
      return $status_ok;
    }
    
  /**
   * Helper method to see if the tplanid provided is valid
   * 
   * This is the only method that should be called directly to check the tplanid
   *
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */    
  protected function checkTestPlanID($messagePrefix='')
  {
    $status=true;
    if(!$this->_isTestPlanIDPresent())
    {
      $msg = $messagePrefix . NO_TPLANID_STR;
      $this->errors[] = new IXR_Error(NO_TPLANID, $msg);
      $status = false;
    }
    else
    {        
      // See if this TPID exists in the db
      $tplanid = $this->dbObj->prepare_int($this->args[self::$testPlanIDParamName]);
      $query = "SELECT id FROM {$this->tables['testplans']} WHERE id={$tplanid}";
      $result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");           
      if(null == $result)
      {
        $msg = $messagePrefix . sprintf(INVALID_TPLANID_STR,$tplanid);
        $this->errors[] = new IXR_Error(INVALID_TPLANID, $msg);
        $status = false;            
      }
      else
      {
        // tplanid exists and its valid
        // Do we need to try to guess build id ?
        if( $this->checkGuess() && 
          (!$this->_isBuildIDPresent() &&  
             !$this->_isParamPresent(self::$buildNameParamName,$messagePrefix)))
        {
          $status = $this->_setBuildID2Latest();
        }
      }                      
    }
    return $status;
  } 
    
  /**
   * Helper method to see if the TestProjectID provided is valid
   * 
   * This is the only method that should be called directly to check the TestProjectID
   *
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */    
   protected function checkTestProjectID($messagePrefix='')
   {
      if(!($status=$this->_isTestProjectIDPresent()))
      {
          $this->errors[] = new IXR_Error(NO_TESTPROJECTID, $messagePrefix . NO_TESTPROJECTID_STR);
      }
      else
      {        
        // See if this Test Project ID exists in the db
        $testprojectid = $this->dbObj->prepare_int($this->args[self::$testProjectIDParamName]);
        $query = "SELECT id FROM {$this->tables['testprojects']} WHERE id={$testprojectid}";
        $result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");           
        if(null == $result)
        {
          $msg = $messagePrefix . sprintf(INVALID_TESTPROJECTID_STR,$testprojectid);
          $this->errors[] = new IXR_Error(INVALID_TESTPROJECTID, $msg);
          $status=false;            
        }
      }
      return $status;
    }  

  /**
   * Helper method to see if the testproject identity provided is valid 
   * Identity can be specified in one of these modes:
   *
   * - internal id (DB)
   * - prefix 
   * 
   *   
   * If everything OK, test project internal ID is setted.
   *
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */    
   protected function checkTestProjectIdentity($messagePrefix='')
   {
      $status=false;
      $fromExternal=false;
      $fromInternal=false;

      if( $this->_isTestProjectIDPresent() )
      {
        $fromInternal=true;
        $status = $this->checkTestProjectID($messagePrefix);
      }
      else if( $this->_isParamPresent(self::$prefixParamName,$messagePrefix,true) )
      {  
        // Go for the prefix
        $fromExternal=true;
  
        $target = $this->dbObj->prepare_string($this->args[self::$prefixParamName]);
        $sql = " SELECT id FROM {$this->tables['testprojects']} WHERE prefix='{$target}' ";

        $fieldValue = $this->dbObj->fetchFirstRowSingleColumn($sql, "id"); 
        $status = (!is_null($fieldValue) && (intval($fieldValue) > 0));
        if( $status )
        {
          $this->args[self::$testProjectIDParamName] = $fieldValue;
        }  
        else
        {
          $status = false;            
          $msg = $messagePrefix . sprintf(TPROJECT_PREFIX_DOESNOT_EXIST_STR,$target);
          $this->errors[] = new IXR_Error(TPROJECT_PREFIX_DOESNOT_EXIST, $msg);
        }
      }  
      else
      {
        $status = false;
      } 

      return $status;
    }   






  /**
   * Helper method to see if the TestSuiteID provided is valid
   * 
   * This is the only method that should be called directly to check the TestSuiteID
   *
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */    
    protected function checkTestSuiteID($messagePrefix='')
    {
      if(!($status=$this->_isTestSuiteIDPresent()))
      {
        $this->errors[] = new IXR_Error(NO_TESTSUITEID, $messagePrefix . NO_TESTSUITEID_STR);
      }
      else
      {        
        // See if this Test Suite ID exists in the db
        $tsuiteMgr = new testsuite($this->dbObj);
        $node_info = $tsuiteMgr->get_by_id($this->args[self::$testSuiteIDParamName]);
        if( !($status=!is_null($node_info)) )
        {
          $msg = $messagePrefix . 
                 sprintf(INVALID_TESTSUITEID_STR, $this->args[self::$testSuiteIDParamName]);
                         $this->errors[] = new IXR_Error(INVALID_TESTSUITEID, $msg);
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
   * @access protected
   */    
    protected function checkGuess()
    {      
      // if guess is set return its value otherwise return true to guess by default
      return($this->_isGuessPresent() ? $this->args[self::$guessParamName] : self::BUILD_GUESS_DEFAULT_MODE);  
    }     
    
  /**
   * Helper method to see if the buildID provided is valid for testplan
   * 
   * if build id has not been provided on call, we can use build name if has been provided.
   *
   * This is the only method that should be called directly to check the buildID
   *   
   * @return boolean
   * @access protected
   *
   * @internal revision
   */    
    protected function checkBuildID($msg_prefix)
    {
      $tplan_id=$this->args[self::$testPlanIDParamName];
      $status=true;
      $try_again=false;
        
      // First thing is to know is test plan has any build
      $buildQty = $this->tplanMgr->getNumberOfBuilds($tplan_id);
      if( $buildQty == 0)
      {
        $status = false;
        $tplan_info = $this->tplanMgr->get_by_id($tplan_id);
        $msg = $msg_prefix . sprintf(TPLAN_HAS_NO_BUILDS_STR,$tplan_info['name'],$tplan_info['id']);
        $this->errors[] = new IXR_Error(TPLAN_HAS_NO_BUILDS,$msg);
      } 
       
      if( $status )
      {
        if(!$this->_isBuildIDPresent())
        {
          $try_again=true;
          if($this->_isBuildNamePresent())
          {
            $try_again=false;
            $bname = trim($this->args[self::$buildNameParamName]);
            $buildInfo=$this->tplanMgr->get_build_by_name($tplan_id,$bname); 
            if( is_null($buildInfo) )
            {
              $msg = $msg_prefix . sprintf(BUILDNAME_DOES_NOT_EXIST_STR,$bname);
              $this->errors[] = new IXR_Error(BUILDNAME_DOES_NOT_EXIST,$msg);
              $status=false;
            }
            else
            {  
              $this->args[self::$buildIDParamName]=$buildInfo['id'];
            }
          }
        }
         
        if($try_again)
        {
          // this means we aren't supposed to guess the buildid
          if(false == $this->checkGuess())       
          {
            $this->errors[] = new IXR_Error(NO_BUILDID, NO_BUILDID_STR);        
            $status=false;
          }
          else
          {
            $setBuildResult = $this->_setBuildID2Latest();
            if(false == $setBuildResult)
            {
              $this->errors[] = new IXR_Error(NO_BUILD_FOR_TPLANID, NO_BUILD_FOR_TPLANID_STR);
              $status=false;
            }
          }
        }
         
        if( $status)
        {
          $buildID = $this->dbObj->prepare_int($this->args[self::$buildIDParamName]);
          $buildInfo=$this->tplanMgr->get_build_by_id($tplan_id,$buildID); 
          if( is_null($buildInfo) )
          {
            $tplan_info = $this->tplanMgr->get_by_id($tplan_id);
            $msg = sprintf(BAD_BUILD_FOR_TPLAN_STR,$buildID,$tplan_info['name'],$tplan_id);          
            $this->errors[] = new IXR_Error(BAD_BUILD_FOR_TPLAN, $msg);        
            $status=false;
          }
        }
      } 
      return $status;
    }
     

    /**
   * Helper method to see if a param is present
   * 
   * @param string $pname parameter name 
   * @param string $messagePrefix used to be prepended to error message
   * @param boolean $setError default false
   *                true: add predefined error code to $this->error[]
   *
   * @return boolean
   * @access protected
   *
   * 
   */         
  protected function _isParamPresent($pname,$messagePrefix='',$setError=false)
  {
    $status_ok = (isset($this->args[$pname]) ? true : false);
    if(!$status_ok && $setError)
    {
      $msg = $messagePrefix . sprintf(MISSING_REQUIRED_PARAMETER_STR,$pname);
      $this->errors[] = new IXR_Error(MISSING_REQUIRED_PARAMETER, $msg);              
    }
    return $status_ok;
  }

    /**
   * Helper method to see if the status provided is valid 
   *   
   * @return boolean
   * @access protected
   */         
    protected function _isStatusValid($status)
    {
      return(in_array($status, $this->statusCode));
    }           

    /**
   * Helper method to see if a testcasename is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */          
  protected function _isTestCaseNamePresent()
  {
    return (isset($this->args[self::$testCaseNameParamName]) ? true : false);
  }

    /**
   * Helper method to see if a testcasename is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */          
   protected function _isTestCaseExternalIDPresent()
   {
        $status=isset($this->args[self::$testCaseExternalIDParamName]) ? true : false;
        return $status;
   }


  /**
   * Helper method to see if:
   * a timestamp is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */    
    protected function _isTimeStampPresent()
    {
      return (isset($this->args[self::$timeStampParamName]) ? true : false);
    }

    /**
   * Helper method to see if a buildID is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */    
    protected function _isBuildIDPresent()
    {
      return (isset($this->args[self::$buildIDParamName]) ? true : false);
    }
    
  /**
   * Helper method to see if a buildname is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */    
    protected function _isBuildNamePresent()
    {                                   
        $status=isset($this->args[self::$buildNameParamName]) ? true : false;
      return $status;
    }
    
  /**
   * Helper method to see if build notes are given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */    
    protected function _isBuildNotePresent()
    {
      return (isset($this->args[self::$buildNotesParamName]) ? true : false);
    }
    
  /**
   * Helper method to see if testsuiteid is given as one of the arguments
   *   
   * @return boolean
   * @access protected
   */    
  protected function _isTestSuiteIDPresent()
  {
    return (isset($this->args[self::$testSuiteIDParamName]) ? true : false);
  }    
    
    /**
   * Helper method to see if a note is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */    
    protected function _isNotePresent()
    {
      return (isset($this->args[self::$noteParamName]) ? true : false);
    }        
    
    /**
   * Helper method to see if a tplanid is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */    
    protected function _isTestPlanIDPresent()
    {      
      return (isset($this->args[self::$testPlanIDParamName]) ? true : false);      
    }

    /**
   * Helper method to see if a TestProjectID is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */    
    protected function _isTestProjectIDPresent()
    {      
      return (isset($this->args[self::$testProjectIDParamName]) ? true : false);      
    }        
    
    /**
   * Helper method to see if automated is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */    
    protected function _isAutomatedPresent()
    {      
      return (isset($this->args[self::$automatedParamName]) ? true : false);      
    }        
    
    /**
   * Helper method to see if testMode is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */    
    protected function _isTestModePresent()
    {
      return (isset($this->args[self::$testModeParamName]) ? true : false);      
    }
    
    /**
   * Helper method to see if a devKey is given as one of the arguments 
   *    
   * @return boolean
   * @access protected
   */
    protected function _isDevKeyPresent()
    {
      return (isset($this->args[self::$devKeyParamName]) ? true : false);
    }
    
    /**
   * Helper method to see if a tcid is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */
    protected function _isTestCaseIDPresent()
    {
      return (isset($this->args[self::$testCaseIDParamName]) ? true : false);
    }  
    
  /**
   * Helper method to see if the guess param is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */
    protected function _isGuessPresent()
    {
    $status=isset($this->args[self::$guessParamName]) ? true : false;
    return $status;
    }
    
    /**
   * Helper method to see if the testsuitename param is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */
    protected function _isTestSuiteNamePresent()
    {
        return (isset($this->args[self::$testSuiteNameParamName]) ? true : false);
    }    
    
  /**
   * Helper method to see if the deep param is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */
    protected function _isDeepPresent()
    {
    return (isset($this->args[self::$deepParamName]) ? true : false);
    }      
    
  /**
   * Helper method to see if the status param is given as one of the arguments 
   *   
   * @return boolean
   * @access protected
   */
    protected function _isStatusPresent()
    {
    return (isset($this->args[self::$statusParamName]) ? true : false);
    }      
    
  /**
   * Helper method to see if the tcid provided is valid 
   *   
   * @param struct $tcaseid   
   * @param string $messagePrefix used to be prepended to error message
   * @param boolean $setError default false
   *                true: add predefined error code to $this->error[]
   * @return boolean
   * @access protected
   */
    protected function _isTestCaseIDValid($tcaseid,$messagePrefix='',$setError=false)
    {
      $status_ok=is_numeric($tcaseid);
      if($status_ok)
      {
        // must be of type 'testcase' and show up in the nodes_hierarchy      
        $tcaseid = $this->dbObj->prepare_int($tcaseid);
        $query = " SELECT NH.id AS id " .
                 " FROM {$this->tables['nodes_hierarchy']} NH, " .
                 " {$this->tables['node_types']} NT " .
                 " WHERE NH.id={$tcaseid} AND node_type_id=NT.id " .
                 " AND NT.description='testcase'";
        $result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");
        $status_ok = is_null($result) ? false : true; 
      }
      else if($setError)
      {
        $this->errors[] = new IXR_Error(TCASEID_NOT_INTEGER, 
                                        $messagePrefix . TCASEID_NOT_INTEGER_STR);
      }
      return $status_ok;
    }    
    
    /**
   * Helper method to see if a devKey is valid 
   *   
   * @param string $devKey   
   * @return boolean
   * @access protected
   */    
    protected function _isDevKeyValid($devKey)
    {                       
        if(null == $devKey || "" == $devKey)
        {
            return false;
        }
        else
        {   
          $this->userID = null;
          $this->devKey = $this->dbObj->prepare_string($devKey);
          $query = "SELECT id FROM {$this->tables['users']} WHERE script_key='{$this->devKey}'";
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
   * @access protected
   */        
    protected function _setTCVersion()
    {
    // TODO: Implement
    }
    
    /**
   * Helper method to See if the tcid and tplanid are valid together 
   * 
   * @param map $platformInfo key: platform ID
   * @param string $messagePrefix used to be prepended to error message
   * @return boolean
   * @access protected
   */            
    protected function _checkTCIDAndTPIDValid($platformInfo=null,$messagePrefix='')
    {    
      $tplan_id = $this->args[self::$testPlanIDParamName];
      $tcase_id = $this->args[self::$testCaseIDParamName];
      $platform_id = !is_null($platformInfo) ? key($platformInfo) : null;
        
      $filters = array('exec_status' => "ALL", 'active_status' => "ALL",
                       'tplan_id' => $tplan_id, 'platform_id' => $platform_id);
      $info = $this->tcaseMgr->get_linked_versions($tcase_id,$filters);
      $status_ok = !is_null($info);

      if( $status_ok )
      {
        $this->tcVersionID = key($info);
        $dummy = current($info);
        $plat = is_null($platform_id) ? 0 : $platform_id; 
        $this->versionNumber = $dummy[$tplan_id][$plat]['version'];
      }
      else
      {
        $tplan_info = $this->tplanMgr->get_by_id($tplan_id);
        $tcase_info = $this->tcaseMgr->get_by_id($tcase_id,testcase::ALL_VERSIONS,null, 
                                                 array('output' => 'essential'));
        if( is_null($platform_id) )
        {
          $msg = sprintf(TCASEID_NOT_IN_TPLANID_STR,$tcase_info[0]['name'],$tcase_id,$tplan_info['name'],$tplan_id);          
          $this->errors[] = new IXR_Error(TCASEID_NOT_IN_TPLANID, $msg);
        }
        else
        {
              
          $msg = sprintf(TCASEID_NOT_IN_TPLANID_FOR_PLATFORM_STR,$tcase_info[0]['name'],
                 $tcase_id,$tplan_info['name'],$tplan_id,$platformInfo[$platform_id],$platform_id);          
          $this->errors[] = new IXR_Error(TCASEID_NOT_IN_TPLANID_FOR_PLATFORM, $msg);
        }
      }
      return $status_ok;      
    }

  /**
   * Run all the necessary checks to see if the createBuild request is valid
   *  
   * @param string $messagePrefix used to be prepended to error message
   * @return boolean
   * @access protected
   */
  protected function _checkCreateBuildRequest($messagePrefix='')
  {    
      
    $checkFunctions = array('authenticate','checkTestPlanID');
    $status_ok=$this->_runChecks($checkFunctions,$messagePrefix);
    if($status_ok)
    {
      $status_ok=$this->_isParamPresent(self::$buildNameParamName,$messagePrefix,self::SET_ERROR);            
    }       
        
    return $status_ok;
  }  

  /**
   * Run all the necessary checks to see if the createBuild request is valid
   *  
   * @return boolean
   * @access protected
   */
  protected function _checkGetBuildRequest()
  {    
        $checkFunctions = array('authenticate','checkTestPlanID');       
        $status_ok=$this->_runChecks($checkFunctions);       
      return $status_ok;
  }

  
  /**
   * Run a set of functions 
   * @param array $checkFunctions set of function to be runned
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */
  protected function _runChecks($checkFunctions,$messagePrefix='')
  {
      foreach($checkFunctions as $pfn)
      {
          if( !($status_ok = $this->$pfn($messagePrefix)) )
          {
              break; 
          }
      } 
      return $status_ok;
  }



  /**
   * Gets the latest build by choosing the maximum build id for a specific test plan 
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["tplanid"]
   * @return mixed 
   *         
   * @access public
   */    
  public function getLatestBuildForTestPlan($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $status_ok=true;
    $this->_setArgs($args);
    $resultInfo=array();

    $checkFunctions = array('authenticate','checkTestPlanID');       
    $status_ok=$this->_runChecks($checkFunctions,$msg_prefix);       

    if( $status_ok )
    {
      $testPlanID = $this->args[self::$testPlanIDParamName];
      $build_id = $this->tplanMgr->get_max_build_id($testPlanID);
         
      if( ($status_ok=$build_id > 0) )
      {
        $builds = $this->tplanMgr->get_builds($testPlanID);  
        $build_info = $builds[$build_id];
      }
      else
      {
        $tplan_info=$this->tplanMgr->get_by_id($testPlanID);
        $msg = $msg_prefix . sprintf(TPLAN_HAS_NO_BUILDS_STR,$tplan_info['name'],
                                     $tplan_info['id']);
        $this->errors[] = new IXR_Error(TPLAN_HAS_NO_BUILDS,$msg);
      }
    }
        
    return $status_ok ? $build_info : $this->errors;
  }





  /**
   * _getLatestBuildForTestPlan
   *
   * @param struct $args
   *
   */
  protected function _getLatestBuildForTestPlan($args)
  {
    $builds = $this->_getBuildsForTestPlan($args);
    $maxid = -1;
    $maxkey = -1;
    foreach ($builds as $key => $build) 
    {
      if ($build['id'] > $maxid)
      {
        $maxkey = $key;
        $maxid = $build['id'];
      }
    }
    $maxbuild = array();
    $maxbuild[] = $builds[$maxkey];

    return $maxbuild;
  }
  
  /**
   * Gets the result of LAST EXECUTION for a particular testcase on a test plan.
   * If there are no filter criteria regarding platform and build,
   * result will be get WITHOUT checking for a particular platform and build.
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["tplanid"]
   * @param int $args["testcaseid"]: Pseudo optional.
   *                                 if does not is present then testcaseexternalid MUST BE present
   *
   * @param int $args["testcaseexternalid"]: Pseudo optional.
   *                                         if does not is present then testcaseid MUST BE present
   *
   * @param string $args["platformid"]: optional. 
   *                                    ONLY if not present, then $args["platformname"] 
   *                                    will be analized (if exists)
   *
   * @param string $args["platformname"]: optional (see $args["platformid"])
   *
   * @param int $args["buildid"]: optional
   *                              ONLY if not present, then $args["buildname"] will be analized (if exists)
   * 
   * @param int $args["buildname"] - optional (see $args["buildid"])
   *
   * @param int $args["options"] - optional 
   *                               options['getBugs'] = true / false
   *
   *
   * @return mixed $resultInfo
   *               if execution found
   *               array that contains a map with these keys:
   *               id (execution id),build_id,tester_id,execution_ts,
   *               status,testplan_id,tcversion_id,tcversion_number,
   *               execution_type,notes.
   *
   *               If user has requested getbugs, then a key bugs (that is an array)
   *               will also exists. 
   *               
   *               
   *
   *               if test case has not been execute,
   *               array('id' => -1)
   *
   * @access public
   */
    public function getLastExecutionResult($args)
    {
      $operation=__FUNCTION__;
      $msg_prefix="({$operation}) - ";
        
      $this->_setArgs($args);
      $resultInfo = array();
      $status_ok=true;

      $options = new stdClass();
      $options->getBugs = 0;

                
      // Checks are done in order
      $checkFunctions = array('authenticate','checkTestPlanID','checkTestCaseIdentity');

      $status_ok=$this->_runChecks($checkFunctions,$msg_prefix) && 
                 $this->_checkTCIDAndTPIDValid(null,$msg_prefix) && 
                 $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);       

      $execContext = array('tplan_id' => $this->args[self::$testPlanIDParamName],
                           'platform_id' => null,'build_id' => null);

      if( $status_ok )
      {
        if( $this->_isParamPresent(self::$optionsParamName,$msg_prefix) )
        {
          $dummy = $this->args[self::$optionsParamName];
          if( is_array($dummy) )
          {
            foreach($dummy as $key => $value)
            {
              $options->$key = ($value > 0) ? 1 : 0;
            }
          }
        }

        // Now we can check for Optional parameters
        if($this->_isBuildIDPresent() || $this->_isBuildNamePresent())
        {
          if( ($status_ok =  $this->checkBuildID($msg_prefix)) )
          {
            $execContext['build_id'] = $this->args[self::$buildIDParamName];  
          }  
        }  

        if( $status_ok )
        {
          if( $this->_isParamPresent(self::$platformIDParamName,$msg_prefix) ||
              $this->_isParamPresent(self::$platformNameParamName,$msg_prefix) )
          {
            $status_ok = $this->checkPlatformIdentity($this->args[self::$testPlanIDParamName]);

            if( $status_ok)
            {
              $execContext['platform_id'] = $this->args[self::$platformIDParamName];  
            }  
          }  
        }  
      }  




      if( $status_ok )
      {

        $sql = " SELECT MAX(id) AS exec_id FROM {$this->tables['executions']} " .
               " WHERE testplan_id = {$this->args[self::$testPlanIDParamName]} " .
               " AND tcversion_id IN (" .
               " SELECT id FROM {$this->tables['nodes_hierarchy']} " .
               " WHERE parent_id = {$this->args[self::$testCaseIDParamName]})";

        if(!is_null($execContext['build_id']))
        {
          $sql .= " AND build_id = " . intval($execContext['build_id']);
        }  

        if(!is_null($execContext['platform_id']))
        {
          $sql .= " AND platform_id = " . intval($execContext['platform_id']);
        }  

        $rs = $this->dbObj->fetchRowsIntoMap($sql,'exec_id');
        if( is_null($rs) )
        {
          // has not been executed
          // execution id = -1 => test case has not been runned.
          $resultInfo[]=array('id' => -1);
        }  
        else
        {
          // OK Select * is not a good practice but ... (fman)
          $targetID = intval(key($rs));
          $sql = "SELECT * FROM {$this->tables['executions']} WHERE id=" . $targetID;
          $resultInfo[0] = $this->dbObj->fetchFirstRow($sql);

          if($options->getBugs)
          {
            $resultInfo[0]['bugs'] = array();
            $sql = " SELECT DISTINCT bug_id FROM {$this->tables['execution_bugs']} " . 
                   " WHERE execution_id = " . $targetID;
            $resultInfo[0]['bugs'] = (array)$this->dbObj->get_recordset($sql);       
          }  
        }  
      }
      
      return $status_ok ? $resultInfo : $this->errors;
    }




   /**
   * Adds the result to the database 
   *
   * @return int
   * @access protected
   */      
  protected function _insertResultToDB($user_id=null,$exec_ts=null)
  {
    
    $build_id = $this->args[self::$buildIDParamName];
    $status = $this->args[self::$statusParamName];
    $testplan_id =  $this->args[self::$testPlanIDParamName];
    $tcversion_id =  $this->tcVersionID;
    $version_number =  $this->versionNumber;

    $tester_id =  is_null($user_id) ? $this->userID : $user_id;
    $execTimeStamp = is_null($exec_ts) ? $this->dbObj->db_now() : $exec_ts;

    // return $execTimeStamp;

    $platform_id = 0;
    
    if( isset($this->args[self::$platformIDParamName]) )
    {
      $platform_id = $this->args[self::$platformIDParamName];   
    }
    
    $notes='';
    $notes_field="";
    $notes_value="";  

    if($this->_isNotePresent())
    {
      $notes = $this->dbObj->prepare_string($this->args[self::$noteParamName]);
    }
    
    if(trim($notes) != "")
    {
      $notes_field = ",notes";
      $notes_value = ", '{$notes}'";  
    }

    $duration_field = '';
    $duration_value = '';
    if( isset($this->args[self::$executionDurationParamName]) )
    {
      $duration_field = ',execution_duration';
      $duration_value = ", " . 
          floatval($this->args[self::$executionDurationParamName]);  
    }

    $execution_type = constant("TESTCASE_EXECUTION_TYPE_AUTO");

    $query = "INSERT INTO {$this->tables['executions']} " .
             " (build_id, tester_id, execution_ts, status, testplan_id, tcversion_id, " .
             " platform_id, tcversion_number," .
             " execution_type {$notes_field} {$duration_field}) " .
             " VALUES({$build_id},{$tester_id},{$execTimeStamp}," .
             " '{$status}',{$testplan_id}," .
             " {$tcversion_id},{$platform_id}, {$version_number},{$execution_type} " .
             " {$notes_value} {$duration_value})";

    $this->dbObj->exec_query($query);
    return $this->dbObj->insert_id($this->tables['executions']);    
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
   *
   * @param -
   * @return string
   * @access public
   */  
  public function testLinkVersion()
  {
    return TL_VERSION_NUMBER;        
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
    $str = " Testlink API Version: " . self::$version . " initially written by Asiel Brumfield\n" .
           " with contributions by TestLink development Team";
    return $str;        
  }

  /**
   * Creates a new build for a specific test plan
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["testplanid"]
   * @param string $args["buildname"];
   * @param string $args["buildnotes"];
   * @param string $args["active"];
   * @param string $args["open"];
   * @param string $args["releasedate"]: YYYY-MM-DD;
   * @param int $args["copytestersfrombuild"] OPTIONAL,
   *        if > 0 and valid buildid tester assignments will be copied.
   *   
   * @return mixed $resultInfo
   *         
   * @access public
   */    
  public function createBuild($args)
  {
    $operation = __FUNCTION__;
    $messagePrefix="({$operation}) - ";
    $resultInfo = array();
    $resultInfo[0]["status"] = true;
    $resultInfo[0]["operation"] = $operation;
    $insertID = '';
    $returnMessage = GENERAL_SUCCESS_STR;

    $this->_setArgs($args);

    // check the tpid
    if($this->_checkCreateBuildRequest($messagePrefix) && 
       $this->userHasRight("testplan_create_build",self::CHECK_PUBLIC_PRIVATE_ATTR))
    {
      $testPlanID = intval($this->args[self::$testPlanIDParamName]);
      $buildName = $this->args[self::$buildNameParamName];          
      $buildNotes = "";
      if($this->_isBuildNotePresent())
      {      
        $buildNotes = $this->dbObj->prepare_string($this->args[self::$buildNotesParamName]);
      }
      
      
      if ($this->tplanMgr->check_build_name_existence($testPlanID,$buildName))
      {
        //Build exists so just get the id of the existing build
        $insertID = $this->tplanMgr->get_build_id_by_name($testPlanID,$buildName);
        $returnMessage = sprintf(BUILDNAME_ALREADY_EXISTS_STR,$buildName,$insertID);
        $resultInfo[0]["status"] = false;
      
      } 
      else 
      {
        //Build doesn't exist so create one
        // ,$active=1,$open=1);
        // ($tplan_id,$name,$notes = '',$active=1,$open=1,$release_date='')

        // key 2 check with default value is parameter is missing
        $k2check = array(self::$activeParamName => 1,self::$openParamName => 1,
                         self::$releaseDateParamName => null,
                         self::$copyTestersFromBuildParamName => 0);
        foreach($k2check as $key => $value)
        {
          $opt[$key] = $this->_isParamPresent($key) ? $this->args[$key] : $value;
        }

        // check if release date is valid date.
        // do not check relation with now(), i.e can be <,> or =.
        //
        if( !is_null($opt[self::$releaseDateParamName]) )
        {
          if( !$this->validateDateISO8601($opt[self::$releaseDateParamName]) )
          {
            $opt[self::$releaseDateParamName] = null;
          }  
        }  

        $bm = new build_mgr($this->dbObj);
        $insertID = $bm->create($testPlanID,$buildName,$buildNotes,
                                $opt[self::$activeParamName],
                                $opt[self::$openParamName],
                                $opt[self::$releaseDateParamName]);
      
        if( $insertID > 0)
        {
          $sourceBuild = intval($opt[self::$copyTestersFromBuildParamName]);

          if( $sourceBuild > 0 )
          {
            // Check if belongs to test plan, otherwise ignore in silence
            $sql = " SELECT id FROM {$this->tables['builds']} " .
                   " WHERE id = " . $sourceBuild .
                   " AND testplan_id = " . $testPlanID;
            $rs = $this->dbObj->get_recordset($sql);

            if( count($rs) == 1 )
            {
              $taskMgr = new assignment_mgr($this->dbObj);
              $taskMgr->copy_assignments($sourceBuild, $insertID, $this->userID);
            }  
          } 
        }  
      }
      
      $resultInfo[0]["id"] = $insertID;  
      $resultInfo[0]["message"] = $returnMessage;
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
    //TODO: NEED associated RIGHT
    if($this->authenticate())
    {
      return $this->tprojectMgr->get_all();  
    }
    else
    {
      return $this->errors;
    }
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
    $messagePrefix="(" .__FUNCTION__ . ") - ";
        
    $this->_setArgs($args);
    // check the tplanid
    //TODO: NEED associated RIGHT
    $checkFunctions = array('authenticate','checkTestProjectID');       
    $status_ok=$this->_runChecks($checkFunctions,$messagePrefix);       
  
    if($status_ok)
    {
      $testProjectID = $this->args[self::$testProjectIDParamName];
      $info=$this->tprojectMgr->get_all_testplans($testProjectID);
      if( !is_null($info) && count($info) > 0 )
      {
          $info = array_values($info);
      }
      return $info;  
    }
    else
    {
      return $this->errors;
    } 
  }
  
  /**
   * Gets a list of builds within a test plan
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["testplanid"]
   * @return 
   *         if no errors
   *            no build present => null
   *            array of builds
   *         
   *         
   * @access public
   */    
  public function getBuildsForTestPlan($args)
  {
      $operation=__FUNCTION__;
       $msg_prefix="({$operation}) - ";
        $this->_setArgs($args);

        $builds=null;
        $status_ok=true;
        $checkFunctions = array('authenticate','checkTestPlanID');       
        $status_ok=$this->_runChecks($checkFunctions,$msg_prefix);       
      
        if( $status_ok )
        {
            $testPlanID = $this->args[self::$testPlanIDParamName];
            $dummy = $this->tplanMgr->get_builds($testPlanID);
            
        if( !is_null($dummy) )
        {
           $builds=array_values($dummy);
        }
        }
        return $status_ok ? $builds : $this->errors;
  }


  /**
   * List test suites within a test plan alphabetically
   * 
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["testplanid"]
   * @return mixed $resultInfo
   */
   public function getTestSuitesForTestPlan($args)
   {
      $operation=__FUNCTION__;
       $msg_prefix="({$operation}) - ";
     $this->_setArgs($args);

        $checkFunctions = array('authenticate','checkTestPlanID');       
        $status_ok=$this->_runChecks($checkFunctions,$msg_prefix);       
    if($status_ok)
    {
      $testPlanID = $this->args[self::$testPlanIDParamName];      
      $result = $this->tplanMgr->get_testsuites($testPlanID);
      return   $result;
    }
    else
    {
      return $this->errors;
    } 
   }
  
  /**
   * create a test project
   * 
   * @param struct $args
   * @param string $args["devKey"]
   * @param string $args["testprojectname"]
   * @param string $args["testcaseprefix"]
   * @param string $args["notes"] OPTIONAL
   * @param map $args["options"] OPTIONAL ALL int treated as boolean
   *        keys  requirementsEnabled,testPriorityEnabled,automationEnabled,inventoryEnabled
   *
   * @param int $args["active"]  OPTIONAL
   * @param int $args["public"]  OPTIONAL
   * @param string $args["itsname"]  OPTIONAL  
   * @param boolean $args["itsEnabled"]  OPTIONAL  
   * 
   *
   * @return mixed $resultInfo
   */
  public function createTestProject($args)
  {
    $this->_setArgs($args);
    $msg_prefix = "(" . __FUNCTION__ . ") - ";
    $checkRequestMethod='_check' . ucfirst(__FUNCTION__) . 'Request';
 
    $status_ok = false; 
    if( $this->$checkRequestMethod($msg_prefix) && 
        $this->userHasRight("mgt_modify_product"))
    {
      $status_ok = true; 
  
      $item = new stdClass();
      $item->options = new stdClass();
      $item->options->requirementsEnabled = 1;
      $item->options->testPriorityEnabled = 1;
      $item->options->automationEnabled = 1;
      $item->options->inventoryEnabled = 1;

      if( $this->_isParamPresent(self::$optionsParamName,$msg_prefix) )
      {
        // has to be an array ?
        $dummy = $this->args[self::$optionsParamName];
        if( is_array($dummy) )
        {
          foreach($dummy as $key => $value)
          {
            $item->options->$key = $value > 0 ? 1 : 0;
          }
        }
      }

      // other optional parameters (not of complex type)
      // key 2 check with default value is parameter is missing
      $keys2check = array(self::$activeParamName => 1,self::$publicParamName => 1,
                          self::$noteParamName => '',
                          self::$itsEnabledParamName => 0,
                          self::$itsNameParamName => '');
      foreach($keys2check as $key => $value)
      {
        $optional[$key]=$this->_isParamPresent($key) ? trim($this->args[$key]) : $value;
      }

      $item->name = htmlspecialchars($this->args[self::$testProjectNameParamName]);
      $item->prefix = htmlspecialchars($this->args[self::$testCasePrefixParamName]);

      $item->notes = htmlspecialchars($optional[self::$noteParamName]);
      $item->active = ($optional[self::$activeParamName] > 0) ? 1 : 0;
      $item->is_public = ($optional[self::$publicParamName] > 0) ? 1 : 0;
      $item->color = '';
      
      $its = null;
      if ($optional[self::$itsNameParamName] != "") 
      {
        $this->itsMgr = new tlIssueTracker($this->dbObj);
        $its = $this->getIssueTrackerSystem($this->args,'internal');

        $itsOK = !is_null($its);
        if( !$itsOK  ) 
        {
          $status_ok = false;
        }

      }
    }

    // All checks OK => try to create testproject 
    if( $status_ok )
    {  
      $tproject_id = $this->tprojectMgr->create($item);

      // link & enable its?
      if( $itsOK && $tproject_id > 0 )
      {
        // link 
        $this->itsMgr->link($its["id"], $tproject_id);

        // enable
        if ($optional[self::$itsEnabledParamName] > 0)
        {
          $this->tprojectMgr->enableIssueTracker($tproject_id);
        } 
      }

      $ret = array();
      $ret[]= array("operation" => __FUNCTION__,
                    "additionalInfo" => null,
                    "status" => true, "id" => $tproject_id, 
                    "message" => GENERAL_SUCCESS_STR);
      return $ret;
    }

    return ($status_ok ? $ret : $this->errors);
  }
  
  /**
   * _checkCreateTestProjectRequest
   *
   */
  protected function _checkCreateTestProjectRequest($msg_prefix)
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
             $this->errors[] = new IXR_Error(TESTPROJECTNAME_SINTAX_ERROR, 
                                             $msg_prefix . $check_op['msg']);
          }
      }
      
      if( $status_ok ) 
      {
          $check_op=$this->tprojectMgr->checkNameExistence($name);
          $status_ok=$check_op['status_ok'];     
          if(!$status_ok)
          {     
             $this->errors[] = new IXR_Error(TESTPROJECTNAME_EXISTS, 
                                             $msg_prefix . $check_op['msg']);
          }
      }

      if( $status_ok ) 
      {
          $status_ok=!empty($prefix);
          if(!$status_ok)
          {     
             $this->errors[] = new IXR_Error(TESTPROJECT_TESTCASEPREFIX_IS_EMPTY, 
                                             $msg_prefix . $check_op['msg']);
          }
      }

      if( $status_ok ) 
      {
           $info=$this->tprojectMgr->get_by_prefix($prefix);
           if( !($status_ok = is_null($info)) )
           {
              $msg = $msg_prefix . sprintf(TPROJECT_PREFIX_ALREADY_EXISTS_STR,$prefix,$info['name']);
              $this->errors[] = new IXR_Error(TPROJECT_PREFIX_ALREADY_EXISTS,$msg);
           }
      }

      return $status_ok;
  }

  
  
  /**
   * List test cases within a test suite
   * 
   * By default test cases that are contained within child suites 
   * will be returned. 
   * Set the deep flag to false if you only want test cases in the test suite provided 
   * and no child test cases.
   *  
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["testsuiteid"]
   * @param boolean $args["deep"] - optional (default is true)
   * @param boolean $args["details"] - optional (default is simple)
   *                                use full if you want to get 
   *                                summary,steps & expected_results
   *
   * @param boolean $args["getkeywords"] - optional (default false)
   * @return mixed $resultInfo
   *
   *
   */
  public function getTestCasesForTestSuite($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
      
    $this->_setArgs($args);
    $status_ok=$this->_runChecks(array('authenticate','checkTestSuiteID'),$msg_prefix);       
    
    $details='simple';
    $key2search=self::$detailsParamName;
    if( $this->_isParamPresent($key2search) )
    {
        $details=$this->args[$key2search];  
    }
      
    if($status_ok)
    {    
      $testSuiteID = $this->args[self::$testSuiteIDParamName];
      $dummy = $this->tprojectMgr->tree_manager->get_path($testSuiteID);
      $this->args[self::$testProjectIDParamName] = $dummy[0]['parent_id'];
      $status_ok = $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);
    }


    if($status_ok)
    {    
      $tsuiteMgr = new testsuite($this->dbObj);
      if(!$this->_isDeepPresent() || $this->args[self::$deepParamName] )
      {
        $pfn = 'get_testcases_deep';
      }  
      else
      {
        $pfn = 'get_children_testcases';
      }

      $opt = null;
      if( isset($this->args[self::$getKeywordsParamName]) && $this->args[self::$getKeywordsParamName])
      {
        $opt = array('getKeywords' => true);
      }  

      return $tsuiteMgr->$pfn($testSuiteID,$details,$opt);
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
  * If possible also pass the string for the test suite name. 
  *
  * No results will be returned if there are test cases with the same name that match the criteria provided.  
  * 
  * @param struct $args
  * @param string $args["devKey"]
  * @param string $args["testcasename"]
  * @param string $args["testsuitename"] - optional
  * @param string $args["testprojectname"] - optional
  * @param string $args["testcasepathname"] - optional
  *               Full test case path name, starts with test project name
  *               pieces separator -> :: -> default value of getByPathName()
  * @return mixed $resultInfo
  */
  public function getTestCaseIDByName($args)
  {
    $msg_prefix="(" .__FUNCTION__ . ") - ";
    $status_ok=true;
    $this->_setArgs($args);
    $result = null;
      
    $checkFunctions = array('authenticate','checkTestCaseName');       
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);
      
    if( $status_ok )
    {      
      $testCaseName = $this->args[self::$testCaseNameParamName];
      $testCaseMgr = new testcase($this->dbObj);
      $keys2check = array(self::$testSuiteNameParamName,self::$testCasePathNameParamName,
                          self::$testProjectNameParamName);
      foreach($keys2check as $key)
      {
        $optional[$key]=$this->_isParamPresent($key) ? trim($this->args[$key]) : '';
      }
 
      if( $optional[self::$testCasePathNameParamName] != '' )
      {
        $dummy = $testCaseMgr->getByPathName($optional[self::$testCasePathNameParamName]);
        if( !is_null($dummy) )
        {
          $result[0] = $dummy;
        }
      }
      else
      {
        $result = $testCaseMgr->get_by_name($testCaseName,$optional[self::$testSuiteNameParamName],
                                            $optional[self::$testProjectNameParamName]);
      }

      $match_count = count($result);
      switch($match_count)
      {
        case 0:
          $status_ok = false;
          $this->errors[] = new IXR_ERROR(NO_TESTCASE_BY_THIS_NAME, 
                                          $msg_prefix . NO_TESTCASE_BY_THIS_NAME_STR);
        break;

        case 1:
          $status_ok = true;
        break;
        
        default:
          // multiple matches.
          $status_ok = true;
        break;
        
      }
    }

    // $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);       
    // if we have multiple matches, we have issues to check test project access for user
    // requesting operation.
    // what to do ?
    // check access for each result and remove result if user has no access to corresponding
    // test project.
    if($status_ok)
    {
      $out = null;
      foreach($result as $testcase)
      {
        $this->args[self::$testProjectIDParamName] = $this->tcaseMgr->get_testproject($testcase['id']);
        if( $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR) )
        {
          $out[] = $testcase; 
        }  
      }  
    } 
    return $status_ok ? $out : $this->errors; 
  }
   
   /**
    * createTestCase
    * @param struct $args
    * @param string $args["devKey"]
    * @param string $args["testcasename"]
    * @param int    $args["testsuiteid"]: test case parent test suite id
    * @param int    $args["testprojectid"]: test case parent test suite id
    *
    * @param string $args["authorlogin"]: to set test case author
    * @param string $args["summary"]
    * @param array  $args["steps"]
    *
    * @param string $args["preconditions"] - optional
    * @param int    $args["importance"] - optional - see const.inc.php for domain
    * @param int    $args["execution"] - optional - see ... for domain
    * @param int    $args["order'] - optional
    * @param int    $args["internalid"] - optional - do not use
    * @param string $args["checkduplicatedname"] - optional
    * @param string $args["actiononduplicatedname"] - optional
    * @param int    $args["status"] - optional - see const.inc.php $tlCfg->testCaseStatus
    * @param number $args["estimatedexecduration"] - optional
    *
    * @return mixed $resultInfo
    * @return string $resultInfo['operation'] - verbose operation
    * @return boolean $resultInfo['status'] - verbose operation
    * @return int $resultInfo['id'] - test case internal ID (Database ID)
    * @return mixed $resultInfo['additionalInfo'] 
    * @return int $resultInfo['additionalInfo']['id'] same as $resultInfo['id']
    * @return int $resultInfo['additionalInfo']['external_id'] without prefix
    * @return int $resultInfo['additionalInfo']['status_ok'] 1/0
    * @return string $resultInfo['additionalInfo']['msg'] - for debug 
    * @return string $resultInfo['additionalInfo']['new_name'] only present if new name generation was needed
    * @return int $resultInfo['additionalInfo']['version_number']
    * @return boolean $resultInfo['additionalInfo']['has_duplicate'] - for debug 
    * @return string $resultInfo['message'] operation message
    */
  public function createTestCase($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
      
    $wfStatusDomain = config_get('testCaseStatus');
      
    $keywordSet='';
    $this->_setArgs($args);
    $checkFunctions = array('authenticate','checkTestProjectID','checkTestSuiteID','checkTestCaseName');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix) && 
                 $this->userHasRight("mgt_modify_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);

    if( $status_ok )
    {
      $keys2check = array(self::$authorLoginParamName,self::$summaryParamName, self::$stepsParamName);
      foreach($keys2check as $key)
      {
        if(!$this->_isParamPresent($key))
        {
          $status_ok = false;
          $msg = $msg_prefix . sprintf(MISSING_REQUIRED_PARAMETER_STR,$key);
          $this->errors[] = new IXR_Error(MISSING_REQUIRED_PARAMETER, $msg);              
        }   
      }
    }                        

    if( $status_ok )
    {
      $author_id = tlUser::doesUserExist($this->dbObj,$this->args[self::$authorLoginParamName]);          
      if( !($status_ok = !is_null($author_id)) )
      {
        $msg = $msg_prefix . sprintf(NO_USER_BY_THIS_LOGIN_STR,$this->args[self::$authorLoginParamName]);
        $this->errors[] = new IXR_Error(NO_USER_BY_THIS_LOGIN, $msg);        
      }
    }

    if( $status_ok )
    {
      $keywordSet=$this->getKeywordSet($this->args[self::$testProjectIDParamName]);
    }

    if( $status_ok )
    {
      // Optional parameters
      $opt=array(self::$importanceParamName => 2,
                 self::$executionTypeParamName => TESTCASE_EXECUTION_TYPE_MANUAL,
                 self::$orderParamName => testcase::DEFAULT_ORDER,
                 self::$internalIDParamName => testcase::AUTOMATIC_ID,
                 self::$checkDuplicatedNameParamName => testcase::DONT_CHECK_DUPLICATE_NAME,
                 self::$actionOnDuplicatedNameParamName => 'generate_new',
                 self::$preconditionsParamName => '',
                 self::$statusParamName => $wfStatusDomain['draft'],
                 self::$estimatedExecDurationParamName => null);
        
      foreach($opt as $key => $value)
      {
        if($this->_isParamPresent($key))
        {
          $opt[$key]=$this->args[$key];      
        }   
      }
    }
        
             
    if( $status_ok )
    {
      $options = array('check_duplicate_name' => $opt[self::$checkDuplicatedNameParamName],
                       'action_on_duplicate_name' => $opt[self::$actionOnDuplicatedNameParamName],
                       'status' => $opt[self::$statusParamName],
                       'estimatedExecDuration' => $opt[self::$estimatedExecDurationParamName]);

      $op_result=$this->tcaseMgr->create($this->args[self::$testSuiteIDParamName],
                                         $this->args[self::$testCaseNameParamName],
                                         $this->args[self::$summaryParamName],
                                         $opt[self::$preconditionsParamName],
                                         $this->args[self::$stepsParamName],
                                         $author_id,$keywordSet,
                                         $opt[self::$orderParamName],
                                         $opt[self::$internalIDParamName],
                                         $opt[self::$executionTypeParamName],
                                         $opt[self::$importanceParamName],
                                         $options);
            
      $resultInfo=array();
      $resultInfo[] = array("operation" => $operation, "status" => true, 
                            "id" => $op_result['id'], 
                            "additionalInfo" => $op_result,
                            "message" => GENERAL_SUCCESS_STR);
    } 
    return ($status_ok ? $resultInfo : $this->errors);
  }  
   

   /**
   * Reports a result for a single test case
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["testcaseid"]: optional, if not present           
   *                                 testcaseexternalid must be present
   *
   * @param int $args["testcaseexternalid"]: optional, if does not is present           
   *                                         testcaseid must be present
   *
   *
   *
   * @param int $args["testplanid"] 
   * @param string $args["status"] - status is {@link $validStatusList}
   * @param int $args["buildid"] - optional.
   *                               if not present and $args["buildname"] exists
   *                               then 
   *                                    $args["buildname"] will be checked and used if valid
   *                               else 
   *                                    build with HIGHEST ID will be used
   *
   * @param int $args["buildname"] - optional.
   *                               if not present Build with higher internal ID will be used
   *
   *
   * @param string $args["notes"] - optional
   * @param string $args["execduration"] - optional
   *
   * @param bool $args["guess"] - optional defining whether to guess optinal params or require them 
   *                               explicitly default is true (guess by default)
   *
   * @param string $args["bugid"] - optional
   *
   * @param string $args["platformid"] - optional, if not present platformname must be present
   * @param string $args["platformname"] - optional, if not present platformid must be present
   *    
   *
   * @param string $args["customfields"] - optional
   *               contains an map with key:Custom Field Name, value: value for CF.
   *               VERY IMPORTANT: value must be formatted in the way it's written to db,
   *               this is important for types like:
   *
   *               DATE: strtotime()
   *               DATETIME: mktime()
   *               MULTISELECTION LIST / CHECKBOX / RADIO: se multipli selezione ! come separatore
   *
   *
   *               these custom fields must be configured to be writte during execution.
   *               If custom field do not meet condition value will not be written
   *
   * @param boolean $args["overwrite"] - optional, if present and true, then last execution
   *                for (testcase,testplan,build,platform) will be overwritten.            
   *
   * @param boolean $args["user"] - optional, if present and user is a valid login 
   *                                (no other check will be done) it will be used when writting execution.
   *
   * @param string $args["timestamp"] - optional, if not present now is used
   *                                    format YYYY-MM-DD HH:MM:SS
   *                                    example 2015-05-22 12:15:45   
   * @return mixed $resultInfo 
   *         [status]  => true/false of success
   *         [id]      => result id or error code
   *         [message]  => optional message for error message string
   * @access public
   *
   * @internal revisions
   *
   */
  public function reportTCResult($args)
  {    
    $resultInfo = array();
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $this->errors = null;

    $this->_setArgs($args);              
    $resultInfo[0]["status"] = true;
    
    $checkFunctions = array('authenticate','checkTestCaseIdentity','checkTestPlanID',
                            'checkBuildID','checkStatus');

    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);       

    if($status_ok)
    {      
      // This check is needed only if test plan has platforms
      $platformSet = $this->tplanMgr->getPlatforms($this->args[self::$testPlanIDParamName],
                                                        array('outputFormat' => 'map'));  
      $targetPlatform = null;
      
      if( !is_null($platformSet) )
      {       
        $status_ok = $this->checkPlatformIdentity($this->args[self::$testPlanIDParamName],
                                                  $platformSet,$msg_prefix);
        if($status_ok)
        {
          $targetPlatform[$this->args[self::$platformIDParamName]] = $platformSet[$this->args[self::$platformIDParamName]];
        }
      }
      $status_ok = $status_ok && $this->_checkTCIDAndTPIDValid($targetPlatform,$msg_prefix);
    }

    $tester_id = null;
    if($status_ok)
    { 
      $this->errors = null;
      if( $this->_isParamPresent(self::$userParamName) )
      {
        $tester_id = tlUser::doesUserExist($this->dbObj,$this->args[self::$userParamName]);          
        if( !($status_ok = !is_null($tester_id)) )
        {
          $msg = $msg_prefix . sprintf(NO_USER_BY_THIS_LOGIN_STR,$this->args[self::$userParamName]);
          $this->errors[] = new IXR_Error(NO_USER_BY_THIS_LOGIN, $msg);  
        }
      }
    }

    $exec_ts = null;
    if($status_ok)
    { 
      if( $this->_isParamPresent(self::$timeStampParamName) )
      {
        // Now check if is a valid one
        $exec_ts = $this->args[self::$timeStampParamName];

        try
        {
          checkTimeStamp($exec_ts);
          $exec_ts = "'{$exec_ts}'";
        }
        catch(Exception $e) 
        {
          $status_ok = false;
          $this->errors = null;
          $msg = $msg_prefix . sprintf(INVALID_TIMESTAMP_STR,$exec_ts);
          $this->errors[] = new IXR_Error(INVALID_TIMESTAMP, $msg);  
        }  
      }
    }

    if($status_ok && $this->userHasRight("testplan_execute",self::CHECK_PUBLIC_PRIVATE_ATTR))
    { 
      $executionID = 0;  
      $resultInfo[0]["operation"] = $operation;
      $resultInfo[0]["overwrite"] = false;
      $resultInfo[0]["status"] = true;
      $resultInfo[0]["message"] = GENERAL_SUCCESS_STR;


      if($this->_isParamPresent(self::$overwriteParamName) && $this->args[self::$overwriteParamName])
      {
        $executionID = $this->_updateResult($tester_id,$exec_ts);
        $resultInfo[0]["overwrite"] = true;      
      }

      if($executionID == 0)
      {
        $executionID = $this->_insertResultToDB($tester_id,$exec_ts);      
      } 
      
      $resultInfo[0]["id"] = $executionID;  
      
      // Do we need to insert a bug ?
      if($this->_isParamPresent(self::$bugIDParamName))
      {
        $bugID = $this->args[self::$bugIDParamName];
        $resultInfo[0]["bugidstatus"] = $this->_insertExecutionBug($executionID, $bugID);
      }
          
      if($this->_isParamPresent(self::$customFieldsParamName))
      {
        $resultInfo[0]["customfieldstatus"] = $this->_insertCustomFieldExecValues($executionID);   
      }

      //
      if( $executionID > 0 && !$resultInfo[0]["overwrite"])
      {
        // Get steps info
        // step number, result, notes
        if( $this->_isParamPresent(self::$stepsParamName) )
        {
          $resultInfo[0]["steps"] = 'yes!';
          
          $st = &$this->args[self::$stepsParamName];
          foreach($st as $sp)
          {
            $nst[$sp['step_number']] = $sp;
          } 

          $r2d2 = array('fields2get' => 'TCSTEPS.step_number,TCSTEPS.id', 
                        'accessKey' => 'step_number', 
                        'renderGhostSteps' => false, 
                        'renderImageInline' => false);
          
          // return array('tcx' => $this->tcVersionID); //gretel
          $steps = $this->tcaseMgr->getStepsSimple($this->tcVersionID,0,$r2d2);
        
          $target = DB_TABLE_PREFIX . 'execution_tcsteps';
          $resultsCfg = config_get('results');
          foreach($nst as $spnum => $spdata)
          {

            // check if step exists, if not ignore
            if( isset($steps[$spnum]) )
            {
              // if result is not on domain, write it
              // anyway.
              $status = strtolower(trim($spdata['result']));
              $status = $status[0];

              $sql = " INSERT INTO {$target} (execution_id,tcstep_id,notes";
              $sql .= ",status";
           
              $values = " VALUES ( {$executionID}, {$steps[$spnum]['id']}," . 
                        "'" . $this->dbObj->prepare_string($spdata['notes']) . "'";
              $values .= ",'" . $this->dbObj->prepare_string($status) . "'";
              $sql .= ") " . $values . ")";
              
              if( $status != $resultsCfg['status_code']['not_run'] )
              {
                $this->dbObj->exec_query($sql);
              }  
            }  
          } 
        } 
      } 

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
   * @access protected
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
   * This is the only method that should be called directly to check test case identity
   *   
   * If everything OK, test case internal ID is setted.
   *
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */    
   protected function checkTestCaseIdentity($messagePrefix='')
   {
      // Three Cases - Internal ID, External ID, No Id        
      $status=false;
      $tcaseID=0;
      $my_errors=array();
      $fromExternal=false;
      $fromInternal=false;

      if($this->_isTestCaseIDPresent())
      {
        $fromInternal=true;
        $status = ( ($tcaseID = intval($this->args[self::$testCaseIDParamName])) > 0);

        if( !$status )
        {
          $this->errors[] = new IXR_Error($tcaseID,
                                sprintf($messagePrefix . INVALID_TCASEID_STR,$tcaseID));
        }  
      }
      elseif ($this->_isTestCaseExternalIDPresent())
      {
        $fromExternal = true;
        $tcaseExternalID = $this->args[self::$testCaseExternalIDParamName]; 
        $tcaseID = intval($this->tcaseMgr->getInternalID($tcaseExternalID));
        $status = $tcaseID > 0 ? true : false;
      
        // Invalid TestCase ID
        if( !$status )
        {
          $this->errors[] = new IXR_Error(INVALID_TESTCASE_EXTERNAL_ID, 
                                sprintf($messagePrefix . INVALID_TESTCASE_EXTERNAL_ID_STR,$tcaseExternalID));                  
        }
      }
      
      if( $status )
      {
        if($this->_isTestCaseIDValid($tcaseID,$messagePrefix))
        {
          $this->_setTestCaseID($tcaseID);  
        }  
        else
        {  
          $status=false;
          if ($fromInternal)
          {
            $this->errors[] = new IXR_Error(INVALID_TCASEID,
                                  sprintf($messagePrefix . INVALID_TCASEID_STR,$tcaseID));
          } 
          elseif ($fromExternal)
          {
            $this->errors[] = new IXR_Error(INVALID_TESTCASE_EXTERNAL_ID, 
                                  sprintf($messagePrefix . INVALID_TESTCASE_EXTERNAL_ID_STR,$tcaseExternalID));
          }
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
   * @param int $args["buildid"] - optional
   * @param int $args["platformid"] - optional  
   * @param int $args["testcaseid"] - optional
   * @param int $args["keywordid"] - optional mutual exclusive with $args["keywords"]
   * @param int $args["keywords"] - optional  mutual exclusive with $args["keywordid"]
   *
   * @param boolean $args["executed"] - optional
   * @param int $args["$assignedto"] - optional
   * @param string $args["executestatus"] - optional
   * @param array $args["executiontype"] - optional
   * @param array $args["getstepinfo"] - optional - default false
   * @param string $args["details"] - optional 
   *                     'full': (default) get summary,steps,expected_results,test suite name
   *                     'simple':
   *                     'details':
   * @return mixed $resultInfo
   *
   * @internal revisions
   * @since 1.9.13
   * 20141230 - franciscom - TICKET 6805: platform parameter
   */
  public function getTestCasesForTestPlan($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
          
    // Optional parameters that are not mutual exclusive, 
    // DEFAULT value to use if parameter was not provided
    $opt=array(self::$testCaseIDParamName => null,self::$buildIDParamName => null,
               self::$keywordIDParamName => null,self::$executedParamName => null,
               self::$assignedToParamName => null,self::$executeStatusParamName => null,
               self::$executionTypeParamName => null,self::$getStepsInfoParamName => false,
               self::$detailsParamName => 'full',self::$platformIDParamName => null);
             
    $optMutualExclusive = array(self::$keywordIDParamName => null,self::$keywordNameParamName => null);   
    $this->_setArgs($args);
    if(!($this->_checkGetTestCasesForTestPlanRequest($msg_prefix) && 
         $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR)) )
    {
      return $this->errors;
    }
      
    $tplanid = $this->args[self::$testPlanIDParamName];
    $tplanInfo = $this->tplanMgr->tree_manager->get_node_hierarchy_info($tplanid);
    
    foreach($opt as $key => $value)
    {
      if($this->_isParamPresent($key))
      {
        $opt[$key]=$this->args[$key];      
      }   
    }
      
    $keywordSet = $opt[self::$keywordIDParamName];
    if( is_null($keywordSet) )
    {
      $keywordSet = null;
      $keywordList = $this->getKeywordSet($tplanInfo['parent_id']);
      if( !is_null($keywordList) )
      {
        $keywordSet = explode(",",$keywordList);
      }
    }

    $options = array('executed_only' => $opt[self::$executedParamName], 
                     'details' => $opt[self::$detailsParamName],
                     'output' => 'mapOfMap' );
            
    $filters = array('tcase_id' => $opt[self::$testCaseIDParamName],
                     'keyword_id' => $keywordSet,
                     'assigned_to' => $opt[self::$assignedToParamName],
                     'exec_status' => $opt[self::$executeStatusParamName],
                     'build_id' => $opt[self::$buildIDParamName],
                     'exec_type' => $opt[self::$executionTypeParamName],
                     'platform_id' => $opt[self::$platformIDParamName]);
      
    $recordset = $this->tplanMgr->getLTCVNewGeneration($tplanid,$filters,$options);

    // Do we need to get Test Case Steps?
    if( !is_null($recordset) && $opt[self::$getStepsInfoParamName] )
    {
      $itemSet = array_keys($recordset);
      switch($options['output'])
      { 
        case 'mapOfArray':
        case 'mapOfMap':
          foreach($itemSet as $itemKey)
          {
            $keySet = array_keys($recordset[$itemKey]);
            $target = &$recordset[$itemKey];
            foreach($keySet as $accessKey)
            {
              $steps = $this->tcaseMgr->get_steps($target[$accessKey]['tcversion_id']);
              $target[$accessKey]['steps'] = $steps;
            }
          }
        break;
        
        case 'array':
        case 'map':
        default:
          foreach($itemSet as $accessKey)
          {
            $sts = $this->tcaseMgr->get_steps($recordset[$accessKey]['tcversion_id']);
            $recordset[$accessKey]['steps'] = $sts;
          } 
        break;
      }
    }

    return $recordset;
  }


  /**
   * Run all the necessary checks to see if a GetTestCasesForTestPlanRequest()
   * can be accepted.
   *
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */
  protected function _checkGetTestCasesForTestPlanRequest($messagePrefix='')
  {
    $status = $this->authenticate();
    if($status)
    {
      $status &=$this->checkTestPlanID($messagePrefix);
          
      if($status && $this->_isTestCaseIDPresent($messagePrefix))
      {
        $status &=$this->_checkTCIDAndTPIDValid(null,$messagePrefix);
      }
      if($status && $this->_isBuildIDPresent($messagePrefix))  
      {
        $status &=$this->checkBuildID($messagePrefix);
      }
    }
    return $status;
  }
  
  /**
   * Gets value of a Custom Field with scope='design' for a given Test case
   *
   * @param struct $args
   * @param string $args["devKey"]: used to check if operation can be done.
   *                                if devKey is not valid => abort.
   *
   * @param string $args["testcaseexternalid"]:  
   * @param string $args["version"]: version number  
   * @param string $args["testprojectid"]: 
   * @param string $args["customfieldname"]: custom field name
   * @param string $args["details"] optional, changes output information
   *                                null or 'value' => just value
   *                                'full' => a map with all custom field definition
   *                                             plus value and internal test case id
   *                                'simple' => value plus custom field name, label, and type (as code).
     *
     * @return mixed $resultInfo
   *         
   * @access public
   */    
  public function getTestCaseCustomFieldDesignValue($args)
  {
    $msg_prefix="(" .__FUNCTION__ . ") - ";
    $this->_setArgs($args);  
    
    $checkFunctions = array('authenticate','checkTestProjectID','checkTestCaseIdentity',
                            'checkTestCaseVersionNumber');
    $status_ok=$this->_runChecks($checkFunctions,$msg_prefix);       

    if( $status_ok )
    {
      $status_ok=$this->_isParamPresent(self::$customFieldNameParamName,$msg_prefix,self::SET_ERROR);
    }
        
        
    if($status_ok)
    {
      $ret = $this->checkTestCaseAncestry();
      $status_ok = $ret['status_ok'];
      if( $status_ok )
      {
        // Check if version number exists for Test Case
        $ret = $this->checkTestCaseVersionNumberAncestry();
        $status_ok = $ret['status_ok'];
      }
            
      if($status_ok )
      {
        $status_ok=$this->_checkGetTestCaseCustomFieldDesignValueRequest($msg_prefix);
      }
      else 
      {
        $this->errors[] = new IXR_Error($ret['error_code'], $msg_prefix . $ret['error_msg']); 
      }           
    }
        
    if($status_ok && $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR))
    {
      $details='value';
      if( $this->_isParamPresent(self::$detailsParamName) )
      {
        $details=$this->args[self::$detailsParamName];  
      }
      
        
      $cf_name=$this->args[self::$customFieldNameParamName];
      $tproject_id=$this->args[self::$testProjectIDParamName];
      $tcase_id=$this->args[self::$testCaseIDParamName];
            
      $cfield_mgr = $this->tprojectMgr->cfield_mgr;
      $cfinfo = $cfield_mgr->get_by_name($cf_name);
      $cfield = current($cfinfo);
      $filters = array('cfield_id' => $cfield['id']);
      $cfieldSpec = $this->tcaseMgr->get_linked_cfields_at_design($tcase_id,$this->tcVersionID,null,$filters,$tproject_id);
            
      switch($details)
      {
        case 'full':
          $retval = $cfieldSpec[$cfield['id']]; 
        break;
           
        case 'simple':
          $retval = array('name' => $cf_name, 'label' => $cfieldSpec[$cfield['id']]['label'], 
                          'type' => $cfieldSpec[$cfield['id']]['type'], 
                          'value' => $cfieldSpec[$cfield['id']]['value']);
        break;
               
        case 'value':
        default:
          $retval=$cfieldSpec[$cfield['id']]['value'];
        break;
              
      }
      return $retval;
    }
    else
    {
      return $this->errors;
    } 
  }
  
    /**
   * Run all the necessary checks to see if GetTestCaseCustomFieldDesignValueRequest()
   * can be accepted.
   *  
     * - Custom Field exists ?
     * - Can be used on a test case ?
     * - Custom Field scope includes 'design' ?
     * - is linked to testproject that owns test case ?
   *
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */
    protected function _checkGetTestCaseCustomFieldDesignValueRequest($messagePrefix='')
  {    
      // $status_ok=$this->authenticate($messagePrefix);
        $cf_name=$this->args[self::$customFieldNameParamName];

        //  $testCaseIDParamName = "testcaseid";
      //  public static $testCaseExternalIDParamName = "testcaseexternalid";
  
        // Custom Field checks:
        // - Custom Field exists ?
        // - Can be used on a test case ?
        // - Custom Field scope includes 'design' ?
        // - is linked to testproject that owns test case ?
        //
 
        // - Custom Field exists ?
        $cfield_mgr=$this->tprojectMgr->cfield_mgr; 
        $cfinfo=$cfield_mgr->get_by_name($cf_name);
        if( !($status_ok=!is_null($cfinfo)) )
        {
           $msg = sprintf(NO_CUSTOMFIELD_BY_THIS_NAME_STR,$cf_name);
           $this->errors[] = new IXR_Error(NO_CUSTOMFIELD_BY_THIS_NAME, $messagePrefix . $msg);
        }
      
        // - Can be used on a test case ?
        if( $status_ok )
        {
            $cfield=current($cfinfo);
            $status_ok = (strcasecmp($cfield['node_type'],'testcase') == 0 );
            if( !$status_ok )
            {
               $msg = sprintf(CUSTOMFIELD_NOT_APP_FOR_NODE_TYPE_STR,$cf_name,'testcase',$cfield['node_type']);
               $this->errors[] = new IXR_Error(CUSTOMFIELD_NOT_APP_FOR_NODE_TYPE, $messagePrefix . $msg);
            }
        }
 
        // - Custom Field scope includes 'design' ?
        if( $status_ok )
        {
            $status_ok = ($cfield['show_on_design'] || $cfield['enable_on_design']);
            if( !$status_ok )
            {
               $msg = sprintf(CUSTOMFIELD_HAS_NOT_DESIGN_SCOPE_STR,$cf_name);
               $this->errors[] = new IXR_Error(CUSTOMFIELD_HAS_NOT_DESIGN_SCOPE, $messagePrefix . $msg);
            }
        }

        // - is linked to testproject that owns test case ?
        if( $status_ok )
        {
            $allCF = $cfield_mgr->get_linked_to_testproject($this->args[self::$testProjectIDParamName]);
            $status_ok=!is_null($allCF) && isset($allCF[$cfield['id']]) ;
            if( !$status_ok )
            {
                $tproject_info = $this->tprojectMgr->get_by_id($this->args[self::$testProjectIDParamName]);
              $msg = sprintf(CUSTOMFIELD_NOT_ASSIGNED_TO_TESTPROJECT_STR,
                             $cf_name,$tproject_info['name'],$this->args[self::$testProjectIDParamName]);
              $this->errors[] = new IXR_Error(CUSTOMFIELD_NOT_ASSIGNED_TO_TESTPROJECT, $messagePrefix . $msg);
            }
             
        }
      
        return $status_ok;
  }



  /**
   * getKeywordSet()
   *  
   * @param int tproject_id
   *            
   * @return string that represent a list of keyword id (comma is character separator)
   *
   * @access protected
   */
  protected function getKeywordSet($tproject_id)
  { 
    $keywordSet = null;
    $kMethod = null;

    if($this->_isParamPresent(self::$keywordNameParamName))
    {
      $kMethod='getValidKeywordSetByName';
      $accessKey=self::$keywordNameParamName;
    }
    else if ($this->_isParamPresent(self::$keywordIDParamName))
    {
      $kMethod='getValidKeywordSetById';
      $accessKey=self::$keywordIDParamName;
    }

    if( !is_null($kMethod) )
    {
      $keywordSet=$this->$kMethod($tproject_id,$this->args[$accessKey]);
    }
      
    return $keywordSet;
  }
  


  /**
   * getValidKeywordSetByName()
   *  
   * @param int $tproject_id
   * @param $keywords array of keywords names
   *
   * @return string that represent a list of keyword id (comma is character separator)
   *
   * @access protected
   */
  protected function getValidKeywordSetByName($tproject_id,$keywords)
  { 
    return $this->getValidKeywordSet($tproject_id,$keywords,true);
  }
  
   /**
    * 
    * @param $tproject_id the testprojectID the keywords belong
    * @param $keywords array of keywords or keywordIDs
    * @param $byName set this to true if $keywords is an array of keywords, false if it's an array of keywordIDs
    * @return string that represent a list of keyword id (comma is character separator)
    */
  protected function getValidKeywordSet($tproject_id,$keywords,$byName,$op=null)
  {
    $keywordSet = '';

    $sql = " SELECT keyword,id FROM {$this->tables['keywords']} " .
           " WHERE testproject_id = {$tproject_id} ";
    
    $keywords = trim($keywords);
    if($keywords != "")
    {
      $a_keywords = explode(",",$keywords);
      $items_qty = count($a_keywords);
      for($idx = 0; $idx < $items_qty; $idx++)
      {
        $a_keywords[$idx] = trim($a_keywords[$idx]);
      }
      $itemSet = implode("','",$a_keywords);

      if ($byName)
      {
        $sql .= " AND keyword IN ('{$itemSet}')";
      }
      else
      {
        $sql .= " AND id IN ({$itemSet})";
      }
    }

    
    $keywordMap = $this->dbObj->fetchRowsIntoMap($sql,'keyword');
    if(!is_null($keywordMap))
    {
      if(is_null($op))
      {
        $a_items = null;
        for($idx = 0; $idx < $items_qty; $idx++)
        {
          if(isset($keywordMap[$a_keywords[$idx]]))
          {
            $a_items[] = $keywordMap[$a_keywords[$idx]]['id'];  
          }
        }
        if( !is_null($a_items))
        {
          $keywordSet = implode(",",$a_items);
        }    
      }  
      else
      {
        foreach($keywordMap as $kw => $elem)
        {
          $keywordSet[$elem['id']] = $elem['keyword'];
        }  
      }  
    }

    return $keywordSet;
   }
   
  /**
   * getValidKeywordSetById()
   *  
   * @param int $tproject_id
   * @param $keywords array of keywords ID
   *
   * @return string that represent a list of keyword id (comma is character separator)
   *
   * @access protected
   */
    protected function  getValidKeywordSetById($tproject_id,$keywords)
    {
      return $this->getValidKeywordSet($tproject_id,$keywords,false);
    }


    /**
   * checks if test case version number is positive integer
   *  
   * @return boolean
   *
   * @access protected
   */
  protected function checkTestCaseVersionNumber()
  {
    $status=true;
    if(!($status=$this->_isParamPresent(self::$versionNumberParamName)))
    {
      $msg = sprintf(MISSING_REQUIRED_PARAMETER_STR,self::$versionNumberParamName);
      $this->errors[] = new IXR_Error(MISSING_REQUIRED_PARAMETER, $msg);              
    }
    else
    {
      $version = $this->args[self::$versionNumberParamName];
      if( !($status = is_int($version)) )
      {
        $msg = sprintf(PARAMETER_NOT_INT_STR,self::$versionNumberParamName,$version);
        $this->errors[] = new IXR_Error(PARAMETER_NOT_INT, $msg);
      }
      else 
      {
        if( !($status = ($version > 0)) )
        {
          $msg = sprintf(VERSION_NOT_VALID_STR,$version);
          $this->errors[] = new IXR_Error(VERSION_NOT_VALID,$msg);
        }
      }
    }
    return $status;
  }

   /**
    * Add a test case version to a test plan 
    *
    * @param args['testprojectid']
    * @param args['testplanid']
    * @param args['testcaseexternalid']
    * @param args['version']
    * @param args['platformid'] - OPTIONAL Only if  test plan has no platforms
    * @param args['executionorder'] - OPTIONAL
    * @param args['urgency'] - OPTIONAL
    * @param args['overwrite'] - OPTIONAL
    *
    */
  public function addTestCaseToTestPlan($args)
  {
    $operation=__FUNCTION__;
    $messagePrefix="({$operation}) - ";
    $this->_setArgs($args);
    
    $op_result=null;
    $additional_fields='';
    $doDeleteLinks = false;
    $doLink = false;
    $hasPlatforms = false;
    $hasPlatformIDArgs = false;
    $platform_id = 0;
    $checkFunctions = array('authenticate','checkTestProjectID',
                            'checkTestCaseVersionNumber',
                            'checkTestCaseIdentity','checkTestPlanID');
    
    $status_ok = $this->_runChecks($checkFunctions,$messagePrefix);

    
    // Test Plan belongs to test project ?
    if( $status_ok )
    {
       $tproject_id = $this->args[self::$testProjectIDParamName];
       $tplan_id = $this->args[self::$testPlanIDParamName];
       $tplan_info = $this->tplanMgr->get_by_id($tplan_id);
       
       $sql=" SELECT id FROM {$this->tables['testplans']}" .
            " WHERE testproject_id={$tproject_id} AND id = {$tplan_id}";         
        
       $rs=$this->dbObj->get_recordset($sql);
    
       if( count($rs) != 1 )
       {
          $status_ok=false;
          $tproject_info = $this->tprojectMgr->get_by_id($tproject_id);
          $msg = sprintf(TPLAN_TPROJECT_KO_STR,$tplan_info['name'],$tplan_id,
                                               $tproject_info['name'],$tproject_id);  
          $this->errors[] = new IXR_Error(TPLAN_TPROJECT_KO,$msg_prefix . $msg); 
       }
                  
    } 
       
    // Test Case belongs to test project ?
    if( $status_ok )
    {
      $ret = $this->checkTestCaseAncestry();
      $status_ok = $ret['status_ok'];
      
      if( !$ret['status_ok'] )
      {
        $this->errors[] = new IXR_Error($ret['error_code'], $msg_prefix . 
                                        $ret['error_msg']); 
        
      }           
    }
        
    // Does this Version number exist for this test case ?     
    if( $status_ok )
    {
      $tcase_id=$this->args[self::$testCaseIDParamName];
      $version_number=$this->args[self::$versionNumberParamName];
      $sql = " SELECT TCV.version,TCV.id " . 
             " FROM {$this->tables['nodes_hierarchy']} NH, {$this->tables['tcversions']} TCV " .
             " WHERE NH.parent_id = {$tcase_id} " .
             " AND TCV.version = {$version_number} " .
             " AND TCV.id = NH.id ";
        
      $target_tcversion=$this->dbObj->fetchRowsIntoMap($sql,'version');
      if( !is_null($target_tcversion) && count($target_tcversion) != 1 )
      {
        $status_ok=false;
        $tcase_info=$this->tcaseMgr->get_by_id($tcase_id);
        $msg = sprintf(TCASE_VERSION_NUMBER_KO_STR,$version_number,$tcase_external_id,$tcase_info[0]['name']);  
        $this->errors[] = new IXR_Error(TCASE_VERSION_NUMBER_KO,$msg_prefix . $msg); 
      }                  
                   
    }     

    if( $status_ok )
    {
      // Optional parameters
      $additional_fields=null;
      $additional_values=null;
      $opt_fields=array(self::$urgencyParamName => 'urgency', self::$executionOrderParamName => 'node_order');
      $opt_values=array(self::$urgencyParamName => null, self::$executionOrderParamName => 1);
      foreach($opt_fields as $key => $field_name)
      {
        if($this->_isParamPresent($key))
        {
          $additional_values[]=$this->args[$key];
          $additional_fields[]=$field_name;              
        }   
        else
        {
          if( !is_null($opt_values[$key]) )
          {
            $additional_values[]=$opt_values[$key];
            $additional_fields[]=$field_name;              
          }
        }
      }
    }

    if( $status_ok )
    {
      // 20100705 - work in progress - BUGID 3564
      // if test plan has platforms, platformid argument is MANDATORY
      $opt = array('outputFormat' => 'mapAccessByID');
      $platformSet = $this->tplanMgr->getPlatforms($tplan_id,$opt);  
      $hasPlatforms = !is_null($platformSet);
      $hasPlatformIDArgs = $this->_isParamPresent(self::$platformIDParamName);
      
      if( $hasPlatforms )
      {
        if( $hasPlatformIDArgs )
        {
          // Check if platform id belongs to test plan
          $platform_id = $this->args[self::$platformIDParamName];
          $status_ok = isset($platformSet[$platform_id]);
          if( !$status_ok )
          {
            $msg = sprintf( PLATFORM_ID_NOT_LINKED_TO_TESTPLAN_STR,$platform_id,$tplan_info['name']);
            $this->errors[] = new IXR_Error(PLATFORM_ID_NOT_LINKED_TO_TESTPLAN, $msg);
          }
        }
        else
        {
          $msg = sprintf(MISSING_PLATFORMID_BUT_NEEDED_STR,$tplan_info['name'],$tplan_id);  
          $this->errors[] = new IXR_Error(MISSING_PLATFORMID_BUT_NEEDED,$msg_prefix . $msg); 
          $status_ok = false;
        }
      }
    }  

    

    if( $status_ok && $this->userHasRight("testplan_planning",self::CHECK_PUBLIC_PRIVATE_ATTR) )
    {
      // 20100711 - franciscom
      // Because for TL 1.9 link is done to test plan + platform, logic used 
      // to understand what to unlink has to be changed.
      // If same version exists on other platforms
      //  just add this new record
      // If other version exists on other platforms
      //  error -> give message to user
      //
      // 
           
      // Other versions must be unlinked, because we can only link ONE VERSION at a time
      // 20090411 - franciscom
      // As implemented today I'm going to unlink ALL linked versions, then if version
      // I'm asking to link is already linked, will be unlinked and then relinked.
      // May be is not wise, IMHO this must be refactored, and give user indication that
      // requested version already is part of Test Plan.
      // 
      $sql = " SELECT TCV.version,TCV.id " . 
             " FROM {$this->tables['nodes_hierarchy']} NH, {$this->tables['tcversions']} TCV " .
             " WHERE NH.parent_id = ". intval($tcase_id) .
             " AND TCV.id = NH.id ";
                 
      $all_tcversions = $this->dbObj->fetchRowsIntoMap($sql,'id');
      $id_set = array_keys($all_tcversions);

      // get records regarding all test case versions linked to test plan  
      $in_clause=implode(",",$id_set);
      $sql = " SELECT tcversion_id, platform_id, PLAT.name FROM {$this->tables['testplan_tcversions']} TPTCV " .
             " LEFT OUTER JOIN {$this->tables['platforms']} PLAT ON PLAT.id = platform_id " . 
             " WHERE TPTCV.testplan_id={$tplan_id} AND TPTCV.tcversion_id IN({$in_clause}) ";

      if( $hasPlatforms )
      {
        $sql .= " AND TPTCV.platform_id=" . intval($platform_id);
      }  

      $rs = $this->dbObj->fetchMapRowsIntoMap($sql,'tcversion_id','platform_id');
      
      $doLink = is_null($rs);
      
      if( !$doLink )
      {
        // Are we going to update ?
        // var_dump($rs);die();
        // echo $target_tcversion[$version_number]['id']; die();
        if( isset($rs[$target_tcversion[$version_number]['id']]) )
        {
          if( $hasPlatforms )
          {
            $plat_keys = array_flip(array_keys($rs[$target_tcversion[$version_number]['id']]));
      
            // need to understand what where the linked platforms.
            $platform_id = $this->args[self::$platformIDParamName];
            $linkExists = isset($plat_keys[$platform_id]);
            $doLink = !$linkExists;
            if( $linkExists )
            {
              $platform_name = $rs[$target_tcversion[$version_number]['id']][$platform_id]['name'];
              $msg = sprintf(LINKED_FEATURE_ALREADY_EXISTS_STR,$tplan_info['name'],$tplan_id,
                             $platform_name, $platform_id);  
              $this->errors[] = new IXR_Error(LINKED_FEATURE_ALREADY_EXISTS,$msg_prefix . $msg); 
              $status_ok = false;
            }
          }  
          else
          {
            // do nothing on silence, and say bye!!!
            $op_result['operation']=$operation;
            $op_result['status']=true;
            $op_result['message']='Nothing to do - already linked';
            return $op_result;
          }     
        }  
        else 
        {
          // Other version than requested done is already linked
          $doLink = false;
          if($this->_isParamPresent(self::$overwriteParamName) && $this->args[self::$overwriteParamName])
          {
            $doLink = $doDeleteLinks = true;
          }

          reset($rs);
          $linked_tcversion = key($rs);          
          $other_version = $all_tcversions[$linked_tcversion]['version'];
          if( !$doLink )
          {
            $doLink = false;
            $msg = sprintf(OTHER_VERSION_IS_ALREADY_LINKED_STR,$other_version,$version_number,
                           $tplan_info['name'],$tplan_id);
            $this->errors[] = new IXR_Error(OTHER_VERSION_IS_ALREADY_LINKED,$msg_prefix . $msg); 
            $status_ok = false;
          }  
        }
        
      }
      
      if( $doLink && $hasPlatforms )
      {
       $additional_values[] = $platform_id;
       $additional_fields[] = 'platform_id';              
      }

      if( $doDeleteLinks )
      {
        // $in_clause=implode(",",$id_set);
        $sql = " DELETE FROM {$this->tables['testplan_tcversions']} " .
               " WHERE testplan_id=" . intval($tplan_id) .
               " AND tcversion_id=" . intval($linked_tcversion);

        if( $hasPlatforms )
        {
          $sql .= " AND platform_id=" . intval($platform_id);
        }     
        $this->dbObj->exec_query($sql);
      }
          
      if( $doLink)
      {  
        $fields="testplan_id,tcversion_id,author_id,creation_ts";
        if( !is_null($additional_fields) )
        {
          $dummy = implode(",",$additional_fields);
          $fields .= ',' . $dummy; 
        }
            
        $sql_values="{$tplan_id},{$target_tcversion[$version_number]['id']}," .
                    "{$this->userID},{$this->dbObj->db_now()}";
        if( !is_null($additional_values) )
        {
          $dummy = implode(",",$additional_values);
          $sql_values .= ',' . $dummy; 
        }
           
        $sql=" INSERT INTO {$this->tables['testplan_tcversions']} ({$fields}) VALUES({$sql_values})"; 
        $this->dbObj->exec_query($sql);

        $op_result['feature_id']=$this->dbObj->insert_id($this->tables['testplan_tcversions']);

      }
      $op_result['operation']=$operation;
      $op_result['status']=true;
      $op_result['message']='';
    }
       
    return ($status_ok ? $op_result : $this->errors);
  }  

  
   /**
    * get set of test suites AT TOP LEVEL of tree on a Test Project
    *
    * @param args['testprojectid']
    *  
    * @return array
    *
    */
   public function getFirstLevelTestSuitesForTestProject($args)
   {
      $msg_prefix="(" .__FUNCTION__ . ") - ";
      $status_ok=true;
      $this->_setArgs($args);

      $checkFunctions = array('authenticate','checkTestProjectID');
      $status_ok=$this->_runChecks($checkFunctions,$msg_prefix);

      if( $status_ok )
      {
        $result = $this->tprojectMgr->get_first_level_test_suites($this->args[self::$testProjectIDParamName]);
        if( is_null($result) )
        {
          $status_ok=false;
          $tproject_info = $this->tprojectMgr->get_by_id($this->args[self::$testProjectIDParamName]);
          $msg=$msg_prefix . sprintf(TPROJECT_IS_EMPTY_STR,$tproject_info['name']);
          $this->errors[] = new IXR_ERROR(TPROJECT_IS_EMPTY,$msg); 
        } 
      }
      return $status_ok ? $result : $this->errors;       
   }
   

   /**
    *  Assign Requirements to a test case 
    *  we can assign multiple requirements.
    *  Requirements can belong to different Requirement Spec
    *         
    *  @param struct $args
    *  @param string $args["devKey"]
    *  @param int $args["testcaseexternalid"]
    *  @param int $args["testprojectid"] 
    *  @param string $args["requirements"] 
    *                array(array('req_spec' => 1,'requirements' => array(2,4)),
    *                array('req_spec' => 3,'requirements' => array(22,42))
    *
    */
  public function assignRequirements($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $status_ok=true;
    $this->_setArgs($args);
    $resultInfo=array();
    $checkFunctions = array('authenticate','checkTestProjectID','checkTestCaseIdentity');       
    $status_ok=$this->_runChecks($checkFunctions,$msg_prefix);

    if( $status_ok )
    {
      $ret = $this->checkTestCaseAncestry();
      $status_ok=$ret['status_ok'];
      if( !$status_ok )
      {
        $this->errors[] = new IXR_Error($ret['error_code'], $msg_prefix . $ret['error_msg']); 
      }           
    }
       
    if( $status_ok )
    {
      $ret = $this->checkReqSpecQuality();
      $status_ok=$ret['status_ok'];
      if( !$status_ok )
      {
        $this->errors[] = new IXR_Error($ret['error_code'], $msg_prefix . $ret['error_msg']); 
      }           
    }
       
    if($status_ok)
    {
      // assignment
      // Note: when test case identity is checked this args key is setted
      //       this does not means that this mut be present on method call.
      //
      $tcase_id=$this->args[self::$testCaseIDParamName];
      foreach($this->args[self::$requirementsParamName] as $item)
      {
        foreach($item['requirements'] as $req_id)
        {
          $this->reqMgr->assign_to_tcase($req_id,$tcase_id,$this->userID);
        }          
      }
      $resultInfo[] = array("operation" => $operation,"status" => true, "id" => -1, 
                            "additionalInfo" => '',"message" => GENERAL_SUCCESS_STR);
    }
        
    return ($status_ok ? $resultInfo : $this->errors);
  }


  /**
   * checks if a test case belongs to test project
   *
   * @param string $messagePrefix used to be prepended to error message
   * 
   * @return map with following keys
   *             boolean map['status_ok']
   *             string map['error_msg']
   *             int map['error_code']
   */
  protected function checkTestCaseAncestry($messagePrefix='')
  {
    $ret = array('status_ok' => true, 'error_msg' => '' , 'error_code' => 0);
    $tproject_id = $this->args[self::$testProjectIDParamName];
    $tcase_id = $this->args[self::$testCaseIDParamName];
    $tcase_tproject_id = $this->tcaseMgr->get_testproject($tcase_id);
      
    if($tcase_tproject_id != $tproject_id)
    {
      $status_ok = false;
      $tcase_info = $this->tcaseMgr->get_by_id($tcase_id);
      $dummy = $this->tcaseMgr->getExternalID($tcase_id); 
      $tcase_external_id = $dummy[0];

      $tproject_info = $this->tprojectMgr->get_by_id($tproject_id);
      $msg = $messagePrefix . 
             sprintf(TCASE_TPROJECT_KO_STR,$tcase_external_id,
                     $tcase_info[0]['name'],
                     $tproject_info['name'],$tproject_id);  

      $ret = array('status_ok' => false, 'error_msg' => $msg , 
                   'error_code' => TCASE_TPROJECT_KO);
    } 
    return $ret;
  }


  /*
   *  checks Quality of requirements spec
   *  checks done on 
   *  Requirements Specification is present on system
   *  Requirements Specification belongs to test project
   * 
   * @return map with following keys
   *             boolean map['status_ok']
   *             string map['error_msg']
   *             int map['error_code']
   */
  protected function checkReqSpecQuality()
  {
      $ret=array('status_ok' => true, 'error_msg' => '' , 'error_code' => 0);
      $tproject_id=$this->args[self::$testProjectIDParamName];
      $nodes_types = $this->tprojectMgr->tree_manager->get_available_node_types();
          
      foreach($this->args[self::$requirementsParamName] as $item)
      {
          // does it exist ?
          $req_spec_id=$item['req_spec'];
          $reqspec_info=$this->reqSpecMgr->get_by_id($req_spec_id);      
          if(is_null($reqspec_info))
          {
              $status_ok=false;
              $msg = sprintf(REQSPEC_KO_STR,$req_spec_id);
              $error_code=REQSPEC_KO;
              break;  
          }       
          
          // does it belongs to test project ?
          $a_path=$this->tprojectMgr->tree_manager->get_path($req_spec_id);
          $req_spec_tproject_id=$a_path[0]['parent_id'];
          if($req_spec_tproject_id != $tproject_id)
          {
              $status_ok=false;
              $tproject_info = $this->tprojectMgr->get_by_id($tproject_id);
              $msg = sprintf(REQSPEC_TPROJECT_KO_STR,$reqspec_info['title'],$req_spec_id,
                                                     $tproject_info['name'],$tproject_id);  
              $error_code=REQSPEC_TPROJECT_KO;
              break;  
          }
          
          // does this specification have requirements ?
          $my_requirements = $this->tprojectMgr->tree_manager->get_subtree_list($req_spec_id,$nodes_types['requirement']);
          $status_ok = (trim($my_requirements) != "");
          if(!$status_ok)
          {
              $msg = sprintf(REQSPEC_IS_EMPTY_STR,$reqspec_info['title'],$req_spec_id);
              $error_code = REQSPEC_IS_EMPTY;
              break;
          }
          
          // if everything is OK, analise requirements
          if( $status_ok )
          {
              $dummy=array_flip(explode(",",$my_requirements));
              foreach($item['requirements'] as $req_id)
              {
                  if( !isset($dummy[$req_id]) )
                  {
                      $status_ok=false;
                      $req_info = $this->reqMgr->get_by_id($req_id,requirement_mgr::LATEST_VERSION);
                      
                      if( is_null($req_info) )
                      {
                          $msg = sprintf(REQ_KO_STR,$req_id);
                          $error_code=REQ_KO;
                      }
                      else 
                      {  
                          $req_info = $req_inf[0];
                          $msg = sprintf(REQ_REQSPEC_KO_STR,$req_info['req_doc_id'],$req_info['title'],$req_id,
                                         $reqspec_info['title'],$req_spec_id);
                          $error_code=REQ_REQSPEC_KO;
                      }
                      break;
                  }      
              }
          }
          
          if( !$status_ok )
          {
              break;
          }
      }

    if(!$status_ok)
    {
      $ret=array('status_ok' => false, 'error_msg' => $msg , 'error_code' => $error_code);                                               
    } 
    return $ret;
  }

  /**
   * Insert record into execution_bugs table
   * @param  int    $executionID   
   * @param  string $bugID
   * @return boolean
   * @access protected
     * contribution by hnishiyama
  **/
  protected function _insertExecutionBug($executionID, $bugID)
  {
    // Check for existence of executionID
    $sql="SELECT id FROM {$this->tables['executions']} WHERE id={$executionID}";
    $rs=$this->dbObj->fetchRowsIntoMap($sql,'id');
    $status_ok = !(is_null($rs) || $bugID == '');    
    if($status_ok)
    {
      $safeBugID=$this->dbObj->prepare_string($bugID);
      $sql="SELECT execution_id FROM {$this->tables['execution_bugs']} " .  
           "WHERE execution_id={$executionID} AND bug_id='{$safeBugID}'";
        
      if( is_null($this->dbObj->fetchRowsIntoMap($sql, 'execution_id')) )
      {
        $sql = "INSERT INTO {$this->tables['execution_bugs']} " .
               "(execution_id,bug_id) VALUES({$executionID},'{$safeBugID}')";
        $result = $this->dbObj->exec_query($sql); 
        $status_ok=$result ? true : false ;
      }
    }
    return $status_ok;
  }


/**
 *  get bugs linked to an execution ID
 * @param  int $execution_id   
 *
 * @return map indexed by bug_id
 */
  protected function _getBugsForExecutionId($execution_id)
  {
    $rs=null;
    if( !is_null($execution_id) && $execution_id <> '' )
    {
        $sql = "SELECT execution_id,bug_id, B.name AS build_name " .
               "FROM {$this->tables['execution_bugs']} ," .
               " {$this->tables['executions']} E, {$this->tables['builds']} B ".
               "WHERE execution_id={$execution_id} " .
               "AND   execution_id=E.id " .
               "AND   E.build_id=B.id " .
               "ORDER BY B.name,bug_id";
        $rs=$this->dbObj->fetchRowsIntoMap($sql,'bug_id');
    }
    return $rs;   
  }


  /**
   * Gets attachments for specified test suite.
   * The attachment file content is Base64 encoded. To save the file to disk in client,
   * Base64 decode the content and write file in binary mode.
   *
   * @param struct $args
   * @param string $args["devKey"] Developer key
   * @param int $args["testsuiteid"]: id of the testsuite
   *
   * @return mixed $resultInfo
   * @author dennis@etern-it.de
   */
  public function getTestSuiteAttachments($args)
  {
    $this->_setArgs($args);
    $attachments=null;
    $checkFunctions = array('authenticate','checkTestSuiteID');
    $status_ok = $this->_runChecks($checkFunctions) &&
                 $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);

    if($status_ok)
    {
      $tsuite_id = $this->args[self::$testSuiteIDParamName];
      $attachmentRepository = tlAttachmentRepository::create($this->dbObj);
      $attachmentInfos = $attachmentRepository->getAttachmentInfosFor($tsuite_id,"nodes_hierarchy");

      if ($attachmentInfos)
      {
        foreach ($attachmentInfos as $attachmentInfo)
        {
          $aID = $attachmentInfo["id"];
          $content = $attachmentRepository->getAttachmentContent($aID, $attachmentInfo);

          if ($content != null)
          {
            $attachments[$aID]["id"] = $aID;
            $attachments[$aID]["name"] = $attachmentInfo["file_name"];
            $attachments[$aID]["file_type"] = $attachmentInfo["file_type"];
            $attachments[$aID]["title"] = $attachmentInfo["title"];
            $attachments[$aID]["date_added"] = $attachmentInfo["date_added"];
            $attachments[$aID]["content"] = base64_encode($content);
          }
        }
      }
    }
    return $status_ok ? $attachments : $this->errors;
  }


/**
 * Gets attachments for specified test case.
 * The attachment file content is Base64 encoded. To save the file to disk in client,
 * Base64 decode the content and write file in binary mode. 
 * 
 * @param struct $args
 * @param string $args["devKey"] Developer key
 * @param int $args["testcaseid"]: optional, if does not is present           
 *                                 testcaseexternalid must be present
 *
 * @param int $args["testcaseexternalid"]: optional, if does not is present           
 *                                         testcaseid must be present
 * 
 * @return mixed $resultInfo
 */
public function getTestCaseAttachments($args)
{
  $this->_setArgs($args);
  $attachments=null;
  $checkFunctions = array('authenticate','checkTestCaseIdentity');       
  $status_ok = $this->_runChecks($checkFunctions) && 
               $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);
  
  if($status_ok)
  {    
    $tcase_id = $this->args[self::$testCaseIDParamName];
    $attachmentRepository = tlAttachmentRepository::create($this->dbObj);
    $attachmentInfos = $attachmentRepository->getAttachmentInfosFor($tcase_id,"nodes_hierarchy");
    
    if ($attachmentInfos)
    {
      foreach ($attachmentInfos as $attachmentInfo)
      {
        $aID = $attachmentInfo["id"];
        $content = $attachmentRepository->getAttachmentContent($aID, $attachmentInfo);
        
        if ($content != null)
        {
          $attachments[$aID]["id"] = $aID;
          $attachments[$aID]["name"] = $attachmentInfo["file_name"];
          $attachments[$aID]["file_type"] = $attachmentInfo["file_type"];
          $attachments[$aID]["title"] = $attachmentInfo["title"];
          $attachments[$aID]["date_added"] = $attachmentInfo["date_added"];
          $attachments[$aID]["content"] = base64_encode($content);
        }
      }
    }
  }
  return $status_ok ? $attachments : $this->errors;
}


  /**
   * update a test suite
   * 
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["testprojectid"] OR string $args["prefix"] 
   * @param string $args["testsuitename"] optional
   * @param string $args["details"] optional
   * @param int $args["parentid"] optional, if do not provided means test suite must be top level.
   * @param int $args["order"] optional. Order inside parent container
   *   
   * @return mixed $resultInfo
   */
  public function updateTestSuite($args)
  {
    $args[self::$actionParamName] = 'update';
    return $this->createTestSuite($args);
  } 

  /**
   * create a test suite
   * 
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["testprojectid"]
   * @param string $args["testsuitename"]
   * @param string $args["details"]
   * @param int $args["parentid"] optional, if do not provided means test suite must be top level.
   * @param int $args["order"] optional. Order inside parent container
   * @param int $args["checkduplicatedname"] optional, default true.
   *                                          will check if there are siblings with same name.
   *
   * @param int $args["actiononduplicatedname"] optional
   *                                            applicable only if $args["checkduplicatedname"]=true
   *                                            what to do if already a sibling exists with same name.
   *   
   * @return mixed $resultInfo
   */
  public function createTestSuite($args)
  {
    $result=array();
    $this->_setArgs($args);
    $action = isset($this->args,self::$actionParamName) ? 
              $this->args[self::$actionParamName] : 'create'; 

    $checkFunctions = array('authenticate','checkTestProjectIdentity');

    switch($action)
    {
      case 'update':
        $operation='updateTestSuite';
        $opt = array(self::$detailsParamName => null,
                     self::$testSuiteNameParamName => null,
                     self::$orderParamName => testsuite::DEFAULT_ORDER,
                     self::$checkDuplicatedNameParamName => testsuite::CHECK_DUPLICATE_NAME,
                     self::$actionOnDuplicatedNameParamName => 'block');
      break;
    
      case 'create';
      default:
        $operation=__FUNCTION__;
        $opt = array(self::$orderParamName => testsuite::DEFAULT_ORDER,
                     self::$checkDuplicatedNameParamName => testsuite::CHECK_DUPLICATE_NAME,
                     self::$actionOnDuplicatedNameParamName => 'block');
        $checkFunctions[] = 'checkTestSuiteName';
      break;
    }

    $msg_prefix="({$operation}) - ";
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);
    
    // When working on PRIVATE containers, globalRole Admin is ENOUGH
    // because this is how TestLink works when this action is done on GUI
    if( $status_ok && $this->user->globalRole->dbID != TL_ROLES_ADMIN)
    {
      $status_ok = FALSE;
      if( $this->userHasRight("mgt_modify_tc",self::CHECK_PUBLIC_PRIVATE_ATTR) )
      {
        $status_ok = true;
      }  
    }  

    if( $status_ok )
    {
      // Needed After refactoring to use checkTestProjectIdentity()
      $key = self::$testProjectIDParamName; 
      $args[$key] = $this->args[$key];  

      // Optional parameters
      foreach($opt as $key => $value)
      {
        if($this->_isParamPresent($key))
        {
          $opt[$key]=$this->args[$key];      
        }   
      }
    }

    if($status_ok)
    {
      $parent_id = $args[self::$testProjectIDParamName];  
      $tprojectInfo = $this->tprojectMgr->get_by_id($args[self::$testProjectIDParamName]);
      
      $tsuiteMgr = new testsuite($this->dbObj);
      if( $this->_isParamPresent(self::$parentIDParamName) )
      {
        $parent_id = $args[self::$parentIDParamName];

        // if parentid exists it must:
        // be a test suite id 
        $node_info = $tsuiteMgr->get_by_id($args[self::$parentIDParamName]);
        if( !($status_ok=!is_null($node_info)) )
        {
          $msg=sprintf(INVALID_PARENT_TESTSUITEID_STR,
          $args[self::$parentIDParamName],$args[self::$testSuiteNameParamName]);
          $this->errors[] = new IXR_Error(INVALID_PARENT_TESTSUITEID,$msg_prefix . $msg);
        }
              
        if($status_ok)
        {
          // Must belong to target test project
          $root_node_id=$tsuiteMgr->getTestProjectFromTestSuite($args[self::$parentIDParamName],null);
           
          if( !($status_ok = ($root_node_id == $args[self::$testProjectIDParamName])) )
          {
            $msg=sprintf(TESTSUITE_DONOTBELONGTO_TESTPROJECT_STR,$args[self::$parentIDParamName],
                         $tprojectInfo['name'],$args[self::$testProjectIDParamName]);
            $this->errors[] = new IXR_Error(TESTSUITE_DONOTBELONGTO_TESTPROJECT,$msg_prefix . $msg);
          }
        }
      } 
    }
     
    if($status_ok)
    {
      switch($action)
      {
        case 'update':
          $op=$tsuiteMgr->update($args[self::$testSuiteIDParamName],
                                 $args[self::$testSuiteNameParamName],
                                 $args[self::$detailsParamName],
                                 $parent_id,
                                 $opt[self::$orderParamName]);

          /*
                                 $opt[self::$checkDuplicatedNameParamName],
                                 $opt[self::$actionOnDuplicatedNameParamName]);
           */
        break;

        case 'create':
        default:
          $op=$tsuiteMgr->create($parent_id,$args[self::$testSuiteNameParamName],
                                 $args[self::$detailsParamName],$opt[self::$orderParamName],
                                 $opt[self::$checkDuplicatedNameParamName],
                                 $opt[self::$actionOnDuplicatedNameParamName]);
        break;  
      }


      if( ($status_ok = $op['status_ok']) )
      {
        $op['status'] = $op['status_ok'] ? true : false;
        $op['operation'] = $operation;
        $op['additionalInfo'] = '';
        $op['message'] = $op['msg'];
        unset($op['msg']);
        unset($op['status_ok']);
        $result[]=$op;  
      }
      else
      {
        // @TODO needs refactoring for UPDATE action
        $op['msg'] = sprintf($op['msg'],$args[self::$testSuiteNameParamName]);
        $this->errors=$op;   
      }
    }
      
    return $status_ok ? $result : $this->errors;
  }


  /**
   * test suite name provided is valid 
   * 
   * @param string $messagePrefix used to be prepended to error message
     *
   * @return boolean
   * @access protected
   */        
    protected function checkTestSuiteName($messagePrefix='')
    {
        $status_ok=isset($this->args[self::$testSuiteNameParamName]) ? true : false;
        if($status_ok)
        {
            $name = $this->args[self::$testSuiteNameParamName];
            if(!is_string($name))
            {
                $msg=$messagePrefix . TESTSUITENAME_NOT_STRING_STR;
              $this->errors[] = new IXR_Error(TESTSUITENAME_NOT_STRING, $msg);
              $status_ok=false;
            }
        }
        else
        {
             $this->errors[] = new IXR_Error(NO_TESTSUITENAME, $messagePrefix . NO_TESTSUITENAME_STR);
        }
        return $status_ok;
    }




    /**
     * Gets info about target test project
     *
     * @param struct $args
     * @param string $args["devKey"]
     * @param string $args["testprojectname"]     
     * @return mixed $resultInfo      
     * @access public
     */    
    public function getTestProjectByName($args)
    {
      $msg_prefix = "(" .__FUNCTION__ . ") - ";
      $status_ok = false;
      $this->_setArgs($args);    

      if($this->authenticate() && 
         $this->_isParamPresent(self::$testProjectNameParamName,$msg_prefix,self::SET_ERROR))
      {
        $op = $this->helperGetTestProjectByName($msg_prefix);
        $status_ok = $op['status_ok'];
      }
      return $status_ok ? $op['info'] : $this->errors;
    }


    /**
     * Gets info about target test project
     *
     * @param struct $args
     * @param string $args["devKey"]
     * @param string $args["testprojectname"]     
     * @param string $args["testplanname"]     
     * @return mixed $resultInfo      
     * @access public
     */    
    public function getTestPlanByName($args)
    {
        $msg_prefix="(" .__FUNCTION__ . ") - ";
         $status_ok=true;
      $this->_setArgs($args);    
      if($this->authenticate())
      {
            $keys2check = array(self::$testPlanNameParamName,
                                self::$testProjectNameParamName);
            foreach($keys2check as $key)
            {
                $names[$key]=$this->_isParamPresent($key,$msg_prefix,self::SET_ERROR) ? trim($this->args[$key]) : '';
                if($names[$key]=='')
                {
                    $status_ok=false;    
                    break;
                }
            }
        }
      
      if($status_ok)
      {
            // need to check name existences
            $name=$names[self::$testProjectNameParamName];
            $check_op=$this->tprojectMgr->checkNameExistence($name);
            $not_found=$check_op['status_ok'];     
            $status_ok=!$not_found;
            if($not_found)      
            {
                $status_ok=false;
                $msg = $msg_prefix . sprintf(TESTPROJECTNAME_DOESNOT_EXIST_STR,$name);
                $this->errors[] = new IXR_Error(TESTPROJECTNAME_DOESNOT_EXIST, $msg);
            }
          else
          {
              $tprojectInfo=current($this->tprojectMgr->get_by_name($name));
          }
      }
      
      if($status_ok)
      {
          $name=trim($names[self::$testPlanNameParamName]);
            $info = $this->tplanMgr->get_by_name($name,$tprojectInfo['id']);
            if( !($status_ok=!is_null($info)) )
            {
                $msg = $msg_prefix . sprintf(TESTPLANNAME_DOESNOT_EXIST_STR,$name,$tprojectInfo['name']);
                $this->errors[] = new IXR_Error(TESTPLANNAME_DOESNOT_EXIST, $msg);
            
            }
        }

        return $status_ok ? $info : $this->errors;
    }


/**
* get test case specification using external ir internal id
* 
* @param struct $args
* @param string $args["devKey"]
* @param int $args["testcaseid"]: optional, if does not is present           
*                                 testcaseexternalid must be present
*
* @param int $args["testcaseexternalid"]: optional, if does not is present           
*                                         testcaseid must be present
* @param int $args["version"]: optional, if does not is present max version number will be
*                                        retuned
*
* @return mixed $resultInfo
*/
public function getTestCase($args)
{
  $msg_prefix="(" .__FUNCTION__ . ") - ";
  $status_ok=true;
  $this->_setArgs($args);
    
  $checkFunctions = array('authenticate','checkTestCaseIdentity');       
  $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);
  // && 
  //             $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);       
  $version_id=testcase::LATEST_VERSION;
  $version_number=-1;

  if( $status_ok )
  {      
    // check optional arguments
    if( $this->_isParamPresent(self::$versionNumberParamName) )
    {
      if( ($status_ok=$this->checkTestCaseVersionNumber()) )
      {
        $version_id=null;
        $version_number=$this->args[self::$versionNumberParamName];
      }
    }
  }
    
  if( $status_ok )
  {      
    $testCaseMgr = new testcase($this->dbObj);
    $id=$this->args[self::$testCaseIDParamName];

    // $result = $testCaseMgr->get_by_id($id,$version_id,'ALL','ALL',$version_number);            
    $filters = array('active_status' => 'ALL', 'open_status' => 'ALL', 'version_number' => $version_number);

    $result = $testCaseMgr->get_by_id($id,$version_id,$filters);            
    // return $result;

    if(0 == sizeof($result))
    {
      $status_ok=false;
      $this->errors[] = new IXR_ERROR(NO_TESTCASE_FOUND,$msg_prefix . NO_TESTCASE_FOUND_STR);
      return $this->errors;
    }
    else
    {
      if( isset($this->args[self::$testCaseExternalIDParamName]) )
      {
        $result[0]['full_tc_external_id']=$this->args[self::$testCaseExternalIDParamName];
      }
      else
      {
        $dummy = $this->tcaseMgr->getPrefix($id);
        $result[0]['full_tc_external_id'] = $dummy[0] . config_get('testcase_cfg')->glue_character .
                                            $result[0]['tc_external_id'];
      }
    }
  }



  if( $status_ok )
  {
    // before returning info need to understand if test case belongs to a test project
    // accessible to user requesting info
    // return $result[0]['id'];
    $this->args[self::$testProjectIDParamName] = $this->tcaseMgr->get_testproject($result[0]['id']);
    $status_ok = $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);
  }  
  return $status_ok ? $result : $this->errors; 
}



  /**
   * create a test plan
   * 
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["testplanname"]
   * @param int $args["testprojectname"] use instead of $args["prefix"]
   * @param int $args["prefix"]          use instead of $args["testprojectname"] 
   * @param string $args["notes"], optional
   * @param string $args["active"], optional default value 1
   * @param string $args["public"], optional default value 1
   *   
   * @return mixed $resultInfo
   * @internal revisions
   */
  public function createTestPlan($args)
  {
    $this->_setArgs($args);
    $status_ok = false;    
    $msg_prefix="(" . __FUNCTION__ . ") - ";

    if($this->authenticate())
    {
      $keys2check = array(self::$testPlanNameParamName);
      $status_ok = true;
      foreach($keys2check as $key)
      {
        $dummy[$key] = $this->_isParamPresent($key,$msg_prefix,self::SET_ERROR) ? 
                       trim($this->args[$key]) : '';
        if($dummy[$key]=='')
        {
          $status_ok=false;    
          break;
        }
      }
    }
    
    if( $status_ok )
    {
      $keys2check = array(self::$testProjectNameParamName,self::$prefixParamName);
      $status_ok = true;
      foreach($keys2check as $key)
      {
        $target[$key] = $this->_isParamPresent($key,$msg_prefix) ? 
                        trim($this->args[$key]) : '';
        if($target[$key] == '')
        {
          $status_ok = false;    
        }
        else
        {
          // first good match is OK
          $status_ok = true;
          break;
        }  
      }

      if($status_ok == false)
      {
        // lazy way to generate error
        foreach($keys2check as $key)
        {
          $dummy[$key] = $this->_isParamPresent($key,$msg_prefix) ? 
                         trim($this->args[$key]) : '';
          if($dummy[$key] == '')
          {
            $status_ok = false;
            break;   
          }
        }
      }  
    }

    if( $status_ok )
    {
      $status_ok = false;

      if( isset($target[self::$testProjectNameParamName]) &&
          $target[self::$testProjectNameParamName] != '' )
      {
        $name = trim($this->args[self::$testProjectNameParamName]);
        $check_op = $this->tprojectMgr->checkNameExistence($name);
        $status_ok = !$check_op['status_ok'];     
        if($status_ok) 
        {
          $tprojectInfo = current($this->tprojectMgr->get_by_name($name));
        }
        else     
        {
          $status_ok=false;
          $msg = $msg_prefix . sprintf(TESTPROJECTNAME_DOESNOT_EXIST_STR,$name);
          $this->errors[] = new IXR_Error(TESTPROJECTNAME_DOESNOT_EXIST, $msg);
        }
      }
      else
      {

        if( isset($target[self::$prefixParamName]) &&
            $target[self::$prefixParamName] != '' )
        {
          $prefix = trim($this->args[self::$prefixParamName]);
          $tprojectInfo = $this->tprojectMgr->get_by_prefix($prefix);
          
          if( ($status_ok = !is_null($tprojectInfo)) == false )
          {  
            $msg = $msg_prefix . sprintf(TPROJECT_PREFIX_DOESNOT_EXIST_STR,$prefix);
            $this->errors[] = new IXR_Error(TPROJECT_PREFIX_DOESNOT_EXIST, $msg);
          }
        }
      }  
    }

    // Now we need to check if user has rights to do this action
    if( $status_ok )
    {
      $this->args[self::$testProjectIDParamName] = $tprojectInfo['id'];
      $this->args[self::$testPlanIDParamName] = null;

      // When working on PRIVATE containers, globalRole Admin is ENOUGH
      // because this is how TestLink works when this action is done on GUI
      if( $this->user->globalRole->dbID != TL_ROLES_ADMIN)
      {
        $status_ok = $this->userHasRight("mgt_testplan_create",self::CHECK_PUBLIC_PRIVATE_ATTR);
      }
    }  

    if( $status_ok )
    {
      $name = trim($this->args[self::$testPlanNameParamName]);
      $info = $this->tplanMgr->get_by_name($name,$tprojectInfo['id']);
      $status_ok=is_null($info);
            
      if( !($status_ok=is_null($info)))
      {
        $msg = $msg_prefix . sprintf(TESTPLANNAME_ALREADY_EXISTS_STR,$name,$tprojectInfo['name']);
        $this->errors[] = new IXR_Error(TESTPLANNAME_ALREADY_EXISTS, $msg);
      }
    }

    if( $status_ok )
    {
      $keys2check = array(self::$activeParamName => 1,self::$publicParamName => 1,self::$noteParamName => '');
      foreach($keys2check as $key => $value)
      {
        $optional[$key]=$this->_isParamPresent($key) ? trim($this->args[$key]) : $value;
      }
      $retval = $this->tplanMgr->create(htmlspecialchars($name),
                                        htmlspecialchars($optional[self::$noteParamName]),
                                        $tprojectInfo['id'],$optional[self::$activeParamName],
                                        $optional[self::$publicParamName]);

      $resultInfo = array();
      $resultInfo[]= array("operation" => __FUNCTION__,"additionalInfo" => null,
                           "status" => true, "id" => $retval, "message" => GENERAL_SUCCESS_STR);
    }

    return $status_ok ? $resultInfo : $this->errors;
  } 


  /**
   * Gets full path from the given node till the top using nodes_hierarchy_table
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param mixed $args["nodeID"] can be just a single node or an array of INTERNAL (DB) ID
   * @return mixed $resultInfo      
   * @access public
   *
   * @internal revision
   * BUGID 3993
   * $args["nodeID"] can be just a single node or an array
   * when path can not be found same date structure will be returned, that on situations
   * where all is ok, but content for KEY(nodeID) will be NULL instead of rising ERROR  
   *
   */    
  public function getFullPath($args)
  {
      $this->_setArgs($args);
      $operation=__FUNCTION__;
      $msg_prefix="({$operation}) - ";
      $checkFunctions = array('authenticate');
      $status_ok=$this->_runChecks($checkFunctions,$msg_prefix) && 
                 $this->_isParamPresent(self::$nodeIDParamName,$msg_prefix,self::SET_ERROR) ;
    
      if( $status_ok )
      {
          $nodeIDSet = $this->args[self::$nodeIDParamName];
          
          // if is array => OK
          if( !($workOnSet = is_array($nodeIDSet)) && (!is_int($nodeIDSet) || $nodeIDSet <= 0) )
        {
              $msg = $msg_prefix . sprintf(NODEID_INVALID_DATA_TYPE);
              $this->errors[] = new IXR_Error(NODEID_INVALID_DATA_TYPE, $msg);
              $status_ok=false;
          } 
          
          if( $status_ok && $workOnSet)
          {
            // do check on each item on set
            foreach($nodeIDSet as $itemID)
            {
              if(!is_int($itemID) || $itemID <= 0) 
        {
                  $msg = $msg_prefix . sprintf(NODEID_IS_NOT_INTEGER_STR,$itemID);
              $this->errors[] = new IXR_Error(NODEID_IS_NOT_INTEGER, $msg);
              $status_ok=false;
          } 
      }
          }
          
      }
      
      if( $status_ok )
      {
        // IMPORTANT NOTICE:
        // (may be a design problem but ..)
        // If $nodeIDSet is an array and for one of items path can not be found
        // get_full_path_verbose() returns null, no matter if for other items
        // information is available
        // 
          $full_path = $this->tprojectMgr->tree_manager->get_full_path_verbose($nodeIDSet);
    }
      return $status_ok ? $full_path : $this->errors;
  }

    /**
    * 
     *
     */
  protected function _insertCustomFieldExecValues($executionID)
  {
    // // Check for existence of executionID   
    $status_ok=true;
    $sql="SELECT id FROM {$this->tables['executions']} WHERE id={$executionID}";
    $rs=$this->dbObj->fetchRowsIntoMap($sql,'id');
    // 
        $cfieldSet=$this->args[self::$customFieldsParamName];
        $tprojectID=$this->tcaseMgr->get_testproject($this->args[self::$testCaseIDParamName]);
        $tplanID=$this->args[self::$testPlanIDParamName];
        $cfieldMgr=$this->tprojectMgr->cfield_mgr;        
        $cfieldsMap = $cfieldMgr->get_linked_cfields_at_execution($tprojectID, 1,'testcase',
                                                                  null,null,null,'name');
        $status_ok = !(is_null($rs) || is_null($cfieldSet) || count($cfieldSet) == 0);    
        $cfield4write = null;
        if( $status_ok && !is_null($cfieldsMap) )
        {
          foreach($cfieldSet as $name => $value)
          {
               if( isset($cfieldsMap[$name]) )
               {
                 $cfield4write[$cfieldsMap[$name]['id']] = array("type_id"  => $cfieldsMap[$name]['type'],
                                                              "cf_value" => $value);
             }
             }  
             if( !is_null($cfield4write) )
             {
               $cfieldMgr->execution_values_to_db($cfield4write,$this->tcVersionID,$executionID,$tplanID,
                                                    null,'write-through');
             }
        }        
    return $status_ok;
  }



   /**
   * delete an execution
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["executionid"]
   *
   * @return mixed $resultInfo 
   *         [status]  => true/false of success
   *         [id]      => result id or error code
   *         [message]  => optional message for error message string
   * @access public
   */  
  public function deleteExecution($args)
  {    
    $resultInfo = array();
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";

    $this->_setArgs($args);              
    $resultInfo[0]["status"] = false;
    
    $checkFunctions = array('authenticate','checkExecutionID');       
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);       
  
    // missing :
    // we need to get Context => Test plan & Test project to understand if 
    // user has the right to do this operation
    if($status_ok)
    {      
      if( $this->userHasRight("exec_delete",self::CHECK_PUBLIC_PRIVATE_ATTR) )  
      {
        $this->tcaseMgr->deleteExecution($args[self::$executionIDParamName]);      
        $resultInfo[0]["status"] = true;
        $resultInfo[0]["id"] = $args[self::$executionIDParamName];  
        $resultInfo[0]["message"] = GENERAL_SUCCESS_STR;
        $resultInfo[0]["operation"] = $operation;
      }
      else
      {
        $status_ok = false;
        $this->errors[] = new IXR_Error(CFG_DELETE_EXEC_DISABLED,CFG_DELETE_EXEC_DISABLED_STR);
      }
    }

    return $status_ok ? $resultInfo : $this->errors;
  }

  /**
   * Helper method to see if an execution id exists on DB
   * no checks regarding other data like test case , test plam, build, etc are done
   * 
   * 
   *   
   * @return boolean
   * @access protected
   */        
    protected function checkExecutionID($messagePrefix='',$setError=false)
    {
      $pname = self::$executionIDParamName;
      $status_ok = $this->_isParamPresent($pname,$messagePrefix,$setError);
      if(!$status_ok)
      {    
        $msg = $messagePrefix . sprintf(MISSING_REQUIRED_PARAMETER_STR, $pname);
        $this->errors[] = new IXR_Error(MISSING_REQUIRED_PARAMETER, $msg);              
      } 
      else
      {
        $status_ok = is_int($this->args[$pname]) && $this->args[$pname] > 0;
        if( !$status_ok )
        {
          $msg = $messagePrefix . sprintf(PARAMETER_NOT_INT_STR,$pname,$this->args[$pname]);
          $this->errors[] = new IXR_Error(PARAMETER_NOT_INT, $msg);
        }
        else
        {
        
        }
      }
      return $status_ok;
    }



  /**
   * Helper method to see if the platform identity provided is valid 
   * This is the only method that should be called directly to check platform identity
   *   
   * If everything OK, platform id is setted.
   *
   * @param int $tplanID Test Plan ID
   * @param map $platformInfo key: platform ID
   * @param string $messagePrefix used to be prepended to error message
   *
   *
   * @return boolean
   * @access protected
   */    
    protected function checkPlatformIdentity($tplanID,$platformInfo=null,$messagePrefix='')
    {
      $status=true;
      $platformID=0;

      $name_exists = $this->_isParamPresent(self::$platformNameParamName,$messagePrefix);
      $id_exists = $this->_isParamPresent(self::$platformIDParamName,$messagePrefix);
      $status = $name_exists | $id_exists;

      if(!$status)
      {
        $pname = self::$platformNameParamName . ' OR ' . self::$platformIDParamName; 
        $msg = $messagePrefix . sprintf(MISSING_REQUIRED_PARAMETER_STR, $pname);
        $this->errors[] = new IXR_Error(MISSING_REQUIRED_PARAMETER, $msg);              
      }        
       
        
      if($status)
      {
        // get test plan name is useful for error messages
        $tplanInfo = $this->tplanMgr->get_by_id($tplanID);
        if(is_null($platformInfo))
        {
          $platformInfo = $this->tplanMgr->getPlatforms($tplanID,array('outputFormat' => 'map'));  
        }

        if(is_null($platformInfo))
        {
          $status = false;
          $msg = sprintf($messagePrefix . TESTPLAN_HAS_NO_PLATFORMS_STR,$tplanInfo['name']);
          $this->errors[] = new IXR_Error(TESTPLAN_HAS_NO_PLATFORMS, $msg);
        }
            
      }
         
      if( $status )
      {
        $platform_name = null;
        $platform_id = null;
        if($name_exists)
        { 
          $this->errors[]=$platformInfo;
          $platform_name = $this->args[self::$platformNameParamName];
          $status = in_array($this->args[self::$platformNameParamName],$platformInfo);
        }
        else
        {
          $platform_id = $this->args[self::$platformIDParamName];
          $status = isset($platformInfo[$this->args[self::$platformIDParamName]]);
        }
            
        if( !$status )
        {
          // Platform does not exist in target testplan
          // Can I Try to understand if platform exists on test project ?
          $msg = sprintf($messagePrefix . PLATFORM_NOT_LINKED_TO_TESTPLAN_STR,
                                 $platform_name,$platform_id,$tplanInfo['name']);
          $this->errors[] = new IXR_Error(PLATFORM_NOT_LINKED_TO_TESTPLAN, $msg);
        }  
      }

      if($status)
      {
        if($name_exists)
        { 
          $dummy = array_flip($platformInfo);
          $this->args[self::$platformIDParamName] = $dummy[$this->args[self::$platformNameParamName]];
        }
      }
      return $status;
    }   



   /**
     * update result of LASTE execution
     *
     * @param
     * @param struct $args
     * @param string $args["devKey"]
     * @param int $args["testplanid"]
     * @param int $args["platformid"]
     * @param int $args["buildid"]
     * @param int $args["testcaseid"] internal ID
     * @param string $args["status"]
     * @param string $args["notes"]
     *
     * @return mixed $resultInfo
     * 
     * @access protected
     */

  protected function _updateResult($user_id=null,$exec_ts=null)
  {
    $tester_id =  is_null($user_id) ? $this->userID : $user_id;
    $execTimeStamp = is_null($exec_ts) ? $this->dbObj->db_now() : $exec_ts;

    $exec_id = 0;
    $status = $this->args[self::$statusParamName];

    // $platform_id = 0;  // hmm here I think we have an issue
    // $testplan_id =  $this->args[self::$testPlanIDParamName];
    // $build_id = $this->args[self::$buildIDParamName];

    $tcversion_id =  $this->tcVersionID;
    $tcase_id = $this->args[self::$testCaseIDParamName];

    $execContext = array('tplan_id' => $this->args[self::$testPlanIDParamName],
                         'platform_id' => $this->args[self::$platformIDParamName],
                         'build_id' => $this->args[self::$buildIDParamName]);
    
    // $db_now=$this->dbObj->db_now();
    
    if( isset($this->args[self::$platformIDParamName]) )
    {
      $platform_id = $this->args[self::$platformIDParamName];   
    }

    // Here steps and expected results are not needed => do not request => less data on network
    // $options = array('getSteps' => 0);
    $opt = array('output' => 'exec_id');
    $exec_id = $this->tcaseMgr->getLatestExecSingleContext(array('id' => $tcase_id, 'version_id' => null),
                                                           $execContext, $opt);
    if( !is_null($exec_id) )
    {
      $execution_type = constant("TESTCASE_EXECUTION_TYPE_AUTO");
      $notes = '';
      $notes_update = '';
      
      if($this->_isNotePresent())
      {
        $notes = $this->dbObj->prepare_string($this->args[self::$noteParamName]);
      }
      
      if(trim($notes) != "")
      {
        $notes_update = ",notes='{$notes}'";  
      }

      $duration_update = '';
      if( isset($this->args[self::$executionDurationParamName]) )
      {
        $duration_update = ",execution_duration=" . 
          floatval($this->args[self::$executionDurationParamName]);  
      }
        

      $sql = " UPDATE {$this->tables['executions']} " .
             " SET tester_id={$tester_id}, execution_ts={$execTimeStamp}," . 
             " status='{$status}', execution_type= {$execution_type} " . 
             " {$notes_update} {$duration_update} WHERE id = {$exec_id}";
      
      $this->dbObj->exec_query($sql);
    }
    return $exec_id;
  }  

   /**
     * Return a TestSuite by ID
     *
     * @param
     * @param struct $args
     * @param string $args["devKey"]
     * @param int $args["testsuiteid"]
     * @return mixed $resultInfo
     * 
     * @access public
     */
    public function getTestSuiteByID($args)
    { 
        $operation=__FUNCTION__;
        $msg_prefix="({$operation}) - ";

        $this->_setArgs($args);
        $status_ok=$this->_runChecks(array('authenticate','checkTestSuiteID'),$msg_prefix);

        $details='simple';
        $key2search=self::$detailsParamName;
        if( $this->_isParamPresent($key2search) )
        { 
            $details=$this->args[$key2search];
        }

        if($status_ok && $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR))
        { 
            $testSuiteID = $this->args[self::$testSuiteIDParamName];
            $tsuiteMgr = new testsuite($this->dbObj);
            return $tsuiteMgr->get_by_id($testSuiteID);

        }
        else
        { 
            return $this->errors;
        }
    }

  /**
   * get list of TestSuites which are DIRECT children of a given TestSuite
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["testsuiteid"]
   * @return mixed $resultInfo
   *
   * @access public
   */
  public function getTestSuitesForTestSuite($args)
  {
      $operation=__FUNCTION__;
      $msg_prefix="({$operation}) - ";
      $items = null;
  
      $this->_setArgs($args);
      $status_ok = $this->_runChecks(array('authenticate','checkTestSuiteID'),$msg_prefix) && 
                   $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);
      if( $status_ok )
      {
          $testSuiteID = $this->args[self::$testSuiteIDParamName];
          $tsuiteMgr = new testsuite($this->dbObj);
          $items = $tsuiteMgr->get_children($testSuiteID);
      }
      return $status_ok ? $items : $this->errors;
  }


  /**
     * Returns the list of platforms associated to a given test plan
     *
     * @param
     * @param struct $args
     * @param string $args["devKey"]
     * @param int $args["testplanid"]
     * @return mixed $resultInfo
     * 
     * @access public
     */
  public function getTestPlanPlatforms($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $this->_setArgs($args);  
    $status_ok = false;
    $items = null;
    
    // Checks if a test plan id was provided
    $status_ok = $this->_isParamPresent(self::$testPlanIDParamName,$msg_prefix,self::SET_ERROR);
    
    if($status_ok)
    {
      // Checks if the provided test plan id is valid
      $status_ok=$this->_runChecks(array('authenticate','checkTestPlanID'),$msg_prefix);
    }
        if($status_ok)
        {
      $tplanID = $this->args[self::$testPlanIDParamName];
          // get test plan name is useful for error messages
      $tplanInfo = $this->tplanMgr->get_by_id($tplanID);
          $items = $this->tplanMgr->getPlatforms($tplanID);  
            if(! ($status_ok = !is_null($items)) )
            {
           $msg = sprintf($messagePrefix . TESTPLAN_HAS_NO_PLATFORMS_STR,$tplanInfo['name']);
           $this->errors[] = new IXR_Error(TESTPLAN_HAS_NO_PLATFORMS, $msg);
            }
        }
      return $status_ok ? $items : $this->errors;
    }   

  /**
   * Gets the summarized results grouped by platform.
   * @see 
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["tplanid"] test plan id
   *
   * @return map where every element has:
   *
   *  'type' => 'platform'
   *  'total_tc => ZZ
   *  'details' => array ( 'passed' => array( 'qty' => X)
   *                       'failed' => array( 'qty' => Y)
   *                       'blocked' => array( 'qty' => U)
   *                       ....)
   *
   * @access public
   */
  public function getTotalsForTestPlan($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $total = null;
    
    $this->_setArgs($args);
    $status_ok=true;

    // Checks are done in order
    $checkFunctions = array('authenticate','checkTestPlanID');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix) && 
                 $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);

    if( $status_ok )
    {
      // $total = $this->tplanMgr->getStatusTotalsByPlatform($this->args[self::$testPlanIDParamName]);
      $total = $this->tplanMetricsMgr->getExecCountersByPlatformExecStatus($this->args[self::$testPlanIDParamName]);
    }

    return $status_ok ? $total : $this->errors;
  }



  /**
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["user"] user name
   *
   * @return true if everything OK, otherwise error structure
   *
   * @access public
   */
  public function doesUserExist($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $this->_setArgs($args);
            
    $user_id = tlUser::doesUserExist($this->dbObj,$this->args[self::$userParamName]);          
    if( !($status_ok = !is_null($user_id)) )
    {
      $msg = $msg_prefix . sprintf(NO_USER_BY_THIS_LOGIN_STR,$this->args[self::$userParamName]);
      $this->errors[] = new IXR_Error(NO_USER_BY_THIS_LOGIN, $msg);  
    }
    return $status_ok ? $status_ok : $this->errors;
  }


  /**
   * check if Developer Key exists.
   *
   * @param struct $args
   * @param string $args["devKey"]
   *
   * @return true if everything OK, otherwise error structure
   *
   * @access public
   */
  public function checkDevKey($args)
  {
      $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $this->_setArgs($args);
      $checkFunctions = array('authenticate');
      $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);
      return $status_ok ? $status_ok : $this->errors;        
  }


/**
 * Uploads an attachment for a Requirement Specification.
 * 
 * The attachment content must be Base64 encoded by the client before sending it.
 * 
 * @param struct $args
 * @param string $args["devKey"] Developer key
 * @param int $args["reqspecid"] The Requirement Specification ID
 * @param string $args["title"] (Optional) The title of the Attachment 
 * @param string $args["description"] (Optional) The description of the Attachment
 * @param string $args["filename"] The file name of the Attachment (e.g.:notes.txt)
 * @param string $args["filetype"] The file type of the Attachment (e.g.: text/plain)
 * @param string $args["content"] The content (Base64 encoded) of the Attachment
 * 
 * @since 1.9beta6
 * @return mixed $resultInfo an array containing the fk_id, fk_table, title, 
 * description, file_name, file_size and file_type. If any errors occur it 
 * returns the error map.
 */
public function uploadRequirementSpecificationAttachment($args)
{
  $msg_prefix = "(" .__FUNCTION__ . ") - ";
  $args[self::$foreignKeyTableNameParamName] = 'req_specs';
  $args[self::$foreignKeyIdParamName] = $args['reqspecid'];
  $this->_setArgs($args);
  return $this->uploadAttachment($args,$msg_prefix,false);
}

/**
 * Uploads an attachment for a Requirement.
 * 
 * The attachment content must be Base64 encoded by the client before sending it.
 * 
 * @param struct $args
 * @param string $args["devKey"] Developer key
 * @param int $args["requirementid"] The Requirement ID
 * @param string $args["title"] (Optional) The title of the Attachment 
 * @param string $args["description"] (Optional) The description of the Attachment
 * @param string $args["filename"] The file name of the Attachment (e.g.:notes.txt)
 * @param string $args["filetype"] The file type of the Attachment (e.g.: text/plain)
 * @param string $args["content"] The content (Base64 encoded) of the Attachment
 * 
 * @since 1.9beta6
 * @return mixed $resultInfo an array containing the fk_id, fk_table, title, 
 * description, file_name, file_size and file_type. If any errors occur it 
 * returns the erros map.
 */
public function uploadRequirementAttachment($args)
{
  $msg_prefix = "(" .__FUNCTION__ . ") - ";
  $args[self::$foreignKeyTableNameParamName] = 'requirements';
  $args[self::$foreignKeyIdParamName] = $args['requirementid'];
  $this->_setArgs($args);
  return $this->uploadAttachment($args,$msg_prefix,false);
}

/**
 * Uploads an attachment for a Test Project.
 * 
 * The attachment content must be Base64 encoded by the client before sending it.
 * 
 * @param struct $args
 * @param string $args["devKey"] Developer key
 * @param int $args["testprojectid"] The Test Project ID
 * @param string $args["title"] (Optional) The title of the Attachment 
 * @param string $args["description"] (Optional) The description of the Attachment
 * @param string $args["filename"] The file name of the Attachment (e.g.:notes.txt)
 * @param string $args["filetype"] The file type of the Attachment (e.g.: text/plain)
 * @param string $args["content"] The content (Base64 encoded) of the Attachment
 * 
 * @since 1.9beta6
 * @return mixed $resultInfo an array containing the fk_id, fk_table, title, 
 * description, file_name, file_size and file_type. If any errors occur it 
 * returns the erros map.
 */
public function uploadTestProjectAttachment($args)
{
  $msg_prefix = "(" .__FUNCTION__ . ") - ";
  $ret = null;
  
  $args[self::$foreignKeyTableNameParamName] = 'nodes_hierarchy';
  $args[self::$foreignKeyIdParamName] = $args[self::$testProjectIDParamName];
  $this->_setArgs($args);
    
  $checkFunctions = array('authenticate', 'checkTestProjectID');
  $statusOk = $this->_runChecks($checkFunctions) && $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);
  $ret = $statusOk ? $this->uploadAttachment($args,$msg_prefix,false) : $this->errors;
  return $ret;
}

/**
 * Uploads an attachment for a Test Suite.
 * 
 * The attachment content must be Base64 encoded by the client before sending it.
 * 
 * @param struct $args
 * @param string $args["devKey"] Developer key
 * @param int $args["testsuiteid"] The Test Suite ID
 * @param string $args["title"] (Optional) The title of the Attachment 
 * @param string $args["description"] (Optional) The description of the Attachment
 * @param string $args["filename"] The file name of the Attachment (e.g.:notes.txt)
 * @param string $args["filetype"] The file type of the Attachment (e.g.: text/plain)
 * @param string $args["content"] The content (Base64 encoded) of the Attachment
 * 
 * @since 1.9beta6
 * @return mixed $resultInfo an array containing the fk_id, fk_table, title, 
 * description, file_name, file_size and file_type. If any errors occur it 
 * returns the erros map.
 */
public function uploadTestSuiteAttachment($args)
{
  $msg_prefix = "(" .__FUNCTION__ . ") - ";
  $args[self::$foreignKeyTableNameParamName] = 'nodes_hierarchy';
  $args[self::$foreignKeyIdParamName] = $args[self::$testSuiteIDParamName];
  $this->_setArgs($args);
  
  $checkFunctions = array('authenticate', 'checkTestSuiteID');
  $statusOk = $this->_runChecks($checkFunctions) && $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);
  $ret = $statusOk ? $this->uploadAttachment($args,$msg_prefix,false) : $this->errors;
  return $ret;
}

/**
 * Uploads an attachment for a Test Case.
 * 
 * The attachment content must be Base64 encoded by the client before sending it.
 * 
 * @param struct $args
 * @param string $args["devKey"] Developer key
 * @param int $args["testcaseid"] Test Case INTERNAL ID
 * @param string $args["title"] (Optional) The title of the Attachment 
 * @param string $args["description"] (Optional) The description of the Attachment
 * @param string $args["filename"] The file name of the Attachment (e.g.:notes.txt)
 * @param string $args["filetype"] The file type of the Attachment (e.g.: text/plain)
 * @param string $args["content"] The content (Base64 encoded) of the Attachment
 * 
 * @since 1.9beta6
 * @return mixed $resultInfo an array containing the fk_id, fk_table, title, 
 * description, file_name, file_size and file_type. If any errors occur it 
 * returns the erros map.
 */
public function uploadTestCaseAttachment($args)
{
  $ret = null;
  $msg_prefix = "(" .__FUNCTION__ . ") - ";
  
  $args[self::$foreignKeyTableNameParamName] = 'nodes_hierarchy';
  $args[self::$foreignKeyIdParamName] = $args[self::$testCaseIDParamName];
  $this->_setArgs($args);
  $checkFunctions = array('authenticate', 'checkTestCaseIdentity');

  if( $statusOk = $this->_runChecks($checkFunctions,$msg_prefix) )
  {
    // Need to get test project information from test case in order to be able
    // to do RIGHTS check on $this->userHasRight()
    // !!! Important Notice!!!!: 
    // method checkTestCaseIdentity sets $this->args[self::$testCaseIDParamName]

     $this->args[self::$testProjectIDParamName] = 
        $this->tcaseMgr->getTestProjectFromTestCase($this->args[self::$testCaseIDParamName]);

     $statusOk = $this->userHasRight("mgt_modify_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);
  }  

  $ret = $statusOk ? $this->uploadAttachment($args,$msg_prefix,false) : $this->errors;
  return $ret;
}

/**
 * Uploads an attachment for an execution.
 * 
 * The attachment content must be Base64 encoded by the client before sending it.
 * 
 * @param struct $args
 * @param string $args["devKey"] Developer key
 * @param int $args["executionid"] execution ID
 * @param string $args["title"] (Optional) The title of the Attachment 
 * @param string $args["description"] (Optional) The description of the Attachment
 * @param string $args["filename"] The file name of the Attachment (e.g.:notes.txt)
 * @param string $args["filetype"] The file type of the Attachment (e.g.: text/plain)
 * @param string $args["content"] The content (Base64 encoded) of the Attachment
 * 
 * @since 1.9beta6
 * @return mixed $resultInfo an array containing the fk_id, fk_table, title, 
 * description, file_name, file_size and file_type. If any errors occur it 
 * returns the erros map.
 */
public function uploadExecutionAttachment($args)
{
  $msg_prefix = "(" .__FUNCTION__ . ") - ";
  $args[self::$foreignKeyTableNameParamName] = 'executions';
  $args[self::$foreignKeyIdParamName] = $args['executionid'];
  $this->_setArgs($args);

  // We need to check that user has right to execute in order to allow
  // him/her to do attachment

  return $this->uploadAttachment($args,$msg_prefix,false);
}

/**
 * Uploads an attachment for specified table. You must specify the table that 
 * the attachment is connected (nodes_hierarchy, builds, etc) and the foreign 
 * key id in this table.
 * 
 * The attachment content must be Base64 encoded by the client before sending it.
 * 
 * @param struct $args
 * @param string $args["devKey"] Developer key
 * @param int $args["fkid"] The Attachment Foreign Key ID
 * @param string $args["fktable"] The Attachment Foreign Key Table
 * @param string $args["title"] (Optional) The title of the Attachment 
 * @param string $args["description"] (Optional) The description of the Attachment
 * @param string $args["filename"] The file name of the Attachment (e.g.:notes.txt)
 * @param string $args["filetype"] The file type of the Attachment (e.g.: text/plain)
 * @param string $args["content"] The content (Base64 encoded) of the Attachment
 * 
 * @since 1.9beta6
 * @return mixed $resultInfo an array containing the fk_id, fk_table, title, 
 * description, file_name, file_size and file_type. If any errors occur it 
 * returns the erros map.
 */
public function uploadAttachment($args, $messagePrefix='', $setArgs=true)
{
  $resultInfo = array();
  if( $setArgs )
  {
    $this->_setArgs($args);
  }
  $msg_prefix = ($messagePrefix == '') ? ("(" .__FUNCTION__ . ") - ") : $messagePrefix;
  
  $checkFunctions = array();
  
  // TODO: please, somebody review if this is valid. I added this property 
  // to avoid the upload method of double authenticating the user. 
  // Otherwise, when uploadTestCaseAttachment was called, for instante, it 
  // would authenticate, check if the nodes_hierarchy is type TestCase 
  // and then call uploadAttachment that would, authenticate again.
  // What do you think?
  if( !$this->authenticated ) 
  {
    $checkFunctions[] = 'authenticate'; 
  }
  // check if :
  // TL has attachments enabled
  // provided FK is valid
  // attachment info is ok
  $checkFunctions[] = 'isAttachmentEnabled'; 
  $checkFunctions[] = 'checkForeignKey';
  $checkFunctions[] = 'checkUploadAttachmentRequest';

  $statusOk = $this->_runChecks($checkFunctions,$msg_prefix); 

  if($statusOk)
  {    
    $fkId = $this->args[self::$foreignKeyIdParamName];
    $fkTable = $this->args[self::$foreignKeyTableNameParamName];
    $title = $this->args[self::$titleParamName];

    // creates a temp file and returns an array with size and tmp_name
    $fInfo = $this->createAttachmentTempFile();
    if ( !$fInfo )
    {
      // Error creating attachment temp file. Ask user to check temp dir 
      // settings in php.ini and security and rights of this dir.
      $msg = $msg_prefix . ATTACH_TEMP_FILE_CREATION_ERROR_STR;
      $this->errors[] = new IXR_ERROR(ATTACH_TEMP_FILE_CREATION_ERROR,$msg); 
      $statusOk = false;
    } 
    else 
    {
      // The values have already been validated in the method 
      // checkUploadAttachmentRequest()
      $fInfo['name'] = $args[self::$fileNameParamName];
      $fInfo['type'] = $args[self::$fileTypeParamName];
        
      $attachmentRepository = tlAttachmentRepository::create($this->dbObj);
      $uploadedFile = $attachmentRepository->insertAttachment($fkId,$fkTable,$title,$fInfo);
      if( !$uploadedFile )
      {
        $msg = $msg_prefix . ATTACH_DB_WRITE_ERROR_STR;
        $this->errors[] = new IXR_ERROR(ATTACH_DB_WRITE_ERROR,$msg); 
        $statusOk = false; 
      } 
      else 
      {
        // We are returning some data that the user originally sent. 
        // Perhaps we could return only new data, like the file size?
        $resultInfo['fk_id'] = $args[self::$foreignKeyIdParamName];
        $resultInfo['fk_table'] = $args[self::$foreignKeyTableNameParamName];
        $resultInfo['title'] = $args[self::$titleParamName];
        $resultInfo['description'] = $args[self::$descriptionParamName];
        $resultInfo['file_name'] = $args[self::$fileNameParamName];

        // It would be nice have all info available in db
        // $resultInfo['file_path'] = $args[""]; 
        // we could also return the tmp_name, but would it be useful?
        $resultInfo['file_size'] = $fInfo['size'];
        $resultInfo['file_type'] = $args[self::$fileTypeParamName];
      }
    }
  }
    
  return $statusOk ? $resultInfo : $this->errors;
}

/**
 * <p>Checks if the attachments feature is enabled in TestLink 
 * configuration.</p>
 * 
 * @since 1.9beta6
 * @return boolean true if attachments feature is enabled in TestLink 
 * configuration, false otherwise.
 */
protected function isAttachmentEnabled($msg_prefix='')
{
  $status_ok = true;
  if (!config_get("attachments")->enabled) 
  {
      $msg = $msg_prefix . ATTACH_FEATURE_DISABLED_STR;
      $this->errors[] = new IXR_ERROR(ATTACH_FEATURE_DISABLED,$msg); 
    $status_ok = false;
  }
  return $status_ok;
}

/**
 * <p>Checks if the given foreign key is valid. What this method basically does 
 * is query the database looking for the foreign key id in the foreign key 
 * table.</p>
 * 
 * @since 1.9beta6
 * @return boolean true if the given foreign key exists, false otherwise.
 */
protected function checkForeignKey($msg_prefix='')
{
  $statusOk = true;
  
  $fkId = $this->args[self::$foreignKeyIdParamName];
    $fkTable = $this->args[self::$foreignKeyTableNameParamName];
    
  if ( isset($fkId) && isset($fkTable) )
  {
    $query = "SELECT id FROM {$this->tables[$fkTable]} WHERE id={$fkId}";
    $result = $this->dbObj->fetchFirstRowSingleColumn($query, "id");
  }
         
    if(null == $result)
    {
      $msg = $msg_prefix . sprintf(ATTACH_INVALID_FK_STR, $fkId, $fkTable);
      $this->errors[] = new IXR_ERROR(ATTACH_INVALID_FK,$msg);
        $statusOk = false;         
  }
  
  return $statusOk;
}

/**
 * <p>Checks if the attachment parameters are valid. It checks if the 
 * <b>file_name</b> parameter is set, if the <b>content</b> is set and if 
 * the <b>file type</b> is set. If the <b>file type</b> is not set, then it uses 
 * <b>application/octet-stream</b>. 
 * This default content type refers to <i>binary</i> files.</p> 
 * 
 * @since 1.9beta6
 * @return boolean true if the file name and the content are set
 */
protected function checkUploadAttachmentRequest($msg_prefix = '')
{
  // Did the client set file name?
  $status = isset($this->args[self::$fileNameParamName]);
  if ( $status )
  {
    // Did the client set file content? 
    $status = isset($this->args[self::$contentParamName]);
    if ( $status )
    {
      // Did the client set the file type? If not so use binary as default file type
      if ( isset($this->args[self::$fileTypeParamName]) )
      {
        // By default, if no file type is provided, put it as binary
        $this->args[self::$fileTypeParamName] = "application/octet-stream";
      }
    }
  }

  if(!$status) 
  {
    $msg = $msg_prefix . sprintf(ATTACH_INVALID_ATTACHMENT_STR, $this->args[self::$fileNameParamName], 
                   sizeof($this->args[self::$contentParamName]));
      $this->errors[] = new IXR_ERROR(ATTACH_INVALID_ATTACHMENT,$msg);
  }
  
  return $status;
}

/**
 * <p>Creates a temporary file and writes the attachment content into this file.</p>
 * 
 * <p>Before writing to the file it <b>Base64 decodes</b> the file content.</p>
 * 
 * @since 1.9beta6
 * @return file handler
 */
protected function createAttachmentTempFile()
{
  $resultInfo = array();
  $filename = tempnam(sys_get_temp_dir(), 'tl-');
  
  $resultInfo["tmp_name"] = $filename;
  $handle = fopen( $filename, "w" );
  fwrite($handle, base64_decode($this->args[self::$contentParamName]));
  fclose( $handle );
  
  $filesize = filesize($filename);
  $resultInfo["size"] = $filesize;
  
    return $resultInfo;
}



  /**
   * checks if a test case version number is defined for a test case
   * if everything is ok $this->tcVersionID will be setted
   *
   * @param string $messagePrefix used to be prepended to error message
   * 
   * @return map with following keys
   *             boolean map['status_ok']
   *             string map['error_msg']
   *             int map['error_code']
   */
  protected function checkTestCaseVersionNumberAncestry($messagePrefix='')
  {
    $ret=array('status_ok' => true, 'error_msg' => '' , 'error_code' => 0);
  
    $tcase_id = $this->args[self::$testCaseIDParamName];
    $version_number = $this->args[self::$versionNumberParamName];
      
    $sql = " SELECT TCV.version,TCV.id " . 
           " FROM {$this->tables['nodes_hierarchy']} NH, {$this->tables['tcversions']} TCV " .
           " WHERE NH.parent_id = {$tcase_id} " .
           " AND TCV.version = {$version_number} " .
           " AND TCV.id = NH.id ";
  
    $target_tcversion = $this->dbObj->fetchRowsIntoMap($sql,'version');
    if( !is_null($target_tcversion) && count($target_tcversion) == 1 )
    {
      $dummy = current($target_tcversion);
      $this->tcVersionID = $dummy['id'];
    }
    else
    {
      $status_ok=false;
      $tcase_info = $this->tcaseMgr->tree_manager->get_node_hierarchy_info($tcase_id);
      $msg = sprintf(TCASE_VERSION_NUMBER_KO_STR,$version_number,$this->args[self::$testCaseExternalIDParamName],
                     $tcase_info['name']);  
      $ret = array('status_ok' => false, 'error_msg' => $msg , 'error_code' => TCASE_VERSION_NUMBER_KO);                                               
    }  
    return $ret;
  } 


  /**
   * Helper method to see if the a provided custom field is not empty.
   *
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */
  protected function checkCustomField($messagePrefix='')
  {
    return (isset($this->args[self::$customFieldNameParamName]) ? true : false);
  }

    /**
    * Helper method to see if the a provided scope is valid. Valids scopes are 
    * design, execution and testplan_design
    *
    * @param string $messagePrefix used to be prepended to error message
    *
    * @return boolean
    * @access protected
    */
    protected function checkCustomFieldScope($messagePrefix='')
    {
      $status = false;
      $domain = array('design' => true,'execution' => true, 'testplan_design' => true);
      $scope = $this->args[self::$scopeParamName];

    $status = is_null($scope) ? false : isset($domain[$scope]);
      return $status;
    }


    /**
     * Gets value of a Custom Field for a entity in a given scope (e.g.: a custom
     * field for a test case in design scope).
     *
     * BUGID-4188: feature request - new method - getTestSuiteCustomFieldValue
     *
     * @param struct $args
     * @param string $args["devKey"]: used to check if operation can be done.
     *                                if devKey is not valid => abort.
     *
     * @param string $args["customfieldname"]: custom field name
     * @param int    $args["testprojectid"]: project id
     * @param string $args["nodetype"]: note type (testcase, testsuite, ...)
     * @param int    $args["nodeid"]: node id (test case version id, project id, ...)
     * @param string $args["scope"]: cf scope (execution, design or testplan_design)
     * @param int    $args["executionid"]: execution id
     * @param int    $args["testplanid"]: test plan id
     * @param int    $args["linkid"]: link id for nodes linked at test plan design scope
     *
     * @return mixed $resultInfo
     *
     * @access protected
     */
    protected function getCustomFieldValue($args,$msg_prefix='')
    {
      $this->_setArgs($args);

      $checkFunctions = array('authenticate','checkTestProjectID','checkCustomField','checkCustomFieldScope');
      $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);

      $scope = $this->args[self::$scopeParamName];

      switch($scope)
      {
        case 'execution': 

          // test plan id is valid ?
          if( ($status_ok = $this->checkTestPlanID($msg_prefix)) )
          {
            // test plan has to belong to test project
            $tplanid = intval($this->args[self::$testPlanIDParamName]);
            $tprojectid = intval($this->args[self::$testProjectIDParamName]);
            
            $sql = " SELECT id FROM {$this->tables['nodes_hierarchy']} " .
                   " WHERE id = " . $tplanid .
                   " AND parent_id = " . $tprojectid;
            
            $rs = $this->dbObj->get_recordset($sql);
            $status_ok = !is_null($rs); 
            if( $status_ok == FALSE )
            {
              $project = $this->tprojectMgr->get_by_id($tprojectid);
              $plan = $this->tplanMgr->get_by_id($tplanid);
              $msg = sprintf(TPLAN_TPROJECT_KO_STR,$plan['name'],$tplanid,
                             $project['name'],$tprojectid);  
              $this->errors[] = new IXR_Error(TPLAN_TPROJECT_KO,
                                              $msg_prefix . $msg); 
            }  
          }  
        break;

        case 'design':
        default:
        break;
      }

      if($status_ok && $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR))
      {
        $cf_name = $this->args[self::$customFieldNameParamName];
        $tproject_id = $this->args[self::$testProjectIDParamName];
        $nodetype = $this->args[self::$nodeTypeParamName];
        $nodeid = $this->args[self::$nodeIDParamName];
        $executionid = $this->args[self::$executionIDParamName];
        $testplanid = $this->args[self::$testPlanIDParamName];
        $linkid = $this->args[self::$linkIDParamName];

        $enabled = 1; // returning only enabled custom fields

        $cfield_mgr = $this->tprojectMgr->cfield_mgr;
        $cfinfo = $cfield_mgr->get_by_name($cf_name);
        $cfield = current($cfinfo);

        switch($scope)
        {
          case 'design':
            $filters = array( 'cfield_id' => $cfield['id']);
            $cfieldSpec = $cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,
                                          $filters,$nodetype,$nodeid);
          break;
            
          case 'execution': 
            $cfieldSpec = $cfield_mgr->get_linked_cfields_at_execution($tproject_id,$enabled,$nodetype,
                                          $nodeid,$executionid,$testplanid);
          break;

        case 'testplan_design':
             $cfieldSpec = $cfield_mgr->get_linked_cfields_at_testplan_design($tproject_id,$enabled,$nodetype,
                                              $nodeid,$linkid,$testplanid );
        break; 
 
          }
          return $cfieldSpec[$cfield['id']];
        }
        else
        {
          return $this->errors;
        }
    }

    /**
     * Gets a Custom Field of a Test Case in Execution Scope.
     * 
     * @param struct $args
     * @param string $args["devKey"]: used to check if operation can be done.
     *                               if devKey is not valid => abort.
     *
     * @param string $args["customfieldname"]: custom field name
     * @param int    $args["testprojectid"]: project id
     * @param int    $args["executionid"]: execution id
     * @param int    $args["version"]: test case version NUMBER
     * @param int    $args["testplanid"]: test plan id
     *
     * @return mixed $resultInfo
     *
     * @access public
     */
  public function getTestCaseCustomFieldExecutionValue($args)
  {
    $msgPrefix = "(" . __FUNCTION__ . ") - ";
 
    $args[self::$nodeTypeParamName] = 'testcase';
    $args[self::$scopeParamName] = 'execution';
    
    $this->_setArgs($args);

    $status_ok = true;
    $p2c = array(self::$executionIDParamName,self::$versionNumberParamName);
    foreach($p2c as $prm)
    {
      $status_ok = $this->_isParamPresent($prm,$msgPrefix,self::SET_ERROR);
      if($status_ok == FALSE)
      {
        break;
      }  
    }

    // version number is related to execution id
    if($status_ok)
    {
      $sql = " SELECT id,tcversion_id FROM {$this->tables['executions']} " .
             " WHERE id = " . intval($args[self::$executionIDParamName]) .
             " AND tcversion_number = " . 
             intval($args[self::$versionNumberParamName]);
      
      $rs = $this->dbObj->get_recordset($sql);

      //return $sql;
      if( is_null($rs) )
      {
        $status_ok = false;
        $msg = sprintf(NO_MATCH_STR,
                       self::$versionNumberParamName . '/' .
                       self::$executionIDParamName);
        $this->errors[] = new IXR_Error(NO_MATCH,$msg);      
      }  
      else
      {
        $args[self::$nodeIDParamName] = $rs[0]['tcversion_id'];
      }  
    }

  
    if($status_ok)
    {
      return $this->getCustomFieldValue($args);
    }  
    return $this->errors;    

  }
    
  /**
    * Gets a Custom Field of a Test Case in Test Plan Design Scope.
   *
   * @param struct $args
   * @param string $args["devKey"]: used to check if operation can be done.
   *                                 if devKey is not valid => abort.
   *
   * @param string $args["customfieldname"]: custom field name
   * @param int    $args["testcaseid"]: project id
   * @param int    $args["version"]: test case version id
   * @param int    $args["testplanid"]: test plan id
   * @param int    $args["linkid"]: link id (important!)
   *
   * @return mixed $resultInfo
   *
   * @access public
   */
  public function getTestCaseCustomFieldTestPlanDesignValue($args)
  {
      $args[self::$nodeTypeParamName] = 'testcase';
      $args[self::$nodeIDParamName] = $args[self::$versionNumberParamName];
      $args[self::$scopeParamName] = 'testplan_design';
  
      return $this->getCustomFieldValue($args);
  }
  
  /**
   * Gets a Custom Field of a Test Suite in Design Scope.
   *
   * @param struct $args
   * @param string $args["devKey"]: used to check if operation can be done.
   *                                 if devKey is not valid => abort.
   *
   * @param string $args["customfieldname"]: custom field name
   * @param int   $args["testprojectid"]: project id
   * @param int    $args["testsuiteid"]: test suite id
   * 
   * @return mixed $resultInfo
   *
   * @access public
   */
  public function getTestSuiteCustomFieldDesignValue($args)
  {
      $args[self::$nodeTypeParamName] = 'testsuite';
      $args[self::$nodeIDParamName] = $args[self::$testSuiteIDParamName];
      $args[self::$scopeParamName] = 'design';
  
      return $this->getCustomFieldValue($args);
  }
  
  /**
   * Gets a Custom Field of a Test Plan in Design Scope.
   *
   * @param struct $args
   * @param string $args["devKey"]: used to check if operation can be done.
   *                                if devKey is not valid => abort.
   *
   * @param string $args["customfieldname"]: custom field name
   * @param int    $args["testprojectid"]: project id
   * @param int    $args["testplanid"]: test plan id
   *
   * @return mixed $resultInfo
   *
   * @access public
   */
  public function getTestPlanCustomFieldDesignValue($args)
  {
      $args[self::$nodeTypeParamName] = 'testplan';
      $args[self::$nodeIDParamName] = $args[self::$testPlanIDParamName];
      $args[self::$scopeParamName] = 'design';
  
      return $this->getCustomFieldValue($args);
  }
  
    /**
     * Gets a Custom Field of a Requirement Specification in Design Scope.
     * 
     * @param struct $args
     * @param string $args["devKey"]: used to check if operation can be done.
     *                                if devKey is not valid => abort.
     *
     * @param string $args["customfieldname"]: custom field name
     * @param int    $args["testprojectid"]: project id
     * @param int    $args["reqspecid"]: requirement specification id
     * 
     * @return mixed $resultInfo
     * 
     * @access public
     */
    public function getReqSpecCustomFieldDesignValue($args)
    {
        $args[self::$nodeTypeParamName] = 'requirement_spec';
        $args[self::$nodeIDParamName] = $args[self::$reqSpecIDParamName];
        $args[self::$scopeParamName] = 'design';
        
        return $this->getCustomFieldValue($args);
    }
  
    /**
     * Gets a Custom Field of a Requirement in Design Scope.
     * 
     * @param struct $args
     * @param string $args["devKey"]: used to check if operation can be done.
     *                                if devKey is not valid => abort.
     *
     * @param string $args["customfieldname"]: custom field name
     * @param int    $args["testprojectid"]: project id
     * @param int    $args["requirementid"]: requirement id
     * 
     * @return mixed $resultInfo
     * 
     * @access public
     */
    public function getRequirementCustomFieldDesignValue($args)
    {
        $args['nodetype'] = 'requirement';
        $args['nodeid'] = $args[self::$requirementIDParamName];
        $args['scope'] = 'design';
        
        return $this->getCustomFieldValue($args);
    }


  /**
   * createTestCaseSteps - can be used also for upgrade (see action)
   * 
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param string $args["testcaseexternalid"] optional if you provide $args["testcaseid"]
   * @param string $args["testcaseid"] optional if you provide $args["testcaseexternalid"]
   * @param string $args["version"] - optional if not provided LAST ACTIVE version will be used
   *                                  if all versions are INACTIVE, then latest version will be used.   
   * @param string $args["action"]
   *               possible values
   *               'create','update','push'
   *               create: if step exist NOTHING WILL BE DONE
   *               update: if step DOES NOT EXIST will be created
   *                       else will be updated.
   *               push: shift down all steps with step number >= step number provided
   *                     and use provided data to create step number requested.
   *                     NOT IMPLEMENTED YET  
   * @param array  $args["steps"]:
   *                each element is a hash with following keys
   *                step_number,actions,expected_results,execution_type
   * 
   * @return mixed $resultInfo
   *
   * @internal revisions
   * 20111018 - franciscom - TICKET 4774: New methods to manage test case steps
   */
  function createTestCaseSteps($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $resultInfo=array();
    $useLatestVersion = true;
    $version = -1;
    $item = null;
    $stepSet = null;
    $stepNumbers = null;
      
    $this->_setArgs($args);
    $checkFunctions = array('authenticate','checkTestCaseIdentity');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix) && 
                 $this->userHasRight("mgt_modify_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);

    if( $status_ok )
    {
      // Important Notice: method checkTestCaseIdentity sets
      // $this->args[self::$testCaseIDParamName]
      $tcaseID = $this->args[self::$testCaseIDParamName];
      $resultInfo[self::$testCaseIDParamName] = $tcaseID;
      $resultInfo['item'] = null;
        
      // if parameter version does not exits or is < 0 
      // then we will on latest version active or not
      if( $this->_isParamPresent(self::$versionNumberParamName) )
      {
        $version = $this->args[self::$versionNumberParamName];
      }
    
      $resultInfo['version'] = 'exists';
      if( $version > 0)
      {
        $item = $this->tcaseMgr->get_basic_info($tcaseID,array('number' => $version));
      }
      else
      {
        $resultInfo['version'] = 'DOES NOT ' . $resultInfo['version'];
        $item = $this->tcaseMgr->get_last_active_version($tcaseID);
        if( is_null($item) )
        {
          // get last version no matter if is active
          $dummy = $this->tcaseMgr->get_last_version_info($tcaseID);
          $dummy['tcversion_id'] = $dummy['id'];
          $item[0] = $dummy;
        }
      }

      if( is_null($item) )
      {
        $status_ok = false;
        $msg = sprintf(VERSION_NOT_VALID_STR,$version);
        $this->errors[] = new IXR_Error(VERSION_NOT_VALID,$msg);
      }

      if( $status_ok)
      {
        $item = current($item);
        $tcversion_id = $item['tcversion_id'];
        $resultInfo['tcversion_id'] = $tcversion_id;


        $step_id = 0;
        $stepSet = null;
        $action = isset($this->args,self::$actionParamName) ? $this->args[self::$actionParamName] : 'create';

        // 
        // id,step_number,actions,expected_results,active,execution_type
        $opt = array('accessKey' => 'step_number');
        $stepSet = (array)$this->tcaseMgr->get_steps($tcversion_id,0,$opt);
        $stepNumberIDSet = array_flip(array_keys($stepSet));
        foreach($stepNumberIDSet as $sn => $dummy)
        {
          $stepNumberIDSet[$sn] = $stepSet[$sn]['id'];
        }

        $resultInfo['stepSet'] = $stepSet;
        $resultInfo['stepNumberIDSet'] = $stepNumberIDSet;
        
        foreach($this->args[self::$stepsParamName] as $si)
        {
          $execution_type = isset($si['execution_type']) ? $si['execution_type'] : TESTCASE_EXECUTION_TYPE_MANUAL;
          $stepExists = isset($stepSet[$si['step_number']]);
          if($stepExists)
          {
            // needed for update op.
            $step_id = $stepSet[$si['step_number']]['id'];
            $resultInfo['stepID'][] = array($step_id,$si['step_number']);
          }

          switch($action)
          {
            case 'update':
              $op = $stepExists ? $action : 'create'; 
            break;
            
            case 'push':
              $op = $stepExists ? $action : 'skip';
            break;
                  
            case 'create':
              $op = $stepExists ? 'skip' : $action; 
            break;
          }
          $resultInfo['feedback'][] = array('operation' => $op, 'step_number' => $si['step_number']);
          switch($op)
          {
            case 'update':
              $this->tcaseMgr->update_step($step_id,$si['step_number'],$si['actions'],
                                           $si['expected_results'],$execution_type);
            break;


            case 'create':
              $this->tcaseMgr->create_step($tcversion_id,$si['step_number'],$si['actions'],
                                           $si['expected_results'],$execution_type);
            break;
            
            case 'push':
              // First action renumber existent steps
              $renumberedSet = null;
              foreach($stepNumberIDSet as $tsn => $dim)
              {
                // echo $tsn;
                if($tsn < $si['step_number'])
                {
                  unset($stepNumberIDSet[$tsn]);
                } 
                else
                {
                  $renumberedSet[$dim] = $tsn+1;
                }
              }
              $this->tcaseMgr->set_step_number($renumberedSet);              
              $this->tcaseMgr->create_step($tcversion_id,$si['step_number'],$si['actions'],
                                           $si['expected_results'],$execution_type);
            break;

            case 'skip':
            default:
            break;
          }
        }        
        
      }
    }
    return ($status_ok ? $resultInfo : $this->errors);
  }


  /**
   * deleteTestCaseSteps
   * @param struct $args
   * @param string $args["devKey"]
   * @param string $args["testcaseexternalid"]
   * @param string $args["version"] - optional if not provided LAST ACTIVE version will be used
   * @param array  $args["steps"]: each element is a step_number
   * 
   * @return mixed $resultInfo
   *
   * @internal revisions
   * 20111018 - franciscom - TICKET 4774: New methods to manage test case steps
   */
  function deleteTestCaseSteps($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $resultInfo=array();
    $version = -1;
    $item = null;
    $stepSet = null;
    $stepNumberIDSet = null;
      
    $this->_setArgs($args);
    $checkFunctions = array('authenticate','checkTestCaseIdentity');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix) && 
                 $this->userHasRight("mgt_modify_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);

    if( $status_ok )
    {
      // Important Notice: method checkTestCaseIdentity sets
      // $this->args[self::$testCaseIDParamName]
      $tcaseID = $this->args[self::$testCaseIDParamName];
      $resultInfo[self::$testCaseIDParamName] = $tcaseID;
      $resultInfo['item'] = null;
        
      // if parameter version does not exits or is < 0 
      // then we will on latest version active or not
      if( $this->_isParamPresent(self::$versionNumberParamName) )
      {
        $version = $this->args[self::$versionNumberParamName];
      }
    
      $resultInfo['version'] = 'exists';
      if( $version > 0)
      {
        $item = $this->tcaseMgr->get_basic_info($tcaseID,array('number' => $version));
      }
      else
      {
        $resultInfo['version'] = 'DOES NOT ' . $resultInfo['version'];
        $item = $this->tcaseMgr->get_last_active_version($tcaseID);
      }
      
      if( is_null($item) )
      {
        $status_ok = false;
        $msg = sprintf(VERSION_NOT_VALID_STR,$version);
        $this->errors[] = new IXR_Error(VERSION_NOT_VALID,$msg);
      }
      // $resultInfo['item'] = is_null($item) ? $msg : $item;
    
      if( $status_ok)
      {
        
        // $resultInfo['steps'] = $this->args[self::$stepsParamName];
        
        $tcversion_id = $item[0]['tcversion_id'];
        $step_id = 0;
        $stepSet = null;
        // 
        // id,step_number,actions,expected_results,active,execution_type
        $opt = array('accessKey' => 'step_number');
        $stepSet = (array)$this->tcaseMgr->get_steps($tcversion_id,0,$opt);
        $resultInfo['stepSet'] = $stepSet;
  
        foreach($this->args[self::$stepsParamName] as $step_number)
        {
          if(isset($stepSet[$step_number]))
          {
            $this->tcaseMgr->delete_step_by_id($stepSet[$step_number]['id']);
          }
        }        
        
      }
    }
        return ($status_ok ? $resultInfo : $this->errors);
  }


  /**
   * Update value of Custom Field with scope='design' for a given Test case
   *
   * @param struct $args
   * @param string $args["devKey"]: used to check if operation can be done.
   *                                if devKey is not valid => abort.
   *
   * @param string $args["testcaseexternalid"]:  
   * @param string $args["version"]: version number  
   * @param string $args["testprojectid"]: 
   * @param string $args["customfields"] - optional
   *               contains an map with key:Custom Field Name, value: value for CF.
   *               VERY IMPORTANT: value must be formatted in the way it's written to db,
   *               this is important for types like:
   *
   *               DATE: strtotime()
   *               DATETIME: mktime()
   *               MULTISELECTION LIST / CHECKBOX / RADIO: se multipli selezione ! come separatore
   *
   *
   *               these custom fields must be configured to be writte during execution.
   *               If custom field do not meet condition value will not be written
   *
   * @return mixed null if everything ok, else array of IXR_Error objects
   *         
   * @access public
   */    
  public function updateTestCaseCustomFieldDesignValue($args)
  {
    $msg_prefix="(" .__FUNCTION__ . ") - ";
    $this->_setArgs($args);  
    
    $checkFunc = array('authenticate','checkTestProjectID',
                       'checkTestCaseIdentity',
                       'checkTestCaseVersionNumber');
    $status_ok = $this->_runChecks($checkFunc,$msg_prefix);       

    if( $status_ok )
    {
      if(!$this->_isParamPresent(self::$customFieldsParamName) )
      {
        $status_ok = false;
        $msg = sprintf(MISSING_REQUIRED_PARAMETER_STR,self::$customFieldsParamName);
        $this->errors[] = new IXR_Error(MISSING_REQUIRED_PARAMETER, $msg);
      }
    }
      
    if( $status_ok )
    {
      // now check if custom fields are ok
      // For each custom field need to check if:
      // 1. is linked to test project
      // 2. is available for test case at design time
      $cfieldMgr = new cfield_mgr($this->dbObj);
      
      // Just ENABLED
      $linkedSet = $cfieldMgr->get_linked_cfields_at_design($this->args[self::$testProjectIDParamName],
                                                            cfield_mgr::ENABLED,null,'testcase',null,'name');
      if( is_null($linkedSet) )
      {
        $status_ok = false;
        $msg = NO_CUSTOMFIELDS_DT_LINKED_TO_TESTCASES_STR;
        $this->errors[] = new IXR_Error(NO_CUSTOMFIELDS_DT_LINKED_TO_TESTCASES, $msg);              
      }
    }
      
      
    if( $status_ok )
    {
      $accessVersionBy['number'] = $this->args[self::$versionNumberParamName];
      $nodeInfo = $this->tcaseMgr->get_basic_info($this->args[self::$testCaseIDParamName],$accessVersionBy);
      $cfSet = $args[self::$customFieldsParamName];
      foreach($cfSet as $cfName => $cfValue)
      {
        // $accessKey = "custom_field_" . $item['id'] . <field_type_id>_<cfield_id>
        //  design_values_to_db($hash,$node_id,$cf_map=null,$hash_type=null)
        $item = $linkedSet[$cfName];
        $accessKey = "custom_field_" . $item['type'] . '_' . $item['id'];
        $hash[$accessKey] = $cfValue;
        $cfieldMgr->design_values_to_db($hash,$nodeInfo[0]['tcversion_id']);
      }        
    }
    else
    {
        return $this->errors;
    }  
  }


  /**
   * Update execution type for a test case version
   *
   * @param struct $args
   * @param string $args["devKey"]: used to check if operation can be done.
   *                                if devKey is not valid => abort.
   *
   * @param string $args["testcaseexternalid"]:  
   * @param string $args["version"]: version number  
   * @param string $args["testprojectid"]: 
   * @param string $args["executiontype"]: TESTCASE_EXECUTION_TYPE_MANUAL,
   *                     TESTCASE_EXECUTION_TYPE_AUTOMATIC
   *
   * @return mixed null if everything ok, else array of IXR_Error objects
   *         
   * @access public
   */    
  public function setTestCaseExecutionType($args)
  {
    $msg_prefix="(" .__FUNCTION__ . ") - ";
    $this->_setArgs($args);  
    
    $checkFunctions = array('authenticate','checkTestProjectID','checkTestCaseIdentity',
                            'checkTestCaseVersionNumber');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);       
    if( $status_ok )
    {
      if(!$this->_isParamPresent(self::$executionTypeParamName))
      {
        $status_ok = false;
        $msg = sprintf(MISSING_REQUIRED_PARAMETER_STR,self::$customFieldsParamName);
        $this->errors[] = new IXR_Error(MISSING_REQUIRED_PARAMETER, $msg);              
      }
    }

    if($status_ok)
    {
      // if value not on domain, will use TESTCASE_EXECUTION_TYPE_MANUAL
      $accessVersionBy['number'] = $this->args[self::$versionNumberParamName];
      $nodeInfo = $this->tcaseMgr->get_basic_info($this->args[self::$testCaseIDParamName],$accessVersionBy);
      $dbg = $this->tcaseMgr->setExecutionType($nodeInfo[0]['tcversion_id'],$this->args[self::$executionTypeParamName]);
      return array($this->args,$dbg);
    }
    else
    {
      return $this->errors;
    }  
  }


  /**
   *
   */
  public function getExecCountersByBuild($args)
  {
    $operation = __FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $total = null;
    
    $this->_setArgs($args);
    $status_ok=true;

    // Checks are done in order
    $checkFunctions = array('authenticate','checkTestPlanID');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);

    if( $status_ok )
    {
      $metrics = $this->tplanMetricsMgr->getExecCountersByBuildExecStatus($this->args[self::$testPlanIDParamName]);
    }

    if( !is_null($metrics) )
    {
      // transform in somethin similar to a simple table
      $out = array();
      foreach($metrics['with_tester'] as $build_id => &$elem)
      {
        $out[$build_id] = array();
        $out[$build_id]['name'] = $metrics['active_builds'][$build_id]['name'];
        $out[$build_id]['notes'] = $metrics['active_builds'][$build_id]['notes'];
        $out[$build_id]['total'] = $metrics['total'][$build_id]['qty'];
        
        foreach($elem as $status_code => &$data)
        {
          $out[$build_id][$status_code] = $data['exec_qty'];    
        }
      }
      return array('raw' => $metrics, 'table' => $out);
      
    }
    else
    {
      return $this->errors;
    }
    
  }


  /**
   * create platform 
   * 
   * @param struct $args
   * @param string $args["devKey"]
   * @param string $args["testprojectname"]
   * @param string $args["platformname"]
   * @param string $args["notes"]
   * @return mixed $resultInfo
   * @internal revisions
   */
  public function createPlatform($args)
  {
    $this->_setArgs($args);
    $status_ok = false;    
    $msg_prefix="(" . __FUNCTION__ . ") - ";


    if($this->authenticate() && 
       $this->userHasRight("platform_management",
                           self::CHECK_PUBLIC_PRIVATE_ATTR))
    {
      $status_ok = true;
      $keys2check = array(self::$platformNameParamName, self::$testProjectNameParamName);
      foreach($keys2check as $key)
      {
        $names[$key]=$this->_isParamPresent($key,$msg_prefix,self::SET_ERROR) ? trim($this->args[$key]) : '';
        if($names[$key] == '')
        {
          $status_ok=false;    
          break;
        }
      }
    }

    if( $status_ok )
    {              
      $op = $this->helperGetTestProjectByName($msg_prefix);
      $status_ok = $op['status_ok'];
    }

    if( $status_ok )
    {
      // now check if platform exists
      if( is_null($this->platformMgr) )
      {
        $this->platformMgr = new tlPlatform($this->dbObj,$op['info']['id']);
      }
      // lazy way
      $name = trim($this->args[self::$platformNameParamName]);
      $itemSet = $this->platformMgr->getAllAsMap('name','allinfo');
      if( isset($itemSet[$name]) )
      {
        $status_ok = false;
        $msg = $msg_prefix . sprintf(PLATFORMNAME_ALREADY_EXISTS_STR,$name,$itemSet[$name]['id']);
        $this->errors[] = new IXR_Error(PLATFORMNAME_ALREADY_EXISTS, $msg);
      }
      
    }

    if( $status_ok )
    {
      $notes = $this->_isNotePresent() ? $this->args[self::$noteParamName] : '';
      $op = $this->platformMgr->create($name,$notes);
      $resultInfo = $op;
    }

     return $status_ok ? $resultInfo : $this->errors;
  } 


  /**
   *
   */
  public function getProjectPlatforms($args)
  {
    $messagePrefix="(" .__FUNCTION__ . ") - ";
        
    $this->_setArgs($args);
    $checkFunctions = array('authenticate','checkTestProjectIdentity');       
    $status_ok=$this->_runChecks($checkFunctions,$messagePrefix);       
  
    if($status_ok)
    {
      $testProjectID = $this->args[self::$testProjectIDParamName];
      if( is_null($this->platformMgr) )
      {
        $this->platformMgr = new tlPlatform($this->dbObj,$testProjectID);
      }
      $itemSet = $this->platformMgr->getAllAsMap('name','allinfo');
      return $itemSet;
    }
    else
    {
      return $this->errors;
    } 
  }


  /**
   * addPlatformToTestPlan 
   * 
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["testplanid"]
   * @param map $args["platformname"]
   * @return mixed $resultInfo
   * @internal revisions
   */
  public function addPlatformToTestPlan($args)
  {
    return $this->platformLinkOp($args,'link',"(" .__FUNCTION__ . ") - ");

  }

  /**
   * removePlatformFromTestPlan 
   * 
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["testplanid"]
   * @param map $args["platformname"]
   * @return mixed $resultInfo
   * @internal revisions
   */
  public function removePlatformFromTestPlan($args)
  {
    return $this->platformLinkOp($args,'unlink',"(" .__FUNCTION__ . ") - ");
  }


  /**
   * if everything ok returns an array on just one element with following user data
   *
   * firstName,lastName,emailAddress,locale,isActive,defaultTestprojectID,
   * globalRoleID 
   * globalRole    array with role info
   * tprojectRoles array  
   * tplanRoles    array
   * login 
   * dbID
   * loginRegExp
   *
   * ATTENTION: userApiKey will be set to NULL, because is worst that access to user password
   * 
   * @param struct $args
   * @param string $args["devKey"]   
   * @param string $args["user"]   Login Name   
   * 
   * @return mixed $ret
   * 
   */
  public function getUserByLogin($args)
  {
    $messagePrefix="(" .__FUNCTION__ . ") - ";
    $this->_setArgs($args);
    $checkFunctions = array('authenticate');       
    $ret = array();

    $status_ok=$this->_runChecks($checkFunctions,$messagePrefix);       
    if( $status_ok )
    {
      $user_id = tlUser::doesUserExist($this->dbObj,$this->args[self::$userParamName]);          
      if( !($status_ok = !is_null($user_id)) )
      {  
        $msg = $msg_prefix . sprintf(NO_USER_BY_THIS_LOGIN_STR,$this->args[self::$userParamName]);
        $this->errors[] = new IXR_Error(NO_USER_BY_THIS_LOGIN, $msg);  
      }
    }

    if( $status_ok )
    {
      $user = tlUser::getByID($this->dbObj,$user_id); 
      $user->userApiKey = null;
      $ret[] = $user;
    }    

    return $status_ok ? $ret : $this->errors;
  }

  /**
   * if everything ok returns an array on just one element with following user data
   *
   * firstName,lastName,emailAddress,locale,isActive,defaultTestprojectID,
   * globalRoleID 
   * globalRole    array with role info
   * tprojectRoles array  
   * tplanRoles    array
   * login 
   * dbID
   * loginRegExp
   *
   * ATTENTION: userApiKey will be set to NULL, because is worst that access to user password
   * 
   * @param struct $args
   * @param string $args["devKey"]   
   * @param string $args["userid"]   user ID as present on users table, column ID
   * 
   * @return mixed $ret
   * 
   */
  public function getUserByID($args)
  {
    $messagePrefix="(" .__FUNCTION__ . ") - ";
    $this->_setArgs($args);
    $checkFunctions = array('authenticate');       
    $ret = array();

    $status_ok=$this->_runChecks($checkFunctions,$messagePrefix);       
    if( $status_ok )
    {
      $user = tlUser::getByID($this->dbObj,$this->args[self::$userIDParamName]);  
      if(is_null($user))
      {
        $status_ok = false;
        $msg = $messagePrefix . sprintf(NO_USER_BY_THIS_ID_STR,$this->args[self::$userIDParamName]);
        $this->errors[] = new IXR_Error(NO_USER_BY_ID_LOGIN, $msg);        
      }  
      else
      {
        $user->userApiKey = null;
        $ret[] = $user;
      }  
    }    

    return $status_ok ? $ret : $this->errors;
  }


  /**
   *
   *
   */
  private function platformLinkOp($args,$op,$messagePrefix)
  {
    $this->_setArgs($args);
    $checkFunctions = array('authenticate','checkTestPlanID');       
    $status_ok = $this->_runChecks($checkFunctions,$messagePrefix) &&     
                 $this->_isParamPresent(self::$platformNameParamName,$messagePrefix,self::SET_ERROR);
    if($status_ok)
    {
      $testPlanID = $this->args[self::$testPlanIDParamName];
      
      // get Test project ID in order to check that requested Platform
      // belong to same test project that test plan
      $dummy = $this->tplanMgr->get_by_id($testPlanID);
      $testProjectID = $dummy['testproject_id'];
      
      if( is_null($this->platformMgr) )
      {
        $this->platformMgr = new tlPlatform($this->dbObj,$testProjectID);
      }
      else
      {
        // extra protection ?? (20131307)  
        $this->platformMgr->setTestProjectID($testProjectID); 
      }  
      $platName = $this->args[self::$platformNameParamName];
      $platform = $this->platformMgr->getByName($platName);
      if(is_null($platform))
      {
        $status_ok = false;
        $msg = $messagePrefix . sprintf(PLATFORM_NAME_DOESNOT_EXIST_STR,$platName);
        $this->errors[] = new IXR_Error(PLATFORM_NAME_DOESNOT_EXIST, $msg);              
      }  
    } 
   
    if($status_ok)
    {
      $linkExists = $this->platformMgr->isLinkedToTestplan($platform['id'],$testPlanID);
      $ret = array('operation' => $op, 'msg' => 'nothing to do', 'linkStatus' => $linkExists);
      switch($op)
      {
        case 'link':
          if(!$linkExists)
          {
            $this->platformMgr->linkToTestplan($platform['id'],$testPlanID);
            $ret['msg'] = 'link done';
          }  
        break;
   
        case 'unlink':
          if($linkExists)
          {
            // If there are test case versions linked to test plan, that use
            // this platform, operation (as happens on GUI) can not be done
            $hits = $this->tplanMgr->countLinkedTCVersionsByPlatform($testPlanID,(array)$platform['id']); 
            if($hits[$platform['id']]['qty'] == 0)
            {  
              $this->platformMgr->unlinkFromTestplan($platform['id'],$testPlanID);     
              $ret['msg'] = 'unlink done';
            }
            else
            {
              $status_ok = false;
              $msg = $messagePrefix . sprintf(PLATFORM_REMOVETC_NEEDED_BEFORE_UNLINK_STR,$platName,$hits[$platform['id']]['qty']);
              $this->errors[] = new IXR_Error(PLATFORM_REMOVETC_NEEDED_BEFORE_UNLINK, $msg);              
            }  
          }  
        break;
      } 
      if($status_ok)
      {
        return $ret;   
      }  
    }
   
    if(!$tatus_ok)
    {
      return $this->errors;
    } 
  }

   /**
    * Update an existing test case
    * Not all test case attributes will be able to be updated using this method
    * See details below
    * 
    * @param struct $args
    * @param string $args["devKey"]
    * @param string $args["testcaseexternalid"] format PREFIX-NUMBER
    * @param int    $args["version"] optional version NUMBER (human readable) 
    * @param string $args["testcasename"] - optional
    * @param string $args["summary"] - optional
    * @param string $args["preconditions"] - optional
    * @param array  $args["steps"] - optional
    *               each element is a hash with following keys
    *               step_number,actions,expected_results,execution_type
    *
    * @param int    $args["importance"] - optional - see const.inc.php for domain
    * @param int    $args["executiontype"] - optional - see ... for domain
    * @param int    $args["status'] - optional
    * @param int    $args["estimatedexecduration'] - optional
    * @param string $args["user'] - login name used as updater - optional
    *                               if not provided will be set to user that request update
    */
  public function updateTestCase($args)
  {
    // Check test case identity
    // Check if user (devkey) has grants to do operation
    //
    // Check that configuration allow changes on Test Case
    // Check that new test case name do not collide with existent one
    //
    // translate args key to column name
    $updKeys = array("summary" => null,"preconditions" => null,
                     "importance" => null,"status" => null,
                     "executiontype" => "execution_type",
                     "estimatedexecduration" => "estimated_exec_duration");

    $resultInfo = array();
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $debug_info = null;

    $this->_setArgs($args);              
    $checkFunctions = array('authenticate','checkTestCaseIdentity');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);
    
    $tprojectID = 0;
    if($status_ok)
    {
      $tcaseID = $this->args[self::$testCaseIDParamName];
      $tprojectID = $this->tcaseMgr->getTestProjectFromTestCase($tcaseID,null);
    }

    if($status_ok)
    {
      $updaterID = $this->updateTestCaseGetUpdater($msg_prefix);
      $status_ok = ($updaterID > 0);      
    }  

    if($status_ok)
    {
      // we have got internal test case ID on checkTestCaseIdentity
      list($status_ok,$tcversion_id) = $this->updateTestCaseGetTCVID($tcaseID);
    }

    // We will check that:
    // updater has right to update
    // user doing call has also has right to update
    if($status_ok)
    {
      $ctx[self::$testProjectIDParamName] = $tprojectID;
      if( $updaterID != $this->userID )
      {
        $ctx['updaterID'] = $updaterID;
      }  

      $ck = self::CHECK_PUBLIC_PRIVATE_ATTR;
      
      $r2c = array('mgt_modify_tc');
      foreach($r2c as $right)
      {
        $status_ok = $this->userHasRight($right,$ck,$ctx);
        if(!$status_ok)
        {
          break;
        }  
      } 
    }  

    // If test case version has been executed, need to check another right
    if($status_ok)
    {
      $xc = $this->tcaseMgr->get_versions_status_quo($tcaseID, $tcversion_id);
      $checkRight = false;
      foreach($xc as $ele)
      {
        if($ele['executed'])
        {
          $checkRight = true;
          break;
        }  
      }  

      if( $checkRight )
      {
        $r2c = array('testproject_edit_executed_testcases');
        foreach($r2c as $right)
        {
          $status_ok = $this->userHasRight($right,$ck,$ctx);
          if(!$status_ok)
          {
            break;
          }  
        } 
      }

    }


    // if name update requested, it will be first thing to be udpated
    // because if we got duplicate name, we will not do update
    if($status_ok)
    {
      if(isset($this->args[self::$testCaseNameParamName]))
      {
        $ret = $this->tcaseMgr->updateName($tcaseID,
                        trim($this->args[self::$testCaseNameParamName]));
        if( !($status_ok = $ret['status_ok']) )
        {
            $this->errors[] = new IXR_Error(constant($ret['API_error_code']),$msg_prefix . $ret['msg']); 
        }
      }
    }  
    
    if($status_ok)
    {
      $fv = null;
      foreach($updKeys as $k2s => $field2update)
      {
        if(isset($this->args[$k2s]))
        {
          $fv[(is_null($field2update) ? $k2s : $field2update)] = $this->args[$k2s];
        }
      }
        
      if(!is_null($fv))
      {
        $sql = $this->tcaseMgr->updateSimpleFields($tcversion_id,$fv);
      }
    }

    // if exist proceed with steps actions / expected results update.
    if($status_ok)
    {
      if ($this->_isParamPresent(self::$stepsParamName) && !is_null($this->args[self::$stepsParamName]))
      {      
        $this->tcaseMgr->update_tcversion_steps($tcversion_id,$this->args[self::$stepsParamName]);
      }    
    }

    if($status_ok)
    {
      // update updater and modification time stamp
      $this->tcaseMgr->updateChangeAuditTrial($tcversion_id,$updaterID);
      return array('status_ok' => true, 'msg' => 'ok', 
                   'operation' => __FUNCTION__);
    }
       
    return $this->errors;
  }    

  /**
   *
   */
  function updateTestCaseGetUpdater($msg_prefix)
  {
    $status_ok = true;
    $updaterID = $this->userID;
    if ($this->_isParamPresent(self::$userParamName))
    {
      $updaterID = tlUser::doesUserExist($this->dbObj,
                          $this->args[self::$userParamName]);
      if ( !($status_ok = !is_null($updaterID)) )
      {
        $msg = $msg_prefix . sprintf(NO_USER_BY_THIS_LOGIN_STR,
                                     $this->args[self::$userParamName]);
        $this->errors[] = new IXR_Error(NO_USER_BY_THIS_LOGIN, $msg);
      }
    }
    return $status_ok ? $updaterID : -1;
  }

  /**
   *
   */
  function updateTestCaseGetTCVID($tcaseID)
  {
    $status_ok = true;
    $tcversion_id = -1;

    // if user has not provided version number, get last version
    // no matter if active or not
    if(isset($this->args[self::$versionNumberParamName]))
    {
      if( ($status_ok = $this->checkTestCaseVersionNumber()) )
      {
        // Check if version number exists for Test Case
        $ret = $this->checkTestCaseVersionNumberAncestry();
        if( !($status_ok = $ret['status_ok']) )
        {
          $this->errors[] = new IXR_Error($ret['error_code'], 
                                $msg_prefix . $ret['error_msg']); 
        }
      }
      
      if( $status_ok )
      {
        $opt = array('number' => $this->args[self::$versionNumberParamName]);
        $dummy = $this->tcaseMgr->get_basic_info($tcaseID,$opt);
        $tcversion_id = $dummy[0]['tcversion_id'];
      }
    }
    else
    {
      // get latest version info
      $dummy = $this->tcaseMgr->get_last_version_info($tcaseID);
      $dummy['tcversion_id'] = $dummy['id'];
      $tcversion_id = $dummy['tcversion_id'];
    }
    
    return array($status_ok,$tcversion_id);
  }



  /**
   *
   */
  private function helperGetTestProjectByName($msgPrefix = '')
  {                           
    $ret = array('status_ok' => true, 'info' => null);

    $name = trim($this->args[self::$testProjectNameParamName]);
    $check_op = $this->tprojectMgr->checkNameExistence($name);
    $ret['status_ok'] = !$check_op['status_ok'];     
    if( $ret['status_ok'] ) 
    {
        $ret['info'] = current($this->tprojectMgr->get_by_name($name));
    }
    else     
    {
      $msg = $msg_prefix . sprintf(TESTPROJECTNAME_DOESNOT_EXIST_STR,$name);
      $this->errors[] = new IXR_Error(TESTPROJECTNAME_DOESNOT_EXIST, $msg);
    }              
    return $ret;
  }


  /**
    * @param struct $args
    * @param string $args["devKey"]
    * @param int $args["testplanid"]
    * @param string $args["testcaseexternalid"] format PREFIX-NUMBER
    * @param int $args["buildid"] Mandatory => you can provide buildname as alternative
    * @param int $args["buildname"] Mandatory => you can provide buildid (DB ID) as alternative
    * @param int $args["platformid"] optional - BECOMES MANDATORY if Test plan has platforms
    *                                           you can provide platformname as alternative  
    *  
    * @param int $args["platformname"] optional - BECOMES MANDATORY if Test plan has platforms
    *                                           you can provide platformid as alternative  
    * @param string $args["user'] - login name => tester
    *
    */
  public function assignTestCaseExecutionTask($args)
  {
    $msgPrefix = "(" . __FUNCTION__ . ") - ";
    $args['action'] = 'assignOne';
    return $this->manageTestCaseExecutionTask($args,$msgPrefix); 
  }


  /**
    * @param struct $args
    * @param string $args["devKey"]
    * @param int $args["testplanid"]
    * @param string $args["testcaseexternalid"] format PREFIX-NUMBER
    * @param int $args["buildid"] Mandatory => you can provide buildname as alternative
    * @param int $args["buildname"] Mandatory => you can provide buildid (DB ID) as alternative
    * @param int $args["platformid"] optional - BECOMES MANDATORY if Test plan has platforms
    *                                           you can provide platformname as alternative  
    *  
    * @param int $args["platformname"] optional - BECOMES MANDATORY if Test plan has platforms
    *                                           you can provide platformid as alternative  
    * @param string $args["user'] - login name => tester 
    *                             - NOT NEEDED f $args['action'] = 'unassignAll'
    * ¸
    *
    */
  public function unassignTestCaseExecutionTask($args)
  {
    $msgPrefix = "(" . __FUNCTION__ . ") - ";
    if( !isset($args['action']) )
    {
      $args['action'] = 'unassignOne';
    }  
    return $this->manageTestCaseExecutionTask($args,$msgPrefix);  
  }

   

  /**
   * Gets the result of LAST EXECUTION for a particular testcase on a test plan.
   * If there are no filter criteria regarding platform and build,
   * result will be get WITHOUT checking for a particular platform and build.
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["tplanid"]
   * @param string $args["testcaseexternalid"] format PREFIX-NUMBER
   * @param int $args["buildid"] Mandatory => you can provide buildname as alternative
   * @param int $args["buildname"] Mandatory => you can provide buildid (DB ID) as alternative
   * @param int $args["platformid"] optional - BECOMES MANDATORY if Test plan has platforms
   *                                           you can provide platformname as alternative  
   *  
   * @param int $args["platformname"] optional - BECOMES MANDATORY if Test plan has platforms
   *                                           you can provide platformid as alternative  
   *
   *
   * @return mixed $resultInfo
   *
   * @access public
   */
  public function getTestCaseAssignedTester($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $status_ok=true;
    $this->_setArgs($args);
    $resultInfo=array();

    // Checks are done in order
    $checkFunctions = array('authenticate','checkTestPlanID','checkTestCaseIdentity','checkBuildID');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);

    // Check if requested test case is linke to test plan
    // if answer is yes, get link info, in order to be able to check if 
    // we need also platform info
    if( $status_ok )
    {
      $execContext = array('tplan_id' => $this->args[self::$testPlanIDParamName],
                           'platform_id' => null,
                           'build_id' => $this->args[self::$buildIDParamName]);

      $tplan_id = $this->args[self::$testPlanIDParamName];
      $tcase_id = $this->args[self::$testCaseIDParamName];
      $filters = array('exec_status' => "ALL", 'active_status' => "ALL",
                       'tplan_id' => $tplan_id, 'platform_id' => null);
      
      $info = $this->tcaseMgr->get_linked_versions($tcase_id,$filters,array('output' => "feature_id"));

      // more than 1 item => we have platforms
      // access key => tcversion_id, tplan_id, platform_id
      $link = current($info);
      $link = $link[$tplan_id]; 
      $hits = count($link);
      $check_platform = (count($hits) > 1) || !isset($link[0]);
    }

    if( $status_ok && $check_platform )
    {
      // this means that platform is MANDATORY
      if( !$this->_isParamPresent(self::$platformIDParamName,$msg_prefix) && 
          !$this->_isParamPresent(self::$platformNameParamName,$msg_prefix) )
      {
        $status_ok = false;
        $pname = self::$platformNameParamName . ' OR ' . self::$platformIDParamName; 
        $msg = $messagePrefix . sprintf(MISSING_REQUIRED_PARAMETER_STR, $pname);
        $this->errors[] = new IXR_Error(MISSING_REQUIRED_PARAMETER, $msg);              
      }  
      else
      {
        // get platform_id and check it
        if( ($status_ok = $this->checkPlatformIdentity($tplan_id)) )
        {
          $platform_set = $this->tplanMgr->getPlatforms($tplan_id,array('outputFormat' => 'mapAccessByID', 
                                                                         'outputDetails' => 'name'));

          // Now check if link has all 3 components
          // test plan, test case, platform
          $platform_id = $this->args[self::$platformIDParamName];
          $platform_info = array($platform_id => $platform_set[$platform_id]);

          if( ($status_ok = $this->_checkTCIDAndTPIDValid($platform_info,$msg_prefix)) )
          {
            $execContext['platform_id'] = $platform_id;
          }  
        }  
      }  
    }

    if( $status_ok )
    {
      $getOpt = array('output' => 'assignment_info', 'build4assignment' => $execContext['build_id']);
      $dummy = $this->tplanMgr->getLinkInfo($tplan_id,$tcase_id,$platform_id,$getOpt);
      $resultInfo[0] = array('user_id' => $dummy[0]['user_id'],'login' => $dummy[0]['login'],
                             'first' => $dummy[0]['first'], 'last' => $dummy[0]['last']);
    }
    
    return $status_ok ? $resultInfo : $this->errors;

  }



 /**
  * Returns all bugs linked to a particular testcase on a test plan.
  * If there are no filter criteria regarding platform and build,
  * result will be get WITHOUT checking for a particular platform and build.
  *
  * @param struct $args
  * @param string $args["devKey"]
  * @param int $args["tplanid"]
  * @param int $args["testcaseid"]: Pseudo optional.
  *                                 if does not is present then testcaseexternalid MUST BE present
  *
  * @param int $args["testcaseexternalid"]: Pseudo optional.
  *                                         if does not is present then testcaseid MUST BE present
  *
  * @param string $args["platformid"]: optional. 
  *                                    ONLY if not present, then $args["platformname"] 
  *                                    will be analized (if exists)
  *
  * @param string $args["platformname"]: optional (see $args["platformid"])
  *
  * @param int $args["buildid"]: optional
  *                              ONLY if not present, then $args["buildname"] will be analized (if exists)
  * 
  * @param int $args["buildname"] - optional (see $args["buildid"])
  *
  *
  * @return mixed $resultInfo
  *               if execution found
  *               array that contains a map with these keys:
  *               bugs
  *
  *               if test case has not been execute,
  *               array('id' => -1)
  *
  * @access public
  */
   public function getTestCaseBugs($args)
   {
     $operation=__FUNCTION__;
     $msg_prefix="({$operation}) - ";
       
     $this->_setArgs($args);
     $resultInfo = array();
     $status_ok=true;
 
               
     // Checks are done in order
     $checkFunctions = array('authenticate','checkTestPlanID','checkTestCaseIdentity');
     $status_ok=$this->_runChecks($checkFunctions,$msg_prefix) && 
                $this->_checkTCIDAndTPIDValid(null,$msg_prefix) && 
                $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR);       

     $execContext = array('tplan_id' => $this->args[self::$testPlanIDParamName],
                          'platform_id' => null,'build_id' => null);

     // Now we can check for Optional parameters
     if($this->_isBuildIDPresent() || $this->_isBuildNamePresent())
     {
       if( ($status_ok =  $this->checkBuildID($msg_prefix)) )
       {
         $execContext['build_id'] = $this->args[self::$buildIDParamName];  
       }  
     }  

     if( $status_ok )
     {
        if( $this->_isParamPresent(self::$platformIDParamName,$msg_prefix) ||
            $this->_isParamPresent(self::$platformNameParamName,$msg_prefix) )
        {
          $status_ok = $this->checkPlatformIdentity($this->args[self::$testPlanIDParamName]);
          if( $status_ok)
          {
            $execContext['platform_id'] = $this->args[self::$platformIDParamName];  
          }  
        }  
     }  

     if( $status_ok )
     {
       $sql = " SELECT id AS exec_id FROM {$this->tables['executions']} " . 
              " WHERE testplan_id = {$this->args[self::$testPlanIDParamName]} " .
              " AND tcversion_id IN (" .
              " SELECT id FROM {$this->tables['nodes_hierarchy']} " .
              " WHERE parent_id = {$this->args[self::$testCaseIDParamName]})";

       if(!is_null($execContext['build_id']))
       {
         $sql .= " AND build_id = " . intval($execContext['build_id']);
       }  

       if(!is_null($execContext['platform_id']))
       {
         $sql .= " AND platform_id = " . intval($execContext['platform_id']);
       }  

       $rs = $this->dbObj->fetchRowsIntoMap($sql,'exec_id');
       if( is_null($rs) )
       {
         // has not been executed
         // execution id = -1 => test case has not been runned.
         $resultInfo[]=array('id' => -1);
       }  
       else
       {
         $targetIDs=array();
         foreach($rs as $execrun)
         {
           $targetIDs[]=$execrun['exec_id'];
         }
         $resultInfo[0]['bugs'] = array();
         $sql = " SELECT DISTINCT bug_id FROM {$this->tables['execution_bugs']} " . 
                " WHERE execution_id in (" . implode(',',$targetIDs) . ")";
         $resultInfo[0]['bugs'] = (array)$this->dbObj->get_recordset($sql);       
       }  
     }
     
     return $status_ok ? $resultInfo : $this->errors;
   }
 

/**
    * @param struct $args
    * @param string $args["devKey"]
    * @param string $args["action"]: assignOne, unassignOne, unassignAll
    * 
    * @param int $args["testplanid"]
    * @param string $args["testcaseexternalid"] format PREFIX-NUMBER
    * @param int $args["buildid"] Mandatory => you can provide buildname as alternative
    * @param int $args["buildname"] Mandatory => you can provide buildid (DB ID) as alternative
    * @param int $args["platformid"] optional - BECOMES MANDATORY if Test plan has platforms
    *                                           you can provide platformname as alternative  
    *  
    * @param int $args["platformname"] optional - BECOMES MANDATORY if Test plan has platforms
    *                                           you can provide platformid as alternative  
    * @param string $args["user'] - login name => tester
    *
    */
  private function manageTestCaseExecutionTask($args,$msg_prefix)
  {
    $status_ok=true;
    $this->_setArgs($args);
    $resultInfo=array();

    // Checks are done in order
    $checkFunctions = array('authenticate','checkTestPlanID','checkTestCaseIdentity','checkBuildID');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);

    if( $status_ok )
    {
      switch ($args['action'])
      {
        case 'assignOne':
        case 'unassignOne':
          if( ($status_ok = $this->_isParamPresent(self::$userParamName,$msg_prefix,self::SET_ERROR)) )
          {
            $tester_id = tlUser::doesUserExist($this->dbObj,$this->args[self::$userParamName]);          
            if( !($status_ok = !is_null($tester_id)) )
            {  
              $msg = $msg_prefix . sprintf(NO_USER_BY_THIS_LOGIN_STR,$this->args[self::$userParamName]);
              $this->errors[] = new IXR_Error(NO_USER_BY_THIS_LOGIN, $msg);  
            }
          }  
        break;

        case 'unassignAll':
        break;
      }
    }
      
    // Check if requested test case is linked to test plan
    // if answer is yes, get link info, in order to be able to check if 
    // we need also platform info
    if( $status_ok )
    {
      $execContext = array('tplan_id' => $this->args[self::$testPlanIDParamName],
                           'platform_id' => null,
                           'build_id' => $this->args[self::$buildIDParamName]);

      $tplan_id = $this->args[self::$testPlanIDParamName];
      $tcase_id = $this->args[self::$testCaseIDParamName];
      $filters = array('exec_status' => "ALL", 'active_status' => "ALL",
                       'tplan_id' => $tplan_id, 'platform_id' => null);
      
      $info = $this->tcaseMgr->get_linked_versions($tcase_id,$filters,array('output' => "feature_id"));

      // more than 1 item => we have platforms
      // access key => tcversion_id, tplan_id, platform_id
      $link = current($info);
      $link = $link[$tplan_id];   // Inside test plan, is indexed by platform
      $hits = count($link);
      $platform_id = 0;
      $check_platform = (count($hits) > 1) || !isset($link[0]);
    }

    if( $status_ok && $check_platform )
    {
      // this means that platform is MANDATORY
      if( !$this->_isParamPresent(self::$platformIDParamName,$msg_prefix) && 
          !$this->_isParamPresent(self::$platformNameParamName,$msg_prefix) )
      {
        $status_ok = false;
        $pname = self::$platformNameParamName . ' OR ' . self::$platformIDParamName; 
        $msg = $messagePrefix . sprintf(MISSING_REQUIRED_PARAMETER_STR, $pname);
        $this->errors[] = new IXR_Error(MISSING_REQUIRED_PARAMETER, $msg);              
      }  
      else
      {
        // get platform_id and check it
        if( ($status_ok = $this->checkPlatformIdentity($tplan_id)) )
        {
          $platform_set = $this->tplanMgr->getPlatforms($tplan_id,array('outputFormat' => 'mapAccessByID', 
                                                                        'outputDetails' => 'name'));

          // Now check if link has all 3 components
          // test plan, test case, platform
          $platform_id = $this->args[self::$platformIDParamName];
          $platform_info = array($platform_id => $platform_set[$platform_id]);

          if( ($status_ok = $this->_checkTCIDAndTPIDValid($platform_info,$msg_prefix)) )
          {
            $execContext['platform_id'] = $platform_id;
          }  
        }  
      }  
    }

    
    if( $status_ok )
    {
      $assignment_mgr = new assignment_mgr($this->dbObj);
      $types = $assignment_mgr->get_available_types();
      
      // Remove old execution task assignment 
      // `id` int(10) unsigned NOT NULL auto_increment,
      // `type` int(10) unsigned NOT NULL default '1',
      // `feature_id` int(10) unsigned NOT NULL default '0',
      // `user_id` int(10) unsigned default '0',
      // `build_id` int(10) unsigned default '0',
      // `deadline_ts` datetime NULL,
      // `assigner_id`  int(10) unsigned default '0',
      // `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      // `status` int(10) unsigned default '1',

      // ATTENTION WITH PLATFORMS
      $link = is_null($execContext['platform_id']) ? $link[0] : $link[$execContext['platform_id']];
      $feature = array($link['feature_id'] => array('build_id' => $execContext['build_id']));
      
      switch ($args['action'])
      {
        case 'unassignOne':
          $signature[] = array('type' => $types['testcase_execution']['id'], 'user_id' => $tester_id, 
                               'feature_id' => $link['feature_id'],'build_id' => $execContext['build_id']);
          $assignment_mgr->deleteBySignature($signature);
        break;

        case 'assignOne':
          // Step 1 - remove if exists
          $signature[] = array('type' => $types['testcase_execution']['id'], 'user_id' => $tester_id, 
                               'feature_id' => $link['feature_id'],'build_id' => $execContext['build_id']);
          $assignment_mgr->deleteBySignature($signature);
    
          // Step 2 - Now assign
          $assign_status = $assignment_mgr->get_available_status();

          $oo[$link['feature_id']]['type'] = $types['testcase_execution']['id'];
          $oo[$link['feature_id']]['status'] = $assign_status['open']['id'];
          $oo[$link['feature_id']]['user_id'] = $tester_id;
          $oo[$link['feature_id']]['assigner_id'] = $this->userID;
          $oo[$link['feature_id']]['build_id'] = $execContext['build_id'];
          $assignment_mgr->assign($oo);
        break;

        case 'unassignAll':
          $oo[$link['feature_id']]['type'] = $types['testcase_execution']['id'];
          $oo[$link['feature_id']]['build_id'] = $execContext['build_id'];
          $assignment_mgr->delete_by_feature_id_and_build_id($oo);
        break;
      }  

      $resultInfo = array("status" => true, "args" => $this->args);
      unset($resultInfo['args']['devKey']);
    }  

    return $status_ok ? $resultInfo : $this->errors;
  }

  /**
   *
   */
  public function getProjectKeywords($args)
  {
    $messagePrefix="(" .__FUNCTION__ . ") - ";
        
    $this->_setArgs($args);
    $checkFunctions = array('authenticate','checkTestProjectID');       
    $status_ok=$this->_runChecks($checkFunctions,$messagePrefix);       
  
    if($status_ok)
    {
      $itemSet = $this->getValidKeywordSet(intval($this->args[self::$testProjectIDParamName]),
                                           '',true,'getProjectKeywords');
      return $itemSet;
    }
    else
    {
      return $this->errors;
    } 
  }


  /**
   * Gets list of keywords for a given Test case
   *
   * @param mixed $testcaseid can be int or array
   *              $testcaseexternalid can be int or array
   *
   * @return map indexed by test case internal (DB) ID
   *
   * @access public
   */
  public function getTestCaseKeywords($args)
  {
    $msgPrefix="(" .__FUNCTION__ . ") - ";
    $this->_setArgs($args);

    // Prepare material for checkTestCaseSetIdentity()
    $a2check = array(self::$testCaseIDParamName,self::$testCaseExternalIDParamName);
    foreach($a2check as $k2c)
    {
      if( isset($this->args[$k2c]) )
      {
        $retAsArray = is_array($this->args[$k2c]);
        $this->args[$k2c] = (array)$this->args[$k2c];
        $outBy = $k2c;
        break;
      }  
    }  
 
    $checkFunctions = array('authenticate','checkTestCaseSetIdentity');
    $status_ok=$this->_runChecks($checkFunctions,$msgPrefix);
    
    if($status_ok)
    {
      foreach($this->args[self::$testCaseIDParamName] as $idx => $tcaseID)
      {
        $accessKey = ($outBy == self::$testCaseIDParamName) ? $tcaseID
                                                            : $this->args[$outBy][$idx]; 
                                                             
        $itemSet[$accessKey] = $this->tcaseMgr->get_keywords_map(intval($tcaseID));
      }  
      // return $retAsArray ? $itemSet : current($itemSet);
      return $itemSet;
    }
    else
    {
      return $this->errors;
    }
  }

  /**
   *  Delete a test plan and all related link to other items
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["$tplanID"]
   *
   * @return mixed $resultInfo
   *         [status]  => true/false of success
   *         [id]      => result id or error code
   *         [message]  => optional message for error message string
   * @access public
   */
  public function deleteTestPlan($args)
  {
    $resultInfo = array();
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
     
    $this->_setArgs($args);
    $resultInfo[0]["status"] = false;
     
    $checkFunctions = array('authenticate','checkTestPlanID');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);

    if($status_ok)
    {
      if( $this->userHasRight("exec_delete",self::CHECK_PUBLIC_PRIVATE_ATTR) )
      {
        $this->tplanMgr->delete($args[self::$testPlanIDParamName]);
        $resultInfo[0]["status"] = true;
        $resultInfo[0]["id"] = $args[self::$testPlanIDParamName];
        $resultInfo[0]["message"] = GENERAL_SUCCESS_STR;
        $resultInfo[0]["operation"] = $operation;
      }
      else
      {
        $status_ok = false;
        $this->errors[] = new IXR_Error(CFG_DELETE_EXEC_DISABLED,CFG_DELETE_EXEC_DISABLED_STR);
      }
    }

    return $status_ok ? $resultInfo : $this->errors;
  }



  /**
   * addTestCaseKeywords
   * @param struct $args
   * @param string $args["devKey"]
   * @param array $args["keywords"]: map key testcaseexternalid
   *                                     values array of keyword name 
   * 
   * @return mixed $resultInfo
   *
   * @internal revisions
   * @since 1.9.14
   */
  function addTestCaseKeywords($args)
  {
    $ret = $this->checksForManageTestCaseKeywords($args,'add');
    if( $ret['status_ok'] )
    {
      $kwSet = $this->args[self::$keywordNameParamName];
      return $this->manageTestCaseKeywords($kwSet,$ret['tprojectSet'],'add');
    }  
    return $this->errors;
  }

  /**
   * removeTestCaseKeywords
   * @param struct $args
   * @param string $args["devKey"]
   * 
   * @return mixed $resultInfo
   *
   * @internal revisions
   * @since 1.9.14
   */
  function removeTestCaseKeywords($args)
  {
    $ret = $this->checksForManageTestCaseKeywords($args,'remove');
    if( $ret['status_ok'] )
    {
      $kwSet = $this->args[self::$keywordNameParamName];
      return $this->manageTestCaseKeywords($kwSet,$ret['tprojectSet'],'remove');
    }  
    return $this->errors;
  }



  /**
   * @used by manageTestCaseKeywords
   */
  protected function checksForManageTestCaseKeywords($args,$action)
  {
    $operation = str_replace('checksForManage',$action,__FUNCTION__);
    $msg_prefix="({$operation}) - ";
    $resultInfo = array();

    $this->_setArgs($args);
    $checkFunctions = array('authenticate');

    // Check on user rights can have some problems if test cases do not belong
    // to same test project
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);
    if( $status_ok )
    {
      $items = array_keys($this->args[self::$keywordNameParamName]);
      $status_ok = $this->checkTestCaseSetIdentity($msg_prefix,$items);
    }

    if( $status_ok )
    {
      // Get test projects
      $idSet = $this->args[self::$testCaseIDParamName];

      foreach( $idSet as $key => $val ) 
      {
        // indexed by same value than keywords
        $tprojectSet[$items[$key]] = $this->tcaseMgr->get_testproject($val);

        // Do authorization checks, all or nothing
        // userHasRight() on failure set error to return to caller
        $status_ok = $this->userHasRight("mgt_modify_tc",
                                         self::CHECK_PUBLIC_PRIVATE_ATTR,
                                         array(self::$testProjectIDParamName => $tprojectSet[$items[$key]])
                                        );
        if(!$status_ok)
        {
          break;
        }  
      }
    }  

    $ret['status_ok'] = $status_ok;
    $ret['tprojectSet'] = $tprojectSet;

    return $ret;
  }

  /**
   * manageTestCaseKeywords
   * @param struct 
   * 
   * @param string $action: domain 'add','remove'
   * @return mixed $resultInfo
   *
   * @internal revisions
   * @since 1.9.14
   */
  protected function manageTestCaseKeywords($keywords,$tprojects,$action)
  {
    switch($action)
    {
      case 'add':
        $method2call = 'addKeywords';
      break;

      case 'delete':
      case 'remove':
        $method2call = 'deleteKeywords';
      break;

      default:
        $resultInfo['status_ok'] = false;
        $resultInfo['verbose'] = __FUNCTION__ . ' :: Banzai!! - No valida method';
        return $resultInfo;
      break;
    }

    $kw = array();
    $resultInfo['validKeywords'] = null;
    $resultInfo['status_ok'] = true;

    foreach($keywords as $ak => $kwset)
    {
      $kw[$ak] = $this->getValidKeywordSet($tprojects[$ak],
                                      implode(",",$kwset),true,true);
      
      $resultInfo['validKeywords'][$ak] = $kw[$ak];
      $resultInfo['status_ok'] = $resultInfo['status_ok'] && ($kw[$ak] != '');
    }  
    
    if($resultInfo['status_ok'])
    {
      foreach($kw as $ak => $val)
      {
        // return array($this->tcaseE2I[$ak],array_keys($val),$ak)
        $this->tcaseMgr->$method2call($this->tcaseE2I[$ak],array_keys($val));
      }  
    }

    return $resultInfo;
  }


 /**
   * Helper method to see if in a test case set identity provided is valid 
   * Identity can be specified in one of these modes:
   *
   * test case internal id
   * test case external id  (PREFIX-NNNN) 
   * 
   * If everything OK, an array of test case internal ID is setted.
   *
   * @param string $messagePrefix used to be prepended to error message
   *
   * @return boolean
   * @access protected
   */    
    protected function checkTestCaseSetIdentity($messagePrefix='',$itemSet=null)
    {
      // Three Cases - Internal ID, External ID, No Id        
      $status_ok = false;
      $fromExternal = false;
      $fromInternal = false;
      $fromItemSet = false;

      $tcaseID = 0;
      $tcaseIDSet = null;
      $tcaseE2I = null;  // External to Internal

      if(!is_null($itemSet))
      {
        $fromExternal = true; 
        $fromItemSet = true;
        $errorCode = INVALID_TESTCASE_EXTERNAL_ID;
        $msg = $messagePrefix . INVALID_TESTCASE_EXTERNAL_ID_STR;
        
        foreach($itemSet as $tcaseExternalID)
        {
          $tcaseE2I[$tcaseExternalID] =
            $tcaseIDSet[] = intval($this->tcaseMgr->getInternalID($tcaseExternalID));
        }
      } 

      if($this->_isTestCaseExternalIDPresent())
      {
        $fromExternal = true;
        $errorCode = INVALID_TESTCASE_EXTERNAL_ID;
        $msg = $messagePrefix . INVALID_TESTCASE_EXTERNAL_ID_STR;

        foreach($this->args[self::$testCaseExternalIDParamName] as $tcaseExternalID)
        {
          $tcaseIDSet[] = intval($this->tcaseMgr->getInternalID($tcaseExternalID));            
        }
      }  

      if($this->_isTestCaseIDPresent())
      {
        $fromInternal = true;
        $errorCode = INVALID_TESTCASE_EXTERNAL_ID;
        $msg = $messagePrefix . INVALID_TCASEID_STR;        
        $tcaseIDSet = $this->args[self::$testCaseIDParamName];       
      }       
       
      if(!is_null($tcaseIDSet))
      {
        $status_ok = true;
        foreach($tcaseIDSet as $idx => $tcaseID)
        {
          if (( ($tcaseID = intval($tcaseID)) <= 0 ) || 
              (!$this->_isTestCaseIDValid($tcaseID,$messagePrefix)))
          {
            $status_ok = false;

            if($fromInternal)
            {  
              $this->errors[] = new IXR_Error($errorCode,sprintf($msg,$tcaseID));
            }
            else 
            {
              if($fromItemSet)
              {
                $tcaseExternalID = $itemSet[$idx];
              } 
              else
              {
                $tcaseExternalID = $this->args[self::$testCaseExternalIDParamName][$idx];
              } 
              $this->errors[] = new IXR_Error($errorCode,sprintf($msg,$tcaseExternalID));                  
            }  
          }
        }  
      }  
       
      if($status_ok)
      {
        $this->_setTestCaseID($tcaseIDSet);
        $this->tcaseE2I = $tcaseE2I;
      }  

      return $status_ok;
    }   


  /**
   *
   */
  private function getTcaseDbId($items)
  {
    $tcaseIDSet = null;
    foreach($items as $idx => $eID)
    {
      $tcaseIDSet[$idx] = intval($this->tcaseMgr->getInternalID($eID)); 
    }
    return $tcaseIDSet;
  }

  /**
   *  Delete a test project and all related link to other items
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["prefix"]
   *
   * @return mixed $resultInfo
   *         [status]  => true/false of success
   *         [message]  => optional message for error message string
   * @access public
   */
  public function deleteTestProject($args)
  {
    $resultInfo = array();
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
     
    $this->_setArgs($args);
    $resultInfo[0]["status"] = false;
     
    $checkFunctions = array('authenticate');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);
    
    if($status_ok)
    {
      $status_ok = $this->userHasRight("mgt_modify_product");
    }
  
    if($status_ok)
    {
       $status_ok = $this->_isParamPresent(self::$prefixParamName,$msg_prefix,true);
    }
  
    if($status_ok)
    {
      if( ($info = $this->tprojectMgr->get_by_prefix($this->args[self::$prefixParamName])) )
      {
        $this->tprojectMgr->delete($info['id']);
        $resultInfo[0]["status"] = true;
      }  
      else
      {
        $status_ok = false;
        $msg = $msg_prefix . sprintf(TPROJECT_PREFIX_DOESNOT_EXIST_STR,
                             $this->args[self::$prefixParamName]);
        $this->errors[] = new IXR_Error(TPROJECT_PREFIX_DOESNOT_EXIST, $msg);
      }
    }

    return $status_ok ? $resultInfo : $this->errors;
  }


 /**
   * Update value of Custom Field with scope='design' 
   * for a given Test Suite
   *
   * @param struct $args
   * @param string $args["devKey"]: used to check if operation can be done.
   *                                if devKey is not valid => abort.
   *
   * @param string $args["testsuiteid"]:  
   * @param string $args["testprojectid"]: 
   * @param string $args["customfields"]
   *               contains an map with key:Custom Field Name, value: value for CF.
   *               VERY IMPORTANT: value must be formatted in the way it's written to db,
   *               this is important for types like:
   *
   *               DATE: strtotime()
   *               DATETIME: mktime()
   *               MULTISELECTION LIST / CHECKBOX / RADIO: se multipli selezione ! come separatore
   *
   *
   *               these custom fields must be configured to be writte during execution.
   *               If custom field do not meet condition value will not be written
   *
   * @return mixed null if everything ok, else array of IXR_Error objects
   *         
   * @access public
   */    
  public function updateTestSuiteCustomFieldDesignValue($args)
  {
    $msg_prefix="(" .__FUNCTION__ . ") - ";
    $this->_setArgs($args);  
    
    $checkFunctions = array('authenticate','checkTestProjectID',
                            'checkTestSuiteID');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);       

    if( $status_ok )
    {
      if(!$this->_isParamPresent(self::$customFieldsParamName) )
      {
        $status_ok = false;
        $msg = sprintf(MISSING_REQUIRED_PARAMETER_STR,self::$customFieldsParamName);
        $this->errors[] = new IXR_Error(MISSING_REQUIRED_PARAMETER, $msg);              
      }
    }
      
    if( $status_ok )
    {
      // now check if custom fields are ok
      // For each custom field need to check if:
      // 1. is linked to test project
      // 2. is available for Test Suite at design time
      $cfieldMgr = new cfield_mgr($this->dbObj);
      
      // Just ENABLED
      $linkedSet = $cfieldMgr->get_linked_cfields_at_design($this->args[self::$testProjectIDParamName],
                                                            cfield_mgr::ENABLED,null,'testsuite',null,'name');
      if( is_null($linkedSet) )
      {
        $status_ok = false;
        $msg = NO_CUSTOMFIELDS_DT_LINKED_TO_TESTSUITES_STR;
        $this->errors[] = new IXR_Error(NO_CUSTOMFIELDS_DT_LINKED_TO_TESTSUITES, $msg);              
      }
    }

    if( $status_ok )
    {
      $cfSet = $args[self::$customFieldsParamName];
      $itemID = $args[self::$testSuiteIDParamName];

      foreach($cfSet as $cfName => $cfValue)
      {
        // $accessKey = "custom_field_" . $item['id'] . <field_type_id>_<cfield_id>
        //  design_values_to_db($hash,$node_id,$cf_map=null,$hash_type=null)
        //  
        // Simple check: if name is not present on set => ignore
        if( isset($linkedSet[$cfName]) )
        {
          $item = $linkedSet[$cfName];
          $accessKey = "custom_field_" . $item['type'] . '_' . $item['id'];
          $hash[$accessKey] = $cfValue;
          $cfieldMgr->design_values_to_db($hash,$itemID);
          $ret[] = array('status' => 'ok' ,
                         'msg' => 'Custom Field:' . $cfName . ' processed ');
        } 
        else
        {
          $ret[] = array('status' => 'ko' ,
                         'msg' => 'Custom Field:' . $cfName . ' skipped ');
        } 

        return $ret;
      }        
    }
    else
    {
      return $this->errors;
    }  
  }

  /**
   * Update value of Custom Field with scope='design'
   * for a given Build
   *
   * @param struct $args
   * @param string $args["devKey"]: used to check if operation can be done.
   *                                if devKey is not valid => abort.
   *
   * @param string $args["buildid"]:
   * @param string $args["testprojectid"]:
   * @param string $args["customfields"]
   *               contains an map with key:Custom Field Name, value: value for CF.
   *               VERY IMPORTANT: value must be formatted in the way it's written to db,
   *               this is important for types like:
   *
   *               DATE: strtotime()
   *               DATETIME: mktime()
   *               MULTISELECTION LIST / CHECKBOX / RADIO: se multipli selezione ! come separatore
   *
   *
   *               these custom fields must be configured to be writte during execution.
   *               If custom field do not meet condition value will not be written
   *
   * @return mixed null if everything ok, else array of IXR_Error objects
   *
   * @access public
   */
  public function updateBuildCustomFieldsValues($args)
  {
    $msg_prefix="(" .__FUNCTION__ . ") - ";
    $this->_setArgs($args);

    $checkFunctions = array('authenticate','checkTestProjectID', 'checkBuildID');
    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);

    if( $status_ok )
    {
      if(!$this->_isParamPresent(self::$customFieldsParamName) )
      {
        $status_ok = false;
        $msg = sprintf(MISSING_REQUIRED_PARAMETER_STR,self::$customFieldsParamName);
        $this->errors[] = new IXR_Error(MISSING_REQUIRED_PARAMETER, $msg);
      }
    }

    if( $status_ok )
    {
      // now check if custom fields are ok
      // For each custom field need to check if:
      // 1. is linked to test project
      // 2. is available for Build at design time
      $cfieldMgr = new cfield_mgr($this->dbObj);

      // Just ENABLED
      $linkedSet = $cfieldMgr->get_linked_cfields_at_design($this->args[self::$testProjectIDParamName],
                                                            cfield_mgr::ENABLED,null,'build',null,'name');
      if( is_null($linkedSet) )
      {
        $status_ok = false;
        $msg = NO_CUSTOMFIELDS_DT_LINKED_TO_BUILDS_STR;
        $this->errors[] = new IXR_Error(NO_CUSTOMFIELDS_DT_LINKED_TO_BUILDS, $msg);
      }
    }

    if( $status_ok )
    {
      $cfSet = $args[self::$customFieldsParamName];
      $ret = array();
      foreach($cfSet as $cfName => $cfValue)
      {
        // $accessKey = "custom_field_" . $item['id'] . <field_type_id>_<cfield_id>
        //  design_values_to_db($hash,$node_id,$cf_map=null,$hash_type=null)
        //
        // Simple check: if name is not present on set => ignore
        if( isset($linkedSet[$cfName]) )
        {
          $item = $linkedSet[$cfName];
          $accessKey = "custom_field_" . $item['type'] . '_' . $item['id'];
          $hash[$accessKey] = $cfValue;
          $cfieldMgr->design_values_to_db($hash,$args[self::$buildIDParamName],null,null,'build');
          // Add the result for each custom field to the returned array
          array_push($ret, array('status' => 'ok' ,
                                 'msg' => 'Custom Field:' . $cfName . ' processed '));
        }
        else
        {
          array_push($ret, array('status' => 'ko' ,
                                 'msg' => 'Custom Field:' . $cfName . ' skipped '));
        }
      }
      // Return the result after all of the fields have been processed
      return $ret;
    }
    else
    {
        return $this->errors;
    }
  }

 /**
  * Returns all test suites inside target 
  * test project with target name
  *
  * @param
  * @param struct $args
  * @param string $args["devKey"]
  * @param int $args["testsuitename"]
  * @param string $args["prefix"]
  * @return mixed $resultInfo
  * 
  * @access public
  */
  public function getTestSuite($args)
  { 
    $ope = __FUNCTION__;
    $msg_prefix = "({$ope}) - ";

    $this->_setArgs($args);
    $status_ok = 
      $this->_runChecks(array('authenticate'),$msg_prefix);

    if($status_ok)
    {
      // Check for mandatory parameters
      $k2s = array(self::$testSuiteNameParamName,
                   self::$prefixParamName);

      foreach ($k2s as $target) 
      {
        $ok = $this->_isParamPresent($target,$msg_prefix,self::SET_ERROR);
        $status_ok = $status_ok && $ok; 
      }
    }  

    if( $status_ok )
    {
      // optionals
      //$details='simple';
      //$k2s=self::$detailsParamName;
      //if( $this->_isParamPresent($k2s) )
      //{ 
      //  $details = $this->args[$k2s];
      //}
    }  
    
    if( $status_ok )
    {
      $tprojectMgr = new testproject($this->dbObj);
      
      $pfx = $this->args[self::$prefixParamName];
      $tproj = $tprojectMgr->get_by_prefix($pfx);

      if(is_null($tproj))
      {
        $status_ok = false;
        $msg = $msg_prefix . sprintf(TPROJECT_PREFIX_DOESNOT_EXIST_STR,$pfx);
        $this->errors[] = new IXR_Error(TPROJECT_PREFIX_DOESNOT_EXIST, $msg);
      }  
      else
      {
        $ctx[self::$testProjectIDParamName] = $dummy['id'];
      }  
    }  
    
    if($status_ok && 
       $this->userHasRight("mgt_view_tc",self::CHECK_PUBLIC_PRIVATE_ATTR,$ctx))
    { 
     $opt = array('recursive' => false, 
                  'exclude_testcases' => true);
     
     // $target = $this->dbObj->prepare_string($tg);
     // $filters['additionalWhereClause'] =
     // " AND name = '{$target}' "; 
     $filters = null;
     $items = 
       $tprojectMgr->get_subtree($tproj['id'],$filters,$opt);
   
     $ni = array();
     if( !is_null($items) && ($l2d = count($items)) > 0)  
     {
       $tg = $this->args[self::$testSuiteNameParamName];
       for($ydx=0; $ydx <= $l2d; $ydx++)
       {
         if(strcmp($items[$ydx]['name'],$tg) == 0 )
         {
           unset($items[$ydx]['tcversion_id']); 
           $ni[] = $items[$ydx];   
         } 
       } 
     } 
     else
     {
      $ni = $items;
     } 
    }

    return $status_ok ? $ni : $this->errors;    
  }  // function end

   /**
    * Get Issue Tracker System by name
    *
    * @param struct $args
    * @param string $args["devKey"]
    * @param string $args["itsname"] ITS name 
    * @return mixed $itsObject      
    * @access public
    */
    public function getIssueTrackerSystem($args,$call=null)
    {
      $operation=__FUNCTION__;
      $msg_prefix="({$operation}) - ";

      $this->_setArgs($args);

      $extCall = is_null($call); 
      if( $extCall )
      {
        $this->authenticate();
      }  

      $ret = null;
      if( is_null($this->itsMgr) )
      {
        $this->itsMgr = new tlIssueTracker($this->dbObj);
      } 

      $ret = $this->itsMgr->getByName($this->args[self::$itsNameParamName]);
      $status_ok = !is_null($ret);
      if( !$status_ok )
      {  
        $msg = $msg_prefix . sprintf(ITS_NOT_FOUND_STR, $this->args[self::$itsNameParamName]);
        $this->errors[] = new IXR_Error(ITS_NOT_FOUND, $msg);
      }  
  
      if( $extCall )
      {
        if( !$status_ok )
        {
          $ret = $this->errors;
        } 
      } 
      return $ret;
    }


  /**
   * 
   */
  function validateDateISO8601($dateAsString)
  {
    return $this->validateDate($dateAsString);
  }

  /**
   *
   */
  function validateDate($dateAsString, $format = 'Y-m-d')
  {
    $d = DateTime::createFromFormat($format, $dateAsString);
    return $d && $d->format($format) == $dateAsString;
  }

/**
   * Get requirements
   *
   * @param string $args["testprojectid"]
   * @param string $args["testplanid"] OPTIONAL
   * @param string $args["platformid"] OPTIONAL
   *
   * @return mixed error if someting's wrong, else array of test cases
   *
   * @access public
   */
  public function getRequirements($args)
  {
    $msg_prefix="(" .__FUNCTION__ . ") - ";
    $this->_setArgs($args);  
    
    $checkFunctions = array('authenticate', 'checkTestProjectID');
    $status_ok = $this->_runChecks($checkFunctions, $msg_prefix);

    if( $status_ok )
    {
      $context['tproject_id'] = $this->args[self::$testProjectIDParamName];

      // check if a context (test plan/platform) is provided
      if ($this->_isParamPresent(self::$testPlanIDParamName)) {
        $status_ok = $this->checkTestPlanID($msg_prefix);
        $context['tplan_id'] = $this->args[self::$testPlanIDParamName];

        if ( $status_ok ) {
          if ($this->_isParamPresent(self::$platformIDParamName)) {
            $status_ok = $this->checkPlatformIdentity($this->args[self::$testPlanIDParamName],
                                                      null,
                                                      $msg_prefix);
            $context['platform_id'] = $this->args[self::$platformIDParamName];
          }
        }
      }
    }

    if( $status_ok )
    {
      $dummy = $this->reqMgr->getAllByContext($context);
      if ( ! is_null($dummy) )
        $req = array_values($dummy);
      else
        $status_ok = false;
    }

    return $status_ok ? $req : $this->errors;
  }


/**
   * Get requirement coverage
   *
   * Retrieve the test cases associated to a requirement
   *
   * @param struct $args
   * @param string $args["devKey"]: used to check if operation can be done.
   *                                if devKey is not valid => abort.
   *
   * @param string $args["testprojectid"]
   * @param string $args["requirementdocid"]
   *
   * @return mixed error if someting's wrong, else array of test cases
   *
   * @access public
   */
  public function getReqCoverage($args)
  {
    $msg_prefix="(" .__FUNCTION__ . ") - ";
    $this->_setArgs($args);

    $resultInfo = array();
    $checkFunctions = array('authenticate', 'checkTestProjectID');
    $status_ok = $this->_runChecks($checkFunctions, $msg_prefix) &&
      $this->userHasRight('mgt_view_req', self::CHECK_PUBLIC_PRIVATE_ATTR);

    if( $status_ok )
    {
      // check req id exists in the project
      $reqDocID = $this->args[self::$requirementDocIDParamName];
      $req = $this->reqMgr->getByDocID($reqDocID,
                                       $this->args[self::$testProjectIDParamName],
                                       null, array('access_key' => 'req_doc_id', 'output' => 'minimun'));
      if ( ! is_null($req) )
      {
        $resultInfo = $this->reqMgr->get_coverage($req[$reqDocID]['id']);
      }
      else
      {
        $msg = $msg_prefix . sprintf(NO_REQ_IN_THIS_PROJECT_STR, $reqDocID,
                                     $this->args[self::$testProjectIDParamName]);
        $this->errors[] = new IXR_Error(NO_REQ_IN_THIS_PROJECT, $msg);
        $status_ok = false;
      }
    }
    return $status_ok ? $resultInfo : $this->errors;
  }


   /**
    * 
    * @param struct $args
    * @param string $args["devKey"]
    * @param string $args["testcaseexternalid"] format PREFIX-NUMBER
    * @param int    $args["testsuiteid"] 
    * 
    */
  public function setTestCaseTestSuite($args)
  {
    // Check test case identity
    // Check if user (devkey) has grants to do operation
    //
    $ret[] = array("operation" => __FUNCTION__, "status" => true, 
                   "message" => GENERAL_SUCCESS_STR);

    $operation = $ret['operation'];
    $msgPrefix = "({$operation}) - ";
    $debug_info = null;

    $this->_setArgs($args);  
    $checkFunctions = 
      array('authenticate','checkTestCaseIdentity','checkTestSuiteID');

    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix);
    if( $status_ok )
    {
      // Test Case & Test Suite belongs to same Test Project?
      $tcaseTProj = $this->args[self::$testProjectIDParamName] = 
        intval($this->tcaseMgr->getTestProjectFromTestCase(
                            $this->args[self::$testCaseIDParamName],null));

      $tsuiteMgr = new testsuite($this->dbObj);
      $tsuite_id = $this->args[self::$testSuiteIDParamName];
      $tsuiteTProj = 
        intval($tsuiteMgr->getTestProjectFromTestSuite($tsuite_id,null));
   
      $status_ok = ($tcaseTProj == $tsuiteTProj);
      if(!$status_ok)
      {
        $msg = $msgPrefix . TSUITE_NOT_ON_TCASE_TPROJ_STR;
        $this->errors[] = new IXR_Error(TSUITE_NOT_ON_TCASE_TPROJ, $msg);
      }  
    } 

    if( $status_ok )
    {
      $ctx[self::$testProjectIDParamName] = $tcaseTProj;
      $ck = self::CHECK_PUBLIC_PRIVATE_ATTR;
      $r2c = array('mgt_modify_tc');
      foreach($r2c as $right)
      {
        $status_ok = $this->userHasRight($right,$ck,$ctx);
        if(!$status_ok)
        {
          break;
        }  
      } 
    } 

    if( $status_ok )
    {

      $sql = "/* " . __FUNCTION__ . " */" . 
             " UPDATE " . $this->tables['nodes_hierarchy'] . 
             " SET parent_id=" . $tsuite_id .
             " WHERE id=" . $this->args['testcaseid'];
      $this->dbObj->exec_query($sql);
    }  

    return $status_ok ? $ret : $this->errors;    
  }

  /**
   * Gets a set of EXECUTIONS for a particular testcase on a test plan.
   * If there are no filter criteria regarding platform and build,
   * result will be get WITHOUT checking for a particular platform and build.
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["tplanid"]
   * @param int $args["testcaseid"]: Pseudo optional.
   *                 if is not present then testcaseexternalid MUST BE present
   *
   * @param int $args["testcaseexternalid"]: Pseudo optional.
   *                 if is not present then testcaseid MUST BE present
   *
   * @param string $args["platformid"]: optional. 
   *                    ONLY if not present, then $args["platformname"] 
   *                    will be analized (if exists)
   *
   * @param string $args["platformname"]: optional (see $args["platformid"])
   * @param int $args["buildid"]: optional
   *        ONLY if not present, $args["buildname"] will be analized (if exists)
   *
   * @param int $args["buildname"] - optional (see $args["buildid"])
   * @param int $args["options"] - optional 
   *                               options['getOrderDescending'] 
   *                               false(=ascending,default)
   * @return mixed $resultInfo
   *               if execution found
   *               array that contains a map with these keys:
   *               id (execution id),build_id,tester_id,execution_ts,
   *               status,testplan_id,tcversion_id,tcversion_number,
   *               execution_type,notes.
   *
   *               if test case has not been executed,
   *               array('id' => -1)
   * @access public
   */
  public function getExecutionSet($args)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
        
    $this->_setArgs($args);
    $resultInfo = array();
    $status_ok=true;

    $opt = new stdClass();
    $opt->getOrderDescending = 0;

    // Checks are done in order
    $checkFunctions = array('authenticate','checkTestPlanID',
                            'checkTestCaseIdentity');

    $status_ok = $this->_runChecks($checkFunctions,$msg_prefix) && 
                 $this->_checkTCIDAndTPIDValid(null,$msg_prefix) && 
                 $this->userHasRight("mgt_view_tc",
                     self::CHECK_PUBLIC_PRIVATE_ATTR);       

    $tplan_id = $this->args[self::$testPlanIDParamName];
    $tcase_id = $this->args[self::$testCaseIDParamName];
    $execContext = array('tplan_id' => $tplan_id,
                         'platform_id' => null,'build_id' => null);

    if( $status_ok )
    {
      if( $this->_isParamPresent(self::$optionsParamName,$msg_prefix) )
      {
        $dummy = $this->args[self::$optionsParamName];
        if( is_array($dummy) )
        {
          foreach($dummy as $key => $value)
          {
            $opt->$key = ($value > 0) ? 1 : 0;
          }
        }
      }

      // Now we can check for Optional parameters
      if($this->_isBuildIDPresent() || $this->_isBuildNamePresent())
      {
        if( ($status_ok =  $this->checkBuildID($msg_prefix)) )
        {
          $execContext['build_id'] = $this->args[self::$buildIDParamName];  
        }  
      }  

      if( $status_ok )
      {
        if( $this->_isParamPresent(self::$platformIDParamName,$msg_prefix) ||
            $this->_isParamPresent(self::$platformNameParamName,$msg_prefix) )
        {
          $status_ok = $this->checkPlatformIdentity($tplan_id);
          if( $status_ok)
          {
            $execContext['platform_id'] = 
              $this->args[self::$platformIDParamName];  
          }  
        }  
      }  
    }  

    if( $status_ok )
    {
      $sql = " SELECT * FROM  {$this->tables['executions']} WHERE id " .
             " IN (SELECT id AS exec_id FROM {$this->tables['executions']} " .
             "     WHERE testplan_id = {$tplan_id} " .
             "     AND tcversion_id " .
             "     IN ( SELECT id FROM {$this->tables['nodes_hierarchy']} " .
             "          WHERE parent_id = {$tcase_id} )";
            
      if(!is_null($execContext['build_id']))
      {
        $sql .= " AND build_id = " . intval($execContext['build_id']);
      }  
            
      if(!is_null($execContext['platform_id']))
      {
        $sql .= " AND platform_id = " . intval($execContext['platform_id']);
      }  

      // closing bracket for 1st level SELECT
      $sql .= ")"; 

      $sql .= " ORDER BY id ";
      $sql .= ($opt->getOrderDescending) ? " DESC" : " ASC";

      $rs = $this->dbObj->fetchRowsIntoMap($sql,'id');
      if( is_null($rs) )
      {
        // has not been executed
        // execution id = -1 => test case has not been runned.
        $resultInfo[]=array('id' => -1);
      }  
      else
      {
        $resultInfo = $rs;
      }  
    }
      
    return $status_ok ? $resultInfo : $this->errors;
  }


 /**
   * Close build
   *
   * @param struct $args
   * @param string $args["devKey"]
   * @param int $args["buildid"]
   *   
   * @return mixed $resultInfo
   *         
   * @access public
   */    
  public function closeBuild($args)
  {
    $operation = __FUNCTION__;
    $messagePrefix="({$operation}) - ";
  
    $resultInfo = array();

    $resultInfo[0]["id"] = 0;
    $resultInfo[0]["status"] = true;
    $resultInfo[0]["operation"] = $operation;
    $resultInfo[0]["message"] = GENERAL_SUCCESS_STR;

    $this->_setArgs($args);

    $checkFunctions = array('authenticate');       
    $status_ok = $this->_runChecks($checkFunctions,$messagePrefix);       

    if( $status_ok )
    {
       $status_ok = $this->_isParamPresent(self::$buildIDParamName,$messagePrefix,self::SET_ERROR);     
    }

    if( $status_ok )
    {
       $buildID = $this->args[self::$buildIDParamName];
       if( !($status_ok = is_int($buildID)) )
       {
         $msg = sprintf(BUILDID_NOT_INTEGER_STR,$buildID);
         $this->errors[] = new IXR_Error(BUILDID_NOT_INTEGER, $msg);
       } 
    }

    if( $status_ok )
    {
       // Get Test Plan ID from Build ID in order to check rights
       $bm = new build_mgr($this->dbObj);

       $buildID = intval($this->args[self::$buildIDParamName]); 
       $opx = array('output' => 'fields', 'fields' => 'id,testplan_id');
       $buildInfo = $bm->get_by_id($buildID,$opx);
      
       if( $buildInfo == false || count($buildInfo) == 0)
       {
         $status_ok = false;
         $msg = sprintf(INVALID_BUILDID_STR,$buildID);
         $this->errors[] = new IXR_Error(INVALID_BUILDID,$msg);
       } 
    }  

    if( $status_ok )
    {
      $context = array();
      $context[self::$testPlanIDParamName] = $buildInfo['testplan_id'];

      $status_ok = 
        $this->userHasRight("testplan_create_build",
                            self::CHECK_PUBLIC_PRIVATE_ATTR,$context);
    }  

    if( $status_ok )
    {
      $bm->setClosed($buildID);
      $resultInfo[0]["id"] = $buildID;
    }


    return $status_ok ? $resultInfo : $this->errors;
  }
 


  /**
   *
   */
  function initMethodYellowPages()
  {
    $this->methods = array( 'tl.reportTCResult' => 'this:reportTCResult',
                            'tl.setTestCaseExecutionResult' => 'this:reportTCResult',
                            'tl.createBuild' => 'this:createBuild',
                            'tl.closeBuild' => 'this:closeBuild',
                            'tl.createPlatform' => 'this:createPlatform',
                            'tl.createTestCase' => 'this:createTestCase',
                            'tl.createTestCaseSteps' => 'this:createTestCaseSteps',
                            'tl.createTestPlan' => 'this:createTestPlan',
                            'tl.createTestProject' => 'this:createTestProject',
                            'tl.createTestSuite' => 'this:createTestSuite',
                            'tl.deleteTestCaseSteps' => 'this:deleteTestCaseSteps',
                            'tl.deleteTestPlan' => 'this:deleteTestPlan',
                            'tl.deleteTestProject' => 'this:deleteTestProject',
                            'tl.uploadExecutionAttachment' => 'this:uploadExecutionAttachment',
                            'tl.uploadRequirementSpecificationAttachment' => 'this:uploadRequirementSpecificationAttachment',
                            'tl.uploadRequirementAttachment' => 'this:uploadRequirementAttachment',
                            'tl.uploadTestProjectAttachment' => 'this:uploadTestProjectAttachment',
                            'tl.uploadTestSuiteAttachment' => 'this:uploadTestSuiteAttachment',
                            'tl.uploadTestCaseAttachment' => 'this:uploadTestCaseAttachment',
                            'tl.uploadAttachment' => 'this:uploadAttachment',
                            'tl.assignRequirements' => 'this:assignRequirements',     
                            'tl.addTestCaseToTestPlan' => 'this:addTestCaseToTestPlan',
                            'tl.addPlatformToTestPlan' => 'this:addPlatformToTestPlan',
                            'tl.removePlatformFromTestPlan' => 'this:removePlatformFromTestPlan',
                            'tl.getExecCountersByBuild' => 'this:getExecCountersByBuild',
                            'tl.getIssueTrackerSystem' => 'this:getIssueTrackerSystem',
                            'tl.getProjects' => 'this:getProjects',
                            'tl.getProjectKeywords' => 'this:getProjectKeywords',
                            'tl.getProjectPlatforms' => 'this:getProjectPlatforms',
                            'tl.getProjectTestPlans' => 'this:getProjectTestPlans',
                            'tl.getTestCaseAssignedTester' => 'this:getTestCaseAssignedTester',
                            'tl.getTestCaseBugs' => 'this:getTestCaseBugs',
                            'tl.getTestCaseKeywords' => 'this:getTestCaseKeywords',
                            'tl.getTestProjectByName' => 'this:getTestProjectByName',
                            'tl.getTestPlanByName' => 'this:getTestPlanByName',
                            'tl.getTestPlanPlatforms' => 'this:getTestPlanPlatforms',
                            'tl.getTotalsForTestPlan' => 'this:getTotalsForTestPlan',
                            'tl.getBuildsForTestPlan' => 'this:getBuildsForTestPlan',
                            'tl.getLatestBuildForTestPlan' => 'this:getLatestBuildForTestPlan',  
                            'tl.getLastExecutionResult' => 'this:getLastExecutionResult',
                            'tl.getTestSuitesForTestPlan' => 'this:getTestSuitesForTestPlan',
                            'tl.getTestSuitesForTestSuite' => 'this:getTestSuitesForTestSuite',
                            'tl.getTestCasesForTestSuite'  => 'this:getTestCasesForTestSuite',
                            'tl.getTestCasesForTestPlan' => 'this:getTestCasesForTestPlan',
                            'tl.getTestCaseIDByName' => 'this:getTestCaseIDByName',
                            'tl.getTestCaseCustomFieldDesignValue' => 'this:getTestCaseCustomFieldDesignValue',
                            'tl.getTestCaseCustomFieldExecutionValue' => 'this:getTestCaseCustomFieldExecutionValue',
                            'tl.getTestCaseCustomFieldTestPlanDesignValue' => 'this:getTestCaseCustomFieldTestPlanDesignValue',
                            'tl.getTestSuiteCustomFieldDesignValue' => 'this:getTestSuiteCustomFieldDesignValue',
                            'tl.getTestPlanCustomFieldDesignValue' => 'this:getTestPlanCustomFieldDesignValue',
                            'tl.getReqSpecCustomFieldDesignValue' => 'this:getReqSpecCustomFieldDesignValue',
                            'tl.getRequirementCustomFieldDesignValue' => 'this:getRequirementCustomFieldDesignValue',
                            'tl.getFirstLevelTestSuitesForTestProject' => 'this:getFirstLevelTestSuitesForTestProject',     
                            'tl.getTestCaseAttachments' => 'this:getTestCaseAttachments',
                            'tl.getTestSuiteAttachments' => 'this:getTestSuiteAttachments',
                            'tl.getTestCase' => 'this:getTestCase',
                            'tl.getFullPath' => 'this:getFullPath',
                            'tl.getTestSuiteByID' => 'this:getTestSuiteByID',
                            'tl.getUserByLogin' => 'this:getUserByLogin',
                            'tl.getUserByID' => 'this:getUserByID',
                            'tl.deleteExecution' => 'this:deleteExecution',
                            'tl.doesUserExist' => 'this:doesUserExist',
                            'tl.updateTestCaseCustomFieldDesignValue' => 'this:updateTestCaseCustomFieldDesignValue',
                            'tl.updateTestCase' => 'this:updateTestCase',
                            'tl.setTestCaseExecutionType' => 'this:setTestCaseExecutionType',
                            'tl.assignTestCaseExecutionTask' => 'this:assignTestCaseExecutionTask',
                            'tl.unassignTestCaseExecutionTask' => 'this:unassignTestCaseExecutionTask',
                            'tl.addTestCaseKeywords' => 'this:addTestCaseKeywords',
                            'tl.removeTestCaseKeywords' => 'this:removeTestCaseKeywords',
                            'tl.updateTestSuiteCustomFieldDesignValue' => 'this:updateTestSuiteCustomFieldDesignValue',
                            'tl.updateBuildCustomFieldsValues' => 'this:updateBuildCustomFieldsValues',
                            'tl.getTestSuite' => 'this:getTestSuite',
                            'tl.updateTestSuite' => 'this:updateTestSuite',
                            'tl.getRequirements' => 'this:getRequirements',
                            'tl.getReqCoverage' => 'this:getReqCoverage',
                            'tl.setTestCaseTestSuite' => 'this:setTestCaseTestSuite',
                            'tl.getExecutionSet' => 'this:getExecutionSet',
                            'tl.checkDevKey' => 'this:checkDevKey',
                            'tl.about' => 'this:about',
                            'tl.testLinkVersion' => 'this:testLinkVersion',
                            'tl.setTestMode' => 'this:setTestMode',
                            'tl.ping' => 'this:sayHello', 
                            'tl.sayHello' => 'this:sayHello',
                            'tl.repeat' => 'this:repeat'
                        );
  }
  
} // class end
