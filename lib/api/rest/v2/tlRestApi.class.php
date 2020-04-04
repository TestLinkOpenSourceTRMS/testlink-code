<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * @filesource 	tlRestApi.class.php
 *
 * @author 		Francisco Mancardi <francisco.mancardi@gmail.com>
 * @package 	TestLink
 * @since     1.9.7             
 * 
 * Implemented using Slim framework Version 2.2.0
 * 
 * 
 * References
 * http://ericbrandel.com/2013/01/14/quickly-build-restful-apis-in-php-with-slim-part-2/
 * https://developer.atlassian.com/display/JIRADEV/JIRA+REST+API+Example+-+Add+Comment
 * http://confluence.jetbrains.com/display/YTD4/Create+New+Work+Item
 * http://www.redmine.org/projects/redmine/wiki/Rest_api
 * http://coenraets.org/blog/2011/12/restful-services-with-jquery-php-and-the-slim-framework/
 * https://github.com/educoder/pest/blob/master/examples/intouch_example.php
 * http://stackoverflow.com/questions/9772933/rest-api-request-body-as-json-or-plain-post-data
 *
 * http://phptrycatch.blogspot.it/
 * http://nitschinger.at/A-primer-on-PHP-exceptions
 *
 *
 *
 */

require_once('../../../../config.inc.php');
require_once('common.php');
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

/**
 * @author    Francisco Mancardi <francisco.mancardi@gmail.com>
 * @package   TestLink 
 */
class tlRestApi
{
  public static $version = "2.1";
    
    
  /**
   * The DB object used throughout the class
   * 
   * @access protected
   */
  protected $db = null;
  protected $tables = null;

  protected $tcaseMgr =  null;
  protected $tprojectMgr = null;
  protected $tsuiteMgr = null;
  protected $tplanMgr = null;
  protected $tplanMetricsMgr = null;
  protected $reqSpecMgr = null;
  protected $reqMgr = null;
  protected $platformMgr = null;
  protected $buildMgr = null;
  protected $cfieldMgr = null; 


  /** userID associated with the apiKey provided */
  protected $userID = null;
  
  /** UserObject associated with the userID */
  protected $user = null;

  /** array where all the args are stored for requests */
  protected $args = null;  

  /** array where error codes and messages are stored */
  protected $errors = array();

  /** The api key being used to make a request */
  protected $apiKey = null;
  
  /** boolean to allow a method to invoke another method and avoid double auth */
  protected $authenticated = false;

  /** The version of a test case that is being used */
  /** This value is setted in following method:     */
  protected $tcVersionID = null;
  protected $versionNumber = null;
  protected $debugMsg;
  
  protected $cfg;

  protected $apiLogPathName;

  protected $l10n;

  
  
  /**
   */
  public function __construct() {

    // We are following Slim naming convention
    $this->app = new \Slim\Slim();
    $this->app->contentType('application/json');

    $tl = array('API_MISSING_REQUIRED_PROP' => null,
                'API_TESTPLAN_ID_DOES_NOT_EXIST' => null,
                'API_TESTPLAN_APIKEY_DOES_NOT_EXIST' => null,
                'API_BUILDNAME_ALREADY_EXISTS' => null,
                'API_INVALID_BUILDID' => null);

    $this->l10n = init_labels($tl);

    // GET Routes
    // test route with anonymous function 
    $this->app->get('/who', function () { echo __CLASS__ . ' : You have called the Get Route /who';});

    // using middleware for authentication
    // https://docs.slimframework.com/routing/middleware/
    //
    $this->app->get('/whoAmI', array($this,'authenticate'), array($this,'whoAmI'));

    $this->app->get('/superman', array($this,'authenticate'), array($this,'superman'));

    $this->app->get('/testprojects', array($this,'authenticate'), array($this,'getProjects'));

    $this->app->get('/testprojects/:id', array($this,'authenticate'), array($this,'getProjects'));
    $this->app->get('/testprojects/:id/testcases', array($this,'authenticate'), array($this,'getProjectTestCases'));
    $this->app->get('/testprojects/:id/testplans', array($this,'authenticate'), array($this,'getProjectTestPlans'));

    $this->app->get('/testplans/:id/builds', array($this,'authenticate'), array($this,'getPlanBuilds'));

    // POST Routes
    $this->app->post('/builds', array($this,'authenticate'), array($this,'createBuild'));


    $this->app->post('/testprojects', array($this,'authenticate'), array($this,'createTestProject'));

    $this->app->post('/executions', array($this,'authenticate'), array($this,'createTestCaseExecution'));
    
    $this->app->post('/testplans', array($this,'authenticate'), array($this,'createTestPlan'));

    $this->app->post('/testplans/:id', array($this,'authenticate'), array($this,'updateTestPlan'));


    $this->app->post('/testplans/:id/platforms', array($this,'authenticate'), array($this,'addPlatformsToTestPlan'));

    $this->app->post('/testsuites', array($this,'authenticate'), array($this,'createTestSuite'));

    $this->app->post('/testcases', array($this,'authenticate'), array($this,'createTestCase'));

    $this->app->post('/keywords', array($this,'authenticate'), array($this,'createKeyword'));


    // update routes
    $this->app->post('/builds/:id', array($this,'authenticate'), array($this,'updateBuild'));


    // $this->app->get('/testplans/:id', array($this,'getTestPlan'));
    $this->apiLogPathName = '/var/testlink/rest-api.log';

    $this->db = new database(DB_TYPE);
    $this->db->db->SetFetchMode(ADODB_FETCH_ASSOC);
    doDBConnect($this->db,database::ONERROREXIT);


    $this->tcaseMgr = new testcase($this->db);
    $this->tprojectMgr = new testproject($this->db);
    $this->tsuiteMgr = new testsuite($this->db);

    $this->tplanMgr = new testplan($this->db);
    $this->tplanMetricsMgr = new tlTestPlanMetrics($this->db);
    $this->reqSpecMgr = new requirement_spec_mgr($this->db);
    $this->reqMgr = new requirement_mgr($this->db);
    $this->cfieldMgr = $this->tprojectMgr->cfield_mgr;
    $this->buildMgr = new build($this->db);

    $this->tables = $this->tcaseMgr->getDBTables();
    

    $this->cfg = array();
    $conf = config_get('results');
    foreach($conf['status_label_for_exec_ui'] as $key => $label ) {
      $this->cfg['exec']['statusCode'][$key] = $conf['status_code'][$key];  
    }
    
    $this->cfg['exec']['codeStatus'] = array_flip($this->cfg['exec']['statusCode']);

    $this->cfg['tcase']['executionType'] = 
      config_get('execution_type');
    $this->cfg['tcase']['status'] = config_get('testCaseStatus');
    $this->cfg['tcase']['executionType'] = 
      config_get('execution_type');
    
    $x = config_get('importance');
    $this->cfg['tcase']['importance'] = []; 
    foreach($x['code_label'] as $code => $label) {
      $this->cfg['tcase']['importance'][$label] = $code; 
    } 


    // DEFAULTS
    $this->cfg['tcase']['defaults']['executionType'] = 
      $this->cfg['tcase']['executionType']['manual'];

    $this->cfg['tcase']['defaults']['importance'] = config_get('testcase_importance_default');




    $this->debugMsg = ' Class:' . __CLASS__ . ' - Method: ';
  }  


