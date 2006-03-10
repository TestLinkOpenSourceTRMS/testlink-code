<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testsuite.class.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2006/03/10 07:42:44 $
 * @author franciscom
 *
 */

require_once( dirname(__FILE__). '/tree.class.php' );
require_once( dirname(__FILE__). '/testcase.class.php' ); // 20060309 - franciscom

class testsuite
{

var $db;

function testsuite(&$db)
{
  $this->db = $db;	
}

// 20060309 - franciscom
// returns hash with:	$ret['status_ok'] -> 0/1
//                    $ret['msg']
//                    $ret['id']        -> when status_ok=1, id of the new element
//
//                  
function create($parent_id,$name,$details,
                $check_duplicate_name=0,
                $action_on_duplicate_name='allow_repeat')
{
  $tree_manager = New tree($this->db);
	$node_types_descr_id=$tree_manager->get_available_node_types();
  $node_types_id_descr=array_flip($node_types_descr_id);
  
  // 20060309 - franciscom
  $ret['status_ok']=0;
  $ret['msg']='ok';
  $ret['id']=-1;
  
    
	$prefix_name_for_copy = config_get('prefix_name_for_copy');
	
	$name = trim($name);
	$ret = array('status_ok' => 1, 'id' => 0, 'msg' => 'ok');
	if ($check_duplicate_name)
	{
		
		// 1. node_type_id of the parent_id
    // $p_node_info = $tree_manager->get_by_id($parent_id);
    // $p_node_type_id = $p_node_info['node_type_id'];
    
    /*
    switch ( $hash_id_descr[$p_node_type_id] )
    {
      case 'testproject'
      $sql = " SELECT count(*) AS qty FROM testsuites,node 
			         WHERE name = '" . $this->db->prepare_string($name) . "'" . 
			       " AND testprojects.id={$parent_id} "; 
      break;
      
      case 'testsuite'
      break;
      
      	
    }
    */
    $sql = " SELECT count(*) AS qty FROM testsuites,nodes_hierarchy 
		         WHERE name = '" . $this->db->prepare_string($name) . "'" . 
		       " AND testsuites.id=nodes_hierarchy.id
		         AND node_type_id = {$node_types_descr_id['testsuite']} 
		         AND nodes_hierarchy.parent_id={$parent_id} "; 
		
		$result = $this->db->exec_query($sql);
		$myrow = $this->db->fetch_array($result);
		
		if( $myrow['qty'])
		{
			if ($action_on_duplicate_name == 'block')
			{
				$ret['status_ok'] = 0;
				$ret['msg'] = lang_get('component_name_already_exists');	
			} 
			else
			{
				$ret['status_ok'] = 1;      
				if ($action_on_duplicate_name == 'generate_new')
				{ 
					$ret['status_ok'] = 1;      
					$name = config_get('prefix_name_for_copy') . " " . $name ;      
				}
			}
		}       
	}
	
	if ($ret['status_ok'])
	{
    // get a new id
    $tsuite_id = $tree_manager->new_node($parent_id,$node_types_descr_id['testsuite']);
		$sql = "INSERT INTO testsuites (id,name,details) " .
		     	 "VALUES ({$tsuite_id},'" . $this->db->prepare_string($name) . "','" . 
				                              $this->db->prepare_string($details) . "')";
		                              
		$result = $this->db->exec_query($sql);
		if ($result)
		{
			$ret['id'] = $tsuite_id;
		}
		else
		{
			$ret['msg'] = "(" . __FUNCTION__ . ") " . $this->db->error_msg();
		}
	}
	return($ret);
}


/* 20060306 - franciscom */
function update($id, $name, $details)
{
	//TODO - check for existent name
	$sql = "UPDATE testsuites
	        SET name = '" . $this->db->prepare_string($name) . "', " .
	       "    details = '" . $this->db->prepare_string($details) . "'" .
	       " WHERE id = {$id}";
	
	$result = $this->db->exec_query($sql);
  $ret['msg']='ok';
	if (!$result)
	{
		$ret['msg'] = $this->db->error_msg();
	}
	return ($ret);	
}


function get_by_name($name)
{
	$sql = " SELECT * FROM testsuites 
	         WHERE name = '" . 
	         $this->db->prepare_string($name) . "'";

  $recordset = $this->db->get_recordset($sql);
  return($recordset);
}




/*
get info for one test suite
*/
function get_by_id($id)
{
	$sql = " SELECT * FROM testsuites 
	         WHERE id = {$id}";
  $recordset = $this->db->get_recordset($sql);
  return($recordset ? $recordset[0] : null);
}


/*
get array of info for every test suite
without any kind of filter.
Every array element contains an assoc array with test suite info

*/
function get_all()
{
	$sql = " SELECT * FROM testsuites ";
  $recordset = $this->db->get_recordset($sql);
  return($recordset);
}


/* 20060225 - franciscom */
function show($id, $sqlResult = '', $action = 'update',$modded_item_id = 0)
{
	
	$smarty = new TLSmarty;
	$smarty->assign('modify_tc_rights', has_rights($this->db,"mgt_modify_tc"));

	if($sqlResult)
	{ 
		$smarty->assign('sqlResult', $sqlResult);
		$smarty->assign('sqlAction', $action);
	}
	
	$item = $this->get_by_id($id);
  $modded_item = $item;
	if ( $modded_item_id )
	{
		$modded_item = $this->get_by_id($modded_item_id);
	}
  
	$smarty->assign('moddedItem',$modded_item);
	$smarty->assign('level', 'testsuite');
	$smarty->assign('container_data', $item);
	$smarty->display('containerView.tpl');
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


// 20060309 - franciscom
function copy_to($id, $parent_id, $user_id,
                 $check_duplicate_name=0,$action_on_duplicate_name='allow_repeat')
{
  $tree_manager = New tree($this->db);
  $tcase_mgr = New testcase($this->db);

  $node_types_descr_id=$tree_manager->get_available_node_types();
  $node_types_id_descr=array_flip($node_types_descr_id);
  
  
	$tsuite_info = $this->get_by_id($id);
	$ret = $this->create($parent_id,$tsuite_info['name'],$tsuite_info['details'],
	                     $check_duplicate_name,$action_on_duplicate_name);

	$new_tsuite_id = $ret['id'];
	
  $subtree = $tree_manager->get_subtree($id,array('testplan' => 'exclude_me'),
	                                          array('testcase' => 'exclude_my_children'));
	
	
  if (!is_null($subtree))
	{
		$the_parent_id = $new_tsuite_id;	
	  foreach($subtree as $the_key => $elem)
	  {
	  	if( $elem['parent_id'] == $id )
	  	{
	  	  $the_parent_id = $new_tsuite_id;	
	  	}
	  	
	    switch ($elem['node_type_id'])
	    {
	      case $node_types_descr_id['testcase']:
        $tcase_mgr->copy_to($elem['id'],$the_parent_id,$user_id);  	      
	      break;
	      
	      
	      case $node_types_descr_id['testsuite']:
	      $tsuite_info = $this->get_by_id($elem['id']);
	      $ret = $this->create($the_parent_id,$tsuite_info['name'],$tsuite_info['details']);      
	      $the_parent_id = $ret['id'];
	      break;
	    }
	  }
	}
	
	
} // end function




// 20060309 - franciscom
// get all test cases in the test suite and all children test suites
// no info about tcversions is returned
function get_testcases_deep($id)
{
  $tree_manager = New tree($this->db);
	$node_types_descr_id=$tree_manager->get_available_node_types();
  $node_types_id_descr=array_flip($node_types_descr_id);

  $subtree = $tree_manager->get_subtree($id,array('testplan' => 'exclude_me'),
	                                          array('testcase' => 'exclude_my_children'));
	$testcases=null;
	if( !is_null($subtree) )
	{
		$testcases = array();
	  foreach ( $subtree as $the_key => $elem)
	  {
	    if($elem['node_type_id'] == $node_types_descr_id['testcase'])
	    {
	      $testcases[]=$elem;
	    }	
	  }
	}
	
  return ($testcases); 
}




// 20060309 - franciscom
function delete_deep($id)
{
  $tree_manager = New tree($this->db);
  $tcase_mgr = New testcase($this->db);

  $node_types_descr_id=$tree_manager->get_available_node_types();
  $node_types_id_descr=array_flip($node_types_descr_id);
  
	$tsuite_info = $this->get_by_id($id);
  $subtree = $tree_manager->get_subtree($id,array('testplan' => 'exclude_me', 'testcase' => 'exclude_me'),
                                            array('testcase' => 'exclude_my_children'));
	
	// add me, to delete me 
	$subtree[]=array('id' => $id);
	$testcases = $this->get_testcases_deep($id);

  //echo "<pre>debug \$subtree" . __FUNCTION__ ; print_r($subtree); echo "</pre>";
	
  if (!is_null($subtree))
	{
    // -------------------------------------------------------------------
		$node_list = array();
		$node_list[]=$id;
	  foreach($subtree as $the_key => $elem)
	  {
      $node_list[]= $elem['id'];
	  }
    $tsuites_id_list=implode(",",$node_list);    
	
	  $sql = "DELETE FROM testsuites WHERE id IN ({$tsuites_id_list})";
		$result = $this->db->exec_query($sql);
    // -------------------------------------------------------------------

    // -------------------------------------------------------------------
    if (!is_null($testcases))
	  {
      echo "<pre>debug" . __FUNCTION__; print_r($testcases); echo "</pre>";
	    foreach($testcases as $the_key => $elem)
	    {
        $tcase_mgr->delete($elem['id']);
	    }
	  }  
    // -------------------------------------------------------------------

    $tree_manager->delete_subtree($id);

	}
} // end function




} // end class

?>
