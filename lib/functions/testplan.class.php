<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Manages test plan operations and related items like Custom fields, 
 * Builds, Custom fields, etc
 *
 * @filesource  testplan.class.php
 * @package     TestLink
 * @author      franciscom
 * @copyright   2007-2016, TestLink community 
 * @link        http://testlink.sourceforge.net/
 *
 *
 * @internal revisions
 * 
 * @since 1.9.15
 **/

/** related functionality */
require_once( dirname(__FILE__) . '/tree.class.php' );
require_once( dirname(__FILE__) . '/assignment_mgr.class.php' );
require_once( dirname(__FILE__) . '/attachments.inc.php' );

/**
 * class to coordinate and manage Test Plans
 * @package   TestLink
 */
class testplan extends tlObjectWithAttachments
{
  /** query options */
  const GET_ALL=null;
  const GET_ACTIVE_BUILD=1;
  const GET_INACTIVE_BUILD=0;
  const GET_OPEN_BUILD=1;
  const GET_CLOSED_BUILD=0;
  const ACTIVE_BUILDS=1;
  const OPEN_BUILDS=1;
  const ENABLED=1;
  const IGNORE=-1;

  /** @var database handler */
  var $db;

  var $tree_manager;
  var $assignment_mgr;
  var $cfield_mgr;
  var $tcase_mgr;
   
  var $assignment_types;
  var $assignment_status;

  /** message to show on GUI */
  var $user_feedback_message = '';

  var $node_types_descr_id;
  var $node_types_id_descr;

  var $import_file_types = array("XML" => "XML"); // array("XML" => "XML", "XLS" => "XLS" );
  
  var $resultsCfg;
  var $tcaseCfg;
  
  var $notRunStatusCode;
  var $execTaskCode;


  // Nodes to exclude when do test plan tree traversal
  var $nt2exclude=array('testplan' => 'exclude_me',
                        'requirement_spec'=> 'exclude_me',
                        'requirement'=> 'exclude_me');

  var $nt2exclude_children=array('testcase' => 'exclude_my_children',
                                 'requirement_spec'=> 'exclude_my_children');

  /**
   * testplan class constructor
   * 
   * @param resource &$db reference to database handler
   */
  function __construct(&$db)
  {
    $this->db = &$db;
    $this->tree_manager = new tree($this->db);
    $this->node_types_descr_id = $this->tree_manager->get_available_node_types();
    $this->node_types_id_descr = array_flip($this->node_types_descr_id);
      
    $this->assignment_mgr = new assignment_mgr($this->db);
    $this->assignment_types = $this->assignment_mgr->get_available_types();
    $this->assignment_status = $this->assignment_mgr->get_available_status();

    $this->cfield_mgr = new cfield_mgr($this->db);
    $this->tcase_mgr = New testcase($this->db);
    $this->platform_mgr = new tlPlatform($this->db);
     
    $this->resultsCfg = config_get('results');
    $this->tcaseCfg = config_get('testcase_cfg');

       
       // special values used too many times
    $this->notRunStatusCode = $this->resultsCfg['status_code']['not_run'];
    $this->execTaskCode = intval($this->assignment_types['testcase_execution']['id']);

    tlObjectWithAttachments::__construct($this->db,'testplans');
  }

  /**
   * getter for import types 
    * @return array key: import file type code, value: import file type verbose description
    */
  function get_import_file_types()
  {
    return $this->import_file_types;
  }

  /**
   * creates a tesplan on Database, for a testproject.
   * 
   * @param string $name: testplan name
   * @param string $notes: testplan notes
   * @param string $testproject_id: testplan parent
   * 
   * @return integer status code
   *     if everything ok -> id of new testplan (node id).
   *     if problems -> 0.
   */
  function create($name,$notes,$testproject_id,$is_active=1,$is_public=1)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $node_types=$this->tree_manager->get_available_node_types();
    $tplan_id = $this->tree_manager->new_node($testproject_id,$node_types['testplan'],$name);
    
    $active_status=intval($is_active) > 0 ? 1 : 0;
    $public_status=intval($is_public) > 0 ? 1 : 0;
    
    $api_key = md5(rand()) . md5(rand()); 

    $sql = "/* $debugMsg */ " . 
           " INSERT INTO {$this->tables['testplans']} (id,notes,api_key,testproject_id,active,is_public) " .
           " VALUES ( {$tplan_id} " . ", '" . $this->db->prepare_string($notes) . "'," .
           "'" .  $this->db->prepare_string($api_key) . "'," .
           $testproject_id . "," . $active_status . "," . $public_status . ")";
    $result = $this->db->exec_query($sql);
    $id = 0;
    if ($result)
    {
      $id = $tplan_id;
    }

    return $id;
  }


  /**
   *
   */
  function createFromObject($item,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my['opt'] = array('doChecks' => false, 'setSessionProject' => true);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    try 
    {
      // mandatory checks
      if(strlen($item->name)==0)
      {
        throw new Exception('Empty name is not allowed');      
      }  
    
      // what checks need to be done ?
      // 1. test project exist
      $pinfo = $this->tree_manager->get_node_hierarchy_info($item->testProjectID);
      if(is_null($pinfo) || $this->node_types_id_descr[$pinfo['node_type_id']] != 'testproject')
      {
        throw new Exception('Test project ID does not exist');      
      }  

      // 2. there is NO other test plan on test project with same name
      $name = trim($item->name);
      $op = $this->checkNameExistence($name,$item->testProjectID);
      if(!$op['status_ok'])
      {
        throw new Exception('Test plan name is already in use on Test project');      
      }  
    }   
    catch (Exception $e) 
    {
      throw $e;  // rethrow
    }

    // seems OK => go
    $active_status = intval($item->active) > 0 ? 1 : 0;
    $public_status = intval($item->is_public) > 0 ? 1 : 0;

    $id = $this->tree_manager->new_node($item->testProjectID,$this->node_types_descr_id['testplan'],$name);
    $sql = "/* $debugMsg */ " . 
           " INSERT INTO {$this->tables['testplans']} (id,notes,api_key,testproject_id,active,is_public) " .
           " VALUES ( {$id} " . ", '" . $this->db->prepare_string($item->notes) . "'," . 
             "'" .  $this->db->prepare_string($api_key) . "'," .
              $item->testProjectID . "," . $active_status . "," . $public_status . ")";
    $result = $this->db->exec_query($sql);
    return $result ? $id : 0;
  }


  /**
   *
   */
  function updateFromObject($item,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my['opt'] = array('doChecks' => false, 'setSessionProject' => true);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    if( !property_exists($item, 'id') )
    {
      throw new Exception('Test plan ID is missing');      
    }  

    if( ($safeID = intval($item->id)) == 0 )
    {
      throw new Exception('Test plan ID 0 is not allowed');      
    }  

    $pinfo = $this->get_by_id($safeID, array( 'output' => 'minimun'));

    if(is_null($pinfo))
    {
      throw new Exception('Test plan ID does not exist');      
    }  

    $attr = array();
    $upd = '';
    try 
    {

      if( property_exists($item, 'name') )
      {
        $name = trim($item->name);
        if(strlen($name)==0)
        {
          throw new Exception('Empty name is not allowed');      
        }  
      
        // 1. NO other test plan on test project with same name
        $op = $this->checkNameExistence($name,$pinfo['testproject_id'],$safeID);
        if(!$op['status_ok'])
        {
          throw new Exception('Test plan name is already in use on Test project');      
        }  
      
        $sql = "/* $debugMsg */ " .
               " UPDATE {$this->tables['nodes_hierarchy']} " .
               " SET name='" . $this->db->prepare_string($name) . "'" .
               " WHERE id={$safeID}";
        $result = $this->db->exec_query($sql);
      }  

      if( property_exists($item, 'notes') )
      {
        $upd = ($upd != '' ? ',' : '') . " notes = '" . $this->db->prepare_string($item->notes) . "' ";
      }

      $intAttr = array('active','is_public');
      foreach($intAttr as $key)
      {
        if( property_exists($item, $key) )
        {
          $upd = ($upd != '' ? ',' : '') . $key . ' = ' . (intval($item->$key) > 0 ? 1 : 0);
        }
      }  

      if($upd != '')
      {
        $sql = " UPDATE {$this->tables['testplans']} " .
               " SET {$upd} WHERE id=" . $safeID;
        $result = $this->db->exec_query($sql);
      }  
    }   
    catch (Exception $e) 
    {
      throw $e;  // rethrow
    }
    return $safeID;
  }



  /**
   * Checks is there is another test plan inside test project 
   * with different id but same name
   *
   **/
  function checkNameExistence($name,$tprojectID,$id=0)
  {
    $check_op['msg'] = '';
    $check_op['status_ok'] = 1;
       
    if($this->get_by_name($name,intval($tprojectID), array('id' => intval($id))) )
    {
      $check_op['msg'] = sprintf(lang_get('error_product_name_duplicate'),$name);
      $check_op['status_ok'] = 0;
    }
    return $check_op;
  }


  /**
   * update testplan information
   * 
   * @param integer $id Test plan identifier
   * @param string $name: testplan name
   * @param string $notes: testplan notes
   * @param boolean $is_active
   * 
   * @return integer result code (1=ok)
   */
  function update($id,$name,$notes,$is_active=null,$is_public=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $do_update = 1;
    $result = null;
    // $active = to_boolean($is_active);
    $name = trim($name);
    
    // two tables to update and we have no transaction yet.
    $rsa = $this->get_by_id($id);
    $duplicate_check = (strcmp($rsa['name'],$name) != 0 );
    
    if($duplicate_check)
    {
      $rs = $this->get_by_name($name,$rsa['parent_id']);
      $do_update = is_null($rs);
    }
    
    if($do_update)
    {
      // Update name
      $sql = "/* $debugMsg */ ";
      $sql .= "UPDATE {$this->tables['nodes_hierarchy']} " .
              "SET name='" . $this->db->prepare_string($name) . "'" .
              "WHERE id={$id}";
      $result = $this->db->exec_query($sql);
      
      if($result)
      {
        $add_upd='';
        if( !is_null($is_active) )
        {
          $add_upd .=',active=' . (intval($is_active) > 0 ? 1 : 0);
        }
        if( !is_null($is_public) )
        {
          $add_upd .=',is_public=' . (intval($is_public) > 0 ? 1:0);
        }
        
        $sql = " UPDATE {$this->tables['testplans']} " .
               " SET notes='" . $this->db->prepare_string($notes). "' " .
               " {$add_upd} WHERE id=" . $id;
        $result = $this->db->exec_query($sql);
      }
    }
    return ($result ? 1 : 0);
  }


  /*
   function: get_by_name
   get information about a testplan using name as access key.
   Search can be narrowed, givin a testproject id as filter criteria.
   
   args: name: testplan name
   [tproject_id]: default:0 -> system wide search i.e. inside all testprojects
   
   returns: if nothing found -> null
   if found -> array where every element is a map with following keys:
   id: testplan id
   notes:
   active: active status
   is_open: open status
   name: testplan name
   testproject_id
   */
  function get_by_name($name,$tproject_id=0,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my = array();
    $my['opt'] = array('output' => 'full', 'id' => 0);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $sql = "/* $debugMsg */ ";

    switch($my['opt']['output'])
    {
      case 'minimun':
        $sql .= " SELECT testplans.id, NH.name ";
      break;
      
      case 'full':
      default:
        $sql .= " SELECT testplans.*, NH.name ";
      break;
    }

    $sql .= " FROM {$this->tables['testplans']} testplans, " .
            " {$this->tables['nodes_hierarchy']} NH" .
            " WHERE testplans.id = NH.id " .
            " AND NH.name = '" . $this->db->prepare_string($name) . "'";
        
    if( ($safe_id = intval($tproject_id)) > 0 )
    {
      $sql .= " AND NH.parent_id={$safe_id} ";
    }
    
    // useful when trying to check for duplicates ?
    if( ($my['opt']['id'] = intval($my['opt']['id'])) > 0)
    {
      $sql .= " AND testplans.id != {$my['opt']['id']} ";
    }  

    $rs = $this->db->get_recordset($sql);
    return($rs);
  }


  /*
   function: get_by_id
   
   args : id: testplan id
   
   returns: map with following keys:
   id: testplan id
   name: testplan name
   notes: testplan notes
   testproject_id
   active
   is_open
   parent_id
   */
  function get_by_id($id, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my = array();
    $my['opt'] = array('output' => 'full','active' => null, 'testPlanFields' => '');
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $safe_id = intval($id);
    switch($my['opt']['output'])
    {
      case 'testPlanFields':
        $sql = "/* $debugMsg */ " .
               " SELECT {$my['opt']['testPlanFields']} FROM {$this->tables['testplans']} " .
               " WHERE id = " . $safe_id;
      break;

      case 'minimun':
        $sql = "/* $debugMsg */ " .
               " SELECT NH_TPLAN.name," .
               " NH_TPROJ.id AS tproject_id, NH_TPROJ.name AS tproject_name,TPROJ.prefix" .
               " FROM {$this->tables['nodes_hierarchy']} NH_TPLAN " .
               " JOIN {$this->tables['nodes_hierarchy']} NH_TPROJ ON NH_TPROJ.id = NH_TPLAN.parent_id " .
               " JOIN {$this->tables['testprojects']} TPROJ ON TPROJ.ID = NH_TPROJ.id " .
               " WHERE NH_TPLAN.id = " . $safe_id;
      break;
      
      case 'full':
      default:
            $sql =   "/* $debugMsg */ " .
                " SELECT TPLAN.*,NH_TPLAN.name,NH_TPLAN.parent_id " .
               " FROM {$this->tables['testplans']} TPLAN, " .
               " {$this->tables['nodes_hierarchy']} NH_TPLAN " .
               " WHERE TPLAN.id = NH_TPLAN.id AND TPLAN.id = " . $safe_id;
      break;  
    
    }

    if(!is_null($my['opt']['active']))
    {
      $sql .= " AND active=" . (intval($my['opt']['active']) > 0 ? 1 : 0) . " ";
    }

    $rs = $this->db->get_recordset($sql);
    return ($rs ? $rs[0] : null);
  }


  /*
      function: get_all
            get array of info for every test plan,
            without considering Test Project and any other kind of filter.
            Every array element contains an assoc array

      args : -

      returns: array, every element is a  map with following keys:
           id: testplan id
           name: testplan name
           notes: testplan notes
           testproject_id
           active
           is_open
           parent_id
  */
  function get_all()
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " . " SELECT testplans.*, NH.name " .
           " FROM {$this->tables['testplans']} testplans, " .
           " {$this->tables['nodes_hierarchy']} NH " .
           " WHERE testplans.id=NH.id";
    $recordset = $this->db->get_recordset($sql);
    return $recordset;
  }

  /*
    function: count_testcases
            get number of testcases linked to a testplan

    args: id: testplan id, can be array of id,

            [platform_id]: null => do not filter by platform
                     can be array of platform id  
            
    returns: number
  */
  public function count_testcases($id,$platform_id=null,$opt=null)
  {
    // output:
    // 'number', just the count
    // 'groupByTestPlan' => map: key test plan id
    //                      element: count
    //
    // 'groupByTestPlanPlatform' => map: first level key test plan id
    //                                   second level key platform id 
    //
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    // protect yourself :) - 20140607
    if( is_null($id) || (is_int($id) && intval($id) <= 0 ) || (is_array($id) && count($id) == 0) )
    {
      return 0;  // >>>----> Bye
    } 


    $my['opt'] = array('output' => 'number');
    $my['opt'] = array_merge($my['opt'],(array)$opt);
    
    $sql_filter = '';
    if( !is_null($platform_id) )
    {
      $sql_filter = ' AND platform_id IN (' . implode(',',(array)$platform_id) . ')';
    }
    


    $out = null;
    $outfields = "/* $debugMsg */ " . ' SELECT COUNT(testplan_id) AS qty ';
    $dummy = " FROM {$this->tables['testplan_tcversions']} " .
             " WHERE testplan_id IN (" . implode(',',(array)$id) . ") {$sql_filter}";

    switch( $my['opt']['output'] )
    {
      case 'groupByTestPlan':
        $sql = $outfields . ', testplan_id' . $dummy . ' GROUP BY testplan_id '; 
        $out = $this->db->fetchRowsIntoMap($sql,'testplan_id');
      break;
      
      case 'groupByTestPlanPlatform':
        $groupBy = ' GROUP BY testplan_id, platform_id ';
        $sql = $outfields . ', testplan_id, platform_id' . $dummy . 
             ' GROUP BY testplan_id,platform_id '; 
        $out = $this->db->fetchMapsRowsIntoMap($sql,'testplan_id','platform_id');
      break;
    
      case 'number':
      default:
        $sql = $outfields . $dummy;
        $rs = $this->db->get_recordset($sql);
    
        $out = 0;
        if(!is_null($rs))
        {
          $out = $rs[0]['qty'];
        }
      break;
    }

    return $out;
  }




  /*
    function: tcversionInfoForAudit
            get info regarding tcversions, to generate useful audit messages
            

    args :
        $tplan_id: test plan id
        $items_to_link: map key=tc_id 
                        value: tcversion_id
    returns: -

    rev: 20080629 - franciscom - audit message improvements
  */
  function tcversionInfoForAudit($tplan_id,&$items)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    // Get human readeable info for audit
    $ret=array();
    // $tcase_cfg = config_get('testcase_cfg');
    $dummy=reset($items);
    
    list($ret['tcasePrefix'],$tproject_id) = $this->tcase_mgr->getPrefix($dummy);
    $ret['tcasePrefix'] .= $this->tcaseCfg->glue_character;
    
        $sql = "/* $debugMsg */ " .
           " SELECT TCV.id, tc_external_id, version, NHB.name " .
         " FROM {$this->tables['tcversions']} TCV,{$this->tables['nodes_hierarchy']} NHA, " .
         " {$this->tables['nodes_hierarchy']} NHB " .
         " WHERE NHA.id=TCV.id " .
         " AND NHB.id=NHA.parent_id  " .
         " AND TCV.id IN (" . implode(',',$items) . ")";
    
    $ret['info']=$this->db->fetchRowsIntoMap($sql,'id');  
    $ret['tplanInfo']=$this->get_by_id($tplan_id);                                                          
    
    return $ret;
  }


  /**
   * associates version of different test cases to a test plan.
   * this is the way to populate a test plan

    args :
        $id: test plan id
        $items_to_link: map key=tc_id 
                        value= map with
                               key: platform_id (can be 0)
                               value: tcversion_id
                        passed by reference for speed
    returns: -

    rev: 20080629 - franciscom - audit message improvements
  */
  function link_tcversions($id,&$items_to_link,$userId)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    // Get human readeable info for audit
    $title_separator = config_get('gui_title_separator_1');
    $auditInfo=$this->tcversionInfoForAudit($id,$items_to_link['tcversion']);
    $platformInfo = $this->platform_mgr->getLinkedToTestplanAsMap($id);
    $platformLabel = lang_get('platform');
    
    // Important: MySQL do not support default values on datetime columns that are functions
    // that's why we are using db_now().
    $sql = "/* $debugMsg */ " .
           "INSERT INTO {$this->tables['testplan_tcversions']} " .
         "(testplan_id,author_id,creation_ts,tcversion_id,platform_id) " . 
         " VALUES ({$id},{$userId},{$this->db->db_now()},";
        $features=null;
    foreach($items_to_link['items'] as $tcase_id => $items)
    {
      foreach($items as $platform_id => $tcversion)
      {
        $addInfo='';
        $result = $this->db->exec_query($sql . "{$tcversion}, {$platform_id})");
        if ($result)
        {
                    $features[$platform_id][$tcversion]=$this->db->insert_id($this->tables['testplan_tcversions']);          
          if( isset($platformInfo[$platform_id]) )
          {
            $addInfo = ' - ' . $platformLabel . ':' . $platformInfo[$platform_id];
          }
          $auditMsg=TLS("audit_tc_added_to_testplan",
                  $auditInfo['tcasePrefix'] . $auditInfo['info'][$tcversion]['tc_external_id'] . 
                  $title_separator . $auditInfo['info'][$tcversion]['name'],
                  $auditInfo['info'][$tcversion]['version'],
                  $auditInfo['tplanInfo']['name'] . $addInfo );
          
          logAuditEvent($auditMsg,"ASSIGN",$id,"testplans");
        }  
      }
    }
    return $features;
  }


  /*
    function: setExecutionOrder

    args :
        $id: test plan id
        $executionOrder: assoc array key=tcversion_id value=order
                         passed by reference for speed

    returns: -
  */
  function setExecutionOrder($id,&$executionOrder)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    foreach($executionOrder as $tcVersionID => $execOrder)
    {
      $execOrder=intval($execOrder);
      $sql="/* $debugMsg */ UPDATE {$this->tables['testplan_tcversions']} " .
         "SET node_order={$execOrder} " .
         "WHERE testplan_id={$id} " .
         "AND tcversion_id={$tcVersionID}";
      $result = $this->db->exec_query($sql);
    }
  }
  

  /**
   * Ignores Platforms, then if a test case version is linked to a test plan
   * and two platforms, we will get item once. 
   * Need to understand if in context where we want to use this method this is
   * a problem
   *
   * 
   * @internal revisions:
   */
  function get_linked_items_id($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = " /* $debugMsg */ ". 
         " SELECT DISTINCT parent_id FROM {$this->tables['nodes_hierarchy']} NHTC " .
         " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NHTC.id " .
         " WHERE TPTCV.testplan_id = " . intval($id);
         
    $linked_items = $this->db->fetchRowsIntoMap($sql,'parent_id');           
    return $linked_items;
  }


  /**
   * @internal revisions
   * 
   */
  function get_linked_tcvid($id,$platformID,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $options = array('addEstimatedExecDuration' => false,
                     'tcase_id' => 0);
    $options = array_merge($options,(array)$opt);

    $addFields = '';
    $addSql = ''; 
    $addWhere = '';

    if($options['addEstimatedExecDuration'])
    {
      $addFields = ',TCV.estimated_exec_duration '; 
      $addSql .= " JOIN {$this->tables['tcversions']} TCV ON TCV.id = tcversion_id ";
    }  

    if($options['tcase_id'] > 0)
    {
      $addFields = ', NHTCV.parent_id AS tcase_id ';
      $addSql .= " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
                 " ON NHTCV.id = tcversion_id ";

      $addWhere = ' AND NHTCV.parent_id = ' . 
                  intval($options['tcase_id']); 
    }

    $sql = " /* $debugMsg */ " . 
           " SELECT tcversion_id {$addFields} " . 
           " FROM {$this->tables['testplan_tcversions']} " .
           $addSql;

    $sql .= " WHERE testplan_id = " . intval($id) . 
            " AND platform_id = " . intval($platformID) . 
            $addWhere;

    $items = $this->db->fetchRowsIntoMap($sql,'tcversion_id');           
    return $items;
  }


  /**
   *
   *
   */ 
  function getLinkedCount($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = " /* $debugMsg */ ". 
         " SELECT COUNT( DISTINCT(TPTCV.tcversion_id) ) AS qty " .
         " FROM {$this->tables['testplan_tcversions']} TPTCV " .
         " WHERE TPTCV.testplan_id = " . intval($id);
         
    $rs = $this->db->get_recordset($sql);           
    return $rs[0]['qty'];
  }



  /**
   * @internal revisions:
   * 
   */
  function getFeatureID($id,$platformID,$tcversionID)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = " /* $debugMsg */ ". 
         " SELECT id FROM {$this->tables['testplan_tcversions']} " .
         " WHERE testplan_id = " . intval($id) . 
         " AND tcversion_id = " . intval($tcversionID) . 
         " AND platform_id = " . intval($platformID) ;
         
    $linked_items = $this->db->fetchRowsIntoMap($sql,'id');
    return !is_null($linked_items) ? key($linked_items) : -1;
  }


  /**
   * @internal revisions:
   * 
   */
  function getRootTestSuites($id,$tproject_id,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $my = array('opt' => array('output' => 'std'));
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $sql = " /* $debugMsg */ ". 
         " SELECT DISTINCT NHTCASE.parent_id AS tsuite_id" .
         " FROM {$this->tables['nodes_hierarchy']} NHTCV " .
         " JOIN {$this->tables['testplan_tcversions']} TPTCV " .
         " ON TPTCV.tcversion_id = NHTCV.id " .
         " JOIN {$this->tables['nodes_hierarchy']} NHTCASE " .
         " ON NHTCASE.id = NHTCV.parent_id " .
         " WHERE TPTCV.testplan_id = {$id} ";
         
    $items = $this->db->fetchRowsIntoMap($sql,'tsuite_id',database::CUMULATIVE);           
      $xsql = " SELECT COALESCE(parent_id,0) AS parent_id,id,name" . 
          " FROM {$this->tables['nodes_hierarchy']} " . 
          " WHERE id IN (" . implode(',',array_keys($items)) . ") AND parent_id IS NOT NULL";

    unset($items);
    $xmen = $this->db->fetchMapRowsIntoMap($xsql,'parent_id','id');
    $tlnodes = array();  
    foreach($xmen as $parent_id => &$children)
    {
      if($parent_id == $tproject_id)
      {
        foreach($children as $item_id => &$elem)
        {
          $tlnodes[$item_id] = '';
        }
      }
      else
      {
        $paty = $this->tree_manager->get_path($parent_id);
        if( !isset($tlnodes[$paty[0]['id']]) )
        {
          $tlnodes[$paty[0]['id']] = '';    
          }
        unset($paty);
      }
    }
    unset($xmen);
    
    // Now with node list get order
      $xsql = " SELECT id,name,node_order " . 
          " FROM {$this->tables['nodes_hierarchy']} " . 
          " WHERE id IN (" . implode(',',array_keys($tlnodes)) . ")" .
          " ORDER BY node_order,name ";
    $xmen = $this->db->fetchRowsIntoMap($xsql,'id');
    switch($my['opt']['output'])
    {
      case 'std':
        foreach($xmen as $xid => $elem)
        {
          $xmen[$xid] = $elem['name'];
        }  
      break;
    }
    unset($tlnodes);
      return $xmen;
  }
  






  /**
   * 
   * 
   */
  function helper_keywords_sql($filter,$options=null)
  {
    
    $sql = array('filter' => '', 'join' => '');

    if( is_array($filter) )
    {
      // 0 -> no keyword, remove 
      if( $filter[0] == 0 )
      {
        array_shift($filter);
      }
      
      if(count($filter))
      {
        $sql['filter'] = " AND TK.keyword_id IN (" . implode(',',$filter) . ")";            
      }  
    }
    else if($filter > 0)
    {
      $sql['filter'] = " AND TK.keyword_id = {$filter} ";
    }
    
    if( $sql['filter'] != '' )
    {
      $sql['join'] = " JOIN {$this->tables['testcase_keywords']} TK ON TK.testcase_id = NH_TCV.parent_id ";
    }

    // mmm, here there is missing documentation
    $ret = is_null($options) ? $sql : array($sql['join'],$sql['filter']);
    return $ret;
  }
  
  
  /**
   * 
    * 
    */
  function helper_urgency_sql($filter)
  {
      
    $cfg = config_get("urgencyImportance");
    $sql = '';
    if ($filter == HIGH)
    {
      $sql .= " AND (urgency * importance) >= " . $cfg->threshold['high'];
    }
    else if($filter == LOW)
    {
      $sql .= " AND (urgency * importance) < " . $cfg->threshold['low'];
    }
    else
    {
      $sql .= " AND ( ((urgency * importance) >= " . $cfg->threshold['low'] . 
            " AND  ((urgency * importance) < " . $cfg->threshold['high']."))) ";
    }    

    return $sql;
  }
  
  
  /**
   * 
    * 
    */
  function helper_assigned_to_sql($filter,$opt,$build_id)
  {  
    
    $join = " JOIN {$this->tables['user_assignments']} UA " .
            " ON UA.feature_id = TPTCV.id " . 
            " AND UA.build_id = " . $build_id . 
            " AND UA.type = {$this->execTaskCode} ";
    
    // Warning!!!:
    // If special user id TL_USER_NOBODY is present in set of user id
    // we will ignore any other user id present on set.
    $ff = (array)$filter;
    $sql = " UA.user_id "; 
    if( in_array(TL_USER_NOBODY,$ff) )
    {
      $sql .= " IS NULL "; 
      $join = ' LEFT OUTER ' . $join;
    } 
    else if( in_array(TL_USER_SOMEBODY,$ff) )
    {
      $sql .= " IS NOT NULL "; 
    }
    else
    {
      $sql_unassigned="";
      $sql = '';
      if( $opt['include_unassigned'] )
      {
        $join = ' LEFT OUTER ' . $join;  // 20130729
        
        $sql = "(";
        $sql_unassigned=" OR UA.user_id IS NULL)";
      }
      $sql .= " UA.user_id IN (" . implode(",",$ff) . ") " . $sql_unassigned;
    }
    
    return array($join, ' AND ' . $sql);
  }




  /**
   * 
   * 
   */
  function helper_exec_status_filter($filter,$lastExecSql)
  {
    $notRunFilter = null;  
    $execFilter = '';
      
    $notRunPresent = array_search($this->notRunStatusCode,$filter); 
    if($notRunPresent !== false)
    {
      $notRunFilter = " E.status IS NULL ";
      unset($filter[$this->notRunStatusCode]);  
    }
    
    if(count($filter) > 0)
    {
      $dummy = " E.status IN ('" . implode("','",$filter) . "') ";
      $execFilter = " ( {$dummy} {$lastExecSql} ) ";  
    }
    
    if( !is_null($notRunFilter) )
    {
      if($execFilter != "")
      {
        $execFilter .= " OR ";
      }
      $execFilter .= $notRunFilter;
    }
    
    if( $execFilter != "")
    {
            // Just add the AND 
      $execFilter = " AND ({$execFilter} )";     
    }
    return array($execFilter,$notRunFilter);    
  }

  /**
   * 
   * 
   */
  function helper_bugs_sql($filter)
  {
    $sql = array('filter' => '', 'join' => '');
    $dummy = explode(',',$filter);
    $items = null;
    foreach($dummy as $v)
    {
      $x = trim($v);
      if($x != '')
      {
        $items[] = $x;
      }  
    }  
    if(!is_null($items))
    {
      $sql['filter'] = " AND EB.bug_id IN ('" . implode("','",$items) . "')";            
      $sql['join'] = " JOIN {$this->tables['execution_bugs']} EB ON EB.execution_id = E.id ";
    }  
    return array($sql['join'],$sql['filter']);
  }




  /*
    function: get_linked_and_newest_tcversions
              returns for every test case in a test plan
              the tc version linked and the newest available version

    args: id: testplan id
          [tcase_id]: default null => all testcases linked to testplan

    returns: map key: testcase internal id
             values: map with following keys:

              [name]
              [tc_id] (internal id)
              [tcversion_id]
              [newest_tcversion_id]
              [tc_external_id]
              [version] (for humans)
              [newest_version] (for humans)

  */
  function get_linked_and_newest_tcversions($id,$tcase_id=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
      
    $tc_id_filter = " ";
    if (!is_null($tcase_id) )
    {
      if( is_array($tcase_id) )
      {
          // ??? implement as in ?
      }
      else if ($tcase_id > 0 )
      {
        $tc_id_filter = " AND NHA.parent_id = {$tcase_id} ";
      }
    }
      
    // Peter Rooms found bug due to wrong SQL, accepted by MySQL but not by PostGres
    // Missing column in GROUP BY Clause
      
    $sql = " /* $debugMsg */ SELECT MAX(NHB.id) AS newest_tcversion_id, " .
           " NHA.parent_id AS tc_id, NHC.name, T.tcversion_id AS tcversion_id," .
           " TCVA.tc_external_id AS tc_external_id, TCVA.version AS version " .
           " FROM {$this->tables['nodes_hierarchy']} NHA " .
        
           // NHA - will contain ONLY nodes of type testcase_version that are LINKED to test plan
           " JOIN {$this->tables['testplan_tcversions']} T ON NHA.id = T.tcversion_id " . 
        
           // Get testcase_version data for LINKED VERSIONS
           " JOIN {$this->tables['tcversions']} TCVA ON TCVA.id = T.tcversion_id" .
        
           // Work on Sibblings - Start
           // NHB - Needed to get ALL testcase_version sibblings nodes
           " JOIN {$this->tables['nodes_hierarchy']} NHB ON NHB.parent_id = NHA.parent_id " .
        
           // Want only ACTIVE Sibblings
           " JOIN {$this->tables['tcversions']} TCVB ON TCVB.id = NHB.id AND TCVB.active=1 " . 
           // Work on Sibblings - STOP 
        
           // NHC will contain - nodes of type TESTCASE (parent of testcase versions we are working on)
           // we use NHC to get testcase NAME ( testcase version nodes have EMPTY NAME)
           " JOIN {$this->tables['nodes_hierarchy']} NHC ON NHC.id = NHA.parent_id " .
        
           // Want to get only testcase version with id (NHB.id) greater than linked one (NHA.id)
           " WHERE T.testplan_id={$id} AND NHB.id > NHA.id" . $tc_id_filter .
           " GROUP BY NHA.parent_id, NHC.name, T.tcversion_id, TCVA.tc_external_id, TCVA.version  ";
      
    // BUGID 4682 - phidotnet - Newest version is smaller than Linked version
    $sql2 = " SELECT SUBQ.name, SUBQ.newest_tcversion_id, SUBQ.tc_id, " .
            " SUBQ.tcversion_id, SUBQ.version, SUBQ.tc_external_id, " .
            " TCV.version AS newest_version " .
            " FROM {$this->tables['tcversions']} TCV, ( $sql ) AS SUBQ " .
            " WHERE SUBQ.newest_tcversion_id = TCV.id AND SUBQ.version < TCV.version " .
            " ORDER BY SUBQ.tc_id ";
      
    return $this->db->fetchRowsIntoMap($sql2,'tc_id');
  }


  /**
   * Remove of records from user_assignments table
   * @author franciscom
   * 
   * @param integer $id   : test plan id
   * @param array $items: assoc array key=tc_id value=tcversion_id
   * 
   * @internal revisions:
   *    20100725 - asimon - BUGID 3497 and hopefully also 3530
   */
  function unlink_tcversions($id,&$items)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    if(is_null($items))
    {
      return;
    }
    
    // Get human readeable info for audit
    $gui_cfg = config_get('gui');
    $title_separator = config_get('gui_title_separator_1');
    $auditInfo=$this->tcversionInfoForAudit($id,$items['tcversion']);
    $platformInfo = $this->platform_mgr->getLinkedToTestplanAsMap($id);
    $platformLabel = lang_get('platform');

        $dummy = null;
    foreach($items['items'] as $tcase_id => $elem) 
    {
      foreach($elem as $platform_id => $tcversion_id) 
      {
        $dummy[] = "(tcversion_id = {$tcversion_id} AND platform_id = {$platform_id})";
      }
    }
    $where_clause = implode(" OR ", $dummy);
    
    /*
     * asimon - BUGID 3497 and hopefully also 3530
     * A very litte error, missing braces in the $where_clause, was causing this bug. 
     * When one set of testcases is linked to two testplans, this statement should check 
     * that the combination of testplan_id, tcversion_id and platform_id was the same, 
     * but instead it checked for either testplan_id OR tcversion_id and platform_id.
     * So every linked testcase with fitting tcversion_id and platform_id without execution
     * was deleted, regardless of testplan_id.
     * Simply adding braces around the where clause solves this.
     * So innstead of: 
     * SELECT id AS link_id FROM testplan_tcversions  
     * WHERE testplan_id=12 AND (tcversion_id = 5 AND platform_id = 0) 
     * OR (tcversion_id = 7 AND platform_id = 0) 
     * OR (tcversion_id = 9 AND platform_id = 0) 
     * OR (tcversion_id = 11 AND platform_id = 0)
     * we need this:
     * SELECT ... WHERE testplan_id=12 AND (... OR ...)
     */ 
    $where_clause = " ( {$where_clause} ) ";
    
    // First get the executions id if any exist
    $sql = " SELECT id AS execution_id FROM {$this->tables['executions']} " .
           " WHERE testplan_id = {$id} AND ${where_clause}";
    $exec_ids = $this->db->fetchRowsIntoMap($sql,'execution_id');
    
    if( !is_null($exec_ids) and count($exec_ids) > 0 )
    {
      // has executions
      $exec_ids = array_keys($exec_ids);
      $exec_id_where= " WHERE execution_id IN (" . implode(",",$exec_ids) . ")";
      
      // Remove bugs if any exist
      $sql=" DELETE FROM {$this->tables['execution_bugs']} {$exec_id_where} ";
      $result = $this->db->exec_query($sql);
      
      // now remove executions
      $sql=" DELETE FROM {$this->tables['executions']} " .
         " WHERE testplan_id = {$id} AND ${where_clause}";
      $result = $this->db->exec_query($sql);
    }
    
    // ----------------------------------------------------------------
    // to remove the assignment to users (if any exists) we need the list of id
    $sql=" SELECT id AS link_id FROM {$this->tables['testplan_tcversions']} " .
       " WHERE testplan_id={$id} AND {$where_clause} ";
    $link_ids = $this->db->fetchRowsIntoMap($sql,'link_id');
    $features = array_keys($link_ids);
    if( count($features) == 1)
    {
      $features=$features[0];
    }
    $this->assignment_mgr->delete_by_feature_id($features);
    // ----------------------------------------------------------------
    
    // Delete from link table
    $sql=" DELETE FROM {$this->tables['testplan_tcversions']} " .
       " WHERE testplan_id={$id} AND {$where_clause} ";
    $result = $this->db->exec_query($sql);
    
    foreach($items['items'] as $tcase_id => $elem)
    {
      foreach($elem as $platform_id => $tcversion)
      {
        $addInfo='';
        if( isset($platformInfo[$platform_id]) )
        {
          $addInfo = ' - ' . $platformLabel . ':' . $platformInfo[$platform_id];
        }
        $auditMsg=TLS("audit_tc_removed_from_testplan",
                $auditInfo['tcasePrefix'] . $auditInfo['info'][$tcversion]['tc_external_id'] . 
                $title_separator . $auditInfo['info'][$tcversion]['name'],
                $auditInfo['info'][$tcversion]['version'],
                $auditInfo['tplanInfo']['name'] . $addInfo );
        
        logAuditEvent($auditMsg,"UNASSIGN",$id,"testplans");
      }
    }
    
  } // end function unlink_tcversions



  /**
   * 
   * @internal revisions
   */
  function get_keywords_map($id,$order_by_clause='')
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $map_keywords=null;
    
    // keywords are associated to testcase id,
    // we need to get the list of testcases linked to the testplan
    //
    // After several tests, this seems to be best option
    // Tested on test plan with 14000 test cases, to get 60 keywords. (20120524)
    //
    $sql = " /* $debugMsg */ " .
         " SELECT SQK.keyword_id ,KW.keyword " .
         " FROM {$this->tables['keywords']} KW " .
         " JOIN ( " .
         "    SELECT DISTINCT TCKW.keyword_id  " .
         "     FROM {$this->tables['testplan_tcversions']} TPTCV " .
         "     JOIN {$this->tables['nodes_hierarchy']} NHTC " . 
         "     ON TPTCV.tcversion_id = NHTC.id " .
         "     JOIN {$this->tables['testcase_keywords']} TCKW " .
         "     ON TCKW.testcase_id = NHTC.parent_id " .
         "     WHERE TPTCV.testplan_id = " . intval($id) . 
         "     ) AS SQK " .
         " ON KW.id = SQK.keyword_id " .
         $order_by_clause;
    $map_keywords = $this->db->fetchColumnsIntoMap($sql,'keyword_id','keyword');
  
    return ($map_keywords);
  }
  

