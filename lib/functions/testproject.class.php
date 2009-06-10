<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @package 	TestLink
 * @author 		franciscom
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: testproject.class.php,v 1.118 2009/06/10 21:50:03 havlat Exp $
 * @filesource	http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/user.class.php?view=markup
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 *
 * 20090606 - franciscom - get_by_prefix() interface changes
 * 20090512 - franciscom - added setPublicStatus()
 * 20090412 - franciscom - BUGID 2363 - getTCasesLinkedToAnyTPlan()
 *                                      getFreeTestCases()
 *         
 * 20090205 - franciscom - getReqSpec() - interface additions
 * 20090125 - franciscom - added utility method _createHierarchyMap()
 * 20090106 - franciscom - get_by_prefix()
 * 20081103 - franciscom - get_all_testcases_id() minor refactoring
 * 20080518 - franciscom - create() interface changes
 * 20080507 - franciscom - get_keywords_tcases() - changed return type
 *                                                 add AND type filter 
 * 20080501 - franciscom - typo erro bug in get_keywords_tcases()
 * 20080322 - franciscom - get_keywords_tcases() - keyword_id can be array
 * 20080322 - franciscom - interface changes get_all_testplans()
 * 20080112 - franciscom - changed methods to manage prefix field
 *                         new methods getTestCasePrefix()
 *
 * 20080107 - franciscom - get_accessible_for_user(), added more data
 *                         for array_of_map output type
 * 20080106 - franciscom - checkName() method
 *                         delete() changed return type
 * 20080104 - franciscom - fixed bug on gen_combo_test_suites()
 *                         due to wrong exclusion in get_subtree().
 * 20071111 - franciscom - new method get_subtree();
 * 20071106 - franciscom - createReqSpec() - changed return type
 * 20071104 - franciscom - get_accessible_for_user
 *                         added optional arg to get_all()
 *
 * 20071002 - azl - added ORDER BY to get_all method
 * 20070620 - franciscom - BUGID 914  fixed delete() (no delete from nodes_hierarchy)
 * 20070603 - franciscom - added delete()
 * 20070219 - franciscom - fixed bug on get_first_level_test_suites()
 * 20070128 - franciscom - added check_tplan_name_existence()
 *
 **/

/** related functions */ 
require_once('attachments.inc.php');
require_once('keyword.class.php');

/**
 * class is responsible to get project related data and CRUD test project
 */
class testproject extends tlObjectWithAttachments
{
	const RECURSIVE_MODE = true;
	const EXCLUDE_TESTCASES = true;
	const INCLUDE_TESTCASES = false;
	const TESTCASE_PREFIX_MAXLEN = 16; // must be changed if field dimension changes
	const GET_NOT_EMPTY_REQSPEC = 1;
	const GET_EMPTY_REQSPEC = 0;
	
	var $db;
	var $tree_manager;
	var $cfield_mgr;

    // Node Types (NT)
    var $nt2exclude=array('testplan' => 'exclude_me',
	                      'requirement_spec'=> 'exclude_me',
	                      'requirement'=> 'exclude_me');


    var $nt2exclude_children=array('testcase' => 'exclude_my_children',
							       'requirement_spec'=> 'exclude_my_children');

