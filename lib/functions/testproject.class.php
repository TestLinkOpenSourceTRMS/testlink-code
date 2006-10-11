<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testproject.class.php,v $
 * @version $Revision: 1.25 $
 * @modified $Date: 2006/10/11 07:00:39 $  $Author: franciscom $
 * @author franciscom
 *
 * 20061010 - franciscom - added get_srs_by_title()
 * 20060709 - franciscom - changed return type and interface of create()
 * 20060425 - franciscom - changes in show() following Andreas Morsing advice (schlundus)
 *
**/

class testproject
{
	var $db;
	var $tree_manager;

	function testproject(&$db)
	{
		$this->db = &$db;	
		$this->tree_manager = new tree($this->db);
	}

/** 
 * create a new test project
 * @param string $name
 * @param string $color
 * @param string $optReq [1,0]
 * @param string $notes
 * [@param boolean $active [1,0] ]
 *
 * @return everything OK -> test project id
 *         problems      -> 0 (invalid node id) 
 *
 * 20060709 - franciscom - return type changed
 *                         added new optional argument active
 *
 * 20060312 - franciscom - name is setted on nodes_hierarchy table
 * 20060101 - franciscom - added notes
 */
function create($name,$color,$optReq,$notes,$active=1)
{
	// Create Node and get the id
	$root_node_id = $this->tree_manager->new_root_node($name);
	$sql = " INSERT INTO testprojects (id,color,option_reqs,notes,active) " .
	       " VALUES (" . $root_node_id . ", '" .	
	                     $this->db->prepare_string($color) . "'," . 
	                     $optReq . ",'" .
		                   $this->db->prepare_string($notes) . "'," . 
		                   $active . ")";
			             
	$result = $this->db->exec_query($sql);
	if ($result)
	{
		tLog('The new testproject '.$name.' was succesfully created.', 'INFO');
		$status_ok = 1;
	}
	else
	{
	   $root_node_id=0;
	}
		
	return($root_node_id);
}

/**
 * update info on tables and on session
 *
 * @param type $id documentation
 * @param type $name documentation
 * @param type $color documentation
 * @param type $opt_req documentation
 * @param type $notes documentation
 * @return type documentation
 *
 *	20060312 - franciscom - name is setted on nodes_hierarchy table
 *
 **/
function update($id, $name, $color, $opt_req,$notes)
{
	$status_msg = 'ok';
	$log_msg = 'Test project ' . $name . ' update: Ok.';
	$log_level = 'INFO';
	
	$sql = " UPDATE testprojects SET color='" . $this->db->prepare_string($color) . "', ".
			" option_reqs=" .  $opt_req . ", " .
			" notes='" . $this->db->prepare_string($notes) . "'" . 
			" WHERE id=" . $id;
	
	$result = $this->db->exec_query($sql);
	if ($result)
	{
		$sql = "UPDATE nodes_hierarchy SET name='" . 
				$this->db->prepare_string($name) .
				"' WHERE id= {$id}";
		$result = $this->db->exec_query($sql);
	}
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
		$log_level ='ERROR';
		$log_msg = $status_msg;
	}
	
	tLog($log_msg,$log_level);
	return $status_msg;
}

function get_by_name($name,$addClause = null)
{
	$sql = " SELECT testprojects.*, nodes_hierarchy.name ".
	       "  FROM testprojects, nodes_hierarchy ". 
	       " WHERE testprojects.id = nodes_hierarchy.id AND".
	       "  nodes_hierarchy.name = '" . 
	         $this->db->prepare_string($name) . "'";
	if (!is_null($addClause))
		$sql .= " AND " . $addClause;
			 
	$recordset = $this->db->get_recordset($sql);
	return $recordset;
}

/*
get info for one test project
*/
function get_by_id($id)
{
	$sql = " SELECT testprojects.*,nodes_hierarchy.name ".
	       " FROM testprojects, nodes_hierarchy ".
	       " WHERE testprojects.id = nodes_hierarchy.id ".
	       " AND testprojects.id = {$id}";
	$recordset = $this->db->get_recordset($sql);
	return ($recordset ? $recordset[0] : null);
}


