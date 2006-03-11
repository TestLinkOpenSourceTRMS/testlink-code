<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testproject.class.php,v $
 * @version $Revision: 1.8 $
 * @modified $Date: 2006/03/11 23:09:19 $
 * @author franciscom
 *
 */

require_once( dirname(__FILE__). '/tree.class.php' );
class testproject
{

var $db;

function testproject(&$db)
{
  $this->db = &$db;	
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


// 20060308 - franciscom - added exclude_branches
// 
function gen_combo_test_suites($id,$exclude_branches=null)
{
	$aa = array(); 

	$tree_manager = New tree($this->db);

	// 20060308 - franciscom
	$test_spec = $tree_manager->get_subtree($id, array("testplan"=>"exclude me","testcase"=>"exclude me"),
                                               array('testcase'=>'exclude my children PLEASE'),
                                               $exclude_branches);
  
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
			   " WHERE testproject_id = {$testproject_id}" .
			   " ORDER BY keyword ASC";
		
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

} // end class

?>
