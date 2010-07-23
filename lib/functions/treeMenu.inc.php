<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * This file generates tree menu for test specification and test execution.
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: treeMenu.inc.php,v 1.137 2010/07/23 16:03:38 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 * @uses 		config.inc.php
 *
 * @internal Revisions:
 *  20100702 - asimon - fixed custom field filtering problem caused by 
 *                      wrong array indexes and little logic errors
 *  20100701 - asimon - replaced is_null in renderTreeNode() by !isset 
 *                      because of warnings in event log
 *  20100701 - asimon - added some additional isset() checks to avoid warnings
 *  20100628 - asimon - removal of constants from filter control class
 *  20160625 - asimon - refactoring for new filter features and BUGID 3516
 *  20100624 - asimon - CVS merge (experimental branch to HEAD)
 *  20100622 - asimon - refactoring of following functions for new filter classes:
 *                      generateExecTree, renderExecTreeNode(), prepareNode(), 
 *                      generateTestSpecTree, renderTreeNode(), filter_by_*()
 *	20100611 - franciscom - renderExecTreeNode(), renderTreeNode() interface changes
 *							generateExecTree() - interface changes and output changes
 *  20100602 - franciscom - extjs_renderExecTreeNodeOnOpen() - added 'tlNodeType' 	
 *  20100428 - asimon - BUGID 3301 and related: 
 *                      added filtering by custom fields to generateTestSpecTree(),
 *                      added importance setting in prepareNode() because of 
 *                      "undefined" error in event log,
 *                      added function filter_by_cfield_values() 
 *                      which is used from generateTestSpecTree()
 *	20100417 - franciscom - BUGID 2498 - spec tree  - filter by test case spec importance
 *	20100417 - franciscom - BUGID 3380 - execution tree - filter by test case execution type
 *	20100415 - franciscom - BUGID 2797 - filter by test case execution type
 *	20100202 - asimon - changes for filtering, BUGID 2455, BUGID 3026
 *						added filter_by_* - functions, changed generateExecTree()
 *	20091212 - franciscom - prepareNode(), generateTestSpecTree() interface changes
 *                          added logic to do filtering on test spec for execution type
 *
 *	20090815 - franciscom - get_last_execution() call changes
 *  20090801 - franciscom - table prefix missed
 *	20090716 - franciscom - BUGID 2692
 * 	20090328 - franciscom - BUGID 2299 - introduced on 20090308.
 *                          Added logic to remove Empty Top level test suites 
 *                          (have neither test cases nor test suites inside) when applying 
 *                          test case keyword filtering.
 *                          BUGID 2296
 *  20090308 - franciscom - generateTestSpecTree() - changes for EXTJS tree
 *  20090211 - franciscom - BUGID 2094 
 *  20090202 - franciscom - minor changes to avoid BUGID 2009
 *  20090118 - franciscom - replaced multiple calls config_get('testcase_cfg')
 *                          added extjs_renderTestSpecTreeNodeOnOpen(), to allow filtering 
 *
 */
require_once(dirname(__FILE__)."/../../third_party/dBug/dBug.php");

/**
*	strip potential newlines and other unwanted chars from strings
*	Mainly for stripping out newlines, carriage returns, and quotes that were 
*	causing problems in javascript using jtree
*
*	@param string $str
*	@return string string with the newlines removed
*/
function filterString($str)
{
	$str = str_replace(array("\n","\r"), array("",""), $str);
	$str = addslashes($str);
	$str = htmlspecialchars($str, ENT_QUOTES);	
	
	return $str;
}

/** 
 * generate data for tree menu of Test Specification
 *
 * @param boolean $ignore_inactive_testcases if all test case versions are inactive, 
 *                            the test case will ignored.
 * @param array $exclude_branches map key=node_id
 * 
 * @internal Revisions:
 * 20100428 - asimon - BUGID 3301, added filtering by custom fields
 * 20090328 - franciscom - BUGID 2299, that was generated during 20090308 
 *                         trying to fix another not reported bug.
 * 20090308 - franciscom - changed arguments in str_ireplace() call
 *                         Due to bug in Test Spec tree when using Keywords filter
 * 20080501 - franciscom - keyword_id can be an array
 * 20071014 - franciscom - $bForPrinting
 *                         used to choose Javascript function 
 *                         to call when clicking on a tree node
 * 20070922 - franciscom - interface changes added $tplan_id,
 * 20070217 - franciscom - added $exclude_branches
 * 20061105 - franciscom - added $ignore_inactive_testcases
 */
 
 
function generateTestSpecTree(&$db,$tproject_id, $tproject_name,$linkto,$filters=null,$options=null)
{
	$tables = tlObjectWithDB::getDBTables(array('tcversions','nodes_hierarchy'));

	$my = array();
	
	// 20100412 - franciscom
	// $my['options'] = array('forPrinting' => 0, 'hideTestCases' => 0,'getArguments' => '', 
	//                        'tc_action_enabled' => 1, 'ignore_inactive_testcases' => 0, 
	//                        'exclude_branches' => null, 'viewType' => 'testSpecTree');
//	$my['options'] = array('forPrinting' => 0, 'hideTestCases' => 0, 
//	                       'tc_action_enabled' => 1, 'ignore_inactive_testcases' => 0, 
//	                       'exclude_branches' => null, 'viewType' => 'testSpecTree');
	$my['options'] = array('forPrinting' => 0, 'hideTestCases' => 0, 
	                       'tc_action_enabled' => 1, 'ignore_inactive_testcases' => 0, 
	                       'viewType' => 'testSpecTree');
	

	// 20100412 - franciscom
	// testplan => only used if opetions['viewType'] == 'testSpecTreeForTestPlan'
	// 20100417 - franciscom - BUGID 2498
	$my['filters'] = array('keywords' => null, 'executionType' => null, 'importance' => null,
	                       'testplan' => null);

	$my['options'] = array_merge($my['options'], (array)$options);
	$my['filters'] = array_merge($my['filters'], (array)$filters);
	
	$treeMenu = new stdClass(); 
	$treeMenu->rootnode = null;
	$treeMenu->menustring = '';
	
	$resultsCfg = config_get('results');
	$showTestCaseID = config_get('treemenu_show_testcase_id');
	$glueChar = config_get('testcase_cfg')->glue_character;
	$menustring = null;
	
	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;	
	
	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	// BUGID 3301
	$tsuite_node_type = $tree_manager->node_descr_id['testsuite'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
	$status_descr_code=$resultsCfg['status_code'];
	$status_code_descr=$resultsCfg['code_status'];
	
	$decoding_hash=array('node_id_descr' => $hash_id_descr,
		                 'status_descr_code' =>  $status_descr_code,
		                 'status_code_descr' =>  $status_code_descr);
	
	$exclude_branches = isset($filters['filter_toplevel_testsuite'])
	                    && is_array($filters['filter_toplevel_testsuite']) ?
	                    $filters['filter_toplevel_testsuite'] : null;
	
	$tcase_prefix=$tproject_mgr->getTestCasePrefix($tproject_id) . $glueChar;
	$test_spec = $tproject_mgr->get_subtree($tproject_id,testproject::RECURSIVE_MODE,
		                                    testproject::INCLUDE_TESTCASES, $exclude_branches);
	
	// Added root node for test specification -> testproject
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];
	
	$map_node_tccount=array();
	$tplan_tcs=null;
	
	DEFINE('DONT_FILTER_BY_TESTER',0);
	DEFINE('DONT_FILTER_BY_EXEC_STATUS',null);
	
	if($test_spec)
	{
		$tck_map = null;  // means no filter
		if(!is_null($my['filters']['filter_keywords']))
		{
			//$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,$my['filters']['keywords']->items,
			//                                              $my['filters']['keywords']->type);
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,
			           $my['filters']['filter_keywords'],
			           $my['filters']['filter_keywords_filter_type']);
			if( is_null($tck_map) )
			{
				$tck_map=array();  // means filter everything
			}
		}
		
		// Important: prepareNode() will make changes to $test_spec like filtering by test case 
		// keywords using $tck_map;
		$pnFilters = null;
	    
//		$pnFilters['filter_testcase_name'] = 
//			isset($my['filters']['filter_testcase_name']) ?
//			$my['filters']['filter_testcase_name'] : null;
//		
//		$pnFilters['filter_execution_type'] = 
//			isset($my['filters']['filter_execution_type']) ?
//			$my['filters']['filter_execution_type'] : null;
//		
//		$pnFilters['filter_priority'] = 
//			isset($my['filters']['filter_priority']) ?
//			$my['filters']['filter_priority'] : null;
		
		$keys2init = array('filter_testcase_name',
		                   'filter_execution_type',
		                   'filter_priority');
		
		foreach ($keys2init as $keyname) {
			$pnFilters[$keyname] = isset($my['filters'][$keyname]) ? $my['filters'][$keyname] : null;
		}
	    
	    $pnFilters['setting_testplan'] = 
	    	$my['filters']['setting_testplan'];
	    
	    // BUGID 3301 - added filtering by custom field values
	    if (isset($my['filters']['filter_custom_fields']) 
	    && isset($test_spec['childNodes'])) {
	    	
	    	$test_spec['childNodes'] = filter_by_cf_values($test_spec['childNodes'], 
			                           $my['filters']['filter_custom_fields'], 
			                           $db, $tsuite_node_type, $tcase_node_type);
	    }
		
	    // 20100412 - franciscom
	    $pnOptions = array('hideTestCases' => $my['options']['hideTestCases'], 
	    				   'viewType' => $my['options']['viewType'],	
		                   'ignoreInactiveTestCases' => $my['options']['ignore_inactive_testcases']);
		
		$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,$tck_map,
			                             $tplan_tcs,$pnFilters,$pnOptions);

		foreach($testcase_counters as $key => $value)
		{
			$test_spec[$key]=$testcase_counters[$key];
		}
		
		$menustring = renderTreeNode(1,$test_spec,$hash_id_descr,
			                         $my['options']['tc_action_enabled'],$linkto,$tcase_prefix,
			                         $my['options']['forPrinting'],$showTestCaseID);
	}
	
	$menustring ='';
	$treeMenu->rootnode = new stdClass();
	$treeMenu->rootnode->name = $test_spec['text'];
	$treeMenu->rootnode->id = $test_spec['id'];
	$treeMenu->rootnode->leaf = isset($test_spec['leaf']) ? $test_spec['leaf'] : false;
	$treeMenu->rootnode->text = $test_spec['text'];
	$treeMenu->rootnode->position = $test_spec['position'];	    
	$treeMenu->rootnode->href = $test_spec['href'];
	
	
	// Change key ('childNodes')  to the one required by Ext JS tree.
	if(isset($test_spec['childNodes']))
	{
		$menustring = str_ireplace('childNodes', 'children', json_encode($test_spec['childNodes'])); 
	}
	// 20090328 - franciscom - BUGID 2299
	// More details about problem found on 20090308 and fixed IN WRONG WAY
	// TPROJECT
	//    |______ TSA
	//            |__ TC1
	//            |__ TC2
	//    | 
	//    |______ TSB
	//            |______ TSC
	// 
	// Define Keyword K1,K2
	//
	// NO TEST CASE HAS KEYWORD ASSIGNED
	// Filter by K1
	// Tree will show root that spins Forever
	// menustring before str_ireplace : [null,null]
	// menustring AFTER [null] 
	//
	// Now fixed.
	//
	// Some minor fix to do
	// Il would be important exclude Top Level Test suites.
	// 
	// 
	// 20090308 - franciscom
	// Changed because found problem on:
	// Test Specification tree when applying Keyword filter using a keyword NOT PRESENT
	// in test cases => Tree root shows loading icon and spin never stops.
	//
	// Attention: do not know if in other situation this will generate a different bug
	// 
	if(!is_null($menustring))
	{
		// Remove null elements (Ext JS tree do not like it ).
		// :null happens on -> "children":null,"text" that must become "children":[],"text"
		// $menustring = str_ireplace(array(':null',',null','null,'),array(':[]','',''), $menustring); 
		$menustring = str_ireplace(array(':null',',null','null,','null'),array(':[]','','',''), $menustring); 
	}
	$treeMenu->menustring = $menustring; 
	
	return $treeMenu;
}


