<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: requirement_spec_mgr.class.php,v $
 *
 * @version $Revision: 1.87.2.1 $
 * @modified $Date: 2010/11/09 11:11:09 $ by $Author: asimon83 $
 * @author Francisco Mancardi
 *
 * Manager for requirement specification (requirement container)
 *
 * @internal revision:  
 *  20110223 - asimon BUGID 4239: wrong last parameter of a function call in copy_to()  
 *                                caused duplicated links between reqs and testcases when copying testproject
 *  20101109 - asimon - BUGID 3989: now it is configurable if custom fields without values are shown
 *	20100908 - franciscom - BUGID 3762 Import Req Spec - custom fields values are ignored
 *							createFromXML()
 * 
 *	20100320 - franciscom - xmlToMapReqSpec() added attributes: type,total_req 
 *	20100311 - franciscom - fixed bug due to missed isset() control
 *  20100307 - amitkhullar - small bug fix for Requirements based report.
 *  20100209 - franciscom - changes in delete_subtree_objects() call due to BUGID 3147 
 * 	20091228 - franciscom - get_requirements() - refactored to manage req versions
 *                          get_coverage() - refactored to manage req versions
 * 	20091225 - franciscom - new method - generateDocID()
 * 	20091223 - franciscom - new method - copy_to() + changes to check_main_data()
 *  20091209 - asimon     - contrib for testcase creation, BUGID 2996
 *	20091202 - franciscom - create(), update() 
 *                          added contribution by asimon83/mx-julian that creates
 *                          links inside scope field.
 *
 *  20091122 - franciscom - new methods getByDocID(), check_main_data()
 *	20091119 - franciscom - added doc_id management
 *
 *  20090525 - franciscom - avoid getDisplayName() crash due to deleted user 
 *  20090514 - franciscom - BUGID 2491
 *  20090427 - amitkhullar- BUGID : 2439 Modified query to handle lower case status codes.
 *  20090322 - franciscom - create() - added node_order.
 *                          check_title() - improvements now manages test project id and parent id.
 *                          get_by_title() - improvements now manages test project id and parent id.
 *
 *  20090322 - franciscom - xmlToMapReqSpec()
 *  20090321 - franciscom - added customFieldValuesAsXML() to improve exportReqSpecToXML()
 *
 *  20090315 - franciscom - added delete_deep();
 *
 *  20090222 - franciscom - added getReqTree(), get_by_id() added node_order in result
 *                          exportReqSpecToXML() (will be available on TL 1.9)
 *
 *  20090111 - franciscom - BUGID 1967 - html_table_of_custom_field_inputs()
 *                                       get_linked_cfields()
 *
*/
require_once( dirname(__FILE__) . '/attachments.inc.php' );
require_once( dirname(__FILE__) . '/requirements.inc.php' );

class requirement_spec_mgr extends tlObjectWithAttachments
{
  const CASE_SENSITIVE=0;
  const CASE_INSENSITIVE=1;
  
  var $db;
  var $cfield_mgr;
  var $tree_mgr;
  
  var $import_file_types = array("XML" => "XML");
  var $export_file_types = array("XML" => "XML");
  var $my_node_type;
  var $node_types_descr_id;
  var $node_types_id_descr;
  var $attachmentTableName;

  /*
    contructor

    args: db: reference to db object

    returns: instance of requirement_spec_mgr

  */
	function __construct(&$db)
	{
		$this->db = &$db;
		$this->cfield_mgr = new cfield_mgr($this->db);
		$this->tree_mgr =  new tree($this->db);

		$this->node_types_descr_id = $this->tree_mgr->get_available_node_types();
		$this->node_types_id_descr = array_flip($this->node_types_descr_id);
		$this->my_node_type = $this->node_types_descr_id['requirement_spec'];

        $this->attachmentTableName = 'req_specs';
		tlObjectWithAttachments::__construct($this->db,$this->attachmentTableName);
	    $this->object_table=$this->tables['req_specs'];
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
    function: create

    args:
          tproject_id:  requirement spec parent (till we will manage unlimited tree depth)
          parent_id:
          doc_id
          title
          scope
          countReq
          user_id: requirement spec author
          [type]
          [node_order]
          [options]

    returns: map with following keys:
             status_ok -> 1/0
             msg -> some simple message, useful when status_ok ==0
             id -> id of requirement specification

    rev:
    	20091202 - franciscom -  added contribution by asimon83/mx-julian 
    	20080830 - franciscom -  added new argument parent_id
        20080318 - franciscom - removed code to get last inserted id

  */
function create($tproject_id,$parent_id,$doc_id,$title, $scope,$countReq,$user_id, 
				$type = TL_REQ_SPEC_TYPE_FEATURE,$node_order=null, $options=null)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $result=array('status_ok' => 0, 'msg' => 'ko', 'id' => 0);
    $title=trim($title);
    $chk=$this->check_main_data($title,$doc_id,$tproject_id,$parent_id);
    $result['msg']=$chk['msg'];
    
	$my['options'] = array( 'actionOnDuplicate' => "block");
	$my['options'] = array_merge($my['options'], (array)$options);

    if ($chk['status_ok'])
    {
    	/* contribution by asimon83/mx-julian */
		if( config_get('internal_links')->enable ) 
		{
			$scope = req_link_replace($this->db, $scope, $tproject_id);
		}
		/* end contribution by asimon83/mx-julian */

    	
        $req_spec_id = $this->tree_mgr->new_node($parent_id,$this->my_node_type,$title,$node_order);
        $sql = "/* $debugMsg */ INSERT INTO {$this->object_table} " .
			   " (id, testproject_id, doc_id, scope, type, total_req, author_id, creation_ts) " .
               " VALUES (" . $req_spec_id . "," . $tproject_id . ",'" . 
               $this->db->prepare_string($doc_id) . "','" .
               $this->db->prepare_string($scope) .  "','" . $this->db->prepare_string($type) . "','" .
               $this->db->prepare_string($countReq) . "'," . $user_id . ", " . $this->db->db_now() . ")";
            
            
        if (!$this->db->exec_query($sql))
        {
        	$result['msg']=lang_get('error_creating_req_spec');
        }
        else
        {
		    $result['id']=$req_spec_id;
            $result['status_ok'] = 1;
		    $result['msg'] = 'ok';
		}
	}
    return $result;
}


/*
  function: get_by_id


  args : id: requirement spec id

  returns: null if query fails
           map with requirement spec info

*/
function get_by_id($id)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " . 
    	   " SELECT '' AS author, '' AS modifier, NH.node_order, " .
    	   " RSPEC.id,testproject_id,RSPEC.scope,RSPEC.total_req,RSPEC.type," .
           " RSPEC.author_id,RSPEC.creation_ts,RSPEC.modifier_id," .
           " RSPEC.modification_ts,NH.name AS title,RSPEC.doc_id " .
    	   " FROM {$this->object_table} RSPEC,  {$this->tables['nodes_hierarchy']} NH" .
    	   " WHERE RSPEC.id = NH.id " . 
    	   " AND RSPEC.id = {$id}";
       