  /**
   *
   */
  function authenticate(\Slim\Route $route) {
    $apiKey = null;
    if(is_null($apiKey)) {  
      $request = $this->app->request();
      $hh = $request->headers();

      if( isset($hh['APIKEY']) ) {
        $apiKey = $hh['APIKEY'];
      } else {
        $apiKey = $hh['PHP_AUTH_USER'];
      }
    } 
    $sql = "SELECT id FROM {$this->tables['users']} " .
           "WHERE script_key='" . $this->db->prepare_string($apiKey) . "'";

    $this->userID = $this->db->fetchFirstRowSingleColumn($sql, "id");
    if( ($ok=!is_null($this->userID)) ) {
      $this->user = tlUser::getByID($this->db,$this->userID);  
    } else {
      $this->app->status(400);
      echo json_encode(array('status' => 'ko', 'message' => 'authentication error'));  
      $this->app->stop();
    }  

    return $ok;
  }



  /**
   *
   */
  public function whoAmI() {    
    echo json_encode(array('name' => __CLASS__ . ' : You have called Get Route /whoAmI'));
  }
  
  /**
   *
   */
  public function superman() {    
    echo json_encode(
      array('name' => __CLASS__ . ' : You have called the Get Route /superman'));
  }


  /**
   *
   * @param mixed idCard if provided identifies test project
   *                     if intval() > 0 => is considered DBID
   *                     else => is used as PROJECT NAME
   */
  public function getProjects($idCard=null, $opt=null) {
    $options = array_merge(array('output' => 'rest'), (array)$opt);
    $op = array('status' => 'ok', 'message' => 'ok', 'item' => null);
    if(is_null($idCard)) {
      $opOptions = array('output' => 'array_of_map', 'order_by' => " ORDER BY name ", 'add_issuetracker' => true,
                          'add_reqmgrsystem' => true);
      $op['item'] = $this->tprojectMgr->get_accessible_for_user($this->userID,$opOptions);
    } else {
      $opOptions = array('output' => 'map',
                         'field_set' => 'prefix', 
                         'format' => 'simple');
      $zx = $this->tprojectMgr->get_accessible_for_user($this->userID,$opOptions);

      $targetID = null;
      if( ($safeID = intval($idCard)) > 0) {
        if( isset($zx[$safeID]) ) {
          $targetID = $safeID;
        } 
      } 
      else {
        // Will consider id = name or prefix
        foreach( $zx as $itemID => $value ) {
          if( strcmp($value['name'],$idCard) == 0 || 
              strcmp($value['prefix'],$idCard) == 0 ) {
            $targetID = $itemID;
            break;   
          }  
        }
      }

      if( null != $targetID ) {
        $op['item'] = $this->tprojectMgr->get_by_id($targetID);  
      }  
    } 

    // Developer (silly?) information
    // json_encode() transforms maps in objects.
    switch($options['output']) {
      case 'internal':
        return $op['item'];
      break;

      case 'rest':
      default:
        echo json_encode($op);
      break;
    }
  }

  /**
   *
   * @param mixed idCard if provided identifies test project
   *                     if intval() > 0 => is considered DBID
   *                     else => is used as PROJECT NAME Or Prefix
   */
  public function getProjectTestPlans($idCard) {
    $op  = array('status' => 'ok', 'message' => 'ok', 'items' => null);
    $tproject = $this->getProjects($idCard, array('output' => 'internal'));
 
    if( !is_null($tproject) ) {
      $items = $this->tprojectMgr->get_all_testplans($tproject['id']);
      $op['items'] = (!is_null($items) && count($items) > 0) ? $items : null;
    } else {
      $op['message'] = "No Test Project identified by '" . $idCard . "'!";
      $op['status']  = 'error';
      $this->app->status(500);
    }

    echo json_encode($op);
  }

