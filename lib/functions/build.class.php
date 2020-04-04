<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Manages test plan operations and related items like Custom fields, 
 * Builds, Custom fields, etc
 *
 * @filesource  build.class.php
 * @package     TestLink
 * @author      franciscom
 * @copyright   2020, TestLink community 
 * @link        http://testlink.sourceforge.net/
 *
 **/

/** related functionality */
require_once( dirname(__FILE__) . '/tree.class.php' );
require_once( dirname(__FILE__) . '/assignment_mgr.class.php' );
require_once( dirname(__FILE__) . '/attachments.inc.php' );

/** 
 * Build Manager Class 
 * @package TestLink
 **/
class build extends tlObject {
  var $db;
  var $cfield_mgr;

  /** 
   * Build Manager class constructor 
   * 
   * @param resource &$db reference to database handler
   **/
  function __construct(&$db) 
  {
    parent::__construct();
    $this->db = &$db;
    $this->cfield_mgr = new cfield_mgr($this->db);
  }


  /**
   * Build Manager
   */
  function setZeroOneAttr($id,$attr,$zeroOne) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = "/* $debugMsg */ " . 
           "UPDATE {$this->tables['builds']} SET {$attr}=" . ($zeroOne ? 1 : 0) . " WHERE id=" . intval($id);
    $this->db->exec_query($sql); 
  }


  /**
   * Build Manager
   */
  function setActive($id) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $this->setZeroOneAttr($id,'active',1);
  }

  /**
   * Build Manager
   */
  function setInactive($id) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $this->setZeroOneAttr($id,'active',0);
  }

  /**
   * Build Manager
   */
  function setOpen($id) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $this->setZeroOneAttr($id,'is_open',1);
    $this->setClosedOnDate($id,null);
  }

  /**
   * Build Manager
   */
  function setClosed($id) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $this->setZeroOneAttr($id,'is_open',0);
    $timestamp = explode(' ',trim($this->db->db_now(),"'"));
    $this->setClosedOnDate($id,$timestamp[0]);
  }



  /**
   * Build Manager 
   *
   * createFromObject
   */
  function createFromObject($item,$opt=null) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    try {
      // mandatory checks
      if(strlen($item->name)==0) {
        throw new Exception('Build - Empty name is not allowed');      
      }  
    
      // what checks need to be done ?
      // 1. does test plan exist?
      $item->tplan_id = intval($item->tplan_id);
      $tm = new tree($this->db);
      $ntv = array_flip($tm->get_available_node_types());
      $pinfo = $tm->get_node_hierarchy_info($item->tplan_id);
      if(is_null($pinfo) || 
         $ntv[$pinfo['node_type_id']] != 'testplan') {
        throw new Exception(
          "Build - Test Plan ID {$item->tplan_id} does not exist");    
      }  

      // 2. there is NO other build on test plan with same name
      $name = trim($item->name);
      $op = $this->checkNameExistence($item->tplan_id,$name);
      if(!$op['status_ok']) {
        throw new Exception(
          "Build name {$name} is already in use on Test Plan {$item->tplan_id}");      
      }  
    } catch (Exception $e) {
      throw $e;  // rethrow
    }

    // seems OK => check all optional attributes
    $build = new stdClass();
    $prop = array('release_date' => '','notes' => '',
                  'commit_id' => '', 'tag' => '',
                  'branch' => '', 'release_candidate' => '',
                  'is_active' => 1,'is_open' => 1,
                  'creation_ts' => $this->db->db_now());

    $build->name = $item->name;
    $build->tplan_id = $item->tplan_id;
    foreach( $prop as $nu => $value  ) {      
      $build->$nu = $value;
      if( property_exists($item, $nu) ) {
        switch( $nu ) {
          case 'creation_ts':
            if(null != $item->$nu && '' == trim($item->$nu) ) {
              $build->$nu = $item->$nu;
            }
          break;
          
          case 'is_active':
          case 'is_open':
            $build->$nu = intval($item->$nu) > 0 ? 1 : 0;
          break;

          default:
            $build->$nu = $item->$nu;
          break; 
        }
      }  
    }
    $build->release_date = trim($build->release_date);
    $ps = 'prepare_string';
    $sql = " INSERT INTO {$this->tables['builds']} " .
           " (testplan_id,name,notes,
              commit_id,tag,branch,release_candidate,
              active,is_open,creation_ts,release_date) " .
           " VALUES ('". $build->tplan_id . "','" . 
             $this->db->$ps($build->name) . "','" .
             $this->db->$ps($build->notes) . "',";

    $sql .=  "'" . $this->db->$ps($build->commit_id) . "'," . 
             "'" . $this->db->$ps($build->tag) . "'," .
             "'" . $this->db->$ps($build->branch) . "'," .
             "'" . $this->db->$ps($build->release_candidate) . "',";

    $sql .= "{$build->is_active},{$build->is_open},{$build->creation_ts}";

    if($build->release_date == '') {
      $sql .= ",NULL)";
    } else {
      $sql .= ",'" . $this->db->$ps($build->release_date) . "')";
    }

    $id = 0;
    $result = $this->db->exec_query($sql);
    if ($result) {
      $id = $this->db->insert_id($this->tables['builds']);
    }
    
    return $id;
  }


  /*
    Build Manager 

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

    if($targetDate == '') {
      $sql .= "NULL,";
    }       
    else {
      $sql .= "'" . $this->db->prepare_string($targetDate) . "',";
    }
    
    $sql .= "{$active},{$open},{$this->db->db_now()})";                        

    $id = 0;
    $result = $this->db->exec_query($sql);
    if ($result) {
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
  function update($id,$name,$notes,$attr=null) {

    $members = array('is_active' => null, 'is_open' => null,
                     'release_date' => '', 'closed_on_date=' => '',
                     'commit_id' => '', 'tag' => '', 
                     'branch' => '', 'release_candidate' => '');

    $members = array_merge($members,(array)$attr);

    $closure_date = '';
    $targetDate = trim($members['release_date']);
    $sql = " UPDATE {$this->tables['builds']} " .
           " SET name='" . $this->db->prepare_string($name) . "'," .
           "     notes='" . $this->db->prepare_string($notes) . "'";
    
    if($targetDate == '') {
      $sql .= ",release_date=NULL";
    } else {
      $sql .= ",release_date='" . $this->db->prepare_string($targetDate) . "'";
    }

    if( !is_null($members['is_active']) ) {
      $sql .=" , active=" . intval($members['is_active']);
    }
    
    if( !is_null($members['is_open']) ) {
      $open_status=intval($members['is_open']) ? 1 : 0; 
      $sql .=" , is_open=" . $open_status;
      
      if($open_status == 1) {
        $closure_date = ''; 
      }
    }

    // New attributes
    $ps = 'prepare_string';
    $ax = array('commit_id','tag','branch','release_candidate');
    foreach( $ax as $fi ) {
      $sql .= ", $fi='" . $this->db->$ps($members[$fi]) . "'";
    }
    
    if($closure_date == '') {
      $sql .= ",closed_on_date=NULL";
    } else {
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
   */
  function delete($id) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $safe_id = intval($id);
    $where = " WHERE build_id={$safe_id}";
    $execIDSetSQL = " SELECT id FROM {$this->tables['executions']} {$where} ";


    // Attachments NEED special processing.
  
    // get test step exec attachments if any exists
    $dummy = " SELECT id FROM {$this->tables['execution_tcsteps']} " . 
             " WHERE execution_id IN ({$execIDSetSQL}) ";
     
    $rs = $this->db->fetchRowsIntoMap($dummy,'id');
    if(!is_null($rs)) {
      foreach($rs as $fik => $v) {
        deleteAttachment($this->db,$fik,false);
      }  
    }  

    // execution attachments
    $dummy = " SELECT id FROM {$this->tables['attachments']} " . 
             " WHERE fk_table = 'executions' " .
             " AND fk_id IN ({$execIDSetSQL}) ";
  
    $rs = $this->db->fetchRowsIntoMap($dummy,'id');
    if(!is_null($rs)) {
      foreach($rs as $fik => $v) {
        deleteAttachment($this->db,$fik,false);
      }  
    }  


    // Execution Bugs
    $sql = " DELETE FROM {$this->tables['execution_bugs']} " .
           " WHERE execution_id IN ({$execIDSetSQL}) ";
    $result = $this->db->exec_query($sql);

    // Execution tcsteps results
    $sql = "DELETE FROM {$this->tables['execution_tcsteps']} " .
           " WHERE execution_id IN ({$execIDSetSQL}) ";
    $result = $this->db->exec_query($sql);

    $sql = "DELETE FROM {$this->tables['cfield_execution_values']} " .
           " WHERE execution_id IN ({$execIDSetSQL}) ";
    $result = $this->db->exec_query($sql);


    // Finally Executions table
    $sql = " DELETE FROM {$this->tables['executions']} {$where}";
    $result = $this->db->exec_query($sql);


    // Build ID is the Access Key 
    // User Task Assignment
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
  */
  function get_by_id($id,$opt=null) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $my = array('options' => 
                array('tplan_id' => null, 'output' => 'full', 'fields' => '*'));
    $my['options'] = array_merge($my['options'],(array)$opt);
    
    $safe_id = intval($id);  
    
    $sql = "/* {$debugMsg} */";
    switch($my['options']['output']) {
      case 'minimun':
        $sql .= " SELECT id,is_open,active,active AS is_active ";  
      break;

      case 'fields':
        $sql .= " SELECT {$my['options']['fields']} "; 
      break;
      
      case 'full':
      default:
        $sql .= " SELECT *, active AS is_active "; 
      break;
    }
    
    $sql .= " FROM {$this->tables['builds']} WHERE id = {$safe_id} ";
    if(!is_null($my['options']['tplan_id']) && ($safe_tplan = intval($my['options']['tplan_id'])) > 0) {
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

 

  /**
   * Build Manager
   *
   */
  function checkNameExistence($tplan_id,$build_name,$build_id=null,
                              $caseSens=0) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = " /* $debugMsg */ SELECT id, name, notes " .
      " FROM {$this->tables['builds']} " .
      " WHERE testplan_id = {$tplan_id} ";
    
    if($caseSens) {
      $sql .= " AND name=";
    } else {
      $build_name = strtoupper($build_name);
      $sql .= " AND UPPER(name)=";
    }
    $sql .= "'" . $this->db->prepare_string($build_name) . "'";
    
    if( !is_null($build_id) ) {
      $sql .= " AND id <> " . $this->db->prepare_int($build_id);
    }

    $result = $this->db->exec_query($sql);
    $rn = $this->db->num_rows($result);
    $status = array();
    $status['status_ok'] = $rn == 0 ? 1 : 0;    
    return $status;
  }


} // end class build_mgr