	$recordset = $this->db->get_recordset($sql);
    $rs = null;
    if(!is_null($recordset))
    {
        // Decode users
        $rs = $recordset[0];
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



/**
 * get analyse based on requirements and test specification
 *
 * @param integer $id: Req Spec id
 * @return array Coverage in three internal arrays: covered, uncovered, nottestable REQ
 * @author martin havlat
 */
function get_coverage($id)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $req_mgr = new requirement_mgr($this->db);
    // $statusFilter = " AND status IN('" . strtoupper(VALID_REQ)."','".VALID_REQ."') ";
    // $order_by = " ORDER BY req_doc_id,title";
	$output = array( 'covered' => array(), 'uncovered' => array(),'nottestable' => array());

    // function get_requirements($id, $range = 'all', $testcase_id = null, $options=null, $filters = null)
    $getOptions = array('order_by' => " ORDER BY req_doc_id,title");
	$getFilters = array('status' => VALID_REQ);
	$validReq = $this->get_requirements($id,'all',null,$getOptions,$getFilters);
	
	// get not-testable requirements
	$getFilters = array('status' => NON_TESTABLE_REQ);
    $output['nottestable'] = $this->get_requirements($id,'all',null,$getOptions,$getFilters);   
      
       
	// get coverage
	if (sizeof($validReq))
	{
		foreach ($validReq as $req)
		{
			// collect TC for REQ
			$arrCoverage = $req_mgr->get_coverage($req['id']);

			if (count($arrCoverage) > 0) 
			{
				// add information about coverage
				$req['coverage'] = $arrCoverage;
				$output['covered'][] = $req;
			} 
			else 
			{
				$output['uncovered'][] = $req;
			}
		}
	}
	return $output;
}


/**
 * get requirement coverage metrics
 *
 * @param integer $srs_id
 * @return array results
 * @author havlatm
 */
function get_metrics($id)
{
	$output = array('notTestable' => 0, 'total' => 0, 'covered' => 0, 'uncovered' => 0);
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	$getFilters = array('status' => NON_TESTABLE_REQ);
    $output['notTestable'] = $this->get_requirements_count($id,'all',null,$getFilters);

	$sql = "/* $debugMsg */ SELECT count(0) AS cnt FROM {$this->tables['requirements']} WHERE srs_id={$id}";
	$output['total'] = $this->db->fetchFirstRowSingleColumn($sql,'cnt');

    //
	$sql = "/* $debugMsg */ SELECT total_req FROM {$this->object_table} WHERE id={$id}";
	$output['expectedTotal'] = $this->db->fetchFirstRowSingleColumn($sql,'total_req');
	if ($output['expectedTotal'] == 0)
	{
		$output['expectedTotal'] = $output['total'];
	}
	
	$sql = "/* $debugMsg */ SELECT DISTINCT REQ.id " .
	       " FROM {$this->tables['requirements']} REQ " .
	       " JOIN {$this->tables['req_coverage']} REQ_COV ON REQ.id=REQ_COV.req_id" .
	       " WHERE REQ.srs_id={$id} " ;
		   
	$rs = $this->db->get_recordset($sql);
	if (!is_null($rs))
	{
		$output['covered'] = count($rs);
	}
	$output['uncovered'] = $output['expectedTotal'] - $output['total'];

	return $output;
}

  /*
    function: get_all_in_testproject
              get info about all req spec defined for a testproject


    args: tproject_id
          [order_by]

    returns: null if no srs exits, or no srs exists for id
	           array, where each element is a map with req spec data.

	           map keys:
             id
             testproject_id
             title
             scope
             total_req
             type
             author_id
             creation_ts
             modifier_id
             modification_ts
  */
function get_all_in_testproject($tproject_id,$order_by=" ORDER BY title")
{
   	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	$sql = "/* $debugMsg */ " . 
	       " SELECT RSPEC.id,testproject_id,RSPEC.scope,RSPEC.total_req,RSPEC.type," .
           " RSPEC.author_id,RSPEC.creation_ts,RSPEC.modifier_id," .
           " RSPEC.modification_ts,NH.name AS title,NH.node_order " .
	       " FROM {$this->object_table} RSPEC, {$this->tables['nodes_hierarchy']} NH " .
	       " WHERE NH.id=RSPEC.id" .
	       " AND testproject_id={$tproject_id}";

    if (!is_null($order_by))
	{
	    $sql .= $order_by;
    }
	return $this->db->get_recordset($sql);
}