  /**
   * Will return LATEST VERSION of each test case.
   * Does return test step info ?
   *
   * @param mixed idCard if provided identifies test project
   *                     if intval() > 0 => is considered DBID
   *                     else => is used as PROJECT NAME
   */ 
  public function getProjectTestCases($idCard) {
    $op  = array('status' => 'ok', 'message' => 'ok', 'items' => null);
    $tproject = $this->getProjects($idCard, array('output' => 'internal'));

    if( !is_null($tproject) ) {
      $tcaseIDSet = array();
      $this->tprojectMgr->get_all_testcases_id($tproject['id'],$tcaseIDSet);

      if( !is_null($tcaseIDSet) && count($tcaseIDSet) > 0 ) {
        $op['items'] = array();
        foreach( $tcaseIDSet as $key => $tcaseID ) {
          $item = $this->tcaseMgr->get_last_version_info($tcaseID);
          $item['keywords'] = 
            $this->tcaseMgr->get_keywords_map($tcaseID,$item['tcversion_id']);
          $item['customfields'] = 
            $this->tcaseMgr->get_linked_cfields_at_design($tcaseID,$item['tcversion_id'],null,null,$tproject['id']);
          $op['items'][] = $item;
        }
      }
    } else {
      $op['message'] = "No Test Project identified by '" . $idCard . "'!";
      $op['status']  = 'error';
    }

    echo json_encode($op);
  }