/**
 * Prepares a Node to be displayed in a navigation tree.
 * This function is used in the construction of:
 *  - Test project specification -> we want ALL test cases defined in test project.
 *  - Test execution             -> we only want the test cases linked to a test plan.
 * 
 * IMPORTANT:
 * when analising a container node (Test Suite) if it is empty and we have requested
 * some sort of filtering NODE WILL BE PRUNED.
 *
 *
 * status: one of the possible execution status of a test case.
 *
 *
 * tplan_tcases: map with testcase versions linked to test plan. 
 *               due to the multiples uses of this function, null has to meanings
 *
 *         		 When we want to build a Test Project specification tree,
 *         		 WE SET it to NULL, because we are not interested in a test plan.
 *         		 
 *         		 When we want to build a Test execution tree, we dont set it deliverately
 *         		 to null, but null can be the result of NO tcversion linked.
 *
 *
 * 20081220 - franciscom - status can be an array with multple values, to do OR search.
 *
 * 20071014 - franciscom - added version info fro test cases in return data structure.
 *
 * 20061105 - franciscom
 * ignore_inactive_testcases: useful when building a Test Project Specification tree 
 *                            to be used in the add/link test case to Test Plan.
 *
 *
 * 20061030 - franciscom
 * tck_map: Test Case Keyword map:
 *          null            => no filter
 *          empty map       => filter out ALL test case ALWAYS
 *          initialized map => filter out test case ONLY if NOT present in map.
 *
 *
 * added argument:
 *                $map_node_tccount
 *                key => node_id
 *                values => node test case count
 *                          node name (useful only for debug purpouses
 *
 *                IMPORTANT: this new argument is not useful for tree rendering
 *                           but to avoid duplicating logic to get test case count
 *
 *
 * return: map with keys:
 *         'total_count'
 *         'passed'  
 *         'failed'
 *         'blocked'
 *         'not run'
 *
 * @internal Revisions
 * 20100417 - franciscom -  BUGID 2498 - filter by test case importance
 * 20100415 - franciscom -  BUGID 2797 - filter by test case execution type
 */
