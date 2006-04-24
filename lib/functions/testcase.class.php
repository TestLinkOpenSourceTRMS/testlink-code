<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testcase.class.php,v $
 * @version $Revision: 1.12 $
 * @modified $Date: 2006/04/24 10:38:02 $ $Author: franciscom $
 * @author franciscom
 *
 * 20060423 - franciscom - added order_by_clause argument - get_keywords_map()
 *
 * 20060323 - franciscom - create_tcversion() interface change added $version
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
	
	$first_version=1;
  $status_ok=1;
  $ret = $this->create_tcase_only($parent_id,$name);
  if( $ret['msg'] == 'ok' )
  {
  	$ret = $this->create_tcversion($ret['id'],$first_version,$summary,$steps,
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


/* 20060323 - franciscom - create_tcversion() interface change added $version*/
function create_tcversion($id,$version,$summary,$steps,
                          $expected_results,$author_id)
{
  // get a new ids
	$tcase_version_id = $this->tree_manager->new_node($id,$this->node_types_descr_id['testcase_version']);
	
	$sql = "INSERT INTO tcversions (id,version,summary,steps,expected_results,author_id,creation_ts)
  	      VALUES({$tcase_version_id},{$version},'" .  $this->db->prepare_string($summary) . "'," . 
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
function get_by_id_old($id,$version_id=TC_ALL_VERSIONS, $get_active=0, $get_open=0)
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

/* 20060326 - franciscom - inferface changes */
/* 20060227 - franciscom */
function show($id, $user_id, $version_id=TC_ALL_VERSIONS, $action='', $msg_result='', $refresh_tree='yes')
{
	// define('DO_NOT_CONVERT',false);
	$the_tpl=config_get('tpl');
	$arrReqs = null;
	
	$can_edit = has_rights($this->db,"mgt_modify_tc");
	$tc_array = $this->get_by_id($id,$version_id);

	// 20060326 - get the status quo of execution and links of tc versions
	$status_quo_map = $this->get_versions_status_quo($id);
	
	//20060324 - franciscom
	$keywords_map=$this->get_keywords_map($id,' ORDER BY KEYWORD ASC ');
	$tc_array[0]['keywords']=$keywords_map;
	$tc_current_version = array($tc_array[0]);
	
	$tc_other_versions = array();
	$qta_versions = count($tc_array);
	if( $qta_versions > 1 )
	{
		$tc_other_versions = array_slice($tc_array,1);
	}
	
	
	
	$linked_tcversions = $this->get_linked_versions($id,'EXECUTED');
  // echo "<pre>debug \$linked_tcversions" . __FUNCTION__; print_r($linked_tcversions); echo "</pre>";
	
	// get assigned REQs
	$arrReqs = getReq4Tc($this->db,$id);
	
	$smarty = new TLSmarty;
	
	$smarty->assign('action',$action);
	$smarty->assign('sqlResult',$msg_result);
	$smarty->assign('can_edit',$can_edit);
	$smarty->assign('can_delete_testcase',$can_edit);
	$smarty->assign('can_delete_version',$can_edit);
	$smarty->assign('status_quo',$status_quo_map);
	
	$smarty->assign('refresh_tree',$refresh_tree);
	
	$smarty->assign('testcase_curr_version',$tc_current_version);
	$smarty->assign('testcase_other_versions',$tc_other_versions);
	
	
	$smarty->assign('arrReqs',$arrReqs);
	$smarty->assign('view_req_rights', has_rights($this->db,"mgt_view_req")); 
	$smarty->assign('opt_requirements', $_SESSION['testprojectOptReqs']); 	
	
	// 20060423 - franciscom
	$smarty->assign('keywords_map',$keywords_map);
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
function check_link_and_exec_status($id)
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


/* 20060326 - franciscom - interface changed */
/* 20060305 - franciscom */
function delete($id,$version_id=TC_ALL_VERSIONS)
{
	$this->_execution_delete($id,$version_id);
	$this->_blind_delete($id,$version_id);
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
		$sql="SELECT NH.parent_id AS testcase_id, NH.id AS tcversion_id,
		             tcversions.*,
		             TTC.testplan_id, TTC.tcversion_id
		      FROM   nodes_hierarchy NH,tcversions,testplan_tcversions TTC
	  	    WHERE  TTC.tcversion_id = tcversions.id 
	  	    AND    tcversions.id = NH.id
	      	AND    NH.parent_id = {$id}"; 
	  break;
	      	
    case "EXECUTED":
		$sql="SELECT NH.parent_id AS testcase_id, NH.id AS tcversion_id,
		             tcversions.*,
		             TTC.testplan_id, TTC.tcversion_id,
		             executions.id AS execution_id
		      FROM   nodes_hierarchy NH,tcversions,testplan_tcversions TTC,executions       
	    	  WHERE  TTC.tcversion_id = tcversions.id
	    	  AND    executions.tcversion_id = tcversions.id
	  	    AND    tcversions.id = NH.id
	      	AND    NH.parent_id = {$id}"; 
    break;

    case "NOT_EXECUTED":
		$sql="SELECT NH.parent_id AS testcase_id, NH.id AS tcversion_id,
		             tcversions.*,
		             TTC.testplan_id, TTC.tcversion_id
		      FROM   nodes_hierarchy NH,tcversions,testplan_tcversions TTC
	    	  WHERE  TTC.tcversion_id = tcversions.id
	  	    AND    tcversions.id = NH.id
	      	AND    NH.parent_id = {$id}
          AND    tcversions.id NOT IN ( SELECT tcversion_id FROM executions
                                        WHERE executions.tcversion_id = tcversions.id )";
    break;
    
  }
  
  // 20060402 - franciscom
	// $recordset = $this->db->get_recordset($sql);
  $recordset = $this->db->fetchRowsIntoMap($sql,'tcversion_id');
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
function _blind_delete($id, $version_id=TC_ALL_VERSIONS)
{
    $sql = array();

    if( $version_id == TC_ALL_VERSIONS)
    {    
    		$sql[]="DELETE FROM testcase_keywords WHERE testcase_id = {$id}";
    		$sql[]="DELETE FROM req_coverage WHERE testcase_id = {$id}";
 
   			$sql[]="DELETE FROM testplan_tcversions 
                WHERE tcversion_id IN (SELECT nodes_hierarchy.id 
                                       FROM nodes_hierarchy WHERE nodes_hierarchy.parent_id = {$id})";
        $sql[]="DELETE FROM tcversions 
                WHERE tcversions.id IN (SELECT nodes_hierarchy.id 
                                        FROM nodes_hierarchy WHERE nodes_hierarchy.parent_id = {$id})";
	
        $item_id=$id;
    }
    else
    {
   			$sql[]="DELETE FROM testplan_tcversions 
                WHERE tcversion_id = {$version_id}";
        $sql[]="DELETE FROM tcversions 
                WHERE tcversions.id = {$version_id}";
    	
    	  $item_id=$version_id;
    }

    foreach ($sql as $the_stm)
    {
    		$result = $this->db->exec_query($the_stm);
    }
    $this->tree_manager->delete_subtree($item_id);

}


/*
Delete the following info:
bugs
executions
*/
function _execution_delete($id,$version_id=TC_ALL_VERSIONS)
{
	  $sql = array();
		if( $version_id	== TC_ALL_VERSIONS )
		{ 
    		$sql[]="DELETE FROM execution_bugs 
          		  WHERE execution_id IN (SELECT execution_id FROM executions 
              		                     WHERE tcversion_id IN (SELECT nodes_hierarchy.id 
                  		                                        FROM nodes_hierarchy 
                      		                                    WHERE nodes_hierarchy.parent_id = {$id}))";

    		$sql[]="DELETE FROM executions 
        		    WHERE tcversion_id IN (SELECT nodes_hierarchy.id 
            		                       FROM nodes_hierarchy WHERE nodes_hierarchy.parent_id = {$id})";
    }
    else
    {
    		$sql[]="DELETE FROM execution_bugs 
        	  	  WHERE execution_id IN (SELECT execution_id FROM executions 
              		                     WHERE tcversion_id = {$version_id})";
    	
    		$sql[]="DELETE FROM executions 
        		    WHERE tcversion_id = {$version_id}";
    }

    foreach ($sql as $the_stm)
    {
    		$result = $this->db->exec_query($the_stm);
    }
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
	$status_ok=1;
	$tcase_info = $this->get_by_id($id);
	$new_tc = $this->create_tcase_only($parent_id,$tcase_info[0]['name']);
  $qta_tcversions = count($tcase_info);
  
  foreach( $tcase_info as $tcversion )
  {
    $this->create_tcversion($new_tc['id'],$tcversion['version'],
                                          $tcversion['summary'],$tcversion['steps'],
                                          $tcversion['expected_results'],$tcversion['author_id']);
  }
  return($status_ok);
} // end function
	
	
/* 20060323 - franciscom */
function create_new_version($id,$user_id)
{
  // get a new id
  $tcversion_id = $this->tree_manager->new_node($id,$this->node_types_descr_id['testcase_version']);
	
  // get last version for this test case
  $last_version_info =  $this->get_last_version_info($id);  
  $this->copy_tcversion($last_version_info['id'],$tcversion_id,$last_version_info['version']+1,$user_id);
    
  $ret['id'] = $tcversion_id;
  $ret['msg'] = 'ok';
  return ($ret);
}


/* 20060323 - franciscom */
function get_last_version_info($id)
{
	
	$sql="SELECT MAX(version) 
	      FROM tcversions,nodes_hierarchy
	      WHERE nodes_hierarchy.id = tcversions.id
	      AND   nodes_hierarchy.parent_id = {$id} ";
	
	$recordset = $this->db->get_recordset($sql);
  $max_version=$recordset[0][0];
	
	$sql="SELECT tcversions.*
	      FROM tcversions,nodes_hierarchy
	      WHERE version = {$max_version}
        AND nodes_hierarchy.id = tcversions.id
	      AND nodes_hierarchy.parent_id = {$id}";
	
	
	
	/*
	$sql="SELECT tcversions.*
	      FROM tcversions,nodes_hierarchy
	      WHERE version = ( SELECT MAX(version) 
	                        FROM tcversions
	                        WHERE nodes_hierarchy.id = tcversions.id
	                        AND   nodes_hierarchy.parent_id = {$id} )
        AND nodes_hierarchy.id = tcversions.id
	      AND nodes_hierarchy.parent_id = {$id}";
	*/
	      
  $recordset = $this->db->get_recordset($sql);
	return($recordset[0]);
}


/* 20060323 - franciscom	*/ 
function copy_tcversion($from_tcversion_id,$to_tcversion_id,$as_version_number,$user_id)
{

    $now = $this->db->db_now();
		$sql="INSERT INTO tcversions (id,version,author_id,creation_ts,
		                              summary,steps,expected_results,importance)
          SELECT {$to_tcversion_id} AS id, {$as_version_number} AS version,
                 {$user_id} AS author_id, {$now} AS creation_ts,
                 summary,steps,expected_results,importance
          FROM tcversions 
          WHERE id={$from_tcversion_id} ";
    $result = $this->db->exec_query($sql);
}	
	
// 20060313 - franciscom
function get_by_id_bulk($id,$version_id=TC_ALL_VERSIONS, $get_active=0, $get_open=0)
{
	
	if( is_array($id) )
	{
		  $tcid_list = implode(",",$id);
			$where_clause = " WHERE nodes_hierarchy.parent_id IN ($tcid_list) ";
			$where_clause_names = " WHERE nodes_hierarchy.id IN ($tcid_list) ";
	}
	else
	{
			$where_clause = " WHERE nodes_hierarchy.parent_id = {$id} ";
			$where_clause_names = " WHERE nodes_hierarchy.id = {$id} ";
	}
	
  // 20060312 - franciscom
	$sql = " SELECT nodes_hierarchy.parent_id AS testcase_id, 
	                tcversions.*, users.first AS author_first_name, users.last AS author_last_name,
	                '' AS updater_first_name, '' AS updater_last_name
	         FROM nodes_hierarchy JOIN tcversions ON nodes_hierarchy.id = tcversions.id 
                          LEFT OUTER JOIN users ON tcversions.author_id = users.id
           {$where_clause} ORDER BY tcversions.version DESC";
  $recordset = $this->db->get_recordset($sql);
  
  
  if($recordset)
  {
  	 // get the names
	   $sql = " SELECT nodes_hierarchy.id AS testcase_id, nodes_hierarchy.name 
	            FROM nodes_hierarchy {$where_clause_names} ";
	   
	   $the_names = $this->db->get_recordset($sql);
     if($the_names)
     {
    	  foreach ($recordset as  $the_key => $row )
    	  {
          reset($the_names);
          foreach($the_names as $row_n)
          {
          	  if( $row['testcase_id'] == $row_n['testcase_id'])
          	  {
          	    $recordset[$the_key]['name']= $row_n['name'];
          	    break;
          	  }
          }
  	    }
  	 }
  	
  	
	 $sql = " SELECT updater_id, users.first AS updater_first_name, users.last  AS updater_last_name
	           FROM nodes_hierarchy JOIN tcversions ON nodes_hierarchy.id = tcversions.id 
                           LEFT OUTER JOIN users ON tcversions.updater_id = users.id
             {$where_clause} and tcversions.updater_id IS NOT NULL ";
                           
    $updaters = $this->db->get_recordset($sql);
    
    if($updaters)
    { 
    	reset($recordset);
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



// 20060313 - franciscom
function get_by_id($id,$version_id=TC_ALL_VERSIONS, $get_active=0, $get_open=0)
{
	
	if( is_array($id) )
	{
		  $tcid_list = implode(",",$id);
			$where_clause = " WHERE NHA.parent_id IN ({$tcid_list}) ";
	}
	else
	{
			$where_clause = " WHERE NHA.parent_id = {$id} ";
	}
	
	if( is_array($version_id) )
	{
	    $versionid_list = implode(",",$version_id);
	    $where_clause  .= " AND tcversions.id IN ({$versionid_list}) ";
	}
	else
	{
			if($version_id != TC_ALL_VERSIONS)
			{
				$where_clause  .= " AND tcversions.id = {$version_id} ";
			}
	}

	
  $sql="SELECT	U.login AS updater_login,users.login as author_login,
		    NHB.name,NHA.parent_id AS testcase_id, tcversions.*, 
		    users.first AS author_first_name, 
		    users.last AS author_last_name, 
		    U.first AS updater_first_name, 
		    U.last  AS updater_last_name
        FROM nodes_hierarchy NHA
        JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id 
        JOIN tcversions ON NHA.id = tcversions.id 
        LEFT OUTER JOIN users ON tcversions.author_id = users.id 
        LEFT OUTER JOIN users U ON tcversions.updater_id = U.id  
        $where_clause 
        ORDER BY tcversions.version DESC";

  $recordset = $this->db->get_recordset($sql);
  
  //echo "<pre>debug" . __FUNCTION__; print_r($recordset); echo "</pre>";
  
  return($recordset ? $recordset : null);
}


// 20060326 - franciscom
//
// args:
//       id: test case id
//
// returns a recorset with the following fields
//
// tcversion_id, linked , executed
//
// linked field: will take the following values
//               NULL if the tc version is not linked to any test plan
//               tcversion_id if linked 
//
// executed field: will take the following values
//                 NULL if the tc version has not been executed in any test plan
//                 tcversion_id if has executions 
//
//
function get_versions_status_quo($id)
{
		$sql="SELECT DISTINCT NH.id AS tcversion_id,
		                      T.tcversion_id AS linked,
		                      E.tcversion_id AS executed
		      FROM   nodes_hierarchy NH
		      LEFT OUTER JOIN testplan_tcversions T ON T.tcversion_id = NH.id
		      LEFT OUTER JOIN executions E ON E.tcversion_id = NH.id
		      WHERE  NH.parent_id = {$id}"; 
		$recordset = $this->db->fetchRowsIntoMap($sql,'tcversion_id');
  	return($recordset);
}
// -------------------------------------------------------------------------------






// -------------------------------------------------------------------------------
//                            Keyword related methods	
// -------------------------------------------------------------------------------
function getKeywords($tcID,$kwID = null)
{
	$sql = "SELECT keyword_id,keywords.keyword 
	        FROM testcase_keywords,keywords 
	        WHERE keyword_id = keywords.id AND testcase_id = {$tcID}";
	if (!is_null($kwID))
	{
		$sql .= " AND keyword_id = {$kwID}";
	}	
	$tcKeywords = $this->db->fetchRowsIntoMap($sql,'keyword_id');
	
	return $tcKeywords;
} 

// 20060423 - franciscom - added order_by_clause argument
// 
function get_keywords_map($id,$order_by_clause='')
{
	$sql = "SELECT keyword_id,keywords.keyword 
	        FROM testcase_keywords,keywords 
	        WHERE keyword_id = keywords.id AND testcase_id = {$id} 
	        {$order_by_clause}";


	$map_keywords = $this->db->fetchColumnsIntoMap($sql,'keyword_id','keyword');
	return($map_keywords);
} 

// 20060409 - franciscom
/*function add_keyword($id,$kw_id)
{
	$current_kw = $this->get_keywords_map($id);
	
	if( !is_null($current_kw) && count($current_kw) > 0 )
	{
	
		
	}
 


}
*/



function addKeyword($id,$kw_id)
{
	$kw = $this->getKeywords($id,$kw_id);
	if (sizeof($kw))
		return 1;
	$sql = " INSERT INTO testcase_keywords (testcase_id,keyword_id) " .
		     " VALUES ($id,$kw_id)";

	return ($this->db->exec_query($sql) ? 1 : 0);
}

function addKeywords($id,$kw_ids)
{
	$bSuccess = 1;
	$num_kws = sizeof($kw_ids);
	for($idx = 0; $idx < $num_kws; $idx++)
	{
		$bSuccess = $bSuccess && $this->addKeyword($id,$kw_ids[$idx]);
	}
	
	return $bSuccess;
}

function deleteKeywords($tcID,$kwID = null)
{
	$sql = " DELETE FROM testcase_keywords WHERE testcase_id = {$tcID} ";
	if (!is_null($kwID))
		$sql .= " AND keyword_id = {$kwID}";
	return $this->db->exec_query($sql);
}

// -------------------------------------------------------------------------------
//                            END Keyword related methods	
// -------------------------------------------------------------------------------


/*


*/
function get_executions($id,$version_id,$tplan_id,$build_id,$exec_to_exclude=null)
{
	
	// --------------------------------------------------------------------
	if( is_array($id) )
	{
		  $tcid_list = implode(",",$id);
			$where_clause = " WHERE NHA.parent_id IN ({$tcid_list}) ";
	}
	else
	{
			$where_clause = " WHERE NHA.parent_id = {$id} ";
	}

	if( is_array($version_id) )
	{
	    $versionid_list = implode(",",$version_id);
	    $where_clause  .= " AND tcversions.id IN ({$versionid_list}) ";
	}
	else
	{
			if($version_id != TC_ALL_VERSIONS)
			{
				$where_clause  .= " AND tcversions.id = {$version_id} ";
			}
	}
	
  if( !is_null($exec_to_exclude ) )
  {
  	
			if( is_array($exec_to_exclude))
			{
			    if(count($exec_to_exclude) > 0 )
			    {
			 	  	$exec_id_list = implode(",",$exec_to_exclude);
	        	$where_clause  .= " AND EXEC.id NOT IN ({$exec_id_list}) ";
	        }
			}
			else
			{
	        $where_clause  .= " AND EXEC.id <> {$exec_id_list} ";
			}
	}
  // --------------------------------------------------------------------	
  $sql="SELECT	NHB.name,NHA.parent_id AS testcase_id, tcversions.*, 
		    users.login AS tester_login,
		    users.first AS tester_first_name, 
		    users.last AS tester_last_name, 
		    EXEC.id AS execution_id, EXEC.status, 
		    EXEC.notes AS execution_notes, EXEC.execution_ts 
	      FROM nodes_hierarchy NHA
        JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id 
        JOIN tcversions ON NHA.id = tcversions.id 
        JOIN executions EXEC ON NHA.id = EXEC.tcversion_id  
                                     AND EXEC.testplan_id = {$tplan_id}
                                     AND EXEC.build_id = {$build_id} 
        LEFT OUTER JOIN users ON EXEC.tester_id = users.id 
        $where_clause 
        ORDER BY NHA.node_order ASC, NHA.parent_id ASC, execution_id DESC";
   

  $recordset = $this->db->fetchArrayRowsIntoMap($sql,'id');
  return($recordset ? $recordset : null);
}




/* 20060330 - franciscom */
function get_last_execution($id,$version_id,$tplan_id,$build_id,$get_no_executions=0)
{
	
	$build_id_filter='';
	
	if( is_array($id) )
	{
		  $tcid_list = implode(",",$id);
			$where_clause = " WHERE NHA.parent_id IN ({$tcid_list}) ";
	}
	else
	{
			$where_clause = " WHERE NHA.parent_id = {$id} ";
	}

	if( is_array($version_id) )
	{
	    $versionid_list = implode(",",$version_id);
	    $where_clause_1 = $where_clause . " AND NHA.id IN ({$versionid_list}) ";
	    $where_clause_2= $where_clause . " AND tcversions.id IN ({$versionid_list}) ";

	}
	else
	{
			if($version_id != TC_ALL_VERSIONS)
			{
				$where_clause_1 = $where_clause . " AND NHA.id = {$version_id} ";
				$where_clause_2 = $where_clause . " AND tcversions.id = {$version_id} ";
			}
	}

  if( !is_null($build_id) )
  {
    $build_id_filter=" AND EXEC.build_id = {$build_id} ";	
  } 



  /*
  $sql="SELECT MAX(EXEC.id) AS execution_id, EXEC.status,
        NHB.name,NHA.parent_id AS testcase_id, tcversions.*, 
		    users.login AS tester_login,
		    users.first AS tester_first_name, 
		    users.last AS tester_last_name, 
		    EXEC.notes AS execution_notes, EXEC.execution_ts, EXEC.build_id,
		    builds.name AS build_name 
	      FROM nodes_hierarchy NHA
        JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id 
        JOIN tcversions ON NHA.id = tcversions.id 
        JOIN executions EXEC ON NHA.id = EXEC.tcversion_id  
                                      AND EXEC.testplan_id = {$tplan_id}
                                      {$build_id_filter}
                                      AND EXEC.status IS NOT NULL
        JOIN builds     ON builds.id = EXEC.build_id 
                           AND builds.testplan_id = {$tplan_id}                                
        LEFT OUTER JOIN users ON EXEC.tester_id = users.id 
        $where_clause 
        GROUP BY tcversions.id
        ORDER BY NHA.node_order ASC, execution_id DESC";
  */
  $sql="SELECT MAX(EXEC.id) AS execution_id, EXEC.tcversion_id AS tcversion_id
  	    FROM nodes_hierarchy NHA
        JOIN executions EXEC ON NHA.id = EXEC.tcversion_id  
                                     AND EXEC.testplan_id = {$tplan_id}
                                      {$build_id_filter}
                                      AND EXEC.status IS NOT NULL
        $where_clause_1 
        GROUP BY tcversion_id";
        $recordset = $this->db->fetchColumnsIntoMap($sql,'tcversion_id','execution_id');

  $and_exec_id='';
  if( !is_null($recordset) )
  {
  	  $the_list = implode(",",$recordset);
  	  if( count($recordset) > 1 )
  	  {
  			$and_exec_id = " AND EXEC.id IN (". $the_list . ") ";
  		}
  		else
  		{
  		  $and_exec_id = " AND EXEC.id = $the_list ";
  		}
  }
  
  /*
  $sql="SELECT EXEC.id AS execution_id, EXEC.status,
        NHB.name,NHA.parent_id AS testcase_id, tcversions.*, 
		    users.login AS tester_login,
		    users.first AS tester_first_name, 
		    users.last AS tester_last_name, 
		    EXEC.notes AS execution_notes, EXEC.execution_ts, EXEC.build_id,
		    builds.name AS build_name 
	      FROM nodes_hierarchy NHA
        JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id 
        JOIN tcversions ON NHA.id = tcversions.id 
        {$executions_join}
        LEFT OUTER JOIN builds     ON builds.id = EXEC.build_id 
                           AND builds.testplan_id = {$tplan_id}                                
        LEFT OUTER JOIN users ON EXEC.tester_id = users.id 
        $where_clause 
        ORDER BY NHA.node_order ASC, NHA.parent_id ASC, execution_id DESC";
   
  */
  $executions_join=" JOIN executions EXEC ON NHA.id = EXEC.tcversion_id  
                                           AND EXEC.testplan_id = {$tplan_id}
                                           {$and_exec_id} 
                                           {$build_id_filter} ";
  if( $get_no_executions )
  {
     $executions_join = " LEFT OUTER " . $executions_join;
  }
  else
  {
     $executions_join .= " AND EXEC.status IS NOT NULL ";
  }

  $sql="SELECT EXEC.id AS execution_id, EXEC.status,
        NHB.name,NHA.parent_id AS testcase_id, tcversions.*, 
		    users.login AS tester_login,
		    users.first AS tester_first_name, 
		    users.last AS tester_last_name, 
		    EXEC.notes AS execution_notes, EXEC.execution_ts, EXEC.build_id,
		    builds.name AS build_name 
	      FROM nodes_hierarchy NHA
        JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id 
        JOIN tcversions ON NHA.id = tcversions.id 
        {$executions_join}
        LEFT OUTER JOIN builds     ON builds.id = EXEC.build_id 
                           AND builds.testplan_id = {$tplan_id}                                
        LEFT OUTER JOIN users ON EXEC.tester_id = users.id 
        $where_clause_2 
        ORDER BY NHA.node_order ASC, NHA.parent_id ASC, execution_id DESC";



  $recordset = $this->db->fetchRowsIntoMap($sql,'id');
  return($recordset ? $recordset : null);
}






} // end class
?>
