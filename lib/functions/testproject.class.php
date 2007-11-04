<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testproject.class.php,v $
 * @version $Revision: 1.40 $
 * @modified $Date: 2007/11/04 11:11:34 $  $Author: franciscom $
 * @author franciscom
 *
 * 20071104 - franciscom - get_accessible_for_user
 *                         added optional arg to get_all()
 *
 * 20071002 - azl - added ORDER BY to get_all method
 * 20070620 - franciscom - BUGID 914  fixed delete() (no delete from nodes_hierarchy)
 * 20070603 - franciscom - added delete()
 * 20070219 - franciscom - fixed bug on get_first_level_test_suites()
 * 20070128 - franciscom - added check_tplan_name_existence()
 * 20061010 - franciscom - added get_srs_by_title()
 * 20060709 - franciscom - changed return type and interface of create()
 * 20060425 - franciscom - changes in show() following Andreas Morsing advice (schlundus)
 *
**/

class testproject
{
	var $db;
	var $tree_manager;
  var $cfield_mgr;

	function testproject(&$db)
	{
		$this->db = &$db;	
		$this->tree_manager = new tree($this->db);
		$this->cfield_mgr=new cfield_mgr($this->db);
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
  $status_ok=1;
	
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
		$status_msg = 'Update FAILED!';
	  $status_ok=0;
		$log_level ='ERROR';
		$log_msg = $status_msg;
	}
	
	tLog($log_msg,$log_level);
	return ($status_ok);
}


/*
  function: get_by_name

  args :
  
  returns: 

*/
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
  function: get_by_id
            

  args : id: test project
  
  returns: null if query fails
           map with test project info

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
 function: get_all
           get array of info for every test project
           without any kind of filter.
           Every array element contains an assoc array with test project info

args:[order_by]: default " ORDER BY nodes_hierarchy.name " -> testproject name

rev:
    20071104 - franciscom - added order_by

*/
function get_all($order_by=" ORDER BY nodes_hierarchy.name ")
{
	$sql = " SELECT testprojects.*, nodes_hierarchy.name ".
	       " FROM testprojects, nodes_hierarchy ".
	       " WHERE testprojects.id = nodes_hierarchy.id ";
	if( !is_null($order_by) )
	{
	  $sql .= $order_by;  
	}
	$recordset = $this->db->get_recordset($sql);
	return $recordset;
}

/** 
function: get_accessible_for_user
          get list of testprojects, considering user roles.
          Remember that user has:
          1. one default role, assigned when user was created
          2. a different role can be assigned for every testproject.
          
          For users roles that has not rigth to modify testprojects
          only active testprojects are returned.

args:
      user_id
      role_id
      [output_type]: choose the output data structure.
                     possible values: map, map_of_map
                     map: key -> test project id
                          value -> test project name
                            
                     map_of_map: key -> test project id
                                 value -> array ('name' => test project name,
                                                 'active' => active status)
                                                 
                     array_of_map: value -> array ('id' => test project id
                                                   'name' => test project name,
                                                   'active' => active status)
                                                 
                     
                     default: map
     [order_by]: default: ORDER BY name
                     
rev :
     20071104 - franciscom - added user_id,role_id to remove global coupling
                             added order_by (BUGID 498)
     20070725 - franciscom - added output_type
     20060312 - franciscom - add nodes_hierarchy on join
     
*/
function get_accessible_for_user($user_id,$output_type='map',$order_by=" ORDER BY name ")
{
	$items = array();
	
  // Get default role
  $sql = " SELECT id,role_id FROM users where id={$user_id}";
  $user_info = $this->db->get_recordset($sql);
	$role_id=$user_info[0]['role_id'];
	
	
	$sql =  " SELECT nodes_hierarchy.id,nodes_hierarchy.name,active
 	          FROM nodes_hierarchy 
 	          JOIN testprojects ON nodes_hierarchy.id=testprojects.id  
	          LEFT OUTER JOIN user_testproject_roles 
		        ON testprojects.id = user_testproject_roles.testproject_id AND  
		 	      user_testproject_roles.user_ID = {$user_id} WHERE ";
		 	      
	if ($role_id != TL_ROLES_NONE)
		$sql .=  "(role_id IS NULL OR role_id != ".TL_ROLES_NONE.")";
	else
		$sql .=  "(role_id IS NOT NULL AND role_id != ".TL_ROLES_NONE.")";
	
	
	if (has_rights($this->db,'mgt_modify_product') != 'yes')
		$sql .= " AND active=1 ";

	$sql .= $order_by;
	
	$arrTemp = $this->db->fetchRowsIntoMap($sql,'id');
	
	if (sizeof($arrTemp))
	{
    switch ($output_type)
	  {
	     case 'map':
		   foreach($arrTemp as $id => $row)
		   {
			   $noteActive = '';
			   if (!$row['active'])
				   $noteActive = TL_INACTIVE_MARKUP;
			   $items[$id] = $noteActive . $row['name'];
		   }
		   break;
		   
	     case 'map_of_map':
		   foreach($arrTemp as $id => $row)
		   {
			   $items[$id] = array( 'name' => $row['name'],
			                        'active' => $row['active']);
		   }
		   
		   case 'array_of_map':
		   foreach($arrTemp as $id => $row)
		   {
			   $items[] = array( 'id' => $id,
			                     'name' => $row['name'],
			                     'active' => $row['active']);
		   }
		   break;
	  }
	}

	return $items;
}





