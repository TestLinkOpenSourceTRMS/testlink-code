<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	requirement_mgr.class.php
 * @package  	TestLink
 * @author 	 	Francisco Mancardi <francisco.mancardi@gmail.com>
 * @copyright 	2007-2011, TestLink community 
 *
 * Manager for requirements.
 * Requirements are children of a requirement specification (requirements container)
 *
 * @internal revisions:
 *	20110331 - franciscom - BUGID 4366: Custom Field when requirements reports is generated was empty
 *							get_by_id()
 *
 *	20110308 - aismon - backported method get_version_revision() from master to branch 1.9
 *	20110116 - franciscom - fixed Crash on MSSQL due to column name with MIXED case
 *  						BUGID 4172 - MSSQL UNION text field issue
 * 	20110115 - franciscom - create_new_revision() - fixed insert of null on timestamp field
 *	20110108 - franciscom - createFromMap() - check improvements
 *						  	BUGID 4150 check for duplicate req title
 *	20110106 - Julian - update() - set author,modifier,creation_ts,modifier_ts depending on creation of new revision
 *                      get_history() - added last_editor to output
 *	20101218 - franciscom - BUGID 4100 createFromMap() - missing update of Req Title when creating new version
 *  20101211 - franciscom - get_history() fixed code to check for NULL dates
 *	20101126 - franciscom - BUGID get_last_version_info() -> get_last_child_info();
 *  20101119 - asimon - BUGID 4038: clicking requirement link does not open req version
 *  20101118 - asimon - BUGID 4031: Prevent copying of req scope to test case summary when creating test cases from requirement
 *  20101109 - asimon - BUGID 3989: now it is configurable if custom fields without values are shown
 *	20101012 - franciscom - html_table_of_custom_field_inputs() refactoring to use new method on cfield_mgr class
 *	20101011 - franciscom - BUGID 3886: CF Types validation - changes in html_table_of_custom_field_inputs()
 *	20101011 - Julian - BUGID 3876: Values of custom fields are not displayed when editing requirement
 *	20101003 - franciscom - BUGID 3834: Create version source <>1 - Bad content used.
 *							create_new_version() interface changed
 *
 *  20101001 - asimon - extended html_table_of_custom_field_inputs() to not lose entered custom field values on errors.
 *  20100920 - franciscom - 3686: When importing requirements, provide the option to 'create new version'
 *  20100915 - Julian - BUGID 3777 - Added get_last_doc_id_for_testproject()
 *	20100914 - franciscom - createFromMap() - added new option 'skipFrozenReq'
 *  20100908 - franciscom - BUGID 2877 - Custom Fields linked to Requirement Versions
 *							createFromXML(),copy_to()
 *							new method createFromMap()
 *
 *  20100907 - franciscom - BUGID 2877 - Custom Fields linked to Requirement Versions
 *							delete(),exportReqToXML()
 *
 *	20100906 - franciscom - BUGID 2877 - Custom Fields linked to Requirement Versions
 *							create(), html_table_of_custom_field_inputs(),copy_version()
 *
 *	20100904 - franciscom - BUGID 3685: XML Requirements Import Updates Frozen Requirement
 *	20100520 - franciscom - BUGID 2169 - customFieldValuesAsXML() new method_exists
 *	20100511 - franciscom - createFromXML() new method
 *	20100509 - franciscom - update() interface changes.
 *  20100324 - asimon - BUGID 1748 - Moved init_relation_type_select here from reqView.php
 *                                   as it is now used from multiple files.
 *  20100323 - asimon - BUGID 1748 - added count_relations()
 *  20100319 - asimon - BUGID 1748 - added functions for requirement relations: 
 *	                    get_relations(), get_all_relation_labels(), delete_relation(),
 *	                    add_relation(), check_if_relation_exists(), delete_all_relations()
 *                      modified delete() to also delete all relations with req
 *	20100309 - franciscom - get_by_id() removed useless code pointed by BUGID 3254
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


	// 20100220 - franciscom - I'm will work only on XML
	// then remove other formats till other dev do refactor
	var $import_file_types = array("csv" => "CSV",
	                               "csv_doors" => "CSV (Doors)",
	                               "XML" => "XML",
							       "DocBook" => "DocBook");

	var $export_file_types = array("XML" => "XML");

	
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

	@internal revisions
	20110331 - franciscom - BUGID 4366	

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

	$where_clause = " WHERE NH_REQV.parent_id ";
	if( ($id_is_array=is_array($id)) )
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
		if( is_null($version_id) )
		{
		    // search by "human" version number
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

  	// 20110331 - BUGID 4366
  	// added -1 AS revision_id to make some process easier 
  
	$sql = " /* $debugMsg */ SELECT REQ.id,REQ.srs_id,REQ.req_doc_id," . 
		   " REQV.scope,REQV.status,REQV.type,REQV.active," . 
           " REQV.is_open,REQV.author_id,REQV.version,REQV.id AS version_id," .
           " REQV.expected_coverage,REQV.creation_ts,REQV.modifier_id," .
           " REQV.modification_ts,REQV.revision, -1 AS revision_id, " .
           " NH_REQ.name AS title, REQ_SPEC.testproject_id, " .
	       " NH_RSPEC.name AS req_spec_title, REQ_SPEC.doc_id AS req_spec_doc_id, NH_REQ.node_order " .
	       " FROM {$this->object_table} REQ " .
	       " JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = REQ.id " .
	       " JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.parent_id = NH_REQ.id ".
	       " JOIN  {$this->tables['req_versions']} REQV ON REQV.id = NH_REQV.id " .  
	       " JOIN {$this->tables['req_specs']} REQ_SPEC ON REQ_SPEC.id = REQ.srs_id " .
	       " JOIN {$this->tables['nodes_hierarchy']} NH_RSPEC ON NH_RSPEC.id = REQ_SPEC.id " .
           $where_clause . $filter_clause . $my['options']['order_by'];


	if ($version_id != self::LATEST_VERSION)
	{
		$recordset = $this->db->get_recordset($sql);
	}
	else
	{
	    // 20090413 - franciscom - 
	    // But, how performance wise can be do this, instead of using MAX(version)
	    // and a group by? 
	    //           
	    // 20100309 - franciscom - 
	    // if $id was a list then this will return something USELESS
	    //           
	    if( !$id_is_array )
	    {         
			$recordset = array($this->db->fetchFirstRow($sql));
		}	
		else
		{
			// Write to event viewer ???
		}
	}

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
	$log_message = lang_get('req_created_automatic_log');
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
			if (config_get('internal_links')->enable )
			{
				$scope = req_link_replace($this->db, $scope, $tproject_id);
			}

			$version_number = 1;
			$op = $this->create_version($result['id'],$version_number,$scope,$user_id,
			                            $status,$type,intval($expected_coverage));
			$result['msg'] = $op['status_ok'] ? $result['msg'] : $op['msg'];

			// BUGID 2877 - Custom Fields linked to Requirement Versions
			$result['version_id'] = $op['status_ok'] ? $op['id'] : -1;
			
			if( $op['status_ok'] )
			{
				$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
				$sql = 	"/* $debugMsg */ " .
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
    $field_size=config_get('field_size');
    
    // get SRSid, needed to do controls
    $rs=$this->get_by_id($id,$version_id);
    $req = $rs[0];
    $srs_id=$req['srs_id'];
    
    // try to avoid function calls when data is available on caller
	$tproject_id = is_null($tproject_id) ? $this->tree_mgr->getTreeRoot($srs_id): $tproject_id;

    /* contribution by asimon83/mx-julian */
	if (config_get('internal_links')->enable ) 
	{
		$scope = req_link_replace($this->db, $scope, $tproject_id);
	}
	/* end contribution by asimon83/mx-julian */
    
	$reqdoc_id=trim_and_limit($reqdoc_id,$field_size->req_docid);
	$title=trim_and_limit($title,$field_size->req_title);
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
	  	if (!$create_revision) {
	  		$sql_temp .= ", modifier_id={$user_id}, modification_ts={$db_now} ";
	  	}
	  	
	  	$sql[] = $sql_temp . " WHERE id={$version_id}";

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
              Requirement relations
              Requirement custom fields values
              Attachments


    args: id: can be one id, or an array of id

    returns:


	@internal revisions
	20101127 - franciscom - req_revisions table
	20100907 - franciscom - BUGID 2877 - Custom Fields linked to Requirement Versions 
  */
	function delete($id,$version_id = self::ALL_VERSIONS)
 	{
 		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
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
		
		// When deleting only one version, we need to check if we need to delete  requirement also.
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

			// BUGID 2877 - Custom Fields linked to Requirement Versions
	    	// Delete Custom fields
	    	// $this->cfield_mgr->remove_all_design_values_from_node($id);
	  		
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

			$implosion = implode(',',$children);

			// ------------------------------------------------------------------------------------------	
			// 20101127 - franciscom - BUGID 
		  	$sql = "/* $debugMsg */ SELECT id from {$this->tables['nodes_hierarchy']} " .
	  	       	   " WHERE parent_id IN ( {$implosion} ) ";
			$revisionSet = $this->db->fetchRowsIntoMap($sql,'id');
			if( !is_null($revisionSet) )
			{
	  			$this->cfield_mgr->remove_all_design_values_from_node(array_keys($revisionSet));
            	
	  			$sql = "DELETE FROM {$this->tables['req_revisions']} WHERE parent_id IN ( {$implosion} ) ";
	  			$result = $this->db->exec_query($sql);
            	
	  			$sql = "DELETE FROM {$this->tables['nodes_hierarchy']} WHERE parent_id IN ( {$implosion} ) ";
	  			$result = $this->db->exec_query($sql);
			}
			// ------------------------------------------------------------------------------------------


	  		// BUGID 2877 - Custom Fields linked to Requirement Versions                    
	  		$this->cfield_mgr->remove_all_design_values_from_node((array)$children);
	  		
	  		$where_clause_children = " WHERE id IN ( {$implosion} ) ";
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
	  		
	  		//also delete relations to other requirements
	  		$this->delete_all_relations($id);
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
    $sql = "/* $debugMsg */ SELECT DISTINCT NHA.id,NHA.name,TCV.tc_external_id " .
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
    	$getOptions = array('output' => 'minimun');
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
	        
            // BUGID 4031 - copying of scope now is configurable
            $prefix = ($req_cfg->use_testcase_summary_prefix_with_title_and_version)
                    ? sprintf($req_cfg->testcase_summary_prefix_with_title_and_version, 
                              $reqID, $reqData['version_id'], $reqData['title'], $reqData['version'])
                    : $req_cfg->testcase_summary_prefix;
            $content = ($req_cfg->copy_req_scope_to_tc_summary) ? $prefix . $reqData['scope'] : $prefix;
            
	  	    $tcase = $tcase_mgr->create($tsuite_id,$tcase_name,$content,
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

	$req = $this->get_by_id($id,requirement_mgr::LATEST_VERSION);
	$reqData[] = $req[0]; 

	
	// BUGID 2169
	// BUGID 2877 - Custom Fields linked to Requirement Versions 
	// $cfXML = $this->customFieldValuesAsXML($id,$tproject_id);
	$cfXML = $this->customFieldValuesAsXML($id,$req[0]['version_id'],$tproject_id);
	

 	$rootElem = "{{XMLCODE}}";
	$elemTpl = "\t" .   "<requirement>" .
	           "\n\t\t" . "<docid><![CDATA[||DOCID||]]></docid>" .
	           "\n\t\t" . "<title><![CDATA[||TITLE||]]></title>" .
	           "\n\t\t" . "<node_order><![CDATA[||NODE_ORDER||]]></node_order>".
			   "\n\t\t" . "<description><![CDATA[\n||DESCRIPTION||\n]]></description>".
			   "\n\t\t" . "<status><![CDATA[||STATUS||]]></status>" .
			   "\n\t\t" . "<type><![CDATA[||TYPE||]]></type>" .
			   "\n\t\t" . "<expected_coverage><![CDATA[||EXPECTED_COVERAGE||]]></expected_coverage>" .			   
			   "\n\t\t" . $cfXML . 
			   "\n\t" . "</requirement>" . "\n";
					   
	$info = array (	"||DOCID||" => "req_doc_id",
							    "||TITLE||" => "title",
							    "||DESCRIPTION||" => "scope",
							    "||STATUS||" => "status",
							    "||TYPE||" => "type",
							    "||NODE_ORDER||" => "node_order",
							    "||EXPECTED_COVERAGE||" => "expected_coverage",
							    );
	
	
	
	$xmlStr = exportDataToXML($reqData,$rootElem,$elemTpl,$info,true);						    
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


/**
 * createFromXML
 *
 * @internal revisions
 * 20100908 - franciscom - BUGID 2877 - Custom Fields linked to Requirement Versions
 * 20100904 - franciscom - BUGID 3685: XML Requirements Import Updates Frozen Requirement
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

	return 	$this->createFromMap($reqAsMap,$tproject_id,$parent_id,$author_id,$filters,$options);
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
 * 20101218 - BUGID 4100 - franciscom
 * 20100908 - franciscom - created
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
						'frozen_req_unable_to_import' => '', 'requirement' => '', 
						'import_req_new_version_created' => '',
						'import_req_update_last_version_failed' => '',
						'import_req_new_version_failed' => '', 'import_req_skipped_plain' => '');
		foreach($labels as $key => $dummy)
		{
			$labels[$key] = lang_get($key);
		}	
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
	$my['options'] = array( 'hitCriteria' => 'docid' , 'actionOnHit' => "update", 
							'skipFrozenReq' => true);
	$my['options'] = array_merge($my['options'], (array)$options);

    // Check:
    // If item with SAME DOCID exists inside container
	// If there is a hit
	//	   We will follow user option: update,create new version
	//
	// If do not exist check must be repeated, but on WHOLE test project
	// 	If there is a hit -> we can not create
	//		else => create
	// 
	$getOptions = array('output' => 'minimun');
	$msgID = 'import_req_skipped';

	$target = array('key' => $my['options']['hitCriteria'], 'value' => $req[$my['options']['hitCriteria']]);

    // IMPORTANT NOTICE
    // When get is done by attribute that can not be unique (like seems to happen now 20110108 with
    // title), we can get more than one hit and then on this context we can not continue
    // with requested operation
	$check_in_reqspec = $this->getByAttribute($target,$tproject_id,$parent_id,$getOptions);


	// 20110206 - franciscom
	// while working on BUGID 4210, new details came to light.
	//
	// In addition to hit criteria there are also the criteria taht we use 
	// when creating/update item using GUI, and these criteria have to be
	// checked abd fullfilled.
	//
	if(is_null($check_in_reqspec))
	{
		$check_in_tproject = $this->getByAttribute($target,$tproject_id,null,$getOptions);
		if(is_null($check_in_tproject))
		{
			$newReq = $this->create($parent_id,$req['docid'],$req['title'],$req['description'],
		    		         		$author_id,$req['status'],$req['type'],$req['expected_coverage'],
		    		         		$req['node_order']);
		
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
		$last_version = $this->get_last_child_info($reqID);
		$msgID = 'frozen_req_unable_to_import';
		$status_ok = false;
		if( $last_version['is_open'] == 1 || !$my['options']['skipFrozenReq'])
		{
    		switch($my['options']['actionOnHit'])
			{
				case 'update_last_version':
					$result = $this->update($reqID,$last_version['id'],$req['docid'],$req['title'],$req['description'],
										    $author_id,$req['status'],$req['type'],$req['expected_coverage'],
										    $req['node_order']);

					$status_ok = ($result['status_ok'] == 1);
					if( $status_ok)
					{
						$msgID = 'import_req_updated';
					}
					else
					{
						$msgID = 'import_req_update_last_version_failed';
					}	
				break;
				
				case 'create_new_version':
					$newItem = $this->create_new_version($reqID,$author_id);
        	    	
					// Set always new version to NOT Frozen
					$this->updateOpen($newItem['id'],1);				
					
					// BUGID 4100 - 20101218 - franciscom
					// Need to update TITLE
					// $title = trim_and_limit($req['title'],$fieldSize->req_title);
			  		// $sql = 	"/* $debugMsg */ UPDATE {$this->tables['nodes_hierarchy']} " .
  		  	 		//		" SET name='" . $this->db->prepare_string($title) . "'" . 
					//		" WHERE id={$reqID}";
					// $this->db->exec_query($sql);
					
					// hmm wrong implementation
					// Need to update ALL fields on new version then why do not use
					// update() ?
					$newReq['version_id'] = $newItem['id']; 

				    // IMPORTANT NOTICE:
				    // We have to DO NOT UPDATE REQDOCID with info received from USER
				    // Because ALL VERSION HAS TO HAVE docid, or we need to improve our checks
				    // and if update fails => we need to delete new created version.
									
					$title = trim_and_limit($req['title'],$fieldSize->req_title);
					$result = $this->update($reqID,$newItem['id'],$req['docid'],
											$title,$req['description'],
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
    $what2add = is_null($result) ? $req['docid'] : $result['msg'];
    
    $user_feedback[] = array('doc_id' => $req['docid'],'title' => $req['title'], 
    				 	     'import_status' => sprintf($labels[$msgID],$what2add));

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

	

  rev :
	   20101011 - franciscom - refactoring for	
       20070302 - check for $id not null, is not enough, need to check is > 0

*/
function get_linked_cfields($id,$child_id,$parent_id=null)
{
	$enabled = 1;
	if (!is_null($id) && $id > 0)
	{
    	$req_info = $this->get_by_id($id);
	  	$tproject_id = $req_info[0]['testproject_id'];
	  	unset($req_info);
	}
	else
	{
	  	$tproject_id = $parent_id;
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


  @internal revisions:
  	20101011 - franciscom - BUGID 3886: CF Types validation 
    20101001 - asimon - extended to not lose entered custom field values on errors

*/
function html_table_of_custom_field_inputs($id,$version_id,$parent_id=null,$name_suffix='', $input_values = null)
{
    $cf_map = $this->get_linked_cfields($id,$version_id,$parent_id,$name_suffix,$input_values);
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
 // function customFieldValuesAsXML($id,$tproject_id)
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

	    $my['options'] = array( 'check_criteria' => '=', 'access_key' => 'id', 
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

  		$output = $this->db->fetchRowsIntoMap($sql,$my['options']['access_key']);
  		
  		return $output;
  	}

	/**
	 * Copy a requirement to a new requirement specification
	 * requirement DOC ID will be changed because must be unique inside
	 * MASTER CONTAINER (test project)
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
	    $new_item = array('id' => -1, 'status_ok' => 0, 'msg' => 'ok', 'mappings' => null);
		$my['options'] = array('copy_also' => null);
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

		// REQ DOCID is test project wide => can not exist duplicates inside
		// a test project => we need to generate a new one using target as
		// starting point
        $target_doc = $this->generateDocID($id,$root);		

		$item_versions = $this->get_by_id($id);
		if($item_versions)
		{
			// BUGID 4150
			// If a sibling exists with same title => need to generate automatically
			// a new one.
			$title = $this->generateUniqueTitle($item_versions[0]['title'],$parent_id,$root);
			
			// $new_item = $this->create_req_only($parent_id,$target_doc,$item_versions[0]['title'],
			//                                   $item_versions[0]['author_id'],$item_versions[0]['node_order']);

			$new_item = $this->create_req_only($parent_id,$target_doc,$title,
			                                   $item_versions[0]['author_id'],$item_versions[0]['node_order']);
			
			if ($new_item['status_ok'])
			{
		        $ret['status_ok']=1;
   		        $new_item['mappings'][$id] = $new_item['id'];
		        
	 			foreach($item_versions as $req_version)
				{
					$op = $this->create_version($new_item['id'],$req_version['version'],
					                            $req_version['scope'],$req_version['author_id'],
					                            $req_version['status'],$req_version['type'],
					                            $req_version['expected_coverage']);

	    			$new_item['mappings'][$req_version['id']] = $op['id'];
	    			
	    			// BUGID 2877 - CF on version
	        		$this->copy_cfields(array('id' => $req_version['id'], 'version_id' =>  $req_version['version_id']),
	        							array('id' => $new_item['id'], 'version_id' => $op['id']));
	    			
				}
	        	
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
	function copy_cfields($source,$destination,$tproject_id=null)
	{
	  $cfmap_from=$this->get_linked_cfields($source['id'],$source['version_id'],$tproject_id);
	  $cfield=null;
	  if( !is_null($cfmap_from) )
	  {
	    foreach($cfmap_from as $key => $value)
	    {
	      $cfield[$key]=array("type_id"  => $value['type'], "cf_value" => $value['value']);
	    }
	  }
	  $this->cfield_mgr->design_values_to_db($cfield,$destination['version_id'],null,'reqversion_copy_cfields');
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
			$field_size = config_get('field_size');
			$req_cfg = config_get('req_cfg');
			$safety_len = 2; // use t
			$mask = $req_cfg->duplicated_docid_algorithm->text;
			
			// req_doc_id has limited size then we need to be sure that generated id will
			// not exceed DB size
    	    $nameSet = array_flip(array_keys($itemSet));
		    $prefix = trim_and_limit($item_info['req_doc_id'],
		    						 $field_size->req_docid-strlen($mask)-$safety_len);
		    						 
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
	            create a new version, doing BY DEFAULT a copy of last version.
	            If reqVersionID is passed, then this version will be used as source data.
	
	  args : $id: requirement id
	         $user_id: who is doing this operation.
			 $reqVersionID = default null => use last version as source 
	
	  returns:
	          map:  id: node id of created tcversion
	                version: version number (i.e. 5)
	                msg
	
	  @internal revisions
	  20101003 - franciscom - BUGID 3834: Create version source <>1 - Bad content used. 
	*/
	function create_new_version($id,$user_id,$reqVersionID=null,$log_msg=null)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	
		// get a new id
	  	$version_id = $this->tree_mgr->new_node($id,$this->node_types_descr_id['requirement_version']);
	  
	  	// Needed to get higher version NUMBER, to generata new VERSION NUMBER
	  	// $sourceVersionInfo =  $this->get_last_version_info($id);
	  	$sourceVersionInfo =  $this->get_last_child_info($id);
	  	$newVersionNumber = $sourceVersionInfo['version']+1; 

	  	$ret = array();
	  	$ret['id'] = $version_id;
	  	$ret['version'] = $newVersionNumber;
	  	$ret['msg'] = 'ok';
      	
	  	$sourceVersionID = is_null($reqVersionID) ? $sourceVersionInfo['id'] : $reqVersionID;
      	
	  	// BUGID 2877 - Custom Fields linked to Requirement Versions
	  	$this->copy_version($id,$sourceVersionID,$version_id,$newVersionNumber,$user_id);
	  	
	  	// need to update log message in new created version
	  	$sql = 	"/* $debugMsg */ " .
	  			" UPDATE {$this->tables['req_versions']} " .
	  			" SET log_message = '" . $this->db->prepare_string($log_msg) . "'" .
	  			" WHERE id={$version_id}";
	  	$this->db->exec_query($sql);		
	  		
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
		if(count($all_reqs) > 0) {
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
 	 * @internal revisions
 	 * 20100906 - franciscom - BUGID 2877 - Custom Fields linked to Requirement Versions 
     */
	// function copy_version($from_version_id,$to_version_id,$as_version_number,$user_id)
	function copy_version($id,$from_version_id,$to_version_id,$as_version_number,$user_id)
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
	         
	    // BUGID 2877 - Custom Fields linked to Requirement Versions     
	    $this->copy_cfields(array('id' => $id, 'version_id' => $from_version_id),
						  	array('id' => $id, 'version_id' => $to_version_id));


	         
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
		$interproject_linking = config_get('req_cfg')->relations->interproject_linking;

		$sql = " $debugMsg SELECT id, source_id, destination_id, relation_type, author_id, creation_ts " . 
			   " FROM {$this->tables['req_relations']} " .
			   " WHERE source_id=$id OR destination_id=$id " .
			   " ORDER BY id ASC ";
   
    	$relations['relations']= $this->db->get_recordset($sql);  
    	
    	if( !is_null($relations['relations']) && count($relations['relations']) > 0 )
    	{
    		$labels = $this->get_all_relation_labels();
			$label_keys = array_keys($labels);
    		foreach($relations['relations'] as $key => $rel) {
        	
    			// is this relation type is configured?
    			if( ($relTypeAllowed = in_array($rel['relation_type'],$label_keys)) ) 
    			{ 
	    			$relations['relations'][$key]['source_localized'] = $labels[$rel['relation_type']]['source'];
	    			$relations['relations'][$key]['destination_localized'] = $labels[$rel['relation_type']]['destination'];
	    			
	    			if ($id == $rel['source_id']) {
	    				$type_localized = 'source_localized';
	    				$other_key = 'destination_id';
	    			} else {
	    				$type_localized = 'destination_localized';
	    				$other_key = 'source_id';
	    			}
	    			$relations['relations'][$key]['type_localized'] = $relations['relations'][$key][$type_localized];
	    			$other_req = $this->get_by_id($rel[$other_key]);
			    		    		
	    			// only add it, if either interproject linking is on or if it is in the same project
	    			$relTypeAllowed = false;
	    			if ($interproject_linking || ($other_req[0]['testproject_id'] == $relations['req']['testproject_id'])) {
	    			
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
	public function count_relations($id) {
		
		$debugMsg = '/* Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__ . ' */';
		$sql = " $debugMsg SELECT COUNT(*) AS qty " .
			   " FROM {$this->tables['req_relations']} " .
			   " WHERE source_id=$id OR destination_id=$id ";
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
		$sql = " $debugMsg INSERT INTO {$this->tables['req_relations']} "	. 
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
	 *		Array
	 *		(
	 *			    [1] => Array
	 *			        (
	 *			            [source] => parent of
	 *			            [destination] => child of
	 *			        )
	 *			    [2] => Array
	 *			        (
	 *			            [source] => blocks
	 *			            [destination] => depends on
	 *			        )
	 *			    [3] => Array
	 *			        (
	 *			            [source] => related to
	 *			            [destination] => related to
	 *			        )
	 *			)
	 */
	public static function get_all_relation_labels() {
		
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
	function init_relation_type_select() {
		
		$htmlSelect = array('items' => array(), 'selected' => null, 'equal_relations' => array());
		$labels = $this->get_all_relation_labels();
		
		foreach ($labels as $key => $lab) {
			$htmlSelect['items'][$key . "_source"] = $lab['source'];
			if ($lab['source'] != $lab['destination']) {
				// relation is not equal as labels for source and dest are different
				$htmlSelect['items'][$key . "_destination"] = $lab['destination']; 
			} else {
				// mark this as equal relation - no parent/child, makes searching simpler
				$htmlSelect['equal_relations'][] = $key . "_source"; 
			}
		}
		
		// set "related to" as default preselected value in forms
		if (defined('TL_REQ_REL_TYPE_RELATED') && isset($htmlSelect[TL_REQ_REL_TYPE_RELATED . "_source"])) {
			$selected_key = TL_REQ_REL_TYPE_RELATED . "_source";
		} else {
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
	 *	@param id: parent id: can be REQ ID or REQ VERSION ID depending of $child_type 
 	 *	@param child_type: 'req_versions', 'req_revisions'
 	 *
 	 *	@return  
     */
	function get_last_child_info($id, $child_type='version')
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$info = null;
		$target_cfg = array('version' => array('table' => 'req_versions', 'field' => 'version'), 
							'revision' => array('table'=> 'req_revisions', 'field' => 'revision'));

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
			$sql = "/* $debugMsg */ SELECT CHILD.* FROM {$this->tables[$table]} CHILD," .
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
	  	$source_info =  $this->get_last_child_info($parent_id,'revision');
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
	  	$sql = 	"/* $debugMsg */ " .
	  			" UPDATE {$this->tables['req_revisions']} " .
	  			" SET name ='" . $this->db->prepare_string($req['title']) . "'," .
	  			"     req_doc_id ='" . $this->db->prepare_string($req['req_doc_id']) . "'" .
	  			" WHERE id = {$item_id} ";
	  	$this->db->exec_query($sql);
	  	
	  	$new_rev = $current_rev+1;
	  	$db_now = $this->db->db_now();
	  	$sql = 	" /* $debugMsg */ " .
	  			" UPDATE {$this->tables['req_versions']} " .
	  			" SET revision = {$new_rev}, log_message=' " . $this->db->prepare_string($log_msg) . "'," .
	  	        " creation_ts = {$db_now} ,author_id = {$user_id}, modifier_id = NULL, " .
	  	        " modification_ts = ";
	  	        
	  	$nullTS = $this->db->db_null_timestamp();
	  	$sql .= is_null($nullTS) ? " NULL " : " {$nullTS} ";
	  	$sql .=	" WHERE id = {$parent_id} ";
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
		$sql = 	'/* $debugMsg */' .
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
		$sql = 	" /* $debugMsg */" . 
				" SELECT COUNT(0) AS qta_rev " . 
				" FROM {$this->tables['req_revisions']} REQRV " .
				" JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.id = REQRV.parent_id " .
				" WHERE NH_REQV.parent_id = {$id} ";
				
		$rs = $this->db->get_recordset($sql);
		$sql = 	"/* $debugMsg */" .
    			" SELECT REQV.id AS version_id, REQV.version," .
    			"		 REQV.creation_ts, REQV.author_id, " .
				"		 REQV.modification_ts, REQV.modifier_id, " . 
    					 self::NO_REVISION . " AS revision_id, " .
    			" 		 REQV.revision, REQV.scope, " .
    			" 		 REQV.status,REQV.type,REQV.expected_coverage,NH_REQ.name, REQ.req_doc_id, " .
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
			$sql .=	" UNION ALL ( " .
    				" SELECT REQV.id AS version_id, REQV.version, " .
    				"		 REQRV.creation_ts, REQRV.author_id, " .
					"		 REQRV.modification_ts, REQRV.modifier_id, " . 
					"		 REQRV.id AS revision_id, " .
					"		 REQRV.revision,REQRV.scope,REQRV.status,REQRV.type, " .
    				"		 REQRV.expected_coverage,REQRV.name,REQRV.req_doc_id, " .
    				"		 COALESCE(REQRV.log_message,'') as log_message" .
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
  				$rs[$ap]['last_editor'] = $user ? $user->getDisplayName() : $labels['undefined'];
  			}
  		}
  		
  		$history = $rs;
  		if( $my['options']['decode_user'] && !is_null($history) )
  		{
  			$this->decode_users($history);
  			// $userCache = null;  // key: user id, value: display name
  			// $key2loop = array_keys($history);
  			// $labels['undefined'] = lang_get('undefined');
  			// $user_keys = array('author' => 'author_id', 'modifier' => 'modifier_id');
  			// foreach( $key2loop as $key )
  			// {
  			// 	foreach( $user_keys as $ukey => $userid_field)
  			// 	{
  			// 		$history[$key][$ukey] = '';
  			// 		if(trim($history[$key][$userid_field]) != "")
  			// 		{
  			// 			if( !isset($userCache[$history[$key][$userid_field]]) )
  			// 			{
  			// 				$user = tlUser::getByID($this->db,$history[$key][$userid_field]);
  			// 				$history[$key][$ukey] = $user ? $user->getDisplayName() : $labels['undefined'];
  			// 				$userCache[$history[$key][$userid_field]] = $history[$key][$ukey];
  			// 			}
  			// 			else
  			// 			{
  			// 				$history[$key][$ukey] = $userCache[$history[$key][$userid_field]];
  			// 			}
  			// 		}
  			// 	}	
  			// }
		}
 
    	return $history;
	}
	
	
	/**
	 * 
	 *
 	 */
	function get_version($version_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$sql = 	" /* $debugMsg */ SELECT REQ.id,REQ.srs_id,REQ.req_doc_id," . 
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
		return is_null($dummy) ? null : $dummy;	
	}	



	/**
	 * 
	 *
	 * @internal revision
	 * 20110306 - franciscom - fixed wrong mapping for REQREV ID on output recordset.
	 *
 	 */
	function get_revision($revision_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		$sql = 	" /* $debugMsg */ SELECT REQ.id,REQ.srs_id,REQ.req_doc_id," . 
		   		" REQRV.scope,REQRV.status,REQRV.type,REQRV.active," . 
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
		return is_null($dummy) ? null : $dummy;	
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
	 * @internal revision
	 * 20110306 - franciscom - created
	 *
 	 */
	function get_version_revision($version_id,$revision_access)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$sql = 	"/* $debugMsg */";
		
		if( isset($revision_access['number']) )
		{	
			$rev_number = intval($revision_access['number']);

			// we have to tables to search on
			// Req Versions -> holds LATEST revision
			// Req Revisions -> holds other revisions
			$sql .= " SELECT NH_REQV.parent_id AS req_id, REQV.id AS version_id, REQV.version," .
    				"		 REQV.creation_ts, REQV.author_id, " .
					"		 REQV.modification_ts, REQV.modifier_id, " . 
    						 self::NO_REVISION . " AS revision_id, " .
    				" 		 REQV.revision, REQV.scope, " .
    				" 		 REQV.status,REQV.type,REQV.expected_coverage,NH_REQ.name, REQ.req_doc_id, " .
    				" COALESCE(REQV.log_message,'') AS log_message, NH_REQ.name AS title " .
    				" FROM {$this->tables['req_versions']}  REQV " .
					" JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.id = REQV.id " .
					" JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = NH_REQV.parent_id " .
					" JOIN {$this->tables['requirements']} REQ ON REQ.id = NH_REQ.id " .
					" WHERE NH_REQV.id = {$version_id} AND REQV.revision = {$rev_number} "; 

			$sql .=	" UNION ALL ( " .
    				" SELECT NH_REQV.parent_id AS req_id, REQV.id AS version_id, REQV.version, " .
    				"		 REQRV.creation_ts, REQRV.author_id, " .
					"		 REQRV.modification_ts, REQRV.modifier_id, " . 
					"		 REQRV.id AS revision_id, " .
					"		 REQRV.revision,REQRV.scope,REQRV.status,REQRV.type, " .
    				"		 REQRV.expected_coverage,REQRV.name,REQRV.req_doc_id, " .
    				"		 COALESCE(REQRV.log_message,'') as log_message, NH_REQ.name AS title " .
					" FROM {$this->tables['req_versions']} REQV " .
					" JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.id = REQV.id " .
					" JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = NH_REQV.parent_id " .
					" JOIN {$this->tables['requirements']} REQ ON REQ.id = NH_REQ.id " .
					" JOIN {$this->tables['req_revisions']} REQRV " .
					" ON REQRV.parent_id = REQV.id " . 
					" WHERE NH_REQV.id = {$version_id} AND REQRV.revision = {$rev_number} ) ";
		
		}	
		else
		{	
			// revision_id is present ONLY on req revisions table, then we do not need UNION
 			$sql .=	" SELECT NH_REQV.parent_id AS req_id, REQV.id AS version_id, REQV.version, " .
    				"		 REQRV.creation_ts, REQRV.author_id, " .
					"		 REQRV.modification_ts, REQRV.modifier_id, " . 
					"		 REQRV.id AS revision_id, " .
					"		 REQRV.revision,REQRV.scope,REQRV.status,REQRV.type, " .
    				"		 REQRV.expected_coverage,REQRV.name,REQRV.req_doc_id, " .
    				"		 COALESCE(REQRV.log_message,'') as log_message, NH_REQ.name AS title " .
					" FROM {$this->tables['req_versions']} REQV " .
					" JOIN {$this->tables['nodes_hierarchy']} NH_REQV ON NH_REQV.id = REQV.id " .
					" JOIN {$this->tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = NH_REQV.parent_id " .
					" JOIN {$this->tables['requirements']} REQ ON REQ.id = NH_REQ.id " .
					" JOIN {$this->tables['req_revisions']} REQRV " .
					" ON REQRV.parent_id = REQV.id " . 
					" WHERE NH_REQV.id = {$version_id} AND REQRV.revision_id = " . intval($revision_access['id']);
		}
		$rs = $this->db->get_recordset($sql);
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
		$generated = $title2check;
  		$attr = array('key' => 'title', 'value' => $title2check);
    	$getOptions = array('output' => 'minimun', 'check_criteria' => 'likeLeft');
    	
    	// search need to be done in like to the left
		$itemSet = $this->getByAttribute($attr,$tproject_id,$parent_id,$getOptions);

		// we borrow logic (may be one day we can put it on a central place) from
		// testcase class create_tcase_only()
		
		if( !is_null($itemSet) && ($siblingQty=count($itemSet)) > 0 )
		{
			$fieldSize = config_get('field_size');
			$reqCfg = config_get('req_cfg');
			$mask = $reqCfg->duplicated_name_algorithm->text;
			
			$title_max_len = $fieldSize->requirement_title;
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

	
} // class end
?>