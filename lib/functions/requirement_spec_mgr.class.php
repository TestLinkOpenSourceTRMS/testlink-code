<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: requirement_spec_mgr.class.php,v $
 *
 * @version $Revision: 1.28 $
 * @modified $Date: 2009/03/23 08:10:18 $ by $Author: franciscom $
 * @author Francisco Mancardi
 *
 * Manager for requirement specification (requirement container)
 *
 * rev: 20090322 - franciscom - xmlToMapReqSpec()
 *      20090321 - franciscom - added customFieldValuesAsXML() to improve exportReqSpecToXML()
 *
 *      20090315 - franciscom - added delete_deep();
 *
 *      20090222 - franciscom - added getReqTree(), get_by_id() added node_order in result
 *                              exportReqSpecToXML() (will be available on TL 1.9)
 *
 *      20090111 - franciscom - BUGID 1967 - html_table_of_custom_field_inputs()
 *                                           get_linked_cfields()
 *
 *      20080830 - franciscom - changes in create() for future use
 *                              changes to use node_order field from nodes hierachy
 *                              table when manage requirements. (get_requirements())
 * 
 *      20080419 - franciscom - bug on update(), no control for duplicate.
 *
 *      20080318 - franciscom - thanks to Postgres have found code that must be removed
 *                              after req_specs get it's id from nodes hierarchy
 * 
 *      20080309 - franciscom - changed return value for get_by_id()
*/
require_once("attachment.class.php");
require_once( dirname(__FILE__) . '/attachments.inc.php' );

class requirement_spec_mgr extends tlObjectWithAttachments
{
  const CASE_SENSITIVE=0;
  const CASE_INSENSITIVE=1;
  
	var $db;
  var $cfield_mgr;
  var $tree_mgr;

  var $object_table="req_specs";
  var $requirements_table="requirements";
  var $req_coverage_table='req_coverage';
  var $nodes_hierarchy_table="nodes_hierarchy";

  var $import_file_types = array("csv" => "CSV",
                                 "csv_doors" => "CSV (Doors)",
                                 "XML" => "XML",
								 "DocBook" => "DocBook");

  var $export_file_types = array("XML" => "XML");
  var $my_node_type;