/**
 * Function: show
 *           displays smarty template to show test project info 
 *           to users.
 *
 * @param type $smarty [ref] smarty object
 * @param type $id test project
 * @param type $sqlResult [default = ''] 
 * @param type $action [default = 'update'] 
 * @param type $modded_item_id [default = 0] 
 * @return -
 *
 *
 **/
function show(&$smarty,$id,$sqlResult='', $action = 'update',$modded_item_id = 0)
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
	$smarty->assign('page_title', lang_get('testproject'));
	$smarty->assign('container_data', $item);
	$smarty->display('containerView.tpl');
}


/*
  function: count_testcases
            Count testcases without considering active/inactive status. 

  args : id: testproject id
  
  returns: int: test cases presents on test project. 

*/
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


  /*
    function: gen_combo_test_suites
              create array with test suite names
              test suites are ordered in parent-child way, means
              order on array is creating traversing tree branches, reaching end
              of branch, and starting again. (recursive algorithim).


    args :  $id: test project id
            [$exclude_branches]: array with test case id to exclude
                                 useful to exclude myself ($id)
            [$mode]: dotted -> $level number of dot characters are appended to 
                               the left of test suite name to create an indent effect.
                               Level indicates on what tree layer testsuite is positioned.
                               Example:
                               
                                null 
                                \
                               id=1   <--- Tree Root = Level 0
                                 |
                                 + ------+
                               /   \      \
                            id=9   id=2   id=8  <----- Level 1
                                    \
                                     id=3       <----- Level 2
                                      \
                                       id=4     <----- Level 3

                               
                               key: testsuite id (= node id on tree).
                               value: every array element is an string, containing testsuite name.

                               Result example:
                               
                                2  .TS1
                                3 	..TS2
                                9 	.20071014-16:22:07 TS1
                               10 	..TS2                               
                               
                               
                     array  -> key: testsuite id (= node id on tree).
                               value: every array element is a map with the following keys
                               'name', 'level'
    
                                2  	array(name => 'TS1',level =>	1)
                                3   array(name => 'TS2',level =>	2)
                                9	  array(name => '20071014-16:22:07 TS1',level =>1)
                               10   array(name =>	'TS2', level 	=> 2)
    
    
    returns: map , structure depens on $mode argument.

  */
	function gen_combo_test_suites($id,$exclude_branches=null,$mode='dotted')
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
				
				switch($mode)
				{
  				case 'dotted':
				  $aa[$current['id']] = str_repeat('.',$the_level) . $current['name'];
          break;
          				  
  				case 'array':
				  $aa[$current['id']] = array('name' => $current['name'], 'level' =>$the_level);
				  break;				  
        }
        
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
	
	
	/* Keywords related methods  */	
	/**
	 * Adds a new keyword to the given test project
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
	 * function: updateKeyword
	 *
	 *
	 * @param type $testprojectID 
	 * @param type $id 
	 * @param type $keyword 
	 * @param type $notes 
	 * 
	 * @return map: keys: msg : verbose indication of what has happened
	 *                          'ok' is everything fine.
	 *
	 *                   status_ok -> 1 -> everyting ok
	 *
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
	 * @return map: keys: msg : verbose indication of what has happened
	 *                          'ok' is everything fine.
	 *
	 *                   keyword_exists -> 1 / 0
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
	 * @return array, every elemen is map with following structure:
	 *
	 *                id
	 *                keyword
	 *                notes
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
	 * @param type $db [ref] db object
	 * @param int $testprojectID  to which the keywords should be imported
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
	 * @param string  $id optional id of the requirement specification
	 *
	 * @return null if no srs exits, or no srs exists for id
	 *         array, where each element is a map with SRS data.
	 *         
	 *         map keys:
   *         id
   *         testproject_id
   *         title
   *         scope 	
   *         total_req
   *         type
   *         author_id
   *         creation_ts
   *         modifier_id
   *         modification_ts
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



  /*
    function: get_srs_by_title
              get srs information using title as access key.
  
    args : tesproject_id
           title: srs title
           [ignore_case]: control case sensitive search.
                          default 0 -> case sensivite search
    
    returns: map.
             key: srs id
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



  /*
    function: check_srs_title
              Do checks on srs title, to undertand if can be used.
              
              Checks:
              1. title is empty ?
              2. does already exist a srs with this title?
  
    args : tesproject_id
           title: srs title
           [ignore_case]: control case sensitive search.
                          default 0 -> case sensivite search
    
    returns: 
  
  */
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
// ----------------------------------------------------------------------------------------


