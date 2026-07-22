<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * @filesource 	RestApi.class.php
 *
 * @author 		Francisco Mancardi <francisco.mancardi@gmail.com>
 * @package 	TestLink
 * 
 * Implemented using
 * Slim framework Version 4.3.0 / 4.4.0
 * PHP > 7.4.0
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
// exportDataToXML() lives here — needed by the legacy suite/case
// XML exporters reused by the /testsuites/{id}/xml endpoint
require_once('xml.inc.php');

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

/**
 * @author    Francisco Mancardi <francisco.mancardi@gmail.com>
 * @package   TestLink 
 */
class RestApi
{
  public static $version = "3.0";
    
    
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

    // $this->app->contentType('application/json');

    $tl = array('API_MISSING_REQUIRED_PROP' => null,
                'API_TESTPLAN_ID_DOES_NOT_EXIST' => null,
                'API_TESTPLAN_APIKEY_DOES_NOT_EXIST' => null,
                'API_BUILDNAME_ALREADY_EXISTS' => null,
                'API_INVALID_BUILDID' => null);

    $this->l10n = init_labels($tl);

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
    $this->buildMgr = new build_mgr($this->db);

    $this->tables = $this->tcaseMgr->getDBTables();

    $this->cfg = array();
    $conf = config_get('results');
    foreach($conf['status_label_for_exec_ui'] as $key => $label ) {
      $this->cfg['exec']['statusCode'][$key] = $conf['status_code'][$key];  
    }
    
    $this->cfg['exec']['codeStatus'] = array_flip($this->cfg['exec']['statusCode']);

    $this->cfg['tcase']['status'] = config_get('testCaseStatus');
    $this->cfg['tcase']['executionType'] = 
      config_get('execution_type');

    $this->cfg['tcase']['executionType']['automatic'] = 
      $this->cfg['tcase']['executionType']['auto'];

        
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
  public function authenticate(Request $request, RequestHandler $handler)
  {
    // the login endpoint is the one route that cannot require a key
    $rp = $request->getUri()->getPath();
    if (substr($rp, -11) == '/auth/login' &&
        strtoupper($request->getMethod()) == 'POST') {
      return $handler->handle($request);
    }

    $apiKey = null;

    // @20200317 - Not tested
    // IMPORTANT NOTICE: 'PHP_AUTH_USER'
    // it seems this needs special configuration
    // with Apache when you use CGI Module
    // http://man.hubwiz.com/docset/PHP.docset/Contents/Resources/
    //        Documents/php.net/manual/en/features.http-auth.html
    //
    // PSR-7 getHeaderLine() is case-insensitive, so this also matches
    // proxies that normalize header names to lowercase.
    $apiKeySet = [
      'Apikey',
      'PHP_AUTH_USER'
    ];
    foreach( $apiKeySet as $accessKey ) {
      $hval = trim($request->getHeaderLine($accessKey));
      if ($hval != '') {
        $apiKey = $hval;
        break;
      }
    }

    if ($apiKey != null && $apiKey != '') {
      $sql = "SELECT id FROM {$this->tables['users']} " .
           "WHERE script_key='" . 
           $this->db->prepare_string($apiKey) . "'";

      $this->userID = $this->db->fetchFirstRowSingleColumn($sql, "id");
      if( ($ok=!is_null($this->userID)) ) {
        $this->user = tlUser::getByID($this->db,$this->userID);  
        return $handler->handle($request);
      } 
    }

    // =========================================================
    // Houston we have a problem
    $msg = 'Authentication Error';
    if ($apiKey == null) {
      $msg .= " (missing authentication key) ";
    } 
    $response = new Response();
    $response->getBody()->write($msg);
    return $response->withStatus(401);
  }