  /*
    function: update

    args: id
          title
          scope
          countReq
          user_id,
          [type]

    returns: map with following keys:
             status_ok -> 1/0
             msg -> some simple message, useful when status_ok ==0

  */
  function update($id,$doc_id,$title, $scope, $countReq,$user_id,
  				  $type = TL_REQ_SPEC_TYPE_FEATURE,$node_order = null)
  {
		$result['status_ok'] = 1;
	  	$result['msg'] = 'ok';

		$title=trim_and_limit($title);
    	$doc_id=trim_and_limit($doc_id);
     
    	$path=$this->tree_mgr->get_path($id); 
    	$tproject_id = $path[0]['parent_id'];
    	$last_idx=count($path)-1;
    	$parent_id = $last_idx==0 ? null : $path[$last_idx]['parent_id'];
    	$chk=$this->check_main_data($title,$doc_id,$path[0]['parent_id'],$parent_id,$id);
    
    	if ($chk['status_ok'])
    	{
    		
    		/* contribution by asimon83/mx-julian */
			if( config_get('internal_links')->enable  ) 
			{
				$scope = req_link_replace($this->db, $scope, $tproject_id);
			}
			/* end contribution by asimon83/mx-julian */
    	
    		
		    $db_now = $this->db->db_now();
    	    $sql = " UPDATE {$this->object_table} " .
    	           " SET scope='" . $this->db->prepare_string($scope) . "', " .
			       " doc_id='" . $this->db->prepare_string($doc_id) . "', " .
			       " type='" . $this->db->prepare_string($type) . "', " .
			       " total_req ='" . $this->db->prepare_string($countReq) . "', " .
			       " modifier_id={$user_id},modification_ts={$db_now} ";
			$sql .= "WHERE id={$id}";
    	    if (!$this->db->exec_query($sql))
			{
    	        $result['msg']=lang_get('error_updating_reqspec');
  		        $result['status_ok'] = 0;
		    }
    	    if( $result['status_ok'] )
    	    {
  		        // need to update node on tree
    	        $sql = " UPDATE {$this->tables['nodes_hierarchy']} " .
  		    	       " SET name='" . $this->db->prepare_string($title) . "'";
				if( !is_null($node_order) )
				{
					$sql .= ",node_order=" . intval($node_order);
				}       
  		    	$sql .= " WHERE id={$id}";
    	    
  		    	if (!$this->db->exec_query($sql))
  		    	{
  		    		$result['msg']=lang_get('error_updating_reqspec');
    	    	    $result['status_ok'] = 0;
  		        }
		    }
    	}    
		else
		{
		    $result['status_ok']=$chk['status_ok'];
		    $result['msg']=$chk['msg'];
		}
    	return $result;
}



  /*
    function: delete
              deletes:
              Requirements spec
              Requirements spec custom fields values
              Requirements ( Requirements spec children )
              Requirements custom fields values

    IMPORTANT/CRITIC: 
    This function can used to delete a Req Specification that contains ONLY Requirements.
    This function is needed by tree class method: delete_subtree_objects()
    To delete a Req Specification that contains other Req Specification delete_deep() must be used.

    args: id: requirement spec id

    returns: message string
             ok if everything is ok

  */
function delete($id)
{
    $req_mgr = new requirement_mgr($this->db);
	
	// Delete Custom fields
	$this->cfield_mgr->remove_all_design_values_from_node($id);
	$result = $this->attachmentRepository->deleteAttachmentsFor($id,"req_specs");

	// delete requirements (one type req spec children) with all related data
	// coverage, attachments, custom fields, etc
	$requirements_info = $this->get_requirements($id);
	if(!is_null($requirements_info))
	{
		$items = null;
		foreach($requirements_info as $req)
		{
			$items[] = $req["id"];
		}
		$req_mgr->delete($items);
	}
		  
	// delete specification itself
	$sql = "DELETE FROM {$this->object_table} WHERE id = {$id}";
	$result = $this->db->exec_query($sql);
	
	$sql = "DELETE FROM {$this->tables['nodes_hierarchy']} WHERE id = {$id}";
	$result = $this->db->exec_query($sql);

	if($result)
	{
		$result = 'ok';
	}
	else
	{
		$result = 'The DELETE SRS request fails.';
	}

	return $result;
} // function end


/**
 * delete_deep()
 * 
 * Delete Req Specification, removing all children (other Req. Spec and Requirements)
 */
function delete_deep($id)
{
	// BUGID 3147 - Delete test project with requirements defined crashed with memory exhausted
    $this->tree_mgr->delete_subtree_objects($id,$id,'',array('requirement' => 'exclude_my_children'));
    $this->delete($id);
}



  /*
    function: get_requirements
              get LATEST VERSION OF requirements contained in a req spec


    args: id: req spec id
          [range]: default 'all'
          [testcase_id]: default null
                         if !is_null, is used as filter
          [order_by]

    returns: array of rows

    rev: 20080830 - franciscom - changed to get node_order from nodes hierarchy table
  */
// function get_requirements($id, $range = 'all', $testcase_id = null,
//                           $order_by=" ORDER BY NH_REQ.node_order,NH_REQ.name,REQ.req_doc_id")
// 
function get_requirements($id, $range = 'all', $testcase_id = null, $options=null, $filters = null)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $req_mgr = new requirement_mgr($this->db);
	$my['options'] = array( 'order_by' => " ORDER BY NH_REQ.node_order,NH_REQ.name,REQ.req_doc_id", 
	                        'output' => 'standard');
	$my['options'] = array_merge($my['options'], (array)$options);

    // null => do not filter
	$my['filters'] = array('status' => null, 'type' => null);
    $my['filters'] = array_merge($my['filters'], (array)$filters);

    switch($my['options']['output'])
    {
	    case 'count':
	       	$rs = 0;	   
	    break;

    	case 'standard':
    	default;
			$rs = null;
	    break;
    }

	
	$sql = '';
	$tcase_filter = '';
    // First Step - get only req info
	$sql = "/* $debugMsg */ SELECT NH_REQ.id FROM {$this->tables['nodes_hierarchy']} NH_REQ ";
	switch($range)
	{
		case 'all';
			break;

		case 'assigned':
			$sql .= " JOIN {$this->tables['req_coverage']} REQ_COV ON REQ_COV.req_id=NH_REQ.id ";
			if(!is_null($testcase_id))
			{       
		    	$tcase_filter = " AND REQ_COV.testcase_id={$testcase_id}";
	  		}
	  		break;
	}
	$sql .= " WHERE NH_REQ.parent_id={$id} " .
	        " AND NH_REQ.node_type_id = {$this->node_types_descr_id['requirement']} {$tcase_filter}";
	$itemSet = $this->db->fetchRowsIntoMap($sql,'id');
   
