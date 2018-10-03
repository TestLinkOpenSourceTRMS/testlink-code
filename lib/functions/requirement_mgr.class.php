<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @filesource  requirement_mgr.class.php
 * @author      Francisco Mancardi <francisco.mancardi@gmail.com>
 * @copyright   2007-2018, TestLink community 
 *
 * Manager for requirements.
 * Requirements are children of a requirement specification (requirements container)
 *
 * 
 */

// Needed to use extends tlObjectWithAttachments, If not present autoload fails.
require_once( dirname(__FILE__) . '/attachments.inc.php');
class requirement_mgr extends tlObjectWithAttachments {
  var $db;
  var $cfield_mgr;
  var $my_node_type;
  var $tree_mgr;
  var $node_types_descr_id;
  var $node_types_id_descr;
  var $attachmentTableName;

  // 20100220 - franciscom - I'm will work only on XML
  // then remove other formats till other dev do refactor
  var $import_file_types = array("csv" => "CSV",
                                 "csv_doors" => "CSV (Doors)",
                                 "XML" => "XML",
                                 "DocBook" => "DocBook");

  var $export_file_types = array("XML" => "XML");
  
  var $fieldSize;
  var $reqCfg;
  var $internal_links;
  var $relationsCfg;
  var $notifyOn;
  var $reqTCLinkCfg;

  
  
  const AUTOMATIC_ID=0;
  const ALL_VERSIONS=0;
  const LATEST_VERSION=-1;
  const NO_REVISION=-1;
    


  /*
    function: requirement_mgr
              contructor

    args: db: reference to db object

    returns: instance of requirement_mgr

  */
  function __construct(&$db) {

    $this->db = &$db;
    $this->cfield_mgr=new cfield_mgr($this->db);
    $this->tree_mgr =  new tree($this->db);
    
    $this->attachmentTableName = 'req_versions';
    
    tlObjectWithAttachments::__construct($this->db,$this->attachmentTableName);

    $this->node_types_descr_id= $this->tree_mgr->get_available_node_types();
    $this->node_types_id_descr=array_flip($this->node_types_descr_id);
    $this->my_node_type=$this->node_types_descr_id['requirement'];
    $this->object_table=$this->tables['requirements'];

    $this->fieldSize = config_get('field_size');
    $this->reqCfg = config_get('req_cfg');
    $this->reqTCLinkCfg = config_get('reqTCLinks');

    $this->relationsCfg = new stdClass();
    $this->relationsCfg->interProjectLinking = $this->reqCfg->relations->interproject_linking;
    
    $this->internal_links = config_get('internal_links');
    
    $this->notifyOn = null;
  }

  /*
    function: get_export_file_types
              getter
  
    args: -
  
    returns: map
             key: export file type code
             value: export file type verbose description
  
  */
  function get_export_file_types()
  {
    return $this->export_file_types;
  }

  /*
    function: get_impor_file_types
              getter
  
    args: -
  
    returns: map
             key: import file type code
             value: import file type verbose description
  
  */
  function get_import_file_types()
  {
    return $this->import_file_types;
  }





/*
  function: get_by_id


  args: id: requirement id (can be an array)
      [version_id]: requirement version id (can be an array)
      [version_number]: 
      [options]
      

  returns: null if query fails
           map with requirement info


*/
function get_by_id($id,$version_id=self::ALL_VERSIONS,$version_number=1,$options=null,$filters=null)
{
  static $debugMsg;
  static $userCache;  // key: user id, value: display name
  static $lables;
  static $user_keys;

  if(!$debugMsg) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $labels['undefined'] = lang_get('undefined');
    $user_keys = array('author' => 'author_id', 'modifier' => 'modifier_id');
  }
  
  
  $my['options'] = array('order_by' => " ORDER BY REQV.version DESC ", 
                         'output_format' => 'array', 'renderImageInline' => false,
                         'decodeUsers' => true, 'outputLevel' => 'std');

  $my['options'] = array_merge($my['options'], (array)$options);

    // null => do not filter
  $my['filters'] = array('status' => null, 'type' => null);
  $my['filters'] = array_merge($my['filters'], (array)$filters);

  $filter_clause = '';
  $dummy[]='';  // trick to make implode() work
  foreach( $my['filters'] as $field2filter => $value) {
    if( !is_null($value) ) {
      $dummy[] = " {$field2filter} = '{$value}' ";
    }
  }

  if( count($dummy) > 1) {
    $filter_clause = implode(" AND ",$dummy);
  }

  $where_clause = " WHERE NH_REQV.parent_id ";
  if( ($id_is_array=is_array($id)) ) {
    $where_clause .= "IN (" . implode(",",$id) . ") ";
  } else {
    $where_clause .= " = {$id} ";
  }
  
  if(is_array($version_id)) {
    $versionid_list = implode(",",$version_id);
    $where_clause .= " AND REQV.id IN ({$versionid_list}) ";
  } else {
    if( is_null($version_id) ) {
      // search by "human" version number
      $where_clause .= " AND REQV.version = {$version_number} ";
    } else {
      if($version_id != self::ALL_VERSIONS && $version_id != self::LATEST_VERSION) {
        $where_clause .= " AND REQV.id = {$version_id} ";
      }
    }
  }

  // added -1 AS revision_id to make some process easier 
  switch($my['options']['outputLevel']) {
    case 'minimal':
      $outf = " /* $debugMsg */ 
                SELECT REQ.id,REQ.req_doc_id,REQV.id AS version_id,
                NH_REQ.name AS title "; 
    break;

    case 'std':
    default:
      $outf = " /* $debugMsg */ SELECT REQ.id,REQ.srs_id,REQ.req_doc_id," . 
              " REQV.scope,REQV.status,REQV.type,REQV.active," . 
              " REQV.is_open,REQV.author_id,REQV.version,REQV.id AS version_id," .
              " REQV.expected_coverage,REQV.creation_ts,REQV.modifier_id," .
              " REQV.modification_ts,REQV.revision, -1 AS revision_id, " .
              " NH_REQ.name AS title, REQ_SPEC.testproject_id, " .
              " NH_RSPEC.name AS req_spec_title, REQ_SPEC.doc_id AS req_spec_doc_id, NH_REQ.node_order ";
    break;
  }

  // added -1 AS revision_id to make some process easier 
  $sql = $outf .
         " FROM {$this->object_table} REQ " .
         " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = REQ.id " .
         " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.parent_id = NH_REQ.id ".
         " JOIN  {$this->tables['req_versions']} REQV ON REQV.id = NH_REQV.id " .  
         " JOIN {$this->tables['req_specs']} REQ_SPEC ON REQ_SPEC.id = REQ.srs_id " .
         " JOIN {$this->tables['nodes_hierarchy']} NH_RSPEC ON NH_RSPEC.id = REQ_SPEC.id " .
         $where_clause . $filter_clause . $my['options']['order_by'];

  $decodeUserMode = 'simple';       
  if ($version_id != self::LATEST_VERSION) {
    switch($my['options']['output_format']) {
        case 'mapOfArray':
        $recordset = $this->db->fetchRowsIntoMap($sql,'id',database::CUMULATIVE);
        $decodeUserMode = 'complex';       
      break;
  
      case 'array':
      default:
        $recordset = $this->db->get_recordset($sql);
      break;
        
    }
  } else {
    // But, how performance wise can be do this, 
    // instead of using MAX(version) and a group by? 
    //           
    // if $id was a list then this will return something USELESS
    //           
    if( !$id_is_array ) {         
      $recordset = array($this->db->fetchFirstRow($sql));
    } else {
      // Write to event viewer ???
      // Developer Needs to user 
      die('use getByIDBulkLatestVersionRevision()');
    }
  }

  $rs = null;
  if(!is_null($recordset) && $my['options']['renderImageInline']) {
    $k2l = array_keys($recordset);
    foreach($k2l as $akx) { 
      $this->renderImageAttachments($id,$recordset[$akx]);
    } 
    reset($recordset);
  }

  $rs = $recordset;
  if(!is_null($recordset) && $my['options']['decodeUsers']) {
    switch ($decodeUserMode) {
      case 'complex':
        // output[REQID][0] = array('id' =>, 'xx' => ...)
        $flevel = array_keys($recordset);
        foreach($flevel as $flk) {
          $key2loop = array_keys($recordset[$flk]);
          foreach( $key2loop as $key ) {
            foreach( $user_keys as $ukey => $userid_field) {
              $rs[$flk][$key][$ukey] = '';
              if(trim($rs[$flk][$key][$userid_field]) != "") {
                if( !isset($userCache[$rs[$flk][$key][$userid_field]]) ) {
                  $user = tlUser::getByID($this->db,$rs[$flk][$key][$userid_field]);
                  $rs[$flk][$key][$ukey] = $user ? $user->getDisplayName() : $labels['undefined'];
                  $userCache[$rs[$flk][$key][$userid_field]] = $rs[$flk][$key][$ukey];
                  unset($user);
                } else {
                  $rs[$flk][$key][$ukey] = $userCache[$rs[$flk][$key][$userid_field]];
                }
              }
            }  
          }
        }  
      break;
      
      case 'simple':
      default:
        $key2loop = array_keys($recordset);
        foreach( $key2loop as $key ) {
          foreach( $user_keys as $ukey => $userid_field) {
            $rs[$key][$ukey] = '';
            if(trim($rs[$key][$userid_field]) != "") {
              if( !isset($userCache[$rs[$key][$userid_field]]) ) {
                $user = tlUser::getByID($this->db,$rs[$key][$userid_field]);
                $rs[$key][$ukey] = $user ? $user->getDisplayName() : $labels['undefined'];
                $userCache[$rs[$key][$userid_field]] = $rs[$key][$ukey];
                unset($user);
              } else {
                $rs[$key][$ukey] = $userCache[$rs[$key][$userid_field]];
              }
            }
          }  
        }
      break;
    }
  }    

  unset($recordset);
  unset($my);
  unset($dummy);

  return $rs;
}

  /*
    function: create

    args: srs_id: req spec id, parent of requirement to be created
          reqdoc_id
          title
          scope
          user_id: author
          [status]
          [type]
          [expected_coverage]
          [node_order]

    returns: map with following keys:
             status_ok -> 1/0
             msg -> some simple message, useful when status_ok ==0
             id -> id of new requirement.

  @internal revision
  */
function create($srs_id,$reqdoc_id,$title, $scope, $user_id,
                $status = TL_REQ_STATUS_VALID, $type = TL_REQ_TYPE_INFO,
                $expected_coverage=1,$node_order=0,$tproject_id=null, $options=null)
{
  // This kind of saving is important when called in a loop in situations like
  // copy test project
  static $debugMsg;
  static $log_message;

  if(!$log_message)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $log_message = lang_get('req_created_automatic_log');
  }
  
  $tproject_id = is_null($tproject_id) ? $this->tree_mgr->getTreeRoot($srs_id) : $tproject_id;

  $result = array( 'id' => 0, 'status_ok' => 0, 'msg' => 'ko');
  $my['options'] = array('quickAndDirty' => false);
  $my['options'] = array_merge($my['options'], (array)$options);

  if(!$my['options']['quickAndDirty'])
  {
    $reqdoc_id = trim_and_limit($reqdoc_id,$this->fieldSize->req_docid);
    $title = trim_and_limit($title,$this->fieldSize->req_title);
    $op = $this->check_basic_data($srs_id,$tproject_id,$title,$reqdoc_id);
  }
  else
  {
    $op['status_ok'] = true;
  }  

  $result['msg'] = $op['status_ok'] ? $result['msg'] : $op['msg'];
  if( $op['status_ok'] )
  {
    $result = $this->create_req_only($srs_id,$reqdoc_id,$title,$user_id,$node_order);
    if($result["status_ok"])
    {
      if ($this->internal_links->enable )
      {
        $scope = req_link_replace($this->db, $scope, $tproject_id);
      }

      $op = $this->create_version($result['id'],1,$scope,$user_id,
                                  $status,$type,intval($expected_coverage));
      $result['msg'] = $op['status_ok'] ? $result['msg'] : $op['msg'];
      $result['version_id'] = $op['status_ok'] ? $op['id'] : -1;
      
      if( $op['status_ok'] )
      {
        $sql =   "/* $debugMsg */ " .
            "UPDATE {$this->tables['req_versions']} " .
            " SET log_message='" . $this->db->prepare_string($log_message) . "'" .
            " WHERE id = " . intval($op['id']) ;
        $this->db->exec_query($sql);
      }

    }  
  }
  return $result;
  
} // function end


  /*
    function: update


    args: id: requirement id
          version_id
          reqdoc_id
          title
          scope
          user_id: author
          status
          type
          $expected_coverage
          [skip_controls]


    returns: map: keys : status_ok, msg

  @internal revision
  20091202 - franciscom - 
  
  */

function update($id,$version_id,$reqdoc_id,$title, $scope, $user_id, $status, $type,
                $expected_coverage,$node_order=null,$tproject_id=null,$skip_controls=0,
                $create_revision=false,$log_msg=null)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
  $result['status_ok'] = 1;
  $result['msg'] = 'ok';
    
  $db_now = $this->db->db_now();
   
  // get SRSid, needed to do controls
  $rs=$this->get_by_id($id,$version_id);
  $req = $rs[0];
  $srs_id=$req['srs_id'];
    
    // try to avoid function calls when data is available on caller
  $tproject_id = is_null($tproject_id) ? $this->tree_mgr->getTreeRoot($srs_id): $tproject_id;

  if ($this->internal_links->enable ) 
  {
    $scope = req_link_replace($this->db, $scope, $tproject_id);
  }
    
  $reqdoc_id=trim_and_limit($reqdoc_id,$this->fieldSize->req_docid);
  $title=trim_and_limit($title,$this->fieldSize->req_title);
  $chk=$this->check_basic_data($srs_id,$tproject_id,$title,$reqdoc_id,$id);

  if($chk['status_ok'] || $skip_controls)
  {
    if( $create_revision )
    {  
      $this->create_new_revision($version_id,$user_id,$tproject_id,$req,$log_msg);
    }
    
     $sql = array();

      $q = "/* $debugMsg */ UPDATE {$this->tables['nodes_hierarchy']} " .
           " SET name='" . $this->db->prepare_string($title) . "'";
      if( !is_null($node_order) )
      {
        $q .= ', node_order= ' . abs(intval($node_order));         
      }
       $sql[] = $q . " WHERE id={$id}";
       

      $sql[] = "/* $debugMsg */ UPDATE {$this->tables['requirements']} " .
               " SET req_doc_id='" . $this->db->prepare_string($reqdoc_id) . "'" .
               " WHERE id={$id}";
      
      $sql_temp = "/* $debugMsg */ UPDATE {$this->tables['req_versions']} " .
                  " SET scope='" . $this->db->prepare_string($scope) . "', " .
                  " status='" . $this->db->prepare_string($status) . "', " .
                  " expected_coverage={$expected_coverage}, " . 
                  " type='" . $this->db->prepare_string($type) . "' ";
      
      // only if no new revision is created set modifier and modification ts
      // otherwise those values are handled by function create_new_revision()
      if (!$create_revision) 
      {
        $sql_temp .= ", modifier_id={$user_id}, modification_ts={$db_now} ";
      }
      
      $sql[] = $sql_temp . " WHERE id=" . intval($version_id);

      foreach($sql as $stm)
      {
        $qres = $this->db->exec_query($stm);
        if( !$qres )
        {
          $result['status_ok'] = 0;
          $result['msg'] = $this->db->error_msg;
          $result['sql'] = $stm;
          break;
        }
      }

    } //     if($chk['status_ok'] || $skip_controls)
    else
    {
      $result['status_ok']=$chk['status_ok'];
      $result['msg']=$chk['msg'];
    }
    return $result;
  } //function end



  /*
    function: delete
              Requirement
              Requirement link to testcases
              Requirement relations
              Requirement custom fields values
              Attachments

    check if we are deleting the only existent version, in this case
    we need to delete the requirement.

    args: id: can be one id, or an array of id

    returns:


  */
  function delete($id,$version_id = self::ALL_VERSIONS,$user_id=null) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $children = null;

    $where = array('coverage' => '','this' => '', 'iam_parent' => '');
    $deleteAll = false;
    $result = null;
    $doIt = true;
    $kaboom = false;

    if(is_array($id)) {    
      $id_list = implode(',',$id);
      $where['coverage'] = " WHERE req_id IN ({$id_list})";
      $where['this'] = " WHERE id IN ({$id_list})";
      $where['iam_parent'] = " WHERE parent_id IN ({$id_list})";
    }
    else {
      $safeID = intval($id);
      $where['coverage'] = " WHERE req_id = " . $safeID;
      $where['this'] = " WHERE id = " . $safeID;
      $where['iam_parent'] = " WHERE parent_id = " . $safeID;
    }

    $set2del = null;    
    
    // if we are trying to delete ONE SPECIFIC VERSION
    // of ONE REQ, and is the ONLY VERSION on DB
    // then we are going to delete the req.
    $checkNotify = true;
    $action4notify = 'delete';
    if( $version_id != self::ALL_VERSIONS ) {
      // we use version id when working on ONE REQ,
      // then I'm going to trust this.
      // From GUI if only one version exists,
      // the operation available is DELETE REQ,
      // not delete version
      $sql = "SELECT COUNT(0) AS VQTY, parent_id FROM " . 
             " {$this->tables['nodes_hierarchy']} " . 
             $where['iam_parent'] . ' GROUP BY parent_id';

      $rs = $this->db->fetchRowsIntoMap($sql,'parent_id');
      $rs = current($rs);
      if(isset($rs['VQTY']) && $rs['VQTY'] > 1) {
        $action4notify = 'delete_version';
      }  
    } 
    
    if( $checkNotify && $this->notifyOn[__FUNCTION__] ) {
      // Need to save data before delete
      $set2del = $this->getByIDBulkLatestVersionRevision($id);
      if( !is_null($set2del) ) {
        foreach($set2del as $rk => $r2d) {
          $this->notifyMonitors($rk,$action4notify,$user_id);
          if($action4notify == 'delete') {
            $this->monitorOff($rk);  
          }  
        }  
      }  
    }  

    // When deleting only one version, we need to check 
    // if we need to delete  requirement also.
    $children[] = $version_id;
    if( $version_id == self::ALL_VERSIONS) {
      $deleteAll = true;
    
      // I'm trying to speedup the next deletes
      $sql = "/* $debugMsg */ " .
             "SELECT NH.id FROM {$this->tables['nodes_hierarchy']} NH " .
             "WHERE NH.parent_id ";
      
      if( is_array($id) ) {
        $sql .=  " IN (" .implode(',',$id) . ") ";
      }
      else {
        $sql .= "  = {$id} ";
      }
  
      $sql .= " AND node_type_id=" . $this->node_types_descr_id['requirement_version'];
      
      $children_rs=$this->db->fetchRowsIntoMap($sql,'id');
      $children = array_keys($children_rs); 

      // delete dependencies with test specification
      $sql = "DELETE FROM {$this->tables['req_coverage']} " . 
             $where['coverage'];
      $result = $this->db->exec_query($sql);

      // also delete relations to other requirements
      // Issue due to FK
      // 
      if ($result) {
        $this->delete_all_relations($id);
      }

      if ($result) {
        // Need to get all versions for all requirements
        $doIt = true;
        $reqIDSet = (array)$id;
        $reqVerSet = $this->getAllReqVersionIDForReq($reqIDSet);

        foreach($reqVerSet as $reqID2Del => $reqVerElem) {
          foreach($reqVerElem as $ydx => $reqVID2Del) {
            $result = $this->attachmentRepository->deleteAttachmentsFor(
            $reqVID2Del,$this->attachmentTableName);
          }
        }
      }
    }        

    // Delete version info
    $target = null;
    if( $doIt ) {
      // As usual working with MySQL makes easier to be lazy and forget that
      // agregate functions need GROUP BY 
      // How many versions are there? 
      // we will delete req also for all with COUNT(0) == 1
      $sql = "SELECT COUNT(0) AS VQTY, parent_id " . 
             " FROM {$this->tables['nodes_hierarchy']} " . 
             $where['iam_parent'] . ' GROUP BY parent_id';

      $rs = $this->db->fetchRowsIntoMap($sql,'parent_id');
      foreach($rs as $el) {
        if(isset($el['VQTY']) && $el['VQTY'] == 1) {
          $target[] = $el['parent_id'];
        }  
      }  

      if( ($kaboom = !is_null($target)) ) {
        $where['this'] = " WHERE id IN (" . implode(',',$target) . ")";
      }  

      // Attachments are related to VERSION
      foreach($children as $key => $reqVID) {
        $result = $this->attachmentRepository->deleteAttachmentsFor($reqVID,$this->attachmentTableName);
      }


      // Going to work on REVISIONS
      $implosion = implode(',',$children);
      $sql = "/* $debugMsg */ " . 
             " SELECT id from {$this->tables['nodes_hierarchy']} " .
             " WHERE parent_id IN ( {$implosion} ) " .
             " AND node_type_id=" .
             $this->node_types_descr_id['requirement_revision'];
             
      $revisionSet = $this->db->fetchRowsIntoMap($sql,'id');
      if( !is_null($revisionSet) ) {
        $this->cfield_mgr->remove_all_design_values_from_node(array_keys($revisionSet));

        $sql = "/* $debugMsg */ DELETE FROM {$this->tables['req_revisions']}
                                WHERE parent_id IN ( {$implosion} ) ";
        $result = $this->db->exec_query($sql);
              
        $sql = "/* $debugMsg */ 
               DELETE FROM {$this->tables['nodes_hierarchy']} 
               WHERE parent_id IN ( {$implosion} ) 
               AND node_type_id=" . $this->node_types_descr_id['requirement_revision'];
        $result = $this->db->exec_query($sql);
      }
      $this->cfield_mgr->remove_all_design_values_from_node((array)$children);

      $where['children'] = " WHERE id IN ( {$implosion} ) ";

      $sql = "DELETE FROM {$this->tables['req_versions']} " . $where['children'];
      $result = $this->db->exec_query($sql);
          
      $sql = "DELETE FROM {$this->tables['nodes_hierarchy']} " . 
             $where['children'] .
             " AND node_type_id=" . $this->node_types_descr_id['requirement_version'];
      $result = $this->db->exec_query($sql);
    } 

    $kaboom = $kaboom || ($deleteAll && $result);
    if( $kaboom ) {
      $sql = "DELETE FROM {$this->object_table} " . $where['this'];
      $result = $this->db->exec_query($sql);

      $sql = "DELETE FROM {$this->tables['nodes_hierarchy']} " . $where['this'] .
             " AND node_type_id=" . $this->node_types_descr_id['requirement'];

      $result = $this->db->exec_query($sql);
    }
  
    $result = (!$result) ? lang_get('error_deleting_req') : 'ok';
    return $result;
  }