  /**
   *
   */
  public function setContentTypeJSON(Request $request, RequestHandler $handler)
  {
    $response = $handler->handle($request);
    // binary endpoints (attachment download) set their own type
    if ($response->getHeaderLine('Content-Type') != '') {
      return $response;
    }
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  /**
   *
   */
  public function whoAmI(Request $request, Response $response, $args)
  {    
    $msg = json_encode(array('name' => __CLASS__ . ' : You have called Get Route /whoAmI'));
    $response->getBody()->write($msg);
    return $response;
  }
  

  /**
   *
   * @param {array} $args
   *                parameter passed in route
   *                example
   *                ../testprojects/12
   *
   *                array(1) {
   *                   ["id"]=> string(2) "12"
   *                }
   *
   */
  public function testprojects(Request $request, Response $response, $args)
  {    
     $itemSet = $this->getProjects($args);

     // $data = array('name' => 'Bob', 'age' => 40);
     // $payload = json_encode($data)//////;
     //
     // $response->getBody()->write($payload);
     // return $response
     //           ->withHeader('Content-Type', 'application/json');
     $payload = json_encode($itemSet);
     $response->getBody()->write($payload);
     return $response;
  }


  /**
   *
   * @param array idCard if provided identifies test project
   *              Slim Framework will provided a map with a key
   *              as defined in the route.
   *              $app->get('/testprojects/{mixedID}/testplans', ...
   *  
   * 
   */
  private function getProjects($idCard=null, $opt=null) 
  {
    $options = array_merge(array('output' => 'rest'), (array)$opt);
    $op = array('status' => 'ok', 'message' => 'ok', 'item' => null);
    if(is_null($idCard) || count($idCard) == 0) {
      $opOptions = array('output' => 'array_of_map', 
                         'order_by' => " ORDER BY name ", 
                         'add_issuetracker' => true,
                         'add_reqmgrsystem' => true);
      $op['item'] = $this->tprojectMgr
                         ->get_accessible_for_user(
                             $this->userID,$opOptions);
    } else {
      $opOptions = array('output' => 'map',
                         'field_set' => 'prefix', 
                         'format' => 'simple');
      $zx = $this->tprojectMgr
                 ->get_accessible_for_user(
                     $this->userID,$opOptions);

      $targetID = null;
      $safeID = intval($idCard['mixedID']);
      if ($safeID > 0) {
        if( isset($zx[$safeID]) ) {
          $targetID = $safeID;
        } 
      } 
      else {
        // Will consider id = name or prefix
        foreach( $zx as $itemID => $value ) {
          if( strcmp($value['name'],$idCard['mixedID']) == 0 || 
              strcmp($value['prefix'],$idCard['mixedID']) == 0 ) {
            $targetID = $itemID;
            break;   
          }  
        }
      }

      if( null != $targetID ) {
        $op['item'] = $this->tprojectMgr->get_by_id($targetID);  
      }  
    } 

    return $op['item'];
  }

  /**
   * Will return LATEST VERSION of each test case.
   * Does return test step info ?
   *
   * Supports optional pagination via query string:
   *   ?page=<1-based page number>&limit=<items per page>
   * When neither page nor limit is provided, ALL test cases are
   * returned in a single response (backward compatible behavior).
   * When paginated, the response carries page/limit/total so clients
   * can iterate.
   *
   * @param array idCard if provided identifies test project
   *                     'id' -> DBID
   *                     'name' ->
   *                     'prefix' ->
   */
  public function getProjectTestCases(Request $request, Response $response, $idCard)
  {

    $op  = array('status' => 'ok',
                 'message' => 'ok',
                 'items' => null);
    $tproject = $this->getProjects($idCard,
                         array('output' => 'internal'));

    if( !is_null($tproject) ) {
      $tcaseIDSet = array();
      $this->tprojectMgr->get_all_testcases_id($tproject['id'],$tcaseIDSet);

      $qs = $request->getQueryParams();
      $paginated = isset($qs['page']) || isset($qs['limit']);
      if( $paginated ) {
        $limit = isset($qs['limit']) ? max(1,intval($qs['limit'])) : 100;
        $page = isset($qs['page']) ? max(1,intval($qs['page'])) : 1;
        $op['total'] = count($tcaseIDSet);
        $op['page'] = $page;
        $op['limit'] = $limit;
        $tcaseIDSet = array_slice($tcaseIDSet,($page-1)*$limit,$limit);
      }

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
      $op['message'] = "No Test Project identified by '" .
        (is_array($idCard) ? implode(',', $idCard) : $idCard) . "'!";
      $op['status']  = 'error';
    }

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;

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
  public function createTestProject(Request $request, Response $response, $args) {
    $op = array('status' => 'ko', 
                'message' => 'ko', 
                'id' => -1);  

    try {
      // Check user grants for requested operation
      // This is a global right
      $rightToCheck="mgt_modify_product";
      if( $this->userHasRight($rightToCheck) ) {
        $op = array('status' => 'ok', 'message' => 'ok');
        $item = json_decode($request->getBody());
        $op['id'] = $this->tprojectMgr->create($item,
                             array('doChecks' => true));
      } else {
        $response = new Response();
        $response->withStatus(403);

        $msg = lang_get('API_INSUFFICIENT_RIGHTS');
        $op['message'] = sprintf($msg,$rightToCheck,0,0);
      } 
    } 
    catch (Exception $e) {
      $response = new Response();
      $response->withStatus(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                       $this->msgFromException($e);  
    }
    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
  }

 /**
  *
  * @param array idCard if provided identifies test project
  *                     'id' -> DBID
  *                     'name' ->
  *                     'prefix' -> 
  */
  public function getProjectTestPlans(Request $request, 
                                      Response $response,
                                      $idCard) 
  {
    $op  = [
      'status' => 'ok', 
      'message' => 'ok', 
      'items' => null
    ];
    
    $tproj = $this->getProjects($idCard, 
                      array('output' => 'internal'));
 
    if( !is_null($tproj) ) {
      $items = $this->tprojectMgr->get_all_testplans($tproj['id']);
      $op['items'] = (!is_null($items) && count($items) > 0) 
                     ? $items : null;
    } else {
      $op['message'] = "No Test Project identified by '" . $idCard . "'!";
      $op['status']  = 'error';
      $response = new Response();
      $response->withStatus(500);
    }

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
  }

  /**
   *
   * @param map idCard[tplanApiKey]
   *              
   */
  public function getPlanBuilds(Request $request, 
                                Response $response, 
                                $idCard)  
  {
    $op  = $this->getStdOp();
    $tplan = $this->tplanMgr->getByAPIKey($idCard['tplanApiKey']);
 
    if( !is_null($tplan) ) {
      $items = $this->tplanMgr->get_builds($tplan['id']);
      $op['items'] = (!is_null($items) && count($items) > 0) 
                     ? $items : null;
    } else {
      $op['message'] = "No Test Plan identified by API KEY:" . 
                       $idCard['tplanApiKey'] . "";
      $op['status']  = 'error';
    }

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
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
   *                       check will be done to verify that 
   *                       is a valid build id inside the test plan.
   *
   *               step 2) is a string ?
   *                       will be used as build name 
   *                       to search inside the test plan.
   * 
   *               if check is OK, tester assignments will be copied.
   *
   */
  public function createBuild(Request $request, 
                              Response $response, 
                              $args) 
  {

    $op = array('status' => 'ko', 'message' => 'ko', 
                'details' => array(), 'id' => -1);  

    $rightToCheck = "testplan_create_build";

    // need to get input, before doing right checks,
    // because right can be tested against in this order
    // Test Plan Right
    // Test Project Right
    // Default Right
    $item = json_decode($request->getBody());
    if( null == $item ) {
      $this->byeHTTP500(__METHOD__);  // No return from it
    }

    $statusOK = true;
    $build = new stdClass();
 
    $reqProps = array('testplan','name');
    foreach( $reqProps as $prop ) {
      if( !property_exists($item, $prop) ) {
        $op['details'][] = 
          $this->l10n['API_MISSING_REQUIRED_PROP'] . $prop;
        $statusOK = false;
      } 
    }

    if( $statusOK ) {
      $build->name = $item->name;

      if( is_numeric($item->testplan) ) {
        // Check if is a valid test plan
        // Get it's test project id
        $tplan_id = intval($item->testplan);
        $tplan = $this->tplanMgr->get_by_id($tplan_id);

        if( null == $tplan ) {
          $statusOK = false;
          $op['details'][] = 
            sprintf($this->l10n['API_TESTPLAN_ID_DOES_NOT_EXIST'],
                    $item->testplan);

          $response = new Response();
          $response->withStatus(404);
        }
      } else {
        $tplanAPIKey = trim($item->testplan);
        $tplan = $this->tplanMgr->getByAPIKey( $tplanAPIKey );
        if( null == $tplan ) {
          $statusOK = false;
          $op['details'][] = 
            sprintf($this->l10n['API_TESTPLAN_APIKEY_DOES_NOT_EXIST'],$item->testplan);

          $response = new Response();
          $response->withStatus(404);
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
        
        $response = new Response();
        $response->withStatus(404);
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

        $response = new Response();
        $response->withStatus(409);
      }

      $build->tplan_id = $context['tplan_id'];
    }    

    // Step 2 - Finally Create It!!
    if( $statusOK ) {
      // key 2 check with default value is parameter is missing
      $k2check = array('is_open' => 1,
                       'release_candidate' => null,
                       'notes' => null,
                       'commit_id' => null, 
                       'tag' => null,
                       'branch' => null,
                       'is_active' => 1,
                       'active' => 1, 
                       'releasedate' => null,'release_date' => null,
                       'copy_testers_from_build' => null,
                       'copytestersfrombuild' => null);

      $buildProp = $this->buildPropMapping();

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

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
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
   *        step 1) is a number ?
   *                will be considered a target build id.
   *                check will be done to verify that is 
   *                a valid build id inside the test plan.
   *
   *        step 2) is a string ?
   *                will be used as build name to search 
   *                inside the test plan.
   * 
   *        if check is OK, tester assignments will be copied.
   *
   */
  public function updateBuild(Request $request, 
                              Response $response, 
                              $args) 
  {

    $op = array('status' => 'ko', 'message' => 'ko', 
                'details' => array(), 'id' => -1);  

    $id = intval($args['id']);
    $rightToCheck = "testplan_create_build";

    // need to get input, before doing right checks,
    // because right can be tested against in this order
    // Test Plan Right
    // Test Project Right
    // Default Right
    $item = json_decode($request->getBody());
    if( null == $item ) {
      $this->byeHTTP500(__METHOD__);  // No return from it
    }

    
    $statusOK = true;
    if( $id <= 0 ) {
        $op['details'][] = $this->l10n['API_MISSING_REQUIRED_PROP'] .
                           'id - the build ID';
        $statusOK = false;
    } 

    if( $statusOK ) {
      $build = $this->buildMgr->get_by_id($id);      
      
      if( null == $build ) {
        $statusOK = false;
        $op['message'] = 
          sprintf($this->l10n['API_INVALID_BUILDID'],$id);

        $response = new Response();
        $response->withStatus(404);
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

        $response = new Response();
        $response->withStatus(403);
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

          $response = new Response();
          $response->withStatus(409);
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

      $buildProp = $this->buildPropMapping();

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

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
  }

  /**
   * 'name'
   * 'testProjectID'
   * 'testProjectPrefix'
   * 'notes'
   * 'active'
   * 'is_public'
   *
   */
  public function createTestPlan(Request $request, 
                                 Response $response, 
                                 $args) 
  {
    $op = $this->getStdIDKO();
    try {
      $item = json_decode($request->getBody());
      $op = array('status' => 'ok', 'message' => 'ok');
      $opeOpt = array('setSessionProject' => false,
                      'doChecks' => true);

      if (property_exists($item, 'testProjectPrefix')) {
        $pi = $this->tprojectMgr->get_by_prefix(trim($item->testProjectPrefix));
        $item->testProjectID = intval($pi[id]);
      }

      $op['id'] = $this->tplanMgr->createFromObject($item,$opeOpt);
      
    } catch (Exception $e) {
      $response = new Response();
      $response->withStatus(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                       $this->msgFromException($e);  
    }

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
  }


  /**
   * 'name'
   * 'testProjectID'
   * 'notes'
   * 'active'
   * 'is_public'
   *
   */
  public function updateTestPlan(Request $request, 
                                 Response $response, 
                                 $args) 
  {
    $op = $this->getStdIDKO();
    $id = intval($args['id']);
    try {
      $op = array('status' => 'ok', 'message' => 'ok');
      $item = json_decode($request->getBody());
      $item->id = $id;
      var_dump($item);
      $op['id'] = $this->tplanMgr->updateFromObject($item);
    } catch (Exception $e) {
      $response = new Response();
      $response->withStatus(500);
      $op['message'] = $this->msgFromException($e);
    }

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
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
   * $ex-steps []
   * An example:
      "steps":[
        {
          "stepNumber":1,
          "notes":"This is an execution created via REST API",
          "statusCode":"b"
        },
        {
          "stepNumber":12,
          "notes":"This is an execution created via REST API",
          "statusCode":"f"
        }
      ]   

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
  public function createTestCaseExecution(Request $request, 
                                          Response $response, 
                                          $args) 
  {
    $op = $this->getStdIDKO();

    try {
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

      // This writes ONLY a test case level, not steps
      $op['id'] = $this->tplanMgr->writeExecution($ex);

    } catch (Exception $e) {
      $response = new Response();
      $response->withStatus(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                       $this->msgFromException($e);  
    }

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
  }

  /**
   * 'name'
   * 'testProjectID'
   * 'parentID'
   * 'notes'
   * 'order'
   */
  public function createTestSuite(Request $request, 
                                  Response $response, 
                                  $args) 
  {
    $op = $this->getStdIDKO();
    try {
      $item = json_decode($request->getBody());
      $op = array('status' => 'ok', 'message' => 'ok');
      $op['id'] = $this->tsuiteMgr->createFromObject($item,array('doChecks' => true));
    } catch (Exception $e) {
      $response = new Response();
      $response->withStatus(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                       $this->msgFromException($e);  
    }

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
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
  public function addPlatformsToTestPlan(Request $request, 
                                         Response $response, 
                                         $args) 
  {
    $op = $this->getStdIDKO();
    $tplan_id = intval($args['tplan_id']);
    try {
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
        $getOpt = array('output' => 'testPlanFields',
                        'active' => 1,
                        'testPlanFields' => 
                          'id,testproject_id,is_public');
        
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
        // Get all test project platforms, 
        // that can be used on TEST PLAN
        // (enabled on execution)
        //
        // then validate
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
              $op['message'][] = 
                  " WARNING! - Platform with name:" .
                  $needle . " Reason: does not exist " .
                  " or is not enabled for execution"; 
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
      $response = new Response();
      $response->withStatus(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                       $this->msgFromException($e);  
    }

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
  }

  /**
   * "name"
   * "testSuite": {"id": xxx}
   * "testProject" : {"id": xxx} or {"prefix": yyy}
   *
   * One of the following
   * "authorLogin" 
   * "authorID"
   * ------------------------------------------
   *
   * "summary"        can be a string or an array of strings
   * "preconditions"  can be a string or an array of strings
   *
   * "importance": {"name": "verbose"} 
   *               - see const.inc.php for domain
   * "executionType": {"name": "verbose"}
   *               - see ... for domain
   * "order"
   *
   * "estimatedExecutionDuration"  // to be implemented
   *
   * "steps": array of objects
   *           IMPORTANT NOTICE: actions and expected_results
   *                             Can be string or array of strings
   *           [
   *             { "step_number":1,
   *               "actions": "red",  
   *               "expected_results": "#f00",
   *               "execution_type":1
   *             },
   *             { "step_number":12,
   *               "actions": "red12",
   *               "expected_results": "#f00",
   *               "execution_type":2
   *             }
   *            ]
   *
   */
  public function createTestCase(Request $request, 
                                 Response $response, 
                                 $args) 
  {
    $op = $this->getStdIDKO();
    try {

      // It will be important to document WHY!!!
      // AFAIK some issues with json_decode()
      // https://stackoverflow.com/questions/34486346/new-lines-and-tabs-in-json-decode-php-7      
      $body = str_replace("\n", '', $request->getBody());
      $item = json_decode($body);

      if (null == $item) {
        $this->byeHTTP500(__METHOD__);
      }

      // create obj with standard properties
      $tcase = $this->buildTestCaseObj($item);
      $op['message'] = 'After buildTestCaseObj() >> ' . json_encode($tcase);
      $this->checkRelatives($tcase);
      
      $ou = $this->tcaseMgr->createFromObject($tcase);
      $op = array('status' => 'ok', 'message' => 'ok', 'id' => -1);
      if( ($op['id']=$ou['id']) <= 0) {
        $op['status'] = 'ko';
        $op['message'] = $ou['msg'];
        $response = new Response();
        $response->withStatus(409);
      }
    } catch (Exception $e) {
      $response = new Response();
      $response->withStatus(500);
      if ($op['message'] == 'ko') {
        $op['message'] = __METHOD__ . ' >> ';  
      } 
      $op['message'] .= $this->msgFromException($e);
    }

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
  }


  /**
   * "keyword"
   * "notes"
   * "testProject": {"prefix":"APR"}
   */
  public function createKeyword(Request $request,
                                Response $response,
                                $args)
  {
    // New SPA/admin path: body carries testProjectID directly.
    // {testProjectID, keyword, notes?}  ->  addKeyword by project id.
    // The legacy prefix path (below) is preserved untouched for
    // existing callers that send testProject.prefix.
    $probe = json_decode((string)$request->getBody());
    if (null != $probe && isset($probe->testProjectID)) {
      $op = array('status' => 'ok', 'message' => 'ok');
      try {
        if (!isset($probe->keyword) || trim((string)$probe->keyword) === '') {
          throw new Exception('Body must carry testProjectID and a non-empty keyword');
        }
        $pid = intval($probe->testProjectID);
        $notes = isset($probe->notes) ? strval($probe->notes) : '';
        $ou = $this->tprojectMgr->addKeyword($pid, trim((string)$probe->keyword), $notes);
        if ($ou['status'] < tl::OK) {
          throw new Exception($ou['msg']);
        }
        $op['id'] = intval($ou['id']);
      } catch (Exception $e) {
        $op = array('status' => 'error',
                    'message' => $this->msgFromException($e));
        $response = $response->withStatus(400);
      }
      $response->getBody()->write(json_encode($op));
      return $response;
    }

    $op = $this->getStdIDKO();

    try {
      $body = $request->getBody();
      $bigString = $body->getContents();

      $ba = explode('",', $bigString);
      $needle = '"notes":';
      foreach( $ba as $pa => $ma) {
        if (strpos($ma, $needle) !== FALSE) {
          $zz = explode($needle,$ma);
          $ba[$pa] = $needle . 
                     str_replace("\n", "?^§", $zz[1]);
        }
        $ba[$pa] .= '",';
      }
    
      $bigString = implode("",$ba);
      $bigString = trim($bigString,'",');
      $item = json_decode($bigString);
      if( null == $item ) {
        $this->byeHTTP500(__METHOD__);
      }

      if (property_exists($item, 'notes')) {
        $item->notes = str_replace("?^§", "\n", $item->notes);

        // try to remove useless spaces
        $item->notes = str_replace("   ", "", $item->notes);
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
        $op = array('status' => 'ok', 'message' => 'ok');
        $op['id'] = $ou['id'];
        if ($ou['status'] < 0) {
          $op['status'] = 'ko';
          $op['message'] = $ou['msg'];          
        }
      }
    } catch (Exception $e) {
      $response = new Response();
      $response->withStatus(500);
      $op['message'] = __METHOD__ . ' >> ' . 
                       $this->msgFromException($e);  
    }

    $payload = json_encode($op);
    $response->getBody()->write($payload);
    return $response;
  }


  /* ************************************ */
  /*             Helpers                  */ 
  /* ************************************ */
  private function buildPropMapping() 
  {
    $bp = array('name' => 'name',
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
    return $bp;
  }


  /**
   *
   *
   */ 
  private function buildTestCaseObj(&$obj) 
  {
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

    // --------------------------------------------------------------
    // summary & preconditions
    // if type is array -> generate string in this way
    // - add <pre>
    // - concact the elements with "\n"
    // - add </pre>
    // --------------------------------------------------------------
    $sk2d = array('summary' => '',
                  'preconditions' => '');
    foreach($sk2d as $key => $value) {
      if (is_array($tcase->$key)) {
        $tcase->$key = "<pre>" . implode("\n", $tcase->$key) . "</pre>";
      } 
    } 
    // --------------------------------------------------------------


    // --------------------------------------------------------------
    // these are objects with name as property.
    $tcfg = $this->cfg['tcase'];
    $ck2d = array('executionType' => $tcfg['executionType']['manual'], 
                  'importance'    => $tcfg['defaults']['importance'], 
                  'status'        => $tcfg['status']['draft']);

    foreach($ck2d as $prop => $defa) {
      $tcase->$prop = property_exists($obj, $prop) ? 
        $tcfg[$prop][$obj->$prop->name] : $defa;      
    }  


    // --------------------------------------------------------------
    if(property_exists($obj, 'steps')) {
      $tcase->steps = [];
      $sk2d = array('actions' => '',
                    'expected_results' => '');
      foreach($obj->steps as $stepObj) {
        foreach($sk2d as $key => $value) {
          if (is_array($stepObj->$key)) {
            $stepObj->$key = "<pre>" . implode("\n", $stepObj->$key) . "</pre>";
          }
        } 
        $tcase->steps[] = $stepObj;
      }      
    }
    // --------------------------------------------------------------


    return $tcase;
  }

  /**
   *
   */
  private function checkExecutionEnvironment($ex) 
  {
    // throw new Exception($message, $code, $previous);

    // no platform
    $platform = 0;

    // Test plan ID exists and is ACTIVE    
    $msg = 'invalid Test plan ID';
    $getOpt = array('output' => 'testPlanFields',
                    'active' => 1,
                    'testPlanFields' => 
                      'id,testproject_id,is_public');
    $status_ok = !is_null($testPlan=$this->tplanMgr->get_by_id($ex->testPlanID,$getOpt));
    
    if($status_ok) {
      // user has right to execute on Test plan ID
      // hasRight(&$db,$roleQuestion,$tprojectID = null,$tplanID = null,$getAccess=false)
      $msg = 'user has no right to execute';
      $status_ok = $this->user->hasRight($this->db,
                                  'testplan_execute',
                                  $testPlan['testproject_id'],
                                  $ex->testPlanID,true); 
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
      throw new Exception(
        "Test Suite ID is invalid (does not exist)");
    }  

    if( $testProjectID != $this->tsuiteMgr->getTestProjectFromTestSuite($testSuiteID,$testSuiteID) ) {
      throw new Exception(
        "Test Suite does not belong to Test Project ID");
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
  protected function userHasRight($rightToCheck,
              $checkPublicPrivateAttr=false,$context=null)
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
   *
   */
  private function getStdOp() 
  {
    $op  = array('status' => 'ok', 
                 'message' => 'ok', 
                 'items' => null);
    return $op;
  }

  /**
   *
   */
  private function getStdIDKO() 
  {
    $op  = array('status' => 'ko', 
                 'message' => 'ko', 
                 'id' => -1);
    return $op;
  }


  /**
   *
   */
  function byeHTTP500($msg=null) 
  {
    $op = array();
    if( null == $msg ) {
      $msg = 'TestLink Fatal Error - Malformed Request Body - ' .
             ' json_decode() issue';
    }
    $op['details'][] = sprintf($msg);

    $response = new Response();
    $response->getBody()->write('Malformed Request Body');
    $response->withStatus(500);
    return $response;
  }


  /**
   *
   */
  function msgFromException($e)
  {
    return $e->getMessage() .
           ' - offending line number: ' . $e->getLine();
  }

  // ==================================================================
  // Endpoints backing the SPA (ui/) — JSON in, JSON out.
  // ==================================================================

  /**
   * POST /auth/login  {login, password}
   * On success returns the user's API key (creating one when the
   * user has none yet) so the SPA can use the standard Apikey header.
   */
  public function authLogin(Request $request, Response $response, $args)
  {
    $op = array('status' => 'error', 'message' => 'Invalid credentials');
    $item = json_decode($request->getBody());

    if (null != $item && isset($item->login) && isset($item->password)) {
      $user = new tlUser();
      $user->login = trim($item->login);
      if ($user->readFromDB($this->db, tlUser::USER_O_SEARCH_BYLOGIN) >= tl::OK &&
          $user->isActive &&
          $user->comparePassword($this->db, $item->password) == tl::OK) {

        if (strlen(trim((string)$user->userApiKey)) == 0) {
          // no stored API key -> mint one (32 hex chars, column limit)
          $user->userApiKey = bin2hex(random_bytes(16));
          $sql = " UPDATE {$this->tables['users']} " .
                 " SET script_key = '" .
                 $this->db->prepare_string($user->userApiKey) . "'" .
                 " WHERE id = " . intval($user->dbID);
          $this->db->exec_query($sql);
        }

        $op = array('status' => 'ok',
                    'apikey' => $user->userApiKey,
                    'user' => array('id' => intval($user->dbID),
                                    'login' => $user->login,
                                    'firstName' => $user->firstName,
                                    'lastName' => $user->lastName));
      }
    }

    if ($op['status'] != 'ok') {
      $response = $response->withStatus(401);
    }
    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * GET /testprojects/{id}/suites
   * Full test suite hierarchy of a project plus per-suite test case
   * counts, so a client can render the tree without N requests.
   */
  public function getProjectSuites(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $sql = " WITH RECURSIVE st AS " .
           " (SELECT id, parent_id, name, node_order " .
           "    FROM {$this->tables['nodes_hierarchy']} " .
           "   WHERE parent_id = {$safeID} AND node_type_id = 2 " .
           "  UNION ALL " .
           "  SELECT nh.id, nh.parent_id, nh.name, nh.node_order " .
           "    FROM {$this->tables['nodes_hierarchy']} nh " .
           "    JOIN st ON nh.parent_id = st.id " .
           "   WHERE nh.node_type_id = 2) " .
           " SELECT * FROM st ORDER BY parent_id, node_order, id ";
    $suites = (array)$this->db->get_recordset($sql);

    $sql = " SELECT parent_id, COUNT(*) AS qty " .
           " FROM {$this->tables['nodes_hierarchy']} " .
           " WHERE node_type_id = 3 GROUP BY parent_id ";
    $counts = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $counts[$row['parent_id']] = intval($row['qty']);
    }

    foreach ($suites as &$suite) {
      $suite['id'] = intval($suite['id']);
      $suite['parent_id'] = intval($suite['parent_id']);
      $suite['tcCount'] = isset($counts[$suite['id']]) ? $counts[$suite['id']] : 0;
    }
    unset($suite);

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'projectID' => $safeID, 'items' => $suites)));
    return $response;
  }

  /**
   * GET /testsuites/{id}/testcases
   * Summary rows of the test cases directly inside a suite.
   */
  public function getSuiteTestCases(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $sql = " SELECT NHTC.id, NHTC.name, NHTC.node_order, " .
           "        MAX(TCV.version) AS latest_version, " .
           "        MAX(TCV.tc_external_id) AS tc_external_id, " .
           "        MAX(TCV.importance) AS importance, " .
           "        MAX(TCV.status) AS status " .
           " FROM {$this->tables['nodes_hierarchy']} NHTC " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.parent_id = NHTC.id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id " .
           " WHERE NHTC.parent_id = {$safeID} AND NHTC.node_type_id = 3 " .
           " GROUP BY NHTC.id, NHTC.name, NHTC.node_order " .
           " ORDER BY NHTC.node_order, NHTC.id ";
    $items = (array)$this->db->get_recordset($sql);
    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'suiteID' => $safeID, 'items' => $items)));
    return $response;
  }

  /**
   * GET /testcases/{id}/detail
   * Latest version of one test case: attributes, steps, recent runs.
   */
  public function getTestCaseDetail(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);

    $sql = " SELECT NHTC.name, NHTC.parent_id AS suite_id, TCV.id AS tcversion_id, " .
           "        TCV.version, TCV.tc_external_id, TCV.summary, TCV.preconditions, " .
           "        TCV.importance, TCV.status, TCV.execution_type, TCV.active " .
           " FROM {$this->tables['nodes_hierarchy']} NHTC " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.parent_id = NHTC.id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id " .
           " WHERE NHTC.id = {$safeID} " .
           " ORDER BY TCV.version DESC LIMIT 1 ";
    $item = $this->db->get_recordset($sql);
    if (is_null($item)) {
      $response->getBody()->write(json_encode(
        array('status' => 'error', 'message' => 'Test case does not exist')));
      return $response->withStatus(404);
    }
    $item = current($item);
    $tcvID = intval($item['tcversion_id']);

    $sql = " SELECT TCS.step_number, TCS.actions, TCS.expected_results, " .
           "        TCS.execution_type " .
           " FROM {$this->tables['tcsteps']} TCS " .
           " JOIN {$this->tables['nodes_hierarchy']} NH ON NH.id = TCS.id " .
           " WHERE NH.parent_id = {$tcvID} ORDER BY TCS.step_number ";
    $item['steps'] = (array)$this->db->get_recordset($sql);

    $sql = " SELECT E.id, E.status, E.execution_ts, E.build_id, E.notes, " .
           "        B.name AS build_name, U.login AS tester " .
           " FROM {$this->tables['executions']} E " .
           " LEFT JOIN {$this->tables['builds']} B ON B.id = E.build_id " .
           " LEFT JOIN {$this->tables['users']} U ON U.id = E.tester_id " .
           " WHERE E.tcversion_id = {$tcvID} " .
           " ORDER BY E.id DESC LIMIT 10 ";
    $item['executions'] = (array)$this->db->get_recordset($sql);

    // evidence attached to those executions
    if (count($item['executions']) > 0) {
      $execIDSet = implode(',', array_map(function ($e) {
        return intval($e['id']);
      }, $item['executions']));
      $sql = " SELECT id, fk_id, file_name, file_size FROM {$this->tables['attachments']} " .
             " WHERE fk_table = 'executions' AND fk_id IN ({$execIDSet}) ";
      $attachMap = array();
      foreach ((array)$this->db->get_recordset($sql) as $att) {
        $attachMap[$att['fk_id']][] = $att;
      }
      foreach ($item['executions'] as &$exec) {
        $exec['attachments'] = isset($attachMap[$exec['id']]) ?
          $attachMap[$exec['id']] : array();
      }
      unset($exec);
    }

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'item' => $item)));
    return $response;
  }

  /**
   * GET /testplans/{id}/summary
   * Latest-execution status totals for a plan (dashboard numbers).
   */
  public function getPlanSummary(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);

    $sql = " SELECT COUNT(*) AS qty FROM {$this->tables['testplan_tcversions']} " .
           " WHERE testplan_id = {$safeID} ";
    $linked = intval($this->db->fetchFirstRowSingleColumn($sql, 'qty'));

    // latest-per-version via loose index scan on
    // (testplan_id, tcversion_id, id) + PK join: cost follows the
    // number of test case versions, not the number of executions.
    $sql = " SELECT E2.status, COUNT(*) AS qty FROM " .
           " (SELECT MAX(id) AS mid FROM {$this->tables['executions']} " .
           "   WHERE testplan_id = {$safeID} GROUP BY tcversion_id) M " .
           " JOIN {$this->tables['executions']} E2 ON E2.id = M.mid " .
           " GROUP BY E2.status ";
    $byStatus = array();
    $executed = 0;
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $byStatus[$row['status']] = intval($row['qty']);
      $executed += intval($row['qty']);
    }
    $byStatus['n'] = max(0, $linked - $executed);

    $info = $this->tplanMgr->get_by_id($safeID);
    $response->getBody()->write(json_encode(
      array('status' => 'ok',
            'planID' => $safeID,
            'name' => $info['name'] ?? '',
            'linked' => $linked,
            'byStatus' => $byStatus)));
    return $response;
  }

  /**
   * GET /testplans/{id}/trend
   * Daily execution counters by status.
   */
  public function getPlanTrend(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $sql = " SELECT DATE(execution_ts) AS execday, status, COUNT(*) AS qty " .
           " FROM {$this->tables['executions']} " .
           " WHERE testplan_id = {$safeID} " .
           " GROUP BY execday, status ORDER BY execday ";
    $days = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $day = $row['execday'];
      if (!isset($days[$day])) {
        $days[$day] = array('day' => $day, 'p' => 0, 'f' => 0, 'b' => 0, 'other' => 0);
      }
      $key = in_array($row['status'], array('p','f','b')) ? $row['status'] : 'other';
      $days[$day][$key] += intval($row['qty']);
    }
    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'items' => array_values($days))));
    return $response;
  }

  /**
   * GET /testplans/{id}/flaky
   * Test case versions whose executions flip between pass and fail.
   */
  public function getPlanFlaky(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    // bounded window (default 30 days, ?days= to widen) keeps the
    // scan proportional to recent activity instead of full history
    $qs = $request->getQueryParams();
    $days = isset($qs['days']) ? max(1, min(365, intval($qs['days']))) : 30;

    // id is monotonically increasing, so ordering by (tcversion_id, id)
    // follows idx_exec_tplan_tcv_id and preserves execution order.
    // Rows are streamed one at a time — materializing the whole window
    // as an array blew past memory_limit on large installations.
    $sql = " SELECT E.tcversion_id, E.status, NHTC.name, NHTC.id AS tcase_id " .
           " FROM {$this->tables['executions']} E " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = E.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " WHERE E.testplan_id = {$safeID} AND E.status IN ('p','f') " .
           " AND E.execution_ts >= NOW() - INTERVAL {$days} DAY " .
           " ORDER BY E.tcversion_id, E.id ";
    $flips = array();
    $prev = array();
    $rs = $this->db->exec_query($sql);
    while ($rs && ($row = $this->db->fetch_array($rs))) {
      $key = $row['tcversion_id'];
      if (!isset($flips[$key])) {
        $flips[$key] = array('tcversion_id' => intval($key),
                             'tcase_id' => intval($row['tcase_id']),
                             'name' => $row['name'],
                             'flips' => 0, 'total' => 0);
      }
      $flips[$key]['total']++;
      if (isset($prev[$key]) && $prev[$key] != $row['status']) {
        $flips[$key]['flips']++;
      }
      $prev[$key] = $row['status'];
    }
    $flips = array_filter($flips, function ($item) {
      return $item['flips'] > 0;
    });
    usort($flips, function ($a, $b) {
      return $b['flips'] <=> $a['flips'];
    });
    $response->getBody()->write(json_encode(
      array('status' => 'ok',
            'analyzed' => count($prev),
            'items' => array_slice($flips, 0, 50))));
    return $response;
  }

  /**
   * GET /testplans/{id}/queue?buildID=&page=&limit=
   * Linked test cases with their latest result on a build —
   * the execution work queue.
   */
  public function getPlanQueue(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $qs = $request->getQueryParams();
    $buildID = isset($qs['buildID']) ? intval($qs['buildID']) : 0;
    $limit = isset($qs['limit']) ? max(1, intval($qs['limit'])) : 200;
    $page = isset($qs['page']) ? max(1, intval($qs['page'])) : 1;
    $offset = ($page - 1) * $limit;

    $buildFilter = $buildID > 0 ? " AND E.build_id = {$buildID} " : '';

    // assignment join: execution assignments for this build
    $uaJoin = " LEFT JOIN {$this->tables['user_assignments']} UA " .
              "   ON UA.type = 1 AND UA.feature_id = T.id " .
              "   AND UA.build_id = " . ($buildID > 0 ? $buildID : 0) .
              " LEFT JOIN {$this->tables['users']} U ON U.id = UA.user_id ";

    // optional filter: assignedTo=<userID> | 'none'
    $assignFilter = '';
    if (isset($qs['assignedTo']) && $qs['assignedTo'] !== '') {
      if ($qs['assignedTo'] === 'none') {
        $assignFilter = ' AND UA.id IS NULL ';
      } else {
        $assignFilter = ' AND UA.user_id = ' . intval($qs['assignedTo']);
      }
    }

    $sql = " SELECT COUNT(*) AS qty " .
           " FROM {$this->tables['testplan_tcversions']} T {$uaJoin} " .
           " WHERE T.testplan_id = {$safeID} {$assignFilter} ";
    $total = intval($this->db->fetchFirstRowSingleColumn($sql, 'qty'));

    $sql = " SELECT T.tcversion_id, NHTCV.parent_id AS tcase_id, NHTC.name, " .
           "        TCV.tc_external_id, TCV.importance, " .
           "        UA.user_id AS assigned_to, U.login AS assigned_login, " .
           "        (SELECT E.status FROM {$this->tables['executions']} E " .
           "          WHERE E.tcversion_id = T.tcversion_id " .
           "            AND E.testplan_id = T.testplan_id {$buildFilter} " .
           "          ORDER BY E.id DESC LIMIT 1) AS exec_status " .
           " FROM {$this->tables['testplan_tcversions']} T " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = T.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id = T.tcversion_id " .
           $uaJoin .
           " WHERE T.testplan_id = {$safeID} {$assignFilter} " .
           " ORDER BY NHTC.name LIMIT {$limit} OFFSET {$offset} ";
    $items = (array)$this->db->get_recordset($sql);

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'total' => $total,
            'page' => $page, 'limit' => $limit, 'items' => $items)));
    return $response;
  }

  /**
   * GET /testplans/{id}/matrix?page=&limit=
   * Test result matrix — one row per linked test case, one column
   * per build, cell = latest execution status on that build.
   * Paginated by test case so the payload stays bounded no matter
   * how large the plan is (the legacy page shipped 11MB at once).
   */
  public function getPlanMatrix(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $qs = $request->getQueryParams();
    $limit = isset($qs['limit']) ? max(1, min(500, intval($qs['limit']))) : 100;
    $page = isset($qs['page']) ? max(1, intval($qs['page'])) : 1;
    $offset = ($page - 1) * $limit;

    // optional drill-down: only cases directly inside one suite
    $suiteID = isset($qs['suiteID']) ? intval($qs['suiteID']) : 0;
    $suiteFilter = $suiteID > 0 ? " AND NHTC.parent_id = {$suiteID} " : '';

    $sql = " SELECT COUNT(*) AS qty " .
           " FROM {$this->tables['testplan_tcversions']} T " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = T.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " WHERE T.testplan_id = {$safeID} {$suiteFilter} ";
    $total = intval($this->db->fetchFirstRowSingleColumn($sql, 'qty'));

    $sql = " SELECT id, name FROM {$this->tables['builds']} " .
           " WHERE testplan_id = {$safeID} ORDER BY id ";
    $builds = (array)$this->db->get_recordset($sql);

    // page of linked test cases
    $sql = " SELECT T.tcversion_id, NHTCV.parent_id AS tcase_id, " .
           "        NHTC.name, TCV.tc_external_id " .
           " FROM {$this->tables['testplan_tcversions']} T " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = T.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id = T.tcversion_id " .
           " WHERE T.testplan_id = {$safeID} {$suiteFilter} " .
           " ORDER BY NHTC.name LIMIT {$limit} OFFSET {$offset} ";
    $rows = (array)$this->db->get_recordset($sql);

    if (count($rows) > 0) {
      // latest execution per (version, build) only for this page —
      // IN-list + loose scan rides idx_exec_tplan_tcv_id
      $idSet = implode(',', array_map(function ($r) {
        return intval($r['tcversion_id']);
      }, $rows));

      $sql = " SELECT E2.tcversion_id, E2.build_id, E2.status FROM " .
             " (SELECT MAX(id) AS mid FROM {$this->tables['executions']} " .
             "   WHERE testplan_id = {$safeID} AND tcversion_id IN ({$idSet}) " .
             "   GROUP BY tcversion_id, build_id) M " .
             " JOIN {$this->tables['executions']} E2 ON E2.id = M.mid ";
      $cells = array();
      foreach ((array)$this->db->get_recordset($sql) as $cell) {
        $cells[$cell['tcversion_id']][$cell['build_id']] = $cell['status'];
      }
      foreach ($rows as &$row) {
        $row['results'] = isset($cells[$row['tcversion_id']]) ?
          $cells[$row['tcversion_id']] : new stdClass();
      }
      unset($row);
    }

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'total' => $total, 'page' => $page,
            'limit' => $limit, 'builds' => $builds, 'items' => $rows)));
    return $response;
  }

  /**
   * PUT /testcases/{id}/update
   * Update the latest version of a test case: name, summary,
   * preconditions, importance, and (optionally) the full step list.
   * When 'steps' is present it REPLACES the existing steps — the
   * client always sends the complete list it wants to keep.
   */
  public function updateTestCase(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $op = array('status' => 'ok', 'message' => 'ok');

    try {
      $item = json_decode($request->getBody());
      if (null == $item) {
        throw new Exception('Malformed request body');
      }

      // latest version node of this test case
      $sql = " SELECT TCV.id FROM {$this->tables['nodes_hierarchy']} NHTCV " .
             " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id " .
             " WHERE NHTCV.parent_id = {$safeID} ORDER BY TCV.version DESC LIMIT 1 ";
      $tcvID = intval($this->db->fetchFirstRowSingleColumn($sql, 'id'));
      if ($tcvID <= 0) {
        throw new Exception('Test case does not exist');
      }

      if (isset($item->name) && trim($item->name) != '') {
        $sql = " UPDATE {$this->tables['nodes_hierarchy']} SET name = '" .
               $this->db->prepare_string(trim($item->name)) . "'" .
               " WHERE id = {$safeID} ";
        $this->db->exec_query($sql);
      }

      $fields = array();
      foreach (array('summary', 'preconditions') as $key) {
        if (isset($item->$key)) {
          $fields[] = " {$key} = '" .
            $this->db->prepare_string($item->$key) . "'";
        }
      }
      if (isset($item->importance)) {
        $fields[] = ' importance = ' . intval($item->importance);
      }
      if (count($fields) > 0) {
        $sql = " UPDATE {$this->tables['tcversions']} SET " .
               implode(',', $fields) . " WHERE id = {$tcvID} ";
        $this->db->exec_query($sql);
      }

      if (isset($item->steps) && is_array($item->steps)) {
        // replace: drop existing step nodes, insert the new list
        $sql = " SELECT id FROM {$this->tables['nodes_hierarchy']} " .
               " WHERE parent_id = {$tcvID} AND node_type_id = 9 ";
        foreach ((array)$this->db->get_recordset($sql) as $row) {
          $this->tcaseMgr->delete_step_by_id(intval($row['id']));
        }
        $stepNum = 0;
        foreach ($item->steps as $step) {
          $stepNum++;
          $this->tcaseMgr->create_step(
            $tcvID, $stepNum,
            isset($step->actions) ? $step->actions : '',
            isset($step->expected_results) ? $step->expected_results : '',
            isset($step->execution_type) ? intval($step->execution_type) : 1);
        }
        $op['steps'] = $stepNum;
      }
      $op['id'] = $safeID;
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * POST /testplans/{id}/link  {tcaseIDs: [...]}
   * Link the LATEST version of each given test case to the plan.
   * Already-linked cases are skipped, so the call is idempotent.
   */
  public function linkPlanCases(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $op = array('status' => 'ok', 'message' => 'ok', 'linked' => 0, 'skipped' => 0);

    try {
      $item = json_decode($request->getBody());
      if (null == $item || !isset($item->tcaseIDs) || !is_array($item->tcaseIDs)) {
        throw new Exception('Body must carry tcaseIDs array');
      }

      foreach ($item->tcaseIDs as $tcaseID) {
        $tcaseID = intval($tcaseID);
        $sql = " SELECT TCV.id FROM {$this->tables['nodes_hierarchy']} NHTCV " .
               " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id " .
               " WHERE NHTCV.parent_id = {$tcaseID} " .
               " ORDER BY TCV.version DESC LIMIT 1 ";
        $tcvID = intval($this->db->fetchFirstRowSingleColumn($sql, 'id'));
        if ($tcvID <= 0) {
          $op['skipped']++;
          continue;
        }

        $sql = " SELECT COUNT(*) AS qty FROM {$this->tables['testplan_tcversions']} " .
               " WHERE testplan_id = {$safeID} AND tcversion_id = {$tcvID} ";
        if (intval($this->db->fetchFirstRowSingleColumn($sql, 'qty')) > 0) {
          $op['skipped']++;
          continue;
        }

        $sql = " INSERT INTO {$this->tables['testplan_tcversions']} " .
               " (testplan_id, tcversion_id, node_order, urgency, platform_id, author_id, creation_ts) " .
               " VALUES ({$safeID}, {$tcvID}, 0, 2, 0, " .
               intval($this->userID) . ", " . $this->db->db_now() . ")";
        $this->db->exec_query($sql);
        $op['linked']++;
      }
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * GET /testprojects/{id}/search?q=&limit=
   * Find test cases by external id (PREFIX-123 or bare number),
   * name, or summary text.
   */
  public function searchTestCases(Request $request, Response $response, $args)
  {
    $projectID = intval($args['id']);
    $qs = $request->getQueryParams();
    $q = isset($qs['q']) ? trim($qs['q']) : '';
    $limit = isset($qs['limit']) ? max(1, min(200, intval($qs['limit']))) : 50;

    $op = array('status' => 'ok', 'q' => $q, 'items' => array());
    if (strlen($q) < 2) {
      $op['message'] = 'query too short';
      $response->getBody()->write(json_encode($op));
      return $response;
    }

    $safeLike = $this->db->prepare_string($q);

    // PREFIX-123 / 123 -> exact external id hit first
    $extID = 0;
    if (preg_match('/(\d+)$/', $q, $m)) {
      $extID = intval($m[1]);
    }
    $extFilter = $extID > 0 ? " OR TCV.tc_external_id = {$extID} " : '';

    // scope to the project's suite subtree (suites can nest)
    $sql = " WITH RECURSIVE st AS " .
           " (SELECT id FROM {$this->tables['nodes_hierarchy']} " .
           "   WHERE parent_id = {$projectID} AND node_type_id = 2 " .
           "  UNION ALL " .
           "  SELECT nh.id FROM {$this->tables['nodes_hierarchy']} nh " .
           "  JOIN st ON nh.parent_id = st.id WHERE nh.node_type_id = 2) " .
           " SELECT DISTINCT NHTC.id AS tcase_id, NHTC.name, S.name AS suite_name, " .
           "        MAX(TCV.tc_external_id) AS tc_external_id, " .
           "        (MAX(TCV.tc_external_id) = " . ($extID > 0 ? $extID : -1) . ") AS exact_hit " .
           " FROM {$this->tables['nodes_hierarchy']} NHTC " .
           " JOIN st ON NHTC.parent_id = st.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.parent_id = NHTC.id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id " .
           " JOIN {$this->tables['nodes_hierarchy']} S ON S.id = NHTC.parent_id " .
           " WHERE NHTC.node_type_id = 3 " .
           " AND (NHTC.name LIKE '%{$safeLike}%' " .
           "      OR TCV.summary LIKE '%{$safeLike}%' {$extFilter}) " .
           " GROUP BY NHTC.id, NHTC.name, S.name " .
           " ORDER BY exact_hit DESC, NHTC.name LIMIT {$limit} ";
    $op['items'] = (array)$this->db->get_recordset($sql);

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * POST /executions/{id}/attachments  (multipart, field 'file')
   * Attach evidence (screenshot, log, ...) to an execution.
   */
  public function uploadExecutionAttachment(Request $request, Response $response, $args)
  {
    $execID = intval($args['id']);
    $op = array('status' => 'error', 'message' => 'no file received');

    if ($execID > 0 && isset($_FILES['file'])) {
      $repo = tlAttachmentRepository::create($this->db);
      $title = isset($_POST['title']) ? $_POST['title'] : $_FILES['file']['name'];
      $upOp = $repo->insertAttachment($execID, 'executions', $title, $_FILES['file']);
      if ($upOp->statusOK) {
        $op = array('status' => 'ok', 'message' => 'ok');
      } else {
        $op['message'] = 'upload rejected: ' . $upOp->statusCode;
      }
    }

    if ($op['status'] != 'ok') {
      $response = $response->withStatus(400);
    }
    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * GET /executions/{id}/attachments
   */
  public function getExecutionAttachments(Request $request, Response $response, $args)
  {
    $execID = intval($args['id']);
    $repo = tlAttachmentRepository::create($this->db);
    $items = (array)$repo->getAttachmentInfosFor($execID, 'executions');
    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'items' => array_values($items))));
    return $response;
  }

  /**
   * GET /attachments/{id}
   * Streams the attachment binary with its stored content type.
   */
  public function downloadAttachment(Request $request, Response $response, $args)
  {
    $attachID = intval($args['id']);
    $repo = tlAttachmentRepository::create($this->db);
    $info = $repo->getAttachmentInfo($attachID);
    if (is_null($info)) {
      $response->getBody()->write(json_encode(
        array('status' => 'error', 'message' => 'attachment not found')));
      return $response->withStatus(404);
    }
    $content = $repo->getAttachmentContent($attachID, $info);
    $response->getBody()->write((string)$content);
    return $response
      ->withHeader('Content-Type', $info['file_type'])
      ->withHeader('Content-Disposition',
        'inline; filename="' . addslashes($info['file_name']) . '"');
  }

  /**
   * GET /users
   * Active users, for assignment pickers.
   */
  public function getUsers(Request $request, Response $response, $args)
  {
    // role name comes from the roles table (users.role_id -> roles.id);
    // active flag is included so the admin UI can list and toggle
    // deactivated users. Legacy fields (id, login, first, last) are
    // kept verbatim — only new fields are added.
    $sql = " SELECT U.id, U.login, U.first, U.last, U.email, " .
           "        U.role_id, U.active, R.description AS role " .
           " FROM {$this->tables['users']} U " .
           " LEFT JOIN {$this->tables['roles']} R ON R.id = U.role_id " .
           " ORDER BY U.login ";
    $items = (array)$this->db->get_recordset($sql);
    foreach ($items as &$u) {
      $u['role_id'] = intval($u['role_id']);
      $u['active'] = intval($u['active']);
    }
    unset($u);
    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'items' => $items)));
    return $response;
  }

  /**
   * POST /testplans/{id}/assign  {buildID, items: [{tcaseID, userID}]}
   * Assign execution of test cases (on one build) to users.
   * userID 0 removes the assignment. Existing assignment for the
   * same (case, build) is replaced.
   */
  public function assignPlanCases(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $op = array('status' => 'ok', 'message' => 'ok',
                'assigned' => 0, 'removed' => 0, 'skipped' => 0);

    try {
      $item = json_decode($request->getBody());
      if (null == $item || !isset($item->buildID) ||
          !isset($item->items) || !is_array($item->items)) {
        throw new Exception('Body must carry buildID and items array');
      }
      $buildID = intval($item->buildID);

      foreach ($item->items as $one) {
        $tcaseID = intval($one->tcaseID ?? 0);
        $userID = intval($one->userID ?? 0);

        // linked feature id = testplan_tcversions row of the case's
        // latest linked version
        $sql = " SELECT T.id FROM {$this->tables['testplan_tcversions']} T " .
               " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = T.tcversion_id " .
               " WHERE T.testplan_id = {$safeID} AND NHTCV.parent_id = {$tcaseID} " .
               " ORDER BY T.tcversion_id DESC LIMIT 1 ";
        $featureID = intval($this->db->fetchFirstRowSingleColumn($sql, 'id'));
        if ($featureID <= 0) {
          $op['skipped']++;
          continue;
        }

        $sql = " DELETE FROM {$this->tables['user_assignments']} " .
               " WHERE type = 1 AND feature_id = {$featureID} " .
               " AND build_id = {$buildID} ";
        $this->db->exec_query($sql);

        if ($userID > 0) {
          $sql = " INSERT INTO {$this->tables['user_assignments']} " .
                 " (type, feature_id, user_id, build_id, assigner_id, creation_ts, status) " .
                 " VALUES (1, {$featureID}, {$userID}, {$buildID}, " .
                 intval($this->userID) . ", " . $this->db->db_now() . ", 1)";
          $this->db->exec_query($sql);
          $op['assigned']++;
        } else {
          $op['removed']++;
        }
      }
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * GET /testplans/{id}/byTester
   * Execution counts by tester (optionally ?buildID=), latest
   * execution per (version, build) only — re-runs don't double count.
   */
  public function getPlanByTester(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $qs = $request->getQueryParams();
    $buildID = isset($qs['buildID']) ? intval($qs['buildID']) : 0;
    $buildFilter = $buildID > 0 ? " AND build_id = {$buildID} " : '';

    $sql = " SELECT U.login, E2.status, COUNT(*) AS qty FROM " .
           " (SELECT MAX(id) AS mid FROM {$this->tables['executions']} " .
           "   WHERE testplan_id = {$safeID} {$buildFilter} " .
           "   GROUP BY tcversion_id, build_id) M " .
           " JOIN {$this->tables['executions']} E2 ON E2.id = M.mid " .
           " JOIN {$this->tables['users']} U ON U.id = E2.tester_id " .
           " GROUP BY U.login, E2.status ORDER BY U.login ";

    $byTester = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $login = $row['login'];
      if (!isset($byTester[$login])) {
        $byTester[$login] = array('login' => $login,
                                  'p' => 0, 'f' => 0, 'b' => 0, 'other' => 0);
      }
      $key = in_array($row['status'], array('p','f','b')) ? $row['status'] : 'other';
      $byTester[$login][$key] += intval($row['qty']);
    }

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'items' => array_values($byTester))));
    return $response;
  }

  /**
   * GET /testplans/{id}/byBuild
   * Latest-status totals per build — build quality comparison.
   */
  public function getPlanByBuild(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);

    $sql = " SELECT COUNT(*) AS qty FROM {$this->tables['testplan_tcversions']} " .
           " WHERE testplan_id = {$safeID} ";
    $linked = intval($this->db->fetchFirstRowSingleColumn($sql, 'qty'));

    $sql = " SELECT B.id AS build_id, B.name, E2.status, COUNT(*) AS qty FROM " .
           " (SELECT MAX(id) AS mid FROM {$this->tables['executions']} " .
           "   WHERE testplan_id = {$safeID} GROUP BY tcversion_id, build_id) M " .
           " JOIN {$this->tables['executions']} E2 ON E2.id = M.mid " .
           " JOIN {$this->tables['builds']} B ON B.id = E2.build_id " .
           " GROUP BY B.id, B.name, E2.status ORDER BY B.id ";

    $byBuild = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $bid = $row['build_id'];
      if (!isset($byBuild[$bid])) {
        $byBuild[$bid] = array('build_id' => intval($bid),
                               'name' => $row['name'], 'linked' => $linked,
                               'p' => 0, 'f' => 0, 'b' => 0, 'other' => 0);
      }
      $key = in_array($row['status'], array('p','f','b')) ? $row['status'] : 'other';
      $byBuild[$bid][$key] += intval($row['qty']);
    }

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'items' => array_values($byBuild))));
    return $response;
  }

  /**
   * true when $s is a valid calendar date in strict YYYY-MM-DD form.
   */
  private function isIsoDate($s)
  {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', (string)$s, $m)) {
      return false;
    }
    return checkdate(intval($m[2]), intval($m[3]), intval($m[1]));
  }

  /**
   * GET /testplans/{id}/milestones
   * Every milestone of the plan plus, on each one, the plan-wide
   * progress as of now: how many linked cases have a latest result
   * (executed %) and how many passed (pass %). Columns a/b/c are the
   * per-priority (high/medium/low) % targets carried by the milestone.
   * Progress is plan-wide (no per-priority breakdown) and reuses the
   * same latest-per-version loose scan as getPlanSummary.
   */
  public function getPlanMilestones(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);

    $sql = " SELECT COUNT(*) AS qty FROM {$this->tables['testplan_tcversions']} " .
           " WHERE testplan_id = {$safeID} ";
    $linked = intval($this->db->fetchFirstRowSingleColumn($sql, 'qty'));

    // latest execution per test case version -> executed & passed totals
    $sql = " SELECT E2.status, COUNT(*) AS qty FROM " .
           " (SELECT MAX(id) AS mid FROM {$this->tables['executions']} " .
           "   WHERE testplan_id = {$safeID} GROUP BY tcversion_id) M " .
           " JOIN {$this->tables['executions']} E2 ON E2.id = M.mid " .
           " GROUP BY E2.status ";
    $executed = 0;
    $passed = 0;
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $executed += intval($row['qty']);
      if ($row['status'] == 'p') {
        $passed += intval($row['qty']);
      }
    }
    $executedPct = $linked > 0 ? round($executed * 100 / $linked, 1) : 0;
    $passPct = $linked > 0 ? round($passed * 100 / $linked, 1) : 0;

    $sql = " SELECT id, name, target_date, start_date, a, b, c " .
           " FROM {$this->tables['milestones']} " .
           " WHERE testplan_id = {$safeID} ORDER BY target_date, name ";
    $items = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $items[] = array(
        'id' => intval($row['id']),
        'name' => $row['name'],
        'target_date' => $row['target_date'],
        'start_date' => $row['start_date'],
        'a' => intval($row['a']),
        'b' => intval($row['b']),
        'c' => intval($row['c']),
        'linked' => $linked,
        'executed' => $executed,
        'passed' => $passed,
        'executedPct' => $executedPct,
        'passPct' => $passPct);
    }

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'planID' => $safeID,
            'linked' => $linked, 'executed' => $executed, 'passed' => $passed,
            'executedPct' => $executedPct, 'passPct' => $passPct,
            'items' => $items)));
    return $response;
  }

  /**
   * POST /milestones
   *   {testplanID, name, target_date (YYYY-MM-DD),
   *    start_date?, A?, B?, C?}
   * A/B/C are the high/medium/low priority % targets (default 100),
   * stored in the a/b/c columns. Missing name or target_date -> 400;
   * an unknown test plan -> 404.
   */
  public function createMilestone(Request $request, Response $response, $args)
  {
    $op = array('status' => 'ok', 'message' => 'ok');
    $item = json_decode($request->getBody());

    // required fields (400)
    $name = ($item != null && isset($item->name)) ? trim((string)$item->name) : '';
    $target = ($item != null && isset($item->target_date))
              ? trim((string)$item->target_date) : '';
    if ($name === '' || !$this->isIsoDate($target)) {
      $op = array('status' => 'error',
                  'message' => 'name and target_date (YYYY-MM-DD) are required');
      $response->getBody()->write(json_encode($op));
      return $response->withStatus(400);
    }

    // test plan must exist (404)
    $tplanID = isset($item->testplanID) ? intval($item->testplanID) : 0;
    $tplan = $tplanID > 0 ? $this->tplanMgr->get_by_id($tplanID) : null;
    if (null == $tplan) {
      $op = array('status' => 'error', 'message' => 'Test plan does not exist');
      $response->getBody()->write(json_encode($op));
      return $response->withStatus(404);
    }

    try {
      $start = (isset($item->start_date) &&
                $this->isIsoDate(trim((string)$item->start_date)))
               ? trim((string)$item->start_date) : null;
      // percentage targets, clamped to 0..100, default 100
      $a = isset($item->A) ? max(0, min(100, intval($item->A))) : 100;
      $b = isset($item->B) ? max(0, min(100, intval($item->B))) : 100;
      $c = isset($item->C) ? max(0, min(100, intval($item->C))) : 100;

      $cols = "testplan_id, name, target_date, a, b, c";
      $vals = intval($tplanID) . "," .
              " '" . $this->db->prepare_string($name) . "'," .
              " '" . $this->db->prepare_string($target) . "'," .
              " {$a}, {$b}, {$c}";
      if ($start !== null) {
        $cols .= ", start_date";
        $vals .= ", '" . $this->db->prepare_string($start) . "'";
      }
      $sql = " INSERT INTO {$this->tables['milestones']} ({$cols}) " .
             " VALUES ({$vals}) ";
      $this->db->exec_query($sql);
      $op['id'] = intval($this->db->insert_id($this->tables['milestones']));
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * DELETE /milestones/{id}
   * Remove a milestone unconditionally.
   */
  public function deleteMilestone(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $op = array('status' => 'ok', 'message' => 'ok');
    try {
      $sql = " DELETE FROM {$this->tables['milestones']} WHERE id = {$safeID} ";
      $this->db->exec_query($sql);
      $op['id'] = $safeID;
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * GET /testplans/{id}/byKeyword
   * Per keyword (linked to any plan case through testcase_keywords):
   * how many plan-linked cases carry it, and the latest-execution
   * verdict counts p/f/b/n across those cases. n = linked cases with
   * no latest result. The verdict side reuses the latest-per-version
   * loose scan; keyword association is via the case node (parent of
   * the version), so it holds regardless of which version the plan
   * links. Keywords with no linked plan case are omitted.
   */
  public function getPlanByKeyword(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);

    // linked plan cases carrying each keyword
    $sql = " SELECT K.id AS keyword_id, K.keyword, " .
           "        COUNT(DISTINCT T.tcversion_id) AS linked " .
           " FROM {$this->tables['testplan_tcversions']} T " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = T.tcversion_id " .
           " JOIN {$this->tables['testcase_keywords']} TK ON TK.testcase_id = NHTCV.parent_id " .
           " JOIN {$this->tables['keywords']} K ON K.id = TK.keyword_id " .
           " WHERE T.testplan_id = {$safeID} " .
           " GROUP BY K.id, K.keyword ORDER BY K.keyword ";
    $items = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $kid = intval($row['keyword_id']);
      $items[$kid] = array('keyword_id' => $kid,
                           'keyword' => $row['keyword'],
                           'linked' => intval($row['linked']),
                           'p' => 0, 'f' => 0, 'b' => 0, 'n' => 0,
                           'executed' => 0);
    }

    // latest execution per version, rolled up to keyword verdict counts
    $sql = " SELECT K.id AS keyword_id, E2.status, " .
           "        COUNT(DISTINCT E2.tcversion_id) AS qty FROM " .
           " (SELECT MAX(id) AS mid FROM {$this->tables['executions']} " .
           "   WHERE testplan_id = {$safeID} GROUP BY tcversion_id) M " .
           " JOIN {$this->tables['executions']} E2 ON E2.id = M.mid " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = E2.tcversion_id " .
           " JOIN {$this->tables['testcase_keywords']} TK ON TK.testcase_id = NHTCV.parent_id " .
           " JOIN {$this->tables['keywords']} K ON K.id = TK.keyword_id " .
           " GROUP BY K.id, E2.status ";
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $kid = intval($row['keyword_id']);
      if (!isset($items[$kid])) {
        continue;
      }
      $qty = intval($row['qty']);
      $items[$kid]['executed'] += $qty;
      if (in_array($row['status'], array('p', 'f', 'b'))) {
        $items[$kid][$row['status']] += $qty;
      }
    }

    foreach ($items as &$kw) {
      $kw['n'] = max(0, $kw['linked'] - $kw['executed']);
      unset($kw['executed']);
    }
    unset($kw);

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'planID' => $safeID,
            'items' => array_values($items))));
    return $response;
  }

  /**
   * GET /testplans/{id}/matrixBySuite
   * Whole-plan overview on one screen: one row per test suite,
   * one column per build, cell = latest-status counts (p/f/b) plus
   * not-run. This is the at-a-glance companion of the paginated
   * case-level matrix.
   */
  public function getPlanMatrixBySuite(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);

    $sql = " SELECT id, name FROM {$this->tables['builds']} " .
           " WHERE testplan_id = {$safeID} ORDER BY id ";
    $builds = (array)$this->db->get_recordset($sql);

    // linked case count per direct parent suite
    $sql = " SELECT S.id AS suite_id, S.name AS suite_name, COUNT(*) AS linked " .
           " FROM {$this->tables['testplan_tcversions']} T " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = T.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " JOIN {$this->tables['nodes_hierarchy']} S ON S.id = NHTC.parent_id " .
           " WHERE T.testplan_id = {$safeID} " .
           " GROUP BY S.id, S.name ORDER BY S.name ";
    $suites = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $suites[$row['suite_id']] =
        array('suite_id' => intval($row['suite_id']),
              'name' => $row['suite_name'],
              'linked' => intval($row['linked']),
              'cells' => array());
    }

    // latest status per (version, build) rolled up to suite counters
    $sql = " SELECT S.id AS suite_id, E2.build_id, E2.status, COUNT(*) AS qty " .
           " FROM (SELECT MAX(id) AS mid FROM {$this->tables['executions']} " .
           "        WHERE testplan_id = {$safeID} " .
           "        GROUP BY tcversion_id, build_id) M " .
           " JOIN {$this->tables['executions']} E2 ON E2.id = M.mid " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = E2.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " JOIN {$this->tables['nodes_hierarchy']} S ON S.id = NHTC.parent_id " .
           " GROUP BY S.id, E2.build_id, E2.status ";
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $sid = $row['suite_id'];
      if (!isset($suites[$sid])) {
        continue;
      }
      $bid = $row['build_id'];
      if (!isset($suites[$sid]['cells'][$bid])) {
        $suites[$sid]['cells'][$bid] = array('p' => 0, 'f' => 0, 'b' => 0);
      }
      $statusKey = in_array($row['status'], array('p','f','b')) ? $row['status'] : 'b';
      $suites[$sid]['cells'][$bid][$statusKey] += intval($row['qty']);
    }

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'builds' => $builds,
            'items' => array_values($suites))));
    return $response;
  }

  /**
   * GET /testplans/{id}/buildsById
   * Builds of a plan, addressed by plan DBID (the legacy route wants
   * the plan API key which the SPA does not have).
   */
  public function getPlanBuildsById(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $sql = " SELECT id, name, notes, active, is_open, creation_ts " .
           " FROM {$this->tables['builds']} " .
           " WHERE testplan_id = {$safeID} ORDER BY id DESC ";
    $items = (array)$this->db->get_recordset($sql);
    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'items' => $items)));
    return $response;
  }

  /**
   * GET /testprojects/{id}/reqspecs
   * Requirement specification tree of a project (specs can nest),
   * with the number of requirements directly inside each spec.
   */
  public function getProjectReqSpecs(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $sql = " WITH RECURSIVE rst AS " .
           " (SELECT id, parent_id, name, node_order " .
           "    FROM {$this->tables['nodes_hierarchy']} " .
           "   WHERE parent_id = {$safeID} AND node_type_id = 6 " .
           "  UNION ALL " .
           "  SELECT nh.id, nh.parent_id, nh.name, nh.node_order " .
           "    FROM {$this->tables['nodes_hierarchy']} nh " .
           "    JOIN rst ON nh.parent_id = rst.id " .
           "   WHERE nh.node_type_id = 6) " .
           " SELECT rst.id, rst.parent_id, rst.name, rst.node_order, RS.doc_id " .
           " FROM rst JOIN {$this->tables['req_specs']} RS ON RS.id = rst.id " .
           " ORDER BY rst.parent_id, rst.node_order, rst.id ";
    $specs = (array)$this->db->get_recordset($sql);

    $sql = " SELECT NHR.parent_id, COUNT(*) AS qty " .
           " FROM {$this->tables['nodes_hierarchy']} NHR " .
           " WHERE NHR.node_type_id = 7 GROUP BY NHR.parent_id ";
    $counts = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $counts[$row['parent_id']] = intval($row['qty']);
    }

    foreach ($specs as &$spec) {
      $spec['id'] = intval($spec['id']);
      $spec['parent_id'] = intval($spec['parent_id']);
      $spec['reqCount'] = isset($counts[$spec['id']]) ? $counts[$spec['id']] : 0;
    }
    unset($spec);

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'projectID' => $safeID, 'items' => $specs)));
    return $response;
  }

  /**
   * POST /reqspecs  {testProjectID, parentID?, docID, title, scope?}
   * Create a requirement specification (type 6 node + req_specs row
   * + first req_specs_revisions row, all via requirement_spec_mgr).
   * Without parentID the spec lands at project root.
   */
  public function createReqSpec(Request $request, Response $response, $args)
  {
    $op = array('status' => 'ok', 'message' => 'ok');
    try {
      $item = json_decode($request->getBody());
      if (null == $item || !isset($item->testProjectID) ||
          !isset($item->docID) || !isset($item->title)) {
        throw new Exception('Body must carry testProjectID, docID, title');
      }
      $tprojectID = intval($item->testProjectID);
      $parentID = isset($item->parentID) && intval($item->parentID) > 0 ?
                  intval($item->parentID) : $tprojectID;
      $scope = isset($item->scope) ? strval($item->scope) : '';

      // type is passed explicitly: the signature default
      // (TL_REQ_SPEC_TYPE_FEATURE) is an undefined constant — every
      // legacy caller sends the form value. '2' = feature.
      $ret = $this->reqSpecMgr->create(
        $tprojectID, $parentID, trim($item->docID), trim($item->title),
        $scope, 0, $this->userID, '2');
      if (intval($ret['status_ok']) == 0) {
        throw new Exception($ret['msg']);
      }
      $op['id'] = intval($ret['id']);
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * GET /reqspecs/{id}/requirements
   * Requirements directly inside a spec: doc id, title, the status
   * of the LATEST version, and how many test cases cover each one.
   */
  public function getReqSpecRequirements(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $sql = " SELECT NHR.id, NHR.name, NHR.node_order, R.req_doc_id, " .
           "        RV.version, RV.status, RV.type, RV.expected_coverage " .
           " FROM {$this->tables['nodes_hierarchy']} NHR " .
           " JOIN {$this->tables['requirements']} R ON R.id = NHR.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHRV ON NHRV.parent_id = NHR.id " .
           " JOIN {$this->tables['req_versions']} RV ON RV.id = NHRV.id " .
           " WHERE NHR.parent_id = {$safeID} AND NHR.node_type_id = 7 " .
           " AND RV.version = " .
           "     (SELECT MAX(RV2.version) FROM {$this->tables['nodes_hierarchy']} NHRV2 " .
           "       JOIN {$this->tables['req_versions']} RV2 ON RV2.id = NHRV2.id " .
           "      WHERE NHRV2.parent_id = NHR.id) " .
           " ORDER BY NHR.node_order, NHR.id ";
    $items = (array)$this->db->get_recordset($sql);

    $sql = " SELECT RC.req_id, COUNT(DISTINCT RC.testcase_id) AS qty " .
           " FROM {$this->tables['req_coverage']} RC " .
           " JOIN {$this->tables['nodes_hierarchy']} NHR ON NHR.id = RC.req_id " .
           " WHERE NHR.parent_id = {$safeID} GROUP BY RC.req_id ";
    $counts = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $counts[$row['req_id']] = intval($row['qty']);
    }

    foreach ($items as &$req) {
      $req['id'] = intval($req['id']);
      $req['coverageCount'] = isset($counts[$req['id']]) ? $counts[$req['id']] : 0;
    }
    unset($req);

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'reqSpecID' => $safeID, 'items' => $items)));
    return $response;
  }

  /**
   * POST /requirements  {reqSpecID, docID, title, scope?}
   * Create a requirement and its first version (type 7 node +
   * requirements row + type 8 version node + req_versions row,
   * all via requirement_mgr).
   */
  public function createRequirement(Request $request, Response $response, $args)
  {
    $op = array('status' => 'ok', 'message' => 'ok');
    try {
      $item = json_decode($request->getBody());
      if (null == $item || !isset($item->reqSpecID) ||
          !isset($item->docID) || !isset($item->title)) {
        throw new Exception('Body must carry reqSpecID, docID, title');
      }
      $srsID = intval($item->reqSpecID);
      $scope = isset($item->scope) ? strval($item->scope) : '';

      $ret = $this->reqMgr->create(
        $srsID, trim($item->docID), trim($item->title), $scope, $this->userID);
      if (intval($ret['status_ok']) == 0) {
        throw new Exception($ret['msg']);
      }
      $op['id'] = intval($ret['id']);
      $op['versionID'] = intval($ret['version_id']);
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * GET /requirements/{id}/detail
   * Latest version of one requirement plus the test cases that
   * cover it (req_coverage).
   */
  public function getRequirementDetail(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);

    $sql = " SELECT NHR.name, NHR.parent_id AS srs_id, R.req_doc_id, " .
           "        RV.id AS req_version_id, RV.version, RV.scope, RV.status, " .
           "        RV.type, RV.expected_coverage, RV.creation_ts, RV.modification_ts " .
           " FROM {$this->tables['nodes_hierarchy']} NHR " .
           " JOIN {$this->tables['requirements']} R ON R.id = NHR.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHRV ON NHRV.parent_id = NHR.id " .
           " JOIN {$this->tables['req_versions']} RV ON RV.id = NHRV.id " .
           " WHERE NHR.id = {$safeID} " .
           " ORDER BY RV.version DESC LIMIT 1 ";
    $item = $this->db->get_recordset($sql);
    if (is_null($item)) {
      $response->getBody()->write(json_encode(
        array('status' => 'error', 'message' => 'Requirement does not exist')));
      return $response->withStatus(404);
    }
    $item = current($item);
    $item['id'] = $safeID;

    $sql = " SELECT DISTINCT RC.testcase_id AS tcase_id, NHTC.name, " .
           "        (SELECT MAX(TCV.tc_external_id) " .
           "           FROM {$this->tables['nodes_hierarchy']} NHTCV " .
           "           JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id " .
           "          WHERE NHTCV.parent_id = RC.testcase_id) AS tc_external_id " .
           " FROM {$this->tables['req_coverage']} RC " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = RC.testcase_id " .
           " WHERE RC.req_id = {$safeID} " .
           " ORDER BY NHTC.name ";
    $item['coverage'] = (array)$this->db->get_recordset($sql);

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'item' => $item)));
    return $response;
  }

  /**
   * POST /requirements/{id}/coverage  {tcaseIDs: [...]}
   * Cover the requirement with test cases: link its LATEST version
   * to the LATEST version of each given case. Already-covered cases
   * are skipped, so the call is idempotent.
   */
  public function addRequirementCoverage(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $op = array('status' => 'ok', 'message' => 'ok', 'linked' => 0, 'skipped' => 0);

    try {
      $item = json_decode($request->getBody());
      if (null == $item || !isset($item->tcaseIDs) || !is_array($item->tcaseIDs)) {
        throw new Exception('Body must carry tcaseIDs array');
      }

      // latest version node of this requirement
      $sql = " SELECT RV.id FROM {$this->tables['nodes_hierarchy']} NHRV " .
             " JOIN {$this->tables['req_versions']} RV ON RV.id = NHRV.id " .
             " WHERE NHRV.parent_id = {$safeID} ORDER BY RV.version DESC LIMIT 1 ";
      $reqVersionID = intval($this->db->fetchFirstRowSingleColumn($sql, 'id'));
      if ($reqVersionID <= 0) {
        throw new Exception('Requirement does not exist');
      }

      foreach ($item->tcaseIDs as $tcaseID) {
        $tcaseID = intval($tcaseID);
        $sql = " SELECT TCV.id FROM {$this->tables['nodes_hierarchy']} NHTCV " .
               " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id " .
               " WHERE NHTCV.parent_id = {$tcaseID} " .
               " ORDER BY TCV.version DESC LIMIT 1 ";
        $tcvID = intval($this->db->fetchFirstRowSingleColumn($sql, 'id'));
        if ($tcvID <= 0) {
          $op['skipped']++;
          continue;
        }

        $sql = " SELECT COUNT(*) AS qty FROM {$this->tables['req_coverage']} " .
               " WHERE req_id = {$safeID} AND testcase_id = {$tcaseID} ";
        if (intval($this->db->fetchFirstRowSingleColumn($sql, 'qty')) > 0) {
          $op['skipped']++;
          continue;
        }

        $sql = " INSERT INTO {$this->tables['req_coverage']} " .
               " (req_id, testcase_id, req_version_id, tcversion_id, author_id, creation_ts) " .
               " VALUES ({$safeID}, {$tcaseID}, {$reqVersionID}, {$tcvID}, " .
               intval($this->userID) . ", " . $this->db->db_now() . ")";
        $this->db->exec_query($sql);
        $op['linked']++;
      }
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * DELETE /requirements/{id}/coverage/{tcaseID}
   * Unlink a test case from a requirement — every coverage row of
   * the pair goes away, whatever req/tc version it pointed at.
   */
  public function deleteRequirementCoverage(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $tcaseID = intval($args['tcaseID']);

    $sql = " DELETE FROM {$this->tables['req_coverage']} " .
           " WHERE req_id = {$safeID} AND testcase_id = {$tcaseID} ";
    $this->db->exec_query($sql);
    $removed = intval($this->db->affected_rows());

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'removed' => $removed)));
    return $response;
  }

  /**
   * GET /testplans/{id}/reqCoverage
   * Requirements coverage report for a plan: for each requirement
   * covering cases linked to the plan, the latest-execution verdict
   * counts (p/f/b/n) of those cases. Latest execution per version
   * uses the same MAX(id) loose-scan pattern as getPlanSummary, so
   * cost stays plan-scoped.
   */
  public function getPlanReqCoverage(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);

    $sql = " SELECT RC.req_id, R.req_doc_id, NHR.name, " .
           "        PL.tcase_id, LE.status " .
           " FROM (SELECT T.tcversion_id, NHTCV.parent_id AS tcase_id " .
           "         FROM {$this->tables['testplan_tcversions']} T " .
           "         JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
           "           ON NHTCV.id = T.tcversion_id " .
           "        WHERE T.testplan_id = {$safeID}) PL " .
           " JOIN (SELECT DISTINCT req_id, testcase_id " .
           "         FROM {$this->tables['req_coverage']}) RC " .
           "   ON RC.testcase_id = PL.tcase_id " .
           " JOIN {$this->tables['requirements']} R ON R.id = RC.req_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHR ON NHR.id = RC.req_id " .
           " LEFT JOIN (SELECT E2.tcversion_id, E2.status FROM " .
           "             (SELECT MAX(id) AS mid FROM {$this->tables['executions']} " .
           "               WHERE testplan_id = {$safeID} GROUP BY tcversion_id) M " .
           "             JOIN {$this->tables['executions']} E2 ON E2.id = M.mid) LE " .
           "   ON LE.tcversion_id = PL.tcversion_id " .
           " ORDER BY R.req_doc_id, RC.req_id ";

    $reqs = array();
    $rs = $this->db->exec_query($sql);
    while ($rs && ($row = $this->db->fetch_array($rs))) {
      $rid = $row['req_id'];
      if (!isset($reqs[$rid])) {
        $reqs[$rid] = array('req_id' => intval($rid),
                            'req_doc_id' => $row['req_doc_id'],
                            'name' => $row['name'],
                            'inPlan' => 0, 'covered' => 0,
                            'p' => 0, 'f' => 0, 'b' => 0, 'n' => 0);
      }
      $reqs[$rid]['inPlan']++;
      $key = in_array($row['status'], array('p','f','b')) ? $row['status'] : 'n';
      $reqs[$rid][$key]++;
    }

    if (count($reqs) > 0) {
      // total covered cases per requirement (plan-linked or not)
      $idSet = implode(',', array_map('intval', array_keys($reqs)));
      $sql = " SELECT req_id, COUNT(DISTINCT testcase_id) AS qty " .
             " FROM {$this->tables['req_coverage']} " .
             " WHERE req_id IN ({$idSet}) GROUP BY req_id ";
      foreach ((array)$this->db->get_recordset($sql) as $row) {
        $reqs[$row['req_id']]['covered'] = intval($row['qty']);
      }
    }

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'planID' => $safeID,
            'items' => array_values($reqs))));
    return $response;
  }

  // ==================================================================
  // Admin module — users, keywords, platforms, custom fields.
  // Replaces the common operations of the legacy usermanagement /
  // keywords / platforms / cfields frame pages.
  // ==================================================================

  /** map a tlUser error code to a human message for the API client */
  private function userErrorMsg($code)
  {
    switch (intval($code)) {
      case tlUser::E_LOGINLENGTH:       return 'Login is empty or too long';
      case tlUser::E_EMAILLENGTH:       return 'Email is too long';
      case tlUser::E_NOTALLOWED:        return 'Login contains characters that are not allowed';
      case tlUser::E_FIRSTNAMELENGTH:   return 'First name is empty or too long';
      case tlUser::E_LASTNAMELENGTH:    return 'Last name is empty or too long';
      case tlUser::E_PWDEMPTY:          return 'Password must not be empty';
      case tlUser::E_LOGINALREADYEXISTS:return 'A user with this login already exists';
      case tlUser::E_EMAILFORMAT:       return 'Email address is not valid';
      case tlUser::E_DBERROR:           return 'Database error while saving the user';
      default:                          return 'Could not create user (code ' . intval($code) . ')';
    }
  }

  /**
   * POST /users  {login, password, firstName, lastName, email, roleID?}
   * Create a user through the tlUser class (same writeToDB +
   * setPassword pattern the legacy usermanagement uses). When roleID
   * is omitted the configured default global role is used. The new
   * user is active and uses internal (DB) password management, so it
   * can immediately authenticate through POST /auth/login.
   */
  public function createUser(Request $request, Response $response, $args)
  {
    $op = array('status' => 'ok', 'message' => 'ok');
    try {
      $item = json_decode($request->getBody());
      if (null == $item || !isset($item->login) || !isset($item->password) ||
          !isset($item->firstName) || !isset($item->lastName) ||
          !isset($item->email)) {
        throw new Exception('Body must carry login, password, firstName, lastName, email');
      }

      $user = new tlUser();
      $user->login = trim($item->login);
      $user->firstName = trim($item->firstName);
      $user->lastName = trim($item->lastName);
      $user->emailAddress = trim($item->email);
      $user->locale = 'en_GB';
      $user->isActive = 1;
      $user->authentication = '';   // '' => configured default method (DB)
      $user->globalRoleID = isset($item->roleID) && intval($item->roleID) > 0 ?
                            intval($item->roleID) : config_get('default_roleid');

      $rc = $user->setPassword((string)$item->password);
      if ($rc < tl::OK) {
        throw new Exception($this->userErrorMsg($rc));
      }

      $rc = $user->writeToDB($this->db);
      if ($rc < tl::OK) {
        throw new Exception($this->userErrorMsg($rc));
      }
      $op['id'] = intval($user->dbID);
      $op['login'] = $user->login;
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * PUT /users/{id}  {active}
   * Activate / deactivate a user. Deactivated users can no longer
   * authenticate (POST /auth/login checks isActive).
   */
  public function updateUser(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $op = array('status' => 'ok', 'message' => 'ok');
    try {
      $item = json_decode($request->getBody());
      if (null == $item || !isset($item->active)) {
        throw new Exception('Body must carry active');
      }
      $active = intval($item->active) ? 1 : 0;
      $sql = " UPDATE {$this->tables['users']} SET active = {$active} " .
             " WHERE id = {$safeID} ";
      $this->db->exec_query($sql);
      $op['id'] = $safeID;
      $op['active'] = $active;
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * GET /testprojects/{id}/keywords
   * Keywords defined in a project plus how many test cases use each.
   */
  public function getProjectKeywords(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $sql = " SELECT id, keyword, notes FROM {$this->tables['keywords']} " .
           " WHERE testproject_id = {$safeID} ORDER BY keyword ";
    $items = (array)$this->db->get_recordset($sql);

    $sql = " SELECT keyword_id, COUNT(*) AS qty " .
           " FROM {$this->tables['testcase_keywords']} GROUP BY keyword_id ";
    $counts = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $counts[$row['keyword_id']] = intval($row['qty']);
    }

    foreach ($items as &$kw) {
      $kw['id'] = intval($kw['id']);
      $kw['linkedCount'] = isset($counts[$kw['id']]) ? $counts[$kw['id']] : 0;
    }
    unset($kw);

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'projectID' => $safeID, 'items' => $items)));
    return $response;
  }

  /**
   * DELETE /keywords/{id}
   * Remove a keyword. Mirrors the keyword-manager behaviour: the
   * tlKeyword deleteFromDB (invoked via deleteKeyword) also removes
   * the testcase_keywords and object_keywords link rows. Deletion is
   * unconditional here (checkBeforeDelete disabled), matching an
   * admin "delete keyword" action.
   */
  public function deleteKeyword(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $op = array('status' => 'ok', 'message' => 'ok');
    try {
      $rc = $this->tprojectMgr->deleteKeyword($safeID,
              array('checkBeforeDelete' => false));
      if ($rc < tl::OK) {
        throw new Exception('Could not delete keyword');
      }
      $op['id'] = $safeID;
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * GET /testprojects/{id}/platforms
   * Every platform of the project (regardless of enable flags) with
   * its design/execution/open flags and how many plans link it.
   */
  public function getProjectPlatforms(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);
    $platMgr = new tlPlatform($this->db, $safeID);
    // null on the enable filters => do not filter, return them all
    $rs = $platMgr->getAll(array('include_linked_count' => true,
                                 'enable_on_design' => null,
                                 'enable_on_execution' => null,
                                 'is_open' => null));
    $items = array();
    foreach ((array)$rs as $row) {
      $items[] = array(
        'id' => intval($row['id']),
        'name' => $row['name'],
        'notes' => $row['notes'],
        'enable_on_design' => intval($row['enable_on_design']),
        'enable_on_execution' => intval($row['enable_on_execution']),
        'is_open' => intval($row['is_open']),
        'linked_count' => isset($row['linked_count']) ? intval($row['linked_count']) : 0);
    }

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'projectID' => $safeID, 'items' => $items)));
    return $response;
  }

  /**
   * POST /platforms  {testProjectID, name, notes?}
   * Create a platform in a project (enabled on design and execution).
   * A duplicate name in the same project is rejected with 400.
   */
  public function createPlatform(Request $request, Response $response, $args)
  {
    $op = array('status' => 'ok', 'message' => 'ok');
    try {
      $item = json_decode($request->getBody());
      if (null == $item || !isset($item->testProjectID) ||
          !isset($item->name) || trim((string)$item->name) === '') {
        throw new Exception('Body must carry testProjectID and a non-empty name');
      }
      $platMgr = new tlPlatform($this->db, intval($item->testProjectID));

      $plat = new stdClass();
      $plat->name = trim((string)$item->name);
      $plat->notes = isset($item->notes) ? strval($item->notes) : '';
      $plat->enable_on_design = 1;
      $plat->enable_on_execution = 1;

      $ret = $platMgr->create($plat);
      if ($ret['status'] == tlPlatform::E_NAMEALREADYEXISTS) {
        throw new Exception('A platform with this name already exists in the project');
      }
      if ($ret['status'] != tl::OK) {
        throw new Exception('Could not create platform');
      }
      $op['id'] = intval($ret['id']);
    } catch (Exception $e) {
      $op = array('status' => 'error',
                  'message' => $this->msgFromException($e));
      $response = $response->withStatus(400);
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }

  /**
   * GET /testprojects/{id}/customfields
   * Custom fields linked to a project (custom_fields JOIN
   * cfield_testprojects). Type ints are mapped to their verbose name
   * via cfield_mgr::$custom_field_types, and the node types the field
   * applies to (cfield_node_types) are resolved to their descriptions.
   * Read-only — custom field creation stays in the legacy UI.
   */
  public function getProjectCustomFields(Request $request, Response $response, $args)
  {
    $safeID = intval($args['id']);

    $sql = " SELECT CF.id, CF.name, CF.label, CF.type, " .
           "        CF.enable_on_design, CF.enable_on_execution, " .
           "        CF.enable_on_testplan_design, " .
           "        CFTP.active, CFTP.display_order " .
           " FROM {$this->tables['custom_fields']} CF " .
           " JOIN {$this->tables['cfield_testprojects']} CFTP " .
           "   ON CFTP.field_id = CF.id " .
           " WHERE CFTP.testproject_id = {$safeID} " .
           " ORDER BY CFTP.display_order, CF.name ";
    $rows = (array)$this->db->get_recordset($sql);

    // node types each field applies to
    $sql = " SELECT CNT.field_id, NT.description " .
           " FROM {$this->tables['cfield_node_types']} CNT " .
           " JOIN {$this->tables['node_types']} NT ON NT.id = CNT.node_type_id " .
           " ORDER BY CNT.field_id, NT.id ";
    $appliesTo = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $appliesTo[$row['field_id']][] = $row['description'];
    }

    $cfTypes = $this->cfieldMgr->custom_field_types;

    $items = array();
    foreach ($rows as $row) {
      $fid = intval($row['id']);
      $typeInt = intval($row['type']);
      $items[] = array(
        'id' => $fid,
        'name' => $row['name'],
        'label' => $row['label'],
        'type' => isset($cfTypes[$typeInt]) ? $cfTypes[$typeInt] : ('type ' . $typeInt),
        'appliesTo' => isset($appliesTo[$fid]) ? implode(', ', $appliesTo[$fid]) : '',
        'active' => intval($row['active']),
        'enable_on_design' => intval($row['enable_on_design']),
        'enable_on_execution' => intval($row['enable_on_execution']));
    }

    $response->getBody()->write(json_encode(
      array('status' => 'ok', 'projectID' => $safeID, 'items' => $items)));
    return $response;
  }

  // ==================================================================
  // Documents & TestLink-XML import/export
  // (SPA replacement for printDocument.php / tcExport.php / tcImport.php)
  // ==================================================================

  /** HTML-escape helper for the printable documents */
  private static function docEsc($s)
  {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  }

  /** execution status code -> human label for the printable report */
  private static function docVerdictLabel($code)
  {
    switch ($code) {
      case 'p': return 'Passed';
      case 'f': return 'Failed';
      case 'b': return 'Blocked';
      case null:
      case '':  return 'Not run';
      default:  return (string)$code;
    }
  }

  /**
   * Shared shell of the printable documents: self-contained HTML,
   * inline CSS only (no external assets), print-friendly.
   */
  private function docHtmlOpen($title, $subtitle)
  {
    $css =
      'body{font-family:-apple-system,"Segoe UI",Helvetica,Arial,sans-serif;' .
      'margin:32px auto;max-width:900px;padding:0 16px;color:#1a1a1a;line-height:1.45}' .
      'h1{font-size:22px;margin:0 0 2px;border-bottom:2px solid #1a1a1a;padding-bottom:6px}' .
      'h2{font-size:16px;margin:26px 0 6px;border-bottom:1px solid #999;padding-bottom:3px}' .
      'h3{font-size:14px;margin:18px 0 4px}' .
      '.sub{color:#555;font-size:12px;margin:0 0 18px}' .
      '.meta{color:#555;font-size:12px;margin:2px 0}' .
      '.block{margin:4px 0 10px}' .
      '.blocklabel{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#777;margin:8px 0 2px}' .
      'table{border-collapse:collapse;width:100%;margin:6px 0 14px;font-size:13px}' .
      'th,td{border:1px solid #bbb;padding:4px 8px;text-align:left;vertical-align:top}' .
      'th{background:#f0f0f0;font-size:12px}' .
      'td.num,th.num{text-align:right;font-variant-numeric:tabular-nums}' .
      '.stepno{width:36px;text-align:right}' .
      '.warn{background:#fff3cd;border:1px solid #d9c069;padding:8px 12px;margin:14px 0;font-size:13px}' .
      '.v-p{color:#166534;font-weight:600}.v-f{color:#b91c1c;font-weight:600}' .
      '.v-b{color:#92400e;font-weight:600}.v-n{color:#666}' .
      '@media print{body{margin:0 auto}}';

    return '<!DOCTYPE html><html><head><meta charset="utf-8">' .
           '<title>' . self::docEsc($title) . '</title>' .
           '<style>' . $css . '</style></head><body>' .
           '<h1>' . self::docEsc($title) . '</h1>' .
           '<p class="sub">' . self::docEsc($subtitle) . '</p>';
  }

  /**
   * GET /testprojects/{id}/document?type=spec[&suiteID=]
   * Self-contained printable HTML of the test specification:
   * suite hierarchy with, per test case, external id, title, summary,
   * preconditions and steps table. Optional suiteID narrows the scope
   * to one suite subtree; the whole-project variant is capped at
   * DOC_MAX_CASES cases with an explicit truncation note.
   */
  const DOC_MAX_CASES = 2000;

  public function getProjectDocument(Request $request, Response $response, $args)
  {
    $projectID = intval($args['id']);
    $qs = $request->getQueryParams();
    $type = isset($qs['type']) ? trim($qs['type']) : 'spec';
    $suiteID = isset($qs['suiteID']) ? intval($qs['suiteID']) : 0;

    if ($type != 'spec') {
      $response->getBody()->write(json_encode(
        array('status' => 'error', 'message' => "Unsupported document type '{$type}'")));
      return $response->withStatus(400);
    }

    $sql = " SELECT name FROM {$this->tables['nodes_hierarchy']} " .
           " WHERE id = {$projectID} AND node_type_id = 1 ";
    $projectName = $this->db->fetchFirstRowSingleColumn($sql, 'name');
    if (is_null($projectName)) {
      $response->getBody()->write(json_encode(
        array('status' => 'error', 'message' => 'Test project does not exist')));
      return $response->withStatus(404);
    }

    $scopeName = null;
    if ($suiteID > 0) {
      $sql = " SELECT name FROM {$this->tables['nodes_hierarchy']} " .
             " WHERE id = {$suiteID} AND node_type_id = 2 ";
      $scopeName = $this->db->fetchFirstRowSingleColumn($sql, 'name');
      $rootOK = !is_null($scopeName) &&
        intval($this->tsuiteMgr->tree_manager->getTreeRoot($suiteID)) == $projectID;
      if (!$rootOK) {
        $response->getBody()->write(json_encode(
          array('status' => 'error',
                'message' => 'Test suite does not exist in this project')));
        return $response->withStatus(404);
      }
    }

    // suite subtree in scope (same recursive shape as getProjectSuites)
    $anchorID = $suiteID > 0 ? $suiteID : $projectID;
    $sql = " WITH RECURSIVE st AS " .
           " (SELECT id, parent_id, name, node_order " .
           "    FROM {$this->tables['nodes_hierarchy']} " .
           "   WHERE parent_id = {$anchorID} AND node_type_id = 2 " .
           "  UNION ALL " .
           "  SELECT nh.id, nh.parent_id, nh.name, nh.node_order " .
           "    FROM {$this->tables['nodes_hierarchy']} nh " .
           "    JOIN st ON nh.parent_id = st.id " .
           "   WHERE nh.node_type_id = 2) " .
           " SELECT * FROM st ";
    $suiteRows = (array)$this->db->get_recordset($sql);
    if ($suiteID > 0) {
      $suiteRows[] = array('id' => $suiteID, 'parent_id' => $anchorID,
                           'name' => $scopeName, 'node_order' => 0);
    }

    $childSuites = array();      // parent -> ordered child suites
    $suiteIDSet = array($anchorID);
    usort($suiteRows, function ($a, $b) {
      return (intval($a['node_order']) <=> intval($b['node_order']))
        ?: (intval($a['id']) <=> intval($b['id']));
    });
    foreach ($suiteRows as $row) {
      $suiteIDSet[] = intval($row['id']);
      if (intval($row['id']) != $suiteID) {
        $childSuites[intval($row['parent_id'])][] = $row;
      }
    }

    // latest-version data of every case in scope, bounded
    $inList = implode(',', array_unique($suiteIDSet));
    $cap = self::DOC_MAX_CASES;
    $sql = " SELECT NHTC.id AS tcase_id, NHTC.parent_id AS suite_id, " .
           "        NHTC.name, NHTC.node_order, " .
           "        TCV.id AS tcversion_id, TCV.version, TCV.tc_external_id, " .
           "        TCV.summary, TCV.preconditions, TCV.importance " .
           " FROM {$this->tables['nodes_hierarchy']} NHTC " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.parent_id = NHTC.id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id " .
           " WHERE NHTC.parent_id IN ({$inList}) AND NHTC.node_type_id = 3 " .
           "   AND TCV.version = (SELECT MAX(TCV2.version) " .
           "         FROM {$this->tables['nodes_hierarchy']} NH2 " .
           "         JOIN {$this->tables['tcversions']} TCV2 ON TCV2.id = NH2.id " .
           "        WHERE NH2.parent_id = NHTC.id) " .
           " ORDER BY NHTC.parent_id, NHTC.node_order, NHTC.id " .
           " LIMIT " . ($cap + 1);
    $caseRows = (array)$this->db->get_recordset($sql);
    $truncated = count($caseRows) > $cap;
    if ($truncated) {
      array_pop($caseRows);
    }

    // steps of those versions, one query
    $stepsByTCV = array();
    if (count($caseRows) > 0) {
      $tcvList = implode(',', array_map(function ($r) {
        return intval($r['tcversion_id']);
      }, $caseRows));
      $sql = " SELECT NH.parent_id AS tcversion_id, TCS.step_number, " .
             "        TCS.actions, TCS.expected_results " .
             " FROM {$this->tables['tcsteps']} TCS " .
             " JOIN {$this->tables['nodes_hierarchy']} NH ON NH.id = TCS.id " .
             " WHERE NH.parent_id IN ({$tcvList}) " .
             " ORDER BY NH.parent_id, TCS.step_number ";
      foreach ((array)$this->db->get_recordset($sql) as $st) {
        $stepsByTCV[$st['tcversion_id']][] = $st;
      }
    }

    $casesBySuite = array();
    foreach ($caseRows as $row) {
      $casesBySuite[intval($row['suite_id'])][] = $row;
    }

    // project prefix for the external case ids
    $sql = " SELECT prefix FROM {$this->tables['testprojects']} " .
           " WHERE id = {$projectID} ";
    $prefix = (string)$this->db->fetchFirstRowSingleColumn($sql, 'prefix');

    $renderCase = function ($tc) use ($prefix, $stepsByTCV) {
      $html = '<h3>' . self::docEsc($prefix . '-' . $tc['tc_external_id']) .
              ' · ' . self::docEsc($tc['name']) .
              ' <span class="meta">v' . intval($tc['version']) . '</span></h3>';
      if (trim((string)$tc['summary']) != '') {
        $html .= '<div class="blocklabel">Summary</div>' .
                 '<div class="block">' . $tc['summary'] . '</div>';
      }
      if (trim((string)$tc['preconditions']) != '') {
        $html .= '<div class="blocklabel">Preconditions</div>' .
                 '<div class="block">' . $tc['preconditions'] . '</div>';
      }
      $steps = isset($stepsByTCV[$tc['tcversion_id']]) ?
        $stepsByTCV[$tc['tcversion_id']] : array();
      if (count($steps) > 0) {
        $html .= '<table><tr><th class="stepno">#</th>' .
                 '<th>Actions</th><th>Expected results</th></tr>';
        foreach ($steps as $st) {
          $html .= '<tr><td class="stepno">' . intval($st['step_number']) . '</td>' .
                   '<td>' . $st['actions'] . '</td>' .
                   '<td>' . $st['expected_results'] . '</td></tr>';
        }
        $html .= '</table>';
      }
      return $html;
    };

    // depth-first walk with hierarchical numbering (1, 1.1, ...)
    $renderSuite = function ($sid, $number, $name) use (
      &$renderSuite, &$childSuites, &$casesBySuite, $renderCase) {
      $html = '';
      if ($name !== null) {
        $html .= '<h2>' . self::docEsc($number . ' ' . $name) . '</h2>';
      }
      foreach (isset($casesBySuite[$sid]) ? $casesBySuite[$sid] : array() as $tc) {
        $html .= $renderCase($tc);
      }
      $childNo = 0;
      foreach (isset($childSuites[$sid]) ? $childSuites[$sid] : array() as $child) {
        $childNo++;
        $childNumber = ($name === null ? '' : $number . '.') . $childNo;
        $html .= $renderSuite(intval($child['id']), $childNumber, $child['name']);
      }
      return $html;
    };

    $title = 'Test specification · ' . $projectName .
             ($scopeName !== null ? ' / ' . $scopeName : '');
    $subtitle = 'Generated ' . date('Y-m-d H:i') . ' · ' .
                count($caseRows) . ' test cases';
    $html = $this->docHtmlOpen($title, $subtitle);
    if ($truncated) {
      $html .= '<div class="warn">Output truncated at ' . $cap .
               ' test cases. Narrow the scope with a suite to get the full detail.</div>';
    }
    // the anchor level itself carries no heading (its name is in the
    // title); child suites get hierarchical numbers 1, 1.1, ...
    $html .= $renderSuite($anchorID, '', null);
    $html .= '</body></html>';

    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
  }

  /**
   * GET /testplans/{id}/document?type=report[&buildID=]
   * Printable HTML test report: plan header, per-suite verdict summary
   * (latest execution per case version — same shape as matrixBySuite),
   * then the per-case latest verdict list with tester/date/notes.
   */
  public function getPlanDocument(Request $request, Response $response, $args)
  {
    $planID = intval($args['id']);
    $qs = $request->getQueryParams();
    $type = isset($qs['type']) ? trim($qs['type']) : 'report';
    $buildID = isset($qs['buildID']) ? intval($qs['buildID']) : 0;

    if ($type != 'report') {
      $response->getBody()->write(json_encode(
        array('status' => 'error', 'message' => "Unsupported document type '{$type}'")));
      return $response->withStatus(400);
    }

    $plan = $this->tplanMgr->get_by_id($planID);
    if (is_null($plan) || !isset($plan['name'])) {
      $response->getBody()->write(json_encode(
        array('status' => 'error', 'message' => 'Test plan does not exist')));
      return $response->withStatus(404);
    }

    $sql = " SELECT name FROM {$this->tables['nodes_hierarchy']} " .
           " WHERE id = " . intval($plan['testproject_id']);
    $projectName = (string)$this->db->fetchFirstRowSingleColumn($sql, 'name');

    $buildName = null;
    if ($buildID > 0) {
      $sql = " SELECT name FROM {$this->tables['builds']} " .
             " WHERE id = {$buildID} AND testplan_id = {$planID} ";
      $buildName = $this->db->fetchFirstRowSingleColumn($sql, 'name');
      if (is_null($buildName)) {
        $response->getBody()->write(json_encode(
          array('status' => 'error', 'message' => 'Build does not belong to this plan')));
        return $response->withStatus(404);
      }
    }
    $buildFilter = $buildID > 0 ? " AND build_id = {$buildID} " : '';

    // ---- per-suite verdict summary (matrixBySuite SQL shape) ----
    $sql = " SELECT S.id AS suite_id, S.name AS suite_name, COUNT(*) AS linked " .
           " FROM {$this->tables['testplan_tcversions']} T " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = T.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " JOIN {$this->tables['nodes_hierarchy']} S ON S.id = NHTC.parent_id " .
           " WHERE T.testplan_id = {$planID} " .
           " GROUP BY S.id, S.name ORDER BY S.name ";
    $suites = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $suites[$row['suite_id']] = array(
        'name' => $row['suite_name'],
        'linked' => intval($row['linked']),
        'p' => 0, 'f' => 0, 'b' => 0, 'other' => 0);
    }

    $sql = " SELECT S.id AS suite_id, E2.status, COUNT(*) AS qty " .
           " FROM (SELECT MAX(id) AS mid FROM {$this->tables['executions']} " .
           "        WHERE testplan_id = {$planID} {$buildFilter} " .
           "        GROUP BY tcversion_id) M " .
           " JOIN {$this->tables['executions']} E2 ON E2.id = M.mid " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = E2.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " JOIN {$this->tables['nodes_hierarchy']} S ON S.id = NHTC.parent_id " .
           " GROUP BY S.id, E2.status ";
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $sid = $row['suite_id'];
      if (!isset($suites[$sid])) {
        continue;
      }
      $key = in_array($row['status'], array('p','f','b')) ? $row['status'] : 'other';
      $suites[$sid][$key] += intval($row['qty']);
    }

    // ---- per-case latest verdict list, bounded like the spec doc ----
    $cap = self::DOC_MAX_CASES;
    $sql = " SELECT NHTC.name, TCV.tc_external_id, S.name AS suite_name, " .
           "        E2.status, E2.execution_ts, E2.notes, " .
           "        U.login AS tester, B.name AS build_name " .
           " FROM {$this->tables['testplan_tcversions']} T " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = T.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " JOIN {$this->tables['nodes_hierarchy']} S ON S.id = NHTC.parent_id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id = T.tcversion_id " .
           " LEFT JOIN (SELECT tcversion_id, MAX(id) AS mid " .
           "              FROM {$this->tables['executions']} " .
           "             WHERE testplan_id = {$planID} {$buildFilter} " .
           "             GROUP BY tcversion_id) M ON M.tcversion_id = T.tcversion_id " .
           " LEFT JOIN {$this->tables['executions']} E2 ON E2.id = M.mid " .
           " LEFT JOIN {$this->tables['users']} U ON U.id = E2.tester_id " .
           " LEFT JOIN {$this->tables['builds']} B ON B.id = E2.build_id " .
           " WHERE T.testplan_id = {$planID} " .
           " ORDER BY S.name, NHTC.name " .
           " LIMIT " . ($cap + 1);
    $caseRows = (array)$this->db->get_recordset($sql);
    $truncated = count($caseRows) > $cap;
    if ($truncated) {
      array_pop($caseRows);
    }

    $title = 'Test report · ' . $plan['name'];
    $subtitle = 'Project ' . $projectName .
                ($buildName !== null ? ' · Build ' . $buildName : ' · all builds') .
                ' · Generated ' . date('Y-m-d H:i');
    $html = $this->docHtmlOpen($title, $subtitle);

    $html .= '<h2>Verdict summary by suite</h2>';
    if (count($suites) == 0) {
      $html .= '<p class="meta">No test cases linked to this plan.</p>';
    } else {
      $tot = array('linked' => 0, 'p' => 0, 'f' => 0, 'b' => 0);
      $html .= '<table><tr><th>Suite</th><th class="num">Cases</th>' .
               '<th class="num">Passed</th><th class="num">Failed</th>' .
               '<th class="num">Blocked</th><th class="num">Not run</th></tr>';
      foreach ($suites as $s) {
        $notRun = max(0, $s['linked'] - $s['p'] - $s['f'] - $s['b'] - $s['other']);
        foreach (array('linked','p','f','b') as $k) {
          $tot[$k] += $s[$k];
        }
        $html .= '<tr><td>' . self::docEsc($s['name']) . '</td>' .
                 '<td class="num">' . $s['linked'] . '</td>' .
                 '<td class="num v-p">' . $s['p'] . '</td>' .
                 '<td class="num v-f">' . $s['f'] . '</td>' .
                 '<td class="num v-b">' . $s['b'] . '</td>' .
                 '<td class="num v-n">' . $notRun . '</td></tr>';
      }
      $html .= '<tr><th>Total</th><th class="num">' . $tot['linked'] . '</th>' .
               '<th class="num v-p">' . $tot['p'] . '</th>' .
               '<th class="num v-f">' . $tot['f'] . '</th>' .
               '<th class="num v-b">' . $tot['b'] . '</th><th></th></tr></table>';
    }

    $html .= '<h2>Latest result per test case</h2>';
    if ($truncated) {
      $html .= '<div class="warn">List truncated at ' . $cap . ' test cases.</div>';
    }
    if (count($caseRows) == 0) {
      $html .= '<p class="meta">No test cases linked to this plan.</p>';
    } else {
      $html .= '<table><tr><th>Test case</th><th>Suite</th><th>Verdict</th>' .
               '<th>Tester</th><th>Date</th><th>Build</th><th>Notes</th></tr>';
      foreach ($caseRows as $row) {
        $code = isset($row['status']) ? $row['status'] : null;
        $cls = in_array($code, array('p','f','b')) ? 'v-' . $code : 'v-n';
        $html .= '<tr><td>' . self::docEsc($row['tc_external_id'] . ': ' . $row['name']) . '</td>' .
                 '<td>' . self::docEsc($row['suite_name']) . '</td>' .
                 '<td class="' . $cls . '">' . self::docEsc(self::docVerdictLabel($code)) . '</td>' .
                 '<td>' . self::docEsc($row['tester'] ?? '') . '</td>' .
                 '<td>' . self::docEsc($row['execution_ts'] ?? '') . '</td>' .
                 '<td>' . self::docEsc($row['build_name'] ?? '') . '</td>' .
                 '<td>' . self::docEsc($row['notes'] ?? '') . '</td></tr>';
      }
      $html .= '</table>';
    }

    $html .= '</body></html>';
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
  }

  /**
   * GET /testsuites/{id}/xml
   * TestLink-format XML export of a suite subtree — same schema as the
   * legacy tcExport.php page (it reuses the very same exporter:
   * testsuite::exportTestSuiteDataToXML).
   */
  public function exportSuiteXML(Request $request, Response $response, $args)
  {
    $suiteID = intval($args['id']);

    $sql = " SELECT name FROM {$this->tables['nodes_hierarchy']} " .
           " WHERE id = {$suiteID} AND node_type_id = 2 ";
    $suiteName = $this->db->fetchFirstRowSingleColumn($sql, 'name');
    if (is_null($suiteName)) {
      $response->getBody()->write(json_encode(
        array('status' => 'error', 'message' => 'Test suite does not exist')));
      return $response->withStatus(404);
    }

    $tprojectID = intval($this->tsuiteMgr->tree_manager->getTreeRoot($suiteID));

    // same option set the legacy export page uses for a deep suite
    // export without the optional extras (keywords/cfields/reqs/attachments)
    $optExport = array('RECURSIVE' => 1, 'TCSTEPS' => 1,
                       'EXTERNALID' => 1, 'ADDPREFIX' => 0,
                       'TCSUMMARY' => 1, 'TCPRECONDITIONS' => 1,
                       'KEYWORDS' => 0, 'CFIELDS' => 0,
                       'REQS' => 0, 'ATTACHMENTS' => 0);

    // the legacy exporter emits notices/warnings for unset optional
    // sections and loads a helper via a cwd-relative require
    // (../../third_party/...), so run it from lib/functions — the cwd
    // the legacy pages give it — and silence the noise so the XML
    // stream stays clean
    $oldLevel = error_reporting(E_ERROR | E_PARSE);
    $oldCwd = getcwd();
    chdir(TL_ABS_PATH . 'lib' . DIRECTORY_SEPARATOR . 'functions');
    ob_start();
    $xml = TL_XMLEXPORT_HEADER .
           $this->tsuiteMgr->exportTestSuiteDataToXML($suiteID, $tprojectID, $optExport);
    ob_end_clean();
    chdir($oldCwd);
    error_reporting($oldLevel);

    $fileName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $suiteName) .
                '.testsuite-deep.xml';
    $response->getBody()->write($xml);
    return $response
      ->withHeader('Content-Type', 'application/xml; charset=utf-8')
      ->withHeader('Content-Disposition',
        'attachment; filename="' . addslashes($fileName) . '"');
  }

  /**
   * POST /testsuites/{id}/xml
   * Import test cases from TestLink-format XML (the schema the export
   * above produces) into suite {id}. Body is the raw XML document.
   * Cases whose name already exists in the target suite are skipped,
   * so re-importing the same file is idempotent.
   * Returns {created, skippedDuplicates, errors[]}.
   */
  public function importSuiteXML(Request $request, Response $response, $args)
  {
    $suiteID = intval($args['id']);

    $sql = " SELECT name FROM {$this->tables['nodes_hierarchy']} " .
           " WHERE id = {$suiteID} AND node_type_id = 2 ";
    $suiteName = $this->db->fetchFirstRowSingleColumn($sql, 'name');
    if (is_null($suiteName)) {
      $response->getBody()->write(json_encode(
        array('status' => 'error', 'message' => 'Test suite does not exist')));
      return $response->withStatus(404);
    }

    $raw = trim((string)$request->getBody());
    if ($raw == '') {
      $response->getBody()->write(json_encode(
        array('status' => 'error', 'message' => 'Empty request body — send the XML document')));
      return $response->withStatus(400);
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($raw);
    if ($xml === false) {
      $detail = array_map(function ($e) {
        return trim($e->message) . ' (line ' . $e->line . ')';
      }, array_slice(libxml_get_errors(), 0, 3));
      libxml_clear_errors();
      $response->getBody()->write(json_encode(
        array('status' => 'error', 'message' => 'Malformed XML',
              'detail' => $detail)));
      return $response->withStatus(400);
    }

    // accept <testcases>, <testsuite> (deep export) or a single <testcase>
    $tcNodes = $xml->xpath('//testcase');
    if ($xml->getName() == 'testcase') {
      $tcNodes = array($xml);
    }

    $op = array('status' => 'ok', 'created' => 0,
                'skippedDuplicates' => 0, 'errors' => array());

    // names already present in the target suite -> duplicate skip
    $sql = " SELECT name FROM {$this->tables['nodes_hierarchy']} " .
           " WHERE parent_id = {$suiteID} AND node_type_id = 3 ";
    $existing = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
      $existing[mb_strtolower(trim($row['name']))] = true;
    }

    foreach ($tcNodes as $tc) {
      $name = trim((string)$tc['name']);
      if ($name == '') {
        $op['errors'][] = 'testcase without name attribute skipped';
        continue;
      }
      if (isset($existing[mb_strtolower($name)])) {
        $op['skippedDuplicates']++;
        continue;
      }

      // steps use the legacy element names (expectedresults, ...)
      $steps = array();
      if (isset($tc->steps->step)) {
        $n = 0;
        foreach ($tc->steps->step as $step) {
          $n++;
          $stepNumber = intval((string)$step->step_number);
          $steps[] = array(
            'step_number' => $stepNumber > 0 ? $stepNumber : $n,
            'actions' => (string)$step->actions,
            'expected_results' => (string)$step->expectedresults,
            'execution_type' => max(1, intval((string)$step->execution_type)));
        }
      }

      $execType = intval((string)$tc->execution_type);
      $importance = intval((string)$tc->importance);
      $order = intval((string)$tc->node_order);

      try {
        $ret = $this->tcaseMgr->create(
          $suiteID, $name,
          (string)$tc->summary, (string)$tc->preconditions,
          $steps, $this->userID, '', $order,
          testcase::AUTOMATIC_ID,
          $execType > 0 ? $execType : TESTCASE_EXECUTION_TYPE_MANUAL,
          $importance > 0 ? $importance : 2);
        if (isset($ret['status_ok']) && $ret['status_ok']) {
          $op['created']++;
          $existing[mb_strtolower($name)] = true;
        } else {
          $op['errors'][] = "'{$name}': " .
            (isset($ret['msg']) ? $ret['msg'] : 'create failed');
        }
      } catch (Exception $e) {
        $op['errors'][] = "'{$name}': " . $this->msgFromException($e);
      }
    }

    if (count($tcNodes) == 0) {
      $op['message'] = 'No <testcase> elements found in the document';
    }

    $response->getBody()->write(json_encode($op));
    return $response;
  }
} // class end