function prepareNode(&$db,&$node,&$decoding_info,&$map_node_tccount,$tck_map = null,
                     $tplan_tcases = null,$filters=null, $options=null)
{
	
	static $hash_id_descr;
	static $status_descr_code;
	static $status_code_descr;
	static $debugMsg;
    static $tables;
    static $my;
    static $enabledFiltersOn;
    static $activeVersionClause;
    static $filterOnTCVersionAttribute;
    static $filtersApplied;

    $tpNode = null;
	if (!$tables)
	{
  	    $debugMsg = 'Class: ' . __CLASS__ . ' - ' . 'Method: ' . __FUNCTION__ . ' - ';
        $tables = tlObjectWithDB::getDBTables(array('tcversions','nodes_hierarchy','testplan_tcversions'));
    }	
	if (!$hash_id_descr)
	{
		$hash_id_descr = $decoding_info['node_id_descr'];
	}
	if (!$status_descr_code)
	{
		$status_descr_code = $decoding_info['status_descr_code'];
	}
	if (!$status_code_descr)
	{
		$status_code_descr = $decoding_info['status_code_descr'];
	}
	
	if (!$my)
	{
		$my = array();
		$my['options'] = array('hideTestCases' => 0, 'showTestCaseID' => 1, 'viewType' => 'testSpecTree',
		                       'getExternalTestCaseID' => 1,'ignoreInactiveTestCases' => 0);

		// asimon - added importance here because of "undefined" error in event log
		$my['filters'] = array('status' => null, 'assignedTo' => null, 
		                       'importance' => null, 'executionType' => null);
		
		$my['options'] = array_merge($my['options'], (array)$options);
		$my['filters'] = array_merge($my['filters'], (array)$filters);
	}
	
	if(!$enabledFiltersOn)
	{
		// 3301 - added filtering by testcase name
		// 20100702 - and custom fields
		$enabledFiltersOn['testcase_name'] = 
			isset($my['filters']['filter_testcase_name']);
		$enabledFiltersOn['keywords'] = !is_null($tck_map);
		$enabledFiltersOn['executionType'] = 
			!is_null($my['filters']['filter_execution_type']);
		$enabledFiltersOn['importance'] = !is_null($my['filters']['filter_priority']);
		$enabledFiltersOn['custom_fields'] = isset($my['filters']['filter_custom_fields']);
		$filterOnTCVersionAttribute = $enabledFiltersOn['executionType'] || $enabledFiltersOn['importance'];
					
		$filtersApplied = false;
		foreach($enabledFiltersOn as $filterValue)
		{
			$filtersApplied = $filtersApplied || $filterValue; 
		}
		
		$activeVersionClause = $filterOnTCVersionAttribute ? " AND TCV.active=1 " : '';
	}
		
	$tcase_counters = array('testcase_count' => 0);
	foreach($status_descr_code as $status_descr => $status_code)
	{
		$tcase_counters[$status_descr]=0;
	}
	
	$node_type = isset($node['node_type_id']) ? $hash_id_descr[$node['node_type_id']] : null;
	$tcase_counters['testcase_count']=0;

	if($node_type == 'testcase')
	{
		$viewType = $my['options']['viewType'];
		if ($enabledFiltersOn['keywords'])
		{
			if (!isset($tck_map[$node['id']]))
			{
				$node = null;
			}	
		}
		
		// added filter by testcase name
		if ($node && $enabledFiltersOn['testcase_name']) {
			$filter_name = $my['filters']['filter_testcase_name'];
			// IMPORTANT:
			// checking with === here, because function stripos could return 0 when string
			// is found at position 0, if clause would then evaluate wrong because 
			// 0 would be casted to false and we only want to delete node if it really is false
			if (stripos($node['name'], $filter_name) === FALSE) {
				$node = null;
			}
		}
		
		if ($node && $viewType == 'executionTree')
		{
			
			$tpNode = isset($tplan_tcases[$node['id']]) ? $tplan_tcases[$node['id']] : null;
//			if (!$tpNode || (!is_null($my['filters']['assignedTo'])) && 
//					((isset($my['filters']['assignedTo'][TL_USER_NOBODY]) && !is_null($tpNode['user_id'])) ||
//							(!isset($my['filters']['assignedTo'][TL_USER_NOBODY]) && (!isset($my['filters']['assignedTo'][TL_USER_SOMEBODY])) && 
//							 !isset($my['filters']['assignedTo'][$tpNode['user_id']]))) || 
//							(!is_null($my['filters']['status']) && !isset($my['filters']['status'][$tpNode['exec_status']])) ||
//							(isset($my['filters']['assignedTo'][TL_USER_SOMEBODY]) && !is_numeric($tpNode['user_id']))
//			)
//			{

			// asimon - additional variables for better readability of following if condition.
			// For original statement/condition see above commented out lines.
			// This is longer, but MUCH better readable and easier to extend for new filter conditions.
			
			$users2filter = isset($my['filters']['filter_assigned_user']) ?
			                      $my['filters']['filter_assigned_user'] : null;
			$results2filter = isset($my['filters']['filter_result_result']) ?
			                        $my['filters']['filter_result_result'] : null;
			
			$wrong_result = !is_null($results2filter) 
			                && !isset($results2filter[$tpNode['exec_status']]);
			
			$somebody_wanted_but_nobody_there = !is_null($users2filter)
			                                    && isset($users2filter[TL_USER_SOMEBODY])
			                                    && !is_numeric($tpNode['user_id']);
			
			$unassigned_wanted_but_someone_assigned = !is_null($users2filter)
			                                          && isset($users2filter[TL_USER_NOBODY])
			                                          && !is_null($tpNode['user_id']);
			
			$wrong_user = !is_null($users2filter)
			              && !isset($users2filter[TL_USER_NOBODY])
			              && !isset($users2filter[TL_USER_SOMEBODY])
			              && !isset($users2filter[$tpNode['user_id']]);
						
			$delete_node = $unassigned_wanted_but_someone_assigned
			               || $wrong_user
			               || $wrong_result
			               || $somebody_wanted_but_nobody_there;
			
			if (!$tpNode || $delete_node) {
				$node = null;
			} else {
				$externalID='';
				$node['tcversion_id'] = $tpNode['tcversion_id'];		
				$node['version'] = $tpNode['version'];		
				if ($my['options']['getExternalTestCaseID'])
				{
					if (!isset($tpNode['external_id']))
					{
						$sql = " /* $debugMsg - line:" . __LINE__ . " */ " . 
						       " SELECT TCV.tc_external_id AS external_id " .
							   " FROM {$tables['tcversions']}  TCV " .
							   " WHERE TCV.id=" . $node['tcversion_id'];
						
						$result = $db->exec_query($sql);
						$myrow = $db->fetch_array($result);
						$externalID = $myrow['external_id'];
					}
					else
					{
						$externalID = $tpNode['external_id'];
					}	
				}
				$node['external_id'] = $externalID;
				unset($tplan_tcases[$node['id']]);
			}
		}
		
		if ($node && $my['options']['ignoreInactiveTestCases'])
		{
			// there are active tcversions for this node ???
			// I'm doing this instead of creating a test case manager object, because
			// I think is better for performance.
			//
			// =======================================================================================
			// 20070106 - franciscom
			// Postgres Problems
			// =======================================================================================
			// Problem 1 - SQL Syntax
			//   While testing with postgres
			//   SELECT count(TCV.id) NUM_ACTIVE_VERSIONS   -> Error
			//
			//   At least for what I remember using AS to create COLUMN ALIAS IS REQUIRED and Standard
			//   while AS is NOT REQUIRED (and with some DBMS causes errors) when you want to give a 
			//   TABLE ALIAS
			//
			// Problem 2 - alias case
			//   At least in my installation the aliases column name is returned lower case, then
			//   PHP fails when:
			//                  if($myrow['NUM_ACTIVE_VERSIONS'] == 0)
			//
			//
			$sql=" /* $debugMsg - line:" . __LINE__ . " */ " . 
			     " SELECT count(TCV.id) AS num_active_versions " .
				 " FROM {$tables['tcversions']} TCV, {$tables['nodes_hierarchy']} NH " .
				 " WHERE NH.parent_id=" . $node['id'] .
				 " AND NH.id = TCV.id AND TCV.active=1";
			
			$result = $db->exec_query($sql);
			$myrow = $db->fetch_array($result);
			if($myrow['num_active_versions'] == 0)
			{
				$node = null;
			}
		}
		
		// -------------------------------------------------------------------
		if ($node && ($viewType=='testSpecTree' || $viewType=='testSpecTreeForTestPlan') )
		{
			$sql = " /* $debugMsg - line:" . __LINE__ . " */ " . 
			       " SELECT COALESCE(MAX(TCV.id),0) AS targetid, TCV.tc_external_id AS external_id" .
				   " FROM {$tables['tcversions']} TCV, {$tables['nodes_hierarchy']} NH " .
				   " WHERE  NH.id = TCV.id {$activeVersionClause} AND NH.parent_id={$node['id']} " .
				   " GROUP BY TCV.tc_external_id ";
			   
			$rs = $db->get_recordset($sql);
			if( is_null($rs) )
			{
				$node = null;
			}
			else
			{	
			    $node['external_id'] = $rs[0]['external_id'];
			    $target_id = $rs[0]['targetid'];
				
				if( $filterOnTCVersionAttribute )
				{
					// BUGID 2797 
					switch ($viewType)
					{
						case 'testSpecTreeForTestPlan':
							// Try to get info from linked tcversions
							// Platform is not needed
							$sql = " /* $debugMsg - line:" . __LINE__ . " */ " . 
								   " SELECT DISTINCT TPTCV.tcversion_id AS targetid " .
								   " FROM {$tables['tcversions']} TCV " .
								   " JOIN {$tables['nodes_hierarchy']} NH " .
								   " ON NH.id = TCV.id {$activeVersionClause} " .
								   " AND NH.parent_id={$node['id']} " .
								   " JOIN {$tables['testplan_tcversions']} TPTCV " .
								   " ON TPTCV.tcversion_id = TCV.id " .
								   " AND TPTCV.testplan_id = " . 
							       " {$my['filters']['setting_testplan']}";
			    			$rs = $db->get_recordset($sql);
							$target_id = !is_null($rs) ? $rs[0]['targetid'] : $target_id;
						break;
					}		
					
					$sql = " /* $debugMsg - line:" . __LINE__ . " */ " . 
						   " SELECT TCV.execution_type " .
						   " FROM {$tables['tcversions']} TCV " .
						   " WHERE TCV.id = {$target_id} ";
					 	   
					if( $enabledFiltersOn['executionType'] )
					{
						$sql .= " AND TCV.execution_type = " .
						        " {$my['filters']['filter_execution_type']} ";
					}
					
					if( $enabledFiltersOn['importance'] )
					{
						$sql .= " AND TCV.importance = " .
						        " {$my['filters']['filter_priority']} ";
					}
					
			    	$rs = $db->fetchRowsIntoMap($sql,'execution_type');
			    	if(is_null($rs))
			    	{
			    		$node = null;
			    	}
			    }
			} 
            if( !is_null($node) )
            {
				// needed to avoid problems when using json_encode with EXTJS
				unset($node['childNodes']);
				$node['leaf']=true;
			}
		}
		// -------------------------------------------------------------------
		
		
		foreach($tcase_counters as $key => $value)
		{
			$tcase_counters[$key]=0;
		}
		if(isset($tpNode['exec_status']) )
		{
			$tc_status_code = $tpNode['exec_status'];
			$tc_status_descr = $status_code_descr[$tc_status_code];   
		}
		else
		{
			$tc_status_descr = "not_run";
			$tc_status_code = $status_descr_code[$tc_status_descr];
		}
		
		$init_value = $node ? 1 : 0;
		$tcase_counters[$tc_status_descr]=$init_value;
		$tcase_counters['testcase_count']=$init_value;
		if ( $my['options']['hideTestCases'] )
		{
			$node = null;
		} 
	}  // if($node_type == 'testcase')
	
	if (isset($node['childNodes']) && is_array($node['childNodes']))
	{
		// node has to be a Test Suite ?
		$childNodes = &$node['childNodes'];
		$childNodesQty = count($childNodes);
		
		for($idx = 0;$idx < $childNodesQty ;$idx++)
		{
			$current = &$childNodes[$idx];
			// I use set an element to null to filter out leaf menu items
			
			if(is_null($current))
			{
				continue;
			}
			
			$counters_map = prepareNode($db,$current,$decoding_info,$map_node_tccount,
				                        $tck_map,$tplan_tcases,$my['filters'],$my['options']);
			foreach($counters_map as $key => $value)
			{
				$tcase_counters[$key] += $counters_map[$key];   
			}  
		}
		foreach($tcase_counters as $key => $value)
		{
			$node[$key] = $tcase_counters[$key];
		}  
		
		if (isset($node['id']))
		{
			$map_node_tccount[$node['id']] = array(	'testcount' => $node['testcase_count'],
				                                    'name' => $node['name']);
		}

        // node must be dstroyed if empty had we have using filtering conditions
		if( ($filtersApplied || !is_null($tplan_tcases)) && 
			!$tcase_counters['testcase_count'] && ($node_type != 'testproject'))
		{
			$node = null;
		}
	}
	else if ($node_type == 'testsuite')
	{
		// does this means is an empty test suite ??? - franciscom 20080328
		$map_node_tccount[$node['id']] = array(	'testcount' => 0,'name' => $node['name']);
		
        // If is an EMPTY Test suite and we have added filtering conditions,
        // We will destroy it.
		if ($filtersApplied || !is_null($tplan_tcases) )
		{
			$node = null;
		}	
	}

	return $tcase_counters;
}