	/** 
	 * Class constructor
	 * 
	 * @param resource &$db reference to database handler
	 */
	function testproject(&$db)
	{
		$this->db = &$db;
		$this->tree_manager = new tree($this->db);
		$this->cfield_mgr=new cfield_mgr($this->db);
		tlObjectWithAttachments::__construct($this->db,'nodes_hierarchy');
        $this->object_table=$this->tables['testprojects'];
	}

/**
 * Create a new test project
 * 
 * @param string $name
 * @param string $color
 * @param boolean $optReq [1,0]
 * @param string $notes
 * @param boolean $active [1,0] optional
 * @param string $tcasePrefix [''] 
 * @param boolean $is_public [1,0] optional
 *
 * @return integer test project id or 0 (if fails)
 *
 * @internal Revisions:
 * 20060709 - franciscom - return type changed
 *                         added new optional argument active
 * 20080112 - franciscom - added $tcasePrefix
 * 20090601 - havlatm - update session if required
 * 
 * @TODO havlatm: rollback node if create fails (DB consistency req.)
 * @TODO havlatm: $is_public parameter need review (do not agreed in team)
 * @TODO havlatm: described return parameter differs from reality
 * @TODO havlatm: parameter $options should be 
 */
function create($name,$color,$options,$notes,$active=1,$tcasePrefix='',$is_public=1)
{
  
	// Create Node and get the id
	$root_node_id = $this->tree_manager->new_root_node($name);
	$tcprefix = $this->formatTcPrefix($tcasePrefix);

	$sql = " INSERT INTO {$this->object_table} (id,color,option_reqs,option_priority," .
	       " option_automation,notes,active,is_public,prefix) " .
	       " VALUES (" . $root_node_id . ", '" .
	                     $this->db->prepare_string($color) . "'," .
	                     $options->requirement_mgmt . "," .
	                     $options->priority_mgmt . "," .
	                     $options->automated_execution . ",'" .
		                 $this->db->prepare_string($notes) . "'," .
		                 $active . "," . $is_public . ",'" .
		                 $this->db->prepare_string($tcprefix) . "')";
	$result = $this->db->exec_query($sql);

	if ($result)
	{
		tLog('The new testproject '.$name.' was succesfully created.', 'INFO');
		
		// set project to session if not defined (the first project) or update the current
		if (!isset($_SESSION['testprojectID']))
		{
			$this->setSessionProject($root_node_id);
		}
	}
	else
	{
		tLog('The new testproject '.$name.' was not created.', 'INFO');
		$root_node_id = 0;
	}

	return($root_node_id);
}

/**
 * Update Test project data in DB and session
 *
 * @param type $id documentation
 * @param type $name documentation
 * @param type $color documentation
 * @param type $opt_req documentation
 * @param type $notes documentation
 * 
 * @return type documentation
 *
 * @internal
 *	20060312 - franciscom - name is setted on nodes_hierarchy table
 *
 **/
function update($id, $name, $color, $opt_req, $optPriority, $optAutomation, 
                $notes,$active=null,$tcasePrefix=null,$is_public=null)
{
    $status_ok=1;
	$status_msg = 'ok';
	$log_msg = 'Test project ' . $name . ' update: Ok.';
	$log_level = 'INFO';

	$add_upd='';
	if( !is_null($active) )
	{
	    $add_upd .=',active=' . (intval($active) > 0 ? 1:0);
	}

	if( !is_null($is_public) )
	{
	    $add_upd .=',is_public=' . (intval($is_public) > 0 ? 1:0);
	}

	if( !is_null($tcasePrefix) )
	{
	    $tcprefix=$this->formatTcPrefix($tcasePrefix);
	    $add_upd .=",prefix='" . $this->db->prepare_string($tcprefix) . "'" ;
	}

	$sql = " UPDATE {$this->object_table} SET color='" . $this->db->prepare_string($color) . "', ".
			" option_reqs=" .  $opt_req . ", " .
			" option_priority=" .  $optPriority . ", " .
			" option_automation=" .  $optAutomation . ", " .
			" notes='" . $this->db->prepare_string($notes) . "' {$add_upd} " .
			" WHERE id=" . $id;
	$result = $this->db->exec_query($sql);

	if ($result)
	{
		// update related node
		$sql = "UPDATE {$this->tables['nodes_hierarchy']} SET name='" .
				$this->db->prepare_string($name) .
				"' WHERE id= {$id}";
		$result = $this->db->exec_query($sql);
	}

	if ($result)
	{
		// update session data
		$this->setSessionProject($id);
	}
	else
	{
		$status_msg = 'Update FAILED!';
		$status_ok = 0;
		$log_level ='ERROR';
		$log_msg = $status_msg;
	}

	tLog($log_msg,$log_level);
	return ($status_ok);
}

/**
 * Set session data related to a Test project
 * 
 * @param integer $projectId Project ID; zero causes unset data
 */
public function setSessionProject($projectId)
{
	$tproject_info = null;
	if ($projectId)
	{
		$tproject_info = $this->get_by_id($projectId);
	}

	if ($tproject_info)
	{
		$_SESSION['testprojectID'] = $tproject_info['id'];
		$_SESSION['testprojectName'] = $tproject_info['name'];
		$_SESSION['testprojectColor'] = $tproject_info['color'];
		$_SESSION['testprojectPrefix'] = $tproject_info['prefix'];
		$_SESSION['testprojectOptReqs'] = isset($tproject_info['option_reqs']) ? $tproject_info['option_reqs'] : null;
		$_SESSION['testprojectOptPriority'] = isset($tproject_info['option_priority']) ? $tproject_info['option_priority'] : null;
		$_SESSION['testprojectOptAutomation'] = isset($tproject_info['option_automation']) ? $tproject_info['option_automation'] : null;

		tLog("Test Project was activated: [" . $tproject_info['id'] . "]" . $tproject_info['name'], 'INFO');
		tLog("Test Project features REQ=" . $_SESSION['testprojectOptReqs'] . ", PRIORITY=" . $_SESSION['testprojectOptPriority']);
	}
	else
	{
		if (isset($_SESSION['testprojectID']))
		{
			tLog("Test Project deactivated: [" . $_SESSION['testprojectID'] . "] " . $_SESSION['testprojectName']);
		}
		unset($_SESSION['testprojectID']);
		unset($_SESSION['testprojectName']);
		unset($_SESSION['testprojectColor']);
		unset($_SESSION['testprojectOptReqs']);
		unset($_SESSION['testprojectOptPriority']);
		unset($_SESSION['testprojectOptAutomation']);
		unset($_SESSION['testprojectPrefix']);
	}

}

/**
 * Get Test project data according to name
 * 
 * @param integer $name 
 * @param string $addClause (optional) additional SQL condition(s)
 * 
 * @return array map with test project info; null if query fails
 */
function get_by_name($name, $addClause = null)
{
	$sql = " SELECT testprojects.*, nodes_hierarchy.name ".
	       " FROM {$this->object_table} testprojects, " .
	       " {$this->tables['nodes_hierarchy']} nodes_hierarchy".
	       " WHERE testprojects.id = nodes_hierarchy.id AND".
	       "  nodes_hierarchy.name = '" . $this->db->prepare_string($name) . "'";
   
	if (!is_null($addClause) )
	{
		$sql .= " AND " . $addClause;
    }
	$recordset = $this->db->get_recordset($sql);
	return $recordset;
}


/**
 * Get Test project data according to ID
 * 
 * @param integer $id test project
 * @return array map with test project info; null if query fails
 */
public function get_by_id($id)
{
	$sql = " SELECT testprojects.*,nodes_hierarchy.name ".
	       " FROM {$this->object_table} testprojects, " .
	       " {$this->tables['nodes_hierarchy']} nodes_hierarchy ".
	       " WHERE testprojects.id = nodes_hierarchy.id ".
	       " AND testprojects.id = {$id}";
	$recordset = $this->db->get_recordset($sql);
	return ($recordset ? $recordset[0] : null);
}


/**
 * Get Test project data according to prefix
 * 
 * @param string $prefix 
 * @param string $addClause optional additional SQL 'AND filter' clause
 * 
 * @return array map with test project info; null if query fails
 */
public function get_by_prefix($prefix, $addClause = null)
{
    $safe_prefix = $this->db->prepare_string($prefix);
    
	$sql = " SELECT testprojects.*,nodes_hierarchy.name ".
	       " FROM {$this->object_table} testprojects, " .
	       " {$this->tables['nodes_hierarchy']} nodes_hierarchy ".
	       " WHERE testprojects.id = nodes_hierarchy.id ".
	       " AND testprojects.prefix = '{$safe_prefix}'";
	       
	$sql .= is_null($addClause) ? '' : " AND {$addClause} ";
	       
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
    20090409 - amitkhullar- added active parameter
    20071104 - franciscom - added order_by

*/
function get_all($order_by=" ORDER BY nodes_hierarchy.name ",$active=null )
{
	$sql = " SELECT testprojects.*, nodes_hierarchy.name ".
	       " FROM {$this->object_table} testprojects, " .
	       " {$this->tables['nodes_hierarchy']} nodes_hierarchy ".
	       " WHERE testprojects.id = nodes_hierarchy.id ";
	
	if (!is_null($active) )
	{
		$sql .= " AND active=" . intval($active) . " ";
	}
	if( !is_null($order_by) )
	{
	  $sql .= $order_by;
	}
	$recordset = $this->db->get_recordset($sql);
	return $recordset;
}

/*
function: get_accessible_for_user
          get list of testprojects, considering user roles.
          Remember that user has:
          1. one default role, assigned when user was created
          2. a different role can be assigned for every testproject.

          For users roles that has not rigth to modify testprojects
          only active testprojects are returned.

args:
      user_id
      [output_type]: choose the output data structure.
                     possible values: map, map_of_map
                     map: key -> test project id
                          value -> test project name

                     map_of_map: key -> test project id
                                 value -> array ('name' => test project name,
                                                 'active' => active status)

                     array_of_map: value -> array  with all testproject table fields plus name.


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
    $sql = " SELECT id,role_id FROM {$this->tables['users']}  where id={$user_id}";
    $user_info = $this->db->get_recordset($sql);
	$role_id=$user_info[0]['role_id'];


	$sql =  " SELECT nodes_hierarchy.name,testprojects.*
 	          FROM {$this->tables['nodes_hierarchy']} nodes_hierarchy
 	          JOIN {$this->object_table} testprojects ON nodes_hierarchy.id=testprojects.id
	          LEFT OUTER JOIN {$this->tables['user_testproject_roles']} user_testproject_roles
		        ON testprojects.id = user_testproject_roles.testproject_id AND
		 	      user_testproject_roles.user_id = {$user_id} WHERE ";

	if ($role_id != TL_ROLES_NO_RIGHTS)
	{
		$sql .=  "(role_id IS NULL OR role_id != ".TL_ROLES_NO_RIGHTS.")";
	}
	else
	{
		$sql .=  "(role_id IS NOT NULL AND role_id != ".TL_ROLES_NO_RIGHTS.")";
    }

	if (has_rights($this->db,'mgt_modify_product') != 'yes')
	{
		$sql .= " AND active=1 ";
    }
	$sql .= $order_by;

    if($output_type == 'array_of_map')
	{
	    $items = $this->db->get_recordset($sql);
	    $do_post_process=0;
	}
	else
	{
	    $arrTemp = $this->db->fetchRowsIntoMap($sql,'id');
	    $do_post_process=1;
	}

	if ($do_post_process && sizeof($arrTemp))
	{
        switch ($output_type)
	    {
	         case 'map':
	    	   foreach($arrTemp as $id => $row)
	    	   {
	    		   $noteActive = '';
	    		   if (!$row['active'])
	    		   {
	    			   $noteActive = TL_INACTIVE_MARKUP;
	    		   }
	    		   $items[$id] = $noteActive . $row['name'];
	    	   }
	    	   break;
        
	         case 'map_of_map':
	    	   foreach($arrTemp as $id => $row)
	    	   {
	    		   $items[$id] = array( 'name' => $row['name'],
	    		                        'active' => $row['active']);
	    	   }
	    	   break;
	    }
	}

	return $items;
}


/*
  function: get_subtree
            Get subtree that has choosen testproject as root.
            Only nodes of type:
            testsuite and testcase are explored and retrieved.

  args: id: testsuite id
        [recursive_mode]: default false
        [exclude_testcases]: default: false
        [exclude_branches]
        [and_not_in_clause]:


  returns: map
           see tree->get_subtree() for details.

  rev : 20080104 - franciscom - added exclude_testcases

*/
function get_subtree($id,$recursive_mode=false,$exclude_testcases=false,
                     $exclude_branches=null, $and_not_in_clause='')
{
  
  $exclude_node_types=$this->nt2exclude;
  if($exclude_testcases)
  {
    $exclude_node_types['testcase']='exclude me';
  }
	$subtree = $this->tree_manager->get_subtree($id,$exclude_node_types,
	                                                $this->nt2exclude_children,
	                                                $exclude_branches,
	                                                $and_not_in_clause,
	                                                $recursive_mode);
  return $subtree;
}


/**
 * displays smarty template to show test project info to users.
 *
 * @param type $smarty [ref] smarty object
 * @param type $id test project
 * @param type $sqlResult [default = '']
 * @param type $action [default = 'update']
 * @param type $modded_item_id [default = 0]
 **/
function show(&$smarty,$template_dir,$id,$sqlResult='', $action = 'update',$modded_item_id = 0)
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

  $smarty->assign('refreshTree',false);
	$smarty->assign('moddedItem',$modded_item);
	$smarty->assign('level', 'testproject');
	$smarty->assign('page_title', lang_get('testproject'));
	$smarty->assign('container_data', $item);
	$smarty->display($template_dir . 'containerView.tpl');
}


/**
 * Count testcases without considering active/inactive status.
 * 
 * @param integer $id: testproject id
 * @return integer count of test cases presents on test project.
 * 
 */
function count_testcases($id)
{
	$tcIDs = array();
	$this->get_all_testcases_id($id,$tcIDs);
	$qty = sizeof($tcIDs);
	
	return $qty;
}