/** collect coverage of Requirement
 * @param string $req_id ID of req.
 * @return assoc_array list of test cases [id, title]
 *
 * Notice regarding platforms:
 * When doing Requirements Based Reports, we analize report situation
 * on a Context composed by:
 *                Test project AND Test plan.
 *
 * We do this because we want to have a dynamic view (i.e. want to add exec info).
 *
 * When a Test plan has platforms defined, user get at GUI possibility to choose
 * one platform.
 * IMHO (franciscom) this has to change how coverage (dynamic) is computed.
 *
 * Static coverage:
 *          depicts relation bewteen Req and test cases spec, and platforms are not considered
 *
 * DYNAMIC coverage: 
 *          depicts relation bewteen Req and test cases spec and exec status of these test case, 
 *          and platforms have to be considered
 *
 */
function get_coverage($id,$context=null,$options=null)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
  $my = array('options' => array('accessKey' => 'idx'));
  $my['options'] = array_merge($my['options'], (array)$options);


  $safe_id = intval($id);
  $common = array();
  
  $common['join'] = " FROM {$this->tables['nodes_hierarchy']} NH_TC " .
                    " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.parent_id=NH_TC.id " .
                    " JOIN {$this->tables['tcversions']} TCV ON TCV.id=NH_TCV.id " .
                    " JOIN {$this->tables['req_coverage']} RC ON RC.testcase_id = NH_TC.id ";
  $common['where'] = " WHERE RC.req_id={$safe_id} ";

  if(is_null($context))
  {
    $sql = "/* $debugMsg - Static Coverage */ " . 
           " SELECT DISTINCT NH_TC.id,NH_TC.name,TCV.tc_external_id,U.login,RC.creation_ts" .
           $common['join'] . 
           " LEFT OUTER JOIN {$this->tables['users']} U ON U.id = RC.author_id " .
           $common['where'];
  }
  else
  {
    
    $sql = "/* $debugMsg - Dynamic Coverage */ " . 
           " SELECT DISTINCT NH_TC.id,NH_TC.name,TCV.tc_external_id" .
           $common['join'] .  
           " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NH_TCV.id " .
           $common['where'] .  
           " AND TPTCV.testplan_id = " . intval($context['tplan_id']) .
           " AND TPTCV.platform_id = " . intval($context['platform_id']);
  }
  $sql .=  " ORDER BY tc_external_id ";


  switch($my['options']['accessKey'])
  {
    case 'tcase_id':
      $rs = $this->db->fetchRowsIntoMap($sql,'id');
    break;

    case 'idx':
    default:
      $rs = $this->db->get_recordset($sql);
    break;
  }
  return $rs;
}


  /*
    function: check_basic_data
              do checks on title and reqdoc id, for a requirement

        Checks:
        empty title
        empty docid
        docid already exists inside test project (DOCID is Test Project WIDE)
        title alreday exists under same REQ SPEC (req. parent)
          

    args: srs_id: req spec id (req parent)
          title
          reqdoc_id
          [id]: default null


    returns: map
             keys: status_ok
                   msg
                   failure_reason

   @internal revision
   20110206 - franciscom - add new key on retval 'failure_reason'
   20110108 - franciscom - check on duplicate title under same parent
  */
  function check_basic_data($srs_id,$tproject_id,$title,$reqdoc_id,$id = null)
  {

    $ret['status_ok'] = 1;
    $ret['msg'] = '';
    $ret['failure_reason'] = '';

  $title = trim($title);
  $reqdoc_id = trim($reqdoc_id);
  
    if ($title == "")
    {
      $ret['status_ok'] = 0;
      $ret['msg'] = lang_get("warning_empty_req_title");
      $ret['failure_reason'] = 'empty_req_title';
    }

    if ($reqdoc_id == "")
    {
      $ret['status_ok'] = 0;
      $ret['msg'] .=  " " . lang_get("warning_empty_reqdoc_id");
      $ret['failure_reason'] = 'empty_reqdoc_id';
    }

    if($ret['status_ok'])
    {
      $ret['msg'] = 'ok';
      $rs = $this->getByDocID($reqdoc_id,$tproject_id);
     if(!is_null($rs) && (is_null($id) || !isset($rs[$id])))
      {
        $ret['msg'] = sprintf(lang_get("warning_duplicate_reqdoc_id"),$reqdoc_id);
        $ret['status_ok'] = 0;
        $ret['failure_reason'] = 'duplicate_reqdoc_id';
      }
    }
    
    // check for duplicate title
    // BUGID 4150
    if($ret['status_ok'])
    {
      $ret['msg'] = 'ok';
      $target = array('key' => 'title', 'value' => $title);
      $getOptions = array('output' => 'id');
    $rs = $this->getByAttribute($target,$tproject_id,$srs_id,$getOptions);
     if(!is_null($rs) && (is_null($id) || !isset($rs[$id])))
      {
        $ret['failure_reason'] = 'sibling_req_with_same_title';
        $ret['msg'] = sprintf(lang_get("warning_sibling_req_with_same_title"),$title);
        $ret['status_ok'] = 0;
      }
    }

    return $ret;
  }


  /*
    function: create_tc_from_requirement
              create testcases using requirements as input


    args:

    returns:

  */
function create_tc_from_requirement($mixIdReq,$srs_id, $user_id, $tproject_id = null, $tc_count=null)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  $tcase_mgr = new testcase($this->db);
  $tsuite_mgr = new testsuite($this->db);

  $auto_testsuite_name = $this->reqCfg->default_testsuite_name;
  $node_descr_type = $this->tree_mgr->get_available_node_types();
  $empty_steps = null;
  $empty_preconditions = ''; // fix for BUGID 2995
    
  $labels['tc_created'] = lang_get('tc_created');

  $output = null;
  $reqSet = is_array($mixIdReq) ? $mixIdReq : array($mixIdReq);
    
  if( is_null($tproject_id) || $tproject_id == 0 ) {
    $tproject_id = $this->tree_mgr->getTreeRoot($srs_id);
  }
    
  if ( $this->reqCfg->use_req_spec_as_testsuite_name ) {
      $full_path = $this->tree_mgr->get_path($srs_id);
      $addition = " (" . lang_get("testsuite_title_addition") . ")";
      $truncate_limit = $this->fieldSize->testsuite_name - strlen($addition);

      // REQ_SPEC_A
      //           |-- REQ_SPEC_A1 
      //                          |-- REQ_SPEC_A2
      //                                         |- REQ100
      //                                         |- REQ101
      //
      // We will try to check if a test suite has already been created for
      // top REQ_SPEC_A  (we do search using automatic generated name as search criteria).
      // If not => we need to create all path till leaves (REQ100 and REQ200)
      //
      //
      // First search: we use test project
      $parent_id = $tproject_id;
      $deep_create = false;
      foreach($full_path as $key => $node) {
        // follow hierarchy of test suites to create
        $tsuiteInfo = null;

        // deal with UTF-8
        // $testsuite_name = substr($node['name'],0,$truncate_limit). $addition;
        $testsuite_name = mb_substr($node['name'],0,$truncate_limit,mb_detect_encoding($node['name'])) . $addition;

        if( !$deep_create ) {
          // child test suite with this name, already exists on current parent ?
          // At first a failure we will not check anymore an proceed with deep create
          $sql = "/* $debugMsg */ SELECT id,name FROM {$this->tables['nodes_hierarchy']} NH " .
                 " WHERE name='" . $this->db->prepare_string($testsuite_name) . "' " .
                 " AND node_type_id=" . $node_descr_type['testsuite'] . 
                 " AND parent_id = {$parent_id} ";
              
              // If returns more that one record use ALWAYS first
          $tsuiteInfo = $this->db->fetchRowsIntoMap($sql,'id');
       }
       
       if( is_null($tsuiteInfo) ) {
          $tsuiteInfo = $tsuite_mgr->create($parent_id,$testsuite_name,$this->reqCfg->testsuite_details);
          $output[] = sprintf(lang_get('testsuite_name_created'), $testsuite_name);
          $deep_create = true;
       }
       else {
         $tsuiteInfo = current($tsuiteInfo);
         $tsuite_id = $tsuiteInfo['id'];
       }
       $tsuite_id = $tsuiteInfo['id'];  // last value here will be used as parent for test cases
       $parent_id = $tsuite_id;
      }
      $output[]=sprintf(lang_get('created_on_testsuite'), $testsuite_name);
  } else {
    // don't use req_spec as testsuite name
    // Warning:
    // We are not maintaining hierarchy !!!
    $sql=" SELECT id FROM {$this->tables['nodes_hierarchy']} NH " .
         " WHERE name='" . $this->db->prepare_string($auto_testsuite_name) . "' " .
         " AND parent_id=" . $tproject_id . " " .
         " AND node_type_id=" . $node_descr_type['testsuite'];
  
    $result = $this->db->exec_query($sql);
    if ($this->db->num_rows($result) == 1) {
      $row = $this->db->fetch_array($result);
      $tsuite_id = $row['id'];
      $label = lang_get('created_on_testsuite');
    } else {
      // not found -> create
      tLog('test suite:' . $auto_testsuite_name . ' was not found.');
      $new_tsuite=$tsuite_mgr->create($tproject_id,$auto_testsuite_name,$this->reqCfg->testsuite_details);
      $tsuite_id=$new_tsuite['id'];
      $label = lang_get('testsuite_name_created');
    }
    $output[]=sprintf($label, $auto_testsuite_name);
  }
  /* end contribution */

  // create TC
  $createOptions = array();
  $createOptions['check_names_for_duplicates'] = config_get('check_names_for_duplicates');
  $createOptions['action_on_duplicate_name'] = config_get('action_on_duplicate_name');

  $testcase_importance_default = config_get('testcase_importance_default');

  // compute test case order
  $testcase_order = config_get('treemenu_default_testcase_order');
  $nt2exclude=array('testplan' => 'exclude_me','requirement_spec'=> 'exclude_me','requirement'=> 'exclude_me');

  $siblings = $this->tree_mgr->get_children($tsuite_id,$nt2exclude);
  if( !is_null($siblings) ) {
    $dummy = end($siblings);
    $testcase_order = $dummy['node_order'];
  }
    
  foreach ($reqSet as $reqID) {
    $reqData = $this->get_by_id($reqID,requirement_mgr::LATEST_VERSION);
    $count = (!is_null($tc_count)) ? $tc_count[$reqID] : 1;
    $reqData = $reqData[0];
        
    // Generate name with progessive
    $instance=1;
    $getOptions = array('check_criteria' => 'like','access_key' => 'name');
    $itemSet = $tcase_mgr->getDuplicatesByName($reqData['title'],$tsuite_id,$getOptions);  

    $nameSet = null;
    if( !is_null($itemSet) ){
      $nameSet = array_flip(array_keys($itemSet));
    }
    
    for ($idx = 0; $idx < $count; $idx++) {
      $testcase_order++;
            
      // We have a little problem to work on:
      // suppose you have created:
      // TC [1]
      // TC [2]
      // TC [3]
      // If we delete TC [2]
      // When I got siblings  il will got 2, if I create new progressive using next,
      // it will be 3 => I will get duplicated name.
      //
      // Seems better option can be:
      // Get all siblings names, put on array, create name an check if exists, if true 
      // generate a new name.
      // This may be at performance level is better than create name then check on db,
      // because this approach will need more queries to DB       
      //
      $tcase_name = $reqData['title'] . " [{$instance}]"; 
      if( !is_null($nameSet) ) {
        while( isset($nameSet[$tcase_name]) ) {
          $instance++;
          $tcase_name = $reqData['title'] . " [{$instance}]"; 
        }
      }        
      $nameSet[$tcase_name]=$tcase_name;
         
      $prefix = ($this->reqCfg->use_testcase_summary_prefix_with_title_and_version)
                    ? sprintf($this->reqCfg->testcase_summary_prefix_with_title_and_version, 
                              $reqID, $reqData['version_id'], $reqData['title'], $reqData['version'])
                    : $this->reqCfg->testcase_summary_prefix;
      $content = ($this->reqCfg->copy_req_scope_to_tc_summary) ? $prefix . $reqData['scope'] : $prefix;
            
      $tcase = $tcase_mgr->create($tsuite_id,$tcase_name,$content,
                            $empty_preconditions, $empty_steps,$user_id,null,
                            $testcase_order,testcase::AUTOMATIC_ID,TESTCASE_EXECUTION_TYPE_MANUAL,
                            $testcase_importance_default,$createOptions);
          
      $tcase_name = $tcase['new_name'] == '' ? $tcase_name : $tcase['new_name'];
      $output[] = sprintf($labels['tc_created'], $tcase_name);

      // create coverage dependency
      $rrv = array('id' => $reqData['id'], 'version_id' => $reqData['version_id']);
      $ttcv = array('id' => $tcase['id'], 'version_id' => $tcase['tcversion_id']);

      if (!$this->assignReqVerToTCVer($rrv,$ttcv,$user_id) ) {
        $output[] = 'Test case: ' . $tcase_name . " was not created";
      }
    }
  }
  return $output;
}


  /*
    function: assign_to_tcase
              assign requirement(s) to test case
              Will use always latest ACTIVE Versions 

    args: req_id: can be an array of requirement id
          testcase_id

    returns: 1/0
  */
  function assign_to_tcase($req_id,$testcase_id,$author_id) {

    static $tcMgr;

    if(!$tcMgr) {
      $tcMgr = new testcase($this->db);
    }

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $output = 0;
    $now = $this->db->db_now();
    if($testcase_id && $req_id) {

      // Need to get latest version for each requirement
      $reqIDSet = (array)$req_id;
      $gopt = array('output' => 'id,version');

      $reqLatestVersionIDSet = array();
      $reqLatestVersionNumberSet = array();
      foreach( $reqIDSet as $req ) {
        $isofix = $this->get_last_version_info($req, $gopt);
        $reqLatestVersionIDSet[] = $isofix['id'];
        $reqLatestVersionNumberSet[] = $isofix['version'];
      }

      // Get Latest Active Test Case Version 
      $tcv = current($tcMgr->get_last_active_version($testcase_id));
      $ltcv = $tcv['tcversion_id'];
      $ltcvNum = $tcv['version'];
      $in_clause = implode(",",$reqLatestVersionIDSet);

      //
      $sql = " /* $debugMsg */ " . 
             " SELECT req_id,testcase_id,req_version_id,tcversion_id " . 
             " FROM {$this->tables['req_coverage']} " .
             " WHERE req_version_id IN ({$in_clause}) " . 
             " AND tcversion_id = {$ltcv}";

      $coverage = $this->db->fetchRowsIntoMap($sql,'req_version_id');
              
      // Useful for audit 
      $tcInfo = $this->tree_mgr->get_node_hierarchy_info($testcase_id);

      $loop2do = count($reqLatestVersionIDSet);
      for($idx=0; $idx < $loop2do; $idx++) {
        if( is_null($coverage) || 
            !isset($coverage[$reqLatestVersionIDSet[$idx]]) ) {
          $sql = " INSERT INTO {$this->tables['req_coverage']} " .
                 " (req_id,testcase_id,req_version_id, tcversion_id," .
                 " author_id,creation_ts) " .
                 " VALUES ({$reqIDSet[$idx]},{$testcase_id}," .
                 " $reqLatestVersionIDSet[$idx],{$ltcv},"  . 
                 " {$author_id},{$now})";
          $result = $this->db->exec_query($sql);
          if ($this->db->affected_rows() == 1) {
            $output = 1;

            // For audit 
            $reqInfo = $this->tree_mgr->get_node_hierarchy_info($reqIDSet[$idx]);
            if($tcInfo && $reqInfo) {
              logAuditEvent(TLS("audit_reqv_assigned_tcv",
                            $reqInfo['name'],
                            $reqLatestVersionNumberSet[$idx],
                            $tcInfo['name'],
                            $ltcvNum),
                            "ASSIGN",$this->object_table);
            }                 
          }
        }    
        else
        {
          $output = 1;
        }
      }
    }
    return $output;
  }


  /**
   *
   */ 
  function assignToTCaseUsingLatestVersions($req_id,$testcase_id,$author_id) {
    return $this->assign_to_tcase($req_id,$testcase_id,$author_id);
  }



  /*
    function: get_relationships

    args :

    returns:

  */
  function get_relationships($req_id) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = " /* $debugMsg */ SELECT nodes_hierarchy.id,nodes_hierarchy.name " .
           " FROM {$this->tables['nodes_hierarchy']} nodes_hierarchy, " .
           "      {$this->tables['req_coverage']} req_coverage " .
           " WHERE req_coverage.testcase_id = nodes_hierarchy.id " .
           " AND  req_coverage.req_id={$req_id}";

    return ($this->db->get_recordset($sql));
  }


  /*
    function: get_all_for_tcase
              get all requirements assigned to a test case
              A filter can be applied to do search on all req spec,
              or only on one.


    args: testcase_id
          [srs_id]: default 'all'

    returns:
    
    

  */
function get_all_for_tcase($testcase_id, $srs_id = 'all')
{                         
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $sql = " /* $debugMsg */ SELECT REQ.id,REQ.req_doc_id,NHA.name AS title, " .
           " NHB.name AS req_spec_title,REQ_COVERAGE.testcase_id " .
           " FROM {$this->object_table} REQ, " .
           "      {$this->tables['req_coverage']} REQ_COVERAGE," .
           "      {$this->tables['nodes_hierarchy']} NHA," .
           "      {$this->tables['nodes_hierarchy']} NHB," .
           "      {$this->tables['req_specs']} RSPEC " ;
    
    $idList = implode(",",(array)$testcase_id);
    $sql .= " WHERE REQ_COVERAGE.testcase_id  IN (" . $idList . ")";
  $sql .= " AND REQ.srs_id=RSPEC.id  AND REQ_COVERAGE.req_id=REQ.id " .
          " AND NHA.id=REQ.id AND NHB.id=RSPEC.id " ;

    // if only for one specification is required
    if ($srs_id != 'all') 
    {
      $sql .= " AND REQ.srs_id=" . $srs_id;
    }
  if (is_array($testcase_id))
  {
      return $this->db->fetchRowsIntoMap($sql,'testcase_id',true);
    }
    else
    {
      return $this->db->get_recordset($sql);
    }  
}




  /*
    function:

    args :

    returns:

  */
  function check_title($title)
  {
    $ret = array('status_ok' => 1, 'msg' => 'ok');
  
    if ($title == "")
    {
      $ret['status_ok'] = 0;
        $ret['msg'] = lang_get("warning_empty_req_title");
      }
  
     return $ret;
  }