	if( !is_null($itemSet) )
	{
		$reqSet = array_keys($itemSet);
		$sql = "/* $debugMsg */ SELECT MAX(NH_REQV.id) AS version_id" . 
		       " FROM {$this->tables['nodes_hierarchy']} NH_REQV " .
		       " WHERE NH_REQV.parent_id IN (" . implode(",",$reqSet) . ") " .
		       " GROUP BY NH_REQV.parent_id ";

		$latestVersionSet = $this->db->fetchRowsIntoMap($sql,'version_id');
	    $reqVersionSet = array_keys($latestVersionSet);

	    $getOptions = null;
	    if( !is_null($my['options']['order_by']) )
	    {
			$getOptions = array('order_by' => $my['options']['order_by']);
		}
		$rs = $req_mgr->get_by_id($reqSet,$reqVersionSet,null,$getOptions,$my['filters']);	    	
        
        switch($my['options']['output'])
        {
        	case 'standard':
		    break;
		    
		    case 'count':
		       	$rs = !is_null($rs) ? count($rs) : 0;	   
		    break;
		}
	}
	return $rs;
	
}



  /*
    function: get_by_title
              get req spec information using title as access key.

    args : title: req spec title
           [tproject_id] 
           [parent_id] 
           [case_analysis]: control case sensitive search.
                            default 0 -> case sensivite search

    returns: map.
             key: req spec id
             value: srs info,  map with folowing keys:
                    id
                    testproject_id
                    doc_id
                    title
                    scope
                    total_req
                    type
                    author_id
                    creation_ts
                    modifier_id
                    modification_ts
  */
function get_by_title($title,$tproject_id=null,$parent_id=null,$case_analysis=self::CASE_SENSITIVE)
{
   	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  	$output=null;
  	$title=trim($title);
    $the_title=$this->db->prepare_string($title);
  	$sql = "/* $debugMsg */ " .
  		   " SELECT RSPEC.id,testproject_id,RSPEC,doc_id,RSPEC.scope,RSPEC.total_req,RSPEC.type," .
           " RSPEC.author_id,RSPEC.creation_ts,RSPEC.modifier_id," .
           " RSPEC.modification_ts,NH.name AS title " .
  	       " FROM {$this->object_table} RSPEC, {$this->tables['nodes_hierarchy']} NH";

    switch ($case_analysis)
    {
        case self::CASE_SENSITIVE:
            $sql .= " WHERE NH.name='{$the_title}'";
        break;

        case self::CASE_INSENSITIVE:
            $sql .= " WHERE UPPER(NH.name)='" . strtoupper($the_title) . "'";    
        break;
    }
  	$sql .= " AND RSPEC.id=NH.id ";


  	if( !is_null($tproject_id) )
  	{
  	  $sql .= " AND RSPEC.testproject_id={$tproject_id}";
    }

  	if( !is_null($parent_id) )
  	{
  	  $sql .= " AND NH.parent_id={$parent_id}";
    }

    $sql .= " AND RSPEC.id=NH.id ";
    $output = $this->db->fetchRowsIntoMap($sql,'id');

  	return $output;
  }

  /*
    function: check_title
              Do checks on req spec title, to understand if can be used.

              Checks:
              1. title is empty ?
              2. does already exist a req spec with this title?

    args : title: req spec title
           [parent_id]: default null -> do check for tile uniqueness system wide.
                        valid id: only inside parent_id with this id.
           
           [id]: req spec id. 
           [case_analysis]: control case sensitive search.
                            default 0 -> case sensivite search

    returns:

  */
  function check_title($title,$tproject_id=null,$parent_id=null,$id=null,
                       $case_analysis=self::CASE_SENSITIVE)
  {
    $ret['status_ok'] = 1;
    $ret['msg'] = '';

    $title = trim($title);

  	if ($title == "")
  	{
		$ret['status_ok'] = 0;
  		$ret['msg'] = lang_get("warning_empty_req_title");
  	}

  	if($ret['status_ok'])
  	{
		$ret['msg']='ok';
      	$rs = $this->get_by_title($title,$tproject_id,$parent_id,$case_analysis);
  		if(!is_null($rs) && (is_null($id) || !isset($rs[$id])))
      	{
  			$ret['msg'] = sprintf(lang_get("warning_duplicate_req_title"),$title);
        	$ret['status_ok'] = 0;
  		}
	}
	return $ret;
  } //function end


  /*
    function: check_main_data
              Do checks on req spec title and doc id, to understand if can be used.

              Checks:
              1. title is empty ?
              2. doc is is empty ?
              3. does already exist a req spec with this title?
              4. does already exist a req spec with this doc id?
              
              VERY IMPORTANT:
  	          $tlCfg->req_cfg->child_requirements_mgmt has effects on check on already
  	          existent title or doc id.
  	          
              $tlCfg->req_cfg->child_requirements_mgmt == ENABLED  => N level tree
                             title and doc id can not repited on ANY level of tree
                 
              This is important due to unique index present on Database
              ATTENTION:
              Must be rethinked!!!!
              

    args : title: req spec title
           doc_id: req spec document id / code / short title
           [parent_id]: default null -> do check for tile uniqueness system wide.
                        valid id: only inside parent_id with this id.
           
           [id]: req spec id. 
           [case_analysis]: control case sensitive search.
                            default 0 -> case sensivite search

    returns:

  */
  function check_main_data($title,$doc_id,$tproject_id=null,$parent_id=null,$id=null,
                           $case_analysis=self::CASE_SENSITIVE)
  {
  	$cfg = config_get('req_cfg');
  	
  	// 20091223 - this has to be removed if we remove unique index
  	// $my_parent_id = $cfg->child_requirements_mgmt == ENABLED ? null : $parent_id;
    $my_parent_id = $parent_id;

    $ret['status_ok'] = 1;
    $ret['msg'] = '';

    $title = trim($title);
    $doc_id = trim($doc_id);

  	if ($title == "")
  	{
		$ret['status_ok'] = 0;
  		$ret['msg'] = lang_get("warning_empty_req_title");
  	}

  	if ($doc_id == "")
  	{
		$ret['status_ok'] = 0;
  		$ret['msg'] = lang_get("warning_empty_doc_id");
  	}
  	
  	// 20091223 - franciscom -
  	// Now that req spec has doc id, IMHO this check has not to be done
  	// or must be improved
  	//
  	// if($ret['status_ok'])
  	// {
	// 	$ret['msg']='ok';
    //   	$rs = $this->get_by_title($title,$tproject_id,$my_parent_id,$case_analysis);
  	// 	if(!is_null($rs) && (is_null($id) || !isset($rs[$id])))
    //   	{
    //   		$info = current($rs);
  	// 		$ret['msg'] = sprintf(lang_get("warning_duplicated_req_spec_title"),$info['doc_id'],$title);
    //     	$ret['status_ok'] = 0;
  	// 	}
	// }

  	if($ret['status_ok'])
  	{
		$ret['msg']='ok';
      	$rs = $this->getByDocID($doc_id,$tproject_id);
  		if(!is_null($rs) && (is_null($id) || !isset($rs[$id])))
      	{                                  
      		$info = current($rs);
  			$ret['msg'] = sprintf(lang_get("warning_duplicated_req_spec_doc_id"),$info['title'],$doc_id);
        	$ret['status_ok'] = 0;
  		}
	}

	return $ret;
  } //function end







  /*
    function:

    args :
            $nodes: array with req_spec in order
    returns:

  */
  function set_order($map_id_order)
  {
    $this->tree_mgr->change_order_bulk($map_id_order);
  } // set_order($map_id_order)


  /*
    function: 

    args:
    
    returns: 

  */
  function get_requirements_count($id, $range = 'all', $testcase_id = null,$filters=null)
  {
  	// filters => array('status' => NON_TESTABLE_REQ, 'type' => 'X');
  	$options = array('output' => 'count');
	$count = $this->get_requirements($id,$range,$testcase_id,$options,$filters);
	return $count;
  }


