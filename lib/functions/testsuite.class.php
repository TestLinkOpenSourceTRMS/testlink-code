<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testsuite.class.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2006/03/03 16:21:03 $
 * @author franciscom
 *
 */

require_once( dirname(__FILE__). '/tree.class.php' );
class testsuite
{

var $db;

function testsuite(&$db)
{
  $this->db = $db;	
}

// 20060226 - franciscom
function create($parent_id,$name,$details,
                $check_duplicate_name=0,
                $action_on_duplicate_name='allow_repeat')
{

  // Create Node Manager 
  $tree_manager = New tree($this->db);
	$node_types_descr_id=$tree_manager->get_available_node_types();
  $node_types_id_descr=array_flip($node_types_descr_id);
   
  //echo "<pre>debug" . _FUNCTION_; print_r($hash_id_descr); echo "</pre>";   
    
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
		
    //echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";

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
		  //echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";
		                              
		$result = $this->db->exec_query($sql);
		if ($result)
		{
			$ret['id'] = $tsuite_id;
		}
		else
		{
			$ret['msg'] = $this->db->error_msg();
		}
	}
	return($ret);
}


function update()
{
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
  
  //echo "<pre>debug"; print_r($item); echo "</pre>";
  		
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





} // end class

?>