  /*
    function: gen_combo_test_suites
              create array with test suite names
              test suites are ordered in parent-child way, means
              order on array is creating traversing tree branches, reaching end
              of branch, and starting again. (recursive algorithim).


    args :  $id: test project id
            [$exclude_branches]: array with testsuite id to exclude
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
		$ret = array();
		$test_spec = $this->get_subtree($id,!self::RECURSIVE_MODE,self::EXCLUDE_TESTCASES,$exclude_branches);

		if(count($test_spec))
		{
		  $ret = $this->_createHierarchyMap($test_spec);
		}
		return $ret;
	}

	/**
	 * Checks a test project name for correctness
	 *
	 * @param string $name the name to check
	 * @return map with keys: status_ok, msg
	 **/
	function checkName($name)
	{
		global $g_ereg_forbidden;
		$ret['status_ok'] = 1;
		$ret['msg'] = 'ok';

		if ($name == "")
		{
			$ret['msg'] = lang_get('info_product_name_empty');
			$ret['status_ok'] = 0;
		}
		// BUGID 0000086
		if ($ret['status_ok'] && !check_string($name,$g_ereg_forbidden))
		{
			$ret['msg'] = lang_get('string_contains_bad_chars');
			$ret['status_ok'] = 0;
		}
		return $ret;
	}

	/**
	 * Checks a test project name for sintax correctness
	 *
	 * @param string $name the name to check
	 * @return map with keys: status_ok, msg
	 **/
	function checkNameSintax($name)
	{
		global $g_ereg_forbidden;
		$ret['status_ok'] = 1;
		$ret['msg'] = 'ok';

		if ($name == "")
		{
			$ret['msg'] = lang_get('info_product_name_empty');
			$ret['status_ok'] = 0;
		}
		if ($ret['status_ok'] && !check_string($name,$g_ereg_forbidden))
		{
			$ret['msg'] = lang_get('string_contains_bad_chars');
			$ret['status_ok'] = 0;
		}
		return $ret;
	}

