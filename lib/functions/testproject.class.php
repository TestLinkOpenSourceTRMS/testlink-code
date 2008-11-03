<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: testproject.class.php,v $
 * @version $Revision: 1.89 $
 * @modified $Date: 2008/11/03 22:02:50 $  $Author: franciscom $
 * @author franciscom
 *
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
require_once( dirname(__FILE__) . '/attachments.inc.php');
require_once( dirname(__FILE__) . '/keyword.class.php');

class testproject extends tlObjectWithAttachments
{
  const RECURSIVE_MODE=true;
  const EXCLUDE_TESTCASES=true;
  const INCLUDE_TESTCASES=false;
  const TESTCASE_PREFIX_MAXLEN=16; // must be changed if field dimension changes


	private $object_table='testprojects';
	private $requirements_table='requirements';
	private $requirement_spec_table='req_specs';
	private $req_coverage_table="req_coverage";
	private $nodes_hierarchy_table="nodes_hierarchy";
	private $keywords_table = "keywords";
	private $testcase_keywords_table="testcase_keywords";
	private $testplans_table="testplans";
	private $custom_fields_table="custom_fields";
	private $cfield_testprojects_table="cfield_testprojects";
	private $cfield_node_types_table="cfield_node_types";
	private $user_testproject_roles_table="user_testproject_roles";

	var $db;
	var $tree_manager;
	var $cfield_mgr;

  // Node Types (NT)
  var $nt2exclude=array('testplan' => 'exclude_me',
	                      'requirement_spec'=> 'exclude_me',
	                      'requirement'=> 'exclude_me');


  var $nt2exclude_children=array('testcase' => 'exclude_my_children',
													       'requirement_spec'=> 'exclude_my_children');