/*
  args :
        [$keyword_id]: can be an array
*/
  function get_keywords_tcases($id,$keyword_id=0)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $CUMULATIVE=1;
    $map_keywords=null;
    
    // keywords are associated to testcase id, then first
    // we need to get the list of testcases linked to the testplan
    $linked_items = $this->get_linked_items_id($id);
    if( !is_null($linked_items) )
    {
      $keyword_filter= '' ;
      
      if( is_array($keyword_id) )
      {
        $keyword_filter = " AND keyword_id IN (" . implode(',',$keyword_id) . ")"; 
      }
      else if( $keyword_id > 0 )
      {
        $keyword_filter = " AND keyword_id = {$keyword_id} ";
      }
      
      
      $tc_id_list = implode(",",array_keys($linked_items));
      
      // 20081116 - franciscom -
      // Does DISTINCT is needed ? Humm now I think no.
      $sql = "SELECT DISTINCT testcase_id,keyword_id,keyword
        FROM {$this->tables['testcase_keywords']} testcase_keywords,
        {$this->tables['keywords']} keywords
        WHERE keyword_id = keywords.id
        AND testcase_id IN ( {$tc_id_list} )
        {$keyword_filter}
        ORDER BY keyword ASC ";
      
      // 20081116 - franciscom
      // CUMULATIVE is needed to get all keywords assigned to each testcase linked to testplan         
      $map_keywords = $this->db->fetchRowsIntoMap($sql,'testcase_id',$CUMULATIVE);
    }
    
    return ($map_keywords);
  } // end function


  /*
    function: copy_as
            creates a new test plan using an existent one as source.
  Note:  copy_test_urgency is not appropriate to copy


    args: id: source testplan id
        new_tplan_id: destination
        [tplan_name]: default null.
                      != null => set this as the new name

        [tproject_id]: default null.
                       != null => set this as the new testproject for the testplan
                              this allow us to copy testplans to differents test projects.

        [user_id]
        [options]: default null
                   allowed keys:
                   items2copy: 
                               null: do a deep copy => copy following test plan child elements:
                              builds,linked tcversions,milestones,user_roles,priorities,
                              platforms,execution assignment.
                              
                              != null, a map with keys that controls what child elements to copy

           copy_assigned_to:
           tcversion_type: 
                          null/'current' -> use same version present on source testplan
                                  'lastest' -> for every testcase linked to source testplan
                                               use lastest available version
                                               
       [mappings]: need to be documented                                        
    returns: N/A
    
    
    20101114 - franciscom - Because user assignment is done at BUILD Level, we will force
                BUILD COPY no matter user choice if user choose to copy
                Test Case assignment.
                
                
  */
  function copy_as($id,$new_tplan_id,$tplan_name=null,$tproject_id=null,$user_id=null,
                     $options=null,$mappings=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $cp_methods = array('copy_milestones' => 'copy_milestones',
                        'copy_user_roles' => 'copy_user_roles',
                        'copy_platforms_links' => 'copy_platforms_links',
                        'copy_attachments' => 'copy_attachments');

    $mapping_methods = array('copy_platforms_links' => 'platforms');

    $my['options'] = array();

    // Configure here only elements that has his own table.
    $my['options']['items2copy']= array('copy_tcases' => 1,'copy_milestones' => 1, 'copy_user_roles' => 1, 
                                        'copy_builds' => 1, 'copy_platforms_links' => 1, 
                                        'copy_attachments' => 1, 'copy_priorities' => 1);

    $my['options']['copy_assigned_to'] = 0;
    $my['options']['tcversion_type'] = null;

    $my['options'] = array_merge($my['options'], (array)$options);
    
    $safe['new_tplan_id'] = intval($new_tplan_id);

    // get source testplan general info
    $rs_source=$this->get_by_id($id);
    
    if(!is_null($tplan_name))
    {
      $sql="/* $debugMsg */ UPDATE {$this->tables['nodes_hierarchy']} " .
           "SET name='" . $this->db->prepare_string(trim($tplan_name)) . "' " .
           "WHERE id=" . $safe['new_tplan_id'];
      $this->db->exec_query($sql);
    }
    
    if(!is_null($tproject_id))
    {
      $sql="/* $debugMsg */ UPDATE {$this->tables['testplans']} SET testproject_id={$tproject_id} " .
           "WHERE id=" . $safe['new_tplan_id'];
      $this->db->exec_query($sql);
    }

    // copy builds and tcversions out of following loop, because of the user assignments per build
    // special measures have to be taken
    $build_id_mapping = null;
    if($my['options']['items2copy']['copy_builds']) 
    {
      $build_id_mapping = $this->copy_builds($id,$safe['new_tplan_id']);
    }

    // Important Notice:
    // Since the addition of Platforms, test case versions are linked to Test Plan AND Platforms
    // this means, that not matter user choice, we will force Platforms COPY.
    // This is a lazy approach, instead of complex one that requires understand what Platforms
    // have been used on SOURCE Test Plan.
    //
    // copy test cases is an special copy
    if( $my['options']['items2copy']['copy_tcases'] )
    {
      $my['options']['items2copy']['copy_platforms_links'] = 1;
      $this->copy_linked_tcversions($id,$new_tplan_id,$user_id,$my['options'],$mappings, $build_id_mapping);
    }

    foreach( $my['options']['items2copy'] as $key => $do_copy )
    {
      if( $do_copy )
      {
        if( isset($cp_methods[$key]) )
        {
          $copy_method=$cp_methods[$key];
          if( isset($mapping_methods[$key]) && isset($mappings[$mapping_methods[$key]]))
          {
            $this->$copy_method($id,$new_tplan_id,$mappings[$mapping_methods[$key]]);
          }
          else
          {
            $this->$copy_method($id,$new_tplan_id);
          }  
        }
      }
    }
  } // end function copy_as



  /**
   * $id: source testplan id
   * $new_tplan_id: destination
   */
  private function copy_builds($id,$new_tplan_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $rs=$this->get_builds($id);

    $id_mapping = array();
    if(!is_null($rs))
    {
      foreach($rs as $build)
      {
        $add2sql = '';
        $fields = 'name,notes,';
        if(strlen(trim($build['release_date'])) > 0)
        {
          $fields .= 'release_date,';
          $add2sql = "'" . $this->db->prepare_string($build['release_date']) . "',";
        }       
        $fields .= 'testplan_id';

        $sql = " /* $debugMsg */ INSERT INTO {$this->tables['builds']} " .
               " ({$fields}) " .
               "VALUES ('" . $this->db->prepare_string($build['name']) ."'," .
               "'" . $this->db->prepare_string($build['notes']) . "', {$add2sql} {$new_tplan_id})";
        
        $this->db->exec_query($sql);
        $new_id = $this->db->insert_id($this->tables['builds']);
        $id_mapping[$build['id']] = $new_id;
      }
    }
    return $id_mapping;
  }


  /*
    function: copy_linked_tcversions

    args: id: source testplan id
        new_tplan_id: destination
        [options]
          [tcversion_type]: default null -> use same version present on source testplan
                              'lastest' -> for every testcase linked to source testplan
                                      use lastest available version
          [copy_assigned_to]: 1 -> copy execution assignments without role control                              

    [$mappings] useful when this method is called due to a Test Project COPY AS (yes PROJECT no PLAN)
          
    returns:
  
    Note: test urgency is set to default in the new Test plan (not copied)
    
    @internal revisions
    20110104 - asimon - BUGID 4118: Copy Test plan feature is not copying test cases for all platforms
    20101114 - franciscom - BUGID 4017: Create plan as copy - Priorities are ALWAYS COPIED
  */
  private function copy_linked_tcversions($id,$new_tplan_id,$user_id=-1, $options=null,$mappings=null, $build_id_mapping)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['options']['tcversion_type'] = null;
      $my['options']['copy_assigned_to'] = 0;
    $my['options'] = array_merge($my['options'], (array)$options);
        $now_ts = $this->db->db_now();

    $sql="/* $debugMsg */ "; 
    if($my['options']['copy_assigned_to'])
    {
      $sql .= " SELECT TPTCV.*, COALESCE(UA.user_id,-1) AS tester, " .
          " COALESCE(UA.build_id,0) as assigned_build " .
              " FROM {$this->tables['testplan_tcversions']} TPTCV " .
              " LEFT OUTER JOIN {$this->tables['user_assignments']} UA ON " .
              " UA.feature_id = TPTCV.id " .
              " WHERE testplan_id={$id} ";
    }
    else
    {
      $sql .= " SELECT TPTCV.* FROM {$this->tables['testplan_tcversions']} TPTCV" .
              " WHERE testplan_id={$id} ";
      }

    $rs=$this->db->get_recordset($sql);
    if(!is_null($rs))
    {
      $tcase_mgr = new testcase($this->db);
      $doMappings = !is_null($mappings);
      $already_linked_versions = array();

      foreach($rs as $elem)
      {
        $tcversion_id = $elem['tcversion_id'];
        
        // Seems useless - 20100204
        $feature_id = $elem['id'];
        if( !is_null($my['options']['tcversion_type']) )
        {
          $sql="/* $debugMsg */ SELECT * FROM {$this->tables['nodes_hierarchy']} WHERE id={$tcversion_id} ";
          $rs2=$this->db->get_recordset($sql);
          // Ticket 4696 - if tcversion_type is set to latest -> update linked version
          if ($my['options']['tcversion_type'] == 'latest') 
          {
            $last_version_info = $tcase_mgr->get_last_version_info($rs2[0]['parent_id']);
            $tcversion_id = $last_version_info ? $last_version_info['id'] : $tcversion_id ;
          }
        }
        
        // mapping need to be done with:
        // platforms
        // test case versions
        $platform_id = $elem['platform_id'];
        if( $doMappings )
        {
          if( isset($mappings['platforms'][$platform_id]) )
          {
            $platform_id = $mappings['platforms'][$platform_id]; 
          }
          if( isset($mappings['test_spec'][$tcversion_id]) )
          {
            $tcversion_id = $mappings['test_spec'][$tcversion_id]; 
          }
        }
        
        // Create plan as copy - Priorities are ALWAYS COPIED
        $sql = "/* $debugMsg */ " . 
               " INSERT INTO {$this->tables['testplan_tcversions']} " .
             " (testplan_id,tcversion_id,platform_id,node_order ";
        $sql_values  = " VALUES({$new_tplan_id},{$tcversion_id},{$platform_id}," .
                    " {$elem['node_order']} ";
                    
        if($my['options']['items2copy']['copy_priorities'])
        {
          $sql .= ",urgency ";
          $sql_values  .= ",{$elem['urgency']}";
        }
        $sql .= " ) " . $sql_values . " ) ";  
             
        // to avoid warnings
        $doIt = !isset($already_linked_versions[$platform_id]);
        if ($doIt || !in_array($tcversion_id, $already_linked_versions[$platform_id])) 
        {
          $this->db->exec_query($sql);
          $new_feature_id = $this->db->insert_id($this->tables['testplan_tcversions']);
          $already_linked_versions[$platform_id][] = $tcversion_id;
        }
        
        if($my['options']['copy_assigned_to'] && $elem['tester'] > 0)
        {
          $features_map = array();
          $feature_id=$new_feature_id;
          $features_map[$feature_id]['user_id'] = $elem['tester'];
          $features_map[$feature_id]['build_id'] = $build_id_mapping[$elem['assigned_build']];
          $features_map[$feature_id]['type'] = $this->assignment_types['testcase_execution']['id'];
          $features_map[$feature_id]['status']  = $this->assignment_status['open']['id'];
          $features_map[$feature_id]['creation_ts'] = $now_ts;
          $features_map[$feature_id]['assigner_id'] = $user_id;

          if ($features_map[$feature_id]['build_id'] != 0) {
            $this->assignment_mgr->assign($features_map);
          }
        }
        
      }
    }
  }