	/**
	 * Checks is there is another testproject with different id but same name
	 *
	 **/
	function checkNameExistence($name,$id=0)
	{
	   	$check_op['msg'] = '';
		$check_op['status_ok'] = 1;
		 	
      	if($this->get_by_name($name,"testprojects.id <> {$id}") )
      	{
        	$check_op['msg'] = sprintf(lang_get('error_product_name_duplicate'),$name);
		    $check_op['status_ok'] = 0;
      	}
		return $check_op;
	}

	/**
	 * Checks is there is another testproject with different id but same prefix
	 *
	 **/
	function checkTestCasePrefixExistence($prefix,$id=0)
	{
	   	$check_op['msg'] = '';
		 	$check_op['status_ok'] = 1;
	
      $sql = " SELECT id FROM {$this->object_table} " .
   	         " WHERE prefix='" . $this->db->prepare_string($prefix) . "'";
		     " AND id <> {$id}";

		  $rs = $this->db->get_recordset($sql);
		  if(!is_null($rs))
		  {
		    	$check_op['msg'] = sprintf(lang_get('error_tcase_prefix_exists'),$prefix);
		    	$check_op['status_ok'] = 0;
		  }
		  
		  return $check_op;
	}



	/** 
	 * allow activate or deactivate a test project
	 * 
	 * @param integer $id test project ID
	 * @param integer $status 1=active || 0=inactive
	 */
	function activateTestProject($id, $status)
	{
		$sql = "UPDATE {$this->tables['testprojects']} SET active=" . $status . " WHERE id=" . $id;
		$result = $this->db->exec_query($sql);

		return $result ? 1 : 0;
	}

	/** @TODO add description */
	function formatTcPrefix($str)
	{
	    // limit tcasePrefix len.
	    $fstr = trim($str);
	    if(tlStringLen($fstr) > self::TESTCASE_PREFIX_MAXLEN)
	    {
			$tcprefix = substr($fstr,self::TESTCASE_PREFIX_MAXLEN);
	    }
		return $fstr;
	}

  /*
    function: getTestCasePrefix


    args : id: test project

    returns: null if query fails
             string

  */
  function getTestCasePrefix($id)
  {
  	$ret=null;
  	$sql = "SELECT prefix FROM {$this->object_table} WHERE id = {$id}";
	$ret = $this->db->fetchOneValue($sql);
  	return ($ret);
  }