  /*
    function: requirement_spec_mgr
              contructor

    args: db: reference to db object

    returns: instance of requirement_spec_mgr

  */
	function requirement_spec_mgr(&$db)
	{
		$this->db = &$db;
		$this->cfield_mgr = new cfield_mgr($this->db);

		$this->tree_mgr =  new tree($this->db);
		$node_types_descr_id=$this->tree_mgr->get_available_node_types();
		$node_types_id_descr=array_flip($node_types_descr_id);
		$this->my_node_type=$node_types_descr_id['requirement_spec'];

		tlObjectWithAttachments::__construct($this->db,$this->object_table);
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
          title
          scope
          countReq
          user_id: requirement spec author
          type:

    returns: map with following keys:
             status_ok -> 1/0
             msg -> some simple message, useful when status_ok ==0
             id -> id of requirement specification

    rev:20080830 - franciscom -  added new argument parent_id
        20080318 - franciscom - removed code to get last inserted id

  */
	function create($tproject_id,$parent_id,$title, $scope, $countReq,$user_id,$type = 'n',$node_order=null)
	{
	  echo "\$tproject_id,\$parent_id,\$title:$tproject_id,$parent_id,$title<br>";
		$result=array();

    $result['status_ok'] = 0;
		$result['msg'] = 'ko';
		$result['id'] = 0;

    $title=trim($title);

    $chk=$this->check_title($title,$tproject_id);  // NEED TO BE REFACTORED
		if ($chk['status_ok'] || true)
		{
		  $name=$title;
		  // 20090322 - francisco.mancardi@gruppotesi.com
		  // 	function new_node($parent_id,$node_type_id,$name='',$node_order=0,$node_id=0) 
		  $req_spec_id = $this->tree_mgr->new_node($parent_id,$this->my_node_type,$name,$node_order);

			$sql = "INSERT INTO {$this->object_table} " .
			       " (id, testproject_id, title, scope, type, total_req, author_id, creation_ts) " .
					   " VALUES (" . $req_spec_id . "," .
					                 $tproject_id . ",'" . $this->db->prepare_string($title) . "','" .
					                 $this->db->prepare_string($scope) .  "','" .
					                 $this->db->prepare_string($type) . "','" .
					                 $this->db->prepare_string($countReq) . "'," .
					                 $user_id . ", " . $this->db->db_now() . ")";

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
		else
		{
		  $result['msg']=$chk['msg'];
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
  		$sql = " SELECT REQ_SPEC.*, '' AS author, '' AS modifier, NH.node_order " .
  	    	   " FROM {$this->object_table} REQ_SPEC,  {$this->nodes_hierarchy_table} NH" .
				" WHERE REQ_SPEC.id = NH.id " . 
				" AND REQ_SPEC.id = {$id}";
  	       
		$recordset = $this->db->get_recordset($sql);
  	
	    $rs = null;
	    if(!is_null($recordset))
	    {
	        // Decode users
	        $rs = $recordset[0];
	        if(strlen(trim($rs['author_id'])) > 0)
	        {
	            $user = tlUser::getByID($this->db,$rs['author_id']);
	            $rs['author'] = $user->getDisplayName();
	        }
	      
	        if(strlen(trim($rs['modifier_id'])) > 0)
	        {
	            $user = tlUser::getByID($this->db,$rs['modifier_id']);
	            $rs['modifier'] = $user->getDisplayName();
	        }
	    }  	
	
	    return $rs;
  }



/**
 * get analyse based on requirements and test specification
 *
 * @param integer $srs_id
 * @return array Coverage in three internal arrays: covered, uncovered, nottestable REQ
 * @author martin havlat
 */
function get_coverage($id)
{
  $req_mgr = new requirement_mgr($this->db);

  $order_by=" ORDER BY req_doc_id,title";
	$output = array( 'covered' => array(), 'uncovered' => array(),
					         'nottestable' => array()	);

	// get requirements
	$sql_common = "SELECT id,title,req_doc_id " .
	              " FROM {$this->requirements_table} WHERE srs_id={$id}";
	$sql = $sql_common . " AND status='" . VALID_REQ . "' {$order_by}";
	$arrReq = $this->db->get_recordset($sql);

	// get not-testable requirements
	$sql = $sql_common . " AND status='" . NON_TESTABLE_REQ . "' {$order_by}";
	$output['nottestable'] = $this->db->get_recordset($sql);

	// get coverage
	if (sizeof($arrReq))
	{
		foreach ($arrReq as $req)
		{
			// collect TC for REQ
			$arrCoverage = $req_mgr->get_coverage($req['id']);

			if (count($arrCoverage) > 0) {
				// add information about coverage
				$req['coverage'] = $arrCoverage;
				$output['covered'][] = $req;
			} else {
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
	$output = array();

	// get nottestable REQs
	$sql = "SELECT count(*) AS cnt " .
	       " FROM {$this->requirements_table} WHERE srs_id={$id} " .
			   " AND status='" . TL_REQ_STATUS_NOT_TESTABLE . "'";

	$output['notTestable'] = $this->db->fetchFirstRowSingleColumn($sql,'cnt');

	$sql = "SELECT count(*) AS cnt FROM {$this->requirements_table} WHERE srs_id={$id}";
	$output['total'] = $this->db->fetchFirstRowSingleColumn($sql,'cnt');

	$sql = "SELECT total_req FROM {$this->object_table} WHERE id={$id}";
	$output['expectedTotal'] = $this->db->fetchFirstRowSingleColumn($sql,'total_req');

	if ($output['expectedTotal'] == 0)
		$output['expectedTotal'] = $output['total'];
	
	$sql = " SELECT DISTINCT requirements.id " .
	       " FROM {$this->requirements_table} requirements, {$this->req_coverage_table} req_coverage " .
	       " WHERE requirements.srs_id={$id}" .
				 " AND requirements.id=req_coverage.req_id";
	$result = $this->db->exec_query($sql);
	if (!empty($result))
		$output['covered'] = $this->db->num_rows($result);
	
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
		$sql = "SELECT REQ_SPEC.*, NH.node_order " .
		       " FROM {$this->object_table} REQ_SPEC, {$this->nodes_hierarchy_table} NH " .
		       " WHERE NH.id=REQ_SPEC.id" .
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
  function update($id,$title, $scope, $countReq, $user_id,$type = TL_REQ_STATUS_NOT_TESTABLE)
  {
	  $result['status_ok'] = 1;
	  $result['msg'] = 'ok';

    $title=trim_and_limit($title);
     
    $nhinfo = $this->tree_mgr->get_node_hierachy_info($id); 
    $chk=$this->check_title($title,$nhinfo['parent_id'],$id);
    
		if ($chk['status_ok'])
		{
	      $db_now = $this->db->db_now();
		    $sql = " UPDATE {$this->object_table} SET title='" . $this->db->prepare_string($title) . "', " .
		           " scope='" . $this->db->prepare_string($scope) . "', " .
		           " type='" . $this->db->prepare_string($type) . "', " .
		           " total_req ='" . $this->db->prepare_string($countReq) . "', " .
		           " modifier_id={$user_id},modification_ts={$db_now} WHERE id={$id}";
        
        
        
		    if (!$this->db->exec_query($sql))
		    {
		    	$result['msg']=lang_get('error_updating_reqspec');
  	      $result['status_ok'] = 0;
	      }
        
        if( $result['status_ok'] )
        {
  	      // need to update node on tree
  	    	$sql = " UPDATE {$this->nodes_hierarchy_table} " .
  	    	       " SET name='" . $this->db->prepare_string($title) . "'" .
  	    	       " WHERE id={$id}";
        
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

		  $sql = "DELETE FROM {$this->nodes_hierarchy_table} WHERE id = {$id}";
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
    $this->tree_mgr->delete_subtree_objects($id);
    $this->delete($id);
  }



  /*
    function: get_requirements
              get requirements contained in a requirement spec


    args: id: req spec id
          [range]: default 'all'
          [testcase_id]: default null
                         if !is_null, is used as filter
          [order_by]

    returns: array of rows

    rev: 20080830 - franciscom - changed to get node_order from nodes_hierarchy table
  */
function get_requirements($id, $range = 'all', $testcase_id = null,
                          $order_by=" ORDER BY NH.node_order,title,req_doc_id")
{
	$sql = '';
	switch($range)
	{
		case 'all';
			$sql = " SELECT requirements.*, NH.node_order" .
			         " FROM {$this->requirements_table} requirements, {$this->nodes_hierarchy_table} NH" .
			         " WHERE requirements.id=NH.id " .
			         " AND srs_id={$id}";
			break;

		case 'assigned':
			$sql = "SELECT requirements.*, NH.node_order" .
			       " FROM {$this->requirements_table} requirements, {$this->nodes_hierarchy_table} NH, " .
			       " {$this->req_coverage_table} req_coverage " .
		         " WHERE requirements.id=NH.id " .
			       " AND req_coverage.req_id=requirements.id " .
			       " AND srs_id={$id} ";
		       
			if(!is_null($testcase_id))
			{       
		    	$sql .= " AND req_coverage.testcase_id={$testcase_id}";
	  		}
	  		break;
	}
	if(!is_null($order_by))
	{
		$sql .= $order_by;
  	}
  	return $this->db->get_recordset($sql);
}



  /*
    function: get_by_title
              get req spec information using title as access key.

    args : title: req spec title
           [tproject_id]
           [case_analysis]: control case sensitive search.
                            default 0 -> case sensivite search

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
  function get_by_title($title,$tproject_id=null,$case_analysis=self::CASE_SENSITIVE)
  {
  	$output=null;
  	$title=trim($title);

    $the_title=$this->db->prepare_string($title);

  	$sql = "SELECT * FROM {$this->object_table} ";

    switch ($case_analysis)
    {
        case self::CASE_SENSITIVE:
            $sql .= " WHERE title='{$the_title}'";
        break;

        case self::CASE_INSENSITIVE:
            $sql .= " WHERE UPPER(title)='" . strtoupper($the_title) . "'";    
        break;
    }

  	if( !is_null($tproject_id) )
  	{
  	  $sql .= " AND testproject_id={$tproject_id}";
		}
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
           [tproject_id]: default null -> do check for tile uniqueness
                                          system wide.
                          valid id: only inside testproject with this id.

           [id] 
           [case_analysis]: control case sensitive search.
                            default 0 -> case sensivite search

    returns:

  */
  function check_title($title,$tproject_id=null,$id=null,$case_analysis=self::CASE_SENSITIVE)
  {
    $ret['status_ok']=1;
    $ret['msg']='';

    $title=trim($title);

  	if (!strlen($title))
  	{
  	  $ret['status_ok']=0;
  		$ret['msg'] = lang_get("warning_empty_req_title");
  	}

  	if($ret['status_ok'])
  	{
  	  $ret['msg']='ok';
      $rs=$this->get_by_title($title,$tproject_id,$case_analysis);

  		if(!is_null($rs) && (is_null($id) || !isset($rs[$id])))
      {
  		  $ret['msg']=sprintf(lang_get("warning_duplicate_req_title"),$title);
        $ret['status_ok']=0;
  	  }
  	}
  	return($ret);
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
  function get_requirements_count($id, $range = 'all', $testcase_id = null)
  {
      $sql='';
	    switch($range)
	    {
	      case 'all';
	      $sql = " SELECT count(id) AS requirements_qty" .
	             " FROM {$this->requirements_table}" .
	             " WHERE srs_id={$id}";
	      break;
      
      
	      case 'assigned':
	    	$sql = " SELECT count(requirements.id) AS requirements_qty" .
	    	       " FROM {$this->requirements_table} requirements, " .
	    	       " {$this->req_coverage_table} req_coverage " .
	             " WHERE req_coverage.req_id=requirements.id " .
	    	       " AND srs_id={$id} ";
	    	       
	    	if( !is_null($testcase_id) )
		    {       
		        $sql .= " AND req_coverage.testcase_id={$testcase_id}";
	      }
	      break;
	    }
	    return $this->db->fetchOneValue($sql);
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
    // function get_subtree($node_id,$exclude_node_types=null,$exclude_children_of=null,
    //                           $exclude_branches=null,$and_not_in_clause='',
    //                           $bRecursive = false,
    //                           $order_cfg=array("type" =>'spec_order'),$key_type='std')
    $map = $this->tree_mgr->get_subtree($id,null,null,null,'',true);
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
    $xmlData = "<req_spec title=\"" . htmlspecialchars($containerData['title']). '" >' .
               "\n<node_order><![CDATA[{$containerData['node_order']}]]></node_order>\n" .
	             "<scope><![CDATA[{$containerData['scope']}]]> \n</scope>{$cfXML}";
	}
	else
	{
		$xmlData = "<requirement>";
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
	else
	{
		$xmlData .= "</requirement>";
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
    
	  $dummy=array();
	  $dummy['node_order'] = (int)$xml_item->node_order;
	  $dummy['scope'] = (string)$xml_item->scope;
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
	  $mapped[]=array('req_spec' => $dummy, 'requirements' => null);
    
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
	$enabled = 1;
	if (!is_null($id) && $id > 0)
	{
		$req_spec_info = $this->get_by_id($id);
		$tproject_id = $req_spec_info['testproject_id'];
	}
	$cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,null,
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
	$cf_smarty = '';
  $cf_map = $this->get_linked_cfields($id,$tproject_id);

	if(!is_null($cf_map))
	{
		$cf_smarty = "<table>";
		foreach($cf_map as $cf_id => $cf_info)
		{
      $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label']));

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
	$cf_smarty = '';
  $cf_map = $this->get_linked_cfields($id,$tproject_id);
	if(!is_null($cf_map))
	{
		foreach($cf_map as $cf_id => $cf_info)
		{
			// if user has assigned a value, then node_id is not null
			if($cf_info['node_id'])
			{
        $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label']));

				$cf_smarty .= '<tr><td class="labelHolder">' .
								htmlspecialchars($label) . ":</td><td>" .
								$this->cfield_mgr->string_custom_field_value($cf_info,$id) .
								"</td></tr>\n";
			}
		}

		if(strlen(trim($cf_smarty)) > 0)
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
    $cfMap=$this->get_linked_cfields($id,$tproject_id);
		if( !is_null($cfMap) && count($cfMap) > 0 )
	  {
        $xml = $this->cfield_mgr->exportValueAsXML($cfMap);
    }
    return $xml;
 }


 /**
  * 
  *
  *
  *
  */
  function createFromXML($xml,$tproject_id,$parent_id,$author_id)
  {
      $items=$this->xmlToMapReqSpec($xml);
      $req_mgr = new requirement_mgr($this->db);
      
      new dBug($items);
      
      $loop2do=count($items);
      $container_id[0]=is_null($parent_id) || $parent_id==0 ? $tproject_id : $parent_id;
	    for($idx = 0;$idx < $loop2do; $idx++)
	    {
         $elem=$items[$idx]['req_spec'];
         $depth=$elem['level'];
         $result=$this->create($tproject_id,$container_id[$depth], $elem['title'],$elem['scope'],0,$author_id);
         if($result['status_ok'])
         {
             $container_id[$depth+1]=$result['id']; 
         
             // work on requirements
             $requirementSet=$items[$idx]['requirements'];
             $items2insert=count($requirementSet);
	           for($jdx = 0;$jdx < $items2insert; $jdx++)
	           {
                  //function create($srs_id,$reqdoc_id,$title, $scope,  $user_id,
                  //                $status = TL_REQ_STATUS_VALID, $type = TL_REQ_STATUS_NOT_TESTABLE)
                 $req=$requirementSet[$jdx];
                 $req_mgr->create($result['id'],$req['docid'],$req['title'],
                                  $req['description'],$author_id,$req['status'],$req['type']);
             }       
         } 
      }    
  }



} // class end


?>
