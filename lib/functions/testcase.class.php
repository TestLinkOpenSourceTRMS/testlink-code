<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testcase.class.php,v $
 * @version $Revision: 1.6 $
 * @modified $Date: 2006/03/13 09:37:49 $
 * @author franciscom
 *
 */

require_once( dirname(__FILE__). '/tree.class.php' );
class testcase
{

var $db;
var $tree_manager;
var $node_types_descr_id;
var $node_types_id_descr;
var $my_node_type;

function testcase(&$db)
{
  $this->db = &$db;	
  $this->tree_manager = New tree($this->db);
	$this->node_types_descr_id=$this->tree_manager->get_available_node_types();
  $this->node_types_id_descr=array_flip($this->node_types_descr_id);
  $this->my_node_type=$this->node_types_descr_id['testcase'];
}

// 20060226 - franciscom
function create($parent_id,$name,$summary,$steps,
                $expected_results,$author_id,$tc_order = null)
{
	
  $status_ok=1;
  $ret = $this->create_tcase_only($parent_id,$name);
  if( $ret['msg'] == 'ok' )
  {
  	$ret = $this->create_tcversion($ret['id'],$summary,$steps,
                                   $expected_results,$author_id);
  }
  return ($ret);
}



/* 20060306 - franciscom */
function create_tcase_only($parent_id,$name)
{
  // get a new id
  $tcase_id = $this->tree_manager->new_node($parent_id,
                                            $this->my_node_type,$name);
  $ret['id'] = $tcase_id;
  $ret['msg'] = 'ok';
  return ($ret);
}


/* 20060306 - franciscom */
function create_tcversion($id,$summary,$steps,
                          $expected_results,$author_id)
{
  // get a new ids
	$tcase_version_id = $this->tree_manager->new_node($id,$this->node_types_descr_id['testcase_version']);
	
	$sql = "INSERT INTO tcversions (id,version,summary,steps,expected_results,author_id,creation_ts)
  	      VALUES({$tcase_version_id},1,'" .  $this->db->prepare_string($summary) . "'," . 
	  	                           "'" . $this->db->prepare_string($steps) . "'," .
	  	                           "'" . $this->db->prepare_string($expected_results) . "'," . $author_id . "," .
                    	  	       $this->db->db_now() . ")";
	$result = $this->db->exec_query($sql);        
	$ret['msg'] = 'ok';
  if (!$result)
	{
		$ret['msg'] = $this->db->error_msg();
	}
	
  return ($ret);
}


/* 20060312 - franciscom */
function get_by_name($name)
{
	$sql = " SELECT nodes_hierarchy.id,nodes_hierarchy.name 
	         FROM nodes_hierarchy 
	         WHERE nodes_hierarchy.node_type_id={$this->my_node_type}
	         AND nodes_hierarchy.name = '" . 
	         $this->db->prepare_string($name) . "'";

  $recordset = $this->db->get_recordset($sql);
  return($recordset);
}




/*
get info for one testcase as an array, where every element is a associative array
will be useful to manage the different versions of a test case

20060227 - franciscom

*/
function get_by_id($id,$version_id=TC_ALL_VERSIONS, $get_active=0, $get_open=0)
{
  // 20060312 - franciscom
	$sql = " SELECT nodes_hierarchy.parent_id AS testcase_id, 
	                (SELECT nodes_hierarchy.name 
	                 FROM nodes_hierarchy WHERE nodes_hierarchy.id = {$id} ) AS name, 
	                tcversions.*, users.first AS author_first_name, users.last AS author_last_name,
	                '' AS updater_first_name, '' AS updater_last_name
	         FROM nodes_hierarchy JOIN tcversions ON nodes_hierarchy.id = tcversions.id 
                          LEFT OUTER JOIN users ON tcversions.author_id = users.id
           WHERE nodes_hierarchy.parent_id = {$id} ORDER BY tcversions.version DESC";
  $recordset = $this->db->get_recordset($sql);
  
  if($recordset)
  {
	 $sql = " SELECT updater_id, users.first AS updater_first_name, users.last  AS updater_last_name
	           FROM nodes_hierarchy JOIN tcversions ON nodes_hierarchy.id = tcversions.id 
                           LEFT OUTER JOIN users ON tcversions.updater_id = users.id
             WHERE nodes_hierarchy.parent_id = {$id} and tcversions.updater_id IS NOT NULL ";
                           
    $updaters = $this->db->get_recordset($sql);
    
    if($updaters)
    {
    	foreach ($recordset as  $the_key => $row )
    	{
    		if ( !is_null($row['updater_id']) )
    		{
      		foreach ($updaters as $row_upd)
      		{
            if ( $row['updater_id'] == $row_upd['updater_id'] )
            {
              $recordset[$the_key]['updater_last_name'] = $row_upd['updater_last_name'];
              $recordset[$the_key]['updater_first_name'] = $row_upd['updater_first_name'];
              break;
            }
      		}
      	}
      }
    }

  }

 
  return($recordset ? $recordset : null);
}


/*
get array of info for every test suite
without any kind of filter.
Every array element contains an assoc array with test suite info

*/
function get_all()
{
	$sql = " SELECT nodes_hierarchy.name, nodes_hierarchy.id
	         FROM  nodes_hierarchy
	         WHERE nodes_hierarchy.node_type_id={$my_node_type}";
  $recordset = $this->db->get_recordset($sql);
  return($recordset);
}


/* 20060227 - franciscom */
function show($id, $user_id)
{
	// define('DO_NOT_CONVERT',false);
	$the_tpl=config_get('tpl');
	$arrReqs = null;
	
	$can_edit = has_rights($this->db,"mgt_modify_tc");
	$tc_array = $this->get_by_id($id);
	$tc_data = $tc_array;
	if( count($tc_array) > 1 )
	{
	  $tc_data = array($tc_array[0]);
	}
	
	
	// get assigned REQs
	$arrReqs = getReq4Tc($this->db,$id);
	
	//$tc_array = array($myrowTC);
	
	$smarty = new TLSmarty;
	
	$smarty->assign('can_edit',$can_edit);
	$smarty->assign('testcase',$tc_data);
	$smarty->assign('arrReqs',$arrReqs);
	$smarty->assign('view_req_rights', has_rights($this->db,"mgt_view_req")); 
	$smarty->assign('opt_requirements', $_SESSION['testprojectOptReqs']); 	
	$smarty->display($the_tpl['tcView']);
}




// 20060226 - franciscom
function viewer_edit_new($amy_keys, $oFCK, $action, $parent_id, $id=null)
{
	$a_tpl = array( 'edit_testsuite' => 'containerEdit.tpl',
					        'new_testsuite'  => 'containerNew.tpl');
	
	$the_tpl = $a_tpl[$action];
	$component_name='';
	$smarty = new TLSmarty();
	$smarty->assign('sqlResult', null);
	$smarty->assign('containerID',$parent_id);	 
	
	$the_data = null;
	if ($action == 'edit_testsuite')
	{
		$the_data = $this->get_by_id($id);
		$name=$the_data['name'];
		$smarty->assign('containerID',$id);	
	}
	
	// fckeditor 
	foreach ($amy_keys as $key)
	{
		// Warning:
		// the data assignment will work while the keys in $the_data are identical
		// to the keys used on $oFCK.
		$of = &$oFCK[$key];
		$of->Value = isset($the_data[$key]) ? $the_data[$key] : null;
		$smarty->assign($key, $of->CreateHTML());
	}
	
	$smarty->assign('level', 'testsuite');
	$smarty->assign('name',$name);
	$smarty->assign('container_data',$the_data);
	
	$smarty->display($the_tpl);
}


// 20060303 - franciscom
function update($id,$tcversion_id,$name,$summary,$steps,
                $expected_results,$user_id,$tc_order = null)
{
$status_ok=0;

$sql = " UPDATE nodes_hierarchy SET name='" . 
         $this->db->prepare_string($name) . "' WHERE id= {$id}";

$result = $this->db->exec_query($sql);
$status_ok=$result ? 1: 0;

if( $status_ok)
{       
	// test case version
	$sql = " UPDATE tcversions 
  	       SET summary='" . $this->db->prepare_string($summary) . "'," .
    	   " steps='" . $this->db->prepare_string($steps) . "'," .
      	 " expected_results='" . $this->db->prepare_string($expected_results) .  "'," .
		   	 " updater_id={$user_id}, modification_ts = " . $this->db->db_now()  .
		   	 " WHERE tcversions.id = {$tcversion_id}";

	$result = $this->db->exec_query($sql);
	$status_ok=$result ? 1: 0;
}
      

// keywords
/*
$sql = "UPDATE mgttestcase SET keywords='" . 
	        $db->prepare_string($keywords) . "', version='" . $db->prepare_string($version) . 
	        "', title='" . $db->prepare_string($title) . "'".
		      ",summary='" . $db->prepare_string($summary) . "', steps='" . 
	      	$db->prepare_string($steps) . "', exresult='" . $db->prepare_string($outcome) . 
		      "', reviewer_id=" . $user_id . ", modified_date=CURRENT_DATE()" .
		      " WHERE id=" . $tcID;
	$result = $db->exec_query($sql);
	
	return $result ? 1: 0;

*/
return ( $status_ok);

} // end function



/*
Need to check for every version of this test case:
1. is linked to one of more test plans ?
2. if anwser is yes then, need to check if has been executed => has records on executions table
   
2. if linked but never executed


20060304 - franciscom
*/
function check_delete_condition($id)
{
  $status='no_links';
   
  // get linked versions
  $exec_status='ALL';
  $linked_tcversions = $this->get_linked_versions($id,$exec_status);
  $has_links_to_testplans = is_null($linked_tcversions) ? 0 : 1;

  if( $has_links_to_testplans )
  {
    // check if executed	
  	$linked_not_exec = $this->get_linked_versions($id,"NOT_EXECUTED");

    $status='linked_and_executed';
    if( count($linked_tcversions) == count($linked_not_exec) )
    {
      $status='linked_but_not_executed';
  	}
  }
  return ($status);
}


/* 20060305 - franciscom */
function delete($id)
{
	$this->_execution_delete($id);
	$this->_blind_delete($id);
	return 1;
}



/*
get for one tc all versions that are linked to test plans
*/
function get_linked_versions($id,$status="ALL")
{
	// status = ALL
	// status = EXECUTED
	// status = NOT_EXECUTED
	//
	switch ($status)
	{
	  case "ALL":
		$sql="SELECT * from nodes_hierarchy,tcversions,testplan_tcversions
	  	    WHERE testplan_tcversions.tcversion_id = tcversions.id 
	  	    AND   tcversions.id = nodes_hierarchy.id
	      	AND   nodes_hierarchy.parent_id = {$id}"; 
	  break;
	      	
    case "EXECUTED":
		$sql="SELECT * from nodes_hierarchy,tcversions,testplan_tcversions,executions
	    	  WHERE  testplan_tcversions.tcversion_id = tcversions.id
	    	  AND    executions.tcversion_id = tcversions.id
	  	    AND    tcversions.id = nodes_hierarchy.id
	      	AND    nodes_hierarchy.parent_id = {$id}"; 
    break;

    case "NOT_EXECUTED":
		$sql="SELECT * from nodes_hierarchy,tcversions,testplan_tcversions
	    	  WHERE  testplan_tcversions.tcversion_id = tcversions.id
	  	    AND    tcversions.id = nodes_hierarchy.id
	      	AND    nodes_hierarchy.parent_id = {$id}
          AND    tcversions.id NOT IN ( SELECT tcversion_id FROM executions
                                        WHERE executions.tcversion_id = tcversions.id )";
    break;
    
  }
  
	$recordset = $this->db->get_recordset($sql);
  return($recordset);

}

/*
Delete the following info:
req_coverage
risk_assignment
custom fields
keywords
links to test plans
tcversions
nodes from hierarchy
*/
function _blind_delete($id)
{

    $sql="DELETE FROM testcase_keywords WHERE testcase_id = {$id}";
    $result = $this->db->exec_query($sql);
    
    $sql="DELETE FROM req_coverage WHERE testcase_id = {$id}";
    $result = $this->db->exec_query($sql);

    $sql="DELETE FROM testplan_tcversions 
          WHERE tcversion_id IN (SELECT nodes_hierarchy.id 
                                 FROM nodes_hierarchy WHERE nodes_hierarchy.parent_id = {$id})";
    $result = $this->db->exec_query($sql);

    $sql="DELETE FROM tcversions 
          WHERE tcversions.id IN (SELECT nodes_hierarchy.id 
                                 FROM nodes_hierarchy WHERE nodes_hierarchy.parent_id = {$id})";
	  $result = $this->db->exec_query($sql);        
    
    
    $this->tree_manager->delete_subtree($id);

}


/*
Delete the following info:
bugs
executions
*/
function _execution_delete($id)
{
    $sql="DELETE FROM execution_bugs 
          WHERE execution_id IN (SELECT execution_id FROM executions 
                                 WHERE tcversion_id IN (SELECT nodes_hierarchy.id 
                                                        FROM nodes_hierarchy 
                                                        WHERE nodes_hierarchy.parent_id = {$id}))";
    $result = $this->db->exec_query($sql);


    $sql="DELETE FROM executions 
          WHERE tcversion_id IN (SELECT nodes_hierarchy.id 
                                 FROM nodes_hierarchy WHERE nodes_hierarchy.parent_id = {$id})";
    $result = $this->db->exec_query($sql);
}

/*

*/
function get_testproject($id)
{
  $a_path = $this->tree_manager->get_path($id);
  return ($a_path[0]['parent_id']);
}


/* 20060306 - franciscom */
function copy_to($id, $parent_id, $user_id)
{
	$tcase_info = $this->get_by_id($id);
	
	echo "<pre>debug" . __FUNCTION__ ; print_r($tcase_info); echo "</pre>";
	
	$new_tc = $this->create_tcase_only($parent_id,$tcase_info[0]['name']);
	  
  $qta_tcversions = count($tcase_info);
  
  foreach( $tcase_info as $tcversion )
  {
    $this->create_tcversion($new_tc['id'],$tcversion['summary'],$tcversion['steps'],
                                          $tcversion['expected_results'],$tcversion['author_id']);
      	
  }
  
} // end function
	
	/* KEYWORD RELATED */
	function getKeywords($tcID,$kwID = null)
	{
		$sql = "SELECT keyword_id,keywords.keyword FROM testcase_keywords,keywords WHERE keyword_id = keywords.id AND testcase_id = {$tcID}";
		if (!is_null($kwID))
			$sql .= " AND keyword_id = {$kwID}";
		$tcKeywords = $this->db->fetchRowsIntoMap($sql,'keyword_id');
		
		return $tcKeywords;
	} 
	
	function addKeyword($tcID,$kwID)
	{
		$kw = $this->getKeywords($tcID,$kwID);
		if (sizeof($kw))
			return 1;
		$sql = "INSERT INTO testcase_keywords (testcase_id,keyword_id) " .
			   " VALUES ($tcID,$kwID)";

		return ($this->db->exec_query($sql) ? 1 : 0);
	}
	
	function addKeywords($tcID,$kwIDs)
	{
		$bSuccess = 1;
		for($i = 0;$i < sizeof($kwIDs);$i++)
		{
			$bSuccess = $bSuccess && $this->addKeyword($tcID,$kwIDs[$i]);
		}
		
		return $bSuccess;
	}
	
	function deleteKeywords($tcID,$kwID = null)
	{
		$sql = "DELETE FROM testcase_keywords WHERE testcase_id = {$tcID}";
		if (!is_null($kwID))
			$sql .= " AND keyword_id = {$kwID}";
		
		return $this->db->exec_query($sql);
	}
	
	/* END KEYWORD RELATED */

} // end class
?>