/*
  function: copy_milestones

  args: id: source testplan id
        new_tplan_id: destination

  returns:

  rev : 
        20090910 - franciscom - added start_date
        
        20070519 - franciscom
        changed date to target_date, because date is an Oracle reverved word.
*/
  private function copy_milestones($tplan_id,$new_tplan_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $rs=$this->get_milestones($tplan_id);
    if(!is_null($rs))
    {
      foreach($rs as $mstone)
      {
        // BUGID 3430 - need to check if start date is NOT NULL
        $add2fields = '';
        $add2values = '';
        $use_start_date = strlen(trim($mstone['start_date'])) > 0;
        if( $use_start_date )
        {
          $add2fields = 'start_date,';
          $add2values = "'" . $mstone['start_date'] . "',";
        }

        $sql = "INSERT INTO {$this->tables['milestones']} (name,a,b,c,target_date,{$add2fields} testplan_id)";        
                $sql .= " VALUES ('" . $this->db->prepare_string($mstone['name']) ."'," .
              $mstone['high_percentage'] . "," . $mstone['medium_percentage'] . "," . 
              $mstone['low_percentage'] . ",'" . $mstone['target_date'] . "', {$add2values}{$new_tplan_id})";
        $this->db->exec_query($sql);
      }
    }
  }


  /**
   * Get all milestones for a Test Plan
   * @param int $tplan_id Test Plan identificator
   * @return array of arrays TBD fields description 
   */
  function get_milestones($tplan_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $sql=" /* $debugMsg */ SELECT id, name, a AS high_percentage, b AS medium_percentage, c AS low_percentage, " .
         "target_date, start_date,testplan_id " .       
         "FROM {$this->tables['milestones']} " .
         "WHERE testplan_id={$tplan_id} ORDER BY target_date,name";
    return $this->db->get_recordset($sql);
  }


  /**
   * Copy user roles to a new Test Plan
   * 
   * @param int $source_id original Test Plan id
   * @param int $target_id new Test Plan id
   */
  private function copy_user_roles($source_id, $target_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ SELECT user_id,role_id FROM {$this->tables['user_testplan_roles']} " .
           " WHERE testplan_id={$source_id} ";
    $rs = $this->db->get_recordset($sql);
    if(!is_null($rs))
    {
        foreach($rs as $elem)
        {
            $sql="INSERT INTO {$this->tables['user_testplan_roles']}  " .
                 "(testplan_id,user_id,role_id) " .
                 "VALUES({$target_id}," . $elem['user_id'] ."," . $elem['role_id'] . ")";
            $this->db->exec_query($sql);
      }
    }
  }


  /**
   * Gets all testplan related user roles
   *
   * @param integer $id the testplan id
   * @return array assoc map with keys taken from the user_id column
   **/
  function getUserRoleIDs($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = " /* $debugMsg */ SELECT user_id,role_id FROM {$this->tables['user_testplan_roles']} " .
           "WHERE testplan_id = {$id}";
    $roles = $this->db->fetchRowsIntoMap($sql,'user_id');
    return $roles;
  }


  /**
   * Inserts a testplan related role for a given user
   *
   * @param int $userID the id of the user
   * @param int $id the testplan id
   * @param int $roleID the role id
   * 
   * @return integer returns tl::OK on success, tl::ERROR else
   **/
  
  function addUserRole($userID,$id,$roleID)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $status = tl::ERROR;
    $sql = " /* $debugMsg */ INSERT INTO {$this->tables['user_testplan_roles']} (user_id,testplan_id,role_id) VALUES " .
         " ({$userID},{$id},{$roleID})";
    if ($this->db->exec_query($sql))
    {
      $testPlan = $this->get_by_id($id);
      $role = tlRole::getByID($this->db,$roleID,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
      $user = tlUser::getByID($this->db,$userID,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
      if ($user && $testPlan && $role)
      {
        logAuditEvent(TLS("audit_users_roles_added_testplan",$user->getDisplayName(),
                      $testPlan['name'],$role->name),"ASSIGN",$id,"testplans");
      }
      $status = tl::OK;
    }
    return $status;
  }


  /**
   * Deletes all testplan related role assignments for a given testplan
   *
   * @param int $id the testplan id
   * @return tl::OK  on success, tl::FALSE else
   **/
  function deleteUserRoles($id,$users=null,$opt=null)
  {
    $my['opt'] = array('auditlog' => true);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $status = tl::ERROR;
    $sql = " /* $debugMsg */ DELETE FROM {$this->tables['user_testplan_roles']} " .
           " WHERE testplan_id = " . intval($id);
    if(!is_null($users))
    {
      $sql .= " AND user_id IN(" . implode(',',$users) . ")";
    } 

    if ($this->db->exec_query($sql) && $my['opt']['auditlog'])
    {
      $testPlan = $this->get_by_id($id);
      if ($testPlan)
      {
        if(is_null($users))
        {
          logAuditEvent(TLS("audit_all_user_roles_removed_testplan",
                        $testPlan['name']),"ASSIGN",$id,"testplans");
        }
        else
        {
          // TBD
        }  
      }
      $status = tl::OK;
    }
    return $status;
  }


  /**
   * Delete test plan and all related link to other items
   *
    */
  function delete($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $the_sql=array();
    $main_sql=array();
    
    $this->deleteUserRoles($id);
    $getFeaturesSQL = " /* $debugMsg */ SELECT id FROM {$this->tables['testplan_tcversions']} WHERE testplan_id={$id} "; 
    $the_sql[]="DELETE FROM {$this->tables['milestones']} WHERE testplan_id={$id}";
    
    // CF used on testplan_design are linked by testplan_tcversions.id
    $the_sql[]="DELETE FROM {$this->tables['cfield_testplan_design_values']} WHERE link_id ".
             "IN ({$getFeaturesSQL})";

    $the_sql[]="DELETE FROM {$this->tables['user_assignments']} WHERE feature_id ".
             "IN ({$getFeaturesSQL})";
    
    $the_sql[]="DELETE FROM {$this->tables['testplan_platforms']} WHERE testplan_id={$id}";

    $the_sql[]="DELETE FROM {$this->tables['testplan_tcversions']} WHERE testplan_id={$id}";

    $the_sql[]="DELETE FROM {$this->tables['cfield_execution_values']} WHERE testplan_id={$id}";
    $the_sql[]="DELETE FROM {$this->tables['user_testplan_roles']} WHERE testplan_id={$id}";
    
    
    // When deleting from executions, we need to clean related tables
    $the_sql[]="DELETE FROM {$this->tables['execution_bugs']} WHERE execution_id ".
           "IN (SELECT id FROM {$this->tables['executions']} WHERE testplan_id={$id})";
    $the_sql[]="DELETE FROM {$this->tables['executions']} WHERE testplan_id={$id}";
    $the_sql[]="DELETE FROM {$this->tables['builds']} WHERE testplan_id={$id}"; //BUGID 3845    
    
    foreach($the_sql as $sql)
    {
      $this->db->exec_query($sql);
    }
    
    $this->deleteAttachments($id);
    
    $this->cfield_mgr->remove_all_design_values_from_node($id);
    // ------------------------------------------------------------------------
    
    // Finally delete from main table
    $main_sql[]="DELETE FROM {$this->tables['testplans']} WHERE id={$id}";
    $main_sql[]="DELETE FROM {$this->tables['nodes_hierarchy']} " . 
                "WHERE id={$id} AND node_type_id=" . 
                $this->node_types_descr_id['testplan'];
    
    foreach($main_sql as $sql)
    {
      $this->db->exec_query($sql);
    }
  } // end delete()



  // --------------------------------------------------------------------------------------
  // Build related methods
  // --------------------------------------------------------------------------------------
  
  /*
    function: get_builds_for_html_options()
  
  
    args :
          $id     : test plan id.
          [active]: default:null -> all, 1 -> active, 0 -> inactive BUILDS
          [open]  : default:null -> all, 1 -> open  , 0 -> closed/completed BUILDS
          [opt]
  
    returns:
  
    rev :
  */
  function get_builds_for_html_options($id,$active=null,$open=null,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my = array();
    $my['opt'] = array('orderByDir' => null,'excludeBuild' => 0);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $sql = " /* $debugMsg */ SELECT id, name " .
           " FROM {$this->tables['builds']} WHERE testplan_id = {$id} ";
    
    if( !is_null($active) )
    {
      $sql .= " AND active=" . intval($active) . " ";
    }
    
    if( !is_null($open) )
    {
      $sql .= " AND is_open=" . intval($open) . " ";
    }
    
    if( $my['opt']['excludeBuild'] > 0)
    {
      $sql .= " AND id <> " . intval($my['opt']['excludeBuild']) . " ";      
    }

    $orderClause = " ORDER BY name ASC";
    if( !is_null($my['opt']['orderByDir']) )
    {
      $xx = explode(':',$my['opt']['orderByDir']);
      $orderClause = 'ORDER BY ' . $xx[0] . ' ' . $xx[1];
    }  
    $sql .= $orderClause;
    
    $recordset=$this->db->fetchColumnsIntoMap($sql,'id','name');

    // we will apply natsort only if order by name was requested
    if( !is_null($recordset) && stripos($orderClause, 'name') !== FALSE)
    {
      natsort($recordset);
    }
    
    return $recordset;
  }


  /*
    function: get_max_build_id
  
    args :
          $id     : test plan id.
  
    returns:
  */
  function get_max_build_id($id,$active = null,$open = null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = " /* $debugMsg */ SELECT MAX(id) AS maxbuildid " .
      " FROM {$this->tables['builds']} " .
      " WHERE testplan_id = {$id}";
    
    if(!is_null($active))
    {
      $sql .= " AND active = " . intval($active) . " ";
    }
    if( !is_null($open) )
    {
      $sql .= " AND is_open = " . intval($open) . " ";
    }
    
    $recordset = $this->db->get_recordset($sql);
    $maxBuildID = 0;
    if ($recordset)
    {
      $maxBuildID = intval($recordset[0]['maxbuildid']);
    }
    return $maxBuildID;
  }

  /*
     function: get_testsuites
      args :
    $id     : test plan id.
      returns: returns flat list of names of test suites (including nest test suites)  No particular Order.
  */
  function get_testsuites($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = " /* $debugMsg */ SELECT NHTSUITE.name, NHTSUITE.id, NHTSUITE.parent_id" . 
         " FROM {$this->tables['testplan_tcversions']}  TPTCV, {$this->tables['nodes_hierarchy']}  NHTCV, " .
         " {$this->tables['nodes_hierarchy']} NHTCASE, {$this->tables['nodes_hierarchy']} NHTSUITE " . 
         " WHERE TPTCV.tcversion_id = NHTCV.id " .
         " AND NHTCV.parent_id = NHTCASE.id " .
         " AND NHTCASE.parent_id = NHTSUITE.id " .
         " AND TPTCV.testplan_id = " . $id . " " .
         " GROUP BY NHTSUITE.name,NHTSUITE.id,NHTSUITE.parent_id " .
         " ORDER BY NHTSUITE.name" ;
    
    $recordset = $this->db->get_recordset($sql);
    
    // Now the recordset contains testsuites that have child test cases.
    // However there could potentially be testsuites that only have grandchildren/greatgrandchildren
    // this will iterate through found test suites and check for 
    $superset = $recordset;
    foreach($recordset as $value)
    {
      $superset = array_merge($superset, $this->get_parenttestsuites($value['id']));
    }    
    
    // At this point there may be duplicates
    $dup_track = array();
    foreach($superset as $value)
    {
      if (!array_key_exists($value['id'],$dup_track))
      {
        $dup_track[$value['id']] = true;
        $finalset[] = $value;
      }        
    }    
    
    // Needs to be alphabetical based upon name attribute 
    usort($finalset, array("testplan", "compare_name"));
    return $finalset;
  }


  /*
   function: compare_name
  Used for sorting a list by nest name attribute
  
    args :
    $a     : first array to compare
    $b       : second array to compare
    
    returns: an integer indicating the result of the comparison
   */
  static private function compare_name($a, $b)
  {
      return strcasecmp($a['name'], $b['name']);
  }


  /*
   function: get_parenttestsuites
  
  Used by get_testsuites
   
  Recursive function used to get all the parent test suites of potentially testcase free testsuites.
  If passed node id isn't the product then it's merged into result set.
  
    args :
    $id     : $id of potential testsuite
    
    returns: an array of all testsuite ancestors of $id
   */
  private function get_parenttestsuites($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

      $sql = " /* $debugMsg */ SELECT name, id, parent_id " .
           "FROM {$this->tables['nodes_hierarchy']}  NH " .
           "WHERE NH.node_type_id <> {$this->node_types_descr_id['testproject']} " .
           "AND NH.id = " . $id;
        
      $recordset = $this->db->get_recordset($sql);
      $myarray = array();
      if (count($recordset) > 0)
      {        
        $myarray = array($recordset[0]);
        $myarray = array_merge($myarray, $this->get_parenttestsuites($recordset[0]['parent_id'])); 
      }
      
      return $myarray;            
  }


  /*
    function: get_builds
              get info about builds defined for a testlan.
              Build can be filtered by active and open status.
  
    args :
          id: test plan id.
          [active]: default:null -> all, 1 -> active, 0 -> inactive BUILDS
          [open]: default:null -> all, 1 -> open  , 0 -> closed/completed BUILDS
          [opt]

    returns: opt['getCount'] == false
               map, where elements are ordered by build name, using variant of nasort php function.
               key: build id
               value: map with following keys
                      id: build id
                      name: build name
                      notes: build notes
                      active: build active status
                      is_open: build open status
                      testplan_id
                      release_date

             opt['getCount'] == true
               map key: test plan id
                   values: map with following key testplan_id, build_qty 
    rev :
  */
  function get_builds($id,$active=null,$open=null,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['opt'] = array('fields' => 
                       'id,testplan_id, name, notes, active, is_open,release_date,closed_on_date,creation_ts',
                       'orderBy' => " ORDER BY name ASC", 'getCount' => false, 'buildID' => null);

    $my['opt'] = array_merge($my['opt'],(array)$opt);
    if( $my['opt']['getCount'] )
    {
      $my['opt']['orderBy'] = null;

      $accessField = 'testplan_id';     
      $groupBy = " GROUP BY testplan_id ";
      $itemSet = (array)$id;

      $sql = " /* $debugMsg */ " . 
           " SELECT testplan_id, count(0) AS build_qty " .
           " FROM {$this->tables['builds']} " .
           " WHERE testplan_id IN ('" . implode("','", $itemSet) . "') "; 
    }  
    else
    {
      $accessField = 'id';     
      $groupBy = '';
      $sql = " /* $debugMsg */ " . 
             " SELECT {$my['opt']['fields']} " .
             " FROM {$this->tables['builds']} WHERE testplan_id = {$id} " ;
      
      if( !is_null($my['opt']['buildID']) )
      {
        $sql .= " AND id=" . intval($my['opt']['buildID']) . " ";
      }
    }  


    if( !is_null($active) )
    {
      $sql .= " AND active=" . intval($active) . " ";
    }
    if( !is_null($open) )
    {
      $sql .= " AND is_open=" . intval($open) . " ";
    }
    
    $sql .= $groupBy;
    $sql .= ($doOrderBy = !is_null($my['opt']['orderBy'])) ? $my['opt']['orderBy'] : '';
    
    $rs = $this->db->fetchRowsIntoMap($sql,$accessField);

    // _natsort_builds() has to be used ONLY if name is used on ORDER BY
    if( !is_null($rs) && $doOrderBy && strpos($my['opt']['orderBy'],'name') !== FALSE)
    {
      $rs = $this->_natsort_builds($rs);
    }
    
    return $rs;
  }


  /**
   * Get a build belonging to a test plan, using build name as access key
   *
   * @param int $id test plan id
   * @param string $build_name
   * 
   * @return array [id,testplan_id, name, notes, active, is_open]
   */
  function get_build_by_name($id,$build_name)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $safe_build_name=$this->db->prepare_string(trim($build_name));
    
    $sql = " /* $debugMsg */ SELECT id,testplan_id, name, notes, active, is_open " .
      " FROM {$this->tables['builds']} " .
      " WHERE testplan_id = {$id} AND name='{$safe_build_name}'";
    
    
    $recordset = $this->db->get_recordset($sql);
    $rs=null;
    if( !is_null($recordset) )
    {
      $rs=$recordset[0];
    }
    return $rs;
  }


  /**
   * Get a build belonging to a test plan, using build id as access key
   *
   * @param int $id test plan id
   * @param int $build_id
   *
   * @return array [id,testplan_id, name, notes, active, is_open]
   */
  function get_build_by_id($id,$build_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = " /* $debugMsg */ SELECT id,testplan_id, name, notes, active, is_open " .
      " FROM {$this->tables['builds']} BUILDS " .
      " WHERE testplan_id = {$id} AND BUILDS.id={$build_id}";
    
    $recordset = $this->db->get_recordset($sql);
    $rs=null;
    if( !is_null($recordset) )
    {
      $rs=$recordset[0];
    }
    return $rs;
  }


  /**
   * Get the number of builds of a given Testplan
   *
   * @param int tplanID test plan id
   *
   * @return int number of builds
   * 
   * @internal revisions:
   * 20100217 - asimon - added parameters active and open to get only number of active/open builds
   */
  function getNumberOfBuilds($tplanID, $active = null, $open = null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $sql = "/* $debugMsg */ SELECT count(id) AS num_builds FROM {$this->tables['builds']} builds " .
             "WHERE builds.testplan_id = " . $tplanID;
    
    if( !is_null($active) )
     {
        $sql .= " AND builds.active=" . intval($active) . " ";
     }
     if( !is_null($open) )
     {
        $sql .= " AND builds.is_open=" . intval($open) . " ";
     }
    
     return $this->db->fetchOneValue($sql);
  }

  /**
   *
   */
  function _natsort_builds($builds_map)
  {
    // BUGID - sort in natural order (see natsort in PHP manual)
    foreach($builds_map as $key => $value)
    {
      $vk[$value['name']]=$key;
      $build_names[$key]=$value['name'];
    }
    
    natsort($build_names);
    $build_num=count($builds_map);
    foreach($build_names as $key => $value)
    {
      $dummy[$key]=$builds_map[$key];
    }
    return $dummy;
  }


  /*
    function: check_build_name_existence
  
    args:
         tplan_id: test plan id.
         build_name
        [build_id}: default: null
                    when is not null we add build_id as filter, this is useful
                    to understand if is really a duplicate when using this method
                    while managing update operations via GUI
  
    returns: 1 => name exists
  
    rev: 20080217 - franciscom - added build_id argument
  
  */
  function check_build_name_existence($tplan_id,$build_name,$build_id=null,$case_sensitive=0)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = " /* $debugMsg */ SELECT id, name, notes " .
      " FROM {$this->tables['builds']} " .
      " WHERE testplan_id = {$tplan_id} ";
    
    
    if($case_sensitive)
    {
      $sql .= " AND name=";
    }
    else
    {
      $build_name=strtoupper($build_name);
      $sql .= " AND UPPER(name)=";
    }
    $sql .= "'" . $this->db->prepare_string($build_name) . "'";
    
    if( !is_null($build_id) )
    {
      $sql .= " AND id <> " . $this->db->prepare_int($build_id);
    }
    
    
    $result = $this->db->exec_query($sql);
    $status= $this->db->num_rows($result) ? 1 : 0;
    
    return $status;
  }


  /*
    function: get_build_id_by_name
  
  Ignores case
  
    args :
    $tplan_id     : test plan id. 
    $build_name   : build name. 
    
    returns: 
    The ID of the build name specified regardless of case.
  
    rev :
  */
  function get_build_id_by_name($tplan_id,$build_name)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = " /* $debugMsg */ SELECT builds.id, builds.name, builds.notes " .
      " FROM {$this->tables['builds']} builds " .
      " WHERE builds.testplan_id = {$tplan_id} ";
    
    $build_name=strtoupper($build_name);        
    $sql .= " AND UPPER(builds.name)=";
    $sql .= "'" . $this->db->prepare_string($build_name) . "'";    
    
    $recordset = $this->db->get_recordset($sql);
    $BuildID = $recordset ? intval($recordset[0]['id']) : 0;
    
    return $BuildID;  
  }


  // --------------------------------------------------------------------------------------
  // Custom field related methods
  // --------------------------------------------------------------------------------------
  /*
    function: get_linked_cfields_at_design
  
    args: $id
          [$parent_id]: testproject id
          [$show_on_execution]: default: null
                                1 -> filter on field show_on_execution=1
                                0 or null -> don't filter
  
    returns: hash
  
    rev :
  */
  function get_linked_cfields_at_design($id,$parent_id=null,$show_on_execution=null)
  {
    $path_len=0;
    if( is_null($parent_id) )
    {
      // Need to get testplan parent (testproject id) in order to get custom fields
      // 20081122 - franciscom - need to check when we can call this with ID=NULL
      $the_path = $this->tree_manager->get_path(!is_null($id) ? $id : $parent_id);
      $path_len = count($the_path);
    }
    $tproject_id = ($path_len > 0)? $the_path[$path_len-1]['parent_id'] : $parent_id; 
    
    $cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,self::ENABLED,
                                                            $show_on_execution,'testplan',$id);
    
    return $cf_map;
  }


  /*
    function: get_linked_cfields_at_execution
  
    args: $id
          [$parent_id]: if present is testproject id
          [$show_on_execution]: default: null
                                1 -> filter on field show_on_execution=1
                                0 or null -> don't filter
  
    returns: hash
  
    rev :
  */
  function get_linked_cfields_at_execution($id,$parent_id=null,$show_on_execution=null)
  {
    $path_len=0;
    if( is_null($parent_id) )
    {
      // Need to get testplan parent (testproject id) in order to get custom fields
      // 20081122 - franciscom - need to check when we can call this with ID=NULL
      $the_path = $this->tree_manager->get_path(!is_null($id) ? $id : $parent_id);
      $path_len = count($the_path);
    }
    $tproject_id = ($path_len > 0)? $the_path[$path_len-1]['parent_id'] : $parent_id; 
    
    // 20081122 - franciscom - humm!! need to look better IMHO this call is done to wrong function
    $cf_map=$this->cfield_mgr->get_linked_cfields_at_execution($tproject_id,self::ENABLED,
    $show_on_execution,'testplan',$id);
    return($cf_map);
  }


  /* Get Custom Fields  Detail which are enabled on Execution of a TestCase/TestProject.
    function: get_linked_cfields_id
  
    args: $testproject_id 
  
    returns: hash map of id : label
  
    rev :
  
  */
  
  function get_linked_cfields_id($tproject_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $field_map = new stdClass();
    
    $sql = " /* $debugMsg */ SELECT field_id,label
      FROM {$this->tables['cfield_testprojects']} cfield_testprojects, 
      {$this->tables['custom_fields']} custom_fields
      WHERE
      custom_fields.id = cfield_testprojects.field_id 
      and cfield_testprojects.active = 1 
      and custom_fields.enable_on_execution = 1 
      and custom_fields.show_on_execution = 1 
      and cfield_testprojects.testproject_id = " . $this->db->prepare_int($tproject_id) .
      "order by field_id";
    
    $field_map = $this->db->fetchColumnsIntoMap($sql,'field_id','label');
    return($field_map);
  }

  /*
    function: html_table_of_custom_field_inputs
              
              
    args: $id
          [$parent_id]: need when you call this method during the creation
                        of a test suite, because the $id will be 0 or null.
                        
          [$scope]: 'design','execution'
          
    returns: html string
    
  */
  function html_table_of_custom_field_inputs($id,$parent_id=null,$scope='design',$name_suffix='',$input_values=null) 
  {
    $cf_smarty='';
    $method_suffix = $scope=='design' ? $scope : 'execution';
    $method_name = "get_linked_cfields_at_{$method_suffix}";
    $cf_map=$this->$method_name($id,$parent_id);

    if(!is_null($cf_map))
    {
      $cf_smarty = $this->cfield_mgr->html_table_inputs($cf_map,$name_suffix,$input_values);
    }
    return($cf_smarty);
  }


  /*
    function: html_table_of_custom_field_values
  
    args: $id
          [$scope]: 'design','execution'
          
          [$filters]:default: null
                              
                             map with keys:
          
                             [show_on_execution]: default: null
                                                  1 -> filter on field show_on_execution=1
                                                       include ONLY custom fields that can be viewed
                                                       while user is execution testcases.
                             
                                                  0 or null -> don't filter
  
    returns: html string
  
    rev :
         20080811 - franciscom - BUGID 1650 (REQ)
         20070701 - franciscom - fixed return string when there are no custom fields.
  */
  function html_table_of_custom_field_values($id,$scope='design',$filters=null,$formatOptions=null)
  {
    $cf_smarty='';
    $parent_id=null;
    $label_css_style=' class="labelHolder" ' ;
    $value_css_style = ' ';

    $add_table=true;
    $table_style='';
    if( !is_null($formatOptions) )
    {
      $label_css_style = isset($formatOptions['label_css_style']) ? $formatOptions['label_css_style'] : $label_css_style;
      $value_css_style = isset($formatOptions['value_css_style']) ? $formatOptions['value_css_style'] : $value_css_style;

      $add_table=isset($formatOptions['add_table']) ? $formatOptions['add_table'] : true;
      $table_style=isset($formatOptions['table_css_style']) ? $formatOptions['table_css_style'] : $table_style;
    } 
    
    $show_cf = config_get('custom_fields')->show_custom_fields_without_value;
    if( $scope=='design' )
    {
      $cf_map=$this->get_linked_cfields_at_design($id,$parent_id,$filters);
    }
    else
    {
      $cf_map=$this->get_linked_cfields_at_execution($id);
    }
    
    if( !is_null($cf_map) )
    {
      foreach($cf_map as $cf_id => $cf_info)
      {
        // if user has assigned a value, then node_id is not null
        // BUGID 3989
        if(isset($cf_info['node_id']) || $cf_info['node_id'] || $show_cf)
        {
          // true => do not create input in audit log
          $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label'],null,true));
          $cf_smarty .= "<tr><td {$label_css_style}>" . htmlspecialchars($label) . "</td>" .
                  "<td {$value_css_style}>" .
                      $this->cfield_mgr->string_custom_field_value($cf_info,$id) . "</td></tr>\n";
        }
      }
    }
    
    if($cf_smarty != '' && $add_table)
    {
      $cf_smarty = "<table {$table_style}>" . $cf_smarty . "</table>";
    }
    return($cf_smarty);
  } // function end


  /*
    function: filterByOnDesignCustomFields
              Filter on values of custom fields that are managed 
              ON DESIGN Area (i.e. when creating Test Specification).
  
    @used by getLinkedItems() in file execSetResults.php
    
    args :
          $tp_tcs - key: test case ID
                    value: map with keys tcase_id,tcversion_id,...

          $cf_hash [cf_id] = value of cfields to filter by.
  
    returns: array filtered by selected custom fields.
  
    @internal revisions
    
  */
  function filterByOnDesignCustomFields($tp_tcs, $cf_hash)  
  {
    $new_tp_tcs = null;
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $or_clause = '';
    $cf_query = '';
    $ignored = 0;
    $doFilter = false;
    $doIt = true;
    
    if (isset($cf_hash)) 
    {
      $countmain = 1;
      foreach ($cf_hash as $cf_id => $cf_value) 
      {
        // single value or array?
        if (is_array($cf_value)) 
        {
          $count = 1;
          $cf_query .= $or_clause;
          foreach ($cf_value as $value) 
          {
            if ($count > 1) 
            {
              $cf_query .= " AND ";
            }
            $cf_query .= " ( CFD.value LIKE '%{$value}%' AND CFD.field_id = {$cf_id} )";
            $count++;
          }
        } 
        else 
        {
          // Because cf value can NOT exists on DB depending on system config.
          if( trim($cf_value) != '')
          {
            $cf_query .= $or_clause;
            $cf_query .= " ( CFD.value LIKE '%{$cf_value}%' AND CFD.field_id = {$cf_id} ) ";
          }  
          else
          {
            $ignored++;
          }  
        }

        if($or_clause == '')
        {
          $or_clause = ' OR ';
        }
      }
      
      // grand finale
      if( $cf_query != '')
      {
        $cf_query =  " AND ({$cf_query}) ";
        $doFilter = true;
      }  
    }
    $cf_qty = count($cf_hash) - $ignored;                                      
    $doIt = !$doFilter;
    foreach ($tp_tcs as $tc_id => $tc_value)
    {
      if( $doFilter )
      {
        $sql = " /* $debugMsg */ SELECT CFD.value FROM {$this->tables['cfield_design_values']} CFD," .
               " {$this->tables['nodes_hierarchy']} NH" .
               " WHERE CFD.node_id = NH.id " .
               " AND NH.parent_id = {$tc_value['tcase_id']} " .
               " {$cf_query} ";

        $rows = $this->db->fetchColumnsIntoArray($sql,'value'); //BUGID 4115
      
        // if there exist as many rows as custom fields to be filtered by => tc does meet the criteria
        // TO CHECK - 20140126 - Give a look to treeMenu.inc.php - filter_by_cf_values()  
        // to understand if both logics are coerent.
        //
        $doIt = (count($rows) == $cf_qty);
      }  
      if( $doIt ) 
      {
        $new_tp_tcs[$tc_id] = $tp_tcs[$tc_id];
      }
    }
    return ($new_tp_tcs);
  }





  /*
    function: get_estimated_execution_time
  
              Takes all testcases linked to testplan and computes
              SUM of values assigned AT DESIGN TIME to customa field
              named CF_ESTIMATED_EXEC_TIME
  
              IMPORTANT:
              1. at time of this writting (20080820) this CF can be of type: string,numeric or float.
              2. YOU NEED TO USE . (dot) as decimal separator (US decimal separator?) or
                 sum will be wrong. 
           
              
              
    args:id testplan id
         itemSet: default null  - can be an arry with test case VERSION ID
  
    returns: sum of CF values for all testcases linked to testplan
  
    rev: 
                        
  */
  function get_estimated_execution_time($id,$itemSet=null,$platformID=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $estimated = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);

    // check if cf exist and is assigned and active intest plan parent (TEST PROJECT)
    $pinfo = $this->tree_manager->get_node_hierarchy_info($id);
    $cf_info = $this->cfield_mgr->get_linked_to_testproject($pinfo['parent_id'],1,array('name' => 'CF_ESTIMATED_EXEC_TIME'));
    if( is_null($cf_info) )
    {
      return $this->getEstimatedExecutionTime($id,$itemSet,$platformID);
    }  
    else
    {
      return $this->getEstimatedExecutionTimeFromCF($id,$itemSet,$platformID);
    } 

  } 

  /**
   *
   */
  function getEstimatedExecutionTime($id,$itemSet=null,$platformID=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $estimated = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);

    $tcVersionIDSet = array();
    $getOpt = array('outputFormat' => 'mapAccessByID' , 'addIfNull' => true);
    $platformSet = array_keys($this->getPlatforms($id,$getOpt));

    if( is_null($itemSet) )
    {
      // we need to loop over all linked PLATFORMS (if any)
      $tcVersionIDSet = array();
      foreach($platformSet as $platfID)
      {
        if(is_null($platformID) || $platformID == $platfID )
        { 
          $linkedItems = $this->get_linked_tcvid($id,$platfID,array('addEstimatedExecDuration' => true));  
          if( (!is_null($linkedItems)) )
          {
            $tcVersionIDSet[$platfID]= $linkedItems;
          }
        }  
      }
    }
    else
    {
      // Important NOTICE
      // we can found SOME LIMITS on number of elements on IN CLAUSE
      // need to make as many set as platforms linked to test plan
      $sql4tplantcv = " /* $debugMsg */ SELECT tcversion_id, platform_id,TCV.estimated_exec_duration " .
                      " FROM {$this->tables['testplan_tcversions']} " .
                      " JOIN {$this->tables['tcversions']} TCV ON TCV.id = tcversion_id " .
                      " WHERE testplan_id=" . intval($id)  .
                      " AND tcversion_id IN (" . implode(',',$itemSet) . ")";
      if( !is_null($platformID) )
      {
        $sql4tplantcv .= " AND platform_id= " . intval($platformID);
      }        
      
      $rs = $this->db->fetchRowsIntoMap($sql4tplantcv,'platform_id',database::CUMULATIVE);
      foreach($rs as $platfID => $elem)
      {
        $tcVersionIDSet[$platfID] = $elem;    
      }  
    }

    $estimated = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);
    foreach($tcVersionIDSet as $platfID => $items)
    {  
      $estimated['platform'][$platfID]['minutes'] = 0;
      $estimated['platform'][$platfID]['tcase_qty'] = count($items);
      foreach($items as $dx)
      {
        if(!is_null($dx['estimated_exec_duration']))
        {  
          $estimated['platform'][$platfID]['minutes'] += $dx['estimated_exec_duration'];
        }  
      }  
      $estimated['totalMinutes'] += $estimated['platform'][$platfID]['minutes'];
      $estimated['totalTestCases'] += $estimated['platform'][$platfID]['tcase_qty'];
    }  

    return $estimated;
  }


  /**
   *
   */
  function getEstimatedExecutionTimeFromCF($id,$itemSet=null,$platformID=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $estimated = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);
    $cf_info = $this->cfield_mgr->get_by_name('CF_ESTIMATED_EXEC_TIME');
    
    // CF exists ?
    if( ($status_ok=!is_null($cf_info)) )
    {
      $cfield_id=key($cf_info);
    }

    if( $status_ok)
    {
      $tcVersionIDSet = array();
      $getOpt = array('outputFormat' => 'mapAccessByID' , 'addIfNull' => true);
      $platformSet = array_keys($this->getPlatforms($id,$getOpt));
      
      $sql = " /* $debugMsg */ ";
      if( DB_TYPE == 'mysql')
      {
        $sql .= " SELECT SUM(value) ";
      } 
      else if ( DB_TYPE == 'postgres' || DB_TYPE == 'mssql' )
      {
        $sql .= " SELECT SUM(CAST(value AS NUMERIC)) ";
      }       
      
      $sql .= " AS SUM_VALUE FROM {$this->tables['cfield_design_values']} CFDV " .
              " WHERE CFDV.field_id={$cfield_id} ";

      if( is_null($itemSet) )
      {
        // 20110112 - franciscom
        // we need to loop over all linked PLATFORMS (if any)
        $tcVersionIDSet = array();
        foreach($platformSet as $platfID)
        {
          if(is_null($platformID) || $platformID == $platfID )
          { 
            $linkedItems = $this->get_linked_tcvid($id,$platfID);  
            if( (!is_null($linkedItems)) )
            {
              $tcVersionIDSet[$platfID]= array_keys($linkedItems);
            }
          }  
        }
      }
      else
      {
        // Important NOTICE
        // we can found SOME LIMITS on number of elements on IN CLAUSE
        //
        // need to make as many set as platforms linked to test plan
        $sql4tplantcv = " /* $debugMsg */ SELECT tcversion_id, platform_id " .
                " FROM {$this->tables['testplan_tcversions']} " .
                " WHERE testplan_id=" . intval($id)  .
                " AND tcversion_id IN (" . implode(',',$itemSet) . ")";
        
        if( !is_null($platformID) )
        {
          $sql4tplantcv .= " AND platform_id= " . intval($platformID);
        }        
      
        $rs = $this->db->fetchColumnsIntoMap($sql4tplantcv,'platform_id','tcversion_id',
                           database::CUMULATIVE);
        foreach($rs as $platfID => $elem)
        {
          $tcVersionIDSet[$platfID] = array_values($elem);    
        }  
      }
    }  
    
    if($status_ok)
    {
      // Important NOTICE
      // we can found SOME LIMITS on number of elements on IN CLAUSE
      //
      $estimated = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);
      foreach($tcVersionIDSet as $platfID => $items)
      {  
        $sql2exec = $sql . " AND node_id IN (" . implode(',',$items) . ")";
        $dummy = $this->db->fetchOneValue($sql2exec);
        $estimated['platform'][$platfID]['minutes'] = is_null($dummy) ? 0 : $dummy;
        $estimated['platform'][$platfID]['tcase_qty'] = count($items);
        
        $estimated['totalMinutes'] += $estimated['platform'][$platfID]['minutes'];
        $estimated['totalTestCases'] += $estimated['platform'][$platfID]['tcase_qty'];
      }  
    }
    return $estimated;
  }    


  /*
    function: get_execution_time
              Takes all executions or a subset of executions, regarding a testplan and 
              computes SUM of values assigned AT EXECUTION TIME to custom field named CF_EXEC_TIME
  
              IMPORTANT:
              1. at time of this writting (20081207) this CF can be of type: string,numeric or float.
              2. YOU NEED TO USE . (dot) as decimal separator (US decimal separator?) or
                 sum will be wrong. 
              
    args:id testplan id
         $execIDSet: default null
  
    returns: sum of CF values for all testcases linked to testplan
  
    rev: 
    @internal revision
  */
  function get_execution_time($context,$execIDSet=null)
  {
    // check if cf exist and is assigned and active intest plan parent (TEST PROJECT)
    $pinfo = $this->tree_manager->get_node_hierarchy_info($id);
    $cf_info = $this->cfield_mgr->get_linked_to_testproject($pinfo['parent_id'],1,array('name' => 'CF_EXEC_TIME'));
    if( is_null($cf_info) )
    {
      return $this->getExecutionTime($context,$execIDSet);
    }  
    else
    {
      return $this->getExecutionTimeFromCF($context->tplan_id,$execIDSet,
                                           $context->platform_id);
    } 
  }


  /**
   *
   */ 
  function getExecutionTime($context,$execIDSet=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $total_time = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);
    $targetSet = array();

    $getOpt = array('outputFormat' => 'mapAccessByID' , 'addIfNull' => true);
    $platformSet = array_keys($this->getPlatforms($context->tplan_id,$getOpt));

    if( is_null($execIDSet) )
    {
      $filters = null;
      if( !is_null($context->platform_id) )
      {   
        $filters = array('platform_id' => $context->platform_id);
      }

      if( !is_null($context->build_id) && $context->build_id > 0)
      {   
        $filters['build_id'] = $context->build_id;
      }

        
      // we will compute time for ALL linked and executed test cases,
      // BUT USING ONLY TIME SPEND for LATEST executed TCVERSION
      $options = array('addExecInfo' => true);
      $executed = $this->getLTCVNewGeneration($context->tplan_id,$filters,$options); 

      if( ($status_ok = !is_null($executed)) )
      {
        $tc2loop = array_keys($executed);
        foreach($tc2loop as $tcase_id)
        {
          $p2loop = array_keys($executed[$tcase_id]);
          foreach($p2loop as $platf_id)
          {
            $targetSet[$platf_id][]=array('id' => $executed[$tcase_id][$platf_id]['exec_id'],
                                          'duration' => $executed[$tcase_id][$platf_id]['execution_duration']);
          }  
        }    
      }
    }  
    else
    {
      // If user has passed in a set of exec id, we assume that
      // he has make a good work, i.e. if he/she wanted just analize 
      // executions for just a PLATFORM he/she has filtered BEFORE
      // passing in input to this method the item set.
      // Then we will IGNORE value of argument platformID to avoid
      // run a second (and probably useless query).
      // We will use platformID JUST as index for output result
      if( is_null($context->platform_id) )
      {
        throw new Exception(__FUNCTION__ . ' When you pass $execIDSet an YOU NEED TO PROVIDE a platform ID');
      }  
      $targetSet[$context->platform_id] = $this->getExecutionDurationForSet($execIDSet);
    }  

    foreach($targetSet as $platfID => $itemSet)
    {  
      $total_time['platform'][$platfID]['minutes'] = 0;
      $total_time['platform'][$platfID]['tcase_qty'] = count($itemSet);
      foreach($itemSet as $dx)
      {
        if(!is_null($dx['duration']))
        {  
          $total_time['platform'][$platfID]['minutes'] += $dx['duration'];
        }  
      }  

      $total_time['totalMinutes'] += $total_time['platform'][$platfID]['minutes'];
      $total_time['totalTestCases'] += $total_time['platform'][$platfID]['tcase_qty'];
    }
    return $total_time;
  }    


  /**
   *
   */ 
  function getExecutionTimeFromCF($id,$execIDSet=null,$platformID=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $total_time = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);
    $targetSet = array();
    $cf_info = $this->cfield_mgr->get_by_name('CF_EXEC_TIME');
    
    // CF exists ?
    if( ($status_ok=!is_null($cf_info)) )
    {
      $cfield_id=key($cf_info);
    }
    

    if( $status_ok)
    {
      $getOpt = array('outputFormat' => 'mapAccessByID' , 'addIfNull' => true);
      $platformSet = array_keys($this->getPlatforms($id,$getOpt));

      // ----------------------------------------------------------------------------
      $sql="SELECT SUM(CAST(value AS NUMERIC)) ";
      if( DB_TYPE == 'mysql')
      {
        $sql="SELECT SUM(value) ";
      } 
      else if ( DB_TYPE == 'postgres' || DB_TYPE == 'mssql' )
      {
        $sql="SELECT SUM(CAST(value AS NUMERIC)) ";
      }        
      $sql .= " AS SUM_VALUE FROM {$this->tables['cfield_execution_values']} CFEV " .
              " WHERE CFEV.field_id={$cfield_id} " .
              " AND testplan_id={$id} ";
      // ----------------------------------------------------------------------------
     
      if( is_null($execIDSet) )
      {
        
        $filters = null;
        if( !is_null($platformID) )
        {   
          $filters = array('platform_id' => $platformID);
        }
        
        // we will compute time for ALL linked and executed test cases,
        // BUT USING ONLY TIME SPEND for LAST executed TCVERSION
        // $options = array('only_executed' => true, 'output' => 'mapOfMap');
        $options = array('addExecInfo' => true);
        $executed = $this->getLTCVNewGeneration($id,$filters,$options); 
        if( ($status_ok = !is_null($executed)) )
        {
          $tc2loop = array_keys($executed);
          foreach($tc2loop as $tcase_id)
          {
            $p2loop = array_keys($executed[$tcase_id]);
            foreach($p2loop as $platf_id)
            {
              $targetSet[$platf_id][]=$executed[$tcase_id][$platf_id]['exec_id'];
            }  
          }    
        }    
      }
      else
      {
        // If user has passed in a set of exec id, we assume that
        // he has make a good work, i.e. if he/she wanted just analize 
        // executions for just a PLATFORM he/she has filtered BEFORE
        // passing in input to this method the item set.
        // Then we will IGNORE value of argument platformID to avoid
        // run a second (and probably useless query).
        // We will use platformID JUST as index for output result
        
        if( is_null($platformID) )
        {
          throw new Exception(__FUNCTION__ . ' When you pass $execIDSet an YOU NEED TO PROVIDE a platform ID');
        }  
        $targetSet[$platformID] = $execIDSet;
      }
    }  
  
    if($status_ok)
    {
      // Important NOTICE
      // we can found SOME LIMITS on number of elements on IN CLAUSE
      //
      $estimated = array('platform' => array(), 'totalMinutes' => 0, 'totalTestCases' => 0);
      foreach($targetSet as $platfID => $items)
      {  
        $sql2exec = $sql . " AND execution_id IN (" . implode(',',$items) . ")";

        $dummy = $this->db->fetchOneValue($sql2exec);
        $total_time['platform'][$platfID]['minutes'] = is_null($dummy) ? 0 : $dummy;
        $total_time['platform'][$platfID]['tcase_qty'] = count($items);

        $total_time['totalMinutes'] += $total_time['platform'][$platfID]['minutes'];
        $total_time['totalTestCases'] += $total_time['platform'][$platfID]['tcase_qty'];
      }  
    }

    
    
    return $total_time;
  }    






  /*
    function: get_prev_builds() 
  
    args: id: testplan id
          build_id: all builds belonging to choosen testplan,
                    with id < build_id will be retreived.
          [active]: default null  -> do not filter on active status
    
    returns: 
  
  */
  function get_prev_builds($id,$build_id,$active=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql =   " /* $debugMsg */ SELECT id,testplan_id, name, notes, active, is_open " .
        " FROM {$this->tables['builds']} " . 
        " WHERE testplan_id = {$id} AND id < {$build_id}" ;
    
    if( !is_null($active) )
    {
      $sql .= " AND active=" . intval($active) . " ";
    }
    
    $recordset = $this->db->fetchRowsIntoMap($sql,'id');
    return $recordset;
  }
  

  /**
   * returns set of tcversions that has same execution status
   * in every build present on buildSet for selected Platform.
   *  
   * id: testplan id
   * buildSet: builds to analise.
   * status: status code (can be an array)
   *
   */
  function get_same_status_for_build_set($id, $buildSet, $status, $platformID=NULL)
  {
    // On Postgresql 
    // An output column’s name can be used to refer to the column’s value in ORDER BY and GROUP BY clauses, 
    // but not in the WHERE or HAVING clauses; there you must write out the expression instead.

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $node_types = $this->tree_manager->get_available_node_types();
    $num_exec = count($buildSet);
    $build_in = implode(",", $buildSet);
    $status_in = implode("',", (array)$status);
    
    $tcversionPlatformString = "";
    $executionPlatformString = "";
    if($platformid) {
      $tcversionPlatformString = "AND T.platform_id=$platformid";
      $executionPlatformString = "AND E.platform_id=$platformid";
    }  
        
    $first_results = null;
    if( in_array($this->notRunStatusCode, (array)$status) )
    {
      
      $sql = " /* $debugMsg */ SELECT distinct T.tcversion_id,E.build_id,NH.parent_id AS tcase_id " .
           " FROM {$this->tables['testplan_tcversions']}  T " .
           " JOIN {$this->tables['nodes_hierarchy']}  NH ON T.tcversion_id=NH.id " .
           " AND NH.node_type_id={$node_types['testcase_version']} " .
           " LEFT OUTER JOIN {$this->tables['executions']} E ON T.tcversion_id = E.tcversion_id " .
           " AND T.testplan_id=E.testplan_id AND E.build_id IN ({$build_in}) " .
           " WHERE T.testplan_id={$id} AND E.build_id IS NULL ";
      
      $first_results = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    }
    
    $sql = " SELECT EE.status,SQ1.tcversion_id, NH.parent_id AS tcase_id, COUNT(EE.status) AS exec_qty " .
         " FROM {$this->tables['executions']} EE, {$this->tables['nodes_hierarchy']} NH," .
         " (SELECT E.tcversion_id,E.build_id,MAX(E.id) AS last_exec_id " .
         " FROM {$this->tables['executions']} E " .
         " WHERE E.build_id IN ({$build_in}) " .
         " GROUP BY E.tcversion_id,E.build_id) AS SQ1 " .
         " WHERE EE.build_id IN ({$build_in}) " .
         " AND EE.status IN ('" . $status . "') AND NH.node_type_id={$node_types['testcase_version']} " .
         " AND SQ1.last_exec_id=EE.id AND SQ1.tcversion_id=NH.id " .
         " GROUP BY status,SQ1.tcversion_id,NH.parent_id" .
         " HAVING COUNT(EE.status)= {$num_exec} " ;
    
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    
    if (count($first_results)) {
      foreach ($first_results as $key => $value) {
        $recordset[$key] = $value;
      }
    }
    
    return $recordset;
  }



  /**
   * BUGID 2455, BUGID 3026
   * find all builds for which a testcase has not been executed
   * 
   * @author asimon
   * @param integer $id Build ID
   * @param array $buildSet build set to check
   * @return array $new_set set of builds which match the search criterium
   * @internal revisions
   * 20101215 - asimon - BUGID 4023: correct filtering also with platforms
   */
  function get_not_run_for_any_build($id, $buildSet, $platformid=NULL) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $node_types=$this->tree_manager->get_available_node_types();
    
    $results = array();
    
    $tcversionPlatformString = "";
    $executionPlatformString = "";
    if($platformid) {
      $tcversionPlatformString = "AND T.platform_id=$platformid";
      $executionPlatformString = "AND E.platform_id=$platformid";
    }
  
    foreach ($buildSet as $build) {
      $sql = "/* $debugMsg */ SELECT distinct T.tcversion_id, E.build_id, E.status, NH.parent_id AS tcase_id " .
           " FROM {$this->tables['testplan_tcversions']} T " .
           " JOIN {$this->tables['nodes_hierarchy']} NH ON T.tcversion_id=NH.id  AND NH.node_type_id=4 " .
           " LEFT OUTER JOIN {$this->tables['executions']} E ON T.tcversion_id = E.tcversion_id " .
           " AND T.testplan_id=E.testplan_id AND E.build_id=$build $executionPlatformString" .
           " WHERE T.testplan_id={$id} AND E.status IS NULL $tcversionPlatformString";
      $results[] = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    }
    
    $recordset = array();
    foreach ($results as $result) 
    {
      if (!is_null($result) && (is_array($result)) ) //BUGID 3806
      {
        $recordset = array_merge_recursive($recordset, $result);
      }
    } 
    $new_set = array();
    foreach ($recordset as $key => $val) {
      $new_set[$val['tcase_id']] = $val;
    }
    
    return $new_set;
  }


  /**
   * link platforms to a new Test Plan
   * 
   * @param int $source_id original Test Plan id
   * @param int $target_id new Test Plan id
   * @param array $mappings: key source platform id, target platform id
   *                         USED when copy is done to a test plan that BELONGS to
   *                         another Test Project.
   */
  private function copy_platforms_links($source_id, $target_id, $mappings = null)
  {
      $sourceLinks = $this->platform_mgr->getLinkedToTestplanAsMap($source_id);
      if( !is_null($sourceLinks) )
      {
        $sourceLinks = array_keys($sourceLinks);
        if( !is_null($mappings) )
        {
          foreach($sourceLinks as $key => $value)
          {
            $sourceLinks[$key] = $mappings[$value];
          }
        }
        $this->platform_mgr->linkToTestplan($sourceLinks,$target_id);
      }
  }
  
  /**
   * link attachments to a new Test Plan
   *
   * @param int $source_id original Test Plan id
   * @param int $target_id new Test Plan id
   */
  private function copy_attachments($source_id, $target_id)
  {
      $this->attachmentRepository->copyAttachments($source_id,$target_id,$this->attachmentTableName);
  }

  /**
   * 
   *
   * outputFormat: 
   *        'array',
   *        'map', 
   *        'mapAccessByID' => map access key: id
   *        'mapAccessByName' => map access key: name
   *
   */
    function getPlatforms($id,$options=null)
    {
      $my['options'] = array('outputFormat' => 'array', 'outputDetails' => 'full', 'addIfNull' => false);
      $my['options'] = array_merge($my['options'], (array)$options);
      
      switch($my['options']['outputFormat'])
      {
        case 'map':
          $platforms = $this->platform_mgr->getLinkedToTestplanAsMap($id);
        break;
          
        default:
          $opt = array('outputFormat' => $my['options']['outputFormat']);
          $platforms = $this->platform_mgr->getLinkedToTestplan($id,$opt);
        break;
      }   
      
      if( !is_null($platforms) )
      {
        switch($my['options']['outputDetails'])
        {
          case 'name':
            foreach($platforms as $id => $elem)
            {
              $platforms[$id] = $elem['name'];    
            }
          break;
          
          default:
          break;  
        }
        
      }
      else if( $my['options']['addIfNull'] )
      {
        $platforms = array( 0 => '');
      }
      return $platforms; 
    }

  /**
   * Logic to determine if platforms should be visible for a given testplan.
   * @return bool true if the testplan has one or more linked platforms;
   *              otherwise false.
   */
    function hasLinkedPlatforms($id)
    {
      return $this->platform_mgr->platformsActiveForTestplan($id);
    }  



    /**
     * changes platform id on a test plan linked test case versions for
     * a target platform.
     * Corresponding executions information is also updated
     *
   * @param id: test plan id
   * @param from: plaftorm id to update (used as filter criteria).
   * @param to: new plaftorm id value
   * @param tcversionSet: default null, can be array with tcversion id
   *                      (used as filter criteria).
   *
    *
    */
    function changeLinkedTCVersionsPlatform($id,$from,$to,$tcversionSet=null)
    {
      $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
      $sqlFilter = '';
      if( !is_null($tcversionSet) )
      {
      $sqlFilter = " AND tcversion_id IN (" . implode(',',(array)$tcversionSet) . " ) ";
      }
      $whereClause = " WHERE testplan_id = {$id} AND platform_id = {$from} {$sqlFilter}";

      $sqlStm = array();
      $sqlStm[] = "/* {$debugMsg} */ " . 
                  " UPDATE {$this->tables['testplan_tcversions']} " .
                  " SET platform_id = {$to} " . $whereClause;

      $sqlStm[] = "/* {$debugMsg} */" .
                  " UPDATE {$this->tables['executions']} " .
                  " SET platform_id = {$to} " . $whereClause;

      foreach($sqlStm as $sql)
      {
        $this->db->exec_query($sql);    
      }
    }

    /**
     *
     * @param id: test plan id
     * @param platformSet: default null, used as filter criteria.
     * @return map: key platform id, values count,platform_id
     */
  public function countLinkedTCVersionsByPlatform($id,$platformSet=null)
  {
    $sqlFilter = '';
    if( !is_null($platformSet) )
    {
      $sqlFilter = " AND platform_id IN (" . implode(',',(array)$platformSet). ") ";
    }
    $sql = " SELECT COUNT(testplan_id) AS qty,platform_id " .
           " FROM {$this->tables['testplan_tcversions']} " .
         " WHERE testplan_id={$id} {$sqlFilter} " .
         " GROUP BY platform_id ";
    $rs = $this->db->fetchRowsIntoMap($sql,'platform_id');
    return $rs;
  }



 /**
  * 
  *
  */
  public function getStatusForReports()
  {
    // This will be used to create dynamically counters if user add new status
    foreach( $this->resultsCfg['status_label_for_exec_ui'] as $tc_status_verbose => $label)
    {
      $code_verbose[$this->resultsCfg['status_code'][$tc_status_verbose]] = $tc_status_verbose;
    }
    if( !isset($this->resultsCfg['status_label_for_exec_ui']['not_run']) )
    {
      $code_verbose[$this->resultsCfg['status_code']['not_run']] = 'not_run';
    }
    return $code_verbose;
  }



  /**
   * getTestCaseSiblings()
   *
   * @internal revisions
   */
  function getTestCaseSiblings($id,$tcversion_id,$platform_id,$opt=null)
  {                                                                    
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my['opt'] = array('assigned_to' => null);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $sql = " SELECT NHTSET.name as testcase_name,NHTSET.id AS testcase_id , NHTCVSET.id AS tcversion_id," .
           " NHTC.parent_id AS testsuite_id, " .
           " TPTCVX.id AS feature_id, TPTCVX.node_order, TCV.tc_external_id " .
           " from {$this->tables['testplan_tcversions']} TPTCVMAIN " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TPTCVMAIN.tcversion_id " . 
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " . 
           " JOIN {$this->tables['nodes_hierarchy']} NHTSET ON NHTSET.parent_id = NHTC.parent_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCVSET ON NHTCVSET.parent_id = NHTSET.id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCVSET.id " .
           " JOIN {$this->tables['testplan_tcversions']} TPTCVX " . 
           " ON TPTCVX.tcversion_id = NHTCVSET.id " .
           " AND TPTCVX.testplan_id = TPTCVMAIN.testplan_id " .
           " AND TPTCVX.platform_id = TPTCVMAIN.platform_id ";
           
    if( !is_null($my['opt']['assigned_to']) )
    {
      $user_id = intval($my['opt']['assigned_to']['user_id']);
      $build_id = intval($my['opt']['assigned_to']['build_id']);

      $addJoin = " /* Analise user assignment to get sibling */ " .
                 " JOIN {$this->tables['user_assignments']} UAMAIN " .
                 " ON UAMAIN.feature_id = TPTCVMAIN.id " . 
                 " AND UAMAIN.build_id = " . $build_id . 
                 " AND UAMAIN.user_id = " . $user_id . 
                 " AND UAMAIN.type = {$this->execTaskCode} " .
                 " JOIN {$this->tables['user_assignments']} UAX " .
                 " ON UAX.feature_id = TPTCVX.id " . 
                 " AND UAX.build_id = " . $build_id . 
                 " AND UAX.user_id = " . $user_id . 
                 " AND UAX.type = {$this->execTaskCode} ";
      $sql .= $addJoin;
      
    }
    
    $sql .= " WHERE TPTCVMAIN.testplan_id = {$id} AND TPTCVMAIN.tcversion_id = {$tcversion_id} " .
            " AND TPTCVMAIN.platform_id = {$platform_id} " .
            " ORDER BY node_order,tc_external_id ";

    // " ORDER BY node_order,external_id,testcase_name ";
    
    $siblings = $this->db->fetchRowsIntoMap($sql,'tcversion_id');
    return $siblings;
  }


  /**
   * getTestCaseNextSibling()
   *
   * @used-by execSetResults.php
   *
   */
  function getTestCaseNextSibling($id,$tcversion_id,$platform_id,$opt=null)
  {
    $my['opt'] = array('move' => 'forward', 'scope' => 'local');
    $my['opt'] = array_merge($my['opt'],(array)$opt);


    $sibling = null;
    switch($my['opt']['scope'])
    {
      case 'world':
        $tptcv = $this->tables['testplan_tcversions'];

        $subq = " SELECT node_order FROM {$this->tables['testplan_tcversions']} TX " .
                " WHERE TX.testplan_id = {$id} AND " .
                " TX.tcversion_id = {$tcversion_id} "; 

        if( $platform_id > 0)
        {
          $subq .= " AND TX.platform_id = {$platform_id} ";
        }  
        $sql= " SELECT tcversion_id,node_order " .
              " FROM {$tptcv} TZ " .
              " WHERE TZ.testplan_id = {$id} AND " .
              " TZ.tcversion_id <> {$tcversion_id} "; 
        if( $platform_id > 0)
        {
          $sql .= " AND TZ.platform_id = {$platform_id} ";
        }  

        $sql .= " ORDER BY TZ.node_order >= ($subq) ";
      break;

      case 'local':
      default:
        $sib = $this->getTestCaseSiblings($id,$tcversion_id,$platform_id,$my['opt']);
      break;
    }
    $tcversionSet = array_keys($sib);
    $elemQty = count($tcversionSet);
    $dummy = array_flip($tcversionSet);

    $pos = $dummy[$tcversion_id];  
    switch($my['opt']['move'])
    {
      case 'backward':
        $pos--;
        $pos = $pos < 0 ? 0 : $pos;
      break;

      case 'forward':
      default:
        $pos++;
      break;
    }

    $sibling_tcversion = $pos < $elemQty ? $tcversionSet[$pos] : 0;
    if( $sibling_tcversion > 0 )
    {
      $sibling = array('tcase_id' => $sib[$sibling_tcversion]['testcase_id'],
                       'tcversion_id' => $sibling_tcversion);
    }
    return $sibling;
  }

    /**
     * Convert a given urgency and importance to a priority level using
     * threshold values in $tlCfg->priority_levels.
     *
     * @param mixed $urgency Urgency of the testcase.
     *      If this is the only parameter given then interpret it as
     *      $urgency*$importance.
     * @param mixed $importance Importance of the testcase. (Optional)
     *
     * @return int HIGH, MEDIUM or LOW
     */
    public function urgencyImportanceToPriorityLevel($urgency, $importance=null)
    {
        $urgencyImportance = intval($urgency) * (is_null($importance) ? 1 : intval($importance)) ;
        return priority_to_level($urgencyImportance);
    }


  /**
   * create XML string with following structure
   *
   *  <?xml version="1.0" encoding="UTF-8"?>
   *    <testplan>
   *      <name></name>
   *      <platforms>
   *        <platform>
   *          <name> </name>
   *          <internal_id> </internal_id>
   *        </platform>
   *        <platform>
   *        ...
   *        </platform>
   *      </platforms>
   *      <executables>
   *        <link>
   *          <platform>
   *            <name> </name>
   *          </platform>
   *          <testcase>
   *            <name> </name>
   *            <externalid> </externalid>
   *            <version> </version>
   *            <execution_order> </execution_order>
   *          </testcase>
   *        </link>
   *        <link>
   *        ...
   *        </link>
   *      </executables>
   *    </testplan>   
   *  </xml>
    *
    */
  function exportLinkedItemsToXML($id)
  {
    $item_info = $this->get_by_id($id);
            
    // Linked platforms
    $xml_root = "<platforms>{{XMLCODE}}\n</platforms>";

    // ||yyy||-> tags,  {{xxx}} -> attribute 
    // tags and attributes receive different treatment on exportDataToXML()
    //
    // each UPPER CASE word in this map is a KEY, that MUST HAVE AN OCCURENCE on $elemTpl
    //
    $xml_template = "\n\t" . 
                    "<platform>" . 
                    "\t\t" . "<name><![CDATA[||PLATFORMNAME||]]></name>" .
                    "\t\t" . "<internal_id><![CDATA[||PLATFORMID||]]></internal_id>" .
                    "\n\t" . "</platform>";
              
    $xml_mapping = null;
    $xml_mapping = array("||PLATFORMNAME||" => "platform_name", "||PLATFORMID||" => 'id');

    $mm = (array)$this->platform_mgr->getLinkedToTestplanAsMap($id);
    $loop2do = count($mm);
    if( $loop2do > 0 )
    { 
      $items2loop = array_keys($mm);
      foreach($items2loop as $itemkey)
      {
        $mm[$itemkey] = array('platform_name' => $mm[$itemkey], 'id' => $itemkey);
      }
    }
    $linked_platforms = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,('noXMLHeader'=='noXMLHeader'));

    // Linked test cases
    $xml_root = "\n<executables>{{XMLCODE}}\n</executables>";
    $xml_template = "\n\t" . 
                    "<link>" . "\n" .
                    "\t\t" . "<platform>" . "\n" . 
                    "\t\t\t" . "<name><![CDATA[||PLATFORMNAME||]]></name>" . "\n" .
                    "\t\t" . "</platform>" . "\n" . 
                    "\t\t" . "<testcase>" . "\n" . 
                    "\t\t\t" . "<name><![CDATA[||NAME||]]></name>\n" .
                    "\t\t\t" . "<externalid><![CDATA[||EXTERNALID||]]></externalid>\n" .
                    "\t\t\t" . "<version><![CDATA[||VERSION||]]></version>\n" .
                    "\t\t\t" . "<execution_order><![CDATA[||EXECUTION_ORDER||]]></execution_order>\n" .
                    "\t\t" . "</testcase>" . "\n" . 
                    "</link>" . "\n" .

    $xml_mapping = null;
    $xml_mapping = array("||PLATFORMNAME||" => "platform_name","||EXTERNALID||" => "external_id",              
                         "||NAME||" => "name","||VERSION||" => "version",
                         "||EXECUTION_ORDER||" => "execution_order");

    $mm = $this->getLinkedStaticView($id,null,array('output' => 'array'));
    $linked_testcases = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,('noXMLHeader'=='noXMLHeader'));

    
    $item_info['linked_platforms'] = $linked_platforms;
    $item_info['linked_testcases'] = $linked_testcases;
    $xml_root = "\n\t<testplan>{{XMLCODE}}\n\t</testplan>";
    $xml_template = "\n\t\t" . "<name><![CDATA[||TESTPLANNAME||]]></name>" . "\n" .
                    "\t\t||LINKED_PLATFORMS||\n" . "\t\t||LINKED_TESTCASES||\n";

    $xml_mapping = null;
    $xml_mapping = array("||TESTPLANNAME||" => "name","||LINKED_PLATFORMS||" => "linked_platforms",              
                         "||LINKED_TESTCASES||" => "linked_testcases");
             
    $xml = exportDataToXML(array($item_info),$xml_root,$xml_template,$xml_mapping);

    return $xml;
  }




  /**
   * create XML string with following structure
   *
   *  <?xml version="1.0" encoding="UTF-8"?>
   *  
   * @param mixed context: map with following keys 
   *             platform_id: MANDATORY
   *             build_id: OPTIONAL
   *             tproject_id: OPTIONAL
   */
  function exportTestPlanDataToXML($id,$context,$optExport = array())
  {
    $platform_id = $context['platform_id'];
    if( !isset($context['tproject_id']) || is_null($context['tproject_id']) )
    {
      $dummy = $this->tree_manager->get_node_hierarchy_info($id);
      $context['tproject_id'] = $dummy['parent_id'];
    }
    $context['tproject_id'] = intval($context['tproject_id']);
    
    $xmlTC = null;


    // CRITIC - this has to be firt population of item_info.
    // Other processes adds info to this map.
    $item_info = $this->get_by_id($id);
    
    // Need to get family
    $nt2exclude = array('testplan' => 'exclude_me','requirement_spec'=> 'exclude_me',
                        'requirement'=> 'exclude_me');
    $nt2exclude_children = array('testcase' => 'exclude_my_children',
                                 'requirement_spec'=> 'exclude_my_children');

    $my = array();
    
    // this can be a litte weird but ...
    // when 
    // 'order_cfg' => array("type" =>'exec_order'
    // additional info test plan id, and platform id are used to get
    // a filtered view of tree.
    //
    $order_cfg = array("type" =>'exec_order',"tplan_id" => $id);
    if( $context['platform_id'] > 0 )
    {
      $order_cfg['platform_id'] = $context['platform_id'];
    }
    $my['options']=array('recursive' => true, 'order_cfg' => $order_cfg,
                         'remove_empty_nodes_of_type' => $this->tree_manager->node_descr_id['testsuite']);
    $my['filters'] = array('exclude_node_types' => $nt2exclude,'exclude_children_of' => $nt2exclude_children);
    $tplan_spec = $this->tree_manager->get_subtree($context['tproject_id'],$my['filters'],$my['options']);

    // -----------------------------------------------------------------------------------------------------
    // Generate test project info 
    $tproject_mgr = new testproject($this->db);
    $tproject_info = $tproject_mgr->get_by_id($context['tproject_id']);

    // ||yyy||-> tags,  {{xxx}} -> attribute 
    // tags and attributes receive different treatment on exportDataToXML()
    //
    // each UPPER CASE word in this map is a KEY, that MUST HAVE AN OCCURENCE on $elemTpl
    //
    $xml_template = "\n\t" . 
            "<testproject>" . 
                "\t\t" . "<name><![CDATA[||TESTPROJECTNAME||]]></name>" .
                "\t\t" . "<prefix><![CDATA[||TESTPROJECTPREFIX||]]></prefix>" .
                "\t\t" . "<internal_id><![CDATA[||TESTPROJECTID||]]></internal_id>" .
                "\n\t" . "</testproject>";
              
    $xml_root = "{{XMLCODE}}";          
    $xml_mapping = null;
    $xml_mapping = array("||TESTPROJECTNAME||" => "name", "||TESTPROJECTPREFIX||" => "prefix","||TESTPROJECTID||" => 'id');
    $mm = array();
    $mm[$context['tproject_id']] = array('name' => $tproject_info['name'],'prefix' => $tproject_info['prefix'],
                                         'id' => $context['tproject_id']);
    $item_info['testproject'] = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,('noXMLHeader'=='noXMLHeader'));
    // -----------------------------------------------------------------------------------------------------
    
    // -----------------------------------------------------------------------------------------------------
    // get target platform (if exists)
    $target_platform = '';
    if( $context['platform_id'] > 0)
    {
      $info = $this->platform_mgr->getByID($context['platform_id']);
      // ||yyy||-> tags,  {{xxx}} -> attribute 
      // tags and attributes receive different treatment on exportDataToXML()
      //
      // each UPPER CASE word in this map is a KEY, that MUST HAVE AN OCCURENCE on $elemTpl
      //
      $xml_template = "\n\t" . 
              "<platform>" . 
                  "\t\t" . "<name><![CDATA[||PLATFORMNAME||]]></name>" .
                  "\t\t" . "<internal_id><![CDATA[||PLATFORMID||]]></internal_id>" .
                  "\n\t" . "</platform>";
                
      $xml_root = "{{XMLCODE}}";          
      $xml_mapping = null;
      $xml_mapping = array("||PLATFORMNAME||" => "platform_name", "||PLATFORMID||" => 'id');

      $mm = array();
      $mm[$context['platform_id']] = array('platform_name' => $info['name'], 'id' => $context['platform_id']);
      $item_info['target_platform'] = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,
                                                      ('noXMLHeader'=='noXMLHeader'));
      $target_platform = "\t\t||TARGET_PLATFORM||\n";
    }
    // -----------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------
    // get Build info (if possible)
    $target_build = '';
    if( isset($context['build_id']) &&  $context['build_id'] > 0)
    {
      $dummy = $this->get_builds($id);
      $info = $dummy[$context['build_id']];
      
      // ||yyy||-> tags,  {{xxx}} -> attribute 
      // tags and attributes receive different treatment on exportDataToXML()
      //
      // each UPPER CASE word in this map is a KEY, that MUST HAVE AN OCCURENCE on $elemTpl
      //
      $xml_template = "\n\t" . 
              "<build>" . 
                  "\t\t" . "<name><![CDATA[||BUILDNAME||]]></name>" .
                  "\t\t" . "<internal_id><![CDATA[||BUILDID||]]></internal_id>" .
                  "\n\t" . "</build>";
                
        $xml_root = "{{XMLCODE}}";          
      $xml_mapping = null;
      $xml_mapping = array("||BUILDNAME||" => "name", "||BUILDID||" => 'id');

      $mm = array();
      $mm[$context['build_id']] = array('name' => $info['name'], 'id' => $context['build_id']);
        $item_info['target_build'] = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,
                                   ('noXMLHeader'=='noXMLHeader'));
        $target_build = "\t\t||TARGET_BUILD||\n";
    }
    // -----------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------
    // get test plan contents (test suites and test cases)
    $item_info['testsuites'] = null;
    if( !is_null($tplan_spec) && isset($tplan_spec['childNodes']) && ($loop2do = count($tplan_spec['childNodes'])) > 0)
    {
      $item_info['testsuites'] = '<testsuites>' . 
                                 $this->exportTestSuiteDataToXML($tplan_spec,$context['tproject_id'],$id,
                                                                 $context['platform_id'],$context['build_id']) . 
                                 '</testsuites>';
    } 
    
    $xml_root = "\n\t<testplan>{{XMLCODE}}\n\t</testplan>";
    $xml_template = "\n\t\t" . "<name><![CDATA[||TESTPLANNAME||]]></name>" . "\n" .
            "\t\t||TESTPROJECT||\n" . $target_platform  . $target_build  . "\t\t||TESTSUITES||\n";

    $xml_mapping = null;
    $xml_mapping = array("||TESTPLANNAME||" => "name", "||TESTPROJECT||" => "testproject",
               "||TARGET_PLATFORM||" => "target_platform","||TARGET_BUILD||" => "target_build",
               "||TESTSUITES||" => "testsuites");

    $zorba = exportDataToXML(array($item_info),$xml_root,$xml_template,$xml_mapping);
    
    return $zorba;
  }


  /**
   * 
   *
   */
  private function exportTestSuiteDataToXML($container,$tproject_id,$tplan_id,$platform_id,$build_id)
  {
    static $keywordMgr;
    static $getLastVersionOpt = array('output' => 'minimun');
    static $tcaseMgr;
    static $tsuiteMgr;
    static $tcaseExportOptions;
    static $linkedItems;

    if(is_null($keywordMgr))
    {
      $tcaseExportOptions = array('CFIELDS' => true, 'KEYWORDS' => true, 'EXEC_ORDER' => 0);
      $keywordMgr = new tlKeyword();      
      $tsuiteMgr = new testsuite($this->db);
      $linkedItems = $this->getLinkedItems($tplan_id);
    }  
    
    $xmlTC = null;
    $cfXML = null;
    $kwXML = null;
    
    if( isset($container['id']) )
    {
      $kwMap = $tsuiteMgr->getKeywords($container['id']);
      if ($kwMap)
      {
        $kwXML = "<keywords>" . $keywordMgr->toXMLString($kwMap,true) . "</keywords>";
      }  

      $cfMap = (array)$tsuiteMgr->get_linked_cfields_at_design($container['id'],null,null,$tproject_id);
      if( count($cfMap) > 0 )
      {
        $cfXML = $this->cfield_mgr->exportValueAsXML($cfMap);
      } 
      
      $tsuiteData = $tsuiteMgr->get_by_id($container['id']);
      $xmlTC = "\n\t<testsuite name=\"" . htmlspecialchars($tsuiteData['name']). '" >' .
               "\n\t\t<node_order><![CDATA[{$tsuiteData['node_order']}]]></node_order>" .
               "\n\t\t<details><![CDATA[{$tsuiteData['details']}]]>" .
               "\n\t\t{$kwXML}{$cfXML}</details>";
    }
    $childNodes = isset($container['childNodes']) ? $container['childNodes'] : null ;
    if( !is_null($childNodes) )
    {
      $loop_qty=sizeof($childNodes); 
      for($idx = 0;$idx < $loop_qty;$idx++)
      {
        $cNode = $childNodes[$idx];
        switch($cNode['node_table'])
        {
          case 'testsuites':
            $xmlTC .= $this->exportTestSuiteDataToXML($cNode,$tproject_id,$tplan_id,$platform_id,$build_id);
          break;
            
          case 'testcases':
            if( is_null($tcaseMgr) )
            {
              $tcaseMgr = new testcase($this->db);
            }
            // testcase::LATEST_VERSION,
            $tcaseExportOptions['EXEC_ORDER'] = $linkedItems[$cNode['id']][$platform_id]['node_order'];
			
			$filter_lv = array( 'exec_status' => 'ALL', 'active_status' => 'ALL','tplan_id' => $tplan_id, 'platform_id' => $platform_id );
			$output_lv = array( 'output' => 'simple' );
			// get tc versions linked in current testplan for current platform
			$info = $tcaseMgr->get_linked_versions($cNode['id'],$filter_lv,$output_lv);
			if( !is_null($info) )
			{
				$tcversID = key($info);
			}

			// get users assigned to tc version in current testplan for the current build
			$versionAssignInfo = $tcaseMgr->get_version_exec_assignment($tcversID, $tplan_id, $build_id );
			$userList = array();
			// extract user names
			if(!is_null($versionAssignInfo))
			{
				foreach($versionAssignInfo[$tcversID][$platform_id] as $vaInfo)
				{
					$assignedTesterId = intval($vaInfo['user_id']);
					if($assignedTesterId)
					{
					  $user = tlUser::getByID($this->db,$assignedTesterId);
					  if ($user)
					  {
						$userList[] = $user->getDisplayName();
					  }
					}
				}
			}
			(count($userList) > 0) ? $tcaseExportOptions['ASSIGNED_USER'] = $userList : $tcaseExportOptions['ASSIGNED_USER'] = null;
			
			$xmlTC .= $tcaseMgr->exportTestCaseDataToXML($cNode['id'],$cNode['tcversion_id'],
                                                         $tproject_id,testcase::NOXMLHEADER,
                                                         $tcaseExportOptions);
          break;
        }
      }
    }

    if( isset($container['id']) )
    {
      $xmlTC .= "</testsuite>"; 
    }
    return $xmlTC;
  }



  /**
   *
   */
  function getFeatureAssignments($tplan_id,$filters=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ ";

    $my['filters'] = array('build' => null, 'tcversion' => null);
    $my['filters'] = array_merge($my['filters'], (array)$filters);

    $sql .=  " SELECT COALESCE(UA.user_id,-1) AS user_id, " . 
        " TPTCV.id AS feature_id, B.id AS build_id, TPTCV.platform_id " .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .
        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        
        " LEFT OUTER JOIN {$this->tables['user_assignments']} UA " .
        " ON UA.feature_id = TPTCV.id AND UA.build_id = B.id " .
        " WHERE TPTCV.testplan_id={$tplan_id} ";
        
    if(!is_null($my['filters']['build']))
    {
      $sql .= " AND B.id IN (" . implode(',',(array)$my['filters']['build']) . ") "; 
    }    
    if(!is_null($my['filters']['tcversion']))
    {
      $sql .= " AND TPTCV.tcversion_id IN (" . implode(',',(array)$my['filters']['tcversion']) . ") "; 
    }    

    $rs =  $this->db->fetchMapRowsIntoMap($sql,'feature_id','build_id');
    return $rs;
  }  // end function



  /**
   * getSkeleton
   * 
   * get structure with Test suites and Test Cases
   * Filters that act on test cases work on attributes that are common to all
   * test cases versions: test case name
   *
   * Development Note:
   * Due to the tree structure is not so easy to try to do as much as filter as
   * possibile using SQL.
   *
   *
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getSkeleton($id,$tprojectID,$filters=null,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $items = array();
    $my['options'] = array('recursive' => false, 'exclude_testcases' => false, 
                           'remove_empty_branches' => false);
                   
    $my['filters'] = array('exclude_node_types' => $this->nt2exclude,
                           'exclude_children_of' => $this->nt2exclude_children,
                           'exclude_branches' => null,
                           'testcase_name' => null,'testcase_id' => null,
                           'execution_type' => null, 'platform_id' => null,
                           'additionalWhereClause' => null);      
   
    $my['filters'] = array_merge($my['filters'], (array)$filters);
    $my['options'] = array_merge($my['options'], (array)$options);
   
    if( $my['options']['exclude_testcases'] )
    {
      $my['filters']['exclude_node_types']['testcase']='exclude me';
    }
    
    // transform some of our options/filters on something the 'worker' will understand
    // when user has request filter by test case name, we do not want to display empty branches
  
    // If we have choose any type of filter, we need to force remove empty test suites
    //
    if( !is_null($my['filters']['testcase_name']) || !is_null($my['filters']['testcase_id']) ||
        !is_null($my['filters']['execution_type']) || !is_null($my['filters']['exclude_branches']) ||
        !is_null($my['filters']['platform_id']) || $my['options']['remove_empty_branches'] )
    {
      $my['options']['remove_empty_nodes_of_type'] = 'testsuite';
    }
    
    $method2call = $my['options']['recursive'] ? '_get_subtree_rec' : '_get_subtree';
    $tcaseSet = array();
    if($my['options']['recursive'])
    {
      $qnum = $this->$method2call($id,$tprojectID,$items,$tcaseSet,
                                  $my['filters'],$my['options']);
    }
    else
    {
      $qnum = $this->$method2call($id,$tprojectID,$items,$my['filters'],$my['options']);
    }  
    return array($items,$tcaseSet);
  }
  
  
  
  /**
   *
   * 
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function _get_subtree_rec($tplan_id,$node_id,&$pnode,&$itemSet,$filters = null, $options = null)
  {
    static $qnum;
    static $my;
    static $exclude_branches;
    static $exclude_children_of;
    static $node_types;
    static $tcaseFilter;
    static $tcversionFilter;
    static $pltaformFilter;
  
    static $childFilterOn;
    static $staticSql;
    static $debugMsg;
    
    if (!$my)
    {
      $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

      $qnum=0;
      $node_types = array_flip($this->tree_manager->get_available_node_types());
      $my['filters'] = array('exclude_children_of' => null,'exclude_branches' => null,
                             'additionalWhereClause' => '', 'testcase_name' => null,
                             'platform_id' => null,
                             'testcase_id' => null,'active_testcase' => false);
                             
      $my['options'] = array('remove_empty_nodes_of_type' => null);
  
      $my['filters'] = array_merge($my['filters'], (array)$filters);
      $my['options'] = array_merge($my['options'], (array)$options);
  
      $exclude_branches = $my['filters']['exclude_branches'];
      $exclude_children_of = $my['filters']['exclude_children_of'];  
  
  
      $tcaseFilter['name'] = !is_null($my['filters']['testcase_name']);
      $tcaseFilter['id'] = !is_null($my['filters']['testcase_id']);
      
      $tcaseFilter['is_active'] = !is_null($my['filters']['active_testcase']) && $my['filters']['active_testcase'];
      $tcaseFilter['enabled'] = $tcaseFilter['name'] || $tcaseFilter['id'] || $tcaseFilter['is_active'];
  
  
      $tcversionFilter['execution_type'] = !is_null($my['filters']['execution_type']);
      $tcversionFilter['enabled'] = $tcversionFilter['execution_type'];
  
      $childFilterOn = $tcaseFilter['enabled'] || $tcversionFilter['enabled'];
      
    
  
      if( !is_null($my['options']['remove_empty_nodes_of_type']) )
      {
        // this way I can manage code or description      
        if( !is_numeric($my['options']['remove_empty_nodes_of_type']) )
        {
          $my['options']['remove_empty_nodes_of_type'] = 
                  $this->tree_manager->node_descr_id[$my['options']['remove_empty_nodes_of_type']];
        }
      }
  
  
      $platformFilter = "";
      if( !is_null($my['filters']['platform_id']) && $my['filters']['platform_id'] > 0 )
      {
        $platformFilter = " AND T.platform_id = " . intval($my['filters']['platform_id']) ;
      }
  
      // Create invariant sql sentences
      $staticSql[0] = " /* $debugMsg - Get ONLY TestSuites */ " .
                      " SELECT NHTS.node_order AS spec_order," . 
                      " NHTS.node_order AS node_order, NHTS.id, NHTS.parent_id," . 
                      " NHTS.name, NHTS.node_type_id, 0 AS tcversion_id " .
                      " FROM {$this->tables['nodes_hierarchy']} NHTS" .
                      " WHERE NHTS.node_type_id = {$this->tree_manager->node_descr_id['testsuite']} " .
                      " AND NHTS.parent_id = ";
               
      $staticSql[1] =  " /* $debugMsg - Get ONLY Test Cases with version linked to (testplan,platform) */ " .
                       " SELECT NHTC.node_order AS spec_order, " .
                       "        TPTCV.node_order AS node_order, NHTC.id, NHTC.parent_id, " .
                       "        NHTC.name, NHTC.node_type_id, TPTCV.tcversion_id " .
                       " FROM {$this->tables['nodes_hierarchy']} NHTC " .
                       " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.parent_id = NHTC.id " .
                       " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NHTCV.id " .
                       " WHERE NHTC.node_type_id = {$this->tree_manager->node_descr_id['testcase']} " .
                       " AND TPTCV.testplan_id = " . intval($tplan_id) . " {$platformFilter} " .
                       " AND NHTC.parent_id = ";  
    
    } // End init static area
    
    $target = intval($node_id);
    $sql = $staticSql[0] . $target . " UNION " . $staticSql[1] . $target;
    
    if( $tcaseFilter['enabled'] )
    {
      foreach($tcaseFilter as $key => $apply)
      {
        if( $apply )
        {
          switch($key)
          {
            case 'name':
               $sql .= " AND NHTC.name LIKE '%{$my['filters']['testcase_name']}%' ";
            break;
            
            case 'id':
                     $sql .= " AND NHTC.id = {$my['filters']['testcase_id']} ";
            break;
          }
        }
      }
    }
    
    $sql .= " ORDER BY node_order,id";
    
    $rs = $this->db->fetchRowsIntoMap($sql,'id');
    if( count($rs) == 0 )
    {
      return $qnum;
    }
  

     foreach($rs as $row)
     {
      if(!isset($exclude_branches[$row['id']]))
      {  
        $node = $row + 
                array('node_type' => $this->tree_manager->node_types[$row['node_type_id']],
                      'node_table' => $this->tree_manager->node_tables_by['id'][$row['node_type_id']]);
        $node['childNodes'] = null;
        
        if($node['node_table'] == 'testcases')
        {
          $node['leaf'] = true; 
          $node['external_id'] = '';
          // $itemSet['nodes'][] = $node;
          //$itemSet['nindex'][] = 
          //  array('tcase_id' => $node['id'], 
          //        'tcversion_id'=> $node['tcversion_id']);
          $itemSet['nindex'][] = $node['id'];
        }      
        

        // why we use exclude_children_of ?
        // 1. Sometimes we don't want the children if the parent is a testcase,
        //    due to the version management
        //
        if(!isset($exclude_children_of[$node_types[$row['node_type_id']]]))
        {
          // Keep walking (Johny Walker Whisky)
          $this->_get_subtree_rec($tplan_id,$row['id'],$node,$itemSet,$my['filters'],$my['options']);
        }
  
           
        // Have added this logic, because when export test plan will be developed
        // having a test spec tree where test suites that do not contribute to test plan
        // are pruned/removed is very important, to avoid additional processing
        //            
        // If node has no childNodes, we check if this kind of node without children
        // can be removed.
        //
          $doRemove = is_null($node['childNodes']) && 
                    ($node['node_type_id'] == $my['options']['remove_empty_nodes_of_type']);
          if(!$doRemove)
          {
            $pnode['childNodes'][] = $node;
          }  
      } // if(!isset($exclude_branches[$rowID]))
    } //while
    return $qnum;
  }


  /**
   *
   * 
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getNotRunAllBuildsForPlatform($id,$platformID,$buildSet=null) 
  {
    // On Postgresql 
    // An output column’s name can be used to refer to the column’s value in ORDER BY and GROUP BY clauses, 
    // but not in the WHERE or HAVING clauses; there you must write out the expression instead.
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    list($safe_id,$buildsCfg,$sqlLEBBP) = $this->helperGetHits($id,$platformID,$buildSet);
    
    $sql =   "/* $debugMsg */ " .
        " SELECT count(0) AS COUNTER ,NHTCV.parent_id AS tcase_id  " .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .
        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        $buildsCfg['statusClause'] .
        
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON " .
        " NHTCV.id = TPTCV.tcversion_id " .
        " LEFT OUTER JOIN {$this->tables['executions']} E ON " .
        " E.testplan_id = TPTCV.testplan_id " .
        " AND E.platform_id = TPTCV.platform_id " .
        " AND E.tcversion_id = TPTCV.tcversion_id " .
        " AND E.build_id = B.id " .
        
        " WHERE TPTCV.testplan_id = " . $safe_id['tplan']  . 
        " AND TPTCV.platform_id " . $safe_id['platform'] .
        " AND E.status IS NULL ";

    $groupBy = ' GROUP BY ' . ((DB_TYPE == 'mssql') ? 'parent_id ':'tcase_id');
    $sql .= $groupBy .    
            " HAVING COUNT(0) = " . intval($buildsCfg['count']) ; 
        
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return $recordset;
  }


  /**
   *
   * 
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsNotRunForBuildAndPlatform($id,$platformID,$buildID) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    // list($safe_id,$buildsCfg,$sqlLEBBP) = $this->helperGetHits($id,$platformID,$buildSet);

    $safe_id['tplan'] = intval($id);
    $safe_id['platform'] = intval($platformID);
    $safe_id['build'] = intval($buildID);

    $sql =   "/* $debugMsg */ " .
        " SELECT DISTINCT NHTCV.parent_id AS tcase_id, E.status, B.id AS build_id " .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .
        
        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .

        " /* Needed to get TEST CASE ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
        " ON NHTCV.id = TPTCV.tcversion_id " .

        " /* Need to Get Execution Info on REQUESTED build set */ " .
        " LEFT OUTER JOIN {$this->tables['executions']} E " .
        " ON  E.testplan_id = TPTCV.testplan_id " .
        " AND E.platform_id = TPTCV.platform_id " .
        " AND E.build_id = B.id " .
        " AND E.tcversion_id = TPTCV.tcversion_id " .
        
        " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] . 
        " AND TPTCV.platform_id = " . $safe_id['platform'] . 
        " AND B.id = " . $safe_id['build'] .
        " AND E.status IS NULL ";
         
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return $recordset;
  }


  /**
   *
   * 
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getNotRunAtLeastOneBuildForPlatform($id,$platformID,$buildSet=null) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    list($safe_id,$buildsCfg,$sqlLEBBP) = $this->helperGetHits($id,$platformID,$buildSet);

    $sql =   "/* $debugMsg */ " .
        " SELECT DISTINCT NHTCV.parent_id AS tcase_id, E.status " .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .
        
        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        $buildsCfg['statusClause'] .
        
        " /* Needed to get TEST CASE ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
        " ON NHTCV.id = TPTCV.tcversion_id " .
        
        " /* Need to Get Execution Info on REQUESTED build set */ " .
        " LEFT OUTER JOIN {$this->tables['executions']} E " .
        " ON  E.testplan_id = TPTCV.testplan_id " .
        " AND E.platform_id = TPTCV.platform_id " .
        " AND E.tcversion_id = TPTCV.tcversion_id " .
        " AND E.build_id = B.id " .
        " AND E.build_in IN ({$buildsCfg['inClause']}) " .
        
        " WHERE TPTCV.testplan_id = $id " . 
        " AND TPTCV.platform_id={$platformID} " .
        " AND E.build_in IN ({$buildsCfg['inClause']}) " .
        " AND E.status IS NULL ";
        
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return $recordset;
  }


  /**
   * returns recordset with test cases that has requested status 
   * (only statuses that are written to DB => this does not work for not run)
   * for LAST EXECUTION on build Set provided, for a platform.
   *
   * FULL means that we have to have SAME STATUS on all builds present on set.
   * If build set is NOT PROVIDED, we will use ALL ACTIVE BUILDS
   * 
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsSingleStatusFull($id,$platformID,$status,$buildSet=null) 
  {
    // On Postgresql 
    // An output column’s name can be used to refer to the column’s value in ORDER BY and GROUP BY clauses, 
    // but not in the WHERE or HAVING clauses; there you must write out the expression instead.

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    list($safe_id,$buildsCfg,$sqlLEBBP) = $this->helperGetHits($id,$platformID,$buildSet);

    $sql =   " /* $debugMsg */ " .
        " /* Count() to be used on HAVING */ " .
        " SELECT COUNT(0) AS COUNTER ,NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        $buildsCfg['statusClause'] .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TPTCV.tcversion_id " .

        " /* Get Latest Execution by BUILD and PLATFORM  */ " .
        " JOIN ({$sqlLEBBP}) AS LEBBP " .
        " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
        " AND LEBBP.platform_id = TPTCV.platform_id " .
        " AND LEBBP.build_id = B.id " .
        " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .

        " /* Get STATUS INFO From Executions */ " .
        " JOIN {$this->tables['executions']} E " .
        " ON  E.id = LEBBP.id " .
        " AND E.tcversion_id = LEBBP.tcversion_id " .
        " AND E.testplan_id = LEBBP.testplan_id " .
        " AND E.platform_id = LEBBP.platform_id " .
        " AND E.build_id = LEBBP.build_id " .
        
        " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] . 
        " AND TPTCV.platform_id=" . $safe_id['platform'] . 
        " AND E.build_id IN ({$buildsCfg['inClause']}) " .
        " AND E.status ='" .$this->db->prepare_string($status) . "'";

    $groupBy = ' GROUP BY ' . ((DB_TYPE == 'mssql') ? 'parent_id ':'tcase_id');
    $sql .= $groupBy .    
            " HAVING COUNT(0) = " . intval($buildsCfg['count']) ; 

    unset($safe_id,$buildsCfg,$sqlLEBBP);
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return $recordset;
  }


  /**
   * getHitsNotRunFullOnPlatform($id,$platformID,$buildSet)
   *
   * returns recordset with:
   * test cases with NOT RUN status on ALL builds in build set (full), for a platform.
   *
   * If build set is null
   * test cases with NOT RUN status on ALL ACTIVE builds (full), for a platform.
   * 
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsNotRunFullOnPlatform($id,$platformID,$buildSet=null) 
  {
    // On Postgresql 
    // An output column’s name can be used to refer to the column’s value in ORDER BY and GROUP BY clauses, 
    // but not in the WHERE or HAVING clauses; there you must write out the expression instead.
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    list($safe_id,$buildsCfg,$sqlLEBBP) = $this->helperGetHits($id,$platformID,$buildSet);
    
    $sql =   " /* $debugMsg */ " .
        " /* Count() to be used on HAVING */ " .
        " SELECT COUNT(0) AS COUNTER ,NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .
        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        $buildsCfg['statusClause'] .
        
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON " .
        " NHTCV.id = TPTCV.tcversion_id " .

        " LEFT OUTER JOIN {$this->tables['executions']} E ON " .
        " E.testplan_id = TPTCV.testplan_id " .
        " AND E.platform_id = TPTCV.platform_id " .
        " AND E.build_id = B.id " .
        " AND E.tcversion_id = TPTCV.tcversion_id " .

        " WHERE TPTCV.testplan_id = " . $safe_id['tplan']  . 
        " AND TPTCV.platform_id = " . $safe_id['platform']  .  
        " AND E.status IS NULL ";

    $groupBy = ' GROUP BY ' . ((DB_TYPE == 'mssql') ? 'parent_id ':'tcase_id');
    $sql .= $groupBy .    
            " HAVING COUNT(0) = " . intval($buildsCfg['count']) ; 

    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return $recordset;
  }


  /**
   * getHitsStatusSetFullOnPlatform($id,$platformID,$statusSet,$buildQty=0) 
   *
   * returns recordset with:
   * test cases that has at least ONE of requested status 
   * ON LAST EXECUTION ON ALL builds in set (full) , for a platform
   *
   * If build set is not provided, thena analisys will be done on 
   * ALL ACTIVE BUILDS
   *
   *
   * IMPORTANT / CRITIC:  This has NOT BE USED FOR NOT RUN,
   *            there is an special method for NOT RUN status.
   *
   * Example:
   * 
   * Test Plan: PLAN B 
   * Builds: B1,B2,B3
   * Test Cases: TC-100, TC-200,TC-300
   *
   * Test Case - Build - LAST Execution status
   * TC-100      B1      Passed
   * TC-100      B2      FAILED
   * TC-100      B3      Not Run
   *
   * TC-200      B1      FAILED
   * TC-200      B2      FAILED
   * TC-200      B3      BLOCKED
   *
   * TC-300      B1      Passed
   * TC-300      B2      Passed
   * TC-300      B3      BLOCKED
   *
   * TC-400      B1      FAILED
   * TC-400      B2      BLOCKED
   * TC-400      B3      FAILED
   * 
   * Request 1:
   * Provide test cases with status (LAST EXECUTION) in ('Passed','BLOCKED')
   * ON ALL ACTIVE Builds
   *
   * ANSWER:
   * TC-300
   *
   * Request 2:
   * Provide test cases with status in ('FAILED','BLOCKED')
   * ON ALL ACTIVE Builds
   *
   * ANSWER:
   * TC-300, TC-400
   *
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   * 20120919 - asimon - TICKET 5226: Filtering by test result did not always show the correct matches
   */
  function getHitsStatusSetFullOnPlatform($id,$platformID,$statusSet,$buildSet=null) 
  {

    // On Postgresql 
    // An output column’s name can be used to refer to the column’s value in ORDER BY and GROUP BY clauses, 
    // but not in the WHERE or HAVING clauses; there you must write out the expression instead.

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    list($safe_id,$buildsCfg,$sqlLEBBP) = $this->helperGetHits($id,$platformID,$buildSet);

    $dummy = (array)$statusSet;
    $statusInClause = implode("','",$dummy);

    // ATTENTION:
    // if I've requested (Passed or Blocked) on ALL BUILDS
    // Have 2 results for build number.
    //
    // That logic is wrong when filtering for the SAME STATUS on ALL builds.
    // Maybe copy/paste-error on refactoring? 
    // Example: With 3 builds and filtering for FAILED or BLOCKED on ALL builds
    // we have to get 3 hits for each test case to be shown, not six hits.
    // $countTarget = intval($buildsCfg['count']) * count($dummy);
    $countTarget = intval($buildsCfg['count']);

    $groupBy = ' GROUP BY ' . ((DB_TYPE == 'mssql') ? 'parent_id ':'tcase_id');
    $sql =   " /* $debugMsg */ " .
        " /* Count() to be used on HAVING */ " .
        " SELECT COUNT(0) AS COUNTER ,NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        $buildsCfg['statusClause'] .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TPTCV.tcversion_id " .

        " /* Get Latest Execution by BUILD and PLATFORM  */ " .
        " JOIN ({$sqlLEBBP}) AS LEBBP " .
        " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
        " AND LEBBP.platform_id = TPTCV.platform_id " .
        " AND LEBBP.build_id = B.id " .
        " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .

        " /* Get STATUS INFO From Executions */ " .
        " JOIN {$this->tables['executions']} E " .
        " ON  E.id = LEBBP.id " .
        " AND E.tcversion_id = LEBBP.tcversion_id " .
        " AND E.testplan_id = LEBBP.testplan_id " .
        " AND E.platform_id = LEBBP.platform_id " .
        " AND E.build_id = LEBBP.build_id " .
        
        " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] . 
        " AND TPTCV.platform_id=" . $safe_id['platform'] . 
        " AND E.build_id IN ({$buildsCfg['inClause']}) " .
        " AND E.status IN ('{$statusInClause}')" .
        $groupBy . " HAVING COUNT(0) = " . $countTarget ; 

    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return $recordset;
  }


  /**
   * getHitsNotRunPartialOnPlatform($id,$platformID,buildSet) 
   *
   * returns recordset with:
   * test cases with NOT RUN status at LEAST ON ONE off ALL ACTIVE builds (Partial), 
   * for a platform.
   * 
   * Example:
   * 
   * Test Plan: PLAN B 
   * Builds: B1,B2,B3
   * Test Cases: TC-100, TC-200,TC-300
   *
   * Test Case - Build - LAST Execution status
   * TC-100      B1      Passed
   * TC-100      B2      FAILED
   * TC-100      B3      Not Run => to have this status means THAT HAS NEVER EXECUTED ON B3
   *
   * TC-200      B1      FAILED
   * TC-200      B2      FAILED
   * TC-200      B3      BLOCKED
   *
   * TC-300      B1      Passed
   * TC-300      B2      Passed
   * TC-300      B3      BLOCKED
   *
   * TC-400      B1      FAILED
   * TC-400      B2      BLOCKED
   * TC-400      B3      FAILED
   * 
   * Request :
   * Provide test cases with status 'NOT RUN'
   * ON At Least ON OF all ACTIVE Builds
   *
   * ANSWER:
   * TC-100
   *
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsNotRunPartialOnPlatform($id,$platformID,$buildSet=null) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    list($safe_id,$buildsCfg,$sqlLEBBP) = $this->helperGetHits($id,$platformID,$buildSet);


    $sql =   " /* $debugMsg */ " .
        " SELECT DISTINCT NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        $buildsCfg['statusClause'] .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON " .
        " NHTCV.id = TPTCV.tcversion_id " .

        " /* Executions, looking for status NULL (remember NOT RUN is not written on DB) */ " .
        " LEFT OUTER JOIN {$this->tables['executions']} E " .
        " ON  E.testplan_id = TPTCV.testplan_id " .
        " AND E.platform_id = TPTCV.platform_id " .
        " AND E.build_id = B.id " .
        " AND E.tcversion_id = TPTCV.tcversion_id " .
        
        " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] .
        " AND TPTCV.platform_id = " . $safe_id['platform'] .
        " AND E.status IS NULL ";
        
   
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return $recordset;
  }

  /**
   * getHitsStatusSetPartialOnPlatform($id,$platformID,$statusSet,$buildSet) 
   *
   * returns recordset with:
   * test cases that has at least ONE of requested status 
   * on LAST EXECUTION ON At Least ONE of builds present on Build Set (Partial), for a platform
   *
   * If build set is EMPTY
   * on LAST EXECUTION ON At Least ONE of ALL ACTIVE builds (full), for a platform
   *
   * Example:
   * 
   * Test Plan: PLAN B 
   * Builds: B1,B2,B3
   * Test Cases: TC-100, TC-200,TC-300
   *
   * Test Case - Build - LAST Execution status
   * TC-100      B1      Passed
   * TC-100      B2      FAILED
   * TC-100      B3      Not Run
   *
   * TC-200      B1      FAILED
   * TC-200      B2      FAILED
   * TC-200      B3      BLOCKED
   *
   * TC-300      B1      Passed
   * TC-300      B2      Passed
   * TC-300      B3      BLOCKED
   *
   * TC-400      B1      FAILED
   * TC-400      B2      BLOCKED
   * TC-400      B3      FAILED
   * 
   * Request 1:
   * Provide test cases with status in ('Passed','BLOCKED')
   * ON At Least ONE, OF ALL ACTIVE Builds
   *
   * ANSWER:
   * TC-200, TC300, TC400
   *
   * Request 2: ????
   * Provide test cases with status in ('FAILED','BLOCKED')
   * ON ALL ACTIVE Builds
   *
   * ANSWER:
   * TC-300, TC-400
   *
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsStatusSetPartialOnPlatform($id,$platformID,$statusSet,$buildSet=null) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $statusInClause = implode("','",$statusSet);
    list($safe_id,$buildsCfg,$sqlLEBBP) = $this->helperGetHits($id,$platformID,$buildSet);

    $sql =   " /* $debugMsg */ " .
        " SELECT DISTINCT NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        $buildsCfg['statusClause'] .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TPTCV.tcversion_id " .

        " /* Get Latest Execution by BUILD and PLATFORM  */ " .
        " JOIN ({$sqlLEBBP}) AS LEBBP " .
        " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
        " AND LEBBP.platform_id = TPTCV.platform_id " .
        " AND LEBBP.build_id = B.id " .
        " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .

        " /* Get STATUS INFO From Executions */ " .
        " JOIN {$this->tables['executions']} E " .
        " ON  E.id = LEBBP.id " .
        " AND E.tcversion_id = LEBBP.tcversion_id " .
        " AND E.testplan_id = LEBBP.testplan_id " .
        " AND E.platform_id = LEBBP.platform_id " .
        " AND E.build_id = LEBBP.build_id " .
        
        " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] . 
        " AND TPTCV.platform_id=" . $safe_id['platform'] . 
        " AND E.build_id IN ({$buildsCfg['inClause']}) " .
        " AND E.status IN('{$statusInClause}') ";

    $groupBy = ' GROUP BY ' . ((DB_TYPE == 'mssql') ? 'parent_id ':'tcase_id');
    $sql .= $groupBy;

    unset($safe_id,$buildsCfg,$sqlLEBBP);

    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return $recordset;

    
  }

  /**
   * getHitsSameStatusFullOnPlatform($id,$platformID,$statusSet,$buildSet) 
   *
   * returns recordset with:
   * test cases that has at least ONE of requested status 
   * ON LAST EXECUTION ON ALL builds on buils set (full) , for a platform
   *
   * If build set is NULL => ON LAST EXECUTION ON ALL ACTIVE builds (full), for a platform
   */
  function getHitsSameStatusFullOnPlatform($id,$platformID,$statusSet,$buildSet=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    return $this->helperGetHitsSameStatusOnPlatform('full',$id,$platformID,$statusSet,$buildSet);
  }




  /**
   * getHitsSameStatusFullALOP($id,$statusSet,$buildSet) 
   *
   * returns recordset with:
   * test cases that has at least ONE of requested status 
   * ON LAST EXECUTION ON ALL builds on buils set (full) , for a platform
   *
   * If build set is NULL => ON LAST EXECUTION ON ALL ACTIVE builds (full), for a platform
   * 
   * @internal revisions:
   * 20120919 - asimon - TICKET 5226: Filtering by test result did not always show the correct matches
   */
  function getHitsSameStatusFullALOP($id,$statusSet,$buildSet=null)
  {
    // On Postgresql 
    // An output column’s name can be used to refer to the column’s value in ORDER BY and GROUP BY clauses, 
    // but not in the WHERE or HAVING clauses; there you must write out the expression instead.


    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    list($safe_id,$buildsCfg,$sqlLEX) = $this->helperGetHits($id,null,$buildSet,
                                                             array('ignorePlatform' => true));

    // 20120919 - asimon - TICKET 5226: Filtering by test result did not always show the correct matches
    // The filtering for "not run" status was simply not implemented for the case 
    // of not using platforms. Maybe that part was forgotten when refactoring the filters.
    // I adopted logic from helperGetHitsSameStatusOnPlatform() to get this working.
    $flippedStatusSet = array_flip($statusSet);  // (code => idx)
    $get = array('notRun' => isset($flippedStatusSet[$this->notRunStatusCode]), 'otherStatus' => false);
    $hits = array('notRun' => array(), 'otherStatus' => array());

    if($get['notRun'])
    {
      $notRunSQL = " /* $debugMsg */ " .
                   " /* COUNT() is needed as parameter for HAVING clause */ " .
                   " SELECT COUNT(0) AS COUNTER, NHTCV.parent_id AS tcase_id" .
                   " FROM {$this->tables['testplan_tcversions']} TPTCV " .
                   " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
                   $buildsCfg['statusClause'] .

                   " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON " .
                   " NHTCV.id = TPTCV.tcversion_id " .

                   " LEFT OUTER JOIN {$this->tables['executions']} E ON " .
                   " E.testplan_id = TPTCV.testplan_id " .
                   " AND E.build_id = B.id " .
                   " AND E.tcversion_id = TPTCV.tcversion_id " .

                   " WHERE TPTCV.testplan_id = " . $safe_id['tplan']  .
                   " AND E.status IS NULL ";
      
      $groupBy = ' GROUP BY ' . ((DB_TYPE == 'mssql') ? 'parent_id ':'tcase_id');
      $notRunSQL .= $groupBy .
                    " HAVING COUNT(0) = " . intval($buildsCfg['count']) ;

      $hits['notRun'] = $this->db->fetchRowsIntoMap($notRunSQL,'tcase_id');

      unset($statusSet[$flippedStatusSet[$this->notRunStatusCode]]);
    }
        
    $get['otherStatus'] = count($statusSet) > 0;
    if($get['otherStatus'])
    {
      $statusInClause = implode("','",$statusSet);
            
      // ATTENTION:
      // if I've requested (Passed or Blocked) on ALL BUILDS
      // Have 2 results for build number.

      // That logic is wrong when filtering for the SAME STATUS on ALL builds.
      // Maybe copy/paste-error on refactoring? 
      // Example: With 3 builds and filtering for FAILED or BLOCKED on ALL builds
      // we have to get 3 hits for each test case to be shown, not six hits.
      // $countTarget = intval($buildsCfg['count']) * count($statusSet);
      $countTarget = intval($buildsCfg['count']);
            
      $otherStatusSQL = " /* $debugMsg */ " .
                        " /* Count() to be used on HAVING - ALOP */ " .
                    " SELECT COUNT(0) AS COUNTER ,tcase_id " .
                    " FROM ( " .
                    " SELECT DISTINCT NHTCV.parent_id AS tcase_id, E.build_id " .
                    " FROM {$this->tables['testplan_tcversions']} TPTCV " .
    
                    " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
                    $buildsCfg['statusClause'] .
    
                    " /* Get Test Case ID */ " .
                    " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TPTCV.tcversion_id " .
    
                    " /* Get Latest Execution by BUILD IGNORE PLATFORM  */ " .
                    " JOIN ({$sqlLEX}) AS LEX " .
                    " ON  LEX.testplan_id = TPTCV.testplan_id " .
                    " AND LEX.build_id = B.id " .
                    " AND LEX.tcversion_id = TPTCV.tcversion_id " .
    
                    " /* Get STATUS INFO From Executions */ " .
                    " JOIN {$this->tables['executions']} E " .
                    " ON  E.id = LEX.id " .
                    " AND E.tcversion_id = LEX.tcversion_id " .
                    " AND E.testplan_id = LEX.testplan_id " .
                    " AND E.build_id = LEX.build_id " .
                    
                    " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] . 
                    " AND E.build_id IN ({$buildsCfg['inClause']}) " .
                    " AND E.status IN ('{$statusInClause}')" .
                    " ) SQX ";
            
      $groupBy = ' GROUP BY ' . ((DB_TYPE == 'mssql') ? 'parent_id ':'tcase_id');
      $otherStatusSQL .= $groupBy .
                         " HAVING COUNT(0) = " . $countTarget ;
    
      $hits['otherStatus'] = $this->db->fetchRowsIntoMap($otherStatusSQL,'tcase_id');
    }
        
    // build results record set
    $hitsFoundOn = array();
    $hitsFoundOn['notRun'] = count($hits['notRun']) > 0;
    $hitsFoundOn['otherStatus'] = count($hits['otherStatus']) > 0;

    if($hitsFoundOn['notRun'] && $hitsFoundOn['otherStatus'])
    {
      $items = array_merge(array_keys($hits['notRun']), array_keys($hits['otherStatus']));
    }
    else if($hitsFoundOn['notRun'])
    {
      $items = array_keys($hits['notRun']);
    }
    else if($hitsFoundOn['otherStatus'])
    {
      $items = array_keys($hits['otherStatus']);
    }

        
    return is_null($items) ? $items : array_flip($items);
  } 



  /**
   * getHitsNotRunOnBuildPlatform($id,$platformID,$buildID)
   *
   * returns recordset with:
   * test cases with NOT RUN status on SPECIFIC build for a PLATFORM.
   * 
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsNotRunOnBuildPlatform($id,$platformID,$buildID) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql =   " /* $debugMsg */ " .
        " SELECT NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON " .
        " NHTCV.id = TPTCV.tcversion_id " .

        " /* Work on Executions */ " .
        " LEFT OUTER JOIN {$this->tables['executions']} E ON " .
        " E.testplan_id = TPTCV.testplan_id " .
        " AND E.platform_id = TPTCV.platform_id " .
        " AND E.tcversion_id = TPTCV.tcversion_id " .
        " AND E.build_id = " . intval($buildID) .
        
        " WHERE TPTCV.testplan_id = " . intval($id) . 
        " AND TPTCV.platform_id = " . intval($platformID) . 
        " AND E.status IS NULL ";

    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return is_null($recordset) ? $recordset : array_flip(array_keys($recordset));
  }


  /**
   * getHitsNotRunOnBuildALOP($id,$buildID)
   *
   * returns recordset with:
   * test cases with NOT RUN status on SPECIFIC build On AT LEAST ONE PLATFORM. (ALOP)
   * 
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsNotRunOnBuildALOP($id,$buildID) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql =   " /* $debugMsg */ " .
        " SELECT DISTINCT NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON " .
        " NHTCV.id = TPTCV.tcversion_id " .

        " /* Work on Executions */ " .
        " LEFT OUTER JOIN {$this->tables['executions']} E ON " .
        " E.testplan_id = TPTCV.testplan_id " .
        " AND E.tcversion_id = TPTCV.tcversion_id " .
        " AND E.build_id = " . intval($buildID) .

        " WHERE TPTCV.testplan_id = " . intval($id) . 
        " AND E.status IS NULL ";

    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return is_null($recordset) ? $recordset : array_flip(array_keys($recordset));
  }



  /**
   * getHitsStatusSetOnBuildPlatform($id,$platformID,$buildID,$statusSet) 
   *
   * returns recordset with:
   * test cases with LAST EXECUTION STATUS on SPECIFIC build for a PLATFORM, IN status SET.
   * 
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsStatusSetOnBuildPlatform($id,$platformID,$buildID,$statusSet) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    list($safe_id,$buildsCfg,$sqlLEBBP) = $this->helperGetHits($id,$platformID,null,array('buildID' => $buildID));
    
    $safe_id['build'] = intval($buildID);
    $statusList = (array)$statusSet;

    // Manage also not run
    $notRunHits = null;
    $dummy = array_flip($statusList);
    if( isset($dummy[$this->notRunStatusCode]) )
    {
      tLog(__FUNCTION__ . ':: getHitsNotRunOnBuildPlatform','DEBUG');
      $notRunHits = $this->getHitsNotRunOnBuildPlatform($safe_id['tplan'],$safe_id['platform'],$safe_id['build']);
      unset($statusList[$dummy[$this->notRunStatusCode]]);
    }

    $statusInClause = implode("','",$statusList);
    $sql =   " /* $debugMsg */ " .
        " SELECT NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TPTCV.tcversion_id " .

        " /* Get Latest Execution by BUILD and PLATFORM  */ " .
        " JOIN ({$sqlLEBBP}) AS LEBBP " .
        " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
        " AND LEBBP.platform_id = TPTCV.platform_id " .
        " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .
        " AND LEBBP.build_id = " . $safe_id['build'] .

        " /* Get STATUS INFO From Executions */ " .
        " JOIN {$this->tables['executions']} E " .
        " ON  E.id = LEBBP.id " .
        " AND E.tcversion_id = LEBBP.tcversion_id " .
        " AND E.testplan_id = LEBBP.testplan_id " .
        " AND E.platform_id = LEBBP.platform_id " .
        " AND E.build_id = LEBBP.build_id " .

        " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] . 
        " AND TPTCV.platform_id = " . $safe_id['platform'] . 
        " AND E.build_id  = " . $safe_id['build'] . 
        " AND E.status IN('{$statusInClause}')";
        
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    $hits = is_null($recordset) ? $recordset : array_flip(array_keys($recordset));
    
    $items = (array)$hits + (array)$notRunHits; 
    return count($items) > 0 ? $items : null;
  }


  /**
   * getHitsStatusSetOnBuildALOP($id,$buildID,$statusSet) 
   *
   * returns recordset with:
   * test cases with LAST EXECUTION STATUS on SPECIFIC build for At Least One PLATFORM, 
   * IN status SET.
   * 
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsStatusSetOnBuildALOP($id,$buildID,$statusSet) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    list($safe_id,$buildsCfg,$sqlLEX) = $this->helperGetHits($id,null,null,
                                 array('buildID' => $buildID, 
                                        'ignorePlatform' => true));
    
    $safe_id['build'] = intval($buildID);
    $statusList = (array)$statusSet;

    // Manage also not run
    $notRunHits = null;
    $dummy = array_flip($statusList);
    if( isset($dummy[$this->notRunStatusCode]) )
    {
      $notRunHits = $this->getHitsNotRunOnBuildALOP($safe_id['tplan'],$safe_id['build']);
      unset($statusList[$dummy[$this->notRunStatusCode]]);
    }

    $statusInClause = implode("','",$statusList);
    $sql =   " /* $debugMsg */ " .
        " SELECT DISTINCT NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TPTCV.tcversion_id " .

        " /* Get Latest Execution by BUILD IGNORE PLATFORM  */ " .
        " JOIN ({$sqlLEX}) AS LEX " .
        " ON  LEX.testplan_id = TPTCV.testplan_id " .
        " AND LEX.tcversion_id = TPTCV.tcversion_id " .
        " AND LEX.build_id = " . $safe_id['build'] .

        " /* Get STATUS INFO From Executions */ " .
        " JOIN {$this->tables['executions']} E " .
        " ON  E.id = LEX.id " .
        " AND E.tcversion_id = LEX.tcversion_id " .
        " AND E.testplan_id = LEX.testplan_id " .
        " AND E.build_id = LEX.build_id " .

        " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] . 
        " AND E.build_id  = " . $safe_id['build'] . 
        " AND E.status IN('{$statusInClause}')";
    
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    $hits = is_null($recordset) ? $recordset : array_flip(array_keys($recordset));
    
    $items = (array)$hits + (array)$notRunHits; 
    return count($items) > 0 ? $items : null;
  }


  /**
   * getHitsStatusSetOnLatestExecALOP($id,$statusSet,$buildSet)  
   *
   * returns recordset with:
   * test cases that has at least ONE of requested status 
   * on ABSOLUTE LASTEST EXECUTION considering all builds on build set IGNORING platform.
   *
   * If build set is NULL, we will analyse  ALL ACTIVE builds (full) IGNORING platform.
   *
   * IMPORTANT / CRITIC:  THIS DOES NOT WORK for Not Run STATUS
   *            HAS NO SENSE, because Not Run IN NOT SAVED to DB
   *            => we can not find LATEST NON RUN
   * Example:
   * 
   *
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsStatusSetOnLatestExecALOP($id,$statusSet,$buildSet=null) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    list($safe_id,$buildsCfg,$sqlLEX) = $this->helperGetHits($id,null,$buildSet,
                                 array('ignorePlatform' => true,
                                      'ignoreBuild' => true));
    
    // Check if 'not run' in present in statusSet => throw exception
    $statusList = (array)$statusSet;
    $dummy = array_flip($statusList);
    if( isset($dummy[$this->notRunStatusCode]) )
    {
      throw new Exception (__METHOD__ . ':: Status Not Run can not be used');  
    }
    $statusInClause = implode("','",$statusList);

    $sql = " /* $debugMsg */ " .
        " SELECT MAX(LEX.id) AS latest_exec_id ,NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        $buildsCfg['statusClause'] .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TPTCV.tcversion_id " .

        " /* Get Latest Execution  IGNORE BUILD, PLATFORM  */ " .
        " JOIN ({$sqlLEX}) AS LEX " .
        " ON  LEX.testplan_id = TPTCV.testplan_id " .
        " AND LEX.tcversion_id = TPTCV.tcversion_id " .

        " /* Get STATUS INFO From Executions */ " .
        " JOIN {$this->tables['executions']} E " .
        " ON  E.id = LEX.id " .
        " AND E.tcversion_id = LEX.tcversion_id " .
        " AND E.testplan_id = LEX.testplan_id " .
        " AND E.build_id = B.id " .

        " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] . 
        " AND E.build_id IN ({$buildsCfg['inClause']}) " .
        " AND E.status IN('{$statusInClause}') ";

    $groupBy = ' GROUP BY ' . ((DB_TYPE == 'mssql') ? 'parent_id ':'tcase_id');
    $sql .= $groupBy;

    unset($safe_id,$buildsCfg,$sqlLEX);
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return is_null($recordset) ? $recordset : array_flip(array_keys($recordset));
  }



  /**
   * getHitsStatusSetOnLatestExecOnPlatform($id,$platformID,$statusSet,$buildSet)  
   *
   * returns recordset with:
   * test cases that has at least ONE of requested status 
   * on ABSOLUTE LASTEST EXECUTION considering all builds on build set, for a platform
   *
   * If build set is NULL, we will analyse  ALL ACTIVE builds (full), for a platform
   *
   * IMPORTANT / CRITIC:  THIS DOES NOT WORK for Not Run STATUS
   *            HAS NO SENSE, because Not Run IN NOT SAVED to DB
   *            => we can not find LATEST NON RUN
   * Example:
   * 
   *
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsStatusSetOnLatestExecOnPlatform($id,$platformID,$statusSet,$buildSet=null) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    list($safe_id,$buildsCfg,$sqlLEBP) = $this->helperGetHits($id,$platformID,$buildSet,
                                  array('ignoreBuild' => true));
    
    // Check if 'not run' in present in statusSet => throw exception
    $statusList = (array)$statusSet;
    $dummy = array_flip($statusList);
    if( isset($dummy[$this->notRunStatusCode]) )
    {
      throw new Exception (__METHOD__ . ':: Status Not Run can not be used');  
    }
    $statusInClause = implode("','",$statusList);

    // --------------------------------------------------------------------------------------    
    $sql =   " /* $debugMsg */ " .
        " SELECT MAX(LEBP.id) AS latest_exec_id ,NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        $buildsCfg['statusClause'] .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TPTCV.tcversion_id " .

        " /* Get Latest Execution on PLATFORM IGNORE BUILD */ " .
        " JOIN ({$sqlLEBP}) AS LEBP " .
        " ON  LEBP.testplan_id = TPTCV.testplan_id " .
        " AND LEBP.platform_id = TPTCV.platform_id " .
        " AND LEBP.tcversion_id = TPTCV.tcversion_id " .
        // " AND LEBP.build_id = B.id " .

        " /* Get STATUS INFO From Executions */ " .
        " JOIN {$this->tables['executions']} E " .
        " ON  E.id = LEBP.id " .
        " AND E.tcversion_id = LEBP.tcversion_id " .
        " AND E.testplan_id = LEBP.testplan_id " .
        " AND E.platform_id = LEBP.platform_id " .
        // " AND E.build_id = LEBBP.build_id " .

        " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] . 
        " AND TPTCV.platform_id=" . $safe_id['platform'] . 
        " AND E.build_id IN ({$buildsCfg['inClause']}) " .
        " AND E.status IN ('{$statusInClause}') ";

    $groupBy = ' GROUP BY ' . ((DB_TYPE == 'mssql') ? 'parent_id ':'tcase_id');
    $sql .= $groupBy;

    unset($safe_id,$buildsCfg,$sqlLEBP);
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return is_null($recordset) ? $recordset : array_flip(array_keys($recordset));
  }



  /**
   * getHitsSameStatusPartialOnPlatform($id,$platformID,$statusSet,$buildSet) 
   *
   * returns recordset with:
   *
   * test cases that has at least ONE of requested status 
   * ON LAST EXECUTION ON AT LEAST ONE OF builds on build set, for a platform
   *
   * If build set is empty
   * test cases that has at least ONE of requested status 
   * ON LAST EXECUTION ON AT LEAST ONE OF ALL ACTIVE builds, for a platform
   *
   */
  function getHitsSameStatusPartialOnPlatform($id,$platformID,$statusSet,$buildSet=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    return $this->helperGetHitsSameStatusOnPlatform('partial',$id,$platformID,$statusSet,$buildSet);
  } 


  /**
   * getHitsSameStatusPartialALOP($id,$statusSet) 
   *
   * returns recordset with:
   *
   * test cases that has at least ONE of requested status 
   * ON LAST EXECUTION ON AT LEAST ONE OF builds on build set, for a platform
   *
   * If build set is empty
   * test cases that has at least ONE of requested status 
   * ON LAST EXECUTION ON AT LEAST ONE OF ALL ACTIVE builds, for a platform
   *
   */
  function getHitsSameStatusPartialALOP($id,$statusSet,$buildSet=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $getHitsNotRunMethod = 'getHitsNotRunPartialALOP';
    $getHitsStatusSetMethod = 'getHitsStatusSetPartialALOP';
        
    // Needed because, may be we will need to remove an element
    $statusSetLocal = (array)$statusSet;  

    $items = null;
    $hits = array('notRun' => array(), 'otherStatus' => array());
    $dummy = array_flip($statusSetLocal);  // (code => idx)
    $get = array('notRun' => isset($dummy[$this->notRunStatusCode]), 'otherStatus' => false);
    
    
    if($get['notRun']) 
    {
      tLog(__METHOD__ . ":: \$tplan_mgr->$getHitsNotRunMethod", 'DEBUG');
      $hits['notRun'] = (array)$this->$getHitsNotRunMethod($id,$buildSet);  
      unset($statusSetLocal[$dummy[$this->notRunStatusCode]]);
    }
    
    if( ($get['otherStatus']=(count($statusSetLocal) > 0)) )
    {
      tLog(__METHOD__ . ":: \$tplan_mgr->$getHitsStatusSetMethod", 'DEBUG');
      $hits['otherStatus'] = (array)$this->$getHitsStatusSetMethod($id,$statusSetLocal,$buildSet);  
    }

    // build results recordset
    $hitsFoundOn = array();
    $hitsFoundOn['notRun'] = count($hits['notRun']) > 0;
    $hitsFoundOn['otherStatus'] = count($hits['otherStatus']) > 0;
    
    
    if($get['notRun'] && $get['otherStatus'])
    {
      if( $hitsFoundOn['notRun'] && $hitsFoundOn['otherStatus'] )
      {
        $items = array_keys($hits['notRun']) + array_keys($hits['otherStatus']);
      }
    } 
    else if($get['notRun'] && $hitsFoundOn['notRun'])
    {
      $items = array_keys($hits['notRun']);
    }
    else if($get['otherStatus'] && $hitsFoundOn['otherStatus'])
    {
      $items = array_keys($hits['otherStatus']);
    }
    
    return is_null($items) ? $items : array_flip($items);
  } 



  /**
   * getHitsStatusSetPartialALOP($id,$platformID,$statusSet,$buildSet) 
   *
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsStatusSetPartialALOP($id,$statusSet,$buildSet=null) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $statusInClause = implode("','",$statusSet);
    list($safe_id,$buildsCfg,$sqlLEX) = $this->helperGetHits($id,null,$buildSet,
                                     array('ignorePlatform' => true));

    $sql =   " /* $debugMsg */ " .
        " SELECT DISTINCT NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        $buildsCfg['statusClause'] .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TPTCV.tcversion_id " .

        " /* Get Latest Execution by JUST BUILD IGNORE PLATFORM */ " .
        " JOIN ({$sqlLEX}) AS LEX " .
        " ON  LEX.testplan_id = TPTCV.testplan_id " .
        " AND LEX.build_id = B.id " .
        " AND LEX.tcversion_id = TPTCV.tcversion_id " .

        // " AND LEX.platform_id = TPTCV.platform_id " .

        " /* Get STATUS INFO From Executions */ " .
        " JOIN {$this->tables['executions']} E " .
        " ON  E.id = LEX.id " .
        " AND E.tcversion_id = LEX.tcversion_id " .
        " AND E.testplan_id = LEX.testplan_id " .
        " AND E.build_id = LEX.build_id " .

        // " AND E.platform_id = LEX.platform_id " .
        
        " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] . 
        " AND E.build_id IN ({$buildsCfg['inClause']}) " .
        " AND E.status IN ('{$statusInClause}') ";


    unset($safe_id,$buildsCfg,$sqlLEX);

    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return $recordset;
  }



  /**
   * getHitsNotRunPartialALOP($id,buildSet) 
   *
   * returns recordset with:
   *
   * test cases with NOT RUN status at LEAST ON ONE of builds 
   * present on build set (Partial), IGNORING Platforms
   * 
   * If build set is empty:
   * test cases with NOT RUN status at LEAST ON ONE of builds 
   * present on ACTIVE BUILDS set (Partial), IGNORING Platforms
   *
   * 
   * Example: (TO BE REWORKED)
   * 
   * Test Plan: PLAN B 
   * Builds: B1,B2,B3
   * Test Cases: TC-100, TC-200,TC-300
   *
   * Test Case - Build - LAST Execution status
   * TC-100      B1      Passed
   * TC-100      B2      FAILED
   * TC-100      B3      Not Run => to have this status means THAT HAS NEVER EXECUTED ON B3
   *
   * TC-200      B1      FAILED
   * TC-200      B2      FAILED
   * TC-200      B3      BLOCKED
   *
   * TC-300      B1      Passed
   * TC-300      B2      Passed
   * TC-300      B3      BLOCKED
   *
   * TC-400      B1      FAILED
   * TC-400      B2      BLOCKED
   * TC-400      B3      FAILED
   * 
   * Request :
   * Provide test cases with status 'NOT RUN'
   * ON At Least ON OF all ACTIVE Builds
   *
   * ANSWER:
   * TC-100
   *
   * @return
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getHitsNotRunPartialALOP($id,$buildSet=null) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    list($safe_id,$buildsCfg,$sqlLEX) = $this->helperGetHits($id,null,$buildSet,
                                   array('ignorePlatform' => true));

    $sql =   " /* $debugMsg */ " .
        " SELECT DISTINCT NHTCV.parent_id AS tcase_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " JOIN {$this->tables['builds']} B ON B.testplan_id = TPTCV.testplan_id " .
        $buildsCfg['statusClause'] .

        " /* Get Test Case ID */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON " .
        " NHTCV.id = TPTCV.tcversion_id " .

        " /* Executions, looking for status NULL (remember NOT RUN is not written on DB) */ " .
        " LEFT OUTER JOIN {$this->tables['executions']} E " .
        " ON  E.testplan_id = TPTCV.testplan_id " .
        " AND E.platform_id = TPTCV.platform_id " .
        " AND E.build_id = B.id " .
        " AND E.tcversion_id = TPTCV.tcversion_id " .
        
        " WHERE TPTCV.testplan_id = " . $safe_id['tplan'] .
        " AND B.id IN ({$buildsCfg['inClause']}) " .
        " AND E.status IS NULL ";
        
    $recordset = $this->db->fetchRowsIntoMap($sql,'tcase_id');
    return $recordset;
  }



  /**
   * helperGetHitsSameStatusOnPlatform($mode,$id,$platformID,$statusSet,$buildSet)
   * 
     * @internal revisions:
   * 20120919 - asimon - TICKET 5226: Filtering by test result did not always show the correct matches
   */
  function helperGetHitsSameStatusOnPlatform($mode,$id,$platformID,$statusSet,$buildSet=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    switch($mode)
    {
      case 'partial':
        $getHitsNotRunMethod = 'getHitsNotRunPartialOnPlatform';
        $getHitsStatusSetMethod = 'getHitsStatusSetPartialOnPlatform';
        
      break;
      
      case 'full':
        $getHitsNotRunMethod = 'getHitsNotRunFullOnPlatform';
        $getHitsStatusSetMethod = 'getHitsStatusSetFullOnPlatform';
      break;
    }
    
    // Needed because, may be we will need to remove an element
    $statusSetLocal = (array)$statusSet;  

    $items = null;
    $hits = array('notRun' => array(), 'otherStatus' => array());

    $dummy = array_flip($statusSetLocal);  // (code => idx)
    $get = array('notRun' => isset($dummy[$this->notRunStatusCode]), 'otherStatus' => false);

    
    if($get['notRun']) 
    {
      $hits['notRun'] = (array)$this->$getHitsNotRunMethod($id,$platformID,$buildSet);  
      unset($statusSetLocal[$dummy[$this->notRunStatusCode]]);
    }
    if( ($get['otherStatus']=(count($statusSetLocal) > 0)) )
    {
      $hits['otherStatus'] = (array)$this->$getHitsStatusSetMethod($id,$platformID,$statusSetLocal,$buildSet);  
    }

    // build results recordset
    $hitsFoundOn = array();
    $hitsFoundOn['notRun'] = count($hits['notRun']) > 0;
    $hitsFoundOn['otherStatus'] = count($hits['otherStatus']) > 0;

        //20120919 - asimon - TICKET 5226: Filtering by test result did not always show the correct matches
        //if($get['notRun'] && $get['otherStatus'])
        //{
            //if( $hitsFoundOn['notRun'] && $hitsFoundOn['otherStatus'] )
        // The problem with this if clause:
        // When $get['notRun'] && $get['otherStatus'] evaluated as TRUE but there were no hits 
        // in one of $hitsFoundOn['notRun'] or $hitsFoundOn['otherStatus'], then no results were returned at all.
        
    if($hitsFoundOn['notRun'] && $hitsFoundOn['otherStatus'])
    {
            // THIS DOES NOT WORK with numeric keys  
            // $items = array_merge(array_keys($hits['notRun']),array_keys($hits['otherStatus']));
            //$items = array_keys($hits['notRun']) + array_keys($hits['otherStatus']);

            // 20120919 - asimon - TICKET 5226: Filtering by test result did not always show the correct matches
            // 
            // ATTENTION: Using the + operator instead of array_merge() for numeric keys is wrong!
            //
            // Quotes from documentation http://www.php.net/manual/en/function.array-merge.php:
            // 
            // array_merge(): "If the input arrays have the same string keys, then the later value for that key 
            // will overwrite the previous one. If, however, the arrays contain numeric keys, 
            // the later value will not overwrite the original value, but will be appended."
            // 
            // + operator: "The keys from the first array will be preserved. 
            // If an array key exists in both arrays, then the element from the first array will be used 
            // and the matching key's element from the second array will be ignored."
            // 
            // That means if there were 5 results in $hits['notRun']) and 10 results in $hits['otherStatus']), 
            // the first 5 testcases from $hits['otherStatus']) were not in the result set because of the + operator.
            // 
            // After using array_keys() we have numeric keys => we HAVE TO USE array_merge().
            $items = array_merge(array_keys($hits['notRun']), array_keys($hits['otherStatus']));
    } 
    else if($hitsFoundOn['notRun'])
    {
      $items = array_keys($hits['notRun']);
    }
    else if($hitsFoundOn['otherStatus'])
    {
      $items = array_keys($hits['otherStatus']);
    }
        
    return is_null($items) ? $items : array_flip($items);
  } 


  /**
   * helperGetHits($id,$platformID,$buildSet,$options)
   *
   *
   */
  function helperGetHits($id,$platformID,$buildSet=null,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my['options'] = array('buildID' => 0, 'ignorePlatform' => false, 'ignoreBuild' => false);
    $my['options'] = array_merge($my['options'],(array)$options);
    
    
    $safe_id['tplan'] = intval($id);
    $safe_id['platform'] = intval($platformID);
    
    $buildsCfg['statusClause'] = "";
    $buildsCfg['inClause'] = "";
    $buildsCfg['count'] = 0;

    if($my['options']['buildID'] <= 0)
    {
      if( is_null($buildSet) )
      {
        $buildSet = array_keys($this->get_builds($id, self::ACTIVE_BUILDS));
        $buildsCfg['statusClause'] = " AND B.active = 1 ";
      }
      $buildsCfg['count'] = count($buildSet);
      $buildsCfg['inClause'] = implode(",",$buildSet);
    }
    else
    {
      $buildsCfg['inClause'] = intval($my['options']['buildID']);
    }

    $platformClause = " AND EE.platform_id = " . $safe_id['platform'];
    $platformField = " ,EE.platform_id ";
    if( $my['options']['ignorePlatform'] )
    {
      $platformClause = " ";
      $platformField = " ";
    }

    $buildField = " ,EE.build_id ";
    if( $my['options']['ignoreBuild'] )
    {
      $buildField = " ";
    }



    $sqlLEX = " SELECT EE.tcversion_id,EE.testplan_id {$platformField} {$buildField} ," .
          " MAX(EE.id) AS id " .
          " FROM {$this->tables['executions']} EE " . 
          " WHERE EE.testplan_id = " . $safe_id['tplan'] . 
          " AND EE.build_id IN ({$buildsCfg['inClause']}) " .
          $platformClause .
          " GROUP BY EE.tcversion_id,EE.testplan_id {$platformField} {$buildField} ";

    return array($safe_id,$buildsCfg,$sqlLEX);
  }


  /**
   * 
   *
   *
   */
  function helperConcatTCasePrefix($id)
  {
    // Get test case prefix
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $io = $this->tree_manager->get_node_hierarchy_info($id);
      
    list($prefix,$garbage) = $this->tcase_mgr->getPrefix(null,$io['parent_id']);
    $prefix .= $this->tcaseCfg->glue_character;
    $concat = $this->db->db->concat("'{$prefix}'",'TCV.tc_external_id');

    unset($io);
    unset($garbage);
    unset($prefix);
    
    return $concat;
  }


  /**
   * 
   *
   *
   */
  function helperColumns($tplanID,&$filters,&$opt)
  {
    $safe_id = intval($tplanID);    
    
    $join['tsuite'] = '';
    $join['builds'] = '';
    
    $order_by['exec'] = '';

    $fields['tcase'] = '';
    $fields['tsuite'] = '';
    $fields['priority'] = " (urgency * importance) AS priority ";
    
    
    $fields['ua'] = " UA.build_id AS assigned_build_id, UA.user_id,UA.type,UA.status,UA.assigner_id ";

    $default_fields['exec'] = " E.id AS exec_id, E.tcversion_number," .
                          " E.tcversion_id AS executed, E.testplan_id AS exec_on_tplan, {$more_exec_fields}" .
                       " E.execution_type AS execution_run_type, " .
                       " E.execution_ts, E.tester_id, E.notes as execution_notes," .
                       " E.build_id as exec_on_build, ";

    $fields['exec'] = $default_fields['exec'];
    if($opt['execution_details'] == 'add_build')
    {
      $fields['exec'] .= 'E.build_id,B.name AS build_name, B.active AS build_is_active,';        
    }
    if( is_null($opt['forced_exec_status']) )
    {
      $fields['exec'] .= " COALESCE(E.status,'" . $this->notRunStatusCode . "') AS exec_status ";
    }
    else
    {
      $fields['exec'] .= " '{$opt['forced_exec_status']}'  AS exec_status ";
    }

    switch($opt['details'])
    {
      case 'full':
        $fields['tcase'] = 'TCV.summary,';
        $fields['tsuite'] = 'NH_TSUITE.name as tsuite_name,';
        $join['tsuite'] = " JOIN {$this->tables['nodes_hierarchy']} NH_TSUITE " . 
                  " ON NH_TCASE.parent_id = NH_TSUITE.id ";
        $opt['steps_info'] = true;
      break;


      case 'summary':
        $fields['tcase'] = 'TCV.summary,';
      break;

      case 'spec_essential':   // TICKET 4710
        $fields['exec'] = '';
        $fields['ua'] = '';
        $join['builds'] = '';
        $filters['ua'] = '';
      break;

      
      case 'exec_tree_optimized':   // TICKET 4710
        // if all following filters are NOT USED, then we will REMOVE executions JOIN
        if( $filters['builds'] == '' && $filters['executions'] == '')
        {
          $join['builds'] = '';
          $join['executions'] = '';

          $fields['exec'] = '';
          $fields['ua'] = '';

          $filters['executions'] = '';
          $filters['ua'] = '';
          $order_by['exec'] = '';
        }      
      break;

      case 'report':   // Results Performance
        $fields['ua'] = '';
        $filters['ua'] = '';
      break;
    }

    if( !is_null($opt['exclude_info']) )
    {
      foreach($opt['exclude_info'] as $victim)
      {
        switch($victim)
        {
          case 'exec_info':
            $fields['exec'] = '';
            $order_by['exec'] = " ";
            $join['executions'] = '';
          break;
          
          case 'priority':
            $fields['priority'] = '';
          break;

          case 'assigned_on_build':
          case 'assigned_to':
            $fields['ua'] = '';
            $filters['ua'] = '';
          break;

        }
      
      }
    
    }

    $fullEID = $this->helperConcatTCasePrefix($safe_id);
    $sql = " SELECT NH_TCASE.parent_id AS testsuite_id, {$fields['tcase']} {$fields['tsuite']} " .
           " NH_TCV.parent_id AS tc_id, NH_TCASE.node_order AS z, NH_TCASE.name," .
           " TPTCV.platform_id, PLAT.name as platform_name ,TPTCV.id AS feature_id, " .
           " TPTCV.tcversion_id AS tcversion_id,  " .
           " TPTCV.node_order AS execution_order, TPTCV.creation_ts AS linked_ts, " .
           " TPTCV.author_id AS linked_by,TPTCV.urgency," .
           " TCV.version AS version, TCV.active, TCV.tc_external_id AS external_id, " .
           " TCV.execution_type,TCV.importance," .  
           " $fullEID AS full_external_id";

    $dummy = array('exec','priority','ua');
    foreach($dummy as $ki)
    {        
      $sql .= ($fields[$ki] != '' ? ',' . $fields[$ki] : '');
    }

    if( $fields['ua'] != '' )
    {
      $join['ua'] = " LEFT OUTER JOIN {$this->tables['user_assignments']} UA ON " .
              " UA.feature_id = TPTCV.id " .
              " AND UA.build_id IN (" . $this->helperBuildInClause($tplanID,$filters,$opt) . ")";
    }
  

    return array($sql,$join,$order_by);
  }


  /**
   * 
   *
   *
   */
  function helperLastExecution($tplanID,$filters,$options)
  {
    $safe_id = intval($tplanID);

    $filterBuildActiveStatus = '';
    $activeStatus = null;
    $domain = array('active' => 1, 'inactive' => 0 , 'any' => null);
    if( !is_null($domain[$options['build_active_status']]) )
    {
      $activeStatus = intval($domain[$options['build_active_status']]);
      $filterBuildActiveStatus = " AND BB.active = " . $activeStatus;
    }
    
    $buildsInClause = $this->helperBuildInClause($tplanID,$filters,$options);

    // Last Executions By Build and Platform (LEBBP)
    $sqlLEBBP = " SELECT EE.tcversion_id,EE.testplan_id,EE.platform_id,EE.build_id," .
          " MAX(EE.id) AS id " .
            " FROM {$this->tables['executions']} EE " . 
            " /* use builds table to filter on active status */ " .
            " JOIN {$this->tables['builds']} BB " .
            " ON BB.id = EE.build_id " . 
             " WHERE EE.testplan_id=" . $safe_id . 
          " AND EE.build_id IN ({$buildsInClause}) " .
          $filterBuildActiveStatus .
             " GROUP BY EE.tcversion_id,EE.testplan_id,EE.platform_id,EE.build_id ";

    unset($dummy);
    unset($buildsInClause);
    unset($filterBuildActiveStatus);
    
    return $sqlLEBBP;
  }



  /**
   * 
   *
   *
   */
  function helperBuildInClause($tplanID,$filters,$options)
  {
    $safe_id = intval($tplanID);
    if(!is_null($filters['builds']))
    {
      $dummy = $filters['builds'];
    }
    else
    {
      $activeStatus = null;
      $domain = array('active' => 1, 'inactive' => 0 , 'any' => null);
      if( !is_null($domain[$options['build_active_status']]) )
      {
        $activeStatus = intval($domain[$options['build_active_status']]);
      }
      $dummy = array_keys($this->get_builds($safe_id,$activeStatus));
    }
    
    return implode(",",$dummy);
  }


  /**
   * 
   *
   *
   */
  function helperBuildActiveStatus($filters,$options)
  {
    $activeStatus = null;
    $domain = array('active' => 1, 'inactive' => 0 , 'any' => null);
    if( !is_null($domain[$options['build_active_status']]) )
    {
      $activeStatus = intval($domain[$options['build_active_status']]);
    }
    
    return $activeStatus;
  }


  // This method is intended to return minimal data useful 
  // to create Execution Tree.
  // Status on Latest execution on Build,Platform is needed
  // 
  // @param int $id test plan id
  // @param mixed $filters
  // @param mixed $options  
  // 
  // [tcase_id]: default null => get any testcase
  //             numeric      => just get info for this testcase
  // 
  // 
  // [keyword_id]: default 0 => do not filter by keyword id
  //               numeric/array()   => filter by keyword id
  // 
  // 
  // [assigned_to]: default NULL => do not filter by user assign.
  //                array() with user id to be used on filter
  //                IMPORTANT NOTICE: this argument is affected by
  //             [assigned_on_build]                 
  // 
  // [build_id]: default 0 or null => do not filter by build id
  //             numeric        => filter by build id
  // 
  // 
  // [cf_hash]: default null => do not filter by Custom Fields values
  //
  //
  // [urgencyImportance] : filter only Tc's with certain (urgency*importance)-value 
  //
  // [tsuites_id]: default null.
  //               If present only tcversions that are children of this testsuites
  //               will be included
  //              
  // [exec_type] default null -> all types. 
  // [platform_id]              
  //       
  function getLinkedForExecTree($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;


    $safe['tplan_id'] = intval($id);
    $my = $this->initGetLinkedForTree($safe['tplan_id'],$filters,$options);
   
  
    if( $my['filters']['build_id'] <= 0 )
    {
      // CRASH IMMEDIATELY
      throw new Exception( $debugMsg . " Can NOT WORK with \$my['filters']['build_id'] <= 0");
    }
    
    if( !$my['green_light'] ) 
    {
      // No query has to be run, because we know in advance that we are
      // going to get NO RECORDS
      return null;  
    }

    $platform4EE = " ";
    if( !is_null($my['filters']['platform_id']) )
    {
      $platform4EE = " AND EE.platform_id = " . intval($my['filters']['platform_id']);
    }
  
    $sqlLEBBP = " SELECT EE.tcversion_id,EE.testplan_id,EE.platform_id,EE.build_id," .
                " MAX(EE.id) AS id " .
                " FROM {$this->tables['executions']} EE " . 
                " WHERE EE.testplan_id = " . $safe['tplan_id'] . 
                " AND EE.build_id = " . intval($my['filters']['build_id']) .
                $platform4EE .
                " GROUP BY EE.tcversion_id,EE.testplan_id,EE.platform_id,EE.build_id ";


    // When there is request to filter by BUG ID, because till now (@20131216) BUGS are linked
    // only to EXECUTED test case versions, the not_run piece of union is USELESS
    $union['not_run'] = null;        

    // if(isset($my['filters']['bug_id'])

    if(!isset($my['filters']['bug_id']))
    {
      // adding tcversion on output can be useful for Filter on Custom Field values,
      // because we are saving values at TCVERSION LEVEL
      //  
      $union['not_run'] = "/* {$debugMsg} sqlUnion - not run */" .
                          " SELECT NH_TCASE.id AS tcase_id,TPTCV.tcversion_id,TCV.version," .
                          // $fullEIDClause .
                          " TCV.tc_external_id AS external_id, " .
                          " TPTCV.node_order AS exec_order," .
                          " COALESCE(E.status,'" . $this->notRunStatusCode . "') AS exec_status " .
                          $my['fields']['tsuites'] .
                          
                          " FROM {$this->tables['testplan_tcversions']} TPTCV " .                          
                          " JOIN {$this->tables['tcversions']} TCV ON TCV.id = TPTCV.tcversion_id " .
                          " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
                          " JOIN {$this->tables['nodes_hierarchy']} NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " .
                          $my['join']['ua'] .
                          $my['join']['keywords'] .
                          $my['join']['cf'] .
                          $my['join']['tsuites'] .
                          
                          " LEFT OUTER JOIN {$this->tables['platforms']} PLAT ON PLAT.id = TPTCV.platform_id " .
                
                          " /* Get REALLY NOT RUN => BOTH LE.id AND E.id ON LEFT OUTER see WHERE  */ " .
                          " LEFT OUTER JOIN ({$sqlLEBBP}) AS LEBBP " .
                          " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
                          " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .
                          " AND LEBBP.platform_id = TPTCV.platform_id " .
                          " AND LEBBP.testplan_id = " . $safe['tplan_id'] .
                          " LEFT OUTER JOIN {$this->tables['executions']} E " .
                          " ON  E.tcversion_id = TPTCV.tcversion_id " .
                          " AND E.testplan_id = TPTCV.testplan_id " .
                          " AND E.platform_id = TPTCV.platform_id " .
                          " AND E.build_id = " . $my['filters']['build_id'] .

                          " WHERE TPTCV.testplan_id =" . $safe['tplan_id'] .
                          $my['where']['not_run'] .
                          " /* Get REALLY NOT RUN => BOTH LE.id AND E.id NULL  */ " .
                          " AND E.id IS NULL AND LEBBP.id IS NULL";
    }         


    $union['exec'] = "/* {$debugMsg} sqlUnion - executions */" . 
                     " SELECT NH_TCASE.id AS tcase_id,TPTCV.tcversion_id,TCV.version," .
                     // $fullEIDClause .
                     " TCV.tc_external_id AS external_id, " .
                     " TPTCV.node_order AS exec_order," . 
                     " COALESCE(E.status,'" . $this->notRunStatusCode . "') AS exec_status " .
                     $my['fields']['tsuites'] .

                     " FROM {$this->tables['testplan_tcversions']} TPTCV " .                          
                     " JOIN {$this->tables['tcversions']} TCV ON TCV.id = TPTCV.tcversion_id " .
                     " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
                     " JOIN {$this->tables['nodes_hierarchy']} NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " .
                     $my['join']['ua'] .
                     $my['join']['keywords'] .
                     $my['join']['cf'] .
                     $my['join']['tsuites'] .

                     " LEFT OUTER JOIN {$this->tables['platforms']} PLAT ON PLAT.id = TPTCV.platform_id " .
                     
                     " JOIN ({$sqlLEBBP}) AS LEBBP " .
                     " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
                     " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .
                     " AND LEBBP.platform_id = TPTCV.platform_id " .
                     " AND LEBBP.testplan_id = " . $safe['tplan_id'] .
                     " JOIN {$this->tables['executions']} E " .
                     " ON  E.id = LEBBP.id " .  // TICKET 5191  
                     " AND E.tcversion_id = TPTCV.tcversion_id " .
                     " AND E.testplan_id = TPTCV.testplan_id " .
                     " AND E.platform_id = TPTCV.platform_id " .
                     " AND E.build_id = " . $my['filters']['build_id'] .

                     $my['join']['bugs'] .  // need to be here because uses join with E table alias

                     " WHERE TPTCV.testplan_id =" . $safe['tplan_id'] .
                     $my['where']['where'];

    return (is_null($union['not_run']) ? $union['exec'] : $union);
  }


  /*
   *
   * @used-by getLinkedForExecTree(),getLinkedForTesterAssignmentTree(), getLinkedTCVersionsSQL()
   *            
   * filters => 'tcase_id','keyword_id','assigned_to','exec_status','build_id', 'cf_hash',
   *            'urgencyImportance', 'tsuites_id','platform_id', 'exec_type','tcase_name'
   *
   *
   *            CRITIC: cf_hash can contains Custom Fields that are applicable to DESIGN and
   *                    TESTPLAN_DESIGN.
   *                    Here we are generating SQL that will be used ON TESTPLAN related tables
   *                    NOT ON TEST SPEC related tables.
   *                    Due to this we are going to consider while building the query ONLY
   *                    CF for TESTPLAN DESING
   *
   * @internal revisions
   */
  function initGetLinkedForTree($tplanID,$filtersCfg,$optionsCfg)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $dummy = array('exec_type','tc_id','builds','keywords','executions','platforms');

    $ic['fields']['tsuites'] = '';

    $ic['join'] = array();
    $ic['join']['ua'] = '';
    $ic['join']['bugs'] = '';
    $ic['join']['cf'] = '';
    $ic['join']['tsuites'] = '';


    $ic['where'] = array();
    $ic['where']['where'] = '';
    $ic['where']['platforms'] = '';
    $ic['where']['not_run'] = '';
    $ic['where']['cf'] = '';

    $ic['green_light'] = true;
    $ic['filters'] = array('tcase_id' => null, 'keyword_id' => 0,
                           'assigned_to' => null, 'exec_status' => null,
                           'build_id' => 0, 'cf_hash' => null,
                           'urgencyImportance' => null, 'tsuites_id' => null,
                           'platform_id' => null, 'exec_type' => null,
                           'tcase_name' => null);

    $ic['options'] = array('hideTestCases' => 0, 'include_unassigned' => false, 
                           'allow_empty_build' => 0, 'addTSuiteOrder' => false,
                           'addImportance' => false, 'addPriority' => false);
    $ic['filters'] = array_merge($ic['filters'], (array)$filtersCfg);
    $ic['options'] = array_merge($ic['options'], (array)$optionsCfg);


    $ic['filters']['build_id'] = intval($ic['filters']['build_id']);
    

    // 20150201
    if($ic['options']['addTSuiteOrder'])
    {
      // PREFIX ALWAYS with COMMA
      $ic['fields']['tsuites'] = ', NH_TSUITE.node_order AS tsuite_order ';
      $ic['join']['tsuites'] = " JOIN {$this->tables['nodes_hierarchy']} NH_TSUITE " . 
                               " ON NH_TSUITE.id = NH_TCASE.parent_id ";
    }  

    // This NEVER HAPPENS for Execution Tree, but if we want to reuse
    // this method for Tester Assignment Tree, we need to add this check
    //
    if( !is_null($ic['filters']['platform_id']) && $ic['filters']['platform_id'] > 0)
    {
      $ic['filters']['platform_id'] = intval($ic['filters']['platform_id']);
      $ic['where']['platforms'] = " AND TPTCV.platform_id = {$ic['filters']['platform_id']} ";
    }
    
    
    $ic['where']['where'] .= $ic['where']['platforms'];

    $dk = 'exec_type';
    if( !is_null($ic['filters'][$dk]) )
    {
      $ic['where'][$dk]= " AND TCV.execution_type IN (" . 
                         implode(",",(array)$ic['filters'][$dk]) . " ) ";     
      $ic['where']['where'] .= $ic['where'][$dk];
    }

    $dk = 'tcase_id';
    if (!is_null($ic['filters'][$dk]) )
    {
      if( is_array($ic['filters'][$dk]) )
      {
        $ic['where'][$dk] = " AND NH_TCV.parent_id IN (" . implode(',',$ic['filters'][$dk]) . ")";            
      }
      else if ($ic['filters'][$dk] > 0)
      {
        $ic['where'][$dk] = " AND NH_TCV.parent_id = " . intval($ic['filters'][$dk]);
      }
      else
      {
        // Best Option on this situation will be signal that query will fail =>
        // NO SENSE run the query
        $ic['green_light'] = false;
      }
      $ic['where']['where'] .= $ic['where'][$dk];
    }

    if (!is_null($ic['filters']['tsuites_id']))
    {
      $dummy = (array)$ic['filters']['tsuites_id'];
      $ic['where']['where'] .= " AND NH_TCASE.parent_id IN (" . implode(',',$dummy) . ")";
    }

    if (!is_null($ic['filters']['urgencyImportance']))
    {
      $ic['where']['where'] .= $this->helper_urgency_sql($ic['filters']['urgencyImportance']);
    }


    if( !is_null($ic['filters']['keyword_id']) )
    {    
      
      list($ic['join']['keywords'],$ic['where']['keywords']) = 
        $this->helper_keywords_sql($ic['filters']['keyword_id'],array('output' => 'array'));


      $ic['where']['where'] .= $ic['where']['keywords']; // **** // CHECK THIS CAN BE NON OK
    }

                              
    // If special user id TL_USER_ANYBODY is present in set of user id,
    // we will DO NOT FILTER by user ID
    if( !is_null($ic['filters']['assigned_to']) && 
        !in_array(TL_USER_ANYBODY,(array)$ic['filters']['assigned_to']) )
    {  
      list($ic['join']['ua'],$ic['where']['ua']) = 
        $this->helper_assigned_to_sql($ic['filters']['assigned_to'],$ic['options'],
                        $ic['filters']['build_id']);            

      $ic['where']['where'] .= $ic['where']['ua']; 

      // TICKET 5566: "Assigned to" does not work in "test execution" page
      // $ic['where']['not_run'] .= $ic['where']['ua'];  

    }
    
    
    if( isset($ic['options']['assigned_on_build']) && 
       !is_null($ic['options']['assigned_on_build']) )
    {
      $ic['join']['ua'] = " LEFT OUTER JOIN {$this->tables['user_assignments']} UA " .
                          " ON UA.feature_id = TPTCV.id " . 
                          " AND UA.build_id = " . $ic['options']['assigned_on_build'] . 
                          " AND UA.type = {$this->execTaskCode} ";
    }


    if( !is_null($ic['filters']['tcase_name']) && 
      ($dummy = trim($ic['filters']['tcase_name'])) != ''  )
    {
      $ic['where']['where'] .= " AND NH_TCASE.name LIKE '%{$dummy}%' "; 
    }
                               

    // Custom fields on testplan_design ONLY => AFFECTS run and NOT RUN.
    if( isset($ic['filters']['cf_hash']) && !is_null($ic['filters']['cf_hash']) )
    {    
      $ic['where']['cf'] = ''; 

      list($ic['filters']['cf_hash'],$cf_sql) = $this->helperTestPlanDesignCustomFields($ic['filters']['cf_hash']);
      if(strlen(trim($cf_sql)) > 0)
      {
        $ic['where']['cf'] .= " AND ({$cf_sql}) ";
        $ic['join']['cf'] = " JOIN {$this->tables['cfield_testplan_design_values']} CFTPD " .
                            " ON CFTPD.link_id = TPTCV.id ";
      }  
      $ic['where']['where'] .= $ic['where']['cf'];
    }



    // I've made the choice to create the not_run key, to manage the not_run part
    // of UNION on getLinkedForExecTree().
    //
    // ATTENTION:
    // on other methods getLinkedForTesterAssignmentTree(), getLinkedTCVersionsSQL()
    // Still is used $ic['where']['where'] on BOTH components of UNION
    //     
    // TICKET 5566: "Assigned to" does not work in "test execution" page
    // TICKET 5572: Filter by Platforms - Wrong test case state count in test plan execution
    $ic['where']['not_run'] = $ic['where']['where'];


    // ****************************************************************************
    // CRITIC - CRITIC - CRITIC 
    // Position on code flow is CRITIC
    // CRITIC - CRITIC - CRITIC 
    // ****************************************************************************
    if (!is_null($ic['filters']['exec_status']))
    {
      // $ic['where']['not_run'] = $ic['where']['where'];
      $dummy = (array)$ic['filters']['exec_status'];

      $ic['where']['where'] .= " AND E.status IN ('" . implode("','",$dummy) . "')";

      if( in_array($this->notRunStatusCode,$dummy) )
      {
        $ic['where']['not_run'] .=  ' AND E.status IS NULL ';
      } 
      else
      {
        $ic['where']['not_run'] = $ic['where']['where'];
      } 
    }


    // BUG ID HAS NO EFFECT ON NOT RUN (at least @20140126)
    // bug_id => will be a list to create an IN() clause
    if( isset($ic['filters']['bug_id']) && !is_null($ic['filters']['bug_id']) )
    {    
      list($ic['join']['bugs'],$ic['where']['bugs']) = $this->helper_bugs_sql($ic['filters']['bug_id']);
      $ic['where']['where'] .= $ic['where']['bugs'];
    }

    return $ic;                       
  }                              


  /**
   *
   */
  function helperTestPlanDesignCustomFields($cfSet)
  {
    $type_domain = $this->cfield_mgr->get_available_types();
    $ret = null;
    $cf_type = null;
    foreach($cfSet as $id => $val)
    {
      $xx = $this->cfield_mgr->get_by_id($id);
      if( $xx[$id]['enable_on_testplan_design'] )
      {
        $ret[$id] = $val;
        $cf_type[$id] = $type_domain[$xx[$id]['type']];
      }  
    }  
    

    $cf_sql = ''; 
    if( !is_null($ret) )
    {  
      $countmain = 1;
      foreach( $ret as $cf_id => $cf_value) 
      {
        if ( $countmain != 1 ) 
        {
          $cf_sql .= " AND ";
        }

        if (is_array($cf_value)) 
        {
          $count = 1;
          switch($cf_type[$cf_id])
          {
            case 'multiselection list':
              // 
              if( count($cf_value) > 1)
              {
                $combo = implode('|',$cf_value);
                $cf_sql .= "( CFTPD.value = '{$combo}' AND CFTPD.field_id = {$cf_id} )";
              }  
              else
              {
                // close set, open set, is sandwiched, is alone
                //$cf_sql .= "( (CFTPD.value LIKE '%|{$cf_value[0]}' AND CFTPD.field_id = {$cf_id}) OR " .
                //           "  (CFTPD.value LIKE '{$cf_value[0]}|%' AND CFTPD.field_id = {$cf_id}) OR " .
                //           "  (CFTPD.value LIKE '%|{$cf_value[0]}|%' AND CFTPD.field_id = {$cf_id}) OR " .
                //           "  (CFTPD.value = '{$cf_value[0]}' AND CFTPD.field_id = {$cf_id}) )";

                $cf_sql .= "( CFTPD.field_id = {$cf_id} AND " .
                           "  (CFTPD.value LIKE '%|{$cf_value[0]}' OR " .
                           "   CFTPD.value LIKE '{$cf_value[0]}|%' OR " .
                           "   CFTPD.value LIKE '%|{$cf_value[0]}|%' OR " .
                           "   CFTPD.value = '{$cf_value[0]}') )";
              }              
            break; 

            default:
              foreach ($cf_value as $value) 
              {
                if ($count > 1) 
                {
                  $cf_sql .= " AND ";
                }

                // When ARRAY NO LIKE but EQUAL
                // Need to document what type of CF are managed as ARRAY
                $cf_sql .= "( CFTPD.value = '{$value}' AND CFTPD.field_id = {$cf_id} )";
                $count++;
              }
            break;             
          }

        } 
        else 
        {
          $cf_sql .= " ( CFTPD.value LIKE '%{$cf_value}%' AND CFTPD.field_id = {$cf_id} ) ";
        }
        $countmain++;
      }  
    }
     
    return array($ret,$cf_sql);    
  }





  // This method is intended to return minimal data useful to create Test Plan Tree, 
  // for feature:
  // test case tester execution assignment:
  // PLATFORM IS NOT USED TO NAVIGATE => is not present on Settings Section.
  // ONLY BUILD IS PRESENT on settings area
  // 
  // 
  // Status on Latest execution on Build ANY PLATFORM is needed
  // 
  // @param int $id test plan id
  // @param mixed $filters
  // @param mixed $options  
  // 
  // [tcase_id]: default null => get any testcase
  //             numeric      => just get info for this testcase
  // 
  // 
  // [keyword_id]: default 0 => do not filter by keyword id
  //               numeric/array()   => filter by keyword id
  // 
  // 
  // [assigned_to]: default NULL => do not filter by user assign.
  //                array() with user id to be used on filter
  //                IMPORTANT NOTICE: this argument is affected by
  //             [assigned_on_build]                 
  // 
  // [build_id]: default 0 or null => do not filter by build id
  //             numeric        => filter by build id
  // 
  // 
  // [cf_hash]: default null => do not filter by Custom Fields values
  //
  //
  // [urgencyImportance] : filter only Tc's with certain (urgency*importance)-value 
  //
  // [tsuites_id]: default null.
  //               If present only tcversions that are children of this testsuites
  //               will be included
  //              
  // [exec_type] default null -> all types. 
  //       
  function getLinkedForTesterAssignmentTree($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe['tplan_id'] = intval($id);
    
    $my = $this->initGetLinkedForTree($safe['tplan_id'],$filters,$options);
      
    // Need to detail better, origin of build_id.
    // is got from GUI Filters area ?
    if(  ($my['options']['allow_empty_build'] == 0) && $my['filters']['build_id'] <= 0 )
    {
      // CRASH IMMEDIATELY
      throw new Exception( $debugMsg . " Can NOT WORK with \$my['filters']['build_id'] <= 0");
    }
    if( !$my['green_light'] ) 
    {
      // No query has to be run, because we know in advance that we are
      // going to get NO RECORDS
      return null;  
    }

    $buildClause = array('lex' => (' AND EE.build_id = ' . $my['filters']['build_id']), 
               'exec_join' => (" AND E.build_id = " . $my['filters']['build_id']));
    if( $my['options']['allow_empty_build'] && $my['filters']['build_id'] <= 0 )
    {
      $buildClause = array('lex' => '','exec_join' => '');
    }

    //
    // Platforms have NOTHING TO DO HERE
    $sqlLEX = " SELECT EE.tcversion_id,EE.testplan_id,EE.build_id," .
          " MAX(EE.id) AS id " .
          " FROM {$this->tables['executions']} EE " . 
          " WHERE EE.testplan_id = " . $safe['tplan_id'] . 
          $buildClause['lex'] .
          " GROUP BY EE.tcversion_id,EE.testplan_id,EE.build_id ";
    
    // -------------------------------------------------------------------------------------
    // adding tcversion on output can be useful for Filter on Custom Field values,
    // because we are saving values at TCVERSION LEVEL
    //  
    $union['not_run'] = "/* {$debugMsg} sqlUnion - not run */" .
              " SELECT NH_TCASE.id AS tcase_id,TPTCV.tcversion_id,TCV.version," .
              " TCV.tc_external_id AS external_id, " .
              " COALESCE(E.status,'" . $this->notRunStatusCode . "') AS exec_status " .
              
                 " FROM {$this->tables['testplan_tcversions']} TPTCV " .                          
                 " JOIN {$this->tables['tcversions']} TCV ON TCV.id = TPTCV.tcversion_id " .
                 " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
                 " JOIN {$this->tables['nodes_hierarchy']} NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " .
              $my['join']['ua'] .
              $my['join']['keywords'] .
              
              " /* Get REALLY NOT RUN => BOTH LE.id AND E.id ON LEFT OUTER see WHERE  */ " .
              " LEFT OUTER JOIN ({$sqlLEX}) AS LEX " .
              " ON  LEX.testplan_id = TPTCV.testplan_id " .
              " AND LEX.tcversion_id = TPTCV.tcversion_id " .
              " AND LEX.testplan_id = " . $safe['tplan_id'] .
              " LEFT OUTER JOIN {$this->tables['executions']} E " .
              " ON  E.tcversion_id = TPTCV.tcversion_id " .
              " AND E.testplan_id = TPTCV.testplan_id " .
              " AND E.id = LEX.id " .  // 20120903
                $buildClause['exec_join'] .

              " WHERE TPTCV.testplan_id =" . $safe['tplan_id'] .
              $my['where']['where'] .
              " /* Get REALLY NOT RUN => BOTH LE.id AND E.id NULL  */ " .
              " AND E.id IS NULL AND LEX.id IS NULL";


    $union['exec'] = "/* {$debugMsg} sqlUnion - executions */" . 
              " SELECT NH_TCASE.id AS tcase_id,TPTCV.tcversion_id,TCV.version," .
              " TCV.tc_external_id AS external_id, " .
              " COALESCE(E.status,'" . $this->notRunStatusCode . "') AS exec_status " .
              
                 " FROM {$this->tables['testplan_tcversions']} TPTCV " .                          
                 " JOIN {$this->tables['tcversions']} TCV ON TCV.id = TPTCV.tcversion_id " .
                 " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
                 " JOIN {$this->tables['nodes_hierarchy']} NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " .
              $my['join']['ua'] .
              $my['join']['keywords'] .
              
              " JOIN ({$sqlLEX}) AS LEX " .
              " ON  LEX.testplan_id = TPTCV.testplan_id " .
              " AND LEX.tcversion_id = TPTCV.tcversion_id " .
              " AND LEX.testplan_id = " . $safe['tplan_id'] .
              " JOIN {$this->tables['executions']} E " .
              " ON  E.tcversion_id = TPTCV.tcversion_id " .
              " AND E.testplan_id = TPTCV.testplan_id " .
              " AND E.id = LEX.id " .  // 20120903
              $buildClause['exec_join'] .

              " WHERE TPTCV.testplan_id =" . $safe['tplan_id'] .
              $my['where']['where'];

    return $union;
  }


  /**
   *
   *
   */
  function getLinkInfo($id,$tcase_id,$platform_id=null,$opt=null)
  {
    $debugMsg = 'Class: ' . __CLASS__ . ' - Method:' . __FUNCTION__;
    $safe_id = array('tplan_id' => 0, 'platform_id' => 0, 'tcase_id' => 0);
    $safe_id['tplan_id'] = intval($id);
    $safe_id['tcase_id'] = intval($tcase_id);
      
    // check and die?
    $my = array('opt' => array('output' => 'version_info','tproject_id' => null,
                'build4assignment' => null, 'collapse' => false));
    $my['opt'] = array_merge($my['opt'],(array)$opt);
      
    $sql = "/* $debugMsg */ " .
           " SELECT TCV.id AS tcversion_id,TCV.version %%needle%% " .
           " FROM {$this->tables['testplan_tcversions']} TPTCV " .  
           " JOIN {$this->tables['tcversions']} TCV " .
           " ON TCV.id = TPTCV.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
           " ON NHTCV.id = TPTCV.tcversion_id ";
           
    $more_cols = ' ';    
    switch($my['opt']['output'])
    {
      case 'tcase_info':
        if(is_null($my['opt']['tproject_id']))
        {
          $dummy = $this->tree_manager->get_node_hierarchy_info($safe_id['tplan_id']);
          $my['opt']['tproject_id'] = $dummy['parent_id'];
        }
        $pp = $this->tcase_mgr->getPrefix($safe_id['tcase_id'],$my['opt']['tproject_id']);
        $prefix = $pp[0] . $this->tcaseCfg->glue_character;
        $more_cols = ', NHTC.name, NHTC.id AS tc_id, ' .
                     $this->db->db->concat("'{$prefix}'",'TCV.tc_external_id') . ' AS full_external_id ';
                    
        $sql .= " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id ";
      break;

      case 'assignment_info':
        if(is_null($my['opt']['build4assignment']))
        {
          // CRASH IMMEDIATELY
          throw new Exception(__METHOD__ . 
                      ' When your choice is to get assignment_info ' .
                      " you need to provide build id using 'build4assignment'");
        }
        // Go ahead
        $safe_id['build_id'] = intval($my['opt']['build4assignment']);

        $more_cols = ',USERS.login,USERS.first,USERS.last' .
                     ',TPTCV.id AS feature_id,TPTCV.platform_id,PLAT.name AS platform_name' .
                     ',NHTCV.parent_id AS tc_id,UA.user_id,TCV.importance,TPTCV.urgency' .
                     ',(TCV.importance * TPTCV.urgency) AS priority ';
        $sql .= " LEFT OUTER JOIN {$this->tables['user_assignments']} UA " .
                " ON UA.build_id = " . $safe_id['build_id'] .
                " AND UA.feature_id = TPTCV.id ";
                 
        $sql .= " LEFT OUTER JOIN {$this->tables['platforms']} PLAT " .
                " ON PLAT.id = TPTCV.platform_id ";

        $sql .= " LEFT OUTER JOIN {$this->tables['users']} USERS " .
                " ON USERS.id = UA.user_id ";

      break;

      
      case 'version_info':
         $more_cols = ',TPTCV.platform_id';
      default:
      break;
    }
    $sql = str_replace('%%needle%%',$more_cols,$sql) .    
           " WHERE TPTCV.testplan_id = {$safe_id['tplan_id']} " .
           " AND NHTCV.parent_id = {$safe_id['tcase_id']} ";
           
    if( !is_null($platform_id) ) 
    {
      if( ($safe_id['platform_id'] = intval($platform_id)) > 0)
      { 
          $sql .= " AND TPTCV.platform_id = " . $safe_id['platform_id'];
        }  
    }          
    
    $rs = $this->db->get_recordset($sql);  
    if(!is_null($rs))
    {
      $rs = $my['opt']['collapse'] ? $rs[0] : $rs;
    }
    return $rs;
  }



  /**
   * @used-by printDocument.php
   *          testplan.class.exportLinkedItemsToXML()
   *          testplan.class.exportForResultsToXML
   */
  public function getLinkedStaticView($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class: ' . __CLASS__ . ' - Method:' . __FUNCTION__;
    $my = array('filters' => '', 'options' => '');

    $my['filters'] = array('platform_id' => null,'tsuites_id' => null, 
                           'tcaseSet' => null, 'build_id' => null);
    $my['filters'] = array_merge($my['filters'],(array)$filters);

    $my['options'] = array('output' => 'map','order_by' => null, 'detail' => 'full');
    $my['options'] = array_merge($my['options'],(array)$options);

    $safe['tplan'] = intval($id);
    $io = $this->tree_manager->get_node_hierarchy_info($safe['tplan']);
    list($prefix,$garbage) = $this->tcase_mgr->getPrefix(null,$io['parent_id']);
    unset($io);
    $prefix .= $this->tcaseCfg->glue_character;
    $feid = $this->db->db->concat("'{$prefix}'",'TCV.tc_external_id');


    $addWhere = array('platform' => '','tsuite' => '', 'tcases' => '', 'build' => '');
    $platQty = 0;
    if( !is_null($my['filters']['platform_id']) )
    {
      $dummy = (array)$my['filters']['platform_id'];
      array_walk($dummy,'intval');
      $addWhere['platform'] = 'AND TPTCV.platform_id IN (' . implode(',',$dummy) . ')';
      $platQty = count((array)$my['filters']['platform_id']);    
    }

    if( !is_null($my['filters']['tsuites_id']) )
    {
      $dummy = (array)$my['filters']['tsuites_id'];
      array_walk($dummy,'intval');
      $addWhere['tsuite'] = 'AND NH_TCASE.parent_id IN (' . implode(',',$dummy) . ')';
    }
    
    if( !is_null($my['filters']['tcaseSet']) )
    {
      $dummy = (array)$my['filters']['tcaseSet'];
      array_walk($dummy,'intval');
      $addWhere['tsuite'] = 'AND NH_TCASE.id IN (' . implode(',',$dummy) . ')';
    }
 
    $join['build'] = '';
    $addField = '-1 AS assigned_to, ';
    if( !is_null($my['filters']['build_id']) )
    {
      $dummy = intval($my['filters']['build_id']);
      $addWhere['build'] = 'AND UA.build_id =' . $dummy;
    
      $join['build'] = " JOIN {$this->tables['user_assignments']} UA " .
                       " ON UA.feature_id = TPTCV.id ";
    
      $addField = " UA.user_id AS assigned_to,";
    }
    


    switch($my['options']['detail'])
    {
      case '4results':
        $my['options']['output'] = 'array'; // FORCED       
        // have had some issues with query and ADODB on MySQL if only
        // $sql = " SELECT NH_TCV.parent_id AS tc_id, {$feid} AS full_external_id,TCV.tc_external_id ";
        // Need to understand why in future  
        $sql = "/* $debugMsg */ " .
               " SELECT {$addField} NH_TCV.parent_id AS tc_id, TPTCV.platform_id, TPTCV.id AS feature_id, " .
               " TCV.tc_external_id AS external_id, {$feid} AS full_external_id, TPTCV.tcversion_id ";
      break;      

      case 'full':
      default:
        $sql = "/* $debugMsg */ " .
               " SELECT {$addField} NH_TCASE.parent_id AS testsuite_id, NH_TCV.parent_id AS tc_id, " . 
               " NH_TCASE.node_order AS spec_order, NH_TCASE.name," .
               " TPTCV.platform_id, PLAT.name as platform_name, TPTCV.id AS feature_id, " .
               " TPTCV.tcversion_id AS tcversion_id, " .
               " TPTCV.node_order AS execution_order, TPTCV.urgency," .
               " TCV.version AS version, TCV.active, TCV.summary," .
               " TCV.tc_external_id AS external_id, TCV.execution_type,TCV.importance," .  
               " {$feid} AS full_external_id, (TPTCV.urgency * TCV.importance) AS priority ";
      break;      
    }

    $sql .=" FROM {$this->tables['nodes_hierarchy']} NH_TCV " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_TCASE ON NH_TCV.parent_id = NH_TCASE.id " .
           " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NH_TCV.id " .
           " JOIN  {$this->tables['tcversions']} TCV ON  TCV.id = NH_TCV.id " .
           $join['build'] .
           " LEFT OUTER JOIN {$this->tables['platforms']} PLAT ON PLAT.id = TPTCV.platform_id ";

    $sql .= " WHERE TPTCV.testplan_id={$safe['tplan']} " . 
            " {$addWhere['platform']} {$addWhere['tsuite']} {$addWhere['build']}";


    switch($my['options']['output'])
    {
      case 'array':
        $rs = $this->db->get_recordset($sql);
      break;
      
      case 'map':
      if($platQty == 1)
      {
        $rs = $this->db->fetchRowsIntoMap($sql,'tc_id',0,-1,'assigned_to');
      }
      else
      {
        $rs = $this->db->fetchMapRowsIntoMap($sql,'platform_id','tc_id');
      }
      break;
    }
    
    new dBug($rs);

    return $rs;
  }


  // need to recheck, because probably we need to be able 
  // to work without build id provided
  // has to be based on TREE USED on features like:
  // assign test case execution  or set test case urgency
  //
  public function getLTCVNewGeneration($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class: ' . __CLASS__ . ' - Method:' . __FUNCTION__;
    $my = array('filters' => array(),
                'options' => array('allow_empty_build' => 1,'addPriority' => false,
                                   'accessKeyType' => 'tcase+platform',
                                   'addImportance' => false,'addExecInfo' => true,
                                   'assigned_on_build' => null, 
                                   'ua_user_alias' => '', 'includeNotRun' => true,
                                   'ua_force_join' => false, 
                                   'orderBy' => null));
    $amk = array('filters','options');
    foreach($amk as $mk)
    {
      $my[$mk] = array_merge($my[$mk], (array)$$mk);
    }

    if( !is_null($sql2do = $this->getLinkedTCVersionsSQL($id,$my['filters'],$my['options'])) )
    {
      // need to document better
      if( is_array($sql2do) )
      {        
        $sql2run = $sql2do['exec'];
        if($my['options']['includeNotRun'])
        {
          $sql2run .= ' UNION ' . $sql2do['not_run'];
        } 
      }
      else
      {
        $sql2run = $sql2do;
      }    

      // added when trying to fix: 
      // TICKET 5788: test case execution order not working on RIGHT PANE
      // Anyway this did not help
      if( !is_null($my['options']['orderBy']) )
      {  
        $sql2run = " SELECT * FROM ($sql2run) XX ORDER BY " . $my['options']['orderBy'];
      }

      switch($my['options']['accessKeyType'])
      {
        case 'tcase+platform':
          $tplan_tcases = $this->db->fetchMapRowsIntoMap($sql2run,'tcase_id','platform_id'); // ,0,-1,'user_id');
        break;
        
        case 'tcase+platform+stackOnUser':
          $tplan_tcases = $this->db->fetchMapRowsIntoMapStackOnCol($sql2run,'tcase_id','platform_id','user_id');
        break;

        case 'index':
          $tplan_tcases = $this->db->get_recordset($sql2run);
        break;  
        
        default:
          $tplan_tcases = $this->db->fetchRowsIntoMap($sql2run,'tcase_id');
        break;  
      }  
    }
    return $tplan_tcases;
  }




  /**
   * 
   * @used-by testplan::getLTCVNewGeneration()
   * @use     initGetLinkedForTree()
   *
   * @parameter map filters
   *            keys:
   *            'tcase_id','keyword_id','assigned_to','exec_status','build_id', 'cf_hash',
   *            'urgencyImportance', 'tsuites_id','platform_id', 'exec_type','tcase_name'
   *            filters defaults values are setted on initGetLinkedForTree()
   *
   * @parameter map options
   *            some defaults are managed here
   *
   *            defaults for keys: 'hideTestCases','include_unassigned','allow_empty_build'
   *            are setted on initGetLinkedForTree().
   * 
   *
   *
   * @internal revisions
   * @since 1.9.13
   */
  function getLinkedTCVersionsSQL($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe['tplan_id'] = intval($id);
    $my = $this->initGetLinkedForTree($safe['tplan_id'],$filters,$options);
    

    $mop = array('options' => array('addExecInfo' => false,'specViewFields' => false, 
                                    'assigned_on_build' => null, 'testSuiteInfo' => false,
                                    'addPriority' => false,'addImportance' => false,
                                    'ignorePlatformAndBuild' => false,
                                    'ignoreBuild' => false, 'ignorePlatform' => false,
                                    'ua_user_alias' => '', 
                                    'ua_force_join' => false));

    $my['options'] = array_merge($mop['options'],$my['options']);
      
    if(  ($my['options']['allow_empty_build'] == 0) && $my['filters']['build_id'] <= 0 )
    {
      // CRASH IMMEDIATELY
      throw new Exception( $debugMsg . " Can NOT WORK with \$my['filters']['build_id'] <= 0");
    }
    if( !$my['green_light'] ) 
    {
      // No query has to be run, because we know in advance that we are
      // going to get NO RECORDS
      return null;  
    }

    $buildClause = array('lex' => (' AND EE.build_id = ' . $my['filters']['build_id']), 
                         'exec_join' => (" AND E.build_id = " . $my['filters']['build_id']));
    if( $my['options']['allow_empty_build'] && $my['filters']['build_id'] <= 0 )
    {
      $buildClause = array('lex' => '','exec_join' => '');
    }

    // TICKET 5182: Add/Remove Test Cases -> Trying to assign new platform to executed test cases
    // Before this ticket LEX was just on BUILD => ignoring platforms
    // Need to understand if will create side effects.
    //
    if($my['options']['ignorePlatformAndBuild'])
    {
      $sqlLEX = " SELECT EE.tcversion_id,EE.testplan_id," .
                " MAX(EE.id) AS id " .
                " FROM {$this->tables['executions']} EE " . 
                " WHERE EE.testplan_id = " . $safe['tplan_id'] . 
                " GROUP BY EE.tcversion_id,EE.testplan_id ";

      $platformLEX = " ";
      $platformEXEC = " ";

    }  
    else if ($my['options']['ignoreBuild']) 
    {
      $sqlLEX = " SELECT EE.tcversion_id,EE.testplan_id,EE.platform_id," .
                " MAX(EE.id) AS id " .
                " FROM {$this->tables['executions']} EE " . 
                " WHERE EE.testplan_id = " . $safe['tplan_id'] . 
                " GROUP BY EE.tcversion_id,EE.testplan_id,EE.platform_id";

      // TICKET 5182           
      $platformLEX = " AND LEX.platform_id = TPTCV.platform_id "; 
      $platformEXEC = " AND E.platform_id = TPTCV.platform_id ";

    }
    else
    {
      $sqlLEX = " SELECT EE.tcversion_id,EE.testplan_id,EE.platform_id,EE.build_id," .
                " MAX(EE.id) AS id " .
                " FROM {$this->tables['executions']} EE " . 
                " WHERE EE.testplan_id = " . $safe['tplan_id'] . 
                $buildClause['lex'] .
                " GROUP BY EE.tcversion_id,EE.testplan_id,EE.platform_id,EE.build_id ";

      // TICKET 5182           
      $platformLEX = " AND LEX.platform_id = TPTCV.platform_id "; 
      $platformEXEC = " AND E.platform_id = TPTCV.platform_id ";

    }  
    
    // -------------------------------------------------------------------------------------
    // adding tcversion on output can be useful for Filter on Custom Field values,
    // because we are saving values at TCVERSION LEVEL
    //  
    
    // TICKET 5165: Issues with DISTINCT CLAUSE on TEXT field
    // Do not know if other usages are going to cry due to missing fields
    //
    // $commonFields = " SELECT NH_TCASE.id AS tcase_id,NH_TCASE.id AS tc_id,TPTCV.tcversion_id,TCV.version," .
    //         " TCV.tc_external_id AS external_id, TCV.execution_type," .
    //         " TCV.summary, TCV.preconditions,TPTCV.id AS feature_id," .
    //         " TPTCV.platform_id,PLAT.name AS platform_name,TPTCV.node_order AS execution_order,".
    //         " COALESCE(E.status,'" . $this->notRunStatusCode . "') AS exec_status ";
    // 
    // $fullEID = $this->helperConcatTCasePrefix($safe['tplan_id']);
    $commonFields = " SELECT NH_TCASE.name AS tcase_name, NH_TCASE.id AS tcase_id, " .
                    " NH_TCASE.id AS tc_id,TPTCV.tcversion_id,TCV.version," .
                    " TCV.tc_external_id AS external_id, TCV.execution_type,TCV.status," .
                    " TPTCV.id AS feature_id," .
                    ($my['options']['addPriority'] ? "(TPTCV.urgency * TCV.importance) AS priority," : '') .
                    " TPTCV.platform_id,PLAT.name AS platform_name,TPTCV.node_order AS execution_order,".
                    " COALESCE(E.status,'" . $this->notRunStatusCode . "') AS exec_status, " .
                    " E.execution_duration, " .
                    ($my['options']['addImportance'] ? " TCV.importance," : '') .
                    $this->helperConcatTCasePrefix($safe['tplan_id']) . "  AS full_external_id ";

    // used on tester assignment feature when working at test suite level
    if( !is_null($my['options']['assigned_on_build']) )
    {
      $commonFields .= ",UA.user_id {$my['options']['ua_user_alias']} ";
    }
    
    if($my['options']['addExecInfo'])
    {
      $commonFields .= ",COALESCE(E.id,0) AS exec_id,E.tcversion_number,E.build_id AS exec_on_build,E.testplan_id AS exec_on_tplan";
    }
    
    if($my['options']['specViewFields'])
    {
      $commonFields .= ",NH_TCASE.name,TPTCV.creation_ts AS linked_ts,TPTCV.author_id AS linked_by" .
                       ",NH_TCASE.parent_id AS testsuite_id";   
    }
    
    $my['join']['tsuites'] = '';
    if($my['options']['testSuiteInfo'])
    {
      $commonFields .= ",NH_TSUITE.name AS tsuite_name ";
      $my['join']['tsuites'] = " JOIN {$this->tables['nodes_hierarchy']} NH_TSUITE " . 
                               " ON NH_TSUITE.id = NH_TCASE.parent_id ";
    }
    
    if($my['options']['ua_force_join'])
    {
      $my['join']['ua'] = str_replace('LEFT OUTER',' ', $my['join']['ua']);
    }  
    new dBug($my['join']['ua']);

    $union['not_run'] = "/* {$debugMsg} sqlUnion - not run */" . $commonFields .
                         " FROM {$this->tables['testplan_tcversions']} TPTCV " .                          
                         " JOIN {$this->tables['tcversions']} TCV ON TCV.id = TPTCV.tcversion_id " .
                         " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
                         " JOIN {$this->tables['nodes_hierarchy']} NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " .
                         $my['join']['tsuites'] .
                         $my['join']['ua'] .
                         $my['join']['keywords'] .
              
                         " LEFT OUTER JOIN {$this->tables['platforms']} PLAT ON PLAT.id = TPTCV.platform_id " .
                         " /* Get REALLY NOT RUN => BOTH LE.id AND E.id ON LEFT OUTER see WHERE  */ " .
                         " LEFT OUTER JOIN ({$sqlLEX}) AS LEX " .
                         " ON  LEX.testplan_id = TPTCV.testplan_id " .
                         $platformLEX .
                         " AND LEX.tcversion_id = TPTCV.tcversion_id " .
                         " AND LEX.testplan_id = " . $safe['tplan_id'] .
                         " LEFT OUTER JOIN {$this->tables['executions']} E " .
                         " ON  E.tcversion_id = TPTCV.tcversion_id " .
                         " AND E.testplan_id = TPTCV.testplan_id " .
                         $platformEXEC .
                         " AND E.id = LEX.id " .  // TICKET 6159
                         $buildClause['exec_join'] .

                         " WHERE TPTCV.testplan_id =" . $safe['tplan_id'] . ' ' .
                         $my['where']['where'] .
                         " /* Get REALLY NOT RUN => BOTH LE.id AND E.id NULL  */ " .
                         " AND E.id IS NULL AND LEX.id IS NULL";
          

    $union['exec'] = "/* {$debugMsg} sqlUnion - executions */" . $commonFields . 
                     " FROM {$this->tables['testplan_tcversions']} TPTCV " .                          
                     " JOIN {$this->tables['tcversions']} TCV ON TCV.id = TPTCV.tcversion_id " .
                     " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
                     " JOIN {$this->tables['nodes_hierarchy']} NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " .
                     " LEFT OUTER JOIN {$this->tables['platforms']} PLAT ON PLAT.id = TPTCV.platform_id " .
                     $my['join']['tsuites'] .
                     $my['join']['ua'] .
                     $my['join']['keywords'] .
             
                     " JOIN ({$sqlLEX}) AS LEX " .
                     " ON  LEX.testplan_id = TPTCV.testplan_id " .
                     $platformLEX .
                     " AND LEX.tcversion_id = TPTCV.tcversion_id " .
                     " AND LEX.testplan_id = " . $safe['tplan_id'] .

                     " JOIN {$this->tables['executions']} E " .
                     " ON  E.tcversion_id = TPTCV.tcversion_id " .
                     " AND E.testplan_id = TPTCV.testplan_id " .
                     $platformEXEC .
                     " AND E.id = LEX.id " .  // TICKET 6159
                     $buildClause['exec_join'] .
                         
                     " WHERE TPTCV.testplan_id =" . $safe['tplan_id'] . ' ' .
                     $my['where']['where'];

    new dBug($union);
    return $union;
  }


  /**
   *
   *
   */
  function getPublicAttr($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " .
           " SELECT is_public FROM {$this->tables['testplans']} " .
           " WHERE id =" . intval($id);   
    $ret = $this->db->get_recordset($sql);
    return $ret[0]['is_public'];
  }



  /**
   *
   *
   */
  function getBuildByCriteria($id, $criteria, $filters=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['opt'] = array('active' => null, 'open' => null);
    $my['opt'] = array_merge($my['opt'],(array)$options);


    switch($criteria)
    {
      case 'maxID':
        $sql = " /* $debugMsg */ " . 
               " SELECT MAX(id) AS id,testplan_id, name, notes, active, is_open," .
               " release_date,closed_on_date " .
               " FROM {$this->tables['builds']} WHERE testplan_id = {$id} " ;
      break;
    }

    if(!is_null($my['opt']['active']))
    {
      $sql .= " AND active = " . intval($my['opt']['active']) . " ";
    }
    if( !is_null($my['opt']['open']) )
    {
      $sql .= " AND is_open = " . intval($my['opt']['open']) . " ";
    }
    
    $rs = $this->db->get_recordset($sql);
    
    return $rs;
  }


  /**
   *
   *
   */
  function writeExecution($ex)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $execNotes = $this->db->prepare_string($ex->notes);
    if(property_exists($ex, 'executionTimeStampISO'))
    {
      $execTS = "'" . $ex->executionTimeStampISO . "'";
    } 
    else
    {
      $execTS = $this->db->db_now();
    }  

    $sql = "/* {$debugMsg} */ " .
           "INSERT INTO {$this->tables['executions']} " .
           " (testplan_id, platform_id, build_id, " .
           "  tcversion_id, tcversion_number, status, " .
           "  tester_id, execution_ts, execution_type, notes) " .
           " VALUES(" .
           "  {$ex->testPlanID},{$ex->platformID},{$ex->buildID}," .
           "  {$ex->testCaseVersionID}, {$ex->testCaseVersionNumber},'{$ex->statusCode}'," .
           "  {$ex->testerID},{$execTS}, {$ex->executionType}, '{$execNotes}')";

    $this->db->exec_query($sql);
    return $this->db->insert_id($this->tables['executions']);    
  }

  /**
   *
   */
  function getExecutionDurationForSet($execIDSet)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " .
           "SELECT E.id, E.execution_duration AS duration ".
           "FROM {$this->tables['executions']} E " .
           "WHERE id IN (" . implode(',',$execIDSet) . ')';
    return $this->db->get_recordset($sql);       
  }

  /**
   *
   */
  function exportForResultsToXML($id,$context,$optExport = array(),$filters=null)
  {
    $my['filters'] = array('platform_id' => null, 'tcaseSet' => null);
    $my['filters'] = array_merge($my['filters'], (array)$filters);


    $item = $this->get_by_id($id,array('output' => 'minimun','caller' => __METHOD__));

    $xmlString = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
                 "<!-- TestLink - www.testlink.org - xml to allow results import -->\n";
    $xmlString .= "<results>\n";
    $xmlString .= "\t<testproject name=\"" . htmlspecialchars($item['tproject_name']) . '"' . 
                  " prefix=\"" . htmlspecialchars($item['prefix']) . '"' . " />\n";

    $xmlString .= "\t<testplan name=\"" . htmlspecialchars($item['name']) . '"' . " />\n";

    if( isset($context['build_id']) &&  $context['build_id'] > 0)
    {
      $dummy = $this->get_builds($id);
      $info = $dummy[$context['build_id']];
      $xmlString .= "\t<build name=\"" . htmlspecialchars($info['name']) . "\" />\n";
    }

    // get target platform (if exists)
    if( $context['platform_id'] > 0)
    {
      $info = $this->platform_mgr->getByID($context['platform_id']);
      $xmlString .= "\t<platform name=\"" . htmlspecialchars($info['name']) . "\" />\n";
      $my['filters']['platform_id'] = $context['platform_id'];
    } 

    // <testcase external_id="BB-1" >
    // <!-- if not present logged user  will be used -->
    // <!-- tester LOGIN Name -->
    // <tester>u0113</tester>  
    // <!-- if not present now() will be used -->
    // <timestamp>2008-09-08 14:00:00</timestamp>  
    // <result>p</result>
    // <notes>functionality works great </notes>
    // </testcase>
    $mm = $this->getLinkedStaticView($id,$my['filters'],array('output' => 'array','detail' => '4results'));
    

    if(!is_null($mm) && ($tcaseQty=count($mm)) > 0)
    {

      // Custom fields processing
      $xcf = $this->cfield_mgr->get_linked_cfields_at_execution($item['tproject_id'],1,'testcase');
      if(!is_null($xcf) && ($cfQty=count($xcf)) > 0)
      {
        for($gdx=0; $gdx < $tcaseQty; $gdx++)
        {
          $mm[$gdx]['xmlcustomfields'] = $this->cfield_mgr->exportValueAsXML($xcf);
        }    
      }  

      // Test Case Steps
      $gso = array('fields2get' => 'TCSTEPS.id,TCSTEPS.step_number', 'renderGhostSteps' => false, 'renderImageInline' => false);
      $stepRootElem = "<steps>{{XMLCODE}}</steps>";
      $stepTemplate = "\n" . '<step>' . "\n" .
                      "\t<step_number>||STEP_NUMBER||</step_number>\n" .
                      "\t<result>p</result>\n" .
                      "\t<notes>||NOTES||</notes>\n" .
                      "</step>\n";
      $stepInfo = array("||STEP_NUMBER||" => "step_number", "||NOTES||" => "notes"); 
   
      for($gdx=0; $gdx < $tcaseQty; $gdx++)
      {
        $mm[$gdx]['steps'] = $this->tcase_mgr->getStepsSimple($mm[$gdx]['tcversion_id'],0,$gso);
        if(!is_null($mm[$gdx]['steps']))
        {
          $qs = count($mm[$gdx]['steps']);
          for($scx=0; $scx < $qs; $scx++)
          {
            $mm[$gdx]['steps'][$scx]['notes'] = 'your step exec notes';
          }  
          $mm[$gdx]['xmlsteps'] = exportDataToXML($mm[$gdx]['steps'],$stepRootElem,$stepTemplate,$stepInfo,true);
        }  
      }
    }  

    
    $xml_root = null;
    $xml_template = "\n" . 
                    "\t<testcase external_id=\"{{FULLEXTERNALID}}\">" . "\n" . 
                    "\t\t" . "<result>X</result>" . "\n" .
                    "\t\t" . "<notes>test link rocks </notes>" . "\n" .
                    "\t\t" . "<tester>put login here</tester>" . "\n" .
                    "\t\t" . "<!-- if not present now() will be used -->" . "\n" .
                    "\t\t" . "<timestamp>YYYY-MM-DD HH:MM:SS</timestamp>" . "\n" .  
                    "\t\t" . "<bug_id>put your bug id here</bug_id>" . "\n" .  
                    "\t\t" . "||STEPS||" . "\n" .  
                    "\t\t" . "||CUSTOMFIELDS||" . "\n" .  
                    "\t</testcase>" . "\n";

    $xml_mapping = null;
    $xml_mapping = array("{{FULLEXTERNALID}}" => "full_external_id", "||CUSTOMFIELDS||" => "xmlcustomfields",
                         "||STEPS||" => "xmlsteps");

    $linked_testcases = exportDataToXML($mm,$xml_root,$xml_template,$xml_mapping,('noXMLHeader'=='noXMLHeader'));
    $zorba = $xmlString .= $linked_testcases . "\n</results>\n";

    return $zorba;
  }


  /**
   *
   */
  function setActive($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " . "UPDATE {$this->tables['testplans']} SET active=1 WHERE id=" . intval($id);
    $this->db->exec_query($sql); 
  }

  /**
   *
   */
  function setInactive($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " . "UPDATE {$this->tables['testplans']} SET active=0 WHERE id=" . intval($id); 
    $this->db->exec_query($sql); 
  }



  /**
   *
   */
  function getByAPIKey($apiKey,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['opt'] = array('checkIsValid' => false);
    $my['opt'] = array_merge($my['opt'],(array)$opt);
    $fields2get = $my['opt']['checkIsValid'] ? 'id' : '*';
    
    $safe = $this->db->prepare_string($apiKey);

    $sql = "/* $debugMsg */ " .
           " SELECT {$fields2get} FROM {$this->tables['testplans']} " .
           " WHERE api_key = '{$safe}'";
 
    $rs = $this->db->get_recordset($sql);
    return ($rs ? $rs[0] : null);
  }



  /**
   *
   * @used-by planEdit.php
   */
  function getFileUploadRelativeURL($id)
  {
    // do_action,tplan_id as expected in planEdit.php
    $url = "lib/plan/planEdit.php?do_action=fileUpload&tplan_id=" . intval($id);
    return $url;
  }

  /**
   * @used-by planEdit.php
   */
  function getDeleteAttachmentRelativeURL($id)
  {
    // do_action,tplan_id as expected in planEdit.php
    $url = "lib/plan/planEdit.php?do_action=deleteFile&tplan_id=" . intval($id) . "&file_id=" ; 
    return $url;
  }


  /**
   * @used-by
   */
  function getAllExecutionsWithBugs($id,$platform_id=null,$build_id=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $safe['tplan_id'] = intval($id);
    $fullEID = $this->helperConcatTCasePrefix($safe['tplan_id']);
    
    $sql = " /* $debugMsg */ ". 
           " SELECT DISTINCT E.id AS exec_id,EB.bug_id,NHTC.id AS tcase_id, NHTC.id AS tc_id, " .
           " NHTC.name AS name, NHTSUITE.name AS tsuite_name, TCV.tc_external_id AS external_id," .
           " $fullEID  AS full_external_id " .
           " FROM {$this->tables['executions']} E " .
           " JOIN {$this->tables['testplan_tcversions']} TPTCV " .
           " ON TPTCV.tcversion_id = E.tcversion_id " .
           " AND TPTCV.testplan_id = E.testplan_id " .
           " JOIN {$this->tables['execution_bugs']} EB " . 
           " ON EB.execution_id = E.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV " . 
           " ON NHTCV.id = E.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC " . 
           " ON NHTC.id = NHTCV.parent_id " .
           " JOIN {$this->tables['tcversions']} TCV " . 
           " ON TCV.id = E.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTSUITE " . 
           " ON NHTSUITE.id = NHTC.parent_id " .
           " WHERE TPTCV.testplan_id = " . $safe['tplan_id'];
         
    $items = $this->db->get_recordset($sql);           
    return $items;
  }


  /**
   *
   */
  public function getLTCVOnTestPlan($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class: ' . __CLASS__ . ' - Method:' . __FUNCTION__;
    $my = array('filters' => array(),
                'options' => array('allow_empty_build' => 1,'addPriority' => false,
                                   'accessKeyType' => 'tcase+platform',
                                   'addImportance' => false,
                                   'includeNotRun' => true, 'orderBy' => null));
    $amk = array('filters','options');
    foreach($amk as $mk)
    {
      $my[$mk] = array_merge($my[$mk], (array)$$mk);
    }
        
    $my['options']['ignorePlatformAndBuild'] = true;    
    if( !is_null($sql2do = $this->getLinkedTCVersionsSQL($id,$my['filters'],$my['options'])) )
    {
      // need to document better
      if( is_array($sql2do) )
      {        
        $sql2run = $sql2do['exec'];
        if($my['options']['includeNotRun'])
        {
          $sql2run .= ' UNION ' . $sql2do['not_run'];
        } 
      }
      else
      {
        $sql2run = $sql2do;
      }

      // added when trying to fix: 
      // TICKET 5788: test case execution order not working on RIGHT PANE
      // Anyway this did not help
      if( !is_null($my['options']['orderBy']) )
      {  
        $sql2run = " SELECT * FROM ($sql2run) XX ORDER BY " . $my['options']['orderBy'];
      }

      switch($my['options']['accessKeyType'])
      {
        case 'tcase+platform':
          $tplan_tcases = $this->db->fetchMapRowsIntoMap($sql2run,'tcase_id','platform_id'); // ,0,-1,'user_id');
        break;
        
        case 'tcase+platform+stackOnUser':
          $tplan_tcases = $this->db->fetchMapRowsIntoMapStackOnCol($sql2run,'tcase_id','platform_id','user_id');
        break;

        case 'index':
          $tplan_tcases = $this->db->get_recordset($sql2run);
        break;  
        
        default:
          $tplan_tcases = $this->db->fetchRowsIntoMap($sql2run,'tcase_id');
        break;  
      }  
    }
    return $tplan_tcases;
  }


  /**
   *
   */
  public function getLTCVOnTestPlanPlatform($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class: ' . __CLASS__ . ' - Method:' . __FUNCTION__;
    $my = array('filters' => array(),
                'options' => array('allow_empty_build' => 1,'addPriority' => false,
                                   'accessKeyType' => 'tcase+platform',
                                   'addImportance' => false,
                                   'includeNotRun' => true, 'orderBy' => null));
    $amk = array('filters','options');
    foreach($amk as $mk)
    {
      $my[$mk] = array_merge($my[$mk], (array)$$mk);
    }
        
    $my['options']['ignoreBuild'] = true;    
    if( !is_null($sql2do = $this->getLinkedTCVersionsSQL($id,$my['filters'],$my['options'])) )
    {
      // need to document better
      if( is_array($sql2do) )
      {        
        $sql2run = $sql2do['exec'];
        if($my['options']['includeNotRun'])
        {
          $sql2run .= ' UNION ' . $sql2do['not_run'];
        } 
      }
      else
      {
        $sql2run = $sql2do;
      }

      // added when trying to fix: 
      // TICKET 5788: test case execution order not working on RIGHT PANE
      // Anyway this did not help
      if( !is_null($my['options']['orderBy']) )
      {  
        $sql2run = " SELECT * FROM ($sql2run) XX ORDER BY " . $my['options']['orderBy'];
      }

      switch($my['options']['accessKeyType'])
      {
        case 'tcase+platform':
          $tplan_tcases = $this->db->fetchMapRowsIntoMap($sql2run,'tcase_id','platform_id'); // ,0,-1,'user_id');
        break;
        
        case 'tcase+platform+stackOnUser':
          $tplan_tcases = $this->db->fetchMapRowsIntoMapStackOnCol($sql2run,'tcase_id','platform_id','user_id');
        break;

        case 'index':
          $tplan_tcases = $this->db->get_recordset($sql2run);
        break;  
        
        default:
          $tplan_tcases = $this->db->fetchRowsIntoMap($sql2run,'tcase_id');
        break;  
      }  
    }
    return $tplan_tcases;
  }


  
  /**
   *
   */
  function getLinkedItems($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = " /* $debugMsg */ ". 
         " SELECT parent_id AS tcase_id,TPTCV.platform_id,TPTCV.node_order " .
         " FROM {$this->tables['nodes_hierarchy']} NHTC " .
         " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NHTC.id " .
         " WHERE TPTCV.testplan_id = " . intval($id);
         
    $items = $this->db->fetchMapRowsIntoMap($sql,'tcase_id','platform_id');
           
    return $items;
  }



  /**
   *
   * @since 1.9.14
   */
  function getLinkedFeatures($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my = array('filters' => array(), $options => array());    
    $my['filters'] = array('platform_id' => null);
    $my['options'] = array('accessKey' => array('tcase_id','platform_id'));

    $my['filters'] = array_merge($my['filters'],(array)$filters);
    $my['options'] = array_merge($my['options'],(array)$options);

    $sql = " /* $debugMsg */ ". 
         " SELECT parent_id AS tcase_id,TPTCV.platform_id,TPTCV.id AS feature_id " .
         " FROM {$this->tables['nodes_hierarchy']} NHTC " .
         " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NHTC.id " .
         " WHERE TPTCV.testplan_id = " . intval($id);

    if(!is_null($my['filters']['platform_id']))
    {
      $sql .= " AND TPTCV.platform_id = " . intval($my['filters']['platform_id']);
    }  

    if(!is_null($my['filters']['tcase_id']))
    {
      $sql .= " AND NHTC.parent_id IN (" . implode(',',$my['filters']['tcase_id']) . ") ";
    }    

    $items = $this->db->fetchMapRowsIntoMap($sql,$my['options']['accessKey'][0],
                                                 $my['options']['accessKey'][1]);
           
    return $items;
  }

  /**
   * @used-by getFilteredLinkedVersions() - specview.php
   * @used-by indirectly on tc_exec_assigment.php for test suites         
   *
   */
  function getLinkedTCVXmen($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe['tplan_id'] = intval($id);
    $my = $this->initGetLinkedForTree($safe['tplan_id'],$filters,$options);

    // adding tcversion on output can be useful for Filter on Custom Field values,
    // because we are saving values at TCVERSION LEVEL
    $commonFields = "/* $debugMsg */ " .
                    " SELECT NH_TCASE.name AS tcase_name, NH_TCASE.id AS tcase_id, " .
                    " NH_TCASE.id AS tc_id,TPTCV.tcversion_id,TCV.version," .
                    " TCV.tc_external_id AS external_id, TCV.execution_type,TCV.status," .
                    " TPTCV.id AS feature_id," .
                    ($my['options']['addPriority'] ? "(TPTCV.urgency * TCV.importance) AS priority," : '') .
                    " TPTCV.platform_id,TPTCV.node_order AS execution_order,".
                    ($my['options']['addImportance'] ? " TCV.importance," : '') .
                    $this->helperConcatTCasePrefix($safe['tplan_id']) . "  AS full_external_id ";

    $commonFields .= ",UA.user_id";      
    $commonFields .= ",NH_TCASE.name,TPTCV.creation_ts AS linked_ts,TPTCV.author_id AS linked_by" .
                     ",NH_TCASE.parent_id AS testsuite_id";   
    
    $commonFields .= ",NH_TSUITE.name AS tsuite_name ";  

    $my['join']['tsuites'] = " JOIN {$this->tables['nodes_hierarchy']} NH_TSUITE " . 
                             " ON NH_TSUITE.id = NH_TCASE.parent_id ";
    
    
    
    $sql =  $commonFields .
            " FROM {$this->tables['testplan_tcversions']} TPTCV " .                          
            " JOIN {$this->tables['tcversions']} TCV ON TCV.id = TPTCV.tcversion_id " .
            " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
            " JOIN {$this->tables['nodes_hierarchy']} NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " .
            $my['join']['tsuites'] .
            $my['join']['ua'] .
            $my['join']['keywords'] .
            " WHERE TPTCV.testplan_id =" . $safe['tplan_id'] . 
            $my['where']['where'];

    $items = $this->db->fetchMapRowsIntoMapStackOnCol($sql,'tcase_id','platform_id','user_id');
    return $items;
  }

  /**
   *
   */
  function getExecCountOnBuild($id,$build_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe['tplan_id'] = intval($id);
    $safe['build_id'] = intval($build_id);
     
    $sql = "/* debugMsg */ SELECT COUNT(0) AS qty " . 
           " FROM {$this->tables['executions']} E " .
           " WHERE E.testplan_id = {$safe['tplan_id']} " .
           " AND E.build_id = {$safe['build_id']}"; 

    $rs = $this->db->get_recordset($sql);

    return $rs[0]['qty'];
  }

  /**
   *
   */
  function getFeatureByID($feature_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $target = (array)$feature_id;
    foreach($target as $idx => $tg)
    {
      $target[$idx] = intval($tg);
    }  
    $inSet = implode(',', $target);

    $sql = " /* $debugMsg */ ". 
           " SELECT parent_id AS tcase_id,tcversion_id,platform_id,TPTCV.id " .
           " FROM {$this->tables['nodes_hierarchy']} NHTC " .
           " JOIN {$this->tables['testplan_tcversions']} TPTCV " .
           " ON TPTCV.tcversion_id = NHTC.id " .
           " WHERE TPTCV.id IN (" . $inSet . ")";
         
    $items = $this->db->fetchRowsIntoMap($sql,'id');           
    return $items;
  }


} // end class testplan