/**
 * getReqTree
 *
 * Example of returned value ( is a recursive one ) 
 * (
 *    [childNodes] => Array
 *        ([0] => Array
 *                (   [id] => 216
 *                    [parent_id] => 179
 *                    [node_type_id] => 6
 *                    [node_order] => 0
 *                    [node_table] => req_specs
 *                    [name] => SUB-R
 *                    [childNodes] => Array
 *                        ([0] => Array
 *                                (   [id] => 181
 *                                    [parent_id] => 216
 *                                    [node_type_id] => 7
 *                                    [node_order] => 0
 *                                    [node_table] => requirements
 *                                    [name] => Gamma Ray Emissions
 *                                    [childNodes] => 
 *                                )
 *                         [1] => Array
 *                                (   [id] => 182
 *                                    [parent_id] => 216
 *                                    [node_type_id] => 7
 *                                    [node_order] => 0
 *                                    [node_table] => requirements
 *                                    [name] => Coriolis Effet
 *                                    [childNodes] => 
 *                                )
 *                        )
 *                )
 *            [1] => Array
 *                (   [id] => 217
 *                    [parent_id] => 179
 *                    [node_type_id] => 6
 *                    [node_order] => 0
 *                    [node_table] => req_specs
 *                    [name] => SUB-R2
 *                    [childNodes] => Array
 *                    ...
 *
 *
 */
function getReqTree($id)
{
    $filters=null;
    $options=array('recursive' => true);
    $map = $this->tree_mgr->get_subtree($id,$filters,$options);
    return $map;  
}


/**
 * exportReqSpecToXML
 * create XML string with following req spec data
 *  - basic data (title, scope)
 *  - custom fields values
 *  - children: can be other req spec  or requirements (tree leaves)
 *
 * Developed using exportTestSuiteDataToXML() as model
 *
 * @internal revision
 * 20100320 - franciscom - added TYPE
 * 20091122 - franciscom - added doc id management  
 */
function exportReqSpecToXML($id,$tproject_id,$optExport=array())
{
	static $req_mgr;
	 
	// manage missing keys
	$optionsForExport=array('RECURSIVE' => true);
	foreach($optionsForExport as $key => $value)
	{
	    $optionsForExport[$key]=isset($optExport[$key]) ? $optExport[$key] : $value;      
	}
	
	$cfXML=null;
	$xmlData = null;
	if($optionsForExport['RECURSIVE'])
	{
	  	$cfXML = $this->customFieldValuesAsXML($id,$tproject_id);
		$containerData = $this->get_by_id($id);
	  	$xmlData = "<req_spec title=\"" . htmlspecialchars($containerData['title']) . '" ' .
	  	           " doc_id=\"" . htmlspecialchars($containerData['doc_id']) . '" >' .
	               "\n<type><![CDATA[{$containerData['type']}]]></type>\n" .
	               "\n<node_order><![CDATA[{$containerData['node_order']}]]></node_order>\n" .
	               "\n<total_req><![CDATA[{$containerData['total_req']}]]></total_req>\n" .
	               "<scope>\n<![CDATA[{$containerData['scope']}]]>\n</scope>\n{$cfXML}";
	}
 
	$req_spec = $this->getReqTree($id);
	$childNodes = isset($req_spec['childNodes']) ? $req_spec['childNodes'] : null ;
	if( !is_null($childNodes) )
	{
	    $loop_qty=sizeof($childNodes); 
	    for($idx = 0;$idx < $loop_qty;$idx++)
	    {
	    	$cNode = $childNodes[$idx];
	    	$nTable = $cNode['node_table'];
	    	if($optionsForExport['RECURSIVE'] && $cNode['node_table'] == 'req_specs')
	    	{
	    		$xmlData .= $this->exportReqSpecToXML($cNode['id'],$tproject_id,$optionsForExport);
	    	}
	    	else if ($cNode['node_table'] == 'requirements')
	    	{
	    	  	if( is_null($req_mgr) )
	    	  	{
	    	      $req_mgr = new requirement_mgr($this->db);
	    		}
	    		$xmlData .= $req_mgr->exportReqToXML($cNode['id'],$tproject_id);
	    	}
	    }
	}    
	if ($optionsForExport['RECURSIVE'])
	{
		$xmlData .= "</req_spec>";
	}
	return $xmlData;
}


/**
 * xmlToReqSpec
 *
 * @param object $source:  
 *               $source->type: possible values 'string', 'file'
 *               $source->value: depends of $source->type 
 *                               'string' => xml string
 *                               'file' => path name of XML file
 *                     
 */
function xmlToReqSpec($source)
{
    $status_ok=true;
    $xml_string=null;
    $req_spec=null;
    switch( $source->type )
    {
        case 'string':
            $xml_string = $source->value;
        break; 
          
        case 'file':
            $xml_file = $source->value;
	          $status_ok=!(($xml_object=@simplexml_load_file($xml_file)) === FALSE);
        break; 
    }

    if( $status_ok )
    {
      
    }

    return $req_spec;
}

/**
 * xmlToMapReqSpec
 *
 */
