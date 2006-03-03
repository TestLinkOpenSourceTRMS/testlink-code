<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testcase.class.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2006/03/03 16:21:02 $
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



function get_by_name($name)
{
	$sql = " SELECT * FROM testcases 
	         WHERE name = '" . 
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

// 20060302 - francisco.mancardi@gruppotesi.com
//
	$sql = " SELECT testcases.id AS testcase_id, name, tcversions.*, 
	                users.first AS author_first_name, users.last AS author_last_name,
	                '' AS updater_first_name, '' AS updater_last_name
	         FROM testcases JOIN nodes_hierarchy ON nodes_hierarchy.parent_id = testcases.id 
                          JOIN tcversions ON nodes_hierarchy.id = tcversions.id 
                          LEFT OUTER JOIN users ON tcversions.author_id = users.id
                          WHERE testcases.id = {$id}";
	
	  //echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";
           
  $recordset = $this->db->get_recordset($sql);
  
  if($recordset)
  {
	 $sql = " SELECT updater_id, users.first AS updater_first_name, users.last  AS updater_last_name
	          FROM testcases JOIN nodes_hierarchy ON nodes_hierarchy.parent_id = testcases.id 
                           JOIN tcversions ON nodes_hierarchy.id = tcversions.id 
                           LEFT OUTER JOIN users ON tcversions.updater_id = users.id
                           WHERE testcases.id = {$id} and tcversions.updater_id IS NOT NULL ";
                           
    //echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";
                       
                           
    $updaters = $this->db->get_recordset($sql);
    
    if($updaters)
    {
    	foreach ($recordset as  $the_key => $row )
    	{
        //echo "<pre>debug \$row "; print_r($row); echo "</pre>";
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
	$sql = " SELECT * FROM testcases ";
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
	
	//echo "<pre>debug - \$can_edit" . $can_edit; echo "</pre>";
	
	$tc_array = $this->get_by_id($id);
	//echo "<pre>debug ( function=" . __FUNCTION__ ." ) "; print_r($tc_array); echo "</pre>";
	
	
	// get assigned REQs
	$arrReqs = getReq4Tc($this->db,$id);
	
	//$tc_array = array($myrowTC);
	
	$smarty = new TLSmarty;
	
	$smarty->assign('can_edit',$can_edit);
	$smarty->assign('testcase',$tc_array);
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




// test case
$sql = " UPDATE testcases SET name='" . $this->db->prepare_string($name) . "'" .
       " WHERE testcases.id = {$id}";

  //echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";


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

  //echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";
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






} // end class

?>
