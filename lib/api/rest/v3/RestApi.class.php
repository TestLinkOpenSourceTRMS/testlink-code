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
    // follows idx_exec_tplan_tcv_id and preserves execution order
    $sql = " SELECT E.tcversion_id, E.status, NHTC.name, NHTC.id AS tcase_id " .
           " FROM {$this->tables['executions']} E " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = E.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " WHERE E.testplan_id = {$safeID} AND E.status IN ('p','f') " .
           " AND E.execution_ts >= NOW() - INTERVAL {$days} DAY " .
           " ORDER BY E.tcversion_id, E.id ";
    $flips = array();
    $prev = array();
    foreach ((array)$this->db->get_recordset($sql) as $row) {
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

    $sql = " SELECT COUNT(*) AS qty FROM {$this->tables['testplan_tcversions']} " .
           " WHERE testplan_id = {$safeID} ";
    $total = intval($this->db->fetchFirstRowSingleColumn($sql, 'qty'));

    $sql = " SELECT T.tcversion_id, NHTCV.parent_id AS tcase_id, NHTC.name, " .
           "        TCV.tc_external_id, TCV.importance, " .
           "        (SELECT E.status FROM {$this->tables['executions']} E " .
           "          WHERE E.tcversion_id = T.tcversion_id " .
           "            AND E.testplan_id = T.testplan_id {$buildFilter} " .
           "          ORDER BY E.id DESC LIMIT 1) AS exec_status " .
           " FROM {$this->tables['testplan_tcversions']} T " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = T.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id = T.tcversion_id " .
           " WHERE T.testplan_id = {$safeID} " .
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
} // class end