function xmlToMapReqSpec($xml_item,$level=0)
{
    static $iterations=0;
    static $mapped;
    static $req_mgr;
    
    // Attention: following PHP Manual SimpleXML documentation, Please remember to cast
    //            before using data from $xml,
    if( is_null($xml_item) )
    {
        return null;      
    }
    
    // used to reset static structures if calling this in loop
    if($level == 0)
    {
    	$iterations = 0;
    	$mapped = null;
    }
    
    $dummy=array();
    $dummy['node_order'] = (int)$xml_item->node_order;
    $dummy['scope'] = (string)$xml_item->scope;
    $dummy['type'] = (int)$xml_item->type;
    $dummy['total_req'] = (int)$xml_item->total_req;
    $dummy['level'] = $level;
    $depth=$level+1;
    
    foreach($xml_item->attributes() as $key => $value)
    {
       $dummy[$key] = (string)$value;  // See PHP Manual SimpleXML documentation.
    }    
    
    
    if( property_exists($xml_item,'custom_fields') )	              
    {
          $dummy['custom_fields']=array();
          foreach($xml_item->custom_fields->children() as $key)
          {
             $dummy['custom_fields'][(string)$key->name]= (string)$key->value;
          }    
    }
    $mapped[]=array('req_spec' => $dummy, 'requirements' => null, 
                    'level' => $dummy['level']);

    // Process children
    if( property_exists($xml_item,'requirement') )	              
    {
        if( is_null($req_mgr) )
        {
            $req_mgr = new requirement_mgr($this->db);
        }
        $loop2do=count($xml_item->requirement);
        for($idx=0; $idx <= $loop2do; $idx++)
        {
            $xml_req=$req_mgr->xmlToMapRequirement($xml_item->requirement[$idx]);
            if(!is_null($xml_req))
            { 
                $fdx=count($mapped)-1;
                $mapped[$fdx]['requirements'][]=$xml_req;
            }    
        }    
    }        
    if( property_exists($xml_item,'req_spec') )	              
    {
        $loop2do=count($xml_item->req_spec);
        for($idx=0; $idx <= $loop2do; $idx++)
        {
            $this->xmlToMapReqSpec($xml_item->req_spec[$idx],$depth);
        }
    }        
    
    return $mapped;
}


// ---------------------------------------------------------------------------------------
// Custom field related functions
// ---------------------------------------------------------------------------------------

/*
  function: get_linked_cfields
            Get all linked custom fields.
            Remember that custom fields are defined at system wide level, and
            has to be linked to a testproject, in order to be used.


  args: id: requirement spec id
        [tproject_id]: node id of parent testproject of requirement spec.
                       need to understand to which testproject requirement spec belongs.
                       this information is vital, to get the linked custom fields.
                       Presence /absence of this value changes starting point
                       on procedure to build tree path to get testproject id.

                       null -> use requirement spec id as starting point.
                       !is_null -> use this value as starting point.

  returns: map/hash
           key: custom field id
           value: map with custom field definition and value assigned for choosen req spec,
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
            			value: value assigned to custom field for this req spec
            			       null if for this  req spec custom field was never edited.

            			node_id:  req spec id
            			         null if for this  req spec, custom field was never edited.


  rev :
       20070302 - check for $id not null, is not enough, need to check is > 0

*/
function get_linked_cfields($id,$tproject_id=null)
{
	if (!is_null($id) && $id > 0)
	{
		$req_spec_info = $this->get_by_id($id);
		$tproject_id = $req_spec_info['testproject_id'];
	}
	$cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,cfield_mgr::CF_ENABLED,null,
	                                                          'requirement_spec',$id);
	return $cf_map;
}