  /*
    function: generateTestCaseNumber


    args: id: test project

    returns: null if query fails
             a new test case number

  */
  function generateTestCaseNumber($id)
  {
  	$ret=null;
    $sql = " UPDATE {$this->object_table} " .
           " SET tc_counter=tc_counter+1 WHERE id = {$id}";
  	$recordset = $this->db->exec_query($sql);

  	$sql = " SELECT tc_counter  FROM {$this->object_table}  WHERE id = {$id}";
  	$recordset = $this->db->get_recordset($sql);
    $ret=$recordset[0]['tc_counter'];
  	return ($ret);
  }

/** 
 * @param integer $id test project ID
 */
function setPublicStatus($id,$status)
{
    $isPublic = val($status) > 0 ? 1 : 0; 
	$sql = "UPDATE {$this->object_table} SET is_public={$isPublic} WHERE id={$id}";
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
	 **/
	public function addKeyword($testprojectID,$keyword,$notes)
	{
		$kw = new tlKeyword();
		$kw->initialize($testprojectID,$keyword,$notes);
		$result = $kw->writeToDB($this->db);
		if ($result >= tl::OK)
			logAuditEvent(TLS("audit_keyword_created",$keyword),"CREATE",$kw->dbID,"keywords");
		return $result;
	}

	/**
	 * updates the keyword with the given id
	 *
	 * @param type $testprojectID
	 * @param type $id
	 * @param type $keyword
	 * @param type $notes
	 *
	 **/
	function updateKeyword($testprojectID,$id,$keyword,$notes)
	{
		$kw = new tlKeyword($id);
		$kw->initialize($testprojectID,$keyword,$notes);
		$result = $kw->writeToDB($this->db);
		if ($result >= tl::OK)
			logAuditEvent(TLS("audit_keyword_saved",$keyword),"SAVE",$kw->dbID,"keywords");
		return $result;
	}

	/**
	 * gets the keyword with the given id
	 *
	 * @param type $kwid
	 **/
	public function getKeyword($id)
	{
		return tlDBObject::createObjectFromDB($this->db,$id,"tlKeyword");
	}
	
	/**
	 * Gets the keywords of the given test project
	 *
	 * @param int $tprojectID the test project id
	 * @param int $keywordID [default = null] the optional keyword id
	 * 
	 * @return array, every elemen is map with following structure:
	 *                id
	 *                keyword
	 *                notes
	 **/
	public function getKeywords($testproject_id)
	{
		$ids = $this->getKeywordIDsFor($testproject_id);
		return tlDBObject::createObjectsFromDB($this->db,$ids,"tlKeyword");
	}

	/**
	 * Deletes the keyword with the given id
	 *
	 * @param int $id the keywordID
	 *
	 * @return int returns 1 on success, 0 else
	 *
	 * @todo: should we now increment the tcversion also?
	 **/
	function deleteKeyword($id)
	{
		$result = tl::ERROR;
		$keyword = $this->getKeyword($id);
		if ($keyword)
		{
			$result = tlDBObject::deleteObjectFromDB($this->db,$id,"tlKeyword");
		}
		if ($result >= tl::OK)
		{
			logAuditEvent(TLS("audit_keyword_deleted",$keyword->name),"DELETE",$id,"keywords");
		}
		return $result;
	}

	/**
	 * delete Keywords
	 *
	 */
	function deleteKeywords($testproject_id)
	{
		$result = tl::OK;
		$kwIDs = $this->getKeywordIDsFor($testproject_id);
		for($i = 0;$i < sizeof($kwIDs);$i++)
		{
			$resultKw = $this->deleteKeyword($kwIDs[$i]);
			if ($resultKw != tl::OK)
				$result = $resultKw;
		}
		return $result;
	}


	/**
	 * 
	 *
	 */
	protected function getKeywordIDsFor($testproject_id)
	{
		$query = " SELECT id FROM {$this->tables['keywords']}  " .
			   " WHERE testproject_id = {$testproject_id}" .
			   " ORDER BY keyword ASC";
		$keywordIDs = $this->db->fetchColumnsIntoArray($query,'id');

		return $keywordIDs;
	}

	/**
	 * Exports the given keywords to a XML file
	 *
	 * @return strings the generated XML Code
	 **/
	public function exportKeywordsToXML($testproject_id,$bNoXMLHeader = false)
	{
		$kwIDs = $this->getKeywordIDsFor($testproject_id);
		$xmlCode = '';
		if (!$bNoXMLHeader)
		{
			$xmlCode .= TL_XMLEXPORT_HEADER."\n";
		}
		$xmlCode .= "<keywords>";
		for($idx = 0;$idx < sizeof($kwIDs);$idx++)
		{
			$keyword = new tlKeyword($kwIDs[$idx]);
			$keyword->readFromDb($this->db);
			$keyword->writeToXML($xmlCode,true);
		}
		$xmlCode .= "</keywords>";

		return $xmlCode;
	}

	/**
	 * Exports the given keywords to CSV
	 *
	 * @return string the generated CSV code
	 **/
	function exportKeywordsToCSV($testproject_id,$delim = ';')
	{
		$kwIDs = $this->getKeywordIDsFor($testproject_id);
		$csv = null;
		for($idx = 0;$idx < sizeof($kwIDs);$idx++)
		{
			$keyword = new tlKeyword($kwIDs[$idx]);
			$keyword->readFromDb($this->db);
			$keyword->writeToCSV($csv,$delim);
		}
		return $csv;
	}

	function importKeywordsFromCSV($testproject_id,$fileName,$delim = ';')
	{
		$handle = fopen($fileName,"r");
		if ($handle)
		{
			while($data = fgetcsv($handle, TL_IMPORT_ROW_MAX, $delim))
			{
				$k = new tlKeyword();
				$k->initialize($testproject_id,NULL,NULL);
				if ($k->readFromCSV(implode($delim,$data)) >= tl::OK)
				{
					if ($k->writeToDB($this->db) >= tl::OK)
						logAuditEvent(TLS("audit_keyword_created",$k->name),"CREATE",$k->dbID,"keywords");
				}
			}
			fclose($handle);
			return tl::OK;
		}
		else
		{
			return ERROR;
		}	
	}

	function importKeywordsFromXMLFile($testproject_id,$fileName)
	{
		$xml = simplexml_load_file($fileName);
		return $this->importKeywordsFromSimpleXML($testproject_id,$xml);
	}

	function importKeywordsFromXML($testproject_id,$xml)
	{
		$xml = simplexml_load_string($xml);
		return $this->importKeywordsFromSimpleXML($testproject_id,$xml);
	}

	function importKeywordsFromSimpleXML($testproject_id,$xml)
	{
		if (!$xml || $xml->getName() != 'keywords')
			return tlKeyword::E_WRONGFORMAT;
		if ($xml->keyword)
		{
			foreach($xml->keyword as $keyword)
			{
				$k = new tlKeyword();
				$k->initialize($testproject_id,NULL,NULL);
				if ($k->readFromSimpleXML($keyword) >= tl::OK)
				{
					if ($k->writeToDB($this->db) >= tl::OK)
						logAuditEvent(TLS("audit_keyword_created",$k->name),"CREATE",$k->dbID,"keywords");
				}
				else
					return tlKeyword::E_WRONGFORMAT;
			}
		}
		return tl::OK;
	}

	/**
	 * Returns all testproject keywords
	 *
	 *	@param  integer $testproject_id the ID of the testproject
	 *	@return array 	map: key: keyword_id, value: keyword
	 */
	function get_keywords_map($testproject_id)
	{
		$keywordMap = null;
		$keywords = $this->getKeywords($testproject_id);
		if ($keywords)
		{
			foreach($keywords as $kw)
			{
				$keywordMap[$kw->dbID] = $kw->name;
			}
		}
		return $keywordMap;
	}
	/* END KEYWORDS RELATED */

	/* REQUIREMENTS RELATED */
	/**
	 * get list of all SRS for a test project
	 * 
	 * @author Martin Havlat
	 * @return associated array List of titles according to IDs
	 * 
	 * @internal
	 * rev :
	 *    20070104 - franciscom - added [$get_not_empy]
	 **/
  function getOptionReqSpec($tproject_id,$get_not_empty=self::GET_EMPTY_REQSPEC)
  {
    $additional_table='';
    $additional_join='';
    if( $get_not_empty )
    {
  		$additional_table=", {$this->tables['requirements']} REQ ";
  		$additional_join=" AND SRS.id = REQ.srs_id ";
  	}
    $sql = " SELECT SRS.id,NH.name AS title " .
           " FROM {$this->tables['req_specs']} SRS, {$this->tables['nodes_hierarchy']} NH " . $additional_table .
           " WHERE testproject_id={$tproject_id} " .
           " AND SRS.id=NH.id " .
           $additional_join .
  		   " ORDER BY title";
  	return $this->db->fetchColumnsIntoMap($sql,'id','title');
  } // function end


	/**
	 * TBD
   * @author Francisco Mancardi - francisco.mancardi@gmail.com
   *
   * @internal rev :
   *      20090125 - franciscom
   **/
	function genComboReqSpec($id,$mode='dotted')
	{
		$ret = array();
    $exclude_node_types=array('testplan' => 'exclude_me','testsuite' => 'exclude_me',
	                            'testcase'=> 'exclude_me','requirement' => 'exclude_me');

	  $subtree = $this->tree_manager->get_subtree($id,$exclude_node_types,null,null,null,false);
 		if(count($subtree))
		{
		  $ret = $this->_createHierarchyMap($subtree);
    }

		return $ret;
	}

  /*
  
              [$mode]: dotted -> $level number of dot characters are appended to
                               the left of item name to create an indent effect.
                               Level indicates on what tree layer item is positioned.
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


                               key: item id (= node id on tree).
                               value: every array element is an string, containing item name.

                               Result example:

                                2  .TS1
                                3 	..TS2
                                9 	.20071014-16:22:07 TS1
                               10 	..TS2


                     array  -> key: item id (= node id on tree).
                               value: every array element is a map with the following keys
                               'name', 'level'

                                2  	array(name => 'TS1',level =>	1)
                                3   array(name => 'TS2',level =>	2)
                                9	  array(name => '20071014-16:22:07 TS1',level =>1)
                               10   array(name =>	'TS2', level 	=> 2)

  */
  protected function _createHierarchyMap($array2map,$mode='dotted')
  {
		$hmap=array();
		$the_level = 1;
		$level = array();
  		$pivot = $array2map[0];

		foreach($array2map as $elem)
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
					$hmap[$current['id']] = str_repeat('.',$the_level) . $current['name'];
					break;

  				case 'array':
					$hmap[$current['id']] = array('name' => $current['name'], 'level' =>$the_level);
					break;
			}

			// update pivot
			$level[$current['parent_id']]= $the_level;
			$pivot=$elem;
		}
		