/**
 * Create the string representation suitable to create a graphic visualization
 * of a node, for the type of menu selected.
 *
 * @internal Revisions
 * 20100611 - franciscom - removed useless $getArguments
 */
function renderTreeNode($level,&$node,$hash_id_descr,
                        $tc_action_enabled,$linkto,$testCasePrefix,
                        $bForPrinting=0,$showTestCaseID)
{
	$menustring='';
	$node_type = $hash_id_descr[$node['node_type_id']];
	extjs_renderTestSpecTreeNodeOnOpen($node,$node_type,$tc_action_enabled,$bForPrinting,
		                               $showTestCaseID,$testCasePrefix);
	
	
	if (isset($node['childNodes']) && $node['childNodes'])
	{
		// 20090118 - franciscom - need to work always original object
		//                         in order to change it's values using reference .
		// Can not assign anymore to intermediate variables.
		//
		$nChildren = sizeof($node['childNodes']);
		for($idx = 0;$idx < $nChildren;$idx++)
		{
			// asimon - replaced is_null by !isset because of warnings in event log
			if(!isset($node['childNodes'][$idx]))
			//if(is_null($node['childNodes'][$idx]))
			{
				continue;
			}
			$menustring .= renderTreeNode($level+1,$node['childNodes'][$idx],$hash_id_descr,
				                          $tc_action_enabled,$linkto,$testCasePrefix,
				                          $bForPrinting,$showTestCaseID);
		}
	}
	
	return $menustring;
}


/** 
 * Creates data for tree menu used on :
 * - Execution of Test Cases
 * - Remove Test cases from test plan
 * 
 * @internal Revisions:
 *  20100719 - asimon - BUGID 3406 - user assignments per build:
 *                                   filter assigned test cases by setting_build
 *	20080617 - franciscom - return type changed to use extjs tree component
 *	20080305 - franciscom - interface refactoring
 *	20080224 - franciscom - added include_unassigned
 *	20071002 - jbarchibald - BUGID 1051 - added cf element to parameter list
 *	20070204 - franciscom - changed $bForPrinting -> $bHideTCs
 */