/*
  function: delete
            delete test project from system, deleting all dependent data:
            keywords, requirements, custom fields, testsuites, testplans, 
            testcases, results, testproject related roles,             

            
  args :id: testproject id
        error [ref]: used to return verbose feedback about operation.
  
  returns: -

*/
function delete($id,&$error)
{

	$error = ''; //clear error string
	
	$a_sql = array();
	$tp = new testproject($db);
	$kwMap = $this->get_keywords_map($id);
	if ($kwMap)
	{
		$kwIDs = implode(",",array_keys($kwMap));
		
		$a_sql[] = array("DELETE FROM testcase_keywords  WHERE keyword_id IN ({$kwIDs})",
							       'info_tc_keywords_delete_fails');
		$a_sql[] = array("DELETE FROM object_keywords  WHERE keyword_id IN ({$kwIDs})",
							       'info_object_keywords_delete_fails');					 
		$a_sql[] = array("DELETE FROM keywords WHERE testproject_id=" .$id,
							       'info_keywords_delete_fails');
	}	

  // -------------------------------------------------------------------------------
	$sql = "SELECT id FROM req_specs WHERE testproject_id=" . $id;
	$srsIDs = $this->db->fetchColumnsIntoArray($sql,"id");
	if ($srsIDs)
	{
		$srsIDs = implode(",",$srsIDs);
		$sql = "SELECT id FROM requirements WHERE srs_id IN ({$srsIDs})";
		$reqIDs = $this->db->fetchColumnsIntoArray($sql,"id");
		if ($reqIDs)
		{
			$reqIDs = implode(",",$reqIDs);
			$a_sql[] = array (
							 "DELETE FROM req_coverage WHERE req_id IN ({$reqIDs})",
							 'info_req_coverage_delete_fails',
							 );
			$a_sql[] = array (
							 "DELETE FROM requirements WHERE id IN ({$reqIDs})",
							 'info_requirements_delete_fails',
							 );
		}
		$a_sql[] = array (
						 "DELETE FROM req_specs WHERE id IN ({$srsIDs})",
						 'info_req_specs_delete_fails',
						 );
	}
	// -------------------------------------------------------------------------------
	
	$a_sql[] = array(
			"UPDATE users SET default_testproject_id = NULL WHERE default_testproject_id = {$id}",
			 'info_resetting_default_project_fails',
	);
	
	$a_sql[] = array(
			"DELETE FROM user_testproject_roles WHERE testproject_id = {$id}",
			 'info_deleting_project_roles_fails',
	);
	$tpIDs = $this->get_all_testplans($id);
	if ($tpIDs)
	{
		$tpIDs = implode(",",array_keys($tpIDs));
		$a_sql[] = array(
			"DELETE FROM user_testplan_roles WHERE testplan_id IN  ({$tpIDs})",
			 'info_deleting_testplan_roles_fails',
		);
		$a_sql[] = array(
			"DELETE FROM testplan_tcversions WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_testplan_tcversions_fails',
		);

		$a_sql[] = array(
			"DELETE FROM risk_assignments WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_testplan_risk_assignments_fails',
		);
		
		$a_sql[] = array(
			"DELETE FROM priorities WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_testplan_risk_assignments_fails',
		);
		
		$a_sql[] = array(
			"DELETE FROM milestones WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_testplan_milestones_fails',
		);
		
		$sql = "SELECT id FROM executions WHERE testplan_id IN ({$tpIDs})";
		$execIDs = $this->db->fetchColumnsIntoArray($sql,"id");
		if ($execIDs)
		{
			$execIDs = implode(",",$execIDs);
		
			$a_sql[] = array(
			"DELETE FROM execution_bugs WHERE execution_id IN ({$execIDs})",
			 'info_deleting_execution_bugs_fails',
				);
		}
			 
		$a_sql[] = array(
			"DELETE FROM builds WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_builds_fails',
		);

		$a_sql[] = array(
			"DELETE FROM executions WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_execution_fails',
		); 
	}
		
	$test_spec = $this->tree_manager->get_subtree($id);
	if(count($test_spec))
	{
		$ids = array("nodes_hierarchy" => array());
		foreach($test_spec as $elem)
		{
			$eID = $elem['id'];
			$table = $elem['node_table'];
			$ids[$table][] = $eID;
			$ids["nodes_hierarchy"][] = $eID;
		}
		
		foreach($ids as $tableName => $fkIDs)
		{
			$fkIDs = implode(",",$fkIDs);
			
			if ($tableName != "testcases")
			{
				$a_sql[] = array(
					"DELETE FROM {$tableName} WHERE id IN ({$fkIDs})",
					 "info_deleting_{$tableName}_fails",
					);
			}
		}
	}			
	//MISSING DEPENDENT DATA:
	/*
	* ATTACHMENTS
	* CUSTOM FIELDS
	*/
		
	// delete all nested data over array $a_sql
	foreach ($a_sql as $oneSQL)
	{
		if (empty($error))
		{
			$sql = $oneSQL[0];
			$result = $this->db->exec_query($sql);	
			if (!$result)
				$error .= lang_get($oneSQL[1]);
		}
	}	
	
	// ---------------------------------------------------------------------------------------
	// delete product itself and items directly related to it like:
	// custom fields assignments
	// custom fields values ( right now we are not using custom fields on test projects)
	// attachments
	if (empty($error))
	{
    // 20070603 - franciscom
    $sql="DELETE FROM cfield_testprojects WHERE testproject_id = {$id} ";
    $this->db->exec_query($sql);     

		$sql = "DELETE FROM testprojects WHERE id = {$id}";
	
		$result = $this->db->exec_query($sql);
		if ($result)
		{
			$tproject_id_on_session = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : $id;
			if ($id == $tproject_id_on_session)
				setSessionTestProject(null);
		}
		else
			$error .= lang_get('info_product_delete_fails');
	}

  // 20070620 - franciscom - 
  // missing
  if (empty($error))
	{
    $sql="DELETE FROM nodes_hierarchy WHERE id = {$id} ";
    $this->db->exec_query($sql);     
  }
	return empty($error) ? 1 : 0;
}