	    return $hmap;
  }



	/**
	 * collect information about current list of Requirements Specification
	 *
	 * @param integer $testproject_id
	 * @param string  $id optional id of the requirement specification
	 *
	 * @return mixed 
	 * 		null if no srs exits, or no srs exists for id
	 * 		array, where each element is a map with SRS data.
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
	 * @internal rev: 
	 * 20090506 - francisco.mancardi@gruppotesi.com - Requirements Refactoring
	 *       
	 **/
	public function getReqSpec($testproject_id, $id = null, $fields=null,$access_key=null)
	{
    	$fields2get="RSPEC.id,testproject_id,RSPEC.scope,RSPEC.total_req,RSPEC.type," .
                "RSPEC.author_id,RSPEC.creation_ts,RSPEC.modifier_id," .
                "RSPEC.modification_ts,NH.name AS title";
    
	    $fields = is_null($fields) ? $fields2get : implode(',',$fields);
    	$sql = " SELECT {$fields} FROM {$this->tables['req_specs']} RSPEC, " .
       			" {$this->tables['nodes_hierarchy']} NH " .
           		" WHERE testproject_id={$testproject_id} " .
				" AND RSPEC.id=NH.id ";
           
		if (!is_null($id))
	    {
    	    $sql .= " AND RSPEC.id=" . $id;
	    }
    	$sql .= "  ORDER BY title";
	    $rs = is_null($access_key) ? $this->db->get_recordset($sql) : $this->db->fetchRowsIntoMap($sql,$access_key);
	      
		return $rs;
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
	 *
	 * rev: 20071106 - franciscom - changed return type
	 */
	function createReqSpec($testproject_id,$title, $scope, $countReq,$user_id,$type = 'n')
	{
		$ignore_case=1;
		$result=array();

		$result['status_ok'] = 0;
		$result['msg'] = 'ko';
		$result['id'] = 0;

    	$title=trim($title);

    	$chk=$this->check_srs_title($testproject_id,$title,$ignore_case);
		if ($chk['status_ok'])
		{
			$sql = "INSERT INTO {$this->tables['req_specs']} " .
			       " (testproject_id, title, scope, type, total_req, author_id, creation_ts)
					    VALUES (" . $testproject_id . ",'" . $this->db->prepare_string($title) . "','" .
					                $this->db->prepare_string($scope) .  "','" . $this->db->prepare_string($type) . "','" .
					                $this->db->prepare_string($countReq) . "'," . $this->db->prepare_string($user_id) . ", " .
					                $this->db->db_now() . ")";

			if (!$this->db->exec_query($sql))
			{
				$result['msg']=lang_get('error_creating_req_spec');
			}
			else
			{
			  $result['id']=$this->db->insert_id($this->tables['req_specs']);
        	$result['status_ok'] = 1;
		    $result['msg'] = 'ok';
			}
		}
		else
		{
			$result['msg']=$chk['msg'];
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
	public function get_srs_by_title($testproject_id,$title,$ignore_case=0)
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
              Do checks on srs title, to understand if can be used.

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
		$ret['status_ok'] = 1;
		$ret['msg'] = '';
		
		$title = trim($title);
		
		if ($title == "")
		{
			$ret['status_ok'] = 0;
			$ret['msg'] = lang_get("warning_empty_req_title");
		}
		
		if($ret['status_ok'])
		{
			$ret['msg'] = 'ok';
			$rs = $this->get_srs_by_title($testproject_id,$title,$ignore_case);
			
			if(!is_null($rs))
			{
				$ret['msg'] = lang_get("warning_duplicate_req_title");
				$ret['status_ok'] = 0;
			}
		}
		return $ret;
	}
/* END REQUIREMENT RELATED */
// ----------------------------------------------------------------------------------------


	/**
	 * Deletes all testproject related role assignments for a given testproject
	 *
	 * @param integer $tproject_id
	 * @return integer tl::OK on success, tl::ERROR else
	 **/
	function deleteUserRoles($tproject_id)
	{
		$query = "DELETE FROM {$this->tables['user_testproject_roles']} " . 
			" WHERE testproject_id = {$tproject_id}";
		if ($this->db->exec_query($query))
		{
			$testProject = $this->get_by_id($tproject_id);
			if ($testProject)
			{
				logAuditEvent(TLS("audit_all_user_roles_removed_testproject",$testProject['name']),
					"ASSIGN",$tproject_id,"testprojects");
			}
			return tl::OK;
		}
		
		return tl::ERROR;
	}

	/**
	 * Gets all testproject related role assignments
	 *
	 * @param integer $tproject_id
	 * @return array assoc array with keys take from the user_id column
	 **/
	function getUserRoleIDs($tproject_id)
	{
		$query = "SELECT user_id,role_id FROM {$this->tables['user_testproject_roles']} " .
			"WHERE testproject_id = {$tproject_id}";
		$roles = $this->db->fetchRowsIntoMap($query,'user_id');
		
		return $roles;
	}

	/**
	 * Inserts a testproject related role for a given user
	 *
	 * @param integer $userID the id of the user
	 * @param integer $tproject_id
	 * @param integer $roleID the role id
	 * 
	 * @return integer tl::OK on success, tl::ERROR else
	 **/
	function addUserRole($userID,$tproject_id,$roleID)
	{
		$query = "INSERT INTO {$this->tables['user_testproject_roles']} " .
			"(user_id,testproject_id,role_id) VALUES ({$userID},{$tproject_id},{$roleID})";
		if($this->db->exec_query($query))
		{
			$testProject = $this->get_by_id($tproject_id);
			$role = tlRole::getByID($this->db,$roleID,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
			$user = tlUser::getByID($this->db,$userID,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
			if ($user && $testProject && $role)
			{
				logAuditEvent(TLS("audit_users_roles_added_testproject",$user->getDisplayName(),
					$testProject['name'],$role->name),"ASSIGN",$tproject_id,"testprojects");
			}
			return tl::OK;
		}
		
		return tl::ERROR;
	}
	
	/**
	 * delete test project from system, deleting all dependent data:
	 *      keywords, requirements, custom fields, testsuites, testplans,
	 *      testcases, results, testproject related roles,
	 * 
	 * @param integer $id test project id
	 * @return integer status
	 * 
	 */
	function delete($id)
	{
		$ret['msg']='ok';
		$ret['status_ok']=1;
		
		$error = '';
		$reqspec_mgr = new requirement_spec_mgr($this->db);
		
		$a_sql = array();
		
		$this->deleteKeywords($id);
		$this->deleteAttachments($id);
		
		$reqSpecSet=$reqspec_mgr->get_all_in_testproject($id);
		if( !is_null($reqSpecSet) && count($reqSpecSet) > 0 )
		{
			foreach($reqSpecSet as $reqSpec)
			{
				$reqspec_mgr->delete_deep($reqSpec['id']);
			}      
		}
		
		$tplanSet = $this->get_all_testplans($id);
		if( !is_null($tplanSet) && count($tplanSet) > 0 )
		{
			$tplan_mgr = new testplan($this->db);
			$items=array_keys($tplanSet);     
			foreach($items as $key)
			{
				$tplan_mgr->delete($key);
			}
		}
		
		$a_sql[] = array(
			"UPDATE {$this->tables['users']}  SET default_testproject_id = NULL " .
			" WHERE default_testproject_id = {$id}",
			'info_resetting_default_project_fails',
		);
		
		foreach ($a_sql as $oneSQL)
		{
			if (empty($error))
			{
				$sql = $oneSQL[0];
				$result = $this->db->exec_query($sql);
				if (!$result)
				{
					$error .= lang_get($oneSQL[1]);
				}	
			}
		}
		
		
		if ($this->deleteUserRoles($id) < tl::OK)
		{
			$error .= lang_get('info_deleting_project_roles_fails');
		}
		

		// ---------------------------------------------------------------------------------------
		// delete product itself and items directly related to it like:
		// custom fields assignments
		// custom fields values ( right now we are not using custom fields on test projects)
		// attachments
		if (empty($error))
		{
			$sql="DELETE FROM {$this->tables['cfield_testprojects']} WHERE testproject_id = {$id} ";
			$this->db->exec_query($sql);
			
			$sql = "DELETE FROM {$this->object_table} WHERE id = {$id}";
			
			$result = $this->db->exec_query($sql);
			if ($result)
			{
				$tproject_id_on_session = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : $id;
				if ($id == $tproject_id_on_session)
				{
					setSessionTestProject(null);
				}	
			}
			else
			{
				$error .= lang_get('info_product_delete_fails');
			}	
		}
		
		if (empty($error))
		{
			$this->tree_manager->delete_subtree_objects($id,'',array('testcase' => 'exclude_tcversion_nodes'));
			$sql="DELETE FROM {$this->tables['nodes_hierarchy']} WHERE id = {$id} ";
			$this->db->exec_query($sql);
		}
		
		if( !empty($error) )
		{
			$ret['msg']=$error;
			$ret['status_ok']=0;
		}
		
		return $ret;
	}


/*
  function: get_all_testcases_id
            All testproject testcases node id.

  args :idList: comma-separated list of IDs (should be the projectID, but could
		            also be an arbitrary suiteID

  returns: array with testcases node id in parameter tcIDs.
           null is nothing found

*/
	function get_all_testcases_id($idList,&$tcIDs)
	{
		static $tcNodeTypeID;
		static $tsuiteNodeTypeID;
		if (!$tcNodeTypeID)
		{
			$tcNodeTypeID = $this->tree_manager->node_descr_id['testcase'];
			$tsuiteNodeTypeID = $this->tree_manager->node_descr_id['testsuite'];
		}
		$sql = "SELECT id,node_type_id from {$this->tables['nodes_hierarchy']} WHERE parent_id IN ({$idList})";
		$sql .= " AND node_type_id IN ({$tcNodeTypeID},{$tsuiteNodeTypeID}) "; 
		
		$result = $this->db->exec_query($sql);
		if ($result)
		{
			$suiteIDs = array();
			while($row = $this->db->fetch_array($result))
			{
				if ($row['node_type_id'] == $tcNodeTypeID)
				{
					$tcIDs[] = $row['id'];
				}
				$suiteIDs[] = $row['id'];
			}
			if (sizeof($suiteIDs))
			{
				$suiteIDs  = implode(",",$suiteIDs);
				$this->get_all_testcases_id($suiteIDs,$tcIDs);
			}
		}	
	}


/*
  function: get_keywords_tcases
            testproject keywords (with related testcase node id),
            that are used on testcases.

  args :testproject_id
        [keyword_id]= 0 -> no filter
                      <> 0 -> look only for this keyword
                      can be an array.



  returns: map: key: testcase_id
                value: map 
                          key: keyword_id
                          value: testcase_id,keyword_id,keyword

                Example:
                 [24] => Array ( [3] => Array( [testcase_id] => 24
                                               [keyword_id] => 3
                                               [keyword] => MaxFactor )
                         
                                 [2] => Array( [testcase_id] => 24
                                               [keyword_id] => 2
                                               [keyword] => Terminator ) )


*/
function get_keywords_tcases($testproject_id, $keyword_id=0, $keyword_filter_type='OR')
{
    $keyword_filter= '' ;
    $subquery='';
    
    if( is_array($keyword_id) )
    {
        $keyword_filter = " AND keyword_id IN (" . implode(',',$keyword_id) . ")";          	
        
        if($keyword_filter_type == 'AND')
        {
		        $subquery = "AND testcase_id IN (" .
		                    " SELECT FOXDOG.testcase_id FROM
		                      ( SELECT COUNT(testcase_id) AS HITS,testcase_id
		                        FROM {$this->tables['keywords']} K, {$this->tables['testcase_keywords']}
		                        WHERE keyword_id = K.id
		                        AND testproject_id = {$testproject_id}
		                        {$keyword_filter}
		                        GROUP BY testcase_id ) AS FOXDOG " .
		                    " WHERE FOXDOG.HITS=" . count($keyword_id) . ")";
		                 
            $keyword_filter ='';
        }    
    }
    else if( $keyword_id > 0 )
    {
        $keyword_filter = " AND keyword_id = {$keyword_id} ";
    }
		
		$map_keywords = null;
		$sql = " SELECT testcase_id,keyword_id,keyword
		         FROM {$this->tables['keywords']} K, {$this->tables['testcase_keywords']}
		         WHERE keyword_id = K.id
		         AND testproject_id = {$testproject_id}
		         {$keyword_filter} {$subquery}
			       ORDER BY keyword ASC ";

		$map_keywords = $this->db->fetchMapRowsIntoMap($sql,'testcase_id','keyword_id');

		return($map_keywords);
} //end function


/*
  function: get_all_testplans

  args : $testproject_id

         [$filters]: optional map, with optional keys
                     [$get_tp_without_tproject_id]
                     used just for backward compatibility (TL 1.5)
                     default: 0 -> 1.6 and up behaviour

                     [$plan_status]
                     default: null -> no filter on test plan status
                              1 -> active test plans
                              0 -> inactive test plans

                     [$exclude_tplans]: null -> do not apply exclusion
                                        id -> test plan id to exclude
         
  returns:

*/
function get_all_testplans($testproject_id,$filters=null)
{
	$sql = " SELECT NH.id,NH.name,notes,active,is_public,testproject_id " .
	       " FROM {$this->tables['nodes_hierarchy']} NH,{$this->tables['testplans']} TPLAN";
	       
	$where = " WHERE NH.id=TPLAN.id ";
    $where .= ' AND (testproject_id = ' . $testproject_id . " ";
    if( !is_null($filters) )
    {
        $key2check=array('get_tp_without_tproject_id' => 0, 'plan_status' => null,
                         'tplan2exclude' => null);
        
        foreach($key2check as $varname => $defValue)
        {
            $$varname=isset($filters[$varname]) ? $filters[$varname] : $defValue;   
        }                
        
        // if($get_tp_without_tproject_id)
	      // {
	      // 		$where .= " OR testproject_id = 0 ";
	      // }
	      $where .= " ) ";
    
	      if(!is_null($plan_status))
	      {
	      	$my_active = to_boolean($plan_status);
	      	$where .= " AND active = " . $my_active;
	      }
    
	      if(!is_null($tplan2exclude))
	      {
	      	$where .= " AND TPLAN.id != {$tplan2exclude} ";
	      }
    }
    else
    {
        $where .=")";  
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
	       " FROM {$this->tables['nodes_hierarchy']} NH, {$this->tables['testplans']} testplans " .
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
  $fl=$this->tree_manager->get_children($tproject_id,
                                        array( 'testcase', 'exclude_me',
                                               'testplan' => 'exclude_me',
                                               'requirement_spec' => 'exclude_me' ));
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



/**
 * getTCasesLinkedToAnyTPlan
 *
 * for target test project id ($id) get test case id of
 * every test case that has been assigned at least to one of all test plans
 * belonging to test project. 
 *
 * @param int $id test project id
 *
 */
function getTCasesLinkedToAnyTPlan($id)
{
    $sql=" SELECT DISTINCT parent_id AS testcase_id " .
         " FROM {$this->tables['nodes_hierarchy']} WHERE id IN " .
         " ( SELECT tcversion_id FROM {$this->tables['testplan_tcversions']} " .
         "   WHERE testplan_id IN " .
         "   ( SELECT NH.id AS tplan_id " .
         "     FROM {$this->tables['nodes_hierarchy']} NH, {$this->tables['node_types']} NT" .
         "     WHERE NH.node_type_id=NT.id AND NT.description='testplan' AND NH.parent_id={$id}))";

    $rs=$this->db->fetchRowsIntoMap($sql,'testcase_id');
    return $rs;
}


/**
 * getFreeTestCases
 *
 *
 * @param int $id test project id
 * 
 */
function getFreeTestCases($id,$options=null)
{
    $retval['items']=null;
    $retval['allfree']=false;
    
    $this->get_all_testcases_id($id,$all);
    $linked=array();
    $free=null;
    if(!is_null($all))
    {
        $all=array_flip($all);
        $linked=$this->getTCasesLinkedToAnyTPlan($id);
        $retval['allfree']=is_null($linked); 
        $free=$retval['allfree'] ? $all : array_diff_key($all,$linked);
    }
    
    if( !is_null($free) && count($free) > 0)
    {
        $in_clause=implode(',',array_keys($free));
   	    $sql = " SELECT MAX(TCV.version) AS version, TCV.tc_external_id, " .
   	           " NHA.parent_id AS id, NHB.name " .
   	           " FROM {$this->tables['tcversions']} TCV,{$this->tables['nodes_hierarchy']} NHA, " .
	           "      {$this->tables['nodes_hierarchy']} NHB " .
	           " WHERE NHA.parent_id IN ({$in_clause}) " .
   	           " AND TCV.id = NHA.id " .
   	           " AND NHB.id = NHA.parent_id " .
	           " GROUP BY NHB.name,NHA.parent_id,TCV.tc_external_id " .
	           " ORDER BY NHA.parent_id";
	    $retval['items']=$this->db->fetchRowsIntoMap($sql,'id');       
    }

    
    return $retval;
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
function get_linked_custom_fields($id,$node_type=null,$access_key='id')
{
  $additional_table="";
  $additional_join="";

  if( !is_null($node_type) )
  {
 		$hash_descr_id = $this->tree_manager->get_available_node_types();
    $node_type_id=$hash_descr_id[$node_type];

    $additional_table=",{$this->tables['cfield_node_types']} CFNT ";
    $additional_join=" AND CFNT.field_id=CF.id AND CFNT.node_type_id={$node_type_id} ";
  }
  
  $sql="SELECT CF.*,CFTP.display_order " .
       " FROM {$this->tables['custom_fields']} CF, {$this->tables['cfield_testprojects']} CFTP " .
       $additional_table .
       " WHERE CF.id=CFTP.field_id " .
       " AND   CFTP.testproject_id={$id} " .
       $additional_join .
       " ORDER BY CFTP.display_order";
  $map = $this->db->fetchRowsIntoMap($sql,$access_key);
  return($map);
}

} // end class

?>