/*
get array of info for every test project
without any kind of filter.
Every array element contains an assoc array with test project info

*/
function get_all()
{
	$sql = " SELECT testprojects.*, nodes_hierarchy.name ".
	       " FROM testprojects, nodes_hierarchy ".
	       " WHERE testprojects.id = nodes_hierarchy.id";
	$recordset = $this->db->get_recordset($sql);
	return $recordset;
}


/**
 * Function-Documentation
 *
 * @param type $smarty [ref] documentation
 * @param type $id documentation
 * @param type $sqlResult [default = ''] documentation
 * @param type $action [default = 'update'] documentation
 * @param type $modded_item_id [default = 0] documentation
 * @return type documentation
 *
 *
 **/
function show(&$smarty,$id, $sqlResult = '', $action = 'update',$modded_item_id = 0)
{
	$smarty->assign('modify_tc_rights', has_rights($this->db,"mgt_modify_tc"));
	$smarty->assign('mgt_modify_product', has_rights($this->db,"mgt_modify_product"));

	if($sqlResult)
	{ 
		$smarty->assign('sqlResult', $sqlResult);
		$smarty->assign('sqlAction', $action);
	}
	
	$item = $this->get_by_id($id);
 	$modded_item = $item;
	if ($modded_item_id)
	{
		$modded_item = $this->get_by_id($modded_item_id);
	}
  
	$smarty->assign('moddedItem',$modded_item);
	$smarty->assign('level', 'testproject');
	$smarty->assign('container_data', $item);
	$smarty->display('containerView.tpl');
}