/*
  function:

  args :
          $nodes: array with req_id in order
  returns:

*/
function set_order($map_id_order)
{
  $this->tree_mgr->change_order_bulk($map_id_order);
}


/**
 * exportReqToXML
 *
 * @param  int $id requirement id
 * @param  int $tproject_id: optional default null.
 *         useful to get custom fields (when this feature will be developed).
 *
 * @return  string with XML code
 *
 */
function exportReqToXML($id,$tproject_id=null, $inc_attachments=false)
{
  $req = $this->get_by_id($id,requirement_mgr::LATEST_VERSION);
  $reqData[] = $req[0]; 

  $elemTpl = "\t" .   "<requirement>" .
             "\n\t\t" . "<docid><![CDATA[||DOCID||]]></docid>" .
             "\n\t\t" . "<title><![CDATA[||TITLE||]]></title>" .
             "\n\t\t" . "<version>||VERSION||</version>" .
             "\n\t\t" . "<revision>||REVISION||</revision>" .
             "\n\t\t" . "<node_order>||NODE_ORDER||</node_order>".
             "\n\t\t" . "<description><![CDATA[||DESCRIPTION||]]></description>".
             "\n\t\t" . "<status><![CDATA[||STATUS||]]></status>" .
             "\n\t\t" . "<type><![CDATA[||TYPE||]]></type>" .
             "\n\t\t" . "<expected_coverage><![CDATA[||EXPECTED_COVERAGE||]]></expected_coverage>" .         
             "\n\t\t" . $this->customFieldValuesAsXML($id,$req[0]['version_id'],$tproject_id);
			 
  // add req attachment content if checked in GUI
  if ($inc_attachments)
  {
	$attachments=null;
	// retrieve all attachments linked to req
	$attachmentInfos = $this->attachmentRepository->getAttachmentInfosFor($id,$this->attachmentTableName,'id');
	  
	// get all attachments content and encode it in base64	  
	if ($attachmentInfos)
	{
		foreach ($attachmentInfos as $attachmentInfo)
		{
			$aID = $attachmentInfo["id"];
			$content = $this->attachmentRepository->getAttachmentContent($aID, $attachmentInfo);
			
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
	  
		if( !is_null($attachments) && count($attachments) > 0 )
		{
			$attchRootElem = "<attachments>\n{{XMLCODE}}\t\t</attachments>\n";
			$attchElemTemplate = "\t\t\t<attachment>\n" .
							   "\t\t\t\t<id><![CDATA[||ATTACHMENT_ID||]]></id>\n" .
							   "\t\t\t\t<name><![CDATA[||ATTACHMENT_NAME||]]></name>\n" .
							   "\t\t\t\t<file_type><![CDATA[||ATTACHMENT_FILE_TYPE||]]></file_type>\n" .
							   "\t\t\t\t<title><![CDATA[||ATTACHMENT_TITLE||]]></title>\n" .
							   "\t\t\t\t<date_added><![CDATA[||ATTACHMENT_DATE_ADDED||]]></date_added>\n" .
							   "\t\t\t\t<content><![CDATA[||ATTACHMENT_CONTENT||]]></content>\n" .
							   "\t\t\t</attachment>\n";

			$attchDecode = array ("||ATTACHMENT_ID||" => "id", "||ATTACHMENT_NAME||" => "name",
								"||ATTACHMENT_FILE_TYPE||" => "file_type", "||ATTACHMENT_TITLE||" => "title",
								"||ATTACHMENT_DATE_ADDED||" => "date_added", "||ATTACHMENT_CONTENT||" => "content");
			$elemTpl .= exportDataToXML($attachments,$attchRootElem,$attchElemTemplate,$attchDecode,true);
        }
    }
  }
  $elemTpl .=  "\n\t" . "</requirement>" . "\n";
             
  $info = array("||DOCID||" => "req_doc_id","||TITLE||" => "title",
                "||DESCRIPTION||" => "scope","||STATUS||" => "status",
                "||TYPE||" => "type","||NODE_ORDER||" => "node_order",
                "||EXPECTED_COVERAGE||" => "expected_coverage",
                "||VERSION||" => "version","||REVISION||" => "revision");
  
  $xmlStr = exportDataToXML($reqData,"{{XMLCODE}}",$elemTpl,$info,true);                
  return $xmlStr;
  
}


/**
 * xmlToMapRequirement
 *
 */
function xmlToMapRequirement($xml_item)
{
  // Attention: following PHP Manual SimpleXML documentation, Please remember to cast
  //            before using data from $xml,
  if( is_null($xml_item) )
  {
    return null;      
  }
        
  $dummy=array();
  foreach($xml_item->attributes() as $key => $value)
  {
     $dummy[$key] = (string)$value;  // See PHP Manual SimpleXML documentation.
  }    
  
  $dummy['node_order'] = (int)$xml_item->node_order;
  $dummy['title'] = (string)$xml_item->title;
  $dummy['docid'] = (string)$xml_item->docid;
  $dummy['description'] = (string)$xml_item->description;
  $dummy['status'] = (string)$xml_item->status;
  $dummy['type'] = (string)$xml_item->type;
  $dummy['expected_coverage'] = (int)$xml_item->expected_coverage;

  if( property_exists($xml_item,'custom_fields') )                
  {
    $dummy['custom_fields']=array();
    foreach($xml_item->custom_fields->children() as $key)
    {
      $dummy['custom_fields'][(string)$key->name]= (string)$key->value;
    }    
  }
  if( property_exists($xml_item,'attachments') )
  {
	$dummy['attachments'] = array();
	foreach($xml_item->attachments->children() as $attachment)
	{
	  $attach_id = (int)$attachment->id;
	  $dummy['attachments'][$attach_id]['id'] = (int)$attachment->id;
	  $dummy['attachments'][$attach_id]['name'] = (string)$attachment->name;
	  $dummy['attachments'][$attach_id]['file_type'] = (string)$attachment->file_type;
	  $dummy['attachments'][$attach_id]['title'] = (string)$attachment->title;
	  $dummy['attachments'][$attach_id]['date_added'] = (string)$attachment->date_added;
	  $dummy['attachments'][$attach_id]['content'] = (string)$attachment->content;  
	}
  }
  return $dummy;
}






/**
 * createFromXML
 *
 * @internal revisions
 */
function createFromXML($xml,$tproject_id,$parent_id,$author_id,$filters = null,$options=null)
{
  $reqAsMap = $this->xmlToMapRequirement($xml);
  
  // Map structure
  // node_order => 0
  // title => Breaks
  // docid => MAZDA3-0001
  // description => Heavy Rain Conditions
  // status => [empty string]
  // type => [empty string]
  // expected_coverage => 0

  return   $this->createFromMap($reqAsMap,$tproject_id,$parent_id,$author_id,$filters,$options);
}


/**
 * createFromMap
 *
 * Map structure
 * node_order => 0
 * title => Breaks
 * docid => MAZDA3-0001
 * description => Heavy Rain Conditions
 * status => [empty string]
 * type => [empty string]
 * expected_coverage => 0
 *
 * @internal revisions
 */
function createFromMap($req,$tproject_id,$parent_id,$author_id,$filters = null,$options=null)
{
  static $missingCfMsg;
  static $linkedCF;
  static $messages;
  static $labels;
  static $fieldSize;
  static $doProcessCF = false;
  static $debugMsg;
  static $getByAttributeOpt;
  static $getLastChildInfoOpt;  // TICKET 5528: Importing a requirement with CF fails after the first time
  
  if(is_null($linkedCF) )
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $fieldSize = config_get('field_size');
    
    $linkedCF = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,cfield_mgr::CF_ENABLED,null,
                                                                'requirement',null,'name');
    $doProcessCF = true;

    $messages = array();
    $messages['cf_warning'] = lang_get('no_cf_defined_can_not_import');
    $messages['cfield'] = lang_get('cf_value_not_imported_missing_cf_on_testproject');

    $labels = array('import_req_created' => '','import_req_skipped' =>'', 'import_req_updated' => '', 
                    'frozen_req_unable_to_import' => '', 'requirement' => '', 'import_req_new_version_created' => '',
                    'import_req_update_last_version_failed' => '',
                    'import_req_new_version_failed' => '', 'import_req_skipped_plain' => '',
                    'req_title_lenght_exceeded' => '', 'req_docid_lenght_exceeded' => '');
    foreach($labels as $key => $dummy)
    {
      $labels[$key] = lang_get($key);
    }  
    $getByAttributeOpt = array('output' => 'id');
    $getLastChildInfoOpt = array('child_type' => 'version', 'output' => ' CHILD.is_open, CHILD.id ');
  }  
  
  $cf2insert=null;
  $status_ok = true;
  $user_feedback = null;
  $dummy = '';                
  $result = null;

  $newReq = null;
  $copy_req = null;
  $getOptions = array('output' => 'minimun');
  $has_filters = !is_null($filters);
  $my['options'] = array( 'hitCriteria' => 'docid' , 'actionOnHit' => "update", 'skipFrozenReq' => true);
  $my['options'] = array_merge($my['options'], (array)$options);

  // 20160314
  // Check data than can create issue when writting to DB due to lenght
  // 
  $req['title'] = trim($req['title']);
  $req['docid'] = trim($req['docid']);

  $checkLengthOK = true;
  $what2add = '';
  if( strlen($req['title']) > $fieldSize->req_title )
  {
     $checkLengthOK = false;
     $what2add = $labels['req_title_lenght_exceeded'] . '/';
  }  

  if( strlen($req['docid']) > $fieldSize->req_docid )
  {
     $checkLengthOK = false;
     $what2add .= $labels['req_docid_lenght_exceeded'];
  }  

  if( $checkLengthOK == FALSE )
  {
    $msgID = 'import_req_skipped_plain';
    $user_feedback[] = array('doc_id' => $req['docid'],'title' => $req['title'], 
                             'import_status' => sprintf($labels[$msgID],$what2add));

    return $user_feedback;
  }  

  // Check:
  // If item with SAME DOCID exists inside container
  // If there is a hit
  //     We will follow user option: update,create new version
  //
  // If do not exist check must be repeated, but on WHOLE test project
  //   If there is a hit -> we can not create
  //    else => create
  // 
  // $getOptions = array('output' => 'minimun');
  $msgID = 'import_req_skipped';

  $target = array('key' => $my['options']['hitCriteria'], 'value' => $req[$my['options']['hitCriteria']]);

  // IMPORTANT NOTICE
  // When get is done by attribute that can not be unique (like seems to happen now 20110108 with
  // title), we can get more than one hit and then on this context we can not continue
  // with requested operation
  $check_in_reqspec = $this->getByAttribute($target,$tproject_id,$parent_id,$getByAttributeOpt);

  // while working on BUGID 4210, new details came to light.
  // In addition to hit criteria there are also the criteria that we use 
  // when creating/update item using GUI, and these criteria have to be
  // checked abd fullfilled.
  //
  if(is_null($check_in_reqspec))
  {
    $check_in_tproject = $this->getByAttribute($target,$tproject_id,null,$getByAttributeOpt);

    if(is_null($check_in_tproject))
    {
	  $importMode = 'creation';
      $newReq = $this->create($parent_id,$req['docid'],$req['title'],$req['description'],
                         $author_id,$req['status'],$req['type'],$req['expected_coverage'],
                         $req['node_order'],$tproject_id,array('quickAndDirty' => true));
	  $reqID = $newReq['id'];
      if( ($status_ok = ($newReq['status_ok'] == 1)) )
      {
        $msgID = 'import_req_created';
      }
      else
      {
        $msgID = 'import_req_skipped_plain';
        $result['msg'] = $newReq['msg'];  // done to use what2add logic far below
      }
        
    }                  
    else
    {
      // Can not have req with same req doc id on another branch => BLOCK
      // What to do if is Frozen ??? -> now ignore and update anyway
      $msgID = 'import_req_skipped';
      $status_ok = false;
    }                    
  }
  else
  {
    // IMPORTANT NOTICE
    // When you
    // Need to get Last Version no matter active or not.
    $reqID = key($check_in_reqspec);
    $last_version = $this->get_last_child_info($reqID,$getLastChildInfoOpt);
    $msgID = 'frozen_req_unable_to_import';
    $status_ok = false;

    if( $last_version['is_open'] == 1 || !$my['options']['skipFrozenReq'])
    {
      switch($my['options']['actionOnHit'])
      {
        case 'update_last_version':
		  $importMode = 'update';
          $result = $this->update($reqID,$last_version['id'],$req['docid'],$req['title'],$req['description'],
                                  $author_id,$req['status'],$req['type'],$req['expected_coverage'],
                                  $req['node_order']);
          
          $status_ok = ($result['status_ok'] == 1);
          if( $status_ok) {
            $msgID = 'import_req_updated';
          }
          else {
            $msgID = 'import_req_update_last_version_failed';
          }  
        break;
      
        case 'create_new_version':
          $newItem = $this->create_new_version($reqID,$author_id,array('notify' => true));
                
          // Set always new version to NOT Frozen
          $this->updateOpen($newItem['id'],1);        
          
          // hmm wrong implementation
          // Need to update ALL fields on new version then why do not use
          // update() ?
          $newReq['version_id'] = $newItem['id']; 
          
          // IMPORTANT NOTICE:
          // We have to DO NOT UPDATE REQDOCID with info received from USER
          // Because ALL VERSION HAS TO HAVE docid, or we need to improve our checks
          // and if update fails => we need to delete new created version.
          $title = trim_and_limit($req['title'],$fieldSize->req_title);
		      $importMode = 'update';
          $result = $this->update($reqID,$newItem['id'],$req['docid'],$title,$req['description'],
                                  $author_id,$req['status'],$req['type'],$req['expected_coverage'],
                                  $req['node_order']);
          
          $status_ok = ($result['status_ok'] == 1);
          if( $status_ok)
          {
            $msgID = 'import_req_new_version_created';
          }
          else
          {
            // failed -> removed just created version
            $this->delete($reqID,$newItem['id']);  
            $msgID = 'import_req_new_version_failed';
          }  
        break;  
    }
    }    
  }     
  $what2add = is_null($result) ? $req['docid'] : $req['docid'] . ':' . $result['msg'];
  
  $user_feedback[] = array('doc_id' => $req['docid'],'title' => $req['title'], 
                           'import_status' => sprintf($labels[$msgID],$what2add));

  $hasAttachments = array_key_exists('attachments',$req);
  // process attachements for creation and update
  if($status_ok && $hasAttachments)
  {
	$addAttachmentsResponse = $this->processAttachments( $importMode, $reqID, $req['attachments'], $feedbackMsg );
  }
  // display only problems during attachments import
  if( isset($addAttachmentsResponse) && !is_null($addAttachmentsResponse) )
  {
	foreach($addAttachmentsResponse as $att_name){
	  $user_feedback[] = array('doc_id' => $req['docid'],'title' => $req['title'],
							'import_status' => sprintf(lang_get('import_req_attachment_skipped'),$att_name));
	}
  }
  if( $status_ok && $doProcessCF && isset($req['custom_fields']) && !is_null($req['custom_fields']) )
  {
    $req_version_id = !is_null($newReq) ? $newReq['version_id'] : $last_version['id'];
    $cf2insert = null;
    foreach($req['custom_fields'] as $cfname => $cfvalue)
    {
      $cfname = trim($cfname);
      if( isset($linkedCF[$cfname]) )
      {
          $cf2insert[$linkedCF[$cfname]['id']]=array('type_id' => $linkedCF[$cfname]['type'],
                                                       'cf_value' => $cfvalue);         
      }
      else
      {
        if( !isset($missingCfMsg[$cfname]) )
        {
            $missingCfMsg[$cfname] = sprintf($messages['cfield'],$cfname,$labels['requirement']);
        }
        $user_feedback[] = array('doc_id' => $req['docid'],'title' => $req['title'], 
                                 'import_status' => $missingCfMsg[$cfname]);
      }
    }  
   if( !is_null($cf2insert) )
   {
      $this->cfield_mgr->design_values_to_db($cf2insert,$req_version_id,null,'simple');
    }  
  }

  return $user_feedback;
}
  
/**
	 * processAttachments
	 *
	 * Analyze attachments info related to requirement to define if the the attachment has to be added.
	 * attachments are ignored only if a attachment with the same ID is already linked to the target requirement.
	 * 
	 * return an array of all attachments names of IDs already linked to target requirement (to display warning messages).
	 * 
	 */

	function processAttachments($importMode, $srs_id, $attachments, $feedbackMsg )
	{
		$tables = tlObjectWithDB::getDBTables(array('requirements','attachments'));

		$knownAttachments = array();
		foreach( $attachments as $attachment )
		{
			$addAttachment = true;
			if($importMode == 'update'){
				// try to bypass the importation of already known attachments.
				// Check in database if the attachment with the same ID is linked to the rspec with the same internal ID
				// The couple attachment ID + InternalID is used as a kind of signature to avoid duplicates. 
				// If signature is not precise enough, could add the use of attachment timestamp (date_added in XML file).
				$sql = " SELECT ATT.id from {$tables['attachments']} ATT " .
					" WHERE ATT.id='{$this->db->prepare_string($attachment[id])}' " .
					" AND ATT.fk_id={$srs_id} ";
				$rsx=$this->db->get_recordset($sql);
				$addAttachment = ( is_null($rsx) || count($rsx) < 1 );
				if( $addAttachment === false ){ // inform user that the attachment has been skipped
					$knownAttachments[] = $attachment['name'];
				}
			}
			if($addAttachment){
				$attachRepo = tlAttachmentRepository::create($this->db);				
				$fileInfo = $attachRepo->createAttachmentTempFile( $attachment['content'] );	
				$fileInfo['name'] = $attachment['name'];
				$fileInfo['type'] = $attachment['file_type'];
				$attachRepo->insertAttachment( $srs_id, $tables['requirements'], $attachment['title'], $fileInfo);
			}
		}
		return $knownAttachments;
	}
  
// ---------------------------------------------------------------------------------------
// Custom field related functions
// ---------------------------------------------------------------------------------------

/*
  function: get_linked_cfields
            Get all linked custom fields.
            Remember that custom fields are defined at system wide level, and
            has to be linked to a testproject, in order to be used.


  args: id: requirement id
    $child_id: requirement version id or requirement revision id
        [parent_id]:
                     this information is vital, to get the linked custom fields.
                     null -> use requirement_id as starting point.
                     !is_null -> use this value as testproject id

  returns: map/hash
           key: custom field id
           value: map with custom field definition and value assigned for choosen requirement,
                  with following keys:

                  id: custom field id
                  name
                  label
                  type: custom field type
                  possible_values: for custom field
                  default_value
                  valid_regexp
                  length_min
                  length_max
                  show_on_design
                  enable_on_design
                  show_on_execution
                  enable_on_execution
                  display_order
                  value: value assigned to custom field for this requirement
                         null if for this requirement custom field was never edited.

                  node_id: requirement id
                           null if for this requirement, custom field was never edited.

  


  20111110 - franciscom - TICKET 4802: Exporting large amount of requirements ( qty > 1900) fails
*/
function get_linked_cfields($id,$child_id,$parent_id=null)
{
  if( !is_null($parent_id) )
  {
      $tproject_id = $parent_id;
  }
  else
  {
      $req_info = $this->get_by_id($id);
      $tproject_id = $req_info[0]['testproject_id'];
      unset($req_info);
  }

  $cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,cfield_mgr::ENABLED,null,
                                                            'requirement',$child_id);
  return $cf_map;
}


