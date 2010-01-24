<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: requirement_mgr.class.php,v $
 *
 * @version $Revision: 1.67 $
 * @modified $Date: 2010/01/24 15:56:35 $ by $Author: franciscom $
 * @author Francisco Mancardi
 *
 * Manager for requirements.
 * Requirements are children of a requirement specification (requirements container)
 *
 * rev:
 *	20100124 - franciscom - BUGID 0003089: Req versions new attributes - active and open 
 *							new methods updateActive(),updateOpen()
 *  20091228 - franciscom - exportReqToXML() - added expected_coverage
 *                          refactoring for feature req versioning
 *  20091227 - franciscom - delete() now manage version_id 
 * 	20091225 - franciscom - new method - generateDocID()
 *  20091216 - franciscom - create_tc_from_requirement() interface changes
 *  20091215 - asimon     - added new method getByDocID()  
 *  20091209 - asimon     - contrib for testcase creation, BUGID 2996
 *  20091208 - franciscom - contrib by julian - BUGID 2995
 *  20091207 - franciscom - create() added node_order
 *	20091202 - franciscom - create(), update() 
 *                          added contribution by asimon83/mx-julian that creates
 *                          links inside scope field.
 *	20091125 - franciscom - expected_coverage management 
 *  20090525 - franciscom - avoid getDisplayName() crash due to deleted user 
 *  20090514 - franciscom - BUGID 2491
 *  20090506 - franciscom - refactoring continued
 *  20090505 - franciscom - refactoring started.
 *                          removed use of REQ.node_order and title.
 *                          this fields must be managed on NH table
 *  
 *  20090401 - franciscom - BUGID 2316
 *  20090315 - franciscom - added require_once '/attachments.inc.php' to avoid autoload() bug
 *                          delete() - fixed delete order due to FK.
 *  20090222 - franciscom - exportReqToXML() - (will be available for TL 1.9)
 *  20081129 - franciscom - BUGID 1852 - bulk_assignment() 
*/

// Needed to use extends tlObjectWithAttachments, If not present autoload fails.
require_once( dirname(__FILE__) . '/attachments.inc.php');
class requirement_mgr extends tlObjectWithAttachments
{
	var $db;
	var $cfield_mgr;
	var $my_node_type;
	var $tree_mgr;
	var $node_types_descr_id;
    var $node_types_id_descr;
	var $attachmentTableName;
	
    const AUTOMATIC_ID=0;
    const ALL_VERSIONS=0;
    const LATEST_VERSION=-1;