function count_testcases($id)
{
	$test_spec = $this->tree_manager->get_subtree($id,array('testplan'=>'exclude me'),
	                                            array('testcase'=>'exclude my children'));
  
 	$hash_descr_id = $this->tree_manager->get_available_node_types();
  
 	$qty = 0;
	if(count($test_spec))
	{
		foreach($test_spec as $elem)
		{
			if($elem['node_type_id'] == $hash_descr_id['testcase'])
				$qty++;
		}
	}
	return $qty;
}


	// 20060308 - franciscom - added exclude_branches
	// 
	function gen_combo_test_suites($id,$exclude_branches=null)
	{
		$aa = array(); 
	
		$test_spec = $this->tree_manager->get_subtree($id, array("testplan"=>"exclude me","testcase"=>"exclude me"),
	                                                     array('testcase'=>'exclude my children PLEASE'),
	                                                     $exclude_branches);
	  
		$hash_descr_id = $this->tree_manager->get_available_node_types();
		$hash_id_descr = array_flip($hash_descr_id);
	  
	  
		if(count($test_spec))
		{
			$pivot = $test_spec[0];
			$the_level = 1;
			$level = array();
		
			foreach($test_spec as $elem)
			{
				$current = $elem;
				
				if ($pivot['id'] == $current['parent_id'])
				{
					$the_level++;
					$level[$current['parent_id']]=$the_level;
				}
				else if ($pivot['parent_id'] != $current['parent_id'])
				{
					$the_level = $level[$current['parent_id']];
				}
				
				if($hash_id_descr[$current['node_type_id']] == "testcase")
				{
					$icon = "gnome-starthere-mini.png";	
				}
				$aa[$current['id']] = str_repeat('.',$the_level) . $current['name'];
				// update pivot
				$level[$current['parent_id']]= $the_level;
				$pivot=$elem;
			}
		}
		
		return $aa;
	}

	/**
	 * Checks a test project name for correctness
	 *
	 * @param string $name the name to check
	 * @param string $msg [ref] the error msg on failure
	 * @return integer return 1 on success, 0 else
	 **/
	function checkTestProjectName($name,&$msg)
	{
		global $g_ereg_forbidden;
		
		$name_ok = 1;
		if (!strlen($name))
		{
			$msg = lang_get('info_product_name_empty');
			$name_ok = 0;
		}
		// BUGID 0000086
		if ($name_ok && !check_string($name,$g_ereg_forbidden))
		{
			$msg = lang_get('string_contains_bad_chars');
			$name_ok = 0;
		}
		return $name_ok;
	}
	
	
	/** allow activate or deactivate a test project
	 * @param integer $id test project ID
	 * @param integer $status 1=active || 0=inactive 
	 */
	function activateTestProject($id, $status)
	{
		$sql = "UPDATE testprojects SET active=" . $status . " WHERE id=" . $id;
		$result = $this->db->exec_query($sql);
	
		return $result ? 1 : 0;
	}
	/* KEYWORDS RELATED */	
	/**
	 * Adds a new keyword to the given product
	 *
	 * @param int  $testprojectID
	 * @param string $keyword
	 * @param string $notes
	 *
	 * @return string 'ok' on success, a db error msg else
	 *
	 * 20051011 - fm - use of check_for_keyword_existence()
	 * 20051004 - fm - refactoring
	 **/
	function addKeyword($testprojectID,$keyword,$notes)
	{
		global $g_allow_duplicate_keywords;
		
		$ret = 'ok';
		$do_action = 1;
		$my_kw = trim($keyword);
		if (!$g_allow_duplicate_keywords)
		{
			$check = $this->check_for_keyword_existence($testprojectID, $my_kw);
			$ret = $check['msg'];
			$do_action = !$check['keyword_exists'];
		}
		
		if ($do_action)
		{
			$sql =  " INSERT INTO keywords (keyword,testproject_id,notes) " .
					" VALUES ('" . $this->db->prepare_string($my_kw) .	"'," . 
					$testprojectID . ",'" . $this->db->prepare_string($notes) . "')";
			
			$result = $this->db->exec_query($sql);
			if (!$result)
				$ret = $this->db->error_msg();
		}
	  
		return $ret;
	}
	
	
	/**
	 * Function-Documentation
	 *
	 * @param type $testprojectID documentation
	 * @param type $id documentation
	 * @param type $keyword documentation
	 * @param type $notes documentation
	 * 
	 * @return type documentation
	 **/
	function updateKeyword($testprojectID,$id,$keyword,$notes)
	{
		global $g_allow_duplicate_keywords;
		
		$ret = array("msg" => "ok", 
					 "status_ok" => 0);
		$do_action = 1;
		$my_kw = trim($keyword);
	
		if (!$g_allow_duplicate_keywords)
		{
			$check = $this->check_for_keyword_existence($testprojectID, $my_kw,$id);
			$do_action = !$check['keyword_exists'];
	
			$ret['msg'] = $check['msg'];
			$ret['status_ok'] = $do_action;
		}
		if ($do_action)
		{
			$sql = "UPDATE keywords SET notes='" . $this->db->prepare_string($notes) . "', keyword='" 
					. $this->db->prepare_string($my_kw) . "' where id=" . $id;

			$result = $this->db->exec_query($sql);
			if (!$result)
			{
				$ret['msg'] = $this->db->error_msg();
				$ret['status_ok'] = 0;
			}
		}
	
		return $ret;
	}

	/**
	 * check_for_keyword_existence
	 *
	 * @param int    $testprojectID product ID
	 * @param string $kw keyword to search for
	 * @param int    $kwID[default = 0] ignore keyword with this id
	 *
	 * @return type
	 *				 				
	 **/
	function check_for_keyword_existence($testprojectID, $kw, $kwID = 0)
	{
		$ret = array(
					 'msg' => 'ok', 
					 'keyword_exists' => 0
					 );
		  
		$sql = 	" SELECT * FROM keywords " .
				" WHERE UPPER(keyword) ='" . strtoupper($this->db->prepare_string($kw)).
			    "' AND testproject_id=" . $testprojectID ;
		
		if ($kwID)
			$sql .= " AND id <> " . $kwID;
		
		if ($this->db->fetchFirstRow($sql))
		{
			$ret['keyword_exists'] = 1;
			$ret['msg'] = lang_get('keyword_already_exists');
		}
		
		return $ret;
	}
	/**
	 * Gets the keywords of the given test project
	 *
	 * @param int $tprojectID the test project id
	 * @param int $keywordID [default = null] the optional keyword id
	 * @return array returns the keyword information
	 **/
	function getKeywords($testproject_id,$keywordID = null)
	{
		$a_keywords = null;
		$sql = " SELECT id,keyword,notes FROM keywords " .
			   " WHERE testproject_id = {$testproject_id}" ;
		if ($keywordID)
			$sql .= " AND id = {$keywordID} ";			   
		$sql .= " ORDER BY keyword ASC";
		
		$a_keywords = $this->db->get_recordset($sql);
		return $a_keywords;
	}
	
	/**
	 * Imports the keywords contained in keywordData to the given product
	 *
	 * @param type $db [ref] documentation
	 * @param int $testprojectID the product to which the keywords should be imported
	 * @param array $keywordData an array with keyword information like
	 * 				 keywordData[$i]['keyword'] => the keyword itself
	 * 				 keywordData[$i]['notes'] => the notes of keyword
	 *
	 * @return array returns an array of result msgs
	 *
	 * @author Andreas Morsing <schlundus@web.de>
	 **/
	function addKeywords($testprojectID,$keywordData)
	{
		$sqlResults = null;
		for($i = 0;$i < sizeof($keywordData);$i++)
		{
			$keyword = $keywordData[$i]['keyword'];
			$notes = $keywordData[$i]['notes'];
			$msg = checkKeywordName($keyword);
			if (!is_null($msg))
				$sqlResults[] = $msg;
			else
				$sqlResults[] = $this->addKeyword($testprojectID,$keyword,$notes);
		}
	
		return $sqlResults;
	}

	/* END KEYWORDS RELATED */	
	
	/* REQUIREMENTS RELATED */
	/** 
	 * collect information about current list of Requirements Specification
	 *  
	 * @param numeric $testproject_id
	 * @param string $set optional id of the requirement specification
	 
	 * @return assoc_array list of SRS
	 * 
	 * @author Martin Havlat 
	 **/
	function getReqSpec($testproject_id, $id = null)
	{
		$sql = "SELECT * FROM req_specs WHERE testproject_id=" . $testproject_id;
		
		if (!is_null($id))
			$sql .= " AND id=" . $id;
		
		$sql .= "  ORDER BY title";
	
		return $this->db->get_recordset($sql);
	}
	
	/** 
	 * create a new System Requirements Specification 
	 * 
	 * @param string $title
	 * @param string $scope
	 * @param string $countReq
	 * @param numeric $testproject_id
	 * @param numeric $user_id
	 * @param string $type
	 * 
	 * @author Martin Havlat 
	 */
	function createReqSpec($testproject_id,$title, $scope, $countReq,$user_id,$type = 'n')
	{
	  $ignore_case=1;
		$result = 'ok';
    $title=trim($title);
  	
    $chk=$this->check_srs_title($testproject_id,$title,$ignore_case);
		if ($chk['status_ok'])
		{
			$sql = "INSERT INTO req_specs (testproject_id, title, scope, type, total_req, author_id, creation_ts)
					    VALUES (" . $testproject_id . ",'" . $this->db->prepare_string($title) . "','" . 
					                $this->db->prepare_string($scope) .  "','" . $this->db->prepare_string($type) . "','" . 
					                $this->db->prepare_string($countReq) . "'," . $this->db->prepare_string($user_id) . ", " . 
					                $this->db->db_now() . ")";
					
			if (!$this->db->exec_query($sql))
			{
				$result = lang_get('error_creating_req_spec');
			}	
		}
		else
		{
		  $result=$chk['msg'];
		}
		return $result; 
	}


  // 20061010 - franciscom
  function get_srs_by_title($testproject_id,$title,$ignore_case=0)
  {
  	$output=null;
  	$title=trim($title);
  	
  	$sql = "SELECT * FROM req_specs ";
  	
  	if($ignore_case)
  	{
  	  $sql .= " WHERE UPPER(title)='" . strtoupper($this->db->prepare_string($title)) . "'";
  	}
  	else
  	{
  	   $sql .= " WHERE title='" . $this->db->prepare_string($title) . "'";
  	}       
  	$sql .= " AND testproject_id={$testproject_id}";
		$output = $this->db->fetchRowsIntoMap($sql,'id');

  	return $output;
  }

  // 20061010 - franciscom
  function check_srs_title($testproject_id,$title,$ignore_case=0)
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
      $rs=$this->get_srs_by_title($testproject_id,$title,$ignore_case);

      if( !is_null($rs) )
      {
  		  $ret['msg']=lang_get("warning_duplicate_req_title");
        $ret['status_ok']=0;  		  
  	  }
  	} 
  	return($ret);
  }





	/* END REQUIREMENT RELATED */