/*
  function: html_table_of_custom_field_inputs
            Return html code, implementing a table with custom fields labels
            and html inputs, for choosen requirement.
            Used to manage user actions on custom fields values.


  args: $id
      $version_id  --- BUGID   - NEEDS CHANGES
        [parent_id]: need to undertad to which testproject the requirement belongs.
                     this information is vital, to get the linked custom fields.
                     null -> use requirement_id as starting point.
                     !is_null -> use this value as starting point.


        [$name_suffix]: must start with '_' (underscore).
                        Used when we display in a page several items
                        (example during test case execution, several test cases)
                        that have the same custom fields.
                        In this kind of situation we can use the item id as name suffix.


  returns: html string


  @internal revisions
*/
function html_table_of_custom_field_inputs($id,$version_id,$parent_id=null,$name_suffix='', $input_values = null)
{
  $cf_map = $this->get_linked_cfields($id,$version_id,$parent_id,$name_suffix);
  $cf_smarty = $this->cfield_mgr->html_table_inputs($cf_map,$name_suffix,$input_values);
  return $cf_smarty;
}


/*
  function: html_table_of_custom_field_values
            Return html code, implementing a table with custom fields labels
            and custom fields values, for choosen requirement.
            You can think of this function as some sort of read only version
            of html_table_of_custom_field_inputs.


  args: $id
    $child_id: req version or req revision ID

  returns: html string

  revision:
  BUGID 4056

*/
function html_table_of_custom_field_values($id,$child_id,$tproject_id=null)
{
    $NO_WARNING_IF_MISSING=true;
  $cf_smarty = '';

  $root_id = is_null($id) ? $tproject_id : null;  
  $cf_map = $this->get_linked_cfields($id,$child_id,$root_id);

  // BUGID 3989
  $show_cf = config_get('custom_fields')->show_custom_fields_without_value;
  
  if(!is_null($cf_map))
  {
    foreach($cf_map as $cf_id => $cf_info)
    {
      // if user has assigned a value, then node_id is not null
      // BUGID 3989
      if($cf_info['node_id'] || $show_cf)
      {
        $label = str_replace(TL_LOCALIZE_TAG,'',
                                   lang_get($cf_info['label'],null,$NO_WARNING_IF_MISSING));

        $cf_smarty .= '<tr><td class="labelHolder">' .
                htmlspecialchars($label) . ":</td><td>" .
                $this->cfield_mgr->string_custom_field_value($cf_info,$child_id) .
                "</td></tr>\n";
      }
    }

    if(trim($cf_smarty) != "")
    {
      $cf_smarty = "<table>" . $cf_smarty . "</table>";
    }
  }
  return $cf_smarty;
} // function end


  /*
    function: values_to_db
              write values of custom fields.

    args: $hash:
          key: custom_field_<field_type_id>_<cfield_id>.
               Example custom_field_0_67 -> 0=> string field

          $node_id:

          [$cf_map]:  hash -> all the custom fields linked and enabled
                              that are applicable to the node type of $node_id.

                              For the keys not present in $hash, we will write
                              an appropriate value according to custom field
                              type.

                              This is needed because when trying to udpate
                              with hash being $_REQUEST, $_POST or $_GET
                              some kind of custom fields (checkbox, list, multiple list)
                              when has been deselected by user.


    rev:
  */
  function values_to_db($hash,$node_id,$cf_map=null,$hash_type=null)
  {
    $this->cfield_mgr->design_values_to_db($hash,$node_id,$cf_map,$hash_type);
  }


 /**
  * customFieldValuesAsXML
  *
  * @param $id: requirement spec id
  * @param $tproject_id: test project id
  *
  *
  */
 function customFieldValuesAsXML($id,$version_id,$tproject_id)
 {
    $xml = null;
    $cfMap=$this->get_linked_cfields($id,$version_id,$tproject_id);
  if( !is_null($cfMap) && count($cfMap) > 0 )
  {
        $xml = $this->cfield_mgr->exportValueAsXML($cfMap);
    }
    return $xml;
 }





  /*
   function: getByDocID
   get req information using document ID as access key.

   args : doc_id:
   [tproject_id]
   [parent_id] -> req spec parent of requirement searched
   default 0 -> case sensivite search

   returns: map.
   key: req spec id
   value: req info, map with following keys:
   id
   doc_id
   testproject_id
   title
   scope
  */
  function getByDocID($doc_id,$tproject_id=null,$parent_id=null, $options = null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['options'] = array('check_criteria' => '=', 'access_key' => 'id', 
                           'case' => 'sensitive', 'output' => 'standard');
    $my['options'] = array_merge($my['options'], (array)$options);
      
         
    $output=null;
    $the_doc_id = $this->db->prepare_string(trim($doc_id));
    switch($my['options']['check_criteria'])
    {
      case '=':
      default:
        $check_criteria = " = '{$the_doc_id}' ";
      break;
        
      case 'like':
        $check_criteria = " LIKE '{$the_doc_id}%' ";
      break;
    }
      
    $sql = " /* $debugMsg */ SELECT ";
    switch($my['options']['output'])
    {
      case 'standard':
         $sql .= " REQ.id,REQ.srs_id,REQ.req_doc_id,NH_REQ.name AS title, REQ_SPEC.testproject_id, " .
                 " NH_RSPEC.name AS req_spec_title, REQ_SPEC.doc_id AS req_spec_doc_id, NH_REQ.node_order ";
      break;
                  
      case 'minimun':
         $sql .= " REQ.id,REQ.srs_id,REQ.req_doc_id,NH_REQ.name AS title, REQ_SPEC.testproject_id";
      break;
    }

    $sql .= " FROM {$this->object_table} REQ " .
            " /* Get Req info from NH */ " .
            " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = REQ.id " .
            " JOIN {$this->tables['req_specs']} REQ_SPEC ON REQ_SPEC.id = REQ.srs_id " .
            " JOIN {$this->tables['nodes_hierarchy']} NH_RSPEC ON NH_RSPEC.id = REQ_SPEC.id " .
            " WHERE REQ.req_doc_id {$check_criteria} ";
      
    if( !is_null($tproject_id) )
    {
      $sql .= " AND REQ_SPEC.testproject_id={$tproject_id}";
    }
      
    if( !is_null($parent_id) )
    {
      $sql .= " AND REQ.srs_id={$parent_id}";
    }

    $out = $this->db->fetchRowsIntoMap($sql,$my['options']['access_key']);
      
    return $out;
  }

  /**
   * Copy a requirement to a new requirement specification
   * requirement DOC ID will be changed because must be unique inside
   * MASTER CONTAINER (test project)
   * 
   * @param integer $id: requirement ID
   * @param integer $parent_id: target req spec id (where we want to copy)
   * @param integer $user_id: who is requesting copy
   * @param integer $tproject_id: FOR SOURCE ($id), optional, 
   *                              is null will be computed here
   * @param array $options: map
   *
   */
  function copy_to($id,$parent_id,$user_id,$tproject_id=null,$options=null) {
    $new_item = array('id' => -1, 'status_ok' => 0, 'msg' => 'ok', 'mappings' => null);
    $my['options'] = array('copy_also' => null, 'caller' => '');
    $my['options'] = array_merge($my['options'], (array)$options);
    
    if( is_null($my['options']['copy_also']) ) {
      $my['options']['copy_also'] = array('testcase_assignment' => true);   
    }

    $copyReqVTCVLinks = isset($my['options']['copy_also']['testcase_assignment']) &&
                        $my['options']['copy_also']['testcase_assignment'];

    $root = $tproject_id;
    if( is_null($root) ) {
      $reqSpecMgr = new requirement_spec_mgr($this->db);
      $target = $reqSpecMgr->get_by_id($parent_id);
      $root = $target['testproject_id'];
    }
  
    // NEED INLINE REFACTORING
    $item_versions = $this->get_by_id($id);
    if($item_versions) {
      if($my['options']['caller'] == 'copy_testproject') { 
        $target_doc = $item_versions[0]['req_doc_id']; 
        $title = $item_versions[0]['title'];
      } else {
        // REQ DOCID is test project wide => can not exist duplicates inside
        // a test project => we need to generate a new one using target as
        // starting point
        $target_doc = $this->generateDocID($id,$root);    

        // If a sibling exists with same title => need to generate automatically
        // a new one.
        $title = $this->generateUniqueTitle($item_versions[0]['title'],
                                            $parent_id,$root);
      }
      
      $new_item = $this->create_req_only($parent_id,$target_doc,$title,
                                         $item_versions[0]['author_id'],
                                         $item_versions[0]['node_order']);
      
      if ($new_item['status_ok']) {
        $ret['status_ok']=1;
        $new_item['mappings']['req'][$id] = $new_item['id'];

        foreach($item_versions as &$req_version) {
          $op = $this->create_version($new_item['id'],$req_version['version'],
                                      $req_version['scope'],$req_version['author_id'],
                                      $req_version['status'],$req_version['type'],
                                      $req_version['expected_coverage']);

          // need to explain how this mappings are used outside this method
          // first thing that can see here, we are mixing req id and 
          // req version id on same hash.
          // 
          $new_item['mappings']['req_version'][$req_version['version_id']] = $op['id'];

          // 2018
          $new_item['mappings']['req_tree'][$id][$req_version['version_id']] = 
            $op['id'];


          // here we have made a mistake, that help to show that 
          // we have some memory issue
          // with copy_cfields().
          // ALWAYS when we have tproject_id we have to use it!!!
          //
          $this->copy_cfields(array('id' => $req_version['id'], 
                                    'version_id' =>  $req_version['version_id']),
                        array('id' => $new_item['id'], 'version_id' => $op['id']),
                        $tproject_id);
          

          $source = $req_version['version_id'];
          $dest = $op['id'];
          $this->attachmentRepository->copyAttachments($source,$dest,
            $this->attachmentTableName);

          // Seems that when we call this function during Test Project Copy
          // we DO NOT USE this piece
          
          if( $copyReqVTCVLinks ) {
            $lnk = $this->getGoodForReqVersion($req_version['version_id']);
            if( !is_null($lnk) ) {
              $reqAndVer = array('id' => $new_item['id'], 
                                 'version_id' => $op['id']);
              foreach($lnk as $links) {
                foreach($links as $value) {
                  $tcAndVer = array('id' => $value['testcase_id'],
                                    'version_id' => $value['tcversion_id']);
                  $this->assignReqVerToTCVer($reqAndVer,$tcAndVer,$user_id);       
                }
              }
            }
          }

          unset($op);
        }
            
      }
    }

    unset($item_versions); // does this help to release memory ?

    return $new_item;
  }


    /**
     * 
     *
     */
  function copy_attachments($source_id,$target_id) {
    $this->attachmentRepository->copyAttachments($source_id,$target_id,$this->attachmentTableName);
  }


  /*
    function: copy_cfields
              Get all cfields linked to any testcase of this testproject
              with the values presents for $from_id, testcase we are using as
              source for our copy.
  
    args: from_id: source item id
          to_id: target item id
  
    returns: -
  
  */
  function copy_cfields($source,$destination,$tproject_id=null)
  {
    $cfmap_from = $this->get_linked_cfields($source['id'],$source['version_id'],$tproject_id);
    $cfield=null;
    if( !is_null($cfmap_from) )
    {
      foreach($cfmap_from as $key => $value)
      {
        $cfield[$key]=array("type_id"  => $value['type'], "cf_value" => $value['value']);
      }
      $this->cfield_mgr->design_values_to_db($cfield,$destination['version_id'],null,'reqversion_copy_cfields');
    }
  }



    /**
   * 
    *
    */
  function generateDocID($id, $tproject_id)
  {
    $item_info = $this->get_by_id($id);
    $item_info = $item_info[0]; 

    // Check if another req with same DOC ID exists on test project (MASTER CONTAINER),
    // If yes generate a new DOC ID
    $getOptions = array('check_criteria' => 'like', 'access_key' => 'req_doc_id');
    $itemSet = $this->getByDocID($item_info['req_doc_id'],$tproject_id,null,$getOptions);
        
    $target_doc = $item_info['req_doc_id'];
    $instance = 1;
    if( !is_null($itemSet) )
    {
      $safety_len = 2; // use t
      $mask = $this->reqCfg->duplicated_docid_algorithm->text;
      
      // req_doc_id has limited size then we need to be sure that generated id will
      // not exceed DB size
          $nameSet = array_flip(array_keys($itemSet));
        $prefix = trim_and_limit($item_info['req_doc_id'],
                     $this->fieldSize->req_docid-strlen($mask)-$safety_len);
                     
          // $target_doc = $prefix . " [{$instance}]"; 
          $target_doc = $prefix . sprintf($mask,$instance);
        while( isset($nameSet[$target_doc]) )
        {
          $instance++;
            $target_doc = $prefix . sprintf($mask,$instance); 
        }
    }
       return $target_doc;
  }

    /**
    * 
     *
     */
  function create_req_only($srs_id,$reqdoc_id,$title,$user_id,$node_order=0)
  {
    static $debugMsg;
    
    if(!$debugMsg)
    {
      $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    }
    
    $req_id = $this->tree_mgr->new_node($srs_id,$this->node_types_descr_id['requirement'],
                                        $title,$node_order);
    $sql = "/* $debugMsg */ INSERT INTO {$this->object_table} " .
           " (id, srs_id, req_doc_id)" .
             " VALUES ({$req_id}, {$srs_id},'" . $this->db->prepare_string($reqdoc_id) . "')";
           
    if (!$this->db->exec_query($sql))
    {
      $result = array( 'id' => -1, 'status_ok' => 0);
      $result['msg'] = lang_get('error_inserting_req');
    }
    else
    {
      $result = array( 'id' => $req_id, 'status_ok' => 1, 'msg' => 'ok');
    }
    
    unset($sql);
    unset($req_id);
    
    return $result;
  }

  /*
    function: create_version
  
    args:
  
    returns:
  
  
  */
  function create_version($id,$version,$scope, $user_id, $status = TL_REQ_STATUS_VALID, 
                          $type = TL_REQ_TYPE_INFO, $expected_coverage=1)
  {
    static $debugMsg;
    
    if(!$debugMsg)
    { 
      $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    }
    
    $item_id = $this->tree_mgr->new_node($id,$this->node_types_descr_id['requirement_version']);
      
    $sql = "/* $debugMsg */ INSERT INTO {$this->tables['req_versions']} " .
           " (id,version,scope,status,type,expected_coverage,author_id,creation_ts) " . 
           " VALUES({$item_id},{$version},'" . trim($this->db->prepare_string($scope)) . "','" . 
           $this->db->prepare_string($status) . "','" . $this->db->prepare_string($type) . "'," .
           "{$expected_coverage}," . intval($user_id) . "," . $this->db->db_now() . ")";
             
    $result = $this->db->exec_query($sql);
    $ret = array( 'msg' => 'ok', 'id' => $item_id, 'status_ok' => 1);
    if (!$result)
    {
      $ret['msg'] = $this->db->error_msg();
      $ret['status_ok']=0;
      $ret['id']=-1;
    }
    unset($sql);
    unset($result);
    unset($item_id);
    return $ret;
  }
  
  /*
    function: create_new_version()
              create a new version, doing BY DEFAULT a copy of last version.
              If reqVersionID is passed, then this version will be used as source data.
  
    args : $id: requirement id
           $user_id: who is doing this operation.
           $reqVersionID = default null => use last version as source 
  
    returns:
            map:  id: node id of created tcversion
                  version: version number (i.e. 5)
                  msg
  
  */
  function create_new_version($id,$user_id,$opt=null) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  
    $my['opt'] = array('reqVersionID' => null,'log_msg' => null, 
                       'notify' => false, 
                       'freezeSourceVersion' => true);

    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $reqVersionID = $my['opt']['reqVersionID'];
    $log_msg = $my['opt']['log_msg'];
    $notify = $my['opt']['notify'];
    
    // get a new id
    $version_id = $this->tree_mgr->new_node($id,$this->node_types_descr_id['requirement_version']);
    
    // Needed to get higher version NUMBER, to generata new VERSION NUMBER
    $sourceVersionInfo =  $this->get_last_child_info($id,  array('child_type' => 'version'));
    if( is_null($sourceVersionInfo) ) {
      throw new Exception($debugMsg . ' $this->get_last_child_info() RETURNED NULL !!! - WRONG - Open Issue on mantis.testlink.org');
    }

    // Till now everything is OK
    if( $notify ) {
      // be optimistic send email before doing nothing
      $this->notifyMonitors($id,__FUNCTION__,$user_id,$log_msg);
    } 

    $newVersionNumber = $sourceVersionInfo['version']+1; 

    $ret = array();
    $ret['id'] = $version_id;
    $ret['version'] = $newVersionNumber;
    $ret['msg'] = 'ok';
    
    $sourceVersionID = is_null($reqVersionID) ? $sourceVersionInfo['id'] : $reqVersionID;

    // Update Link Status To  Test Case Versions for Source Version
    // is done on copy_version()    
    $this->copy_version($id,$sourceVersionID,$version_id,$newVersionNumber,$user_id);
    
    // need to update log message in new created version
    $sql = "/* $debugMsg */ " .
           " UPDATE {$this->tables['req_versions']} " .
           " SET log_message = '" . trim($this->db->prepare_string($log_msg)) . "'" .
           " WHERE id={$version_id}";
    $this->db->exec_query($sql);    
    
    if( $my['opt']['freezeSourceVersion'] ) {
      $this->updateOpen($sourceVersionInfo['id'],0);
    }

    return $ret;
  }


 /**
  * 
  *
  */
  function get_last_version_info($id, $opt=null) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $info = null;
  
    $sql = " /* $debugMsg */ SELECT MAX(version) AS version " .
           " FROM {$this->tables['req_versions']} REQV," .
           " {$this->tables['nodes_hierarchy']} NH WHERE ".
           " NH.id = REQV.id ".
           " AND NH.parent_id = {$id} ";
  
    $max_version = $this->db->fetchFirstRowSingleColumn($sql,'version');
    if ($max_version) {
      $my['opt'] = array('output' => 'default');
      $my['opt'] = array_merge($my['opt'],(array)$opt);

      switch($my['opt']['output']) {
        case 'id':
          $fields = ' REQV.id ';
        break;

        case 'id,version':
          $fields = ' REQV.id, REQV.version ';
        break;

        default:
          $fields = ' REQV.*';
        break;
      }
      $sql = "/* $debugMsg */ SELECT {$fields} " . 
             " FROM {$this->tables['req_versions']} REQV," .
             " {$this->tables['nodes_hierarchy']} NH ".
             " WHERE version = {$max_version} AND NH.id = REQV.id AND NH.parent_id = {$id}";
  
      $info = $this->db->fetchFirstRow($sql);
    }
    return $info;
  }
  
  /**
   * get last defined req doc id for specific test project
   * 
   * @author Julian Krien
   * 
   * @param int $tproj_id test project id
   * 
   * @return string last defned req doc id
   */
  
  function get_last_doc_id_for_testproject($tproj_id)
  {
    $info = null;
    $tproject_mgr = new testproject($this->db);
    $all_reqs = $tproject_mgr->get_all_requirement_ids($tproj_id);
    
    if(count($all_reqs) > 0) 
    {
      //only use maximum value of all reqs array
      $last_req = max($all_reqs);
      $last_req = $this->get_by_id($last_req);
      $info = $last_req[0]['req_doc_id'];
    } 
    return $info;
  }

 /**
  * 
  *
  */
  function copy_version($id,$from_version_id,$to_version_id,$as_version_number,$user_id) {
    
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $now = $this->db->db_now();
    $sql = "/* $debugMsg */ INSERT INTO {$this->tables['req_versions']} " .
           " (id,version,author_id,creation_ts,scope,status,type,expected_coverage) " .
           " SELECT {$to_version_id} AS id, {$as_version_number} AS version, " .
           "        {$user_id} AS author_id, {$now} AS creation_ts," .
           "        scope,status,type,expected_coverage " .
           " FROM {$this->tables['req_versions']} " .
           " WHERE id=" . intval($from_version_id);
    $result = $this->db->exec_query($sql);
           
    $this->copy_cfields(array('id' => $id, 'version_id' => $from_version_id),
                        array('id' => $id, 'version_id' => $to_version_id));
         
 
    $this->copy_attachments($from_version_id,$to_version_id);


    $reqTCLinksCfg = config_get('reqTCLinks');
    $freezeLinkOnNewReqVersion = $reqTCLinksCfg->freezeLinkOnNewReqVersion;
    $freezeLinkedTCases = $freezeLinkOnNewReqVersion &
      $reqTCLinksCfg->freezeBothEndsOnNewREQVersion;

    if( $freezeLinkedTCases ) {
      $this->closeOpenTCVersionOnOpenLinks( $from_version_id );
    }

    $signature = array('user_id' => $user_id, 'when' => $now);

    if( $freezeLinkOnNewReqVersion ) { 
      $this->updateTCVLinkStatus($from_version_id,LINK_TC_REQ_CLOSED_BY_NEW_REQVERSION);      
    }

  }

  /**
   *
   */
  function closeOpenTCVersionOnOpenLinks( $reqVersionID ) {

    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */ ';

    $commonWhere = " WHERE req_version_id = " . intval($reqVersionID) .
                   " AND link_status = " . LINK_TC_REQ_OPEN; 

    $sql = " $debugMsg UPDATE {$this->tables['tcversions']}
             SET is_open = 0
             WHERE id IN (
                 SELECT tcversion_id 
                 FROM {$this->tables['req_coverage']} 
                 $commonWhere
             ) AND is_open = 1";

    $this->db->exec_query($sql);
  }

  /**
   * 
   *
   */
  function updateOpen($reqVersionID,$value) {
    $this->updateBoolean($reqVersionID,'is_open',$value);
  }


  /**
   * 
   *
   */
  function updateActive($reqVersionID,$value) {
    $this->updateBoolean($reqVersionID,'active',$value);
  }  

    /**
   * 
    *
     */
  private function updateBoolean($reqVersionID,$field,$value)
  {
    $booleanValue = $value;
    if( is_bool($booleanValue) ) {
        $booleanValue = $booleanValue ? 1 : 0;
    } else if( !is_numeric($booleanValue) || is_null($booleanValue)) {
      $booleanValue = 1;
    }
    $booleanValue = $booleanValue > 0 ? 1 : 0;
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $sql = "/* $debugMsg */ UPDATE {$this->tables['req_versions']} " .
             " SET {$field}={$booleanValue} WHERE id={$reqVersionID}";
  
    $result = $this->db->exec_query($sql);
     
  }  


  /**
   * get relations for a given requirement ID
   * 
   * @author Andreas Simon
   * 
   * @param int $id Requirement ID
   * 
   * @return array $relations in which this req is either source or destination
   */
  public function get_relations($id) 
  {
    
    $debugMsg = '/* Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . ' */';
    $relations = array();
    $relations['num_relations'] = 0;
    $relations['req'] = current($this->get_by_id($id));
    $relations['relations'] = array();

    $tproject_mgr = new testproject($this->db);

    $sql = " $debugMsg SELECT id, source_id, destination_id, relation_type, author_id, creation_ts " . 
           " FROM {$this->tables['req_relations']} " .
           " WHERE source_id=$id OR destination_id=$id " .
           " ORDER BY id ASC ";
   
    $relations['relations']= $this->db->get_recordset($sql);  
    if( !is_null($relations['relations']) && count($relations['relations']) > 0 )
    {
      $labels = $this->get_all_relation_labels();
      $label_keys = array_keys($labels);
      foreach($relations['relations'] as $key => $rel) 
      {
          
        // is this relation type is configured?
        if( ($relTypeAllowed = in_array($rel['relation_type'],$label_keys)) ) 
        { 
            $relations['relations'][$key]['source_localized'] = $labels[$rel['relation_type']]['source'];
            $relations['relations'][$key]['destination_localized'] = $labels[$rel['relation_type']]['destination'];
            
            if ($id == $rel['source_id']) 
            {
              $type_localized = 'source_localized';
              $other_key = 'destination_id';
            } 
            else 
            {
              $type_localized = 'destination_localized';
              $other_key = 'source_id';
            }
            $relations['relations'][$key]['type_localized'] = $relations['relations'][$key][$type_localized];
            $other_req = $this->get_by_id($rel[$other_key]);
                      
            // only add it, if either interproject linking is on or if it is in the same project
            $relTypeAllowed = false;
            if ($this->relationsCfg->interProjectLinking || ($other_req[0]['testproject_id'] == $relations['req']['testproject_id'])) 
            {
              $relTypeAllowed = true;
              $relations['relations'][$key]['related_req'] = $other_req[0];
              $other_tproject = $tproject_mgr->get_by_id($other_req[0]['testproject_id']);
              $relations['relations'][$key]['related_req']['testproject_name'] = $other_tproject['name'];
              
              $user = tlUser::getByID($this->db,$rel['author_id']);
              $relations['relations'][$key]['author'] = $user->getDisplayName();
            } 
          } 
          
          if( !$relTypeAllowed )
          {
            unset($relations['relations'][$key]);
          }
                 
        } // end foreach
        
        $relations['num_relations'] = count($relations['relations']);
    }
    return $relations;
  }
  
  
  /**
   * checks if there is a relation of a given type between two requirements
   * 
   * @author Andreas Simon
   * 
   * @param integer $first_id requirement ID to check
   * @param integer $second_id another requirement ID to check
   * @param integer $rel_type_id relation type ID to check
   * 
   * @return true, if relation already exists, false if not
   */
  public function check_if_relation_exists($first_id, $second_id, $rel_type_id) {
    
    $debugMsg = '/* Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . ' */';
    $sql = " $debugMsg SELECT COUNT(0) AS qty " .
         " FROM {$this->tables['req_relations']} " .
         " WHERE ((source_id=$first_id AND destination_id=$second_id) " . 
         " OR (source_id=$second_id AND destination_id=$first_id)) " . 
         " AND relation_type=$rel_type_id";
    $rs = $this->db->get_recordset($sql);
      return($rs[0]['qty'] > 0);
  }
  
  
  /**
   * Get count of all relations for a requirement, no matter if it is source or destination
   * or what type of relation it is.
   * 
   * @author Andreas Simon
   * 
   * @param integer $id requirement ID to check
   * 
   * @return integer $count
   */
  public function count_relations($id) 
  {
    $debugMsg = '/* Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . ' */';
    $safeID = intval($id);
    $sql = " $debugMsg SELECT COUNT(*) AS qty " .
           " FROM {$this->tables['req_relations']} " .
           " WHERE source_id={$safeID} OR destination_id={$safeID} ";
    $rs = $this->db->get_recordset($sql);
    return($rs[0]['qty']);
  }
  
  
  /**
   * add a relation of a given type between two requirements
   * 
   * @author Andreas Simon
   * 
   * @param integer $source_id ID of source requirement
   * @param integer $destination_id ID of destination requirement
   * @param integer $type_id relation type ID to set
   * @param integer $author_id user's ID
   */
  public function add_relation($source_id, $destination_id, $type_id, $author_id) {
    
    $debugMsg = '/* Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . ' */';
    $time = $this->db->db_now();
    $sql = " $debugMsg INSERT INTO {$this->tables['req_relations']} "  . 
         " (source_id, destination_id, relation_type, author_id, creation_ts) " .
         " values ($source_id, $destination_id, $type_id, $author_id, $time)";
    $this->db->exec_query($sql);
  }
  
  
  /**
   * delete an existing relation with between two requirements
   * 
   * @author Andreas Simon
   * 
   * @param int $id requirement relation id
   */
  public function delete_relation($id) {
    
    $debugMsg = '/* Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . ' */';
    $sql = " $debugMsg DELETE FROM {$this->tables['req_relations']} WHERE id=$id ";
    $this->db->exec_query($sql);
  }
  
  
  /**
   * delete all existing relations for (from or to) a given req id, no matter which project
   * they belong to or which other requirement they are related to
   * 
   * @author Andreas Simon
   * 
   * @param int $id requirement ID (can be array of IDs)
   */
  public function delete_all_relations($id) {
    
    $debugMsg = '/* Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . ' */';
    $id_list = implode(",", (array)$id);
    $sql = " $debugMsg DELETE FROM {$this->tables['req_relations']} " . 
         " WHERE source_id IN ($id_list) OR destination_id IN ($id_list) ";
    $this->db->exec_query($sql);
  }
  
  
  /**
   * initialize the requirement relation labels
   * 
   * @author Andreas Simon
   * 
   * @return array $labels a map with all labels in following form:
   *    Array
   *    (
   *          [1] => Array
   *              (
   *                  [source] => parent of
   *                  [destination] => child of
   *              )
   *          [2] => Array
   *              (
   *                  [source] => blocks
   *                  [destination] => depends on
   *              )
   *          [3] => Array
   *              (
   *                  [source] => related to
   *                  [destination] => related to
   *              )
   *      )
   */
  public static function get_all_relation_labels() 
  {
    
    $labels = config_get('req_cfg')->rel_type_labels;
    
    foreach ($labels as $key => $label) {
      $labels[$key] = init_labels($label);
    }
    
    return $labels;
  }
  
  
  /**
   * Initializes the select field for the localized requirement relation types.
   * 
   * @author Andreas Simon
   * 
   * @return array $htmlSelect info needed to create select box on multiple templates
   */
  function init_relation_type_select() 
  {
    
    $htmlSelect = array('items' => array(), 'selected' => null, 'equal_relations' => array());
    $labels = $this->get_all_relation_labels();
    
    foreach ($labels as $key => $lab) 
    {
      $htmlSelect['items'][$key . "_source"] = $lab['source'];
      if ($lab['source'] != $lab['destination']) 
      {
        // relation is not equal as labels for source and dest are different
        $htmlSelect['items'][$key . "_destination"] = $lab['destination']; 
      } 
      else 
      {
        // mark this as equal relation - no parent/child, makes searching simpler
        $htmlSelect['equal_relations'][] = $key . "_source"; 
      }
    }
    
    // set "related to" as default preselected value in forms
    if (defined('TL_REQ_REL_TYPE_RELATED') && isset($htmlSelect[TL_REQ_REL_TYPE_RELATED . "_source"])) 
    {
      $selected_key = TL_REQ_REL_TYPE_RELATED . "_source";
    } 
    else 
    {
      // "related to" is not configured, so take last element as selected one
      $keys = array_keys($htmlSelect['items']);
      $selected_key = end($keys);
    }
    $htmlSelect['selected'] = $selected_key;
    
    return $htmlSelect;
  }
  
  

  /**
   * getByAttribute
   * allows search (on this version) by one of following attributes
   * - title
   * - docid
    * 
    */
  function getByAttribute($attr,$tproject_id=null,$parent_id=null, $options = null)
    {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

      $my['options'] = array( 'check_criteria' => '=', 'access_key' => 'id', 
                              'case' => 'sensitive', 'output' => 'standard');
      $my['options'] = array_merge($my['options'], (array)$options);
      
      $output=null;
      $target = $this->db->prepare_string(trim($attr['value']));
      
      $where_clause = $attr['key'] == 'title' ? " NH_REQ.name " : " REQ.req_doc_id ";  
      
      switch($my['options']['check_criteria'])
      {
        case '=':
        default:
          $check_criteria = " = '{$target}' ";
        break;
        
        case 'like':
        case 'likeLeft':
          $check_criteria = " LIKE '{$target}%' ";
        break;
      }
      
    $sql = " /* $debugMsg */ SELECT ";
    switch($my['options']['output'])
    {
      case 'standard':
         $sql .= " REQ.id,REQ.srs_id,REQ.req_doc_id,NH_REQ.name AS title, REQ_SPEC.testproject_id, " .
                  " NH_RSPEC.name AS req_spec_title, REQ_SPEC.doc_id AS req_spec_doc_id, NH_REQ.node_order ";
        break;
                  
      case 'minimun':
         $sql .= " REQ.id,REQ.srs_id,REQ.req_doc_id,NH_REQ.name AS title, REQ_SPEC.testproject_id";
        break;
        
      case 'id':
         $sql .= " REQ.id";
        break;
        
      
    }
    $sql .= " FROM {$this->object_table} REQ " .
             " /* Get Req info from NH */ " .
             " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = REQ.id " .
             " JOIN {$this->tables['req_specs']} REQ_SPEC ON REQ_SPEC.id = REQ.srs_id " .
             " JOIN {$this->tables['nodes_hierarchy']} NH_RSPEC ON NH_RSPEC.id = REQ_SPEC.id " .
        " WHERE {$where_clause} {$check_criteria} ";
      
      if( !is_null($tproject_id) )
      {
        $sql .= " AND REQ_SPEC.testproject_id={$tproject_id}";
      }
      
      if( !is_null($parent_id) )
      {
        $sql .= " AND REQ.srs_id={$parent_id}";
      }
      
      $output = $this->db->fetchRowsIntoMap($sql,$my['options']['access_key']);
      return $output;
    }

  /**
   *  @param id: parent id: can be REQ ID or REQ VERSION ID depending of $child_type 
   *  @param child_type: 'req_versions', 'req_revisions'
   *
   *  @return  
   */
  function get_last_child_info($id, $options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my['options'] = array('child_type' => 'revision', 'output' => 'full');
    $my['options'] = array_merge($my['options'], (array)$options);

    
    $info = null;
    $target_cfg = array('version' => array('table' => 'req_versions', 'field' => 'version'), 
                        'revision' => array('table'=> 'req_revisions', 'field' => 'revision'));

    $child_type = $my['options']['child_type'];  // just for readability 
    $table = $target_cfg[$child_type]['table'];
    $field = $target_cfg[$child_type]['field'];
    
    $sql = " /* $debugMsg */ SELECT COALESCE(MAX($field),-1) AS $field " .
           " FROM {$this->tables[$table]} CHILD," .
           " {$this->tables['nodes_hierarchy']} NH WHERE ".
           " NH.id = CHILD.id ".
           " AND NH.parent_id = {$id} ";
    
    $max_verbose = $this->db->fetchFirstRowSingleColumn($sql,$field);
    
    if ($max_verbose >= 0)
    {
      $sql = "/* $debugMsg */ SELECT ";

      switch($my['options']['output'])
      {
        case 'credentials':
          $sql .= " CHILD.parent_id,CHILD.id,CHILD.revision,CHILD.doc_id ";
        break;
        
        case 'full':
          $sql .= " CHILD.* ";
        break;

        default:
          $sql .= $my['options']['output'];
        break;
      }
    
      $sql .= " FROM {$this->tables[$table]} CHILD," .
              " {$this->tables['nodes_hierarchy']} NH ".
              " WHERE $field = {$max_verbose} AND NH.id = CHILD.id AND NH.parent_id = {$id}";
  
      $info = $this->db->fetchFirstRow($sql);
    }
    return $info;
  }

  
  
  /**
   * 
    *
    * @internal revision
    * 20110115 - franciscom - fixed insert of null on timestamp field
    */
  function create_new_revision($parent_id,$user_id,$tproject_id,$req = null,$log_msg = null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
      $item_id = $this->tree_mgr->new_node($parent_id,$this->node_types_descr_id['requirement_revision']);
      
      // Needed to get higher revision NUMBER, to generata new NUMBER      
      $source_info =  $this->get_last_child_info($parent_id,array('child_type' => 'revision'));
      $current_rev = 0;
      if( !is_null($source_info) )
      {
        $current_rev = $source_info['revision']; 
      }
      $current_rev++;
      
      // Info regarding new record created on req_revisions table
      $ret = array();
      $ret['id'] = $item_id;
      $ret['revision'] = $current_rev;
      $ret['msg'] = 'ok';
        
      $this->copy_version_as_revision($parent_id,$item_id,$current_rev,$user_id,$tproject_id);
      $sql =   "/* $debugMsg */ " .
          " UPDATE {$this->tables['req_revisions']} " .
          " SET name ='" . $this->db->prepare_string($req['title']) . "'," .
          "     req_doc_id ='" . $this->db->prepare_string($req['req_doc_id']) . "'" .
          " WHERE id = {$item_id} ";
      $this->db->exec_query($sql);
      
      $new_rev = $current_rev+1;
      $db_now = $this->db->db_now();
      $sql = " /* $debugMsg */ " .
             " UPDATE {$this->tables['req_versions']} " .
             " SET revision = {$new_rev}, log_message=' " . $this->db->prepare_string($log_msg) . "'," .
             " creation_ts = {$db_now} ,author_id = {$user_id}, modifier_id = NULL";
              
      $nullTS = $this->db->db_null_timestamp();
      if(!is_null($nullTS))
      {
        $sql .= ",modification_ts = {$nullTS} ";
      }  
      
      $sql .=  " WHERE id = {$parent_id} ";
      $this->db->exec_query($sql);
      return $ret;
  }

  
  /**
   * 
    *
    */
  function copy_version_as_revision($parent_id,$item_id,$revision,$user_id,$tproject_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql =   '/* $debugMsg */' .
        " INSERT INTO {$this->tables['req_revisions']} " .
        " (parent_id,id,revision,scope,status,type,active,is_open, " .
          "  expected_coverage,author_id,creation_ts,modifier_id,modification_ts,log_message) " .
          " SELECT REQV.id, {$item_id}, {$revision}, " .
        " REQV.scope,REQV.status,REQV.type,REQV.active,REQV.is_open, " .
          " REQV.expected_coverage,REQV.author_id,REQV.creation_ts,REQV.modifier_id," .
          " REQV.modification_ts,REQV.log_message" .
          " FROM {$this->tables['req_versions']} REQV " .
          " WHERE REQV.id = {$parent_id} ";
      $this->db->exec_query($sql);
      
      // need to copy Custom Fields ALSO
      // BAD NAME -> version_id is REALLY NODE ID
      $source = array('id' => 0, 'version_id' =>  $parent_id);
      $dest = array('id' => 0, 'version_id' =>  $item_id);
      $this->copy_cfields($source,$dest,$tproject_id);
      
  } 
  
  
  /**
   * used to create overwiew of changes between revisions
   * 20110116 - franciscom - BUGID 4172 - MSSQL UNION text field issue
   */
  function get_history($id,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my['options'] = array('output' => "map", 'decode_user' => false);
    $my['options'] = array_merge($my['options'], (array)$options);

    // 
    // Why can I use these common fields ?
    // explain better
    $common_fields = " REQV.id AS version_id, REQV.version,REQV.creation_ts, REQV.author_id, " .
                     " REQV.modification_ts, REQV.modifier_id ";
             
    // needs a double coalesce not too elegant but...    
    
    // Two steps algorithm
    // First understand is we already have a revision
    $sql =   " /* $debugMsg */" . 
        " SELECT COUNT(0) AS qta_rev " . 
        " FROM {$this->tables['req_revisions']} REQRV " .
        " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.id = REQRV.parent_id " .
        " WHERE NH_REQV.parent_id = {$id} ";
        
    $rs = $this->db->get_recordset($sql);
    
    $sql =   "/* $debugMsg */" .
          " SELECT REQV.id AS version_id, REQV.version," .
          "     REQV.creation_ts, REQV.author_id, " .
          "     REQV.modification_ts, REQV.modifier_id, " . 
                self::NO_REVISION . " AS revision_id, " .
          "      REQV.revision, REQV.scope, " .
          "      REQV.status,REQV.type,REQV.expected_coverage,NH_REQ.name, REQ.req_doc_id, " .
          " COALESCE(REQV.log_message,'') AS log_message" .
          " FROM {$this->tables['req_versions']}  REQV " .
          " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.id = REQV.id " .
          " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = NH_REQV.parent_id " .
          " JOIN {$this->tables['requirements']} REQ ON REQ.id = NH_REQ.id " .
          " WHERE NH_REQV.parent_id = {$id} ";
        
    if( $rs[0]['qta_rev'] > 0 )
    {    
      // 
      // Important NOTICE - MSSQL
      // 
      // text fields can be used on union ONLY IF YOU USE UNION ALL
      //
      // UNION ALL returns ALSO duplicated rows.
      // In this situation this is NOT A PROBLEM (because we will not have dups)
      //
      $sql .=  " UNION ALL ( " .
              " SELECT REQV.id AS version_id, REQV.version, " .
              "     REQRV.creation_ts, REQRV.author_id, " .
              "     REQRV.modification_ts, REQRV.modifier_id, " . 
              "     REQRV.id AS revision_id, " .
              "     REQRV.revision,REQRV.scope,REQRV.status,REQRV.type, " .
              "     REQRV.expected_coverage,REQRV.name,REQRV.req_doc_id, " .
              "     COALESCE(REQRV.log_message,'') as log_message" .
              " FROM {$this->tables['req_versions']} REQV " .
              " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.id = REQV.id " .
              " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = NH_REQV.parent_id " .
              " JOIN {$this->tables['requirements']} REQ ON REQ.id = NH_REQ.id " .
              " JOIN {$this->tables['req_revisions']} REQRV " .
              " ON REQRV.parent_id = REQV.id " . 
              " WHERE NH_REQV.parent_id = {$id} " .
              " ) " .
              " ORDER BY version_id DESC,version DESC,revision DESC ";
    }
    
    switch($my['options']['output'])
    {
      case 'map':
          $rs = $this->db->fetchRowsIntoMap($sql,'version_id');
      break;
      
      case 'array':
        $rs = $this->db->get_recordset($sql);
      break;
    }
      
      if( !is_null($rs) )
      {
        $lbl = init_labels(array('undefined' => 'undefined'));
        $key2loop = array_keys($rs);
        foreach($key2loop as $ap)
        {
          $rs[$ap]['item_id'] = ($rs[$ap]['revision_id'] > 0) ? $rs[$ap]['revision_id'] : $rs[$ap]['version_id'];
          
          // IMPORTANT NOTICE
          // each DBMS uses a different (unfortunatelly) way to signal NULL DATE
          //
          // We need to Check with ALL DB types
          // MySQL    NULL DATE -> "0000-00-00 00:00:00" 
          // Postgres NULL DATE -> NULL
          // MSSQL    NULL DATE - ???
          $key4date = 'creation_ts';
          $key4user = 'author_id';
          if( ($rs[$ap]['modification_ts'] != '0000-00-00 00:00:00') && !is_null($rs[$ap]['modification_ts']) )
          {
            $key4date = 'modification_ts';
            $key4user = 'modifier_id';
          }
          $rs[$ap]['timestamp'] = $rs[$ap][$key4date];
          $rs[$ap]['last_editor'] = $rs[$ap][$key4user];
          // decode user_id for last_editor
          $user = tlUser::getByID($this->db,$rs[$ap]['last_editor']);
          $rs[$ap]['last_editor'] = $user ? $user->getDisplayName() : $lbl['undefined'];
        }
      }
      
      $history = $rs;
      if( $my['options']['decode_user'] && !is_null($history) )
      {
        $this->decode_users($history);
    }
 
      return $history;
  }
  
  
  /**
   * 
   *
    */
  function get_version($version_id,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my['opt'] = array('renderImageInline' => false);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $sql = " /* $debugMsg */ SELECT REQ.id,REQ.srs_id,REQ.req_doc_id," . 
           " REQV.scope,REQV.status,REQV.type,REQV.active," . 
           " REQV.is_open,REQV.author_id,REQV.version,REQV.revision,REQV.id AS version_id," .
           " REQV.expected_coverage,REQV.creation_ts,REQV.modifier_id," .
           " REQV.modification_ts,REQV.revision,NH_REQ.name AS title, REQ_SPEC.testproject_id, " .
           " NH_RSPEC.name AS req_spec_title, REQ_SPEC.doc_id AS req_spec_doc_id, NH_REQ.node_order " .
           " FROM {$this->object_table} REQ " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = REQ.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.parent_id = NH_REQ.id ".
           " JOIN  {$this->tables['req_versions']} REQV ON REQV.id = NH_REQV.id " .  
           " JOIN {$this->tables['req_specs']} REQ_SPEC ON REQ_SPEC.id = REQ.srs_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_RSPEC ON NH_RSPEC.id = REQ_SPEC.id " .
           " WHERE REQV.id = " . intval($version_id);

    $dummy = $this->db->get_recordset($sql);
    
    if( !is_null($dummy) )
    {
      $this->decode_users($dummy);    
      $dummy = $dummy[0];
    }

    if(!is_null($dummy) && $my['opt']['renderImageInline'])
    {
      $this->renderImageAttachments($dummy['id'],$dummy);
    }  

    return $dummy;  
  }  



  /**
   * 
   *
   * @internal revision
   *
   */
  function get_revision($revision_id,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my['opt'] = array('renderImageInline' => false);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $sql = " /* $debugMsg */ " .
           " SELECT REQV.id AS req_version_id,REQ.id,REQ.srs_id,
             REQ.req_doc_id,REQRV.scope,REQRV.status,REQRV.type,
             REQRV.active," . 
           " REQRV.is_open,REQRV.author_id,REQV.version,REQRV.parent_id AS version_id," .
           " REQRV.expected_coverage,REQRV.creation_ts,REQRV.modifier_id," .
           " REQRV.modification_ts,REQRV.revision, REQRV.id AS revision_id," .
           " NH_REQ.name AS title, REQ_SPEC.testproject_id, " .
           " NH_RSPEC.name AS req_spec_title, REQ_SPEC.doc_id AS req_spec_doc_id, NH_REQ.node_order " .
           " FROM {$this->tables['req_revisions']} REQRV " .
           " JOIN {$this->tables['req_versions']} REQV ON REQV.id = REQRV.parent_id ".
           " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.id = REQRV.parent_id ".
           " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = NH_REQV.parent_id " .
           " JOIN {$this->tables['requirements']} REQ ON REQ.id = NH_REQ.id " .
           " JOIN {$this->tables['req_specs']} REQ_SPEC ON REQ_SPEC.id = REQ.srs_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_RSPEC ON NH_RSPEC.id = REQ_SPEC.id " .
           " WHERE REQRV.id = " . intval($revision_id);
    $dummy = $this->db->get_recordset($sql);
    
    if( !is_null($dummy) )
    {
      $this->decode_users($dummy);    
      $dummy = $dummy[0];
    }

    if(!is_null($dummy) && $my['opt']['renderImageInline'])
    {
      $this->renderImageAttachments($dummy['id'],$dummy);
    }  

    return $dummy;  
  }  

  
  /**
   * get info regarding a req version, using also revision as access criteria.
   *
   * @int version_id
   * @array revision_access possible keys 'id', 'number'
   *
   * @uses print.inc.php
   * @uses renderReqForPrinting()
   *
   *
   */
  function get_version_revision($version_id,$revision_access,$opt=null) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my['opt'] = array('renderImageInline' => false);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $sql =   "/* $debugMsg */";
    
    if( isset($revision_access['number']) ) {  
      $rev_number = intval($revision_access['number']);

      // we have to tables to search on
      // Req Versions -> holds LATEST revision
      // Req Revisions -> holds other revisions
      $sql .= " SELECT NH_REQV.parent_id AS req_id, REQV.id AS version_id, REQV.version," .
              "     REQV.creation_ts, REQV.author_id, " .
              "     REQV.modification_ts, REQV.modifier_id, " . 
              self::NO_REVISION . " AS revision_id, " .
              "      REQV.revision, REQV.scope, " .
              "      REQV.status,REQV.type,REQV.expected_coverage,NH_REQ.name, REQ.req_doc_id, " .
              " COALESCE(REQV.log_message,'') AS log_message, NH_REQ.name AS title " .
              " FROM {$this->tables['req_versions']}  REQV " .
              " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.id = REQV.id " .
              " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = NH_REQV.parent_id " .
              " JOIN {$this->tables['requirements']} REQ ON REQ.id = NH_REQ.id " .
              " WHERE NH_REQV.id = {$version_id} AND REQV.revision = {$rev_number} "; 

      $sql .=  " UNION ALL ( " .
            " SELECT NH_REQV.parent_id AS req_id, REQV.id AS version_id, REQV.version, " .
            "     REQRV.creation_ts, REQRV.author_id, " .
            "     REQRV.modification_ts, REQRV.modifier_id, " . 
            "     REQRV.id AS revision_id, " .
            "     REQRV.revision,REQRV.scope,REQRV.status,REQRV.type, " .
            "     REQRV.expected_coverage,REQRV.name,REQRV.req_doc_id, " .
            "     COALESCE(REQRV.log_message,'') as log_message, NH_REQ.name AS title " .
            " FROM {$this->tables['req_versions']} REQV " .
            " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.id = REQV.id " .
            " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = NH_REQV.parent_id " .
            " JOIN {$this->tables['requirements']} REQ ON REQ.id = NH_REQ.id " .
            " JOIN {$this->tables['req_revisions']} REQRV " .
            " ON REQRV.parent_id = REQV.id " . 
            " WHERE NH_REQV.id = {$version_id} AND REQRV.revision = {$rev_number} ) ";
    
    }  else {  
      // revision_id is present ONLY on req revisions table, then we do not need UNION
       $sql .=  " SELECT NH_REQV.parent_id AS req_id, REQV.id AS version_id, REQV.version, " .
                "     REQRV.creation_ts, REQRV.author_id, " .
                "     REQRV.modification_ts, REQRV.modifier_id, " . 
                "     REQRV.id AS revision_id, " .
                "     REQRV.revision,REQRV.scope,REQRV.status,REQRV.type, " .
                "     REQRV.expected_coverage,REQRV.name,REQRV.req_doc_id, " .
                "     COALESCE(REQRV.log_message,'') as log_message, NH_REQ.name AS title " .
                " FROM {$this->tables['req_versions']} REQV " .
                " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.id = REQV.id " .
                " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = NH_REQV.parent_id " .
                " JOIN {$this->tables['requirements']} REQ ON REQ.id = NH_REQ.id " .
                " JOIN {$this->tables['req_revisions']} REQRV " .
                " ON REQRV.parent_id = REQV.id " . 
                " WHERE NH_REQV.id = {$version_id} AND REQRV.revision_id = " . intval($revision_access['id']);
    }

    $rs = $this->db->get_recordset($sql);
    
    if(!is_null($rs) && $my['opt']['renderImageInline']) {
      $k2l = array_keys($rs);
      foreach($k2l as $akx) { 
        $this->renderImageAttachments($rs[$akx]['req_id'],$rs[$akx]);
      } 
      reset($rs);
    }  
    return $rs;
  }

  
  
  /**
   * 
    *
    */
  function decode_users(&$rs)
  {
      $userCache = null;  // key: user id, value: display name
      $key2loop = array_keys($rs);
      $labels['undefined'] = lang_get('undefined');
      $user_keys = array('author' => 'author_id', 'modifier' => 'modifier_id');
      foreach( $key2loop as $key )
      {
        foreach( $user_keys as $ukey => $userid_field)
        {
          $rs[$key][$ukey] = '';
          if(trim($rs[$key][$userid_field]) != "")
          {
            if( !isset($userCache[$rs[$key][$userid_field]]) )
            {
              $user = tlUser::getByID($this->db,$rs[$key][$userid_field]);
              $rs[$key][$ukey] = $user ? $user->getDisplayName() : $labels['undefined'];
              $userCache[$rs[$key][$userid_field]] = $rs[$key][$ukey];
            }
            else
            {
              $rs[$key][$ukey] = $userCache[$rs[$key][$userid_field]];
            }
          }
        }  
      }
  }
  


    /**
   * 
    *
    */
  function generateUniqueTitle($title2check, $parent_id, $tproject_id)
  {

    static $fieldSize;
    static $getOptions;
    static $reqCfg;
    static $mask;
    static $title_max_len;
    
    if( !$fieldSize )
    {
      $fieldSize = config_get('field_size');
      $reqCfg = config_get('req_cfg');
      
      $mask = $reqCfg->duplicated_name_algorithm->text;
      $title_max_len = $fieldSize->requirement_title;

        $getOptions = array('output' => 'minimun', 'check_criteria' => 'likeLeft');
    }
    
    $generated = $title2check;
      $attr = array('key' => 'title', 'value' => $title2check);
      
      // search need to be done in like to the left
    $itemSet = $this->getByAttribute($attr,$tproject_id,$parent_id,$getOptions);
  
    // we borrow logic (may be one day we can put it on a central place) from
    // testcase class create_tcase_only()
    if( !is_null($itemSet) && ($siblingQty=count($itemSet)) > 0 )
    {
            $nameSet = array_flip(array_keys($itemSet));
      $target = $title2check . ($suffix = sprintf($mask,++$siblingQty));
      $final_len = strlen($target);
      if( $final_len > $title_max_len)
      {
        $target = substr($target,strlen($suffix),$title_max_len);
      }
             // Need to recheck if new generated name does not crash with existent name
             // why? Suppose you have created:
             // REQ [1]
             // REQ [2]
             // REQ [3]
             // Then you delete REQ [2].
             // When I got siblings  il will got 2 siblings, if I create new progressive using next,
             // it will be 3 => I will get duplicated name.
             while( isset($nameSet[$target]) )
             {
          $target = $title2check . ($suffix = sprintf($mask,++$siblingQty));
          $final_len = strlen($target);
          if( $final_len > $title_max_len)
          {
            $target = substr($target,strlen($suffix),$title_max_len);
          }
             }
             $generated = $target;
    }
    
    return $generated;
  }


    /**
   * 
    *
    */
  function getTestProjectID($id, $reqSpecID=null)
  {
    $reqSpecMgr = new requirement_spec_mgr($this->db);
    $parent = $reqSpecID;
    if( is_null($parent) )
    {
      $dummy = $this->tree_mgr->get_node_hierarchy_info($id);
      $parent = $dummy['parent_id']; 
    }
    $target = $reqSpecMgr->get_by_id($parent);
    return $target['testproject_id'];
  }


    /**
   * @param  $context map with following keys
   *             tproject_id => REQUIRED
   *            tplan_id => OPTIONAL
   *            platform_id => OPTIONAL, will be used ONLY 
   *                         if tplan_id is provided.
    *
    */
  function getAllByContext($context,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    if( !isset($context['tproject_id']) )
    {
      throw new Exception($debugMsg . ' : $context[\'tproject_id\'] is needed');
    }

    $where = "WHERE RSPEC.testproject_id = " . intval($context['tproject_id']);
    $sql = "/* $debugMsg */ " .
         "SELECT DISTINCT REQ.id,REQ.req_doc_id FROM {$this->tables['requirements']} REQ " .
         "JOIN {$this->tables['req_specs']} RSPEC ON RSPEC.id = REQ.srs_id ";
    

    if( isset($context['tplan_id']) )
    {
      
      $sql .= "JOIN {$this->tables['req_coverage']} REQCOV ON REQCOV.req_id = REQ.id " .
          "JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.parent_id = REQCOV.testcase_id " .
          "JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NH_TCV.id ";
    
      $where .= " AND TPTCV.testplan_id = " . intval($context['tplan_id']);
      if( isset($context['platform_id']) && intval($context['platform_id']) > 0 )
      {
        $where .= " AND TPTCV.platform_id = " . intval($context['platform_id']);
      }
    }
    
    
    $sql .= $where;    
    $rs = $this->db->fetchRowsIntoMap($sql,'id');
        
    return $rs;
  }

  /**
   *
   * @used-by 
   */
  function getFileUploadRelativeURL($req_id,$req_version_id) {
    $sfReqID = intval($req_id);
    $sfVersion = intval($req_version_id);

    $url = "lib/requirements/reqEdit.php" .
           "?doAction=fileUpload&requirement_id=" . $sfReqID . 
           "&req_id=" . $sfReqID ."&req_version_id=" . $sfVersion;

    return $url;
  }

  /**
   *
   * @used-by 
   */
  function getDeleteAttachmentRelativeURL($req_id,$req_version_id) {
    $url = "lib/requirements/reqEdit.php?doAction=deleteFile" .
           "&requirement_id=" . intval($req_id) . 
           "&req_version_id=" . intval($req_version_id) .
           "&file_id=" ; 
    return $url;
  }



  /**
   * exportRelationToXML
   * 
   * Function to export a requirement relation to XML.
   *
   * @param  int $relation relation data array
   * @param  string $troject_id
   * @param  boolean check_for_req_project
   *
   * @return  string with XML code
   * <relation>
   *   <source>doc_id</source>
   *   <source_project>prj</source_project>
   *   <destination>doc2_id</destination>
   *   <destination_project>prj2</destination_project>
   *   <type>0</type>
   * </relation>
   * 
   * @internal revisions
   *
   */
  function exportRelationToXML( $relation, $tproject_id = null, $check_for_req_project = false)
  {
    $xmlStr = '';
    $source_docid = null; $destination_docid = null;
    $source_project = null; $destination_project = null;

    if( !is_null($relation) ) 
    {
      // FRL : interproject linking support
      $tproject_mgr = new testproject($this->db);
      $reqs = $this->get_by_id($relation['source_id'],requirement_mgr::LATEST_VERSION);
      if ( ! is_null ( $reqs ) && count($reqs) > 0 )
      {
        $source_docid = $reqs[0]['req_doc_id'];
        if ($check_for_req_project)
        {
          $tproject = $tproject_mgr->get_by_id($reqs[0]['testproject_id']);
          if ($tproject['id'] != $tproject_id)
          {
            $source_project = $tproject['name'];
          }
        }
      }
  
      $reqs = $this->get_by_id($relation['destination_id'],requirement_mgr::LATEST_VERSION);
      if( !is_null($reqs) && count($reqs) > 0 )
      {
        $destination_docid = $reqs[0]['req_doc_id'];
        if ($check_for_req_project)
        {
          $tproject = $tproject_mgr->get_by_id($reqs[0]['testproject_id']);
          if ($tproject['id'] != $tproject_id)
          {
            $destination_project = $tproject['name'];
          }
        }

      }
  
      if ( !is_null($source_docid) && !is_null($destination_docid) )
      {
        $relation['source_doc_id'] = $source_docid;
        $relation['destination_doc_id'] = $destination_docid;
            
        $info = array("||SOURCE||" => "source_doc_id","||DESTINATION||" => "destination_doc_id",
                      "||TYPE||" => "relation_type");

        $elemTpl = "\t" .   "<relation>" . "\n\t\t" . "<source>||SOURCE||</source>" ;
        if (!is_null($source_project))
        {
          $elemTpl .= "\n\t\t" . "<source_project>||SRC_PROJECT||</source_project>";
          $relation['source_project'] = $source_project;
          $info["||SRC_PROJECT||"] = "source_project";
        }

        $elemTpl .= "\n\t\t" . "<destination>||DESTINATION||</destination>";
        if (!is_null($destination_project))
        {
          $elemTpl .= "\n\t\t" . "<destination_project>||DST_PROJECT||</destination_project>";
          $relation['destination_project'] = $destination_project;
          $info["||DST_PROJECT||"] = "destination_project";
        }
        $elemTpl .=  "\n\t\t" . "<type>||TYPE||</type>" . "\n\t" . "</relation>" . "\n";
                   
        $relations[] = $relation;
        $xmlStr = exportDataToXML($relations,"{{XMLCODE}}",$elemTpl,$info,true);              
      }
    }
  
    return $xmlStr;
  }
  
  /**
   * Converts a XML relation into a Map that represents relation.
   * 
   * The XML should be in the following format: 
   * 
   * <relation>
   *  <source>doc_id</source>
   *     <source_project>prj</source_project>
   *     <destination>doc2_id</destination>
   *     <destination_project>prj2</destination_project>
   *     <type>0</type>
   * </relation>
   * 
   * And here is an example of an output map of this function.
   * 
   * [
   *  'source_doc_id' => 'doc_id', 
   *  'destination_doc_id' => 'doc2_id', 
   *  'type'=> 0, 
   *  'source_id' => 100, 
   *  'destination_id' => 101
   * ]
   * 
   * The source_id and the destination_id are set here to null but are used in 
   * other parts of the system. When you add a relation to the database you 
   * have to provide the source_id and destination_id.
   * 
   * @internal revisions
   * 20120110 - frl - add project info if interproject_linking is set
   * 20110314 - kinow - Created function.
   */
  function convertRelationXmlToRelationMap($xml_item)
  {
    // Attention: following PHP Manual SimpleXML documentation, Please remember to cast
    //            before using data from $xml,
    if( is_null($xml_item) )
    {
      return null;      
    }
          
    $dummy=array();
    foreach($xml_item->attributes() as $key => $value)
    {
       $dummy[$key] = (string)$value;  // See PHP Manual SimpleXML documentation.
    }    
    
    $dummy['source_doc_id'] = (string)$xml_item->source;
    $dummy['destination_doc_id'] = (string)$xml_item->destination;
    $dummy['type'] = (string)$xml_item->type;
    $dummy['source_id'] = null;
    $dummy['destination_id'] = null;
    // FRL :  interproject linking support
    $dummy['source_tproject'] = property_exists($xml_item,'source_project') ? (string)$xml_item->source_project : null;
    $dummy['destination_tproject'] = property_exists($xml_item,'destination_project') ? (string)$xml_item->destination_project : null;

    return $dummy;
  }

  /**
   * This function receives a relation XML node, converts it into a map and 
   * then adds this relation to database if it doesn't exist yet.
   *
   * @internal revisions
   * 20110314 - kinow - Created function.
   */
  function createRelationFromXML($xml,$tproject_id,$author_id)
  {
    $relationAsMap = $this->convertRelationXmlToRelationMap($xml);
    $user_feedback = $this->createRelationFromMap($relationAsMap, $tproject_id, $author_id);
    return $user_feedback;
  }

  /**
   * Adds a relation into database. Before adding it checks whether it 
   * exists or not. If it exists than the relation is not added, otherwise 
   * it is.
   *
   * Map structure
   * source_doc_id => doc_id
   * destination_doc_id => doc_id
   * type => 10
   * source_id => 0
   * destination_id = 0
   *
   * @internal revisions
   * 20110314 - kinow - Created function.
   */
  function createRelationFromMap($rel, $tproject_id, $authorId)
  {
    $status_ok = true;

    // get internal source id / destination id
    $options = array('access_key' => 'req_doc_id', 'output' =>'minimun');
    $reqs = null;
    $source_doc_id = $rel['source_doc_id'];

    // FRL : interproject linking support (look for req in defined project and req must be found 
    // in current project if interproject_linking is not set)
    $reqs = $this->getByDocIDInProject($source_doc_id, $rel['source_tproject'], $tproject_id, null, $options);
    $source = ( ! is_null($reqs) ) ? $reqs[$source_doc_id] : null;
    if( !is_null($source)  && ($this->relationsCfg->interProjectLinking || $source['testproject_id'] == $tproject_id) )
    {
      $rel['source_id'] =  $source['id'];
    } 
        
    $destination_doc_id = $rel['destination_doc_id'];
    $reqs = $this->getByDocIDInProject($destination_doc_id, $rel['destination_tproject'], $tproject_id, null, $options);
    $destination = ( ! is_null($reqs) ) ? $reqs[$destination_doc_id] : null;
    if( !is_null($destination) && 
        ($this->relationsCfg->interProjectLinking || $destination['testproject_id'] == $tproject_id) )
    {
      $rel['destination_id'] =  $destination['id'];
    } 

    // 2 - check if relation is valid
    $source_id    = $rel['source_id'];
    $destination_id   = $rel['destination_id'];
    $source_doc_id  .=  (is_null($rel['source_tproject']) ? '' : (' [' . $rel['source_tproject'] . ']'));
    $destination_doc_id .=  (is_null($rel['destination_tproject']) ? '' : (' [' . $rel['destination_tproject'] . ']'));
    $rel_types_desc  =  config_get('req_cfg')->rel_type_description;

    // check if given type is a valid one for rel_type_description defined in config
    $type_desc = array_key_exists(intval($rel['type']), $rel_types_desc) ? $rel_types_desc[intval($rel['type'])] : null;
    $user_feedback = array('doc_id' => $source_doc_id . ' - ' . $destination_doc_id,
                           'title' => lang_get('relation_type') . ' : ' . (is_null($type_desc) ? $rel['type'] : $type_desc));

    if ( is_null($source_id ) )
    {
      $user_feedback['import_status'] = lang_get('rel_add_error_src_id') ." [".$source_doc_id."].";
    }
    else if ( is_null($destination_id ) ) 
    {
      $user_feedback['import_status'] = lang_get('rel_add_error_dest_id') ." [".$destination_doc_id."].";
    }
    else if ($source_id == $destination_id) 
    {
      $user_feedback['import_status'] = lang_get('rel_add_error_self');
    }
    else if  ( ($source['testproject_id'] != $tproject_id)  &&  ($destination['testproject_id'] != $tproject_id) )
    {
      $user_feedback['import_status'] = lang_get('rel_add_not_in_project');
    }
    else if (is_null($type_desc)) 
    {
      $user_feedback['import_status'] = lang_get('rel_add_invalid_type');
    }
    else if ($this->check_if_relation_exists($source_id, $destination_id, $rel['type'])) 
    {
      $user_feedback['import_status'] = sprintf(lang_get('rel_add_error_exists_already'), $type_desc);
    }
    else // all checks are ok => create it
    {
      $this->add_relation($source_id, $destination_id, $rel['type'], $authorId);
      $user_feedback['import_status'] = lang_get('new_rel_add_success');
    }
    
    return array ($user_feedback);
  }

  /**
   * This function retrieves a requirement by doc_id with a specifed project 
   * @param string $doc_id
   * @param string $req_project name of req's project 
   * @param string $tproject_id  used only if  $req_project is null
   * @param string $parent_id
   * @param array $options (same as original $options getByDocID method)
   *
   * @internal revisions
   * 20110314 - kinow - Created function.
   */
  function getByDocIDInProject($doc_id, $req_project=null, $tproject_id=null,$parent_id=null, $options = null)
  {
    $reqs = null;
    if ( !is_null($req_project) )
    {
      $tproject_mgr = new testproject($this->db);
      $info=$tproject_mgr->get_by_name($req_project);
      if ( !is_null($info) ) // is project found ?
      {
        $tproject_id =  $info[0]['id'];
        $reqs = $this->getByDocID($doc_id, $tproject_id, $parent_id, $options);
      }
      //else  $req =  null; // project not found => no req
    }
    else
    {
      $reqs = $this->getByDocID($doc_id, $tproject_id, $parent_id,  $options);
    }
    return $reqs;
  }


