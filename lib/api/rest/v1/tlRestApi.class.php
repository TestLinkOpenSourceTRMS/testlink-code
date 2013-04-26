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
 * References
 * http://ericbrandel.com/2013/01/14/quickly-build-restful-apis-in-php-with-slim-part-2/
 * https://developer.atlassian.com/display/JIRADEV/JIRA+REST+API+Example+-+Add+Comment
 * http://confluence.jetbrains.com/display/YTD4/Create+New+Work+Item
 * http://www.redmine.org/projects/redmine/wiki/Rest_api
 * http://coenraets.org/blog/2011/12/restful-services-with-jquery-php-and-the-slim-framework/
 * https://github.com/educoder/pest/blob/master/examples/intouch_example.php
 * http://stackoverflow.com/questions/9772933/rest-api-request-body-as-json-or-plain-post-data
 *
 *
 *
 *
 *
 * @internal revisions 
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
  public static $version = "1.0";
    
    
  /**
   * The DB object used throughout the class
   * 
   * @access protected
   */
  protected $db = null;
  protected $tables = null;

  protected $tcaseMgr =  null;
  protected $tprojectMgr = null;
  protected $tplanMgr = null;
  protected $tplanMetricsMgr = null;
  protected $reqSpecMgr = null;
  protected $reqMgr = null;
  protected $platformMgr = null;

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
  /** _checkTCIDAndTPIDValid()                      */
  protected $tcVersionID = null;
  protected $versionNumber = null;
  
  
 
  public $statusCode;
  public $codeStatus;
  
  
  /**
   */
  public function __construct()
  {    
  
    // We are following Slim naming convention
    $this->app = new \Slim\Slim();
    $this->app->get('/who', function () {
      echo __CLASS__ . ' : Get Route /who';
    });


    $this->app->get('/whoAmI', array($this,'whoAmI'));
    $this->app->get('/testprojects', array($this,'getProjects'));
    $this->app->get('/testprojects/:id', array($this,'getProjects'));
    // $this->app->get('/testprojects/:id/testplans/', array($this,'getTestProjectTestPlans'));
    // $this->app->get('/testplans/:id', array($this,'getTestPlan'));



    $this->db = new database(DB_TYPE);
    $this->db->db->SetFetchMode(ADODB_FETCH_ASSOC);
    doDBConnect($this->db,database::ONERROREXIT);


    $this->tcaseMgr=new testcase($this->db);
    $this->tprojectMgr=new testproject($this->db);
    $this->tplanMgr=new testplan($this->db);
    $this->tplanMetricsMgr=new tlTestPlanMetrics($this->db);
    $this->reqSpecMgr=new requirement_spec_mgr($this->db);
    $this->reqMgr=new requirement_mgr($this->db);
    
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
  }  



  function authenticate($apiKey=null)
  {
    if(is_null($apiKey))
    {  
      $request = $this->app->request();
      $apiKey  = $request->headers('PHP_AUTH_USER');
    } 

    $sql = "SELECT id FROM {$this->tables['users']} " .
           "WHERE script_key='" . $this->db->prepare_string($apiKey) . "'";

    $this->userID = $this->db->fetchFirstRowSingleColumn($sql, "id");
    if( ($ok=!is_null($this->userID)) )
    {
      $this->user = tlUser::getByID($this->db,$this->userID);  
    }  
    return $ok;
  }



  public function whoAmI()
  {    
    echo json_encode(array('name' => __CLASS__ . ' : Get Route /whoAmI'));
  }
  

  public function getProjects($id=null)
  {
    $op = array('status' => 'ko', 'message' => 'ko', 'item' => null);  
    if($this->authenticate())
    {
      $op = array('status' => 'ok', 'message' => 'ok');
      if(is_null($id))
      {
        $opt = array('output' => 'array_of_map', 'order_by' => " ORDER BY name ", 'add_issuetracker' => true,
                     'add_reqmgrsystem' => true);
        $op['item'] = $this->tprojectMgr->get_accessible_for_user($this->userID,$opt);
      }  
      else
      {
        $opt = array('output' => 'map','field_set' => 'id', 'format' => 'simple');
        $zx = $this->tprojectMgr->get_accessible_for_user($this->userID,$opt);
        if( ($safeID = intval($id)) > 0)
        {
          if( isset($zx[$safeID]) )
          {
            $op['item'] = $this->tprojectMgr->get_by_id($safeID);  
          } 
        } 
        else
        {
          // Will consider id = name
          foreach( $zx as $key => $value ) 
          {
            if( strcmp($value['name'],$id) == 0 )
            {
              $safeID = $this->db->prepare_string($id);
              $op['item'] = $this->tprojectMgr->get_by_name($safeID);
              break;   
            }  
          }
        } 
      }  
    }
    else
    {
      $op['message'] = 'authetication error';
    }  

    // Developer (silly?) information
    // json_encode() transforms maps in objects.
    echo json_encode($op);
  }


  public function getLatestBuildForTestPlan($id)
  {
    $operation=__FUNCTION__;
    $msg_prefix="({$operation}) - ";
    $status_ok=true;


    $checkFunctions = array('authenticate','checkTestPlanID');       
    $status_ok=$this->_runChecks($checkFunctions,$msg_prefix);       

    if( $status_ok )
    {
      $build_id = $this->tplanMgr->get_max_build_id($id);
     
      if( ($status_ok=$build_id > 0) )
      {
          $builds = $this->tplanMgr->get_builds($testPlanID);  
          $build_info = $builds[$build_id];
      }
      else
      {
          $tplan_info=$this->tplanMgr->get_by_id($testPlanID);
          $msg = $msg_prefix . sprintf(TPLAN_HAS_NO_BUILDS_STR,$tplan_info['name'],$tplan_info['id']);
          $this->errors[] = new IXR_Error(TPLAN_HAS_NO_BUILDS,$msg);
      }
    }
    
    return $status_ok ? $build_info : $this->errors;
  }










} // class end