// ######################################################################################
/** 
 * Build Manager Class 
 * @package TestLink
 **/
class build_mgr extends tlObject
{
  /** @var database handler */
  var $db;
  var $cfield_mgr;


  /** 
   * class constructor 
   * 
   * @param resource &$db reference to database handler
   **/
  function build_mgr(&$db)
  {
    parent::__construct();
    $this->db = &$db;
    $this->cfield_mgr = new cfield_mgr($this->db);
  }


  /**
   *
   */
  function setZeroOneAttr($id,$attr,$zeroOne)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = "/* $debugMsg */ " . 
           "UPDATE {$this->tables['builds']} SET {$attr}=" . ($zeroOne ? 1 : 0) . " WHERE id=" . intval($id);
    $this->db->exec_query($sql); 
  }


  /**
   *
   */
  function setActive($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $this->setZeroOneAttr($id,'active',1);
  }

  /**
   *
   */
  function setInactive($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $this->setZeroOneAttr($id,'active',0);
  }

  /**
   *
   */
  function setOpen($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $this->setZeroOneAttr($id,'is_open',1);
    $this->setClosedOnDate($id,null);
  }

  /**
   *
   */
  function setClosed($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $this->setZeroOneAttr($id,'is_open',0);
    $timestamp = explode(' ',trim($this->db->db_now(),"'"));
    $this->setClosedOnDate($id,$timestamp[0]);
  }



  /*
    function: create

    args :
          $tplan_id
          $name
          $notes
          [$active]: default: 1
          [$open]: default: 1
          [release_date]: YYYY-MM-DD


    returns:

    rev :
  */
  function create($tplan_id,$name,$notes = '',$active=1,$open=1,$release_date='')
  {
    $targetDate = trim($release_date);
    $sql = " INSERT INTO {$this->tables['builds']} " .
           " (testplan_id,name,notes,release_date,active,is_open,creation_ts) " .
           " VALUES ('". $tplan_id . "','" . $this->db->prepare_string($name) . "','" .
           $this->db->prepare_string($notes) . "',";

    if($targetDate == '')
    {
      $sql .= "NULL,";
    }       
    else
    {
      $sql .= "'" . $this->db->prepare_string($targetDate) . "',";
    }
    
    $sql .= "{$active},{$open},{$this->db->db_now()})";                        

    $id = 0;
    $result = $this->db->exec_query($sql);
    if ($result)
    {
      $id = $this->db->insert_id($this->tables['builds']);
    }
    
    return $id;
  }


  /*
    function: update

    args :
          $id
          $name
          $notes
          [$active]: default: null
          [$open]: default: null
          [$release_date]=''    FORMAT YYYY-MM-DD
          [$closed_on_date]=''  FORMAT YYYY-MM-DD

    returns:

    rev :
  */
  function update($id,$name,$notes,$active=null,$open=null,$release_date='',$closed_on_date='')
  {
    $closure_date = '';
    $targetDate=trim($release_date);
    $sql = " UPDATE {$this->tables['builds']} " .
           " SET name='" . $this->db->prepare_string($name) . "'," .
           "     notes='" . $this->db->prepare_string($notes) . "'";
    
    if($targetDate == '')
    {
      $sql .= ",release_date=NULL";
    }       
    else
    {
      $sql .= ",release_date='" . $this->db->prepare_string($targetDate) . "'";
    }
    if( !is_null($active) )
    {
      $sql .=" , active=" . intval($active);
    }
    
    if( !is_null($open) )
    {
      $open_status=intval($open) ? 1 : 0; 
      $sql .=" , is_open=" . $open_status;
      
      if($open_status == 1)
      {
        $closure_date = ''; 
      }
    }
    
    if($closure_date == '')
    {
      $sql .= ",closed_on_date=NULL";
    }       
    else
    {
      // may be will be useful validate date format
      $sql .= ",closed_on_date='" . $this->db->prepare_string($closure_date) . "'";
    }
    
    $sql .= " WHERE id={$id}";
    $result = $this->db->exec_query($sql);
    return $result ? 1 : 0;
  }

  /**
   * Delete a build
   * 
   * @param integer $id
   * @return integer status code
   * 
   * @internal revisions:
   * @since 1.9.9
   * 
   */
  function delete($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $safe_id = intval($id);
    $where = " WHERE build_id={$safe_id}";

    $sql = " DELETE FROM {$this->tables['execution_bugs']} " .
           " WHERE execution_id IN (SELECT id FROM {$this->tables['executions']} {$where}) ";
    $result = $this->db->exec_query($sql);

    $sql = " DELETE FROM {$this->tables['executions']} {$where}";
    $result = $this->db->exec_query($sql);

    $sql = " DELETE FROM {$this->tables['user_assignments']}  {$where}";
    $result=$this->db->exec_query($sql);

    // Custom fields
    $this->cfield_mgr->remove_all_design_values_from_node($safe_id,'build');

    $sql = " DELETE FROM {$this->tables['builds']} WHERE id={$safe_id}";
    $result=$this->db->exec_query($sql);
    return $result ? 1 : 0;
  }


  /*
    function: get_by_id
              get information about a build

    args : id: build id

    returns: map with following keys
             id: build id
             name: build name
             notes: build notes
             active: build active status
             is_open: build open status
             testplan_id

    rev :
  */
  function get_by_id($id,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $my = array('options' => array('tplan_id' => null, 'output' => 'full'));
    $my['options'] = array_merge($my['options'],(array)$opt);
    
    $safe_id = intval($id);  
    
    $sql = "/* {$debugMsg} */";
    switch($my['options']['output'])
    {
      case 'minimun':
        $sql .= " SELECT id,is_open,active FROM {$this->tables['builds']} "; 
      break;
      
      case 'full':
      default:
        $sql .= " SELECT * FROM {$this->tables['builds']} "; 
      break;
    }
    
    $sql .= " WHERE id = {$safe_id} ";
    if(!is_null($my['options']['tplan_id']) && ($safe_tplan = intval($my['options']['tplan_id'])) > 0)
    {
      $sql .= " AND testplan_id = {$safe_tplan} ";
    }
    
    $result = $this->db->exec_query($sql);
    $myrow = $this->db->fetch_array($result);
    return $myrow;
  }



  /*
     function: get_by_name
              get information about a build by name
    */          
    
  function get_by_name($name,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $my = array('options' => array('tplan_id' => null, 'output' => 'full'));
    $my['options'] = array_merge($my['options'],(array)$opt);

    $sql = "/* {$debugMsg} */";
    switch($my['options']['output'])
    {
      case 'minimun':
        $sql .= " SELECT B.id, B.name, B.is_open, B.active ";
      break;
      
      case 'full':
      default:
        $sql .= " SELECT B.* ";
      break;
    }

    $sql .= " FROM {$this->tables['builds']} B " .
          " WHERE B.name = '" . $this->db->prepare_string($name) . "'";

    if(!is_null($my['options']['tplan_id']) && ($safe_tplan = intval($my['options']['tplan_id'])) > 0)
    {
      $sql .= " AND B.testplan_id = {$safe_tplan} ";
    }

    $rs = $this->db->get_recordset($sql);
    return($rs);
  }



  /**
   * Set date of closing build
   * 
   * @param integer $id Build identifier
   * @param string $targetDate, format YYYY-MM-DD. can be null
   * 
   * @return TBD TBD
   */
  function setClosedOnDate($id,$targetDate)
  {
    $sql = " UPDATE {$this->tables['builds']} ";
    
    if( is_null($targetDate) )
    {
      $sql .= " SET closed_on_date=NULL ";
    }
    else
    {
      $sql .= " SET closed_on_date='" . $this->db->prepare_string($targetDate) . "'";        
    }
    $sql .= " WHERE id={$id} "; 

    $result = $this->db->exec_query($sql);
  }


  /**
   *
   * NEWNEW
   */
  function get_linked_cfields_at_design($id,$tproject_id,$filters=null,$access_key='id') 
  {
    $safeID = $id == 0 ? null : intval($id);
    $cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,cfield_mgr::CF_ENABLED,
                                                              $filters,'build',$id,$access_key);
    return $cf_map;
  }

  /*
    function: html_table_of_custom_field_inputs
              
              
    args: $id
          [$parent_id]: need when you call this method during the creation
                        of a test suite, because the $id will be 0 or null.
                        
          [$scope]: 'design','execution'
          
    returns: html string
    
  */
  function html_table_of_custom_field_inputs($id,$tproject_id,$scope='design',$name_suffix='',$input_values=null) 
  {
    $cf_smarty='';
    $method_suffix = $scope=='design' ? $scope : 'execution';
    $method_name = "get_linked_cfields_at_{$method_suffix}";
    $cf_map=$this->$method_name($id,$tproject_id);
    if(!is_null($cf_map))
    {
      $cf_smarty = $this->cfield_mgr->html_table_inputs($cf_map,$name_suffix,$input_values);
    }
    return($cf_smarty);
  }


  /*
    function: html_table_of_custom_field_inputs
              
              
    args: $id
          [$parent_id]: need when you call this method during the creation
                        of a test suite, because the $id will be 0 or null.
                        
          [$scope]: 'design','execution'
          
    returns: html string
    
  */
  function html_custom_field_inputs($id,$tproject_id,$scope='design',$name_suffix='',$input_values=null) 
  {
    $itemSet='';
    $method_suffix = $scope=='design' ? $scope : 'execution';
    $method_name = "get_linked_cfields_at_{$method_suffix}";
    $cf_map=$this->$method_name($id,$tproject_id);
    if(!is_null($cf_map))
    {
      $itemSet = $this->cfield_mgr->html_inputs($cf_map,$name_suffix,$input_values);
    }
    return $itemSet;
  }

  /*
    function: html_table_of_custom_field_values
  
    args: $id
          [$scope]: 'design','execution'
          
          [$filters]:default: null
                              
                             map with keys:
          
                             [show_on_execution]: default: null
                                                  1 -> filter on field show_on_execution=1
                                                       include ONLY custom fields that can be viewed
                                                       while user is execution testcases.
                             
                                                  0 or null -> don't filter
  
    returns: html string
  
    rev :
  */
  function html_table_of_custom_field_values($id,$tproject_id,$scope='design',$filters=null,$formatOptions=null)
  {
    $cf_smarty='';
    $parent_id=null;
    $label_css_style=' class="labelHolder" ' ;
    $value_css_style = ' ';

    $add_table=true;
    $table_style='';
    if( !is_null($formatOptions) )
    {
      $label_css_style = isset($formatOptions['label_css_style']) ? $formatOptions['label_css_style'] : $label_css_style;
      $value_css_style = isset($formatOptions['value_css_style']) ? $formatOptions['value_css_style'] : $value_css_style;

      $add_table=isset($formatOptions['add_table']) ? $formatOptions['add_table'] : true;
      $table_style=isset($formatOptions['table_css_style']) ? $formatOptions['table_css_style'] : $table_style;
    } 
    
    $show_cf = config_get('custom_fields')->show_custom_fields_without_value;
    $cf_map=$this->get_linked_cfields_at_design($id,$tproject_id,$filters);
    
    if( !is_null($cf_map) )
    {
      foreach($cf_map as $cf_id => $cf_info)
      {
        if(isset($cf_info['node_id']) || $cf_info['node_id'] || $show_cf)
        {
          $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label'],null,true));
          $cf_smarty .= "<tr><td {$label_css_style}>" . htmlspecialchars($label) . "</td>" .
                  "<td {$value_css_style}>" .
                      $this->cfield_mgr->string_custom_field_value($cf_info,$id) . "</td></tr>\n";
        }
      }
    }
    
    if($cf_smarty != '' && $add_table)
    {
      $cf_smarty = "<table {$table_style}>" . $cf_smarty . "</table>";
    }

    return $cf_smarty;
  }





} // end class build_mgr