/*
  function: getByIDBulkLatestVersionRevision

  @used by reqOverView

  args: id: requirement id (can be an array)
      [version_id]: requirement version id (can be an array)
      [version_number]: 
      [options]
      

  returns: null if query fails
           map with requirement info

  @internal revisions
  @since 1.9.12

*/
function getByIDBulkLatestVersionRevision($id,$opt=null)
{
  static $debugMsg;
  static $userCache;  // key: user id, value: display name
  static $lables;
  static $user_keys;

  if(!$debugMsg)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $labels['undefined'] = lang_get('undefined');
    $user_keys = array('author' => 'author_id', 'modifier' => 'modifier_id');
  }

  $my['opt'] = array('outputFormat' => 'map');
  $my['opt'] = array_merge($my['opt'],(array)$opt);

  $in_clause = "IN (" . implode(",",(array)$id) . ") ";
  $where_clause = " WHERE NH_REQV.parent_id " . $in_clause;

   // added -1 AS revision_id to make some process easier 
  $sql = " /* $debugMsg */ SELECT REQ.id,REQ.srs_id,REQ.req_doc_id," . 
         " REQV.scope,REQV.status,REQV.type,REQV.active," . 
         " REQV.is_open,REQV.author_id,REQV.version,REQV.id AS version_id," .
         " REQV.expected_coverage,REQV.creation_ts,REQV.modifier_id," .
         " REQV.modification_ts,REQV.revision, -1 AS revision_id, " .
         " NH_REQ.name AS title, REQ_SPEC.testproject_id, " .
         " NH_RSPEC.name AS req_spec_title, REQ_SPEC.doc_id AS req_spec_doc_id, NH_REQ.node_order " .

         " FROM {$this->tables['nodes_hierarchy']} NH_REQV JOIN " . 
         "( SELECT XNH_REQV.parent_id,MAX(XNH_REQV.id) AS LATEST_VERSION_ID " . 
         "  FROM  {$this->tables['nodes_hierarchy']} XNH_REQV " . 
         "  WHERE XNH_REQV.parent_id {$in_clause} " .  
         "  GROUP BY XNH_REQV.parent_id ) ZAZA ON NH_REQV.id = ZAZA.LATEST_VERSION_ID " .

         " JOIN {$this->tables['req_versions']} REQV ON REQV.id = NH_REQV.id " .  
         " JOIN {$this->object_table} REQ ON REQ.id = NH_REQV.parent_id " .

         " JOIN {$this->tables['req_specs']} REQ_SPEC ON REQ_SPEC.id = REQ.srs_id " .
         " JOIN {$this->tables['nodes_hierarchy']} NH_RSPEC ON NH_RSPEC.id = REQ_SPEC.id " .
         " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = REQ.id " .
         $where_clause;


  // echo $sql;
  $sqlOpt = ($my['opt']['outputFormat'] == 'map' ? 0 : database::CUMULATIVE);        
  $recordset = $this->db->fetchRowsIntoMap($sql,'id',$sqlOpt);


  $rs = null;
  // echo 'IN::' . __FUNCTION__ . '<br>';
  // new dBug($recordset);


  if(!is_null($recordset))
  {
    // Decode users
    $rs = $recordset;

    // try to guess output structure
    $x = array_keys(current($rs));
    if( is_int($x[0]) )
    {
      // output[REQID][0] = array('id' =>, 'xx' => ...)
      $flevel = array_keys($recordset);
      foreach($flevel as $flk)
      {
        $key2loop = array_keys($recordset[$flk]);
        foreach( $key2loop as $key )
        {
          foreach( $user_keys as $ukey => $userid_field)
          {
            $rs[$flk][$key][$ukey] = '';
            if(trim($rs[$flk][$key][$userid_field]) != "")
            {
              if( !isset($userCache[$rs[$flk][$key][$userid_field]]) )
              {
                $user = tlUser::getByID($this->db,$rs[$flk][$key][$userid_field]);
                $rs[$flk][$key][$ukey] = $user ? $user->getDisplayName() : $labels['undefined'];
                $userCache[$rs[$flk][$key][$userid_field]] = $rs[$flk][$key][$ukey];
                unset($user);
              }
              else
              {
                $rs[$flk][$key][$ukey] = $userCache[$rs[$flk][$key][$userid_field]];
              }
            }
          }  
        }
      }  
    } 
    else
    {
      // output[REQID] = array('id' =>, 'xx' => ...)
      $key2loop = array_keys($recordset);
      foreach( $key2loop as $key )
      {
        foreach( $user_keys as $ukey => $userid_field)
        {
          $rs[$key][$ukey] = '';
          if(trim($rs[$key][$userid_field]) != "")
          {
            if( !isset($userCache[$rs[$key][$userid_field]]) )
            {
              $user = tlUser::getByID($this->db,$rs[$key][$userid_field]);
              $rs[$key][$ukey] = $user ? $user->getDisplayName() : $labels['undefined'];
              $userCache[$rs[$key][$userid_field]] = $rs[$key][$ukey];
              unset($user);
            }
            else
            {
              $rs[$key][$ukey] = $userCache[$rs[$key][$userid_field]];
            }
          }
        }  
      }
    }  

  }    

  unset($recordset);
  unset($my);
  unset($dummy);

  return $rs;
}