function generateExecTree(&$db,&$menuUrl,$tproject_id,$tproject_name,$tplan_id,
                          $tplan_name,$filters,$additionalInfo) 
{
	$treeMenu = new stdClass(); 
	$treeMenu->rootnode = null;
	$treeMenu->menustring = '';
	$resultsCfg = config_get('results');
	$showTestCaseID = config_get('treemenu_show_testcase_id');
	$glueChar=config_get('testcase_cfg')->glue_character;
	
	$menustring = null;
	$any_exec_status = null;
	$tplan_tcases = null;
	$tck_map = null;
    $idx=0;
    $testCaseQty=0;
    $testCaseSet=null;
    	
	$keyword_id = 0;
	$keywordsFilterType = 'Or';
	if (property_exists($filters, 'filter_keywords')
	&& !is_null($filters->{'filter_keywords'})) {
		$keyword_id = $filters->{'filter_keywords'};
		$keywordsFilterType = $filters->{'filter_keywords_filter_type'};
	}

	// 3406 - user assignments per build
	// can't use $build_id here because we need the build ID from settings panel
	$build2filter_assignments = isset($filters->{'setting_build'}) ? $filters->{'setting_build'} : 0;
	
	
	$tc_id = isset($filters->tc_id) ? $filters->tc_id : null; 
	$build_id = isset($filters->filter_result_build) ? $filters->filter_result_build : null;
	$bHideTCs = isset($filters->hide_testcases) ? $filters->hide_testcases : false;
	$assignedTo = isset($filters->filter_assigned_user) ? $filters->filter_assigned_user : null; 
	$include_unassigned = isset($filters->filter_assigned_user_include_unassigned) ?
	                      $filters->filter_assigned_user_include_unassigned : false;
	$setting_platform = isset($filters->setting_platform) ? $filters->setting_platform : null;
	$execution_type = isset($filters->filter_execution_type) ? $filters->filter_execution_type : null;
	$status = isset($filters->filter_result_result) ? $filters->filter_result_result : null;
	$cf_hash = isset($filters->filter_custom_fields) ? $filters->filter_custom_fields : null;
	$show_testsuite_contents = isset($filters->show_testsuite_contents) ? 
	                           $filters->show_testsuite_contents : true;
	$urgencyImportance = isset($filters->filter_priority) ?
	                     $filters->filter_priority : null;
	
	$useCounters=isset($additionalInfo->useCounters) ? $additionalInfo->useCounters : null;
	$useColors=isset($additionalInfo->useColours) ? $additionalInfo->useColours : null;
	$colorBySelectedBuild = isset($additionalInfo->testcases_colouring_by_selected_build) ? 
	                        $additionalInfo->testcases_colouring_by_selected_build : null;

	$tplan_mgr = new testplan($db);
	$tproject_mgr = new testproject($db);
	$tcase_mgr = new testcase($db);
	
	
	$tree_manager = $tplan_mgr->tree_manager;
	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	
	$hash_id_descr = array_flip($hash_descr_id);	    
	$decoding_hash = array('node_id_descr' => $hash_id_descr,
		                   'status_descr_code' =>  $resultsCfg['status_code'],
		                   'status_code_descr' =>  $resultsCfg['code_status']);
	
	$tcase_prefix = $tproject_mgr->getTestCasePrefix($tproject_id) . $glueChar;
	
	$nt2exclude = array('testplan' => 'exclude_me',
		                'requirement_spec'=> 'exclude_me',
		                'requirement'=> 'exclude_me');
	
	$nt2exclude_children = array('testcase' => 'exclude_my_children',
		                         'requirement_spec'=> 'exclude_my_children');
	
  	$my['options']=array('recursive' => true,
  	                     'order_cfg' => array("type" =>'exec_order',"tplan_id" => $tplan_id));
 	$my['filters'] = array('exclude_node_types' => $nt2exclude,
 	                       'exclude_children_of' => $nt2exclude_children);
	
 	// 3301 - added for filtering by toplevel testsuite
 	if (isset($filters->{'filter_toplevel_testsuite'})
 	&& is_array($filters->{'filter_toplevel_testsuite'})) {
 		$my['filters']['exclude_branches'] = 
 			$filters->{'filter_toplevel_testsuite'};
 	}
 	
	// $order_cfg = array("type" =>'exec_order',"tplan_id" => $tplan_id);
    // $test_spec = $tree_manager->get_subtree($tproject_id,$nt2exclude,$nt2exclude_children,
	// 	                                    null,'',RECURSIVE_MODE,$order_cfg);
	// 
    $test_spec = $tree_manager->get_subtree($tproject_id,$my['filters'],$my['options']);
     
	$test_spec['name'] = $tproject_name . " / " . $tplan_name;  // To be discussed
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];
	$map_node_tccount = array();
	
	$tplan_tcases = null;
    $apply_other_filters=true;
    
	if($test_spec)
	{
		if(is_null($tc_id) || $tc_id >= 0)
		{
			$doFilterByKeyword = (!is_null($keyword_id) && $keyword_id > 0);
			if($doFilterByKeyword)
			{
				$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,$keyword_id,$keywordsFilterType);
			}
			
			// Multiple step algoritm to apply keyword filter on type=AND
			// get_linked_tcversions filters by keyword ALWAYS in OR mode.
			// 20100520 - franciscom
			// 3406: user assignments per build
//			$opt = array('include_unassigned' => $include_unassigned, 
//			             'steps_info' => false);
			$opt = array('include_unassigned' => $include_unassigned, 
			             'steps_info' => false,
			             'user_assignments_per_build' => $build2filter_assignments);

			// 20100417 - BUGID 3380 - execution type
			$linkedFilters = array('tcase_id' => $tc_id, 'keyword_id' => $keyword_id,
                                   'assigned_to' => $assignedTo,
                                   'cf_hash' =>  $cf_hash,
                                   'platform_id' => $setting_platform,
                                   'urgencyImportance' => $urgencyImportance,
                                   'exec_type' => $execution_type);
			
			$tplan_tcases = $tplan_mgr->get_linked_tcversions($tplan_id,$linkedFilters,$opt);
			
			if($tplan_tcases && $doFilterByKeyword && $keywordsFilterType == 'AND')
			{
				$filteredSet = $tcase_mgr->filterByKeyword(array_keys($tplan_tcases),$keyword_id,$keywordsFilterType);

				// CAUTION: if $filteredSet is null,
				// then get_linked_tcversions() thinks there are just no filters set,
				// but really there are no testcases which match the wanted keyword criteria,
				// so we have to set $tplan_tcases to null because there is no more filtering necessary
				if ($filteredSet != null) {
					$linkedFilters = array('tcase_id' => array_keys($filteredSet));
					$tplan_tcases = $tplan_mgr->get_linked_tcversions($tplan_id,$linkedFilters);
				} else {
					$tplan_tcases = null;
				}
			}
		}   
		
		if (is_null($tplan_tcases))
		{
			$tplan_tcases = array();
			$apply_other_filters=false;
		}
		
		$filter_methods = config_get('execution_filter_methods');
		$method_key = 'filter_result_method';
		$result_key = 'filter_result_result';
				
		if( $apply_other_filters && property_exists($filters,$method_key)
		&& !is_null($filters->{$method_key}) 
		&& $filter_methods['status_code']['any_build'] == $filters->{$method_key}
		&& !in_array($resultsCfg['status_code']['all'],(array)$filters->{$result_key})
		&& !is_null($filters->{$result_key})) {
			if (in_array($resultsCfg['status_code']['not_run'], (array)$filters->{$result_key})) {
				$tplan_tcases = filter_not_run_for_any_build($tplan_mgr, $tplan_tcases, $tplan_id, $filters);
			} else {
				$tplan_tcases = filter_by_status_for_any_build($tplan_mgr, $tplan_tcases, $tplan_id, $filters);
			}
			if (is_null($tplan_tcases)) {
				$tplan_tcases = array();
				$apply_other_filters=false;
			}
		}
		
		if( $apply_other_filters && property_exists($filters,$method_key)
		&& !is_null($filters->{$method_key})
		&& $filter_methods['status_code']['all_builds'] == $filters->{$method_key}
		&& !in_array($resultsCfg['status_code']['all'],(array)$filters->{$result_key})
		&& !is_null($filters->{$result_key})) {
			$tplan_tcases = filter_by_same_status_for_all_builds($tplan_mgr, $tplan_tcases, $tplan_id, $filters);
			if (is_null($tplan_tcases)) {
				$tplan_tcases = array();
				$apply_other_filters=false;
			}
		}		
		
		if( $apply_other_filters && property_exists($filters,$method_key)
		&& !is_null($filters->{$method_key})
		&& $filter_methods['status_code']['specific_build'] == $filters->{$method_key}
		&& !in_array($resultsCfg['status_code']['all'],(array)$filters->{$result_key})
		&& !is_null($filters->{$result_key})) {
			$tplan_tcases = filter_by_status_for_build($tplan_mgr, $tplan_tcases, $tplan_id, $filters);
			if (is_null($tplan_tcases)) {
				$tplan_tcases = array();
				$apply_other_filters=false;
			}
		}
		
		if( $apply_other_filters && property_exists($filters,$method_key)
		&& !is_null($filters->{$method_key})
		&& $filter_methods['status_code']['current_build'] == $filters->{$method_key}
		&& !in_array($resultsCfg['status_code']['all'],(array)$filters->{$result_key})
		&& !is_null($filters->{$result_key})) {
			// set build id to filter with to build chosen for execution
			$filters->{'filter_result_build'} = 
			$filters->{'setting_build'};
			
			$tplan_tcases = filter_by_status_for_build($tplan_mgr, $tplan_tcases, $tplan_id, $filters);
			if (is_null($tplan_tcases)) {
				$tplan_tcases = array();
				$apply_other_filters=false;
			}
		}

		if( $apply_other_filters && property_exists($filters,$method_key)
		&& !is_null($filters->{$method_key})
		&& $filter_methods['status_code']['latest_execution'] == $filters->{$method_key}
		&& !in_array($resultsCfg['status_code']['all'],(array)$filters->{$result_key})
		&& !is_null($filters->{$result_key})) {
			$tplan_tcases = filter_by_status_for_last_execution($db, $tplan_mgr, $tplan_tcases, $tplan_id, $filters);
			if (is_null($tplan_tcases)) {
				$tplan_tcases = array();
				$apply_other_filters=false;
			}
		}

		
		// BUGID 3450 - Change colors/counters in exec tree.
		// Means: replace exec status in filtered array $tplan_tcases 
		// by the one of last execution of selected build.
		// Since this changes exec status, replacing is done after filtering by status.
		// It has to be done before call to prepareNode() though,
		// because that one sets the counters according to status.
		if ($useColors && $colorBySelectedBuild) {
			foreach ($tplan_tcases as $id => $info) {
				$tables = tlObject::getDBTables('executions');
				
				// get last execution result for selected build
				$sql = " SELECT status FROM {$tables['executions']} E " .
					   " WHERE tcversion_id = {$info['tcversion_id']} " .
				       " AND testplan_id = {$tplan_id} " .
					   " AND platform_id = {$info['platform_id']} " .
					   " AND build_id = {$filters->setting_build} " .
					   " ORDER BY execution_ts DESC limit 1 ";
				
				$result = null;
				$result = $db->fetchOneValue($sql);
				
				if (is_null($result)) {
					// if no value can be loaded it has to be set to not run
					$result = $resultsCfg['status_code']['not_run'];
				}
				
				if ($result != $info['exec_status']) {
					$tplan_tcases[$id]['exec_status'] = $result;
				}
			}
		}
		
		
		// 20080224 - franciscom - 
		// After reviewing code, seems that assignedTo has no sense because tp_tcs
		// has been filtered.
		// Then to avoid changes to prepareNode() due to include_unassigned,
		// seems enough to set assignedTo to 0, if include_unassigned==true
		// $assignedTo = $include_unassigned ? 0 : $assignedTo;
		$assignedTo = $include_unassigned ? 
		              null : $assignedTo;
		
		$pnFilters = array('assignedTo' => $assignedTo);
		