/*
  function: get_keywords_map
            All testproject keywords.

  args :testproject_id
  
  
  returns: map: key: keyword_id
                value: keyword
  

*/
function get_keywords_map($testproject_id)
{
		$map_keywords = null;
		$sql = " SELECT id,keyword FROM keywords " .
			     " WHERE testproject_id = {$testproject_id}" .
			     " ORDER BY keyword ASC";
		$map_keywords = $this->db->fetchColumnsIntoMap($sql,'id','keyword');
		return($map_keywords);
}
	
	
	
/*
  function: get_all_testcases_id
            All testproject testcases node id.

  args :id: testproject id
  
  
  returns: array with testcases node id.
           null is nothing found
  

*/
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



/*
  function: get_keywords_tcases
            testproject keywords (with related testcase node id),
            that are used on testcases.

  args :testproject_id
        [keyword_id]= 0 -> no filter
                      <> 0 -> look only for this keyword
        
  
  
  returns: map: key: testcase_id
                value: map with testcase_id,keyword_id,keyword
  

*/
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


/*
  function: get_all_testplans

  args : $testproject_id
  
         [$get_tp_without_tproject_id]
         used just for backward compatibility (TL 1.5)
         default: 0 -> 1.6 and up behaviour
  
         [$plan_status]
         default: null -> no filter on test plan status
                  1 -> active test plans
                  0 -> inactive test plans
         
  returns: 

*/
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


