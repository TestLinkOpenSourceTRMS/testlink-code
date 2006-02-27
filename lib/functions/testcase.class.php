<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testcase.class.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/02/27 07:45:14 $
 * @author franciscom
 *
 */

require_once( dirname(__FILE__). '/tree.class.php' );
class testcase
{

var $db;

function testcase(&$db)
{
  $this->db = $db;	
}

// 20060226 - franciscom
function create($parent_id,$name,$summary,$steps,
                $expected_results,$author_id,$tc_order = null)
{
  // Create Node Manager 
  $tree_manager = New tree($this->db);
	$node_types_descr_id=$tree_manager->get_available_node_types();
  $node_types_id_descr=array_flip($node_types_descr_id);
   
  // get a new ids
  $tcase_id = $tree_manager->new_node($parent_id,$node_types_descr_id['testcase']);
	$tcase_version_id = $tree_manager->new_node($tcase_id,$node_types_descr_id['testcase_version']);
	
	$sql = "INSERT INTO testcases (id,name)
	        VALUES({$tcase_id},'" . $this->db->prepare_string($name). "')";
	
    //echo "<br>debug 00 - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";
	
	$result = $this->db->exec_query($sql);        

  $status_ok=1;
  $ret['msg'] = 'ok';
  if ($result)
	{
	
		$sql = "INSERT INTO tcversions (id,version,summary,steps,expected_results,author_id,creation_ts)
	  	      VALUES({$tcase_version_id},1,'" .  $this->db->prepare_string($summary) . "'," . 
	  	                           "'" . $this->db->prepare_string($steps) . "'," .
	  	                           "'" . $this->db->prepare_string($expected_results) . "'," . $author_id . "," .
                    	  	       $this->db->db_now() . ")";
			
    //echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";

	  $result = $this->db->exec_query($sql);        

   if (!$result)
	 {
     $status_ok=0;
   }
	}
	
	if( !$status_ok )
	{
		$ret['msg'] = $this->db->error_msg();
	}
  return ($ret);
}


function update()
{
}


function get_by_name($name)
{
	$sql = " SELECT * FROM testcases 
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
	$sql = " SELECT * FROM testcases 
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
	$sql = " SELECT * FROM testcases ";
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