  /*
    function: requirement_mgr
              contructor

    args: db: reference to db object

    returns: instance of requirement_mgr

  */
	function __construct(&$db)
	{
		$this->db = &$db;
		$this->cfield_mgr=new cfield_mgr($this->db);
		$this->tree_mgr =  new tree($this->db);

        $this->attachmentTableName = 'requirements';
		tlObjectWithAttachments::__construct($this->db,$this->attachmentTableName);

		$this->node_types_descr_id= $this->tree_mgr->get_available_node_types();
		$this->node_types_id_descr=array_flip($this->node_types_descr_id);
		$this->my_node_type=$this->node_types_descr_id['requirement'];
	    $this->object_table=$this->tables['requirements'];


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
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	$my['options'] = array('order_by' => " ORDER BY REQV.version DESC ");
    $my['options'] = array_merge($my['options'], (array)$options);

    // null => do not filter
	$my['filters'] = array('status' => null, 'type' => null);
    $my['filters'] = array_merge($my['filters'], (array)$filters);

	$filter_clause = '';
    $dummy[]='';  // trick to make implode() work
    foreach( $my['filters'] as $field2filter => $value)
    {
    	if( !is_null($value) )
    	{
    		$dummy[] = " {$field2filter} = '{$value}' ";
    	}
    }
    if( count($dummy) > 1)
    {
    	$filter_clause = implode(" AND ",$dummy);
    }

    $fields2get="REQ.id,REQ.srs_id,REQ.req_doc_id,REQV.scope,REQV.status,REQV.type,REQV.active," . 
                "REQV.is_open,REQV.author_id,REQV.version,REQV.id AS version_id," .
                "REQV.expected_coverage,REQV.creation_ts,REQV.modifier_id," .
                "REQV.modification_ts,NH_REQ.name AS title";
	$where_clause = " WHERE NH_REQV.parent_id ";
	if(is_array($id))
	{
		$where_clause .= "IN (" . implode(",",$id) . ") ";
	}
	else
	{
		$where_clause .= " = {$id} ";
	}
	
	if(is_array($version_id))
	{
	    $versionid_list = implode(",",$version_id);
	    $where_clause .= " AND REQV.id IN ({$versionid_list}) ";
	}
	else
	{
		if(is_array($version_id))
		{
		    $versionid_list = implode(",",$version_id);
		    $where_clause .= " AND REQV.version IN ({$versionid_list}) ";
		}
        else
        {
			if( is_null($version_id) )
			{
			    $where_clause .= " AND REQV.version = {$version_number} ";
			}
			else 
			{
			    if($version_id != self::ALL_VERSIONS && $version_id != self::LATEST_VERSION)
			    {
			    	$where_clause .= " AND REQV.id = {$version_id} ";
			    }
	    	}
        }
    }
  
	$sql = " /* $debugMsg */ SELECT {$fields2get}, REQ_SPEC.testproject_id, " .
	       " NH_RSPEC.name AS req_spec_title, REQ_SPEC.doc_id AS req_spec_doc_id, NH_REQ.node_order " .
	       " FROM {$this->object_table} REQ " .
	       " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = REQ.id " .
	       " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.parent_id = NH_REQ.id ".
	       " JOIN  {$this->tables['req_versions']} REQV ON REQV.id = NH_REQV.id " .  
	       " JOIN {$this->tables['req_specs']} REQ_SPEC ON REQ_SPEC.id = REQ.srs_id " .
	       " JOIN {$this->tables['nodes_hierarchy']} NH_RSPEC ON NH_RSPEC.id = REQ_SPEC.id " .
           $where_clause . $filter_clause . $my['options']['order_by'];

	$recordset = $this->db->get_recordset($sql);
  	$rs = null;
  	$userCache = null;  // key: user id, value: display name
  	if(!is_null($recordset))
  	{
  	    // Decode users
  	    $rs = $recordset;
  	    $key2loop = array_keys($recordset);
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
	20091202 - franciscom -  added contribution by asimon83/mx-julian 

  */
function create($srs_id,$reqdoc_id,$title, $scope, $user_id,
                $status = TL_REQ_STATUS_VALID, $type = TL_REQ_TYPE_INFO,
                $expected_coverage=1,$node_order=0)
{
    $result = array( 'id' => 0, 'status_ok' => 0, 'msg' => 'ko');
	$field_size = config_get('field_size');

	$reqdoc_id = trim_and_limit($reqdoc_id,$field_size->req_docid);
	$title = trim_and_limit($title,$field_size->req_title);
	$tproject_id = $this->tree_mgr->getTreeRoot($srs_id);

	$op = $this->check_basic_data($srs_id,$tproject_id,$title,$reqdoc_id);
	$result['msg'] = $op['status_ok'] ? $result['msg'] : $op['msg'];
	if( $op['status_ok'] )
	{
		$result = $this->create_req_only($srs_id,$reqdoc_id,$title,$user_id,$node_order);
		if($result["status_ok"])
		{

			if (config_get('req_cfg')->internal_links) 
			{
				$scope = req_link_replace($this->db, $scope, $tproject_id);
			}

			$version_number = 1;
			$op = $this->create_version($result['id'],$version_number,$scope,$user_id,
			                            $status,$type,$expected_coverage);
			$result['msg'] = $op['status_ok'] ? $result['msg'] : $op['msg'];
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
                $expected_coverage,$skip_controls=0)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $result['status_ok'] = 1;
    $result['msg'] = 'ok';
    
    $db_now = $this->db->db_now();
    $field_size=config_get('field_size');
    
    // get SRSid, needed to do controls
    $rs=$this->get_by_id($id,$version_id);
    $req = $rs[0];
    $srs_id=$req['srs_id'];
	$tproject_id = $this->tree_mgr->getTreeRoot($srs_id);

    /* contribution by asimon83/mx-julian */
	if (config_get('req_cfg')->internal_links) 
	{
		$scope = req_link_replace($this->db, $scope, $tproject_id);
	}
	/* end contribution by asimon83/mx-julian */
    
	$reqdoc_id=trim_and_limit($reqdoc_id,$field_size->req_docid);
	$title=trim_and_limit($title,$field_size->req_title);
    $chk=$this->check_basic_data($srs_id,$tproject_id,$title,$reqdoc_id,$id);

    if($chk['status_ok'] || $skip_controls)
	{
 		$sql = array();

  		$sql[] = "/* $debugMsg */ UPDATE {$this->tables['nodes_hierarchy']} " .
  		  	     " SET name='" . $this->db->prepare_string($title) . "'" .
  		  	     " WHERE id={$id}";
 	  	
	  	$sql[] = "/* $debugMsg */ UPDATE {$this->tables['requirements']} " .
	  	         " SET req_doc_id='" . $this->db->prepare_string($reqdoc_id) . "'" .
	  	         " WHERE id={$id}";
	  	
	  	$sql[] = "/* $debugMsg */ UPDATE {$this->tables['req_versions']} " .
	  			 " SET scope='" . $this->db->prepare_string($scope) . "', " .
	  	         " status='" . $this->db->prepare_string($status) . "', " .
	  	         " type='" . $this->db->prepare_string($type) . "', " .
	  	         " modifier_id={$user_id}, modification_ts={$db_now}, " . 
	  	         " expected_coverage={$expected_coverage} " . 
	  	         " WHERE id={$version_id}";

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

    } // 	  if($chk['status_ok'] || $skip_controls)
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
              Requirement custom fields values
              Attachments


    args: id: can be one id, or an array of id

    returns:

  */
	function delete($id,$version_id = self::ALL_VERSIONS)
 	{
 		$children = null;
		$where_clause_coverage = '';
	  	$where_clause_this = '';
		$deleteAll = false;
	    $result = null;
	    $doIt = true;
	    
	  	if(is_array($id))
	  	{
		
			$id_list = implode(',',$id);
	    	$where_clause_coverage = " WHERE req_id IN ({$id_list})";
			$where_clause_this = " WHERE id IN ({$id_list})";
	  	}
	    else
	    {
	    	$where_clause_coverage = " WHERE req_id = {$id}";
			$where_clause_this = " WHERE id = {$id}";
	    }
		
		// When deleting only one version, we need to check if we need to delete 
		// requirement also.
		$children[] = $version_id;
	  	if( $version_id == self::ALL_VERSIONS)
	  	{
 			$deleteAll = true;
			// I'm trying to speedup the next deletes
	    	$sql="SELECT NH.id FROM {$this->tables['nodes_hierarchy']} NH WHERE NH.parent_id ";
	    	if( is_array($id) )
	    	{
	      		$sql .=  " IN (" .implode(',',$id) . ") ";
	    	}
	    	else
	    	{
	      		$sql .= "  = {$id} ";
	    	}
	
	    	$children_rs=$this->db->fetchRowsIntoMap($sql,'id');
            $children = array_keys($children_rs); 

	    	// Delete Custom fields
	    	$this->cfield_mgr->remove_all_design_values_from_node($id);
	  		
	  		// delete dependencies with test specification
	  		$sql = "DELETE FROM {$this->tables['req_coverage']} " . $where_clause_coverage;
	  		$result = $this->db->exec_query($sql);

			if ($result)
	  		{
	  			$doIt = true;
	  			$the_ids = is_array($id) ? $id : array($id);
				foreach($the_ids as $key => $value)
				{
					$result = $this->attachmentRepository->deleteAttachmentsFor($value,$this->attachmentTableName);
				}
	    	}
	  	}        

        // Delete version info
	  	if( $doIt )
	  	{
	  			$where_clause_children = " WHERE id IN (" .implode(',',$children) . ") ";
	  			$sql = "DELETE FROM {$this->tables['req_versions']} " . $where_clause_children;
	  			$result = $this->db->exec_query($sql);

	  			$sql = "DELETE FROM {$this->tables['nodes_hierarchy']} " . $where_clause_children;
	  			$result = $this->db->exec_query($sql);
		} 

		if( $deleteAll && $result)
		{
	  		$sql = "DELETE FROM {$this->object_table} " . $where_clause_this;
	  		$result = $this->db->exec_query($sql);

	  		$sql = "DELETE FROM {$this->tables['nodes_hierarchy']} " . $where_clause_this;
	  		$result = $this->db->exec_query($sql);
		}
	
	    $result = (!$result) ? lang_get('error_deleting_req') : 'ok';
	  	return $result;
	}


/** collect coverage of Requirement
 * @param string $req_id ID of req.
 * @return assoc_array list of test cases [id, title]
 *
 * rev: 20080226 - franciscom - get test case external id
 */
function get_coverage($id)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = " SELECT DISTINCT NHA.id,NHA.name,TCV.tc_external_id " .
  	       " FROM {$this->tables['nodes_hierarchy']} NHA, " .
  	       " {$this->tables['nodes_hierarchy']} NHB, " .
  	       " {$this->tables['tcversions']} TCV, " .
  	       " {$this->tables['req_coverage']} RC " .
           " WHERE RC.testcase_id = NHA.id " .
  		   " AND NHB.parent_id=NHA.id " .
           " AND TCV.id=NHB.id " .
  		   " AND RC.req_id={$id}";
  	return $this->db->get_recordset($sql);
}


  /*
    function: check_basic_data
              do checks on title and reqdoc id, for a requirement

    args: srs_id: req spec id (req parent)
          title
          reqdoc_id
          [id]: default null


    returns: map
             keys: status_ok
                   msg

  */
  function check_basic_data($srs_id,$tproject_id,$title,$reqdoc_id,$id = null)
  {

  	$req_cfg = config_get('req_cfg');

  	$my_srs_id=$srs_id;

  	$ret['status_ok'] = 1;
  	$ret['msg'] = '';

  	if ($title == "")
  	{
  		$ret['status_ok'] = 0;
  		$ret['msg'] = lang_get("warning_empty_req_title");
  	}

  	if ($reqdoc_id == "")
  	{
  		$ret['status_ok'] = 0;
  		$ret['msg'] .=  " " . lang_get("warning_empty_reqdoc_id");
  	}

  	if($ret['status_ok'])
  	{
  		$ret['msg'] = 'ok';

  		// if($req_cfg->reqdoc_id->is_system_wide)
  		// {
  		// 	// req doc id MUST BE unique inside the whole DB
        // 	$my_srs_id = null;
  		// }
  		$rs = $this->getByDocID($reqdoc_id,$tproject_id);
        // new dBug($rs);
        // $checks = array();
        // $checks['!is_null(rs)'] = !is_null($rs);
        // $checks['is_null(id)'] = is_null($id);
        // $checks['!isset(rs[$id]'] = !isset($rs[$id]);
        // 
        // new dBug($checks);
        
        
 		if(!is_null($rs) && (is_null($id) || !isset($rs[$id])))
  		{
  			$ret['msg'] = sprintf(lang_get("warning_duplicate_reqdoc_id"),$reqdoc_id);
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
    $fields2get="RSPEC.id,testproject_id,RSPEC.scope,RSPEC.total_req,RSPEC.type," .
                "RSPEC.author_id,RSPEC.creation_ts,RSPEC.modifier_id," .
                "RSPEC.modification_ts, NH.parent_id, NH.name AS title";
    
    $tcase_mgr = new testcase($this->db);
   	$tsuite_mgr = new testsuite($this->db);

  	$req_cfg = config_get('req_cfg');
  	$field_size = config_get('field_size');
 	
  	$auto_testsuite_name = $req_cfg->default_testsuite_name;
    $node_descr_type=$this->tree_mgr->get_available_node_types();
    $empty_steps = null;
    $empty_preconditions = ''; // fix for BUGID 2995
    
    $labels['tc_created'] = lang_get('tc_created');

  	$output = null;
  	$reqSet = is_array($mixIdReq) ? $mixIdReq : array($mixIdReq);
  	
  	/* contribution BUGID 2996, testcase creation */
    if( is_null($tproject_id) || $tproject_id == 0 )
    {
  		$tproject_id = $this->tree_mgr->getTreeRoot($srs_id);
  	}
  	
  	if ( $req_cfg->use_req_spec_as_testsuite_name ) 
  	{
 		$full_path = $this->tree_mgr->get_path($srs_id);
  		$addition = " (" . lang_get("testsuite_title_addition") . ")";
  		$truncate_limit = $field_size->testsuite_name - strlen($addition);

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
  		foreach($full_path as $key => $node) 
  		{
  			// follow hierarchy of test suites to create
  			$tsuiteInfo = null;
  			$testsuite_name = substr($node['name'],0,$truncate_limit). $addition;
  			if( !$deep_create )
  			{
  				// child test suite with this name, already exists on current parent ?
  				// At first a failure we will not check anymore an proceed with deep create
  				$sql="/* $debugMsg */ SELECT id,name FROM {$this->tables['nodes_hierarchy']} NH " .
  	     			 " WHERE name='" . $this->db->prepare_string($testsuite_name) . "' " .
  	     			 " AND node_type_id=" . $node_descr_type['testsuite'] . 
  	     			 " AND parent_id = {$parent_id} ";
            	
            	// If returns more that one record use ALWAYS first
  				$tsuiteInfo = $this->db->fetchRowsIntoMap($sql,'id');
 			}
 			
 			if( is_null($tsuiteInfo) )
 			{
  				$tsuiteInfo = $tsuite_mgr->create($parent_id,$testsuite_name,$req_cfg->testsuite_details);
  				$output[] = sprintf(lang_get('testsuite_name_created'), $testsuite_name);
  				$deep_create = true;
 			}
 			else
 			{
 				$tsuiteInfo = current($tsuiteInfo);
 				$tsuite_id = $tsuiteInfo['id'];
 			}
			$tsuite_id = $tsuiteInfo['id'];  // last value here will be used as parent for test cases
 			$parent_id = $tsuite_id;
  		}
  		$output[]=sprintf(lang_get('created_on_testsuite'), $testsuite_name);
  	} 
  	else 
  	{
  		// don't use req_spec as testsuite name
  		// Warning:
  		// We are not maintaining hierarchy !!!
  		$sql=" SELECT id FROM {$this->tables['nodes_hierarchy']} NH " .
  		     " WHERE name='" . $this->db->prepare_string($auto_testsuite_name) . "' " .
  		     " AND parent_id=" . $testproject_id . " " .
  	    	 " AND node_type_id=" . $node_descr_type['testsuite'];
  
  		$result = $this->db->exec_query($sql);
    	if ($this->db->num_rows($result) == 1) {
    		$row = $this->db->fetch_array($result);
    		$tsuite_id = $row['id'];
    		$label = lang_get('created_on_testsuite');
    	} else {
    		// not found -> create
	    	tLog('test suite:' . $auto_testsuite_name . ' was not found.');
	    	$new_tsuite=$tsuite_mgr->create($testproject_id,$auto_testsuite_name,$req_cfg->testsuite_details);
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
    if( !is_null($siblings) )
    {
    	$dummy = end($siblings);
    	$testcase_order = $dummy['node_order'];
    }
    
  	foreach ($reqSet as $reqID)
  	{
        $reqData = $this->get_by_id($reqID,requirement_mgr::LATEST_VERSION);
        $count = (!is_null($tc_count)) ? $tc_count[$reqID] : 1;
        $reqData = $reqData[0];
        
        // Generate name with progessive
        $instance=1;
     	$getOptions = array('check_criteria' => 'like','access_key' => 'name');
        $itemSet = $tcase_mgr->getDuplicatesByName($reqData['title'],$tsuite_id,$getOptions);	
        $nameSet = null;
        if( !is_null($itemSet) )
        {
        	$nameSet = array_flip(array_keys($itemSet));
        }
        for ($idx = 0; $idx < $count; $idx++) 
        {
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
            if( !is_null($nameSet) )
            {
            	while( isset($nameSet[$tcase_name]) )
            	{
            		$instance++;
            		$tcase_name = $reqData['title'] . " [{$instance}]"; 
            	}
            }        
            $nameSet[$tcase_name]=$tcase_name;
            
            // 20100106 - franciscom - multiple test case steps feature - removed expected_results
	        // Julian - BUGID 2995
	  	    $tcase = $tcase_mgr->create($tsuite_id,$tcase_name,$req_cfg->testcase_summary_prefix . $reqData['scope'] ,
						                $empty_preconditions, $empty_steps,$user_id,null,
						                $testcase_order,testcase::AUTOMATIC_ID,TESTCASE_EXECUTION_TYPE_MANUAL,
						                $testcase_importance_default,$createOptions);
	        
	        $tcase_name = $tcase['new_name'] == '' ? $tcase_name : $tcase['new_name'];
	        $output[]=sprintf($labels['tc_created'], $tcase_name);

	  		// create coverage dependency
	  		if (!$this->assign_to_tcase($reqData['id'],$tcase['id']) ) 
	  		{
	  			$output[] = 'Test case: ' . $tcase_name . " was not created";
	  		}
        }
  	}
  	return $output;
  }


  /*
    function: assign_to_tcase
              assign requirement(s) to test case

    args: req_id: can be an array of requirement id
          testcase_id

    returns: 1/0
    
    rev:  20090401 - franciscom - BUGID 2316 - refactored

  */
  function assign_to_tcase($req_id,$testcase_id)
  {
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

  	$output = 0;
    if($testcase_id && $req_id)
    {
        $items = (array)$req_id;
  	    $in_clause = implode(",",$items);
    	$sql = " /* $debugMsg */ SELECT req_id,testcase_id FROM {$this->tables['req_coverage']} " .
  		       " WHERE req_id IN ({$in_clause}) AND testcase_id = {$testcase_id}";
 	    $coverage = $this->db->fetchRowsIntoMap($sql,'req_id');
   	    
  	    $loop2do=count($items);
   	    for($idx=0; $idx < $loop2do; $idx++)
   	    {
   	        if( is_null($coverage) || !isset($coverage[$items[$idx]]) )
   	        {
   	            $sql = "INSERT INTO {$this->tables['req_coverage']} (req_id,testcase_id) " .
         	           "VALUES ({$items[$idx]},{$testcase_id})";
           	    $result = $this->db->exec_query($sql);
         	    if ($this->db->affected_rows() == 1)
         	    {
        	        $output = 1;
         	        $tcInfo = $this->tree_mgr->get_node_hierarchy_info($testcase_id);
        	        $reqInfo = $this->tree_mgr->get_node_hierarchy_info($items[$idx]);
        	        if($tcInfo && $reqInfo)
        	        {
        	    	    logAuditEvent(TLS("audit_req_assigned_tc",$reqInfo['name'],$tcInfo['name']),
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




  /*
    function: unassign_from_tcase

    args: req_id
          testcase_id

    returns:

  */
	function unassign_from_tcase($req_id,$testcase_id)
	{
		$output = 0;
		$sql = " DELETE FROM {$this->tables['req_coverage']} " .
		     " WHERE req_id={$req_id} " .
		     " AND testcase_id={$testcase_id}";
	
		$result = $this->db->exec_query($sql);
	
		if ($result && $this->db->affected_rows() == 1)
		{
			$tcInfo = $this->tree_mgr->get_node_hierarchy_info($testcase_id);
			$reqInfo = $this->tree_mgr->get_node_hierarchy_info($req_id);
			if($tcInfo && $reqInfo)
			{
				logAuditEvent(TLS("audit_req_assignment_removed_tc",$reqInfo['name'],
				                  $tcInfo['name']),"ASSIGN",$this->object_table);
			}
			$output = 1;
		}
		return $output;
	}

  /*
    function: bulk_assignment
              assign N requirements to M test cases
              Do not write audit info              

    args: req_id: can be an array
          testcase_id: can be an array

    returns: number of assignments done


  */
  function bulk_assignment($req_id,$testcase_id)
  {
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $insertCounter=0;  // just for debug
  	$requirementSet=$req_id;
  	$tcaseSet=$testcase_id;
  	
    if( !is_array($req_id) )
    {
       $requirementSet=array($req_id);  
    }
    if( !is_array($testcase_id) )
    {
       $tcaseSet=array($testcase_id);  
    }

    $req_list=implode(",",$requirementSet);
    $tcase_list=implode(",",$tcaseSet);
    
    // Get coverage for this set of requirements and testcase, to be used
    // to understand if insert if needed
    $sql = " /* $debugMsg */ SELECT req_id,testcase_id FROM {$this->tables['req_coverage']} " .
  		   " WHERE req_id IN ({$req_list}) AND testcase_id IN ({$tcase_list})";
    $coverage = $this->db->fetchMapRowsIntoMap($sql,'req_id','testcase_id');
   
   
  	$insert_sql = "INSERT INTO {$this->tables['req_coverage']} (req_id,testcase_id) ";
  	foreach($tcaseSet as $tcid)
  	{
  	    foreach($requirementSet as $reqid)
  	    {
            if( !isset($coverage[$reqid][$tcid]) )
            {
                $insertCounter++;
  	            $sql = $insert_sql . "VALUES ({$reqid},{$tcid})";
                $this->db->exec_query($sql);
            }    
  	    }
  	}
  	return $insertCounter;
  }


  /*
    function: get_relationships

    args :

    returns:

  */
  function get_relationships($req_id)
  {
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
function exportReqToXML($id,$tproject_id=null)
{            
 	$rootElem = "{{XMLCODE}}";
	$elemTpl = "\t" .   "<requirement>" .
	           "\n\t\t" . "<docid><![CDATA[||DOCID||]]></docid>" .
	           "\n\t\t" . "<title><![CDATA[||TITLE||]]></title>" .
	           "\n\t\t" . "<node_order><![CDATA[||NODE_ORDER||]]></node_order>".
			   "\n\t\t" . "<description><![CDATA[\n||DESCRIPTION||\n]]></description>".
			   "\n\t\t" . "<status><![CDATA[||STATUS||]]></status>" .
			   "\n\t\t" . "<type><![CDATA[||TYPE||]]></type>" .
			   "\n\t\t" . "<expected_coverage><![CDATA[||EXPECTED_COVERAGE||]]></expected_coverage>" .			   
			   "\n\t" . "</requirement>" . "\n";
					   
	$info = array (	"||DOCID||" => "req_doc_id",
							    "||TITLE||" => "title",
							    "||DESCRIPTION||" => "scope",
							    "||STATUS||" => "status",
							    "||TYPE||" => "type",
							    "||NODE_ORDER||" => "node_order",
							    "||EXPECTED_COVERAGE||" => "expected_coverage",
							    );
	
	$req = $this->get_by_id($id,requirement_mgr::LATEST_VERSION);
	$reqData[] = $req[0]; 
	$xmlStr=exportDataToXML($reqData,$rootElem,$elemTpl,$info,true);						    
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
	return $dummy;
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


  rev :
       20070302 - check for $id not null, is not enough, need to check is > 0

*/
function get_linked_cfields($id,$parent_id=null)
{
	$enabled = 1;
	if (!is_null($id) && $id > 0)
	{
    	$req_info = $this->get_by_id($id);
    	$req_info = $req_info[0];
	  	$tproject_id = $req_info['testproject_id'];
	}
	else
	{
	  	$tproject_id = $parent_id;
	}
	$cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,null,
	                                                          'requirement',$id);

	return $cf_map;
}


/*
  function: html_table_of_custom_field_inputs
            Return html code, implementing a table with custom fields labels
            and html inputs, for choosen requirement.
            Used to manage user actions on custom fields values.


  args: $id
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

*/
function html_table_of_custom_field_inputs($id,$parent_id=null,$name_suffix='')
{
    $NO_WARNING_IF_MISSING=true;
    $cf_smarty = '';
    $cf_map = $this->get_linked_cfields($id,$parent_id);
    
    if(!is_null($cf_map))
    {
    	$cf_smarty = "<table>";
    	foreach($cf_map as $cf_id => $cf_info)
    	{
            $label=str_replace(TL_LOCALIZE_TAG,'',
                               lang_get($cf_info['label'],null,$NO_WARNING_IF_MISSING));
    
    		$cf_smarty .= '<tr><td class="labelHolder">' . htmlspecialchars($label) . ":</td><td>" .
    			          $this->cfield_mgr->string_custom_field_input($cf_info,$name_suffix) .
    					  "</td></tr>\n";
    	}
    	$cf_smarty .= "</table>";
    }
    return $cf_smarty;
}


/*
  function: html_table_of_custom_field_values
            Return html code, implementing a table with custom fields labels
            and custom fields values, for choosen requirement.
            You can think of this function as some sort of read only version
            of html_table_of_custom_field_inputs.


  args: $id

  returns: html string

*/
function html_table_of_custom_field_values($id)
{
    $NO_WARNING_IF_MISSING=true;
	$PID_NO_NEEDED = null;
	$cf_smarty = '';

	$cf_map = $this->get_linked_cfields($id,$PID_NO_NEEDED);

	if(!is_null($cf_map))
	{
		foreach($cf_map as $cf_id => $cf_info)
		{
			// if user has assigned a value, then node_id is not null
			if($cf_info['node_id'])
			{
				$label = str_replace(TL_LOCALIZE_TAG,'',
	                                 lang_get($cf_info['label'],null,$NO_WARNING_IF_MISSING));

				$cf_smarty .= '<tr><td class="labelHolder">' .
								htmlspecialchars($label) . ":</td><td>" .
								$this->cfield_mgr->string_custom_field_value($cf_info,$id) .
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

	    $my['options'] = array( 'check_criteria' => '=', 'access_key' => 'id', 
	                            'case' => 'sensitive');
	    $my['options'] = array_merge($my['options'], (array)$options);
    	
    	$fields2get="REQ.id,REQ.srs_id,REQ.req_doc_id,NH_REQ.name AS title";
    	   
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
  		
		$sql = " /* $debugMsg */ SELECT {$fields2get}, REQ_SPEC.testproject_id, " .
		       " NH_RSPEC.name AS req_spec_title, REQ_SPEC.doc_id AS req_spec_doc_id, NH_REQ.node_order " .
		       " FROM {$this->object_table} REQ " .
		       " /* Get Req info from NH */ " .
		       " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = REQ.id " .
		       " JOIN {$this->tables['req_specs']} REQ_SPEC ON REQ_SPEC.id = REQ.srs_id " .
		       " JOIN {$this->tables['nodes_hierarchy']} NH_RSPEC ON NH_RSPEC.id = REQ_SPEC.id " .
		       " WHERE REQ.req_doc_id {$check_criteria} ";

  		
  		if( !is_null($tproject_id) )
  		{
  			$sql .= " AND REQ_SPEC.testproject_ID={$tproject_id}";
  		}
    	
  		if( !is_null($parent_id) )
  		{
  			$sql .= " AND REQ.srs_id={$parent_id}";
  		}

  		$output = $this->db->fetchRowsIntoMap($sql,$my['options']['access_key']);
  		
  		return $output;
  	}

	/**
	 * Copy a requirement to a new requirement specification
	 * requirement DOC ID will be changed because must be unique inside
	 * MASTER CONATAINER (test project)
	 * 
	 * @param integer $id: requirement ID
	 * @param integer $parent_id: target req spec id (where we want to copy)
	 * @param integer $user_id: who is requesting copy
	 * @param integer $tproject_id: optional, is null will be computed here
	 * @param array $options: map
	 *
	 */
	function copy_to($id,$parent_id,$user_id,$tproject_id=null,$options=null)
	{
	    $new_item = array('id' => -1, 'status_ok' => 0, 'msg' => 'ok');
	    $my['options'] = array();
	    $my['options'] = array_merge($my['options'], (array)$options);
    
   	    if( is_null($my['options']['copy_also']) )
	    {
	        $my['options']['copy_also'] = array('testcase_assignment' => true);   
	    }

		$root = $tproject_id;
		if( !is_null($root) )
		{
			$reqSpecMgr = new requirement_spec_mgr($this->db);
			$target = $reqSpecMgr->get_by_id($parent_id);
			$root = $target['testproject_id'];
		}
        $target_doc = $this->generateDocID($id,$root);		

		$item_versions = $this->get_by_id($id);
		if($item_versions)
		{
			$new_item = $this->create_req_only($parent_id,$target_doc,$item_versions[0]['title'],
			                                   $item_versions[0]['author_id'],$item_versions[0]['node_order']);
			if ($new_item['status_ok'])
			{
		        $ret['status_ok']=1;
	 			foreach($item_versions as $req_version)
				{
					$op = $this->create_version($new_item['id'],$req_version['version'],
					                            $req_version['scope'],$req_version['author_id'],
					                            $req_version['status'],$req_version['type'],
					                            $req_version['expected_coverage']);
				}
				
				$this->copy_cfields($id,$new_item['id']);
	        	$this->copy_attachments($id,$new_item['id']);
		    	if( isset($my['options']['copy_also']['testcase_assignment']) &&
		    	    $my['options']['copy_also']['testcase_assignment'] )
				{
	        	 $linked_items = $this->get_coverage($id);
            	 if( !is_null($linked_items) )
            	 {
            	 	foreach($linked_items as $value)
            	 	{
            	 		$this->assign_to_tcase($new_item['id'],$value['id']);
            	 	}
            	 }	            
				}
			}
		}

		return($new_item);
	}


    /**
     * 
     *
     */
	function copy_attachments($source_id,$target_id)
	{
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
	function copy_cfields($from_id,$to_id)
	{
	  $cfmap_from=$this->get_linked_cfields($from_id);
	  $cfield=null;
	  if( !is_null($cfmap_from) )
	  {
	    foreach($cfmap_from as $key => $value)
	    {
	      $cfield[$key]=array("type_id"  => $value['type'], "cf_value" => $value['value']);
	    }
	  }
	  $this->cfield_mgr->design_values_to_db($cfield,$to_id,null,'tcase_copy_cfields');
	}



    /**
	 * 
 	 *
 	 */
	function generateDocID($id, $tproject_id)
	{
		$field_size = config_get('field_size');
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
			// req_doc_id has limited size then we need to be sure that generated id will
			// not exceed DB size
    	    $nameSet = array_flip(array_keys($itemSet));
		    // 6 magic from " [xxx]"
		    $prefix = trim_and_limit($item_info['req_doc_id'],$field_size->req_docid-6);
    	    $target_doc = $prefix . " [{$instance}]"; 
    		while( isset($nameSet[$target_doc]) )
    		{
    			$instance++;
    	    	$target_doc = $prefix . " [{$instance}]"; 
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
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$result = array( 'id' => -1, 'status_ok' => 0, 'msg' => 'ok');
		$req_id = $this->tree_mgr->new_node($srs_id,$this->node_types_descr_id['requirement'],
		                                    $title,$node_order);
		$db_now = $this->db->db_now();
		$sql = "/* $debugMsg */ INSERT INTO {$this->object_table} " .
		       " (id, srs_id, req_doc_id)" .
    	       " VALUES ({$req_id}, {$srs_id},'" . $this->db->prepare_string($reqdoc_id) . "')";
		  	   
    	if (!$this->db->exec_query($sql))
    	{
		 	  $result['msg'] = lang_get('error_inserting_req');
		}
		else
		{
			$result = array( 'id' => $req_id, 'status_ok' => 1, 'msg' => 'ok');
		}
    	return $result;
    }

	/*
	  function: create_version
	
	  args:
	
	  returns:
	
	  rev: 20080113 - franciscom - interface changes added tc_ext_id
	
	*/
	function create_version($id,$version,$scope, $user_id, $status = TL_REQ_STATUS_VALID, 
	                        $type = TL_REQ_TYPE_INFO, $expected_coverage=1)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$item_id = $this->tree_mgr->new_node($id,$this->node_types_descr_id['requirement_version']);
	    
		$sql = "/* $debugMsg */ INSERT INTO {$this->tables['req_versions']} " .
		       " (id,version,scope,status,type,expected_coverage,author_id,creation_ts) " . 
	  	       " VALUES({$item_id},{$version},'" . $this->db->prepare_string($scope) . "','" . 
	  	       $this->db->prepare_string($status) . "','" . $this->db->prepare_string($type) . "'," .
	  	       "{$expected_coverage},{$user_id}," . $this->db->db_now() . ")";
	  	       
		$result = $this->db->exec_query($sql);
		$ret = array( 'msg' => 'ok', 'id' => $item_id, 'status_ok' => 1);
		if (!$result)
		{
			$ret['msg'] = $this->db->error_msg();
		  	$ret['status_ok']=0;
		  	$ret['id']=-1;
		}
		return $ret;
	}
	
	/*
	  function: create_new_version()
	            create a new version, doing a copy of last version.
	
	  args : $id: requirement id
	         $user_id: who is doing this operation.
	
	  returns:
	          map:  id: node id of created tcversion
	                version: version number (i.e. 5)
	                msg
	
	  rev: 
	*/
	function create_new_version($id,$user_id)
	{
	  // get a new id
	  $version_id = $this->tree_mgr->new_node($id,$this->node_types_descr_id['requirement_version']);
	  $last_version_info =  $this->get_last_version_info($id);
	  $this->copy_version($last_version_info['id'],$version_id,$last_version_info['version']+1,$user_id);
	
	  $ret['id'] = $version_id;
	  $ret['version'] = $last_version_info['version']+1;
	  $ret['msg'] = 'ok';
	  return $ret;
	}


    /**
	 * 
 	 *
     */
	function get_last_version_info($id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$info = null;
	
		$sql = " /* $debugMsg */ SELECT MAX(version) AS version " .
		       " FROM {$this->tables['req_versions']} REQV," .
		       " {$this->tables['nodes_hierarchy']} NH WHERE ".
		       " NH.id = REQV.id ".
		       " AND NH.parent_id = {$id} ";
	
		$max_version = $this->db->fetchFirstRowSingleColumn($sql,'version');
		if ($max_version)
		{
			$sql = "/* $debugMsg */ SELECT REQV.* FROM {$this->tables['req_versions']} REQV," .
			       " {$this->tables['nodes_hierarchy']} NH ".
			       " WHERE version = {$max_version} AND NH.id = REQV.id AND NH.parent_id = {$id}";
	
			$info = $this->db->fetchFirstRow($sql);
		}
		return $info;
	}

    /**
	 * 
 	 *
     */
	function copy_version($from_version_id,$to_version_id,$as_version_number,$user_id)
	{
		
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	    $now = $this->db->db_now();
	    $sql="/* $debugMsg */ INSERT INTO {$this->tables['req_versions']} " .
	         " (id,version,author_id,creation_ts,scope,status,type,expected_coverage) " .
	         " SELECT {$to_version_id} AS id, {$as_version_number} AS version, " .
	         "        {$user_id} AS author_id, {$now} AS creation_ts," .
	         "        scope,status,type,expected_coverage " .
	         " FROM {$this->tables['req_versions']} " .
	         " WHERE id={$from_version_id} ";
	    $result = $this->db->exec_query($sql);
	}

    /**
	 * 
 	 *
     */
	function updateOpen($reqVersionID,$value)
	{
		$this->updateBoolean($reqVersionID,'is_open',$value);
	}	

    /**
	 * 
 	 *
     */
	function updateActive($reqVersionID,$value)
	{
		$this->updateBoolean($reqVersionID,'active',$value);
	}	

    /**
	 * 
 	 *
     */
	private function updateBoolean($reqVersionID,$field,$value)
	{
		$booleanValue = $value;
	    if( is_bool($booleanValue) )
	    {
	    	$booleanValue = $booleanValue ? 1 : 0;
	    }
		else if( !is_numeric($booleanValue) || is_null($booleanValue))
		{
			$booleanValue = 1;
		}
		$booleanValue = $booleanValue > 0 ? 1 : 0;
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = "/* $debugMsg */ UPDATE {$this->tables['req_versions']} " .
  		       " SET {$field}={$booleanValue} WHERE id={$reqVersionID}";
	
	    $result = $this->db->exec_query($sql);
	   
	}	


} // class end
?>