  /**
   * 
   *        $item->name               
   *        $item->prefix
   *        $item->notes
   *        $item->active
   *        $item->public
   *        $item->options
   *        $item->options->requirementsEnabled
   *        $item->options->testPriorityEnabled
   *        $item->options->automationEnabled
   *        $item->options->inventoryEnabled
   */
  public function createTestProject() {
    $op = array('status' => 'ko', 'message' => 'ko', 'id' => -1);  

    try {
      // Check user grants for requested operation
      // This is a global right
      $rightToCheck="mgt_modify_product";
      if( $this->userHasRight($rightToCheck) ) {
        $request = $this->app->request();
        $item = json_decode($request->getBody());
        $op['id'] = $this->tprojectMgr->create($item,array('doChecks' => true));
        $op = array('status' => 'ok', 'message' => 'ok');
      } else {
        $this->app->status(403);
        $msg = lang_get('API_INSUFFICIENT_RIGHTS');
        $op['message'] = sprintf($msg,$rightToCheck,0,0);
      } 
    } 
    catch (Exception $e) {
      $this->app->status(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                       $this->msgFromException($e);  
    }
    echo json_encode($op);
  }



  /**
   *
   * Request Body
   *
   * $ex->testPlanID
   * $ex->buildID
   * $ex->platformID  -> optional
   * $ex->testCaseExternalID
   * $ex->notes
   * $ex->statusCode
   *
   *
   * Checks to be done
   * 
   * A. User right & Test plan existence
   * user has right to execute on target Test plan?
   * this means also that: Test plan ID exists ?
   *   
   * B. Build
   * does Build ID exist on target Test plan ?
   * is Build enable to execution ?
   *
   * C. Platform
   * do we need a platform ID in order to execute ?
   * is a platform present on provided data ?
   * does this platform belong to target Test plan ?
   *
   * D. Test case identity
   * is target Test case part of Test plan ?
   *
   *
   * Z. Other mandatory information
   * We are not going to check for other mandatory info
   * like: mandatory custom fields. (if we will be able in future to manage it)
   *
   * 
   */
  public function createTestCaseExecution() {
    $op = array('status' => ' ko', 'message' => 'ko', 'id' => -1);  
    try {
      $request = $this->app->request();
      $ex = json_decode($request->getBody());
      $util = $this->checkExecutionEnvironment($ex);

      // Complete missing propertie
      if( property_exists($ex, 'platformID') == FALSE ) {
        $ex->platformID = 0;
      }

      if( property_exists($ex, 'executionType') == FALSE ) {
        $ex->executionType = 
          $this->cfg['tcase']['executionType']['auto'];
      }

      // If we are here this means we can write execution status!!!
      $ex->testerID = $this->userID;
      foreach($util as $prop => $value) {
        $ex->$prop = $value;
      }  
      $op = array('status' => 'ok', 'message' => 'ok');
      $op['id'] = $this->tplanMgr->writeExecution($ex);
    } catch (Exception $e) {
      $this->app->status(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                      $e->getMessage();  
    }
    echo json_encode($op);
  }



  //
  // Support methods
  //
  private function checkExecutionEnvironment($ex) {
    // throw new Exception($message, $code, $previous);

    // no platform
    $platform = 0;

    // Test plan ID exists and is ACTIVE    
    $msg = 'invalid Test plan ID';
    $getOpt = array('output' => 'testPlanFields','active' => 1,
                    'testPlanFields' => 'id,testproject_id,is_public');
    $status_ok = !is_null($testPlan=$this->tplanMgr->get_by_id($ex->testPlanID,$getOpt));
    
    if($status_ok) {
      // user has right to execute on Test plan ID
      // hasRight(&$db,$roleQuestion,$tprojectID = null,$tplanID = null,$getAccess=false)
      $msg = 'user has no right to execute';
      $status_ok = $this->user->hasRight($this->db,'testplan_execute',
                                         $testPlan['testproject_id'],$ex->testPlanID,true); 
    }  

    if($status_ok) {
      // Check if couple (buildID,testPlanID) is valid
      $msg = '(buildID,testPlanID) couple is not valid';
      $getOpt = array('fields' => 'id,active,is_open', 'buildID' => $ex->buildID, 'orderBy' => null);
      $status_ok = !is_null($build = $this->tplanMgr->get_builds($ex->testPlanID,null,null,$getOpt));

      if($status_ok) {
        // now check is execution can be done againts this build
        $msg = 'Build is not active and/or closed => execution can not be done';
        $status_ok = $build[$ex->buildID]['active'] && $build[$ex->buildID]['is_open'];
      }  
    }  

    if($status_ok && property_exists($ex, 'platformID')) {
      // Get Test plan platforms
      $platform = $ex->platformID;

      $getOpt = array('outputFormat' => 'mapAccessByID' , 'addIfNull' => false);
      $platformSet = $this->tplanMgr->getPlatforms($ex->testPlanID,$getOpt);

      if( !($hasPlatforms = !is_null($platformSet)) && $platform !=0) {
        $status_ok = false;
        $msg = 'You can not execute against a platform, because Test plan has no platforms';
      }  

      if($status_ok) {
        if($hasPlatforms) {  
          if($platform == 0) {
            $status_ok = false;
            $msg = 'Test plan has platforms, you need to provide one in order to execute';
          } else if (!isset($platformSet[$platform])) {
            $status_ok = false;
            $msg = '(platform,test plan) couple is not valid';
          }
        }
      }  
    } 

    if($status_ok) {
      // Test case check
      $msg = 'Test case does not exist';

      $tcaseID = $this->tcaseMgr->getInternalID($ex->testCaseExternalID);
      $status_ok = ($tcaseID > 0);
      if( $status_ok = ($tcaseID > 0) ) {
        $msg = 'Test case doesn not belong to right test project';
        $testCaseTestProject = $this->tcaseMgr->getTestProjectFromTestCase($tcaseID,0);
        $status_ok = ($testCaseTestProject == $testPlan['testproject_id']);
      }  

      if($status_ok) {
        // Does this test case is linked to test plan ?
        $msg = 'Test case is not linked to (test plan,platform) => can not be executed';
        $getFilters = array('testplan_id' => $ex->testPlanID, 
                            'platform_id' => $platform);

        $getOpt = array('output' => 'simple');
        $links = $this->tcaseMgr->get_linked_versions($tcaseID,$getFilters,$getOpt);
        $status_ok = !is_null($links);
      }  
    }  

    if($status_ok) {
      // status code is OK ?
      $msg = 'not run status is not a valid execution status (can not be written to DB)';
      $status_ok = ($ex->statusCode != $this->cfg['exec']['statusCode']['not_run']);

      if($status_ok) {
        $msg = 'Requested execution status is not configured on TestLink';
        $status_ok = isset($this->cfg['exec']['codeStatus'][$ex->statusCode]);
      }  
    }  

    if($status_ok) {
      $ret = new stdClass();
      $ret->testProjectID = $testPlan['testproject_id'];
      $ret->testCaseVersionID = key($links);
      $ret->testCaseVersionNumber = 
        $links[$ret->testCaseVersionID][$ex->testPlanID][$platform]['version'];
    }

    if(!$status_ok) {
      throw new Exception($msg);
    }  

    return $ret;
  }
 


  /**
   * 'name'
   * 'testProjectID'
   * 'notes'
   * 'active'
   * 'is_public'
   *
   */
  public function createTestPlan() {
    $op = array('status' => 'ko', 'message' => 'ko', 'id' => -1);  
    try {
      $request = $this->app->request();
      $item = json_decode($request->getBody());

      $op = array('status' => 'ok', 'message' => 'ok');
      $opeOpt = array('setSessionProject' => false,
                      'doChecks' => true);
      $op['id'] = $this->tplanMgr->createFromObject($item,$opeOpt);
      
    } catch (Exception $e) {
      $this->app->status(500);
      $op['message'] = __METHOD__ . ' >> ' . $e->getMessage();   
    }
    echo json_encode($op);
  }

  /**
   * 'name'
   * 'testProjectID'
   * 'notes'
   * 'active'
   * 'is_public'
   *
   */
  public function updateTestPlan($id) {
    $op = array('status' => 'ko', 'message' => 'ko', 'id' => -1);  
    try {
      $op = array('status' => 'ok', 'message' => 'ok');

      $request = $this->app->request();
      $item = json_decode($request->getBody());
      $item->id = $id;
      $op['id'] = $this->tplanMgr->updateFromObject($item);
    } catch (Exception $e) {
      $this->app->status(500);
      $op['message'] = __METHOD__ . ' >> ' . $e->getMessage();  
    }
    echo json_encode($op);
  }


  /**
   * 'name'
   * 'testProjectID'
   * 'parentID'
   * 'notes'
   * 'order'
   */
  public function createTestSuite() {
    $op = array('status' => 'ko', 'message' => 'ko', 'id' => -1);  
    try {
      $request = $this->app->request();
      $item = json_decode($request->getBody());
      $op = array('status' => 'ok', 'message' => 'ok');
      $op['id'] = $this->tsuiteMgr->createFromObject($item,array('doChecks' => true));
    } catch (Exception $e) {
      $this->app->status(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                      msgFromException($e);  
    }
    echo json_encode($op);
  }

  /**
   * "name"
   * "testSuite": {"id": xxx}
   * "testProject" : {"id": xxx} or {"prefix": yyy}
   * "authorLogin"
   * "authorID"
   * "summary"
   * "preconditions"
   * "importance" - see const.inc.php for domain
   * "executionType"  - see ... for domain
   * "order"
   *
   * "estimatedExecutionDuration"  // to be implemented
   */
  public function createTestCase() {
    $op = array('status' => 'ko', 'message' => 'ko', 'id' => -1);  
    try {
      $request = $this->app->request();
      $item = json_decode($request->getBody());
      if(is_null($item)) {
        throw new Exception("Fatal Error " . __METHOD__ . " json_decode(requesBody) is NULL", 1);
      }

      // create obj with standard properties
      try {
        $tcase = $this->buildTestCaseObj($item);
        $this->checkRelatives($tcase);
      } catch (Exception $e) {
        $this->app->status(500);
        $op['message'] = 'After buildTestCaseObj() >> ' .
                         $this->msgFromException($e);  
        echo json_encode($op);
        return;
      }
      
      $ou = $this->tcaseMgr->createFromObject($tcase);
      $op = array('status' => 'ok', 'message' => 'ok', 'id' => -1);
      if( ($op['id']=$ou['id']) <= 0) {
        $op['status'] = 'ko';
        $op['message'] = $ou['msg'];
        $this->app->status(409);
      }
    } catch (Exception $e) {
      $this->app->status(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                       $this->msgFromException($e);   
    }
    echo json_encode($op);
  }


  /**
   *
   * @param mixed testplan
   *
   *        step 1) testplan is a number ? 
   *                use it as test plan id
   * 
   *        step 2) testplan is a string ?
   *                use it as test plan apikey
   *        
   *        Is not possible to consider testplan as name
   *        becase name can be used in several test projects.
   *        One option can be request testprojectname/testplanname
   *
   * @param string name: build name
   * @param string [notes]
   * @param string [active]
   * @param string [open]
   * @param string [releasedate]: format YYYY-MM-DD;
   * @param int    [copytestersfrombuild]
   *
   *               step 1) is a number ?
   *                       will be considered a target build id.
   *                       check will be done to verify that is a valid
   *                       build id inside the test plan.
   *
   *               step 2) is a string ?
   *                       will be used as build name to search inside
   *                       the test plan.
   * 
   *               if check is OK, tester assignments will be copied.
   *
   */
  public function createBuild() {

    $op = array('status' => 'ko', 'message' => 'ko', 
                'details' => array(), 'id' => -1);  

    $rightToCheck = "testplan_create_build";

    // need to get input, before doing right checks,
    // because right can be tested against in this order
    // Test Plan Right
    // Test Project Right
    // Default Right
    $request = $this->app->request();
    $item = json_decode($request->getBody());
    if( null == $item ) {
      $this->byeHTTP500();
    }

    $statusOK = true;
    $build = new stdClass();
 
    $reqProps = array('testplan','name');
    foreach( $reqProps as $prop ) {
      if( !property_exists($item, $prop) ) {
        $op['details'][] = $this->l10n['API_MISSING_REQUIRED_PROP'] .
                           $prop;
        $statusOK = false;
      } 
    }

    if( $statusOK ) {
      $build->name = $item->name;

      if( is_numeric($item->testplan) ) {
        // Check if is a valid test plan
        // Get it's test project id
        $tplan_id = intval($item->testplan);
        $tplan = $this->tplanMgr->get_by_id( $tplan_id );

        if( null == $tplan ) {
          $statusOK = false;
          $op['details'][] = 
            sprintf($this->l10n['API_TESTPLAN_ID_DOES_NOT_EXIST'],$item->testplan);
          $this->app->status(404);
        }
      } else {
        $tplanAPIKey = trim($item->testplan);
        $tplan = $this->tplanMgr->getByAPIKey( $tplanAPIKey );
        if( null == $tplan ) {
          $statusOK = false;
          $op['details'][] = 
            sprintf($this->l10n['API_TESTPLAN_APIKEY_DOES_NOT_EXIST'],$item->testplan);
          $this->app->status(404);
        }
      }
    }

    if( $statusOK ) {
      // Ready to check user permissions
      $context = array('tplan_id' => $tplan['id'], 
                       'tproject_id' => $tplan['testproject_id']);

      if( !$this->userHasRight($rightToCheck,TRUE,$context) ) {
        $statusOK = false;
        $msg = lang_get('API_INSUFFICIENT_RIGHTS');
        $op['message'] = 
          sprintf($msg,$rightToCheck,$this->user->login,
                  $context['tproject_id'],$context['tplan_id']);
        $this->app->status(403);
      } 
    }  

    // Go ahead, try create build!!
    // Step 1 - Check if build name already exists
    if( $statusOK ) {
      $build->id = 
        $this->tplanMgr->get_build_id_by_name( $context['tplan_id'], $build->name );

      if( $build->id > 0 ) {
        $statusOK = false;
        $op['message'] = 
          sprintf($this->l10n['API_BUILDNAME_ALREADY_EXISTS'], 
                  $build->name, $build->id);
        $this->app->status(409);
      }

      $build->tplan_id = $context['tplan_id'];
    }    

    // Step 2 - Finally Create It!!
    if( $statusOK ) {
      // key 2 check with default value is parameter is missing
      $k2check = array('is_open' => 1,
                       'release_candidate' => null,
                       'notes' => null,
                       'commit_id' => null, 'tag' => null,
                       'branch' => null,
                       'is_active' => 1,'active' => 1, 
                       'releasedate' => null,'release_date' => null,
                       'copy_testers_from_build' => null,
                       'copytestersfrombuild' => null);

      $buildProp = array('tplan_id' => 'tplan_id',
                         'release_date' => 'release_date',
                         'releasedate' => 'release_date',
                         'active' => 'is_active',
                         'is_active' => 'is_active',
                         'notes' => 'notes',
                         'commit_id' => 'commit_id', 
                         'tag' => 'tag', 'branch' => 'branch', 
                         'release_candidate' =>'release_candidate',
                         'is_open' => 'is_open',
                         'copytestersfrombuild' => 
                              'copytestersfrombuild',                         
                         'copy_testers_from_build' => 
                              'copytestersfrombuild');

      $skipKey = array();
      foreach( $k2check as $key => $value ) {
        $translate = $buildProp[$key]; 
        if( !isset($skipKey[$translate]) ) {
          $build->$translate = $value;
          if( property_exists($item, $key) ) {
            $build->$translate = $item->$key;
            $skipKey[$translate] = true;
          }
        }
      }

      $itemID = $this->buildMgr->createFromObject($build);
      if( $itemID > 0 ) {
        $op = array('status' => 'ok', 'message' => 'ok', 
                    'details' => array(), 'id' => $itemID);  
      } 
    }    

    echo json_encode($op);
  }


  /**
   *
   *
   */ 
  private function getUserIDByAttr($user) {
    $debugMsg = $this->debugMsg . __FUNCTION__;
    $run = false;
    $udi = -1;

    $sql = "/* $debugMsg */ SELECT id FROM {$this->tables['users']} ";
    if(property_exists($user, 'login')) {
      $run = true;
      $sql .= " WHERE login='" . $this->db->prepare_string(trim($user->login)) . "'";
    }

    if($run==false && property_exists($user, 'id')) {
      $run = true;
      $sql .= " WHERE id=" . intval($user->id);
    } 

    if($run) {
      $rs = $this->db->get_recordset($sql);
    }  

    return ($run && !is_null($rs)) ? $rs[0]['id'] : $uid;
  }

  /**
   *
   *
   */ 
  private function buildTestCaseObj(&$obj) {
    if(is_null($obj)) {
      throw new Exception("Fatal Error - " . __METHOD__ . " arg is NULL");
    } 

    $tcase = new stdClass();
    $tcase->authorID = -1;
    $tcase->steps = null;
    $tcase->testProjectID = -1;

    $accessKey = array();
    $isOK = true;

    // Knowing author is critic, because rights are related to user.
    // Another important thing:
    // do we need to check that author when provided, has rights to do
    // requested action?
    // If we do not do this check, we will find in test cases created
    // by people that do not have rights.
    // May be is time to add a field that provide info about source of action
    // GUI, API
    // 
    if(property_exists($obj, 'author')) {
      if(property_exists($obj->author, 'login') || property_exists($obj->author, 'id')) {
        $tcase->authorID = $this->getUserIDByAttr($obj->author);
      } 
    }  

    // Last resort: get author from credentials use to make the call.
    // no error message returned.
    if($tcase->authorID <= 0) {
      $tcase->authorID = $this->userID;
    }  

    
    // Mandatory attributes
    $ma = array('name' => null,
                'testProject' => array('id','prefix'),
                'testSuite' => array('id'));

    foreach ($ma as $key => $dummy) {
      if( !($isOK = $isOK && property_exists($obj, $key)) ) {
        throw new Exception("Missing Attribute: {$key} ");
      }  
    }

    foreach ($ma as $key => $attr) {
      if( !is_null($attr) ) {
        $attrOK = false;
        foreach($attr as $ak) {
          $accessKey[$key][$ak] = property_exists($obj->$key,$ak);
          $attrOK = $attrOK || $accessKey[$key][$ak];
        }  

        if(!$attrOK) {
          $msg = "Attribute: {$key} mandatory key (";
          if(count($attr) > 1) {
            $msg .= "one of set: ";
          }  
          $msg .= implode('/',$attr) . ") is missing";
          throw new Exception($msg);            
        }  
      }  
    }

    $tcase->name = trim($obj->name);
    $tcase->testSuiteID = intval($obj->testSuite->id);

    $gOpt = array('output' => 'array_of_map', 
                  'field_set' => 'prefix',
                  'add_issuetracker' => false, 
                  'add_reqmgrsystem' => false);


    $msg = "Test project with ";        
    if($accessKey['testProject']['id']) {
      $safeID = intval($obj->testProject->id);
      $gFilters = array('id' => array('op' => '=', 'value' => $safeID));
      $msg .= "id={$safeID} ";
    }  

    if($accessKey['testProject']['prefix']) {
      $gFilters = array('prefix' => 
                        array('op' => '=', 'value' => trim($obj->testProject->prefix)) );
      $msg .= "prefix={$obj->testProject->prefix} ";
    }
    
    $info = $this->tprojectMgr->get_accessible_for_user($this->userID,$gOpt,$gFilters);

    if(is_null($info)) {
      $msg .= "does not exist or you have no rights to use it";
      throw new Exception($msg,999);            
    } 

    $tcase->testProjectID = intval($info[0]['id']);

    $sk2d = array('summary' => '',
                  'preconditions' => '',
                  'order' => 100, 
                  'estimatedExecutionTime' => 0);
    foreach($sk2d as $key => $value) {
      $tcase->$key = property_exists($obj, $key) 
                     ? $obj->$key : $value;
    } 

    // name is the access
    $tcfg = $this->cfg['tcase'];
    $ck2d = array('executionType' => 
                     $tcfg['executionType']['manual'], 
                  'importance' => 
                    $tcfg['defaults']['importance'], 
                  'status' => 
                    $tcfg['status']['draft']);

    foreach($ck2d as $prop => $defa) {
      $tcase->$prop = property_exists($obj, $prop) ? 
        $tcfg[$prop][$obj->$prop->name] : $defa;      
    }  


    if(property_exists($obj, 'steps')) {
      $tcase->steps = $obj->steps;
    }

    return $tcase;
  }


  /**
   *
   *
   */ 
  private function checkRelatives($ctx) 
  {
    $testProjectID = $ctx->testProjectID;
    $testSuiteID = $ctx->testSuiteID; 
    if($testProjectID <= 0) {
      throw new Exception("Test Project ID is invalid (<=0)");
    }  

    if($testSuiteID <= 0) {
      throw new Exception("Test Suite ID is invalid (<=0)");
    }  

    $pinfo = $this->tprojectMgr->get_by_id($testProjectID);
    if( is_null($pinfo) ) {
      throw new Exception("Test Project ID is invalid (does not exist)");
    }  

    $pinfo = $this->tsuiteMgr->get_by_id($testSuiteID);
    if( is_null($pinfo) ) {
      throw new Exception("Test Suite ID is invalid (does not exist)");
    }  

    if( $testProjectID != $this->tsuiteMgr->getTestProjectFromTestSuite($testSuiteID,$testSuiteID) ) {
      throw new Exception("Test Suite does not belong to Test Project ID");
    }  
  }


  /**
   * checks if a user has requested right on test project, test plan pair.
   * 
   * @param string $rightToCheck  one of the rights defined in rights table
   * @param boolean $checkPublicPrivateAttr (optional)
   * @param map $context (optional)
   *            keys tproject_id,tplan_id  (both are also optional)
   *
   * @return boolean
   * @access protected
   *
   *
   */
  protected function userHasRight($rightToCheck,$checkPublicPrivateAttr=false,
                                  $context=null)
  {
    $status_ok = true;

    // for global rights context is NULL
    if( is_null($context) ) {
      $tproject_id = 0;
      $tplan_id = null;      
    } else {
      $tproject_id = intval(isset($context['tproject_id']) ? 
                    $context['tproject_id'] : 0);

      $tplan_id = null;
      if(isset($context['tplan_id'])) {
        $tplan_id = intval($context['tplan_id']);
      } 

      if( $tproject_id <= 0 && !is_null($tplan_id) ) {
        // get test project from test plan
        $dummy = $this->tplanMgr->get_by_id($tplanid,array('output' => 'minimun'));  
        $tproject_id = intval($dummy['tproject_id']);
      }
    }

    // echo $rightToCheck;
    if(!$this->user->hasRight($this->db,$rightToCheck,
                              $tproject_id,$tplan_id,$checkPublicPrivateAttr)) {
      $status_ok = false;
    }
    return $status_ok;
  }

  /**
   * "keyword"
   * "notes"
   * "testProject": {"prefix":"APR"}
   */
  public function createKeyword() {
    $op = array('status' => 'ko', 'message' => 'ko', 'id' => -1);  
    try {
      $request = $this->app->request();
      $item = json_decode($request->getBody());
      if(is_null($item)) {
        throw new Exception("Fatal Error " . __METHOD__ . " json_decode(requestBody) is NULL", 1);
      }

      // create obj with standard properties
      $pfx = $item->testProject->prefix;
      $pid = $this->tprojectMgr->get_by_prefix((string)$pfx);
      if( null == $pid ) {
          $op['status'] = 'ko';
          $op['message'] = "Can't get test project ID";
      } else {
        $pid = $pid['id'];
        $ou = $this->tprojectMgr->addKeyword($pid,$item->keyword,$item->notes);       
        $op = array('status' => 'ok', 'message' => 'ok', 'id' => -1);
        if( ($op['id'] = $ou['id']) <= 0) {
          $op['status'] = 'ko';
          $op['message'] = $ou['msg'];
        }        
      }
    } catch (Exception $e) {
      $this->app->status(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                       $this->msgFromException($e);  
    }
    echo json_encode($op);
  }


  /**
   *
   * @param mixed idCard identifies test plan via apikey
   *              
   */
  public function getPlanBuilds($idCard) {
    $op  = array('status' => 'ok', 'message' => 'ok', 'items' => null);
    $tplan = $this->tplanMgr->getByAPIKey($idCard);
 
    if( !is_null($tplan) ) {
      $items = $this->tplanMgr->get_builds($tplan['id']);
      $op['items'] = (!is_null($items) && count($items) > 0) ? $items : null;
    } else {
      $op['message'] = "No Test Plan identified by '" . $idCard . "'!";
      $op['status']  = 'error';
    }

    echo json_encode($op);
  }


  /**
   *
   * @param string id: build id
   * @param string [notes]
   * @param string [active]
   * @param string [open]
   * @param string [releasedate]: format YYYY-MM-DD;
   * @param int    [copytestersfrombuild]
   *
   *               step 1) is a number ?
   *                       will be considered a target build id.
   *                       check will be done to verify that is a valid
   *                       build id inside the test plan.
   *
   *               step 2) is a string ?
   *                       will be used as build name to search inside
   *                       the test plan.
   * 
   *               if check is OK, tester assignments will be copied.
   *
   */
  public function updateBuild($id) {

    $op = array('status' => 'ko', 'message' => 'ko', 
                'details' => array(), 'id' => -1);  

    $rightToCheck = "testplan_create_build";

    // need to get input, before doing right checks,
    // because right can be tested against in this order
    // Test Plan Right
    // Test Project Right
    // Default Right
    $request = $this->app->request();
    $item = json_decode($request->getBody());
    if( null == $item ) {
      $this->byeHTTP500();
    }

    
    $statusOK = true;
    if( intval($id) <= 0 ) {
        $op['details'][] = $this->l10n['API_MISSING_REQUIRED_PROP'] .
                           'id - the build ID';
        $statusOK = false;
    } 

    if( $statusOK ) {
      $build = $this->buildMgr->get_by_id( $id );      
      
      if( null == $build ) {
        $statusOK = false;
        $op['message'] = 
          sprintf($this->l10n['API_INVALID_BUILDID'],$id);
        $this->app->status(404);
      }
    }

    if( $statusOK ) {
      $tplan = $this->tplanMgr->get_by_id( $build['testplan_id'] );

      // Ready to check user permissions
      $context = array('tplan_id' => $tplan['id'], 
                       'tproject_id' => $tplan['testproject_id']);

      if( !$this->userHasRight($rightToCheck,TRUE,$context) ) {
        $statusOK = false;
        $msg = lang_get('API_INSUFFICIENT_RIGHTS');
        $op['message'] = 
          sprintf($msg,$rightToCheck,$this->user->login,
                  $context['tproject_id'],$context['tplan_id']);
        $this->app->status(403);
      } 
    }  

    // Go ahead, try to update build!!
    if( $statusOK ) {

      // Step 1 - Check if build name already exists
      if( property_exists($item,'name') ) {
        if( $this->tplanMgr->check_build_name_existence(
                             $tplan['id'],$item->name,$id) ) {
          $statusOK = false;
          $op['message'] = 
            sprintf($this->l10n['API_BUILDNAME_ALREADY_EXISTS'], 
                      $item->name, $id);
          $this->app->status(409);
        }
      }
    }    

    // Step 2 - Finally Update It!!
    if( $statusOK ) {

      $k2check = array('is_open', 'name',
                       'release_candidate',
                       'notes','commit_id','tag',
                       'branch','is_active','active', 
                       'releasedate','release_date',
                       'copy_testers_from_build',
                       'copytestersfrombuild');

      $buildProp = array('name' => 'name',
                         'tplan_id' => 'tplan_id',
                         'release_date' => 'release_date',
                         'releasedate' => 'release_date',
                         'active' => 'is_active',
                         'is_active' => 'is_active',
                         'notes' => 'notes',
                         'commit_id' => 'commit_id', 
                         'tag' => 'tag', 'branch' => 'branch', 
                         'release_candidate' =>'release_candidate',
                         'is_open' => 'is_open',
                         'copytestersfrombuild' => 
                              'copytestersfrombuild',                         
                         'copy_testers_from_build' => 
                              'copytestersfrombuild');

      $skipKey = array();
      $buildObj = new stdClass();
      $attr = array();
      foreach( $k2check as $key ) {
        $translate = $buildProp[$key]; 
        if( !isset($skipKey[$translate]) ) {

          // init with value got from DB.
          if( isset($build[$translate]) ) {
            $buildObj->$translate = $build[$translate];
          }

          if( property_exists($item, $key) ) {
            $buildObj->$translate = $item->$key;
            $skipKey[$translate] = true;
          }
          
          if( property_exists($buildObj, $translate) ) {
            $attr[$translate] = $buildObj->$translate;
          }  
        }
      }

      // key 2 check 
      // $id,$name,$notes,$active=null,$open=null,
      // $release_date='',$closed_on_date='') {

      /*
      $k2check = array('name','notes','active','is_open',
                       'release_date');

      foreach( $k2check as $key ) {
        if( property_exists($item, $key) ) {
          $$key = $item->$key;
        } else {
          $$key = $build[$key];
        }
      }

      $ox = $this->buildMgr->update($id,$name,
                               $notes,$active,$is_open,$release_date);
      */
      $ox = $this->buildMgr->update($build['id'],
              $buildObj->name,$buildObj->notes,$attr);

      if( $ox ) {
        $op = array('status' => 'ok', 'message' => 'ok', 
                    'details' => array(), 'id' => $id);  
      
        // Special processing Build Closing/Opening
        // we need also to manage close on date.
        if( property_exists($item,'is_open') ) {
          $oio = intval($build['is_open']);
          $nio = intval($item->is_open);
          if( $oio != $nio ) {
            if( $nio ) {
              $this->buildMgr->setOpen($id);
            } else {
              $this->buildMgr->setClosed($id);
            }
          }
        }


      } 
    }    

    echo json_encode($op);
  }

  /**
   * body will contain an array of objects
   * that can be 
   * {'name': platform name}
   * {'id': platform id}
   *
   * Check if done to understand if all platforms
   * exist before doing any action
   *
   *
   */
  public function addPlatformsToTestPlan($tplan_id) {
    $op = array('status' => 'ko', 'message' => 'ko', 'id' => -1);  
    try {
      $request = $this->app->request();
      $plat2link = json_decode($request->getBody());

      $op = array('status' => 'ok', 'message' => 'ok');
      $statusOK = true;  
      if (null == $plat2link || !is_array($plat2link)) {
        $statusOK = false;
        $op['status'] = 'ko';
        $op['message'] = 'Bad Body';
      }
      
      if ($statusOK) {
        // Validate Test plan existence.
        // Get Test Project ID before doing anything
        $getOpt = array('output' => 'testPlanFields','active' => 1,
                    'testPlanFields' => 'id,testproject_id,is_public');
        
        $testPlan = $this->tplanMgr->get_by_id($tplan_id,$getOpt);
        $statusOK = !is_null($testPlan);

        if ($statusOK) {
          $tproject_id = $testPlan['testproject_id'];
        } else {
          $op['status'] = 'ko';
          $op['message'] = 'Invalid Test Plan ID';
        }
      }

      if ($statusOK) {
        // Get all test project platforms, then validate
        $platMgr = new tlPlatform($this->db,$tproject_id);
        $platDomain = $platMgr->getAll();
        $idToLink = [];
        $op['message'] = [];

        foreach ($plat2link as $accessObj) {
          $checkOK = false;
          if (property_exists($accessObj, 'name')) {
            $needle = trim($accessObj->name);
            foreach ($platDomain as $target) {
              if ($target['name'] == $needle) {
                $checkOK = true;
                $idToLink[$target['id']] = $target['id'];
              }
            }
            $statusOK = $statusOK && $checkOK; 
            if ($checkOK == false) {
              $op['message'][] = "Platform with name:" .
                                 $needle .
                                 " does not exist"; 
            }
          }

          if (property_exists($accessObj, 'id')) {
            $needle = intval($accessObj->id);
            foreach ($platDomain as $target) {
              if ($target['id'] == $needle) {
                $checkOK = true;
                $idToLink[$target['id']] = $target['id'];
              }
            }
            $statusOK = $statusOK && $checkOK; 
            if ($checkOK == false) {
              $op['message'][] = "Platform with id:" .
                                 $needle .
                                 " does not exist"; 
            }
          }
        }

        $op['status'] = $statusOK;
      }

      if ($statusOK) {
        $p2link = [];
        // Finally link platforms, if not linked yet
        $gOpt = array('outputFormat' => 'mapAccessByID');
        $linked = (array)$platMgr->getLinkedToTestplan($tplan_id,$gOpt);
        foreach ($idToLink as $plat_id) {
          if (!isset($linked[$plat_id])) {
            $p2link[$plat_id]=$plat_id;
          }
        }
        if (count($p2link) >0){
          $platMgr->linkToTestplan($p2link,$tplan_id);
        }
      }  

      if ($op['status']) {
        $op['message'] = 'ok';
      }
    } catch (Exception $e) {
      $this->app->status(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                       $this->msgFromException($e);   
    }
    echo json_encode($op);
  }




  /**
   *
   */
  function byeHTTP500($msg=null) {
    $op = array();
    if( null == $msg ) {
      $msg = 'TestLink Fatal Error - Malformed Request Body - ' .
             ' json_decode() issue';
    }
    $op['details'][] = sprintf($msg);
    $this->app->status(500);
    echo json_encode($op);
    exit();  // Bye!
  }

  /**
   *
   */
  function msgFromException($e)
  {
    return $e->getMessage() . 
           ' - offending line number: ' . $e->getLine();   
  }
} // class end