// ##################################################################################
/** 
 * Milestone Manager Class 
 * @package TestLink
 **/
class milestone_mgr extends tlObject
{
  /** @var database handler */
  var $db;

  /** 
   * class constructor 
   * 
   * @param resource &$db reference to database handler
   **/
  function milestone_mgr(&$db)
  {
    parent::__construct();
    $this->db = &$db;
  }

  /*
    function: create()

    args :  keys
            $tplan_id
            $name
            $target_date: string with format: 
            $start_date: 
            $low_priority: percentage
            $medium_priority: percentage
            $high_priority: percentage

    returns:

  */
  function create($mi)
  {
    $item_id=0;
    $dateFields=null;
    $dateValues=null;
    $dateKeys=array('target_date','start_date');
    
    // check dates
    foreach($dateKeys as $varname)
    {
      $value=  trim($mi->$varname);
      if($value != '') 
      {
        if (($time = strtotime($value)) == -1 || $time === false) 
        {
          die (__FUNCTION__ . ' Abort - Invalid date');
        }
        $dateFields[]=$varname;  
        $dateValues[]=" '{$this->db->prepare_string($value)}' ";
      }
    }
    $additionalFields='';
    if( !is_null($dateFields) )
    {
      $additionalFields= ',' . implode(',',$dateFields) ;
      $additionalValues= ',' . implode(',',$dateValues) ;
    }
    /* for future
    $sql = "INSERT INTO {$this->tables['milestones']} " .
           " (testplan_id,name,platform_id,build_id,a,b,c{$additionalFields}) " .
           " VALUES (" . intval($mi->tplan_id) . "," . 
           "'{$this->db->prepare_string($mi->name)}'," .
           intval($mi->platform_id) . "," . intval($mi->build_id) . "," .
           $mi->low_priority . "," .  $mi->medium_priority . "," . $mi->high_priority . 
           $additionalValues . ")";
    */
    $sql = "INSERT INTO {$this->tables['milestones']} " .
           " (testplan_id,name,a,b,c{$additionalFields}) " .
           " VALUES (" . intval($mi->tplan_id) . "," . 
           "'{$this->db->prepare_string($mi->name)}'," .
           $mi->low_priority . "," .  $mi->medium_priority . "," . $mi->high_priority . 
           $additionalValues . ")";

    $result = $this->db->exec_query($sql);
    
    if ($result)
    {
      $item_id = $this->db->insert_id($this->tables['milestones']);
    }
    
    return $item_id;
  }