/*
  function: check_tplan_name_existence

  args :
        tproject_id: 
        tplan_id: 
        [case_sensitive]: 1-> do case sensitive search
                          default: 0
  
  returns: 1 -> tplan name exists
  

*/
function check_tplan_name_existence($tproject_id,$tplan_name,$case_sensitive=0)
{
	$sql = " SELECT NH.id, NH.name, testproject_id " .
	       " FROM nodes_hierarchy NH, testplans " .
         " WHERE NH.id=testplans.id " .
         " AND testproject_id = {$tproject_id} ";  	

	if($case_sensitive)
	{
	    $sql .= " AND NH.name=";
	}       
	else
	{
      $tplan_name=strtoupper($tplan_name);	    
	    $sql .= " AND UPPER(NH.name)=";
	}          
	$sql .= "'" . $this->db->prepare_string($tplan_name) . "'";	
  $result = $this->db->exec_query($sql);
  $status= $this->db->num_rows($result) ? 1 : 0;
  
	return($status);
}


 /*
    function: gen_combo_first_level_test_suites
              create array with test suite names

    args :  id: testproject_id
            [mode]

    returns: 
            array, every element is a map
            
    rev :
          20070219 - franciscom
          fixed bug when there are no children        

*/
function get_first_level_test_suites($tproject_id,$mode='simple')
{
  $fl=$this->tree_manager->get_children($tproject_id, array('testplan' => 'exclude_me'));

  switch ($mode)
  {
    case 'simple':
    break;
    
    case 'smarty_html_options':
    if( !is_null($fl) && count($fl) > 0)
    {
      foreach($fl as $idx => $map)
      {
        $dummy[$map['id']]=$map['name'];
      }
      $fl=null;
      $fl=$dummy;
    }
    break;
  }
	return($fl);
}

// -------------------------------------------------------------------------------
// Custom field related methods
// -------------------------------------------------------------------------------
// The 
/*
  function: get_linked_custom_fields
            Get custom fields that has been linked to testproject.
            Search can be narrowed by:
            node type 
            node id
            
            Important:
            custom fields id will be sorted based on the sequence number 
            that can be specified at User Interface (UI) level, while
            linking is done.
            
  args : id: testproject id
         [node_type]: default: null -> no filter
                      verbose string that identifies a node type.
                      (see tree class, method get_available_node_types).
                      Example:
                      You want linked custom fields , but can be used
                      only on testcase -> 'testcase'.
                      
  returns: map.
           key: custom field id
           value: map (custom field definition) with following keys
            
           id 	(custom field id)
           name 	
           label 	
           type 	
           possible_values
           default_value
           valid_regexp
           length_min
           length_max
           show_on_design
           enable_on_design
           show_on_execution
           enable_on_execution
           display_order


*/
function get_linked_custom_fields($id,$node_type=null) 
{
  $additional_table="";
  $additional_join="";

  if( !is_null($node_type) )
  {
 		$hash_descr_id = $this->tree_manager->get_available_node_types();
    $node_type_id=$hash_descr_id[$node_type]; 
  
    $additional_table=",cfield_node_types CFNT ";
    $additional_join=" AND CFNT.field_id=CF.id AND CFNT.node_type_id={$node_type_id} ";
  }
  $sql="SELECT CF.*,CFTP.display_order " .
       " FROM custom_fields CF, cfield_testprojects CFTP " .
       $additional_table .  
       " WHERE CF.id=CFTP.field_id " .
       " AND   CFTP.testproject_id={$id} " .
       $additional_join .  
       " ORDER BY display_order";

  $map = $this->db->fetchRowsIntoMap($sql,'id');     
  return($map);                                 
}

} // end class

?>