<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testproject.class.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2006/03/06 17:31:00 $
 * @author franciscom
 *
 */

require_once( dirname(__FILE__). '/tree.class.php' );
class testproject
{

var $db;

function testproject(&$db)
{
  $this->db = $db;	
}


/** 
 * create a new test project
 * @param string $name
 * @param string $color
 * @param string $optReq [1,0]
 * @param string $notes
 * @return boolean result
 *
 * 20060101 - fm - added notes
 */
function create($name,$color,$optReq,$notes)
{
	$status_ok=0;

  // Create Node and get the id
  $tree_manager = New tree($this->db);
  $root_node_id = $tree_manager->new_root_node();

	$sql = " INSERT INTO testprojects (id,name,color,option_reqs,notes) " .
	       " VALUES (" . $root_node_id . ", '" .	
	                 $this->db->prepare_string($name)  . "','" . 
	                 $this->db->prepare_string($color) . "',"  . 
	                 $optReq . ",'" .
			             $this->db->prepare_string($notes) . "')";
			             
	$result = @$this->db->exec_query($sql);

	if ($result)
	{
		tLog('The new testproject '.$name.' was succesfully created.', 'INFO');
		$status_ok = 1;
	}
		
	return $status_ok;
}

/*
update info on tables and on session

*/
function update($id, $name, $color, $opt_req,$notes)
{
	$status_msg='ok';
  $log_msg = 'Product ' . $name . ' update: Ok.';
	$log_level='INFO';
	
	$sql = " UPDATE testprojects SET name='" . $this->db->prepare_string($name) . "', " .
	       " color='" . $this->db->prepare_string($color) . "', ".
			   " option_reqs=" .  $opt_req . ", " .
			   " notes='" . $this->db->prepare_string($notes) . "'" . 
			   " WHERE id=" . $id;
			   
	$result = $this->db->exec_query($sql);

	if ($result)
	{
		// update session data
		$_SESSION['testprojectColor'] = $color;
		$_SESSION['testprojectName'] = $name;
		$_SESSION['testprojectOptReqs'] = $opt_req;
	}
	else
	{
		$status_msg = 'Update product FAILED!';
		$log_level='ERROR';
		$log_msg = 'FAILED SQL: ' . $sql . "\n Result: " . $this->db->error_msg();
	}
	
	tLog($log_msg,$log_level);
	return($status_msg);
}




function get_by_name($name)
{
	$sql = " SELECT * FROM testprojects 
	         WHERE name = '" . 
	         $this->db->prepare_string($name) . "'";

  $recordset = $this->db->get_recordset($sql);
  return($recordset);
}




/*
get info for one test project
*/
function get_by_id($id)
{
	$sql = " SELECT * FROM testprojects
	         WHERE id = {$id}";
  $recordset = $this->db->get_recordset($sql);
  return($recordset ? $recordset[0] : null);
}


/*
get array of info for every test project
without any kind of filter.
Every array element contains an assoc array with test project info

*/
function get_all()
{
	$sql = " SELECT * FROM testprojects ";
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
	$smarty->assign('level', 'testproject');
	$smarty->assign('container_data', $item);
	$smarty->display('containerView.tpl');
}

// 20060301 - franciscom
function get_all_keywords($id)
{
	$a_keywords = null;
 	$sql = " SELECT id,keyword,notes FROM keywords 
 	         WHERE testproject_id = {$testproject_id} 
 	         ORDER BY keyword ASC";

  $result = $this->db->exec_query($sql);
	if ($result)
	{
		while ($myrow = $this->db->fetch_array($result)) 
		{
			$a_keywords[] = array( 'id' => $myrow['id'],
									            'keyword' => $myrow['keyword'], 
									            'notes' => $myrow['notes']
								            );
		}
	}
  return ($a_keywords);
}


/* 20060305 - franciscom */
function count_testcases($id)
{
  $tree_manager = New tree($this->db);
	$test_spec = $tree_manager->get_subtree($id,array('testplan'=>'exclude me'),
	                                            array('testcase'=>'exclude my children'));
  
  $hash_descr_id = $tree_manager->get_available_node_types();
  
  $qty=0;
  if( count($test_spec) > 0 )
  {
    foreach($test_spec as $elem)
    {
    	if($elem['node_type_id'] == $hash_descr_id['testcase'])
    	{
    	  $qty++;
    	}
    }
  }
  return ($qty);
}


function gen_combo_test_suites($id)
{
	$aa = array(); 

	$tree_manager = New tree($this->db);
	//echo __FUNCTION__  ;
	$test_spec = $tree_manager->get_subtree($id, array("testplan"=>"exclude me",
                                                     "testcase"=>"exclude me"),
                                               array('testcase'=>'exclude my children PLEASE'));
  
  $hash_descr_id = $tree_manager->get_available_node_types();
  $hash_id_descr = array_flip($hash_descr_id);
  
  
  // 20060223 - franciscom
  if( count($test_spec) > 0 )
  {
   	$pivot=$test_spec[0];
   	$the_level=1;
    $level=array();
  
   	foreach ($test_spec as $elem)
   	{
   	 $current = $elem;
  
     if( $pivot['parent_id'] == $current['parent_id'])
     {
       $the_level=$the_level;
     }
     else if ($pivot['id'] == $current['parent_id'])
     {
     	  $the_level++;
     	  $level[$current['parent_id']]=$the_level;
     }
     else 
     {
     	  $the_level=$level[$current['parent_id']];
     }
     
     if( $hash_id_descr[$current['node_type_id']] == "testcase") 
     {
       $icon="gnome-starthere-mini.png";	
     }
     $aa[$current['id']] = str_repeat('.',$the_level) . $current['name'];
     // update pivot
     $level[$current['parent_id']]= $the_level;
     $pivot=$elem;
   	}
	}
	
	return($aa);
}

} // end class

?>