//		$pnFilters['filter_testcase_name'] = 
//			isset($filters->{'filter_testcase_name'}) ?
//			$filters->{'filter_testcase_name'} : null;
//			
//		$pnFilters['filter_execution_type'] = 
//			isset($filters->{'filter_execution_type'}) ?
//			$filters->{'filter_execution_type'} : null;
//		
//		$pnFilters['filter_priority'] = 
//			isset($filters->{'filter_priority'}) ?
//			$filters->{'filter_priority'} : null;
		
		$keys2init = array('filter_testcase_name',
		                   'filter_execution_type',
		                   'filter_priority');
		
		foreach ($keys2init as $keyname) {
			$pnFilters[$keyname] = isset($filters->{$keyname}) ? $filters->{$keyname} : null;
		}
	    		
		// 20100412 - franciscom
		$pnOptions = array('hideTestCases' => $bHideTCs, 'viewType' => 'executionTree');
		$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
		                                 $tck_map,$tplan_tcases,$pnFilters,$pnOptions);

		foreach($testcase_counters as $key => $value)
		{
			$test_spec[$key] = $testcase_counters[$key];
		}
	
		
		// 3516
		// can now be left in form of array, will not be sent by URL anymore
		//$keys = implode(array_keys($tplan_tcases), ",");
		$keys = array_keys($tplan_tcases);
		
		// $getArguments .= "&show_only_tcs=" . $keys;
		
		$menustring = renderExecTreeNode(1,$test_spec,$tplan_tcases,
			                             $hash_id_descr,1,$menuUrl,$bHideTCs,$useCounters,$useColors,
			                             $showTestCaseID,$tcase_prefix,$show_testsuite_contents);
        
	}  // if($test_spec)
	
		
	$treeMenu->rootnode=new stdClass();
	$treeMenu->rootnode->name=$test_spec['text'];
	$treeMenu->rootnode->id=$test_spec['id'];
	$treeMenu->rootnode->leaf=$test_spec['leaf'];
	$treeMenu->rootnode->text=$test_spec['text'];
	$treeMenu->rootnode->position=$test_spec['position'];	    
	$treeMenu->rootnode->href=$test_spec['href'];
	
	if( !is_null($menustring) )
	{  
		// Change key ('childNodes')  to the one required by Ext JS tree.
		$menustring = str_ireplace('childNodes', 'children', json_encode($test_spec['childNodes']));
		
		// Remove null elements (Ext JS tree do not like it ).
		// :null happens on -> "children":null,"text" that must become "children":[],"text"
		// $menustring = str_ireplace(array(':null',',null','null,'),array(':[]','',''), $menustring); 
		$menustring = str_ireplace(array(':null',',null','null,','null'),array(':[]','','',''), $menustring); 
	}  
	
	$treeMenu->menustring = $menustring;
	
	return array($treeMenu, $keys);
}


/**
 * ???
 * 
 * @param integer $level
 * @param array &$node reference to recursive map
 * @param array &$tcases_map reference to map that contains info about testcase exec status
 *              when node is of testcase type.
 * @param boolean $bHideTCs 1 -> hide testcase
 * 
 * @return datatype description
 * 
 * @internal Revisions:
 *	20100611 - franciscom - removed useless $getArguments
 *	20071229 - franciscom -added $useCounters,$useColors
 */
function renderExecTreeNode($level,&$node,&$tcase_node,$hash_id_descr,
                            $tc_action_enabled,$linkto,$bHideTCs,$useCounters,$useColors,
                            $showTestCaseID,$testCasePrefix,$showTestSuiteContents)
{
	$node_type = $hash_id_descr[$node['node_type_id']];
	$menustring = '';
    extjs_renderExecTreeNodeOnOpen($node,$node_type,$tcase_node,$tc_action_enabled,$bHideTCs,
                                   $useCounters,$useColors,$showTestCaseID,$testCasePrefix,
                                   $showTestSuiteContents);
	
	
	if( isset($tcase_node[$node['id']]) )
	{
		unset($tcase_node[$node['id']]);
	}
	if (isset($node['childNodes']) && $node['childNodes'])
	{
	    // 20080615 - franciscom - need to work always original object
	    //                         in order to change it's values using reference .
	    // Can not assign anymore to intermediate variables.
        $nodes_qty = sizeof($node['childNodes']);
		for($idx = 0;$idx <$nodes_qty ;$idx++)
		{
			if(is_null($node['childNodes'][$idx]))
			{
				continue;
			}
			$menustring .= renderExecTreeNode($level+1,$node['childNodes'][$idx],$tcase_node,
			                                  $hash_id_descr,
			                                  $tc_action_enabled,$linkto,$bHideTCs,
			                                  $useCounters,$useColors,$showTestCaseID,
			                                  $testCasePrefix,$showTestSuiteContents);
		}
	}
	return $menustring;
}


/**
 * @return array a map:
 *         key    => node_id
 *         values => node test case count considering test cases presents
 *                   in the nodes of the subtree that starts on node_id
 *                   Means test case can not be sons/daughters of node_id.
 *                   node name (useful only for debug purpouses).
 */
function get_testproject_nodes_testcount(&$db,$tproject_id, $tproject_name,
                                         $keywordsFilter=null)
{
	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;

	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);

	$resultsCfg = config_get('results');
	// $status_descr_code = $resultsCfg['status_code'];
	// $status_code_descr = $resultsCfg['code_status'];
  
	$decoding_hash = array('node_id_descr' => $hash_id_descr,
                       'status_descr_code' =>  $resultsCfg['status_code'],
                       'status_code_descr' =>  $resultsCfg['code_status']);
	
	$test_spec = $tproject_mgr->get_subtree($tproject_id,RECURSIVE_MODE);
	
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = 1;
	
	$map_node_tccount = array(); 
	$tplan_tcases = null;
	
	if($test_spec)
	{
		$tck_map = null;
		if( !is_null($keywordsFilter) )
		{
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,
			                                              $keywordsFilter->items,$keywordsFilter->type);
		}	
		
		//@TODO: schlundus, can we speed up with NO_EXTERNAL?
		$filters = null;
		
		// 20100412 - franciscon
 	    $options = array('hideTestCases' => 0, 'viewType' => 'testSpecTree');
		$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
		                                 $tck_map,$tplan_tcases,$filters,$options);
		$test_spec['testcase_count'] = $testcase_counters['testcase_count'];
	}

	return $map_node_tccount;
}


/**
 * @return array a map:
 *         key    => node_id
 *         values => node test case count considering test cases presents
 *                   in the nodes of the subtree that starts on node_id
 *                   Means test case can not be sons/daughters of node_id.
 * 
 *                   node name (useful only for debug purpouses).
 */
function get_testplan_nodes_testcount(&$db,$tproject_id, $tproject_name,
                                      $tplan_id,$tplan_name,$keywordsFilter=null)
{
	$tplan_mgr = new testplan($db);
	$tproject_mgr = new testproject($db);
	
	$tree_manager = $tplan_mgr->tree_manager;
	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
	
	$resultsCfg=config_get('results');
	$decoding_hash=array('node_id_descr' => $hash_id_descr,
		'status_descr_code' =>  $resultsCfg['status_code'],
		'status_code_descr' =>  $resultsCfg['code_status']);
	
	$test_spec = $tproject_mgr->get_subtree($tproject_id,RECURSIVE_MODE);
	
	$linkedFilters = array('keyword_id' => $keywordsFilter->items);
	$tplan_tcases = $tplan_mgr->get_linked_tcversions($tplan_id,$linkedFilters);
	if (is_null($tplan_tcases))
	{
		$tplan_tcases = array();
	}
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];
	$map_node_tccount=array(); 
	
	if($test_spec)
	{
		$tck_map = null;
		
		if(!is_null($keywordsFilter))
		{
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,
				$keywordsFilter->items,$keywordsFilter->type);
		}	
		//@TODO: schlundus, can we speed up with NO_EXTERNAL?
		$filters = null;
		$options = array('hideTestCases' => 0, 'viewType' => 'executionTree');
		$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
			                             $tck_map,$tplan_tcases,$filters,$options);
		
		$test_spec['testcase_count'] = $testcase_counters['testcase_count'];
	}
	
	return($map_node_tccount);
}


function create_counters_info(&$node,$useColors)
{
	$resultsCfg=config_get('results');
	
	// I will add not_run if not exists
	$keys2display=array('not_run' => 'not_run');
	
	foreach($resultsCfg['status_label_for_exec_ui'] as $key => $value)
	{
		if( $key != 'not_run')
		{
			$keys2display[$key]=$key;  
		}  
	}
	$status_verbose=$resultsCfg['status_label'];
	
	$add_html='';
	foreach($keys2display as $key => $value)
	{
		if( isset($node[$key]) )
		{
			$css_class= $useColors ? (" class=\"light_{$key}\" ") : '';   
			$add_html .= "<span {$css_class} " . ' title="' . lang_get($status_verbose[$key]) . '">' . 
				         $node[$key] . ",</span>";
		}
	}
	$add_html = "(" . rtrim($add_html,",</span>") . "</span>)"; 
	
	return $add_html;
}


/**
 * VERY IMPORTANT: node must be passed BY REFERENCE
 * 
 * @internal Revisions:
 *	20080629 - franciscom - fixed bug missing argument for call to ST
 */