  /*
    function: testproject
              Constructor

    args:

    returns:

  */
	function testproject(&$db)
	{
		$this->db = &$db;
		$this->tree_manager = new tree($this->db);
		$this->cfield_mgr=new cfield_mgr($this->db);

		tlObjectWithAttachments::__construct($this->db,'nodes_hierarchy');
	}

/**
 * create a new test project
 * @param string $name
 * @param string $color
 * @param string $optReq [1,0]
 * @param string $notes
 * [@param boolean $active [1,0] ]
 * [@param string $tcasePrefix [''] ]
  *
 * @return everything OK -> test project id
 *         problems      -> 0 (invalid node id)
 *
 * 20080112 - franciscom - added $tcasePrefix
 * 20060709 - franciscom - return type changed
 *                         added new optional argument active
 *
 */
// function create($name,$color,$optReq,$optPriority,$optAutomation,$notes,$active=1,$tcasePrefix='')
function create($name,$color,$options,$notes,$active=1,$tcasePrefix='')
{
  
	// Create Node and get the id
	$root_node_id = $this->tree_manager->new_root_node($name);
	$tcprefix=$this->formatTcPrefix($tcasePrefix);

	$sql = " INSERT INTO {$this->object_table} (id,color,option_reqs,option_priority," .
	       "option_automation,notes,active,prefix) VALUES (" . $root_node_id . ", '" .
	                     $this->db->prepare_string($color) . "'," .
	                     $options->requirement_mgmt . "," .
	                     $options->priority_mgmt . "," .
	                     $options->automated_execution . ",'" .
		                 $this->db->prepare_string($notes) . "'," .
		                 $active . ",'" .
		                 $this->db->prepare_string($tcprefix) . "')";

	$result = $this->db->exec_query($sql);
	if ($result)
	{
		tLog('The new testproject '.$name.' was succesfully created.', 'INFO');
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
function update($id, $name, $color, $opt_req, $optPriority, $optAutomation, $notes,$active=null,$tcasePrefix=null)
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
		$sql = "UPDATE {$this->nodes_hierarchy_table} SET name='" .
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
		$_SESSION['testprojectOptPriority'] = $optPriority;
		$_SESSION['testprojectOptAutomation'] = $optAutomation;
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
	       " FROM {$this->object_table}, {$this->nodes_hierarchy_table} ".
	       " WHERE testprojects.id = nodes_hierarchy.id AND".
	       "  nodes_hierarchy.name = '" . $this->db->prepare_string($name) . "'";

	if (!is_null($addClause) )
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
	       " FROM {$this->object_table}, {$this->nodes_hierarchy_table} ".
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
	       " FROM {$this->object_table}, {$this->nodes_hierarchy_table} ".
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
  $sql = " SELECT id,role_id FROM users where id={$user_id}";
  $user_info = $this->db->get_recordset($sql);
	$role_id=$user_info[0]['role_id'];


	$sql =  " SELECT nodes_hierarchy.name,testprojects.*
 	          FROM {$this->nodes_hierarchy_table}
 	          JOIN {$this->object_table} ON nodes_hierarchy.id=testprojects.id
	          LEFT OUTER JOIN {$this->user_testproject_roles_table}
		        ON testprojects.id = user_testproject_roles.testproject_id AND
		 	      user_testproject_roles.user_ID = {$user_id} WHERE ";

	if ($role_id != TL_ROLES_NONE)
		$sql .=  "(role_id IS NULL OR role_id != ".TL_ROLES_NONE.")";
	else
		$sql .=  "(role_id IS NOT NULL AND role_id != ".TL_ROLES_NONE.")";


	if (has_rights($this->db,'mgt_modify_product') != 'yes')
		$sql .= " AND active=1 ";

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


/*
  function: count_testcases
            Count testcases without considering active/inactive status.

  args : id: testproject id

  returns: int: test cases presents on test project.

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
		$test_spec = $this->get_subtree($id,!self::RECURSIVE_MODE,self::EXCLUDE_TESTCASES,$exclude_branches);

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
	 * @return map with keys: status_ok, msg
	 **/
	function checkName($name)
	{
		global $g_ereg_forbidden;
		$ret['status_ok']=1;
		$ret['msg']='ok';

		if (!strlen($name))
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
		$ret['status_ok']=1;
		$ret['msg']='ok';

		if (!strlen($name))
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
		 	
      if( $this->get_by_name($name,"testprojects.id <> {$id}") )
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

  /*
    function:

    args:

    returns:

  */

  function formatTcPrefix($str)
  {
	    // limit tcasePrefix len.
	    $fstr=trim($str);
	    if(strlen($fstr) > self::TESTCASE_PREFIX_MAXLEN)
	    {
	      $tcprefix=substr($fstr,self::TESTCASE_PREFIX_MAXLEN);
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
  	$sql = " SELECT testprojects.prefix ".
  	       " FROM {$this->object_table} " .
  	       " WHERE testprojects.id = {$id}";
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
           " SET tc_counter=tc_counter+1 " .
  	       " WHERE testprojects.id = {$id}";
  	$recordset = $this->db->exec_query($sql);

  	$sql = " SELECT tc_counter ".
  	       " FROM {$this->object_table} " .
  	       " WHERE testprojects.id = {$id}";
  	$recordset = $this->db->get_recordset($sql);
    $ret=$recordset[0]['tc_counter'];
  	return ($ret);
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
	 *
	 * @param type $kwid
	 *
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
	 * @return array, every elemen is map with following structure:
	 *
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
			$result = tlDBObject::deleteObjectFromDB($this->db,$id,"tlKeyword");
		if ($result >= tl::OK)
			logAuditEvent(TLS("audit_keyword_deleted",$keyword->name),"DELETE",$id,"keywords");
		return $result;
	}

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

	protected function getKeywordIDsFor($testproject_id)
	{
		$query = " SELECT id FROM keywords " .
			   " WHERE testproject_id = {$testproject_id}" .
			   " ORDER BY keyword ASC";
		$keywordIDs = $this->db->fetchColumnsIntoArray($query,'id');

		return $keywordIDs;
	}

	/**
	 * Exports the given keywords to a XML file
	 *
	 *
	 * @return strings the generated XML Code
	 **/
	public function exportKeywordsToXML($testproject_id,$bNoXMLHeader = false)
	{
		//SCHLUNDUS: mayvbe a keywordCollection object should be used instead?
		$kwIDs = $this->getKeywordIDsFor($testproject_id);
		$xmlCode = '';
		if (!$bNoXMLHeader)
			$xmlCode .= TL_XMLEXPORT_HEADER."\n";
		$xmlCode .= "<keywords>";
		for($i = 0;$i < sizeof($kwIDs);$i++)
		{
			$keyword = new tlKeyword($kwIDs[$i]);
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
		//SCHLUNDUS: maybe a keywordCollection object should be used instead?
		$kwIDs = $this->getKeywordIDsFor($testproject_id);
		$csv = null;
		for($i = 0;$i < sizeof($kwIDs);$i++)
		{
			$keyword = new tlKeyword($kwIDs[$i]);
			$keyword->readFromDb($this->db);
			$keyword->writeToCSV($csv,$delim);
		}
		return $csv;
	}

	function importKeywordsFromCSV($testproject_id,$fileName,$delim = ';')
	{
		//SCHLUNDUS: maybe a keywordCollection object should be used instead?
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
			return ERROR;
	}

	function importKeywordsFromXMLFile($testproject_id,$fileName)
	{
		$xml = simplexml_load_file($fileName);
		return $this->importKeywordsFromSimpleXML($testproject_id,$xml);
	}

	function importKeywordsFromXML($testproject_id,$xml)
	{
		//SCHLUNDUS: maybe a keywordCollection object should be used instead?
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

	/*
	*        Returns all testproject keywords
	*
	*	@param  int $testproject_id the ID of the testproject
	*	@returns: map: key: keyword_id, value: keyword
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
   *
   * @return associated array List of titles according to IDs
   *
   * @author Martin Havlat
   *
   * rev :
   *      20070104 - franciscom - added [$get_not_empy]
   **/
  function getOptionReqSpec($tproject_id,$get_not_empty=0)
  {
    $additional_table='';
    $additional_join='';
    if( $get_not_empty )
    {
  		$additional_table=", requirements REQ ";
  		$additional_join=" AND SRS.id = REQ.srs_id ";
  	}
    $sql = " SELECT SRS.id,SRS.title " .
           " FROM req_specs SRS " . $additional_table .
           " WHERE testproject_id={$tproject_id} " .
           $additional_join .
  		     " ORDER BY SRS.title";
  	return $this->db->fetchColumnsIntoMap($sql,'id','title');
  } // function end



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
			$sql = "INSERT INTO req_specs (testproject_id, title, scope, type, total_req, author_id, creation_ts)
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
			  $result['id']=$this->db->insert_id('req_specs');
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
/**
 * Deletes all testproject related role assignments for a given testproject
 *
 * @param int $tproject_id
 * @returns tl::OK on success, tl::ERROR else
 **/
function deleteUserRoles($tproject_id)
{
	$query = "DELETE FROM user_testproject_roles WHERE testproject_id = {$tproject_id}";
	if ($this->db->exec_query($query))
	{
		$testProject = $this->get_by_id($tproject_id);
		if ($testProject)
			logAuditEvent(TLS("audit_all_user_roles_removed_testproject",$testProject['name']),"ASSIGN",$tproject_id,"testprojects");
		return tl::OK;
	}
	return tl::ERROR;
}

/**
 * Gets all testproject related role assignments
 *
 * @param int $tproject_id
 * @return array assoc array with keys take from the user_id column
 **/
function getUserRoleIDs($tproject_id)
{
	$query = "SELECT user_id,role_id FROM user_testproject_roles " .
	         "WHERE testproject_id = {$tproject_id}";
	$roles = $this->db->fetchRowsIntoMap($query,'user_id');

	return $roles;
}
/**
 * Inserts a testproject related role for a given user
 *
 * @param int $userID the id of the user
 * @param int $tproject_id
 * @param int $roleID the role id
 * @returns tl::OK on success, tl::ERROR else
 **/
function addUserRole($userID,$tproject_id,$roleID)
{
	$query = "INSERT INTO user_testproject_roles " .
	         "(user_id,testproject_id,role_id) VALUES ({$userID},{$tproject_id},{$roleID})";
	if($this->db->exec_query($query))
	{
		$testProject = $this->get_by_id($tproject_id);
		$role = tlRole::getByID($this->db,$roleID,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
		$user = tlUser::getByID($this->db,$userID,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
		if ($user && $testProject && $role)
			logAuditEvent(TLS("audit_users_roles_added_testproject",$user->getDisplayName(),$testProject['name'],$role->name),"ASSIGN",$tproject_id,"testprojects");
		return tl::OK;
	}

	return tl::ERROR;
}
/*
  function: delete
            delete test project from system, deleting all dependent data:
            keywords, requirements, custom fields, testsuites, testplans,
            testcases, results, testproject related roles,


  args :id: testproject id

  returns: -

*/
function delete($id)
{
  $ret['msg']='ok';
  $ret['status_ok']=1;

	$error = '';

	$a_sql = array();

	$this->deleteKeywords($id);
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

	if ($this->deleteUserRoles($id) < tl::OK)
		$error .= lang_get('info_deleting_project_roles_fails');

	$tpIDs = $this->get_all_testplans($id);
	if ($tpIDs)
	{
		//SCHLUNDUS: can be refactored by calling testplan->delete and let the testplan delete itself
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
	* CUSTOM FIELDS
	*/

	$this->deleteAttachments($id);

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

		$sql = "DELETE FROM {$this->object_table} WHERE id = {$id}";

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
    $sql="DELETE FROM {$this->nodes_hierarchy_table} WHERE id = {$id} ";
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
		$sql = "SELECT id,node_type_id from {$this->tree_manager->obj_table} WHERE parent_id IN ({$idList})";
		$sql .= " AND node_type_id IN ({$tcNodeTypeID},{$tsuiteNodeTypeID}) "; 
		
		$result = $this->db->exec_query($sql);
		if ($result)
		{
			$suiteIDs = array();
			while($row = $this->db->fetch_array($result))
			{
				if ($row['node_type_id'] == $tcNodeTypeID)
					$tcIDs[] = $row['id'];
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
    
    //echo $keyword_filter_type;
    if( is_array($keyword_id) )
    {
        $keyword_filter = " AND keyword_id IN (" . implode(',',$keyword_id) . ")";          	
        
        if($keyword_filter_type == 'AND')
        {
		        $subquery = "AND testcase_id IN (" .
		                    " SELECT FOXDOG.testcase_id FROM
		                      ( SELECT COUNT(testcase_id) AS HITS,testcase_id
		                        FROM {$this->keywords_table} K, {$this->testcase_keywords_table}
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
		         FROM {$this->keywords_table} K, {$this->testcase_keywords_table}
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
// $get_tp_without_tproject_id=0,$plan_status=null)
function get_all_testplans($testproject_id,$filters=null)
{
	$sql = " SELECT NH.id,NH.name,notes,active,testproject_id " .
	       " FROM {$this->nodes_hierarchy_table} NH,{$this->testplans_table} TPLAN";
	       
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
	       " FROM {$this->nodes_hierarchy_table} NH, {$this->testplans_table} " .
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
  // 20071111 - franciscom
  $fl=$this->tree_manager->get_children($tproject_id,
                                        array('testplan' => 'exclude_me',
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

    $additional_table=",{$this->cfield_node_types_table} CFNT ";
    $additional_join=" AND CFNT.field_id=CF.id AND CFNT.node_type_id={$node_type_id} ";
  }
  $sql="SELECT CF.*,CFTP.display_order " .
       " FROM {$this->custom_fields_table} CF, {$this->cfield_testprojects_table} CFTP " .
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