/**
 *
 * @internal revisions
 * @since 1.9.12
 */
function getCoverageCounter($id) {
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
  $safe_id = intval($id);
  $sql = "/* $debugMsg */ " . 
           " SELECT COUNT(0) AS qty " .
           " FROM {$this->tables['req_coverage']} " .
           " WHERE req_id = " . $safe_id;


  $rs = $this->db->get_recordset($sql);
  return $rs[0]['qty'];
}


  /**
   *
   * @internal revisions
   * @since 1.9.12
   */
  function getCoverageCounterSet($itemSet) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " . 
             " SELECT req_id, COUNT(0) AS qty " .
             " FROM {$this->tables['req_coverage']} " .
             " WHERE req_id IN (" . implode(',', $itemSet) . ")" .
             " GROUP BY req_id ";

    $rs = $this->db->fetchRowsIntoMap($sql,'req_id');
    return $rs;
  }


  /**
   *
   * @internal revisions
   * @since 1.9.12
   */
  public function getRelationsCounters($itemSet) {
    $debugMsg = '/* Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . ' */';
    $inSet = implode(',',$itemSet);

    $sqlS = " $debugMsg SELECT COUNT(*) AS qty, source_id AS req_id " .
            " FROM {$this->tables['req_relations']} " .
            " WHERE source_id IN ({$inSet}) ";
    $sqlS .= (DB_TYPE == 'mssql') ? ' GROUP BY source_id ' : ' GROUP BY req_id ';

    $sqlD = " $debugMsg SELECT COUNT(*) AS qty, destination_id AS req_id " .
            " FROM {$this->tables['req_relations']} " .
            " WHERE destination_id IN ({$inSet}) ";
    $sqlD .= (DB_TYPE == 'mssql') ? ' GROUP BY destination_id ' : ' GROUP BY req_id ';

    $sqlT = " SELECT SUM(qty) AS qty, req_id " .
            " FROM ($sqlS UNION ALL $sqlD) D ".
            ' GROUP BY req_id ';

    $rs = $this->db->fetchColumnsIntoMap($sqlT,'req_id','qty');
    return $rs;
  }


  /**
   *
   *
   */
  function updateScope($reqVersionID)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = "/* $debugMsg */ UPDATE {$this->tables['req_versions']} " .
           " SET scope='" . $this->db->prepare_string($scope) . "'" .
           " WHERE id=" . intval($reqVersionID);
    $this->db->exec_query($sql);    
  }


  /**
   * render Image Attachments INLINE
   * 
   */
  function renderImageAttachments($id,&$item2render,$basehref=null)
  {
    static $attSet;
    static $targetTag;

    if(!$attSet || !isset($attSet[$id]))
    {
      $attSet[$id] = $this->attachmentRepository->getAttachmentInfosFor($id,$this->attachmentTableName,'id');
      $beginTag = '[tlInlineImage]';
      $endTag = '[/tlInlineImage]';
    }  

    if(is_null($attSet[$id]))
    {
      return;
    } 

    // $href = '<a href="Javascript:openTCW(\'%s\',%s);">%s:%s' . " $versionTag (link)<p></a>";
    // second \'%s\' needed if I want to use Latest as indication, need to understand
    // Javascript instead of javascript, because CKeditor sometimes complains
    $bhref = is_null($basehref) ? $_SESSION['basehref'] : $basehref;
    $img = '<p><img src="' . $bhref . '/lib/attachments/attachmentdownload.php?id=%id%"></p>'; 

    $key2check = array('scope');
    $rse = &$item2render;
    foreach($key2check as $item_key)
    {
      $start = strpos($rse[$item_key],$beginTag);
      $ghost = $rse[$item_key];

      // There is at least one request to replace ?
      if($start !== FALSE)
      {
        $xx = explode($beginTag,$rse[$item_key]);

        // How many requests to replace ?
        $xx2do = count($xx);
        $ghost = '';
        for($xdx=0; $xdx < $xx2do; $xdx++)
        {
          // Hope was not a false request.
          if( strpos($xx[$xdx],$endTag) !== FALSE)
          {
            // Separate command string from other text
            // Theorically can be just ONE, but it depends
            // is user had not messed things.
            $yy = explode($endTag,$xx[$xdx]);
            if( ($elc = count($yy)) > 0)
            {
              $atx = $yy[0];
              try
              {
                if(isset($attSet[$id][$atx]) && $attSet[$id][$atx]['is_image'])
                {
                  $ghost .= str_replace('%id%',$atx,$img);
                } 
                $lim = $elc-1;
                for($cpx=1; $cpx <= $lim; $cpx++) 
                {
                  $ghost .= $yy[$cpx];
                }  
              } 
              catch (Exception $e)
              {
                $ghost .= $rse[$item_key];
              }
            }  
          }
          else
          {
            // nothing to do
            $ghost .= $xx[$xdx];
          }  
        }
      }

      // reconstruct field contents
      if($ghost != '')
      {
        $rse[$item_key] = $ghost;
      }
    }   
  }



  /**
   * scope is managed at revision and version level
   * @since 1.9.13
   */ 
  function inlineImageProcessing($idCard,$scope,$rosettaStone)
  {
    // get all attachments, then check is there are images
    $att = $this->attachmentRepository->getAttachmentInfosFor($idCard->id,$this->attachmentTableName,'id');
    foreach($rosettaStone as $oid => $nid)
    {
      if($att[$nid]['is_image'])
      {
        $needle = str_replace($nid,$oid,$att[$nid]['inlineString']);
        $inlineImg[] = array('needle' => $needle, 'rep' => $att[$nid]['inlineString']);
      }  
    }  
    
    if( !is_null($inlineImg) )
    {
      $dex = $scope;
      foreach($inlineImg as $elem)
      {
        $dex = str_replace($elem['needle'],$elem['rep'],$dex);
      }  
      $this->updateScope($idCard->versionID,$dex);
    }  
  }

  /**
   *
   */
  function monitorOn($req_id,$user_id,$tproject_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    // simple checks
    // key = column name!!
    $safe = array();
    $safe['req_id'] = intval($req_id);
    $safe['user_id'] = intval($user_id);
    $safe['testproject_id'] = intval($tproject_id);

    $fields = implode(',',array_keys($safe));

    foreach($safe as $key => $val)
    {
      if( $val <= 0 )
      {
        throw new Exception("$key invalid value", 1);
      }  
    }  
    
    try
    {
      // check before insert
      $sql = "/* $debugMsg */ " .
             " SELECT req_id FROM {$this->tables['req_monitor']} " .
             " WHERE req_id = {$safe['req_id']} " .
             " AND user_id = {$safe['user_id']} " .
             " AND testproject_id = {$safe['testproject_id']}";
      $rs = $this->db->get_recordset($sql);

      if( is_null($rs) )
      {
        $sql = "/* $debugMsg */ " .
               " INSERT INTO {$this->tables['req_monitor']} ($fields) " . 
               " VALUES ({$safe['req_id']},{$safe['user_id']},{$safe['testproject_id']})";
        $this->db->exec_query($sql);      
      }  
    }
    catch (Exception $e)
    {
      echo $e->getMessage();
    }
  }

  /**
   *
   */
  function monitorOff($req_id,$user_id=null,$tproject_id=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    // simple checks
    $safe = array();
    $safe['req_id'] = intval($req_id);
    $safe['user_id'] = intval($user_id);
    $safe['tproject_id'] = intval($tproject_id);

    $key2check = array('req_id');
    foreach($key2check as $key)
    {
      $val = $safe[$key];
      if( $val <= 0 )
      {
        throw new Exception("$key invalid value", 1);
      }  
    }  
    
    // Blind delete
    try
    {
      $sql = "/* $debugMsg */ " .
             " DELETE FROM {$this->tables['req_monitor']} " .
             " WHERE req_id = {$safe['req_id']} ";

      if($safe['user_id'] >0)
      {
        $sql .= " AND user_id = {$safe['user_id']} ";
      }

      if($safe['tproject_id'] >0)
      {
        $sql .= " AND testproject_id = {$safe['tproject_id']}";
      }       
      $this->db->exec_query($sql);      
    }
    catch (Exception $e)
    {
      echo $e->getMessage();
    }
  }

  /**
   *
   */
  function getMonitoredByUser($user_id,$tproject_id,$opt=null,$filters=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['opt'] = array('reqSpecID' => null);
    $my['filters'] = array();

    $my['opt'] = array_merge($my['opt'],(array)$opt);
    $my['filters'] = array_merge($my['opt'],(array)$filters);

    // simple checks
    $safe = array();
    $safe['user_id'] = intval($user_id);
    $safe['tproject_id'] = intval($tproject_id);

    foreach($safe as $key => $val)
    {
      if( $val <= 0 )
      {
        throw new Exception("$key invalid value", 1);
      }  
    }  
    
    $rs = null;

    if( is_null($my['opt']['reqSpecID']) )
    {
      $sql = "/* $debugMsg */ " .
             " SELECT RQM.* FROM {$this->tables['req_monitor']} RQM " .
             " WHERE RQM.user_id = {$safe['user_id']} " .
             " AND RQM.testproject_id = {$safe['tproject_id']}";
    }
    else
    {
      $sql = "/* $debugMsg */ " .
             " SELECT RQM.* FROM {$this->tables['req_monitor']} RQM " .
             " JOIN {$this->tables['nodes_hierarchy']} NH_REQ " .
             " ON NH_REQ.id = RQM.req_id " .
             " WHERE RQM.user_id = {$safe['user_id']} " .
             " AND RQM.testproject_id = {$safe['tproject_id']} " .
             " AND NH_REQ.parent_id = " . intval($my['opt']['reqSpecID']);
    } 

    try
    {
      $rs = $this->db->fetchRowsIntoMap($sql,'req_id');      
    }
    catch (Exception $e)
    {
      echo $e->getMessage();
    }
 



    return $rs;
  }

  /**
   * 
   *
   */
  function getReqMonitors($req_id,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $options = array('tproject_id' => 0, 'output' => 'map');
    $options = array_merge($options,(array)$opt);

    // simple checks
    $safe = array();
    $safe['req_id'] = intval($req_id);
    $safe['tproject_id'] = intval($options['tproject_id']);

    $sql = "/* $debugMsg */ " .
             " SELECT RMON.user_id,U.login,U.email,U.locale " .
             " FROM {$this->tables['req_monitor']} RMON " .
             " JOIN {$this->tables['users']} U " .
             " ON U.id = RMON.user_id ".
             " WHERE req_id = {$safe['req_id']} ";

    if($safe['tproject_id'] > 0)
    {
      $sql .= " AND testproject_id = {$safe['tproject_id']}";
    }         
  
    switch($options['output'])
    {
      case 'array':
        $rs = $this->db->get_recordset($sql);
      break;

      case 'map':
      default:
        $rs = $this->db->fetchRowsIntoMap($sql,'user_id');
      break;
    }
    return $rs;
  }

  /**
   *
   */
  function notifyMonitors($req_id,$action,$user_id,$log_msg=null)
  {
    static $user;
    $mailBodyCache = '';
    $mailSubjectCache = '';
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $safe = array();
    $safe['req_id'] = intval($req_id);

    // who is monitoring?
    $iuSet = $this->getReqMonitors($safe['req_id']);
    if( is_null($iuSet) )
    {
      return;
    }  

    if( !$user )
    {
      $user = new tlUser($this->db);
    }  

    $author = $user->getNames($this->db,$user_id);
    $author = $author[$user_id];
    $idCard = $author['login'] . 
              " ({$author['first']} {$author['last']})";
    
    // use specific query because made things simpler
    $sql = "/* $debugMsg */ " .
           " SELECT REQ.id,REQ.req_doc_id,REQV.scope," .
           " NH_REQ.name AS title, REQV.version " .
           " FROM {$this->object_table} REQ " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = REQ.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.parent_id = NH_REQ.id ".
           " JOIN  {$this->tables['req_versions']} REQV ON REQV.id = NH_REQV.id " .
           " WHERE REQ.id = {$safe['req_id']} " .
           " ORDER BY REQV.version DESC ";

    if( !is_null($rs = $this->db->get_recordset($sql)) )
    {
      $req = $rs[0];
    }  

    $mailCfg = $this->getMonitorMailCfg($action);

    $from = config_get('from_email');
    $trf = array("%docid%" => $req['req_doc_id'],
                 "%title%" => $req['title'],
                 "%scope%" => $req['scope'],
                 "%user%" => $idCard,
                 "%logmsg%" => $log_msg,
                 "%version%" => $req['version'],
                 "%timestamp%" => date("D M j G:i:s T Y"));
    $body['target'] = array_keys($trf);
    $body['values'] = array_values($trf);
    $subj['target'] = array_keys($trf);
    $subj['values'] = array_values($trf);

    foreach($iuSet as $ue)
    {
      if( !isset($mailBodyCache[$ue['locale']]) )
      {
        $lang = $ue['locale'];
        $mailBodyCache[$lang] = mailBodyGet($mailCfg['bodyAccessKey']);
      
        // set values
        $mailBodyCache[$lang] = 
          str_replace($body['target'],$body['values'],$mailBodyCache[$lang]);
    
        $mailSubjectCache[$lang] = 
          lang_get($mailCfg['subjectAccessKey'],$lang);
      
        $mailSubjectCache[$lang] = 
          str_replace($subj['target'],$subj['values'],$mailSubjectCache[$lang]);
    
      }  
    
      // send mail
      $auditMsg = 'Requirement - ' . $action . ' - mail to user: ' . $ue["login"] .
                  ' using address:' . $ue["email"];
      try
      {
        $xmail = array();
        $xmail['cc'] = '';
        $xmail['attachment'] = null;
        $xmail['exit_on_error'] = false;
        $xmail['htmlFormat'] = true; 

      
       $rmx = @email_send($from,$ue["email"],
              $mailSubjectCache[$ue['locale']],$mailBodyCache[$ue['locale']],
              $xmail['cc'],$xmail['attachment'],$xmail['exit_on_error'],
              $xmail['htmlFormat'],null);
       $apx = $rmx->status_ok ? 'Succesful - ' : 'ERROR -'; 
      }
      catch (Exception $e)
      {
        $apx = 'ERROR - ';    
      }
      $auditMsg = $apx . $auditMsg;
      logAuditEvent($auditMsg);
    } 
  }

  /**
   *
   */
  function getMonitorMailCfg($action) {
    $cfg = null;
    switch( $action ) {
      case 'create_new_version':
        $cfg['subjectAccessKey'] = 'mail_subject_req_new_version';
        $cfg['bodyAccessKey'] = 'requirements/req_create_new_version.txt';
      break;

      case 'delete':
        $cfg['subjectAccessKey'] = 'mail_subject_req_delete';
        $cfg['bodyAccessKey'] = 'requirements/req_delete.txt';
      break;

      case 'delete_version':
        $cfg['subjectAccessKey'] = 'mail_subject_req_delete_version';
        $cfg['bodyAccessKey'] = 'requirements/req_delete_version.txt';
      break;
    }
    return $cfg;
  }

  /**
   *
   */
  function setNotifyOn($cfg)
  {
    foreach($cfg as $key => $val)
    {
      $this->notifyOn[$key] = $val;
    }  
  }

 /**
  *
  */
  function getNotifyOn($key=null) {
    if( !is_null($key) && isset($this->notifyOn['key']) ) {
      return $this->notifyOn['key'];
    }  
    return $this->notifyOn;
  }

 /**
  *
  */
  function updateCoverage($link,$whoWhen,$opt=null) {

    // Set coverage for previous version to FROZEN & INACTIVE ?
    // Create coverage for NEW Version
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $options = array('freezePrevious' => true);
    $options = array_merge($options,(array)$opt);

    $safeF = intval($link['source']);
    $safeT = intval($link['dest']);

    // Set coverage for previous version to FROZEN & INACTIVE ?
    if( $options['freezePrevious'] ) {
      $sql = " /* $debugMsg */ " .
             " UPDATE {$this->tables['req_coverage']} " .
             " SET link_status = " . LINK_TC_REQ_CLOSED_BY_NEW_REQVERSION . "," .
             "     is_active=0 " .
             " WHERE req_version_id=" . $safeF; 
      $this->db->exec_query($sql);
    }

    // Create coverage for NEW Version
    $sql = "/* $debugMsg */ " .
           " INSERT INTO {$this->tables['req_coverage']} " .
           " (testcase_id,tcversion_id,req_id," . 
           "  req_version_id,author_id,creation_ts) " .

           " SELECT testcase_id,tcversion_id,req_id, " .
           "        {$safeT} AS req_version_id," .
           "        {$whoWhen['user_id']} AS author_id, " .
           "        {$whoWhen['when']} AS creation_ts" .
           " FROM {$this->tables['req_coverage']} " .
           " WHERE req_version_id=" . $safeF;
    $this->db->exec_query($sql);

  }


 /**
  *
  */
  function updateTCVLinkStatus($from_version_id,$reason) {

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $safeF = intval($from_version_id);

    $sql = " /* $debugMsg */ " .
           " UPDATE {$this->tables['req_coverage']} " .
           " SET link_status = " . $reason  . "," .
           "     is_active=0 " .
           " WHERE req_version_id=" . $safeF; 
    $this->db->exec_query($sql);

  }


 /**
  *
  */
  function getAllReqVersionIDForReq( $idSet ) {

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $idList = implode(",", (array)$idSet);
    $sql = " /* $debugMsg */ 
             SELECT REQ.id AS req_id, NHREQVER.id AS req_version_id 
             FROM {$this->object_table} REQ 
             JOIN {$this->tables['nodes_hierarchy']} NHREQVER 
             ON NHREQVER.parent_id = REQ.id ";
    $sql .= " WHERE REQ.id IN ($idList)";

    return $this->db->fetchColumnsIntoMap(
      $sql,'req_id','req_version_id',database::CUMULATIVE);


  }


 /**
  *
  */
  function getActiveForTCVersion($tcversion_id, $srs_id = 'all') {                         
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $sql = " /* $debugMsg */ " . 
           " SELECT REQ.id,REQ.id AS req_id,REQ.req_doc_id,NHREQ.name AS title, RCOV.is_active," .
           " NHRS.name AS req_spec_title,RCOV.testcase_id," . 
           " REQV.id AS req_version_id, REQV.version " .
           " FROM {$this->object_table} REQ " .
           " JOIN {$this->tables['req_specs']} RSPEC " .
           " ON REQ.srs_id = RSPEC.id " .
           " JOIN {$this->tables['req_coverage']} RCOV " .
           " ON RCOV.req_id = REQ.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHRS " .
           " ON NHRS.id=RSPEC.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHREQ " .
           " ON NHREQ.id=REQ.id " .
           " JOIN {$this->tables['req_versions']} REQV " .
           " ON RCOV.req_version_id=REQV.id ";

    $idList = implode(",",(array)$tcversion_id);
    
    $sql .= " WHERE RCOV.tcversion_id IN (" . $idList . ")" . 
            " AND RCOV.is_active=1 ";

    // if only for one specification is required
    if ($srs_id != 'all')  {
      $sql .= " AND REQ.srs_id=" . intval($srs_id);
    }

    if ( is_array($tcversion_id) ) {
      return $this->db->fetchRowsIntoMap($sql,'tcversion_id',true);
    }
    else {
      return $this->db->get_recordset($sql);
    }  
  }

 /**
  * what is meaning of Good?
  * 
  */
  function getGoodForTCVersion($tcversion_id) {                         
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $sql = " /* $debugMsg */ " . 
           " SELECT REQ.id,REQ.id AS req_id,REQ.req_doc_id,
             NHREQ.name AS title, RCOV.is_active,
             RCOV.testcase_id,RCOV.tcversion_id,
             NHRS.name AS req_spec_title," . 
           " REQV.id AS req_version_id, REQV.version " .

           " FROM {$this->object_table} REQ " .
           " JOIN {$this->tables['req_specs']} RSPEC " .
           " ON REQ.srs_id = RSPEC.id " .
           " JOIN {$this->tables['req_coverage']} RCOV " .
           " ON RCOV.req_id = REQ.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHRS " .
           " ON NHRS.id=RSPEC.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHREQ " .
           " ON NHREQ.id=REQ.id " .
           " JOIN {$this->tables['req_versions']} REQV " .
           " ON RCOV.req_version_id=REQV.id ";

    $idList = implode(",",(array)$tcversion_id);
    
    $sql .= " WHERE RCOV.tcversion_id IN (" . $idList . ")";
            //" AND RCOV.is_active=1 ";

    if ( is_array($tcversion_id) ) {
      return $this->db->fetchRowsIntoMap($sql,'tcversion_id',true);
    }
    else {
      return $this->db->get_recordset($sql);
    }  
  }

 /**
  *
  */
  function getActiveForReqVersion($req_version_id) {

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $safe_id = intval($req_version_id);

    $sql = " /* $debugMsg */ " . 
           " SELECT NH_TC.id, NH_TC.id AS tcase_id,NH_TC.name,NH_TC.name AS tcase_name," . 
           " TCV.tc_external_id,TCV.version,TCV.id AS tcversion_id, " .
           " /* Seems to be compatible with MySQL,MSSQL,POSTGRES */ " .
           " (CASE WHEN RC.link_status = " . LINK_TC_REQ_CLOSED_BY_EXEC . 
           "       THEN 0 ELSE is_active END) AS can_be_deleted, " .
           " (CASE WHEN RC.link_status = " . LINK_TC_REQ_CLOSED_BY_NEW_TCVERSION . 
           "       THEN 1 ELSE 0 END) AS is_obsolete " .
           " FROM {$this->tables['nodes_hierarchy']} NH_TC " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_TCV " .
           " ON NH_TCV.parent_id=NH_TC.id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NH_TCV.id " .
           " JOIN {$this->tables['req_coverage']} RC ON RC.tcversion_id = NH_TCV.id ";

    $sql .= " WHERE RC.req_version_id={$safe_id} ";

    return $this->db->get_recordset($sql);
  }

  /**
   *
   */
  function delReqVersionTCVersionLink($bond,$caller=null) {

    $safeID = array( 'req' => intval($bond['req']), 
                     'tc' => intval($bond['tc']) );
    $output = 0;
    $sql = " DELETE FROM {$this->tables['req_coverage']} " .
           " WHERE req_version_id=" . $safeID['req'] .
           " AND tcversion_id=" . $safeID['tc'];
  
    $result = $this->db->exec_query($sql);
  
    if ($result && $this->db->affected_rows() == 1) {
      
      // Going to audit
      $sql = "SELECT NHP.name,NHC.id " . 
             " FROM {$this->tables['nodes_hierarchy']} NHP " .
             " JOIN {$this->tables['nodes_hierarchy']} NHC " .
             " ON NHP.id = NHC.parent_id " .
             " WHERE NHC.id IN(" . 
             $safeID['req'] . "," . $safeID['tc'] . ")";

      $mx = $this->db->fetchRowsIntoMap($sql,'id');
  
      $sql = " SELECT TCV.version " . 
             " FROM {$this->tables['tcversions']} TCV " .
             " WHERE TCV.id = " . $safeID['tc'];
      $tcv = current($this->db->fetchRowsIntoMap($sql,'version'));

      $sql = " SELECT RQV.version  " . 
             " FROM {$this->tables['req_versions']} RQV " .
             " WHERE RQV.id = " . $safeID['req'];
      $rqv = current($this->db->fetchRowsIntoMap($sql,'version'));

      logAuditEvent(TLS("audit_reqv_assignment_removed_tcv",
                        $mx[$safeID['req']]['name'],$rqv['version'],
                        $mx[$safeID['tc']]['name'],$tcv['version']),
                        "ASSIGN",$this->object_table);
      $output = 1;
    }
    return $output;
  }


  /**
   *
   */
  function delReqVersionTCVersionLinkByID($link_id) {

    $safeID = intval($link_id);

    // First get audit info
    $sql = " SELECT TCV.version AS tcv_vernum, NHTC.name AS tcname, " .
           " RQV.version AS req_vernum, NHRQ.name AS reqname " .
           " FROM {$this->tables['req_coverage']} RCOV " .
           " JOIN {$this->tables['tcversions']} TCV " .
           " ON TCV.id = RCOV.tcversion_id " .
           " JOIN {$this->tables['req_versions']} RQV " .
           " ON RQV.id = RCOV.req_version_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC " .
           " ON NHTC.id = RCOV.testcase_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHRQ " .
           " ON NHRQ.id = RCOV.req_id " .
           " WHERE RCOV.id = $safeID ";

    $audit = current($this->db->get_recordset($sql));

    $sql = " DELETE FROM {$this->tables['req_coverage']} " .
           " WHERE id = $safeID ";
  
    $result = $this->db->exec_query($sql);
    if ($result && $this->db->affected_rows() == 1) {
      logAuditEvent(TLS("audit_reqv_assignment_removed_tcv",
                        $audit['reqname'],$audit['req_vernum'],
                        $audit['tcname'],$audit['tcv_vernum']),
                        "ASSIGN",$this->object_table);
      $output = 1;
    }
    return $output;
  }


  /**
   *
   */
  function getLatestReqVersionCoverageCounterSet($itemSet) {

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " . 
             " SELECT RCOV.req_id, COUNT(0) AS qty " .
             " FROM {$this->tables['req_coverage']} RCOV " .
             " JOIN {$this->views['latest_req_version_id']} LRQV " .
             " ON LRQV.req_version_id = RCOV.req_version_id " .
             " WHERE LRQV.req_version_id IN (" . 
               implode(',', $itemSet) . ")" .
             " AND is_active = 1" .
             " GROUP BY req_id ";
    // echo $sql;
    $rs = $this->db->fetchRowsIntoMap($sql,'req_id');
    return $rs;
  }

  /*
    function: bulkAssignLatestREQVTCV
              assign N requirements to M test cases
              Do not write audit info              

    args: req_id: can be an array
          testcase_id: can be an array

    returns: number of assignments done


  */
  function bulkAssignLatestREQVTCV($req_id,$testcase_id,$author_id) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $insertCounter=0;  // just for debug
    
    $reqSet = (array)$req_id;  
    $tcaseSet = (array)$testcase_id;  

    $inReqID = implode(",",$reqSet);
    $inTCaseID = implode(",",$tcaseSet);
    
    // Get coverage for this set of requirements and testcase
    // to be used to understand if insert if needed    
    $sql = " /* $debugMsg */
             SELECT RCOV.req_id,RCOV.testcase_id,
                    RCOV.req_version_id,RCOV.tcversion_id
             FROM {$this->tables['req_coverage']} RCOV
             JOIN {$this->views['latest_req_version_id']} LRQV
             ON LRQV.req_version_id = RCOV.req_version_id
             JOIN {$this->views['latest_tcase_version_id']} LTCV
             ON LTCV.tcversion_id = RCOV.tcversion_id
             WHERE LRQV.req_id IN ({$inReqID}) 
             AND LTCV.testcase_id IN ({$inTCaseID}) ";

    // $coverage = $this->db->get_recordset($sql);
    $coverage = (array) $this->db->fetchMapRowsIntoMap($sql,
                              'req_version_id','tcversion_id');
    $sql = " /* $debugMsg */ 
             SELECT * FROM {$this->views['latest_tcase_version_id']}
             WHERE testcase_id IN ({$inTCaseID}) ";
    $ltcvSet = $this->db->fetchRowsIntoMap($sql,'tcversion_id');

    $sql = " /* $debugMsg */ 
             SELECT * FROM {$this->views['latest_req_version_id']}
             WHERE req_id IN ({$inReqID}) ";
    $lrqvSet = $this->db->fetchRowsIntoMap($sql,'req_version_id');

    $now = $this->db->db_now();
    $ins = " INSERT INTO {$this->tables['req_coverage']} 
             (req_id,testcase_id,req_version_id,
              tcversion_id,author_id,creation_ts) ";
    
    foreach( $ltcvSet as $tcversion_id => $tc ) {
      $sql = $ins;
      $values = array();
      foreach( $lrqvSet as $req_version_id => $req ) {
        if( !isset($coverage[$req_version_id][$tcversion_id]) ) {
         $insertCounter++;
         $values[] = " ({$req['req_id']},{$tc['testcase_id']},
                        $req_version_id,$tcversion_id,
                        {$author_id},{$now}) ";
        }                
      }

      if( count($values) > 0 ) {
        $sql .= " VALUES " . implode(',',$values);
        $this->db->exec_query($sql);
      }
    }

    return $insertCounter;
  }


  /**
   *
   * reqIdentity array('id' =>,'version_id' =>);
   * tcIdentity array('id' =>,'version_id' =>);
   *
   */
  function assignReqVerToTCVer($reqIdentity,$tcIdentity,$authorID) {

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $now = $this->db->db_now();
    $sql = " /* $debugMsg */ 
             INSERT INTO {$this->tables['req_coverage']} 
             (req_id,testcase_id,req_version_id, tcversion_id,
              author_id,creation_ts)
             VALUES ({$reqIdentity['id']},{$tcIdentity['id']},
                     {$reqIdentity['version_id']},
                     {$tcIdentity['version_id']},
                     {$authorID},{$now})";

    $result = $this->db->exec_query($sql);

    return 1;
  }

 /**
  * what is meaning of Good?
  * 
  */
  function getGoodForReqVersion($reqVersionID) {                         
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $sql = " /* $debugMsg */ " . 
           " SELECT REQ.id,REQ.id AS req_id,REQ.req_doc_id,
             NHREQ.name AS title, RCOV.is_active,
             RCOV.testcase_id,RCOV.tcversion_id,
             NHRS.name AS req_spec_title," . 
           " REQV.id AS req_version_id, REQV.version " .

           " FROM {$this->object_table} REQ " .
           " JOIN {$this->tables['req_specs']} RSPEC " .
           " ON REQ.srs_id = RSPEC.id " .
           " JOIN {$this->tables['req_coverage']} RCOV " .
           " ON RCOV.req_id = REQ.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHRS " .
           " ON NHRS.id=RSPEC.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHREQ " .
           " ON NHREQ.id=REQ.id " .
           " JOIN {$this->tables['req_versions']} REQV " .
           " ON RCOV.req_version_id=REQV.id ";

    $idList = implode(",",(array)$reqVersionID);
    
    $sql .= " WHERE RCOV.req_version_id IN (" . $idList . ")";

    echo $sql;

    return $this->db->fetchRowsIntoMap($sql,'req_version_id',true);
  }


} // class end