/* 20060402 - franciscom */
function delete()
{
	
	
	
}


// 20060409 - franciscom
function get_keywords_map($testproject_id)
{
		$map_keywords = null;
		$sql = " SELECT id,keyword FROM keywords " .
			     " WHERE testproject_id = {$testproject_id}" .
			     " ORDER BY keyword ASC";
		$map_keywords = $this->db->fetchColumnsIntoMap($sql,'id','keyword');
		return($map_keywords);
}
	
function get_all_testcases_id($id)
{
	$a_tcid = array();
	$test_spec = $this->tree_manager->get_subtree($id,
					array('testplan'=>'exclude me'),
	         		array('testcase'=>'exclude my children'));
	
	$hash_descr_id = $this->tree_manager->get_available_node_types();
	if(count($test_spec))
	{
		$tcNodeType = $hash_descr_id['testcase']; 
		foreach($test_spec as $elem)
		{
			if($elem['node_type_id'] == $tcNodeType)
			{
				$a_tcid[] = $elem['id'];
			}
		}
	}
	return $a_tcid;
}

// 20060430 - franciscom
function get_keywords_tcases($testproject_id, $keyword_id=0)
{
    $keyword_filter= '' ;
    if( $keyword_id > 0 )
    {
       $keyword_filter = " AND keyword_id = {$keyword_id} ";
    }
		$map_keywords = null;
		$sql = " SELECT testcase_id,keyword_id,keyword 
		         FROM keywords K, testcase_keywords  
		         WHERE keyword_id = K.id  
		         AND testproject_id = {$testproject_id}
		         {$keyword_filter}
			       ORDER BY keyword ASC ";
		$map_keywords = $this->db->fetchRowsIntoMap($sql,'testcase_id');
		return($map_keywords);
} //end function


// 
// 20060603 - franciscom
function get_all_testplans($testproject_id,$get_tp_without_tproject_id=0,$plan_status=null)
{
	$sql = " SELECT nodes_hierarchy.id, nodes_hierarchy.name, 
	                notes,active, testproject_id 
	         FROM nodes_hierarchy,testplans";
	$where = " WHERE nodes_hierarchy.id=testplans.id ";
  $where .= ' AND (testproject_id = ' . $testproject_id . " ";  	

	if($get_tp_without_tproject_id)
	{
			$where .= " OR testproject_id = 0 ";
	}
	$where .= " ) ";

	if(!is_null($plan_status))
	{	
		$my_active = to_boolean($plan_status);
		$where .= " AND active = " . $my_active;
	}
	$sql .= $where . " ORDER BY name";

	$map = $this->db->fetchRowsIntoMap($sql,'id');
	return($map);
	
}



} // end class

?>