  /*
    function: update

    args :
          $id
          $name
          $notes
          [$active]: default: 1
          [$open]: default: 1



    returns:

    rev :
  */
  function update($id,$name,$target_date,$start_date,$low_priority,$medium_priority,$high_priority)
  {
    $sql = "UPDATE {$this->tables['milestones']} " . 
           " SET name='{$this->db->prepare_string($name)}', " .
         " target_date='{$this->db->prepare_string($target_date)}', " .
         " start_date='{$this->db->prepare_string($start_date)}', " .
         " a={$low_priority}, b={$medium_priority}, c={$high_priority} WHERE id={$id}";
    $result = $this->db->exec_query($sql);
    return $result ? 1 : 0;
  }



  /*
    function: delete

    args :
          $id


    returns:

  */
  function delete($id)
  {
    $sql = "DELETE FROM {$this->tables['milestones']} WHERE id={$id}";
    $result=$this->db->exec_query($sql);
    return $result ? 1 : 0;
  }


  /*
    function: get_by_id

    args :
          $id
    returns:

  */
  function get_by_id($id)
  {
    $sql=" SELECT M.id, M.name, M.a AS high_percentage, " .
         " M.b AS medium_percentage, M.c AS low_percentage, " .
         " M.target_date, M.start_date, " .
         " M.testplan_id, NH_TPLAN.name AS testplan_name " .
    //     " M.build_id, B.name AS build_name, " . 
    //     " M.platform_id, P.name AS platform_name " .  
         " FROM {$this->tables['milestones']} M " .
         " JOIN {$this->tables['nodes_hierarchy']} NH_TPLAN " .
         " ON NH_TPLAN.id=M.testplan_id " .
    //     " LEFT OUTER JOIN {$this->tables['builds']} B " .
    //     " ON B.id=M.build_id " .
    //     " LEFT OUTER JOIN {$this->tables['platforms']} P " .
    //     " ON P.id=M.platform_id " .
         " WHERE M.id = " . $this->db->prepare_int($id);
            
    $row = $this->db->fetchRowsIntoMap($sql,'id');
    return $row;
  }