function extjs_renderExecTreeNodeOnOpen(&$node,$node_type,$tcase_node,$tc_action_enabled,
                                        $bForPrinting,$useCounters=1,$useColors=null,
                                        $showTestCaseID=1,$testCasePrefix,$showTestSuiteContents=1)
{
	static $resultsCfg;
	static $status_descr_code;
	static $status_code_descr;
	static $status_verbose;
	
	if(!$resultsCfg)
	{ 
		$resultsCfg=config_get('results');
		$status_descr_code=$resultsCfg['status_code'];
		$status_code_descr=$resultsCfg['code_status'];
		$status_verbose=$resultsCfg['status_label'];
	}
	
	$name = filterString($node['name']);
	$buildLinkTo = 1;
	$pfn = "ST";
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;	
	$create_counters=0;
	$versionID = 0;
	$node['leaf']=false;
	
	$testcaseColouring=1;
	$countersColouring=1;
	if( !is_null($useColors) )
	{
		$testcaseColouring=$useColors->testcases;
		$countersColouring=$useColors->counters;
	}
	
	// $doIt=true;
	// custom Property that will be accessed by EXT-JS using node.attributes
   	$node['tlNodeType'] = $node_type;
	switch($node_type)
	{
		case 'testproject':
			$create_counters=1;
			$pfn = $bForPrinting ? 'TPLAN_PTP' : 'SP';
			$label =  $name . " (" . $testcase_count . ")";
		break;
			
		case 'testsuite':
			$create_counters=1;
			$label =  $name . " (" . $testcase_count . ")";	
			if( $bForPrinting )
			{
				$pfn = 'TPLAN_PTS';
			}
			else
			{
				$pfn = $showTestSuiteContents ? 'STS' : null; 
			}
		break;
			
		case 'testcase':
			$node['leaf'] = true;
			$buildLinkTo = $tc_action_enabled;
			if (!$buildLinkTo)
			{
				$pfn = null;
			}
			
			//echo "DEBUG - Test Case rendering: \$node['id']:{$node['id']}<br>";
			$status_code = $tcase_node[$node['id']]['exec_status'];
			$status_descr = $status_code_descr[$status_code];
			$status_text = lang_get($status_verbose[$status_descr]);
			$css_class = $testcaseColouring ? (" class=\"light_{$status_descr}\" ") : '';   
			$label = "<span {$css_class} " . '  title="' . $status_text .	'" alt="' . $status_text . '">';
			
			if($showTestCaseID)
			{
				$label .= "<b>".htmlspecialchars($testCasePrefix.$node['external_id'])."</b>:";
			} 
			$label .= "{$name}</span>";
			
			$versionID = $node['tcversion_id'];
		break;
	}
	
	if($create_counters)
	{
		$label = $name ." (" . $testcase_count . ")";
		if($useCounters)
		{
			$add_html = create_counters_info($node,$countersColouring);        
			$label .= $add_html; 
		}
	}
    
	$node['text'] = $label;
	$node['position'] = isset($node['node_order']) ? $node['node_order'] : 0;
	$node['href'] = is_null($pfn)? '' : "javascript:{$pfn}({$node['id']},{$versionID})";
	
	// Remove useless keys
	foreach($status_descr_code as $key => $code)
	{
		if(isset($node[$key]))
		{
			unset($node[$key]); 
		}  
	}
	
	$key2del = array('node_type_id','parent_id','node_order','node_table',
		             'tcversion_id','external_id','version','testcase_count');  
	foreach($key2del as $key)
	{
		if(isset($node[$key]))
		{
			unset($node[$key]); 
		}  
	}
}


/**
 * Filter out the testcases that don't have the given value 
 * in their custom field(s) from the tree.
 * Recursive function.
 * 
 * @author Andreas Simon
 * @since 1.9
 * 
 * @param array &$tcase_tree reference to test case set/tree to filter
 * @param array &$cf_hash reference to selected custom field information
 * @param resource &$db reference to DB handler object
 * @param int $node_type_testsuite ID of node type for testsuites
 * @param int $node_type_testcase ID of node type for testcase
 * 
 * @return array $tcase_tree filtered tree structure
 * 
 * @internal revisions:
 * 
 * 20100702 - did some changes to logic in here and added a fix for array indexes
 */
function filter_by_cf_values(&$tcase_tree, &$cf_hash, &$db, $node_type_testsuite, $node_type_testcase) {
	static $tables = null;
	static $debugMsg = null;
	
	if (!$debugMsg) {
		$tables = tlObject::getDBTables('cfield_design_values');
		$debugMsg = 'Function: ' . __FUNCTION__;
	}
	
	$node_deleted = false;
	
	// This code is in parts based on (NOT simply copy/pasted)
	// some filter code used in testplan class.
	// Implemented because we have a tree here, 
	// not simple one-dimensional array of testcases like in tplan class.
	
	foreach ($tcase_tree as $key => $node) {
		
		if ($node['node_type_id'] == $node_type_testsuite) {

			$delete_suite = false;
			
			if (isset($node['childNodes']) && is_array($node['childNodes'])) {
				// node is a suite and has children, so recurse one level deeper			
				$tcase_tree[$key]['childNodes'] = filter_by_cf_values($tcase_tree[$key]['childNodes'], 
				                                                      $cf_hash, $db, 
				                                                      $node_type_testsuite,
				                                                      $node_type_testcase);
				
				// now remove testsuite node if it is empty after coming back from recursion
				if (!count($tcase_tree[$key]['childNodes'])) {
					$delete_suite = true;
				}
			} else {
				// nothing in here, suite was already empty
				$delete_suite = true;
			}
			
			if ($delete_suite) {
				unset($tcase_tree[$key]);
				$node_deleted = true;
			}			
		} else if ($node['node_type_id'] == $node_type_testcase) {
			// node is testcase, check if we need to delete it
			
			$passed = false;
			
			foreach ($cf_hash as $cf_id => $cf_value)
			{
				// there will never be more than one record that has a field_id / node_id combination
				$sql = " /* $debugMsg */ SELECT value FROM {$tables['cfield_design_values']} " .
				       " WHERE field_id = $cf_id " .
				       " AND node_id = {$node['id']} ";
				
				$result = $db->exec_query($sql);
				$row = $db->fetch_array($result);
				
				// push both to arrays so we can compare
				$possibleValues = explode ('|', $row['value']);
				$valuesSelected = explode ('|', $cf_value);
				
				// we want to match any selected item from list and checkboxes.
				if ( count($valuesSelected) ) {
					foreach ($valuesSelected as $vs_id => $vs_value) {
						$found = array_search($vs_value, $possibleValues);
						if (is_int($found)) {
							$passed = true;
						} else {
							$passed = false;
							break;
						}
					}
				}

				// jumping out of foreach here creates an AND search
				// removing this if would cause OR search --> the first found value counts
				if (!$passed) {
					break;
				}
			}
			
			// now delete node if no match was found
			if (!$passed) {
				unset($tcase_tree[$key]);
				$node_deleted = true;
			}			
		}
	}
	
	// 20100702 - asimon
	// if we deleted a note, the numeric indexes of this array do have missing numbers,
	// which causes problems in later loop constructs in other functions that assume numeric keys
	// in these arrays without missing numbers in between - crashes JS tree!
	// -> so I have to fix the array indexes here starting from 0 without missing a key 
	if ($node_deleted) {
		$tcase_tree = array_values($tcase_tree);
	}
	
	return $tcase_tree;
}


/**
 * remove the testcases that don't have the given result in any build
 * 
 * @param object &$tplan_mgr reference to test plan manager object
 * @param array &$tcase_set reference to test case set to filter
 * @param integer $tplan_id ID of test plan
 * @param array $filters filters to apply to test case set
 * @return array new tcase_set
 */
function filter_by_status_for_any_build(&$tplan_mgr,&$tcase_set,$tplan_id,$filters) {
	
	$key2remove=null;
	$buildSet = $tplan_mgr->get_builds($tplan_id, testplan::ACTIVE_BUILDS);
	$status = 'filter_result_result';
	
	if( !is_null($buildSet) ) {
		$tcase_build_set = $tplan_mgr->get_status_for_any_build($tplan_id,
		                                   array_keys($buildSet),$filters->{$status});  
		                                                             
		if( is_null($tcase_build_set) ) {
			$tcase_set = array();
		} else {
			$key2remove=null;
			foreach($tcase_set as $key_tcase_id => $value) {
				if( !isset($tcase_build_set[$key_tcase_id]) ) {
					$key2remove[]=$key_tcase_id;
				}
			}
		}
		
	if( !is_null($key2remove) ) {
			foreach($key2remove as $key) {
				unset($tcase_set[$key]); 
			}
		}
	}
		
	return $tcase_set;
}