/*
  function: html_table_of_custom_field_inputs
            Return html code, implementing a table with custom fields labels
            and html inputs, for choosen req spec.
            Used to manage user actions on custom fields values.


  args: $id
        [tproject_id]: node id of testproject (req spec parent).
                       this information is vital, to get the linked custom fields,
                       because custom fields are system wide, but to be used are
                       assigned to a test project.
                       is null this method or other called will use get_path() 
                       method to get test project id.

        [parent_id]: Need to e rethinked, may be remove (20090111 - franciscom)

        [$name_suffix]: must start with '_' (underscore).
                        Used when we display in a page several items
                        (example during test case execution, several test cases)
                        that have the same custom fields.
                        In this kind of situation we can use the item id as name suffix.


  returns: html string

*/
function html_table_of_custom_field_inputs($id,$tproject_id=null,$parent_id=null,$name_suffix='')
{
    $NO_WARNING_IF_MISSING=true;
    $cf_smarty = '';
    $cf_map = $this->get_linked_cfields($id,$tproject_id);

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
            and custom fields values, for choosen req spec.
            You can think of this function as some sort of read only version
            of html_table_of_custom_field_inputs.


  args: $id

  returns: html string

*/
function html_table_of_custom_field_values($id,$tproject_id)
{
    $NO_WARNING_IF_MISSING=true;    
	$cf_smarty = '';
  	$cf_map = $this->get_linked_cfields($id,$tproject_id);
	
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
              write values of custom fields to db

    args: hash:
          key: custom_field_<field_type_id>_<cfield_id>.
               Example custom_field_0_67 -> 0=> string field

          node_id: req spec id

          [cf_map]:  hash -> all the custom fields linked and enabled
                              that are applicable to the node type of $node_id.

                              For the keys not present in $hash, we will write
                              an appropriate value according to custom field
                              type.

                              This is needed because when trying to udpate
                              with hash being $_REQUEST, $_POST or $_GET
                              some kind of custom fields (checkbox, list, multiple list)
                              when has been deselected by user.

         [hash_type]

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
 function customFieldValuesAsXML($id,$tproject_id)
 {
	$xml = null;
	$cfMap = $this->get_linked_cfields($id,$tproject_id);
	if( !is_null($cfMap) && count($cfMap) > 0 )
	{
		$xml = $this->cfield_mgr->exportValueAsXML($cfMap);
	}
	return $xml;
 }


 /**
  * create a req spec tree on system from $xml data
  *
  *
  * @internal revisions
  * 20100908 - franciscom - BUGID 3762 Import Req Spec - custom fields values are ignored 
  */
function createFromXML($xml,$tproject_id,$parent_id,$author_id,$filters = null,$options=null)
{
	static $req_mgr;
	static $labels;
    static $missingCfMsg;
	static $linkedCF;
	static $messages;
	static $doProcessCF = false;
	
	// init static items
	if( is_null($labels) )
	{
		$labels = array('import_req_spec_created' => '', 'import_req_spec_skipped' => '',
						'import_req_spec_updated' => '', 'import_req_spec_ancestor_skipped' => '',
						'import_req_created' => '','import_req_skipped' =>'', 'import_req_updated' => '');
		foreach($labels as $key => $dummy)
		{
			$labels[$key] = lang_get($key);
		}

		$messages = array();
  		$messages['cf_warning'] = lang_get('no_cf_defined_can_not_import');
  		$messages['cfield'] = lang_get('cf_value_not_imported_missing_cf_on_testproject');

    	$linkedCF = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,cfield_mgr::CF_ENABLED,null,
    															 	'requirement_spec',null,'name');
		$doProcessCF = true;
	}
	
	$user_feedback = null;
    $copy_reqspec = null;
    $copy_req = null;
	$getOptions = array('output' => 'minimun');
	$my['options'] = array('skipFrozenReq' => true);
	$my['options'] = array_merge($my['options'], (array)$options);

	// echo __CLASS__ . ' ' . __FUNCTION__;
	// new dBug($options);
	// new dBug($my['options']);

	$items = $this->xmlToMapReqSpec($xml);
    
    $has_filters = !is_null($filters);
    if($has_filters)
    {
        if(!is_null($filters['requirements']))
        {
            foreach($filters['requirements'] as $reqspec_pos => $requirements_pos)
            {
                $copy_req[$reqspec_pos] = is_null($requirements_pos) ? null : array_keys($requirements_pos);
            }
        }
    }
   
    $loop2do = count($items);
    $container_id[0] = (is_null($parent_id) || $parent_id == 0) ? $tproject_id : $parent_id;

	// items is an array of req. specs
	$skip_level = -1;
    for($idx = 0;$idx < $loop2do; $idx++)
    {
        $rspec = $items[$idx]['req_spec'];
        $depth = $rspec['level'];
        if( $skip_level > 0 && $depth >= $skip_level)
        {
        	$msgID = 'import_req_spec_ancestor_skipped';
        	$user_feedback[] = array('doc_id' => $rspec['doc_id'],'title' => $rspec['title'],
        							 'import_status' => sprintf($labels[$msgID],$rspec['doc_id']));
        	continue;
        }
        
        $req_spec_order = isset($rspec['node_order']) ? $rspec['node_order'] : 0;
		
		// 20100320 - 
		// Check if req spec with same DOCID exists, inside container_id
		// If there is a hit
		//	  We will go in update 
		// If Check fails, need to repeat check on WHOLE Testproject.
		// If now there is a HIT we can not import this branch
		// If Check fails => we can import creating a new one.
		//
		// Important thing:
		// Working in this way, i.e. doing check while walking the structure to import
		// we can end importing struct with 'holes'.
		//
 		$check_in_container = $this->getByDocID($rspec['doc_id'],$tproject_id,$container_id[$depth],$getOptions);
		$skip_level = $depth + 1;
		$result['status_ok'] = 0;
   		$msgID = 'import_req_spec_skipped';
		
     	if(is_null($check_in_container))
		{
			$check_in_tproject = $this->getByDocID($rspec['doc_id'],$tproject_id,null,$getOptions);
			if(is_null($check_in_tproject))
			{
        		$msgID = 'import_req_spec_created';
        		$result = $this->create($tproject_id,$container_id[$depth],$rspec['doc_id'],$rspec['title'],
            		                    $rspec['scope'],$rspec['total_req'],$author_id,$rspec['type'],$req_spec_order);
        	}
        }
        else
        {
        	$msgID = 'import_req_spec_updated';
		    $reqSpecID = key($check_in_container);
			$result = $this->update($reqSpecID,$rspec['doc_id'],$rspec['title'],$rspec['scope'],
									$rspec['total_req'],$author_id,$rspec['type'],$req_spec_order);
       		$result['id'] = $reqSpecID;
        }
        $user_feedback[] = array('doc_id' => $rspec['doc_id'],'title' => $rspec['title'],
                                 'import_status' => sprintf($labels[$msgID],$rspec['doc_id']));


        // 20100908 - Custom Fields
        if( $result['status_ok'] && $doProcessCF && 
        	isset($rspec['custom_fields']) && !is_null($rspec['custom_fields']) )
        {	
    			$cf2insert = null;
    			foreach($rspec['custom_fields'] as $cfname => $cfvalue)
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
    					$user_feedback[] = array('doc_id' => $rspec['docid'],'title' => $rspec['title'], 
    						 	                 'import_status' => $missingCfMsg[$cfname]);
    		   		}
    			}  
 				if( !is_null($cf2insert) )
 				{
    				$this->cfield_mgr->design_values_to_db($cf2insert,$result['id'],null,'simple');
    			}	
		}
        
        
        if($result['status_ok'])
        {
        	$skip_level = -1;
            $container_id[$depth+1] = ($reqSpecID = $result['id']); 
            $reqSet = $items[$idx]['requirements'];
            $create_req = (!$has_filters || isset($copy_req[$idx])) && !is_null($reqSet);
            if($create_req)
            {
    			if(is_null($req_mgr))
    			{
    				$req_mgr =  new requirement_mgr($this->db);
    			}
                
                $items_qty = isset($copy_req[$idx]) ? count($copy_req[$idx]) : count($reqSet);
                $keys2insert = isset($copy_req[$idx]) ? $copy_req[$idx] : array_keys($reqSet);
                for($jdx = 0;$jdx < $items_qty; $jdx++)
                {
                    $req = $reqSet[$keys2insert[$jdx]];
                    $dummy = $req_mgr->createFromMap($req,$tproject_id,$reqSpecID,$author_id,
                    								 null,$my['options']);
					$user_feedback = array_merge($user_feedback,$dummy);
                } 
            }  // if($create_req)   
        } // if($result['status_ok'])
    }    
    return $user_feedback;
}



  /*
    function: getByDocID
              get req spec information using document ID as access key.

    args : doc_id:
           [tproject_id] 
           [parent_id]
           [options]: 
           			 [case]: control case sensitive search.
                             default 0 -> case sensivite search
           			 [access_key]:
           			 [check_criteria]:
           			 [output]:

    returns: map.
             key: req spec id
             value: srs info,  map with folowing keys:
                    id
                    testproject_id
                    title
                    scope
                    total_req
                    type
                    author_id
                    creation_ts
                    modifier_id
                    modification_ts
  */