  /**
   * check existence of milestone name in Test Plan
   * 
   * @param integer $tplan_id  test plan id.
   * @param string $milestone_name milestone name
   * @param integer $milestone_id default: null
   *                when is not null we add milestone_id as filter, this is useful
   *                to understand if is really a duplicate when using this method
   *                while managing update operations via GUI
   * 
   * @return integer 1 => name exists
   */
  function check_name_existence($tplan_id,$milestone_name,$milestone_id=null,$case_sensitive=0)
  {
    $sql = " SELECT id, name FROM {$this->tables['milestones']} " .
           " WHERE testplan_id = " . $this->db->prepare_int($tplan_id);
    
    if($case_sensitive)
    {
      $sql .= " AND name=";
    }
    else
    {
      $milestone_name=strtoupper($milestone_name);
      $sql .= " AND UPPER(name)=";
    }
    $sql .= "'{$this->db->prepare_string($milestone_name)}'";
    
    if( !is_null($milestone_id) )
    {
      $sql .= " AND id <> " . $this->db->prepare_int($milestone_id);
    }
    
    $result = $this->db->exec_query($sql);
    $status= $this->db->num_rows($result) ? 1 : 0;
    
    return $status;
  }


  /*
    function: get_all_by_testplan
              get info about all milestones defined for a testlan
    args :
          tplan_id


    returns:

    rev :
  */
  function get_all_by_testplan($tplan_id)
  {
    $sql=" SELECT M.id, M.name, M.a AS high_percentage, M.b AS medium_percentage, M.c AS low_percentage, " .
       " M.target_date, M.start_date, M.testplan_id, NH.name as testplan_name " .   
       " FROM {$this->tables['milestones']} M, {$this->tables['nodes_hierarchy']} NH " .
       " WHERE testplan_id={$tplan_id} AND NH.id = testplan_id " .
       " ORDER BY M.target_date,M.name";
    $rs=$this->db->get_recordset($sql);
    return $rs;
  }


} // end class milestone_mgr