/**
 * filter testcases out that do not have the same execution result in all builds
 * 
 * @param object &$tplan_mgr reference to test plan manager object
 * @param array &$tcase_set reference to test case set to filter
 * @param integer $tplan_id ID of test plan
 * @param array $filters filters to apply to test case set
 * 
 * @return array new tcase_set
 */
function filter_by_same_status_for_all_builds(&$tplan_mgr,&$tcase_set,$tplan_id,$filters) {
	$key2remove=null;
	$buildSet = $tplan_mgr->get_builds($tplan_id, testplan::ACTIVE_BUILDS);
	$status = 'filter_result_result';
	
	if( !is_null($buildSet) ) {
		$tcase_build_set = $tplan_mgr->get_same_status_for_build_set($tplan_id,
		                                                             array_keys($buildSet),$filters->{$status});  
		                                                             
		if( is_null($tcase_build_set) ) {
			$tcase_set = array();
		} else {
			$key2remove=null;
			foreach($tcase_set as $key_tcase_id => $value) {
				if( !isset($tcase_build_set[$key_tcase_id]) ) {
					$key2remove[]=$key_tcase_id;
				}
			}
		}
		
		if( !is_null($key2remove) ) {
			foreach($key2remove as $key) {
				unset($tcase_set[$key]); 
			}
		}
	}
	
	return $tcase_set;
}

/**
 * filter testcases out which do not have the chosen status in the given build
 * used by filter options 'result on specific build' and 'result on current build'
 *  
 * @param object &$tplan_mgr reference to test plan manager object
 * @param array &$tcase_set reference to test case set to filter
 * @param integer $tplan_id ID of test plan
 * @param array $filters filters to apply to test case set
 * @return array new tcase_set
 */
function filter_by_status_for_build(&$tplan_mgr,&$tcase_set,$tplan_id,$filters) {
	$key2remove=null;
	$build_key = 'filter_result_build';
	$result_key = 'filter_result_result';
	
	$buildSet = array($filters->$build_key => $tplan_mgr->get_build_by_id($tplan_id,$filters->$build_key));
	
	if( !is_null($buildSet) ) {
		$tcase_build_set = $tplan_mgr->get_status_for_any_build($tplan_id,
		                                                array_keys($buildSet),$filters->$result_key);  
		if( is_null($tcase_build_set) ) {
			$tcase_set = array();
		} else {
			$key2remove=null;
			foreach($tcase_set as $key_tcase_id => $value) {
				if( !isset($tcase_build_set[$key_tcase_id]) ) {
					$key2remove[]=$key_tcase_id;
				}
			}
		}

		if( !is_null($key2remove) ) {
			foreach($key2remove as $key) {
				unset($tcase_set[$key]); 
			}
		}
	}
	
	return $tcase_set;
}

/**
 * filter testcases by the result of their latest execution
 * 
 * @param object &$db reference to database handler
 * @param object &$tplan_mgr reference to test plan manager object
 * @param array &$tcase_set reference to test case set to filter
 * @param integer $tplan_id ID of test plan
 * @param array $filters filters to apply to test case set
 * @return array new tcase_set
 */
function filter_by_status_for_last_execution(&$db, &$tplan_mgr,&$tcase_set,$tplan_id,$filters) {
	$tables = tlObject::getDBTables('executions');
	$result_key = 'filter_result_result';
	
	$in_status = implode("','", $filters->$result_key);
	
	foreach($tcase_set as $tc_id => $tc_info) {
		// get last execution result for each testcase, 
		// if it differs from the result in tcase_set the tcase will be deleted from set
		$sql = " SELECT status FROM {$tables['executions']} E " .
			   " WHERE tcversion_id = {$tc_info['tcversion_id']} AND testplan_id = {$tplan_id} " .
			   " AND platform_id = {$tc_info['platform_id']} " .
			   " AND status = '{$tc_info['exec_status']}' " .
			   " AND status IN ('{$in_status}') " .
			   " ORDER BY execution_ts DESC limit 1 ";
		$result = null;
		$result = $db->fetchArrayRowsIntoMap($sql,'status');
		
		if (is_null($result)) {
			unset($tcase_set[$tc_id]);
		}
	}
	
	return $tcase_set;
}


/**
 * filter out those testcases, that do not have at least one build in 'not run' status
 * 
 * @param object &$tplan_mgr reference to test plan manager object
 * @param array &$tcase_set reference to test case set to filter
 * @param integer $tplan_id ID of test plan
 * @param array $filters filters to apply to test case set
 * @return array new tcase_set
 */
function filter_not_run_for_any_build(&$tplan_mgr,&$tcase_set,$tplan_id,$filters) {
	$key2remove=null;
	$buildSet = $tplan_mgr->get_builds($tplan_id);
	
	if( !is_null($buildSet) ) {
		$tcase_build_set = $tplan_mgr->get_not_run_for_any_build($tplan_id, array_keys($buildSet));  
		                                                             
		if( is_null($tcase_build_set) ) {
			$tcase_set = array();
		} else {
			$key2remove=null;
			foreach($tcase_set as $key_tcase_id => $value) {
				if( !isset($tcase_build_set[$key_tcase_id]) ) {
					$key2remove[]=$key_tcase_id;
				}
			}
		}
		
		if( !is_null($key2remove) ) {
			foreach($key2remove as $key) {
				unset($tcase_set[$key]); 
			}
		}
	}
	
	return $tcase_set;
}



/** VERY IMPORTANT: node must be passed BY REFERENCE */
function extjs_renderTestSpecTreeNodeOnOpen(&$node,$node_type,$tc_action_enabled,
			$bForPrinting,$showTestCaseID,$testCasePrefix)
{
	$name = filterString($node['name']);
	$buildLinkTo = 1;
	$pfn = "ET";
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;	
	
	switch($node_type)
	{
		case 'testproject':
			$pfn = $bForPrinting ? 'TPROJECT_PTP' : 'EP';
			$label =  $name . " (" . $testcase_count . ")";
			break;
			
		case 'testsuite':
			$pfn = $bForPrinting ? 'TPROJECT_PTS' : 'ETS';
			$label =  $name . " (" . $testcase_count . ")";	
			break;
			
		case 'testcase':
			$buildLinkTo = $tc_action_enabled;
			if (!$buildLinkTo)
			{
				$pfn = "void";
			}
			
			$label = "";
			if($showTestCaseID)
			{
				$label .= "<b>{$testCasePrefix}{$node['external_id']}</b>:";
			} 
			$label .= $name;
			break;
			
	} // switch	
	
	$node['text']=$label;
	$node['position']=isset($node['node_order']) ? $node['node_order'] : 0;
	$node['href']=is_null($pfn)? '' : "javascript:{$pfn}({$node['id']})";
	
	// Remove useless keys
	$resultsCfg=config_get('results');
	$status_descr_code=$resultsCfg['status_code'];
	
	foreach($status_descr_code as $key => $code)
	{
		if(isset($node[$key]))
		{
			unset($node[$key]); 
		}  
	}
	$key2del=array('node_type_id','parent_id','node_order','node_table',
		'tcversion_id','external_id','version','testcase_count');  
	
	foreach($key2del as $key)
	{
		if(isset($node[$key]))
		{
			unset($node[$key]); 
		}  
	}
}


/**
 * generate array with Keywords for a filter
 *
 */
function buildKeywordsFilter($keywordsId,&$guiObj)
{
    $keywordsFilter = null;
    
    if(!is_null($keywordsId))
    {
        $items = array_flip((array)$keywordsId);
        if(!isset($items[0]))
        {
            $keywordsFilter = new stdClass();
            $keywordsFilter->items = $keywordsId;
            $keywordsFilter->type = isset($guiObj->keywordsFilterTypes) ? $guiObj->keywordsFilterTypes->selected: 'OR';
        }
    }
    
    return $keywordsFilter;
}


/**
 * generate object with test case execution type for a filter
 *
 */
function buildExecTypeFilter($execTypeSet)
{
    $itemsFilter = null;
    
    if(!is_null($execTypeSet))
    {
        $items = array_flip((array)$execTypeSet);
        if(!isset($items[0]))
        {
            $itemsFilter = new stdClass();
            $itemsFilter->items = $execTypeSet;
        }
    }
    
    return $itemsFilter;
}

/**
 * generate object with test case importance for a filter
 *
 */
function buildImportanceFilter($importance)
{
    $itemsFilter = null;
    
    if(!is_null($importance))
    {
        $items = array_flip((array)$importance);
        if(!isset($items[0]))
        {
            $itemsFilter = new stdClass();
            $itemsFilter->items = $importance;
        }
    }
    
    return $itemsFilter;
}

?>