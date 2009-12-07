<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: requirement_mgr.class.php,v $
 *
 * @version $Revision: 1.42 $
 * @modified $Date: 2009/12/07 18:16:12 $ by $Author: franciscom $
 * @author Francisco Mancardi
 *
 * Manager for requirements.
 * Requirements are children of a requirement specification (requirements container)
 *
 * rev:  
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

		tlObjectWithAttachments::__construct($this->db,'requirements');

		$node_types_descr_id= $this->tree_mgr->get_available_node_types();
		$node_types_id_descr=array_flip($node_types_descr_id);
		$this->my_node_type=$node_types_descr_id['requirement'];
	    $this->object_table=$this->tables['requirements'];


	}


/*
  function: get_by_id


  args: id: requirement id

  returns: null if query fails
           map with requirement info

*/
function get_by_id($id)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $field2get="REQ.id,REQ.srs_id,REQ.req_doc_id,REQ.scope,REQ.status,REQ.type,REQ.author_id," .
               "REQ.expected_coverage,REQ.creation_ts,REQ.modifier_id,REQ.modification_ts,NH_REQ.name AS title";
  
	$sql = " /* $debugMsg */ SELECT {$field2get}, REQ_SPEC.testproject_id, NH_RSPEC.name AS req_spec_title, " .
	       " NH_REQ.node_order " .
	       " FROM {$this->object_table} REQ, {$this->tables['req_specs']} REQ_SPEC," .
	       " {$this->tables['nodes_hierarchy']} NH_REQ, {$this->tables['nodes_hierarchy']} NH_RSPEC " .
	       " WHERE REQ.srs_id = REQ_SPEC.id " .
	       " AND REQ.id = NH_REQ.id " .
	       " AND REQ.srs_id = NH_RSPEC.id " .
	       " AND REQ.id = {$id}";
	       
	$recordset = $this->db->get_recordset($sql);
	
  $rs = null;
  if(!is_null($recordset))
  {
      // Decode users
      $rs = $recordset[0];
      $rs['author'] = '';
      $rs['modifier'] = '';
      if(trim($rs['author_id']) != "")
      {
          $user = tlUser::getByID($this->db,$rs['author_id']);
          // need to manage deleted users
          if($user) 
          {
              $rs['author'] = $user->getDisplayName();
          }
          else
          {
              $rs['author'] = lang_get('undefined');
          }    
      }
    
      if(trim($rs['modifier_id']) != "")
      {
          $user = tlUser::getByID($this->db,$rs['modifier_id']);
          // need to manage deleted users
          if($user) 
          {
              $rs['modifier'] = $user->getDisplayName();
          }
          else
          {
              $rs['modifier'] = lang_get('undefined');
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

    returns: map with following keys:
             status_ok -> 1/0
             msg -> some simple message, useful when status_ok ==0
             id -> id of new requirement.

	@internal revision
	20091202 - franciscom -  added contribution by asimon83/mx-julian 

  */
function create($srs_id,$reqdoc_id,$title, $scope, $user_id,
                $status = TL_REQ_STATUS_VALID, $type = TL_REQ_STATUS_NOT_TESTABLE,
                $expected_coverage=1,$node_order=0)
{
    $result['id'] = 0;
	$result['status_ok'] = 0;
	$result['msg'] = 'ko';

	$field_size = config_get('field_size');

	$reqdoc_id = trim_and_limit($reqdoc_id,$field_size->req_docid);
	$title = trim_and_limit($title,$field_size->req_title);

	$result = $this->check_basic_data($srs_id,$title,$reqdoc_id);
	if($result['status_ok'])
	{
    	/* contribution by asimon83/mx-julian */
		if (config_get('req_cfg')->internal_links) 
		{
			$tproject_id = $this->tree_mgr->getTreeRoot($srs_id);
			$scope = req_link_replace($this->db, $scope, $tproject_id);
		}
		/* end contribution by asimon83/mx-julian */
    
        $parent_id=$srs_id;
		$name=$title;
		$req_id = $this->tree_mgr->new_node($parent_id,$this->my_node_type,$name,$node_order);
		$db_now = $this->db->db_now();
		$sql = " INSERT INTO {$this->object_table} " .
		       " (id, srs_id, req_doc_id, scope, status, type, author_id, creation_ts,expected_coverage)" .
               " VALUES ({$req_id}, {$srs_id},'" . $this->db->prepare_string($reqdoc_id) . "','" .
		  	   $this->db->prepare_string($scope) . "','" .
		  	   $this->db->prepare_string($status) . "','" . $this->db->prepare_string($type) . "',"  .
		  	   "{$user_id}, {$db_now}, {$expected_coverage})";


        if (!$this->db->exec_query($sql))
        {
		 	  $result['msg'] = lang_get('error_inserting_req');
		}
		else
		{
			$result['id']=$req_id;
  	        $result['status_ok'] = 1;
	        $result['msg'] = 'ok';
		}
    }
	  return $result;
} // function end


  /*
    function: update


    args: id: requirement id
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

function update($id,$reqdoc_id,$title, $scope, $user_id, $status, $type,
                $expected_coverage,$skip_controls=0)
{
    $result['status_ok'] = 1;
    $result['msg'] = 'ok';
    
    $db_now = $this->db->db_now();
    $field_size=config_get('field_size');
    
    // get SRSid, needed to do controls
    $rs=$this->get_by_id($id);
    $srs_id=$rs['srs_id'];

    /* contribution by asimon83/mx-julian */
	if (config_get('req_cfg')->internal_links) 
	{
		$tproject_id = $this->tree_mgr->getTreeRoot($srs_id);
		$scope = req_link_replace($this->db, $scope, $tproject_id);
	}
	/* end contribution by asimon83/mx-julian */
    
	$reqdoc_id=trim_and_limit($reqdoc_id,$field_size->req_docid);
	$title=trim_and_limit($title,$field_size->req_title);
    $chk=$this->check_basic_data($srs_id,$title,$reqdoc_id,$id);

    if($chk['status_ok'] || $skip_controls)
	{
	  	$sql = "UPDATE requirements SET scope='" . $this->db->prepare_string($scope) . "', " .
	  	       " status='" . $this->db->prepare_string($status) . "', " .
	  	       " type='" . $this->db->prepare_string($type) . "', " .
	  	       " modifier_id={$user_id}, req_doc_id='" . $this->db->prepare_string($reqdoc_id) . "', " .
	  	       " modification_ts={$db_now}, expected_coverage={$expected_coverage}  " . 
	  	       " WHERE id={$id}";

	  	if ($this->db->exec_query($sql))
	  	{
        // need to update node on tree
  		  $sql = " UPDATE {$this->tables['nodes_hierarchy']} " .
  		         " SET name='" . $this->db->prepare_string($title) . "'" .
  		         " WHERE id={$id}";

  		  if (!$this->db->exec_query($sql))
  		  {
  			    $result['msg']=lang_get('error_updating_req');
    	        $result['status_ok'] = 0;
    	  }
	  	}
        else
	  	{
	  	   $result['status_ok']=0;
	  	   $result['msg'] = lang_get('error_updating_req');
	    }  // else
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
	function delete($id)
 	{
		  $where_clause_coverage = '';
	  	$where_clause_this = '';
	
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
	
	    // Delete Custom fields
	    $this->cfield_mgr->remove_all_design_values_from_node($id);
	
	  	// delete dependencies with test specification
	  	$sql = "DELETE FROM {$this->tables['req_coverage']} " . $where_clause_coverage;
	  	$result = $this->db->exec_query($sql);
	
		  if ($result)
	  	{
	  	    if(is_array($id))
	  	  	{
	  	    	$the_ids = $id;
	  		  }
	      	else
	      	{
	        	$the_ids = array($id);
	      	}
	
			    foreach($the_ids as $key => $value)
			    {
			    	$result = $this->attachmentRepository->deleteAttachmentsFor($value,"requirements");
			    }
	    }

	  	if ($result)
	  	{
	  		$sql = "DELETE FROM {$this->object_table} " . $where_clause_this;
	  		$result = $this->db->exec_query($sql);
	  	}

	  	if ($result)
	  	{
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
  function check_basic_data($srs_id,$title,$reqdoc_id,$id = null)
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

  		if($req_cfg->reqdoc_id->is_system_wide)
  		{
  			// req doc id MUST BE unique inside the whole DB
        	$my_srs_id = null;
  		}
  		$rs = $this->get_by_docid($reqdoc_id,$my_srs_id);


  		if(!is_null($rs) && (is_null($id) || !isset($rs[$id])))
  		{
  			$ret['msg'] = lang_get("warning_duplicate_reqdoc_id");
  			$ret['status_ok'] = 0;
  		}
  	}

  	return $ret;
  }



/**
 * get_by_docid
 *
 */
function get_by_docid($reqdoc_id,$srs_id=null)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $fields2get="REQ.id,REQ.srs_id,REQ.req_doc_id,REQ.scope,REQ.status,type,REQ.expected_coverage," .
                "NH.node_order,REQ.author_id,REQ.creation_ts,REQ.modifier_id," .
                "REQ.modification_ts,NH.name AS title";

	$sql = "SELECT {$fields2get} FROM {$this->object_table} REQ, {$this->tables['nodes_hierarchy']} NH " .
	       " WHERE req_doc_id='" . $this->db->prepare_string($reqdoc_id) . "'"  .
	       " AND REQ.id = NH.id ";

    if( !is_null($srs_id) )
    {
        $sql .=	 " AND srs_id={$srs_id}";
    }
	return($this->db->fetchRowsIntoMap($sql,'id'));
}

  /*
    function: get_by_title

    args:

    returns:

  */
function get_by_title($title,$ignore_case=0)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $fields2get="REQ.id,REQ.srs_id,REQ.req_doc_id,REQ.scope,REQ.status,REQ.type,NH.node_order,"  .
                "REQ.expected_coverage,REQ.author_id,REQ.creation_ts,REQ.modifier_id," .
                "REQ.modification_ts,NH.name AS title";

  	$output = array();
    $sql = " /* $debugMsg */ SELECT {$fields2get} FROM {$this->object_table} REQ, " .
  	       "{$this->tables['nodes_hierarchy']} NH ";

    $the_title=$this->db->prepare_string($title);
  	if($ignore_case)
  	{
  	  $sql .= " WHERE UPPER(title)='" . strupper($the_title) . "'";
  	}
  	else
  	{
  	   $sql .= " WHERE title='{$the_title}'";
  	}
  	$sql .= " AND REQ.id = NH.id";


  	$result = $this->db->exec_query($sql);
  	if (!empty($result)) 
  	{
  		$output = $this->db->fetch_array($result);
  	}

  	return $output;
} // function end



  /*
    function: create_tc_from_requirement
              create testcases using requirements as input


    args:

    returns:

  */
function create_tc_from_requirement($mixIdReq,$srs_id, $user_id)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $fields2get="RSPEC.id,testproject_id,RSPEC.scope,RSPEC.total_req,RSPEC.type," .
                "RSPEC.author_id,RSPEC.creation_ts,RSPEC.modifier_id," .
                "RSPEC.modification_ts,NH.name AS title";
    
    $tcase_mgr = new testcase($this->db);
  	$req_cfg = config_get('req_cfg');
  	$field_size = config_get('field_size');
  	$auto_testsuite_name = $req_cfg->default_testsuite_name;
    $node_descr_type=$this->tree_mgr->get_available_node_types();
    $empty_steps='';
    $empty_results='';


  	$output = null;
  	if (is_array($mixIdReq)) {
  		$arrIdReq = $mixIdReq;
  	} else {
  		$arrIdReq = array($mixIdReq);
  	}
  	if ( $req_cfg->use_req_spec_as_testsuite_name )
  	{
  	    // SRS Title
        $sql = " /* $debugMsg */ SELECT {$fields2get} FROM {$this->tables['req_specs']} RSPEC," .
  	           " {$this->tables['nodes_hierarchy']} NH " .
               " WHERE RSPEC.id = {$srs_id} AND RSPEC.id=NH.id ";
    	$arrSpec = $this->db->get_recordset($sql);
  	    $testproject_id=$arrSpec[0]['testproject_id'];
  	    $auto_testsuite_name = substr($arrSpec[0]['title'],0,$field_size->testsuite_name);
  	}

  	// find container with the following characteristics:
  	// 1. testproject_id is its father
  	// 2. has the searched name
  	$sql=" /* $debugMsg */ SELECT id FROM {$this->tables['nodes_hierarchy']} NH " .
  	     " WHERE name='" . $this->db->prepare_string($auto_testsuite_name) . "' " .
  	     " AND parent_id=" . $testproject_id . " " .
  	     " AND node_type_id=" . $node_descr_type['testsuite'];


  	$result = $this->db->exec_query($sql);
    if ($this->db->num_rows($result) == 1) 
    {
  		$row = $this->db->fetch_array($result);
  		$tsuite_id = $row['id'];
        $output[]=sprintf(lang_get('created_on_testsuite'), $auto_testsuite_name);
  	}
  	else 
  	{
  		// not found -> create
  		tLog('test suite:' . $auto_testsuite_name . ' was not found.');
      $tsuite_mgr=New testsuite($this->db);
      $new_tsuite=$tsuite_mgr->create($testproject_id,$auto_testsuite_name,$req_cfg->testsuite_details);
      $tsuite_id=$new_tsuite['id'];
      $output[]=sprintf(lang_get('testsuite_name_created'), $auto_testsuite_name);
   	}

  	//create TC
  	foreach ($arrIdReq as $execIdReq)
  	{
        $reqData = $this->get_by_id($execIdReq);
  	    $tcase=$tcase_mgr->create($tsuite_id,$reqData['title'],
  	                              $req_cfg->testcase_summary_prefix . $reqData['scope'] ,
  	                              $empty_steps,$empty_results,$user_id,null,
  	                              DEFAULT_TC_ORDER,AUTOMATIC_ID,
  		                          config_get('check_names_for_duplicates'),
  		                          config_get('action_on_duplicate_name'));

        $tcase_name=$tcase['new_name'];
        if( $tcase_name == '' )
        {
            $tcase_name=$reqData['title'];
        }
        $output[]=sprintf(lang_get('tc_created'), $tcase_name);

  		// create coverage dependency
  		if (!$this->assign_to_tcase($reqData['id'],$tcase['id']) ) 
  		{
  			$output[] = 'Test case: ' . $reqData['title'] . "was not created";
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
         	        $tcInfo = $this->tree_mgr->get_node_hierachy_info($testcase_id);
        	        $reqInfo = $this->tree_mgr->get_node_hierachy_info($items[$idx]);
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
			$tcInfo = $this->tree_mgr->get_node_hierachy_info($testcase_id);
			$reqInfo = $this->tree_mgr->get_node_hierachy_info($req_id);
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
					   "\n\t" . "</requirement>" . "\n";
					   
	$info = array (	"||DOCID||" => "req_doc_id",
							    "||TITLE||" => "title",
							    "||DESCRIPTION||" => "scope",
							    "||STATUS||" => "status",
							    "||TYPE||" => "type",
							    "||NODE_ORDER||" => "node_order",
							    );
	
	$reqData[] = $this->get_by_id($id);
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


} // class end
?>