function getByDocID($doc_id,$tproject_id=null,$parent_id=null,$options=null)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	$my['options'] = array( 'check_criteria' => '=', 'access_key' => 'id', 
							'case' => 'sensitive', 'output' => 'standard');
	$my['options'] = array_merge($my['options'], (array)$options);

    
  	$output=null;
    $the_doc_id=$this->db->prepare_string(trim($doc_id));

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
  			 $sql .= " RSPEC.id,testproject_id,RSPEC.doc_id,RSPEC.scope,RSPEC.total_req,RSPEC.type," .
           			 " RSPEC.author_id,RSPEC.creation_ts,RSPEC.modifier_id," .
           			 " RSPEC.modification_ts,NH.name AS title ";
        break;
           			 
		case 'minimun':
  			 $sql .= " RSPEC.id,testproject_id,RSPEC.doc_id,NH.name AS title ";
        break;
		
	}

	$sql .= " FROM {$this->object_table} RSPEC, {$this->tables['nodes_hierarchy']} NH " .
 		    " WHERE RSPEC.doc_id {$check_criteria} ";

  	if( !is_null($tproject_id) )
  	{
  	  $sql .= " AND RSPEC.testproject_id={$tproject_id}";
    }

  	if( !is_null($parent_id) )
  	{
  	  $sql .= " AND NH.parent_id={$parent_id}";
    }

    $sql .= " AND RSPEC.id=NH.id ";
	$output = $this->db->fetchRowsIntoMap($sql,$my['options']['access_key']);
  	return $output;
  }


	/*
	  function: copy_to
	            deep copy one req spec to another parent (req spec or testproject).
	            
	
	  args : id: req spec id (source or copy)
	         parent_id:
	         user_id: who is requesting copy operation
	         [options]
	                                              
	  returns: map with following keys:
	           status_ok: 0 / 1
	           msg: 'ok' if status_ok == 1
	           id: new created if everything OK, -1 if problems.
	
	  rev :
	*/
	function copy_to($id, $parent_id, $tproject_id, $user_id,$options = null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$op = array('status_ok' => 1, 'msg' => 'ok', 'id' => -1 , 'mappings' => null);
		$my['options'] = array('copy_also' => null);
		$my['options'] = array_merge($my['options'], (array)$options);


		$field_size = config_get('field_size');
		$item_info = $this->get_by_id($id);
        $target_doc = $this->generateDocID($id,$tproject_id);		
		$new_item = $this->create($tproject_id,$parent_id,$target_doc,$item_info['title'],
		                          $item_info['scope'],$item_info['total_req'],
		                          $item_info['author_id'],$item_info['type'],$item_info['node_order']);
	
	    $op = $new_item;
	    if( $new_item['status_ok'] )
	    {
	    	$op['mappings'][$id] = $new_item['id'];
	    		
			$this->copy_cfields($id,$new_item['id']);
        	
        	// Now loop to copy all items inside it    	
 			$my['filters'] = null;
			$subtree = $this->tree_mgr->get_subtree($id,$my['filters']);
			if (!is_null($subtree))
			{
				$reqMgr =  new requirement_mgr($this->db);
				$parent_decode=array();
			  	$parent_decode[$id]=$new_item['id'];
				foreach($subtree as $the_key => $elem)
				{
					// 20100311 - franciscom
				  	$the_parent_id=isset($parent_decode[$elem['parent_id']]) ? $parent_decode[$elem['parent_id']] : null;
					switch ($elem['node_type_id'])
					{
						case $this->node_types_descr_id['requirement']:
							// BUGID 4239: wrong last parameter here caused duplicated links 
							//             between reqs and testcases when copying testproject
							//$ret = $reqMgr->copy_to($elem['id'],$the_parent_id,$user_id,
							//                              $tproject_id,$my['options']['copy_also']);
							$ret = $reqMgr->copy_to($elem['id'],$the_parent_id,$user_id,
							                              $tproject_id,$my['options']);
							$op['status_ok'] = $ret['status_ok'];    
							$op['mappings'] += $ret['mappings'];
							break;
							
						case $this->node_types_descr_id['requirement_spec']:
							$item_info = $this->get_by_id($elem['id']);
        	                $target_doc = $this->generateDocID($elem['id'],$tproject_id);		
							$ret = $this->create($tproject_id,$the_parent_id,$target_doc,$item_info['title'],
			                                     $item_info['scope'],$item_info['total_req'],
			                                     $item_info['author_id'],$item_info['type'],$item_info['node_order']);

					    	$parent_decode[$elem['id']]=$ret['id'];
				      		$op['mappings'][$elem['id']] = $ret['id'];

				      		if( ($op['status_ok'] = $ret['status_ok']) )
				      		{
				      			$this->copy_cfields($elem['id'],$ret['id']);
							}
							break;
					}
					if( $op['status_ok'] == 0 )
					{
						break;
					}
				}
			}
		}	
		return $op;
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
  		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
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

		// Check if another req with same DOC ID exists on target container,
		// If yes generate a new DOC ID
		$getOptions = array('check_criteria' => 'like', 'access_key' => 'doc_id');
		$itemSet = $this->getByDocID($item_info['doc_id'],$tproject_id,null,$getOptions);
		$target_doc = $item_info['doc_id'];
		$instance = 1;
		if( !is_null($itemSet) )
		{
			// doc_id has limited size => we need to be sure that generated id will not exceed DB size
            $nameSet = array_flip(array_keys($itemSet));
	        // 6 magic from " [xxx]"
	        $prefix = trim_and_limit($item_info['doc_id'],$field_size->docid-6);
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
	function getFirstLevelInTestProject($tproject_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	  	$sql = "/* $debugMsg */ SELECT * from {$this->tables['nodes_hierarchy']} " .
	  	       " WHERE parent_id = {$tproject_id} " .
	  	       " AND node_type_id = {$this->node_types_descr_id['requirement_spec']} " .
	  	       " ORDER BY node_order,id";
		$rs = $this->db->fetchRowsIntoMap($sql,'id');
		return $rs;
	}


} // class end
?>