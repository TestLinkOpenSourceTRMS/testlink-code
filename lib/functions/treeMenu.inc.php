<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * functions related to tree menu building for test specification and test execution.
 * 
 * @filesource	treeMenu.inc.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2005-2012, TestLink community 
 * @link 		http://www.teamst.org/index.php
 * @uses 		config.inc.php
 *
 * @internal revisions
 * @since 1.9.4
 * 20120607 - franciscom - 	somework to be able to generate tree used on Test Report, and Test Plan Report
 * 20120415 - franciscom - 	filter_by_same_status_for_all_builds() => filterStatusSetAllActiveBuilds()
 *
 * 20120205 - franciscom - 	remove deprecated method
 * 20110115 - franciscom -	work on extjs_renderExecTreeNodeOnOpen() and related functions
 *							trying to improve performance
 * 20111031 - franciscom - 	TICKET 4790: Setting & Filters panel - Wrong use of BUILD on settings area
 *							generateExecTree().
 *
 * 20110823 - franciscom - 	filter_by_cf_values() interface changes
 * 							TICKET 4710: Performance/Filter Problem on big project - get_ln_tcversions()
 *							new functions apply_status_filters(); update_status_for_colors();
 *
 * 20110820 - franciscom - 	TICKET 4710: Performance/Filter Problem on big project
 *							generateExecTree() - changes in call to get_linked_tcversions()
 *
 * 20110709 - franciscom - fixed event viewer warning due to missing isset() check generateExecTree()
 *
 * @since 1.9.3
 * 20110311 - asimon - BUGID 3765: Req Spec Doc ID disappeared for req specs without direct requirement child nodes
 *
 */
require_once(dirname(__FILE__)."/../../third_party/dBug/dBug.php");


require_once("execTreeMenu.inc.php");

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
	// BUGID 4470 - avoid escaped characters in trees
	// $str = addslashes($str);
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
 * 20110811 - franciscom - TICKET 4661: Implement Requirement Specification Revisioning for better traceabilility
 * 20100810 - asimon - filtering by testcase ID
 * 20100428 - asimon - BUGID 3301, added filtering by custom fields
 */
 
 
function generateTestSpecTree(&$db,$tproject_id, $tproject_name,$linkto,$filters=null,$options=null)
{
 	$chronos[] = microtime(true);

	$tables = tlObjectWithDB::getDBTables(array('tcversions','nodes_hierarchy'));

	$my = array();
	
	$my['options'] = array('forPrinting' => 0, 'hideTestCases' => 0, 
	                       'tc_action_enabled' => 1, 'ignore_inactive_testcases' => 0, 
	                       'viewType' => 'testSpecTree');
	

	// testplan => only used if opetions['viewType'] == 'testSpecTreeForTestPlan'
	
	// 20120205 - franciscom - hmm seems this code is INCOMPLETE
	// may be we can remove ?
	$my['filters'] = array('keywords' => null, 'executionType' => null, 'importance' => null,
	                       'testplan' => null, 'filter_tc_id' => null);

	$my['options'] = array_merge($my['options'], (array)$options);
	$my['filters'] = array_merge($my['filters'], (array)$filters);
	
	
	if( $my['options']['viewType'] == 'testSpecTree' )
	{
		$rr = generateTestSpecTreeNew($db,$tproject_id,$tproject_name,$linkto,$filters,$options);
		$chronos[] = microtime(true);
		$tnow = end($chronos);
		$tprev = prev($chronos);
		$t_elapsed = number_format( $tnow - $tprev, 4);
		echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (get_subtree()):' . $t_elapsed .'<br>';
		reset($chronos);	
		return $rr;
	}
	
	
	// new dBug($filters);
	// new dBug($my['filters']);
	
	$treeMenu = new stdClass(); 
	$treeMenu->rootnode = null;
	$treeMenu->menustring = '';
	
	$resultsCfg = config_get('results');
	$glueChar = config_get('testcase_cfg')->glue_character;
	$menustring = null;
	
	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;	
	
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
	$status_descr_code=$resultsCfg['status_code'];
	$status_code_descr=$resultsCfg['code_status'];
	
	$decoding_hash=array('node_id_descr' => $hash_id_descr,
		                 'status_descr_code' =>  $status_descr_code,
		                 'status_code_descr' =>  $status_code_descr);
	
	// IMPORTANT NOTICE
	// $filters['filter_toplevel_testsuite'] is managed in REVERSE form
	// it contains NOT WHAT user wants, but all that we need to exclude
	// in order provide what user WANTS.
	// This is right way to go.
	// 
	$exclude_branches = isset($filters['filter_toplevel_testsuite']) && 
						is_array($filters['filter_toplevel_testsuite']) ?
	                    $filters['filter_toplevel_testsuite'] : null;
	
	$tcase_prefix = $tproject_mgr->getTestCasePrefix($tproject_id) . $glueChar;

	/*
	$test_spec = $tproject_mgr->get_subtree($tproject_id,testproject::RECURSIVE_MODE,
		                                    testproject::INCLUDE_TESTCASES, $exclude_branches);

	*/	
	// new dBug($filters);
	$test_spec = getTestSpecTree($tproject_id,$tproject_mgr,$filters);


	// Added root node for test specification -> testproject
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];

	
	$map_node_tccount=array();
	$tplan_tcs=null;
	
	if($test_spec)
	{
		$tck_map = null;  // means no filter
		if(!is_null($my['filters']['filter_keywords']))
		{
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
		$keys2init = array('filter_testcase_name','filter_execution_type','filter_priority','filter_tc_id');
		foreach ($keys2init as $keyname) {
			$pnFilters[$keyname] = isset($my['filters'][$keyname]) ? $my['filters'][$keyname] : null;
		}
	    
	    $pnFilters['setting_testplan'] = $my['filters']['setting_testplan'];
	    if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes'])) 
	    {
	    	$test_spec['childNodes'] = filter_by_cf_values($db, $test_spec['childNodes'],
	    												   $my['filters']['filter_custom_fields'],$hash_descr_id);
	    }
		
	    $pnOptions = array('hideTestCases' => $my['options']['hideTestCases'], 
	    				   'viewType' => $my['options']['viewType'],	
		                   'ignoreInactiveTestCases' => $my['options']['ignore_inactive_testcases']);
		
		$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,$tck_map,
			                             $tplan_tcs,$pnFilters,$pnOptions);

		foreach($testcase_counters as $key => $value)
		{
			$test_spec[$key] = $testcase_counters[$key];
		}
		
		
		$showTestCaseID = config_get('treemenu_show_testcase_id');
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
 * @internal revisions
 */
function prepareNode(&$db,&$node,&$decoding_info,&$map_node_tccount,$tck_map = null,
                     &$tplan_tcases = null,$filters=null, $options=null)
{
	
	static $status_descr_list;
	static $debugMsg;
    static $tables;
    static $my;
    static $enabledFiltersOn;
    static $activeVersionClause;
    static $filterOnTCVersionAttribute;
    static $filtersApplied;
    static $users2filter;
    static $results2filter;

    $tpNode = null;
	if (!$tables)
	{
		// new dBug($tplan_tcases);
		
  	    $debugMsg = 'Class: ' . __CLASS__ . ' - ' . 'Method: ' . __FUNCTION__ . ' - ';
        $tables = tlObjectWithDB::getDBTables(array('tcversions','nodes_hierarchy','testplan_tcversions'));

		$status_descr_list = array_keys($decoding_info['status_descr_code']);
		$status_descr_list[] = 'testcase_count';
		
		$my = array();
		$my['options'] = array('hideTestCases' => 0, 'showTestCaseID' => 1, 'viewType' => 'testSpecTree',
		                       'getExternalTestCaseID' => 1,'ignoreInactiveTestCases' => 0);

		// asimon - added importance here because of "undefined" error in event log
		$my['filters'] = array('status' => null, 'assignedTo' => null, 
		                       'importance' => null, 'executionType' => null,
		                       'filter_tc_id' => null);
		
		$my['options'] = array_merge($my['options'], (array)$options);
		$my['filters'] = array_merge($my['filters'], (array)$filters);

		$enabledFiltersOn['testcase_id'] = isset($my['filters']['filter_tc_id']);
		$enabledFiltersOn['testcase_name'] = isset($my['filters']['filter_testcase_name']);
		$enabledFiltersOn['executionType'] = isset($my['filters']['filter_execution_type']);
		$enabledFiltersOn['importance'] = isset($my['filters']['filter_priority']);
		$enabledFiltersOn['custom_fields'] = isset($my['filters']['filter_custom_fields']);
		$enabledFiltersOn['keywords'] = isset($tck_map);


		$filterOnTCVersionAttribute = $enabledFiltersOn['executionType'] || $enabledFiltersOn['importance'];
					
		$filtersApplied = false;
		foreach($enabledFiltersOn as $filterValue)
		{
			$filtersApplied = $filtersApplied || $filterValue; 
		}
		
		$activeVersionClause = $filterOnTCVersionAttribute ? " AND TCV.active=1 " : '';
		
		$users2filter = isset($my['filters']['filter_assigned_user']) ?
		                      $my['filters']['filter_assigned_user'] : null;
		                      
		$results2filter = isset($my['filters']['filter_result_result']) ?
		                  		$my['filters']['filter_result_result'] : null;
		
	}
		
	// $tcase_counters = array('testcase_count' => 0);
	//foreach($status_descr_code as $status_descr => $status_code)
	//{
	//	$tcase_counters[$status_descr]=0;
	//}
	// $tcase_counters['testcase_count']=0;
	$tcase_counters = array_fill_keys($status_descr_list, 0);
	
	$node_type = isset($node['node_type_id']) ? $decoding_info['node_id_descr'][$node['node_type_id']] : null;

	if($node_type == 'testcase')
	{
		if( ($enabledFiltersOn['keywords'] && !isset($tck_map[$node['id']])) ||
			($enabledFiltersOn['testcase_name'] && 
				 stripos($node['name'], $my['filters']['filter_testcase_name']) === FALSE)  ||
			($enabledFiltersOn['testcase_id'] && ($node['id'] != $my['filters']['filter_tc_id']) ) 
		  ) 	
		{
			unset($tplan_tcases[$node['id']]);
			$node = null;
		}
		else
		{
			if($my['options']['viewType'] == 'executionTree')
			{
				$tpNode = isset($tplan_tcases[$node['id']]) ? $tplan_tcases[$node['id']] : null;
				if( !($delete_node=is_null($tpNode)) )
				{			
					$delete_node =  !is_null($results2filter) && 
									!isset($results2filter[$tpNode['exec_status']]);
				
					if(!$delete_node && !is_null($users2filter))
					{ 
						$somebody_wanted_but_nobody_there = isset($users2filter[TL_USER_SOMEBODY]) && 
															!is_numeric($tpNode['user_id']);
				
						$unassigned_wanted_but_someone_assigned = isset($users2filter[TL_USER_NOBODY]) && 
															  	  !is_null($tpNode['user_id']);
				
						$wrong_user = !isset($users2filter[TL_USER_NOBODY]) && 
									  !isset($users2filter[TL_USER_SOMEBODY]) && 
				    	    	      !isset($users2filter[$tpNode['user_id']]);

						$delete_node = $unassigned_wanted_but_someone_assigned || $wrong_user  || 
								   	   $somebody_wanted_but_nobody_there;
					}
				}

				if($delete_node) 
				{
					unset($tplan_tcases[$node['id']]);
					$node = null;
				} 
				else 
				{
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
		}
		// -------------------------------------------------------------------
		
		// -------------------------------------------------------------------
		if ($node && ($my['options']['viewType']=='testSpecTree' || 
					  $my['options']['viewType'] =='testSpecTreeForTestPlan') )
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
					switch ($my['options']['viewType'])
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
		
		// ========================================================================
		foreach($tcase_counters as $key => $value)
		{
			$tcase_counters[$key]=0;
		}
		if(isset($tpNode['exec_status']) )
		{
			$tc_status_descr = $decoding_info['status_code_descr'][$tpNode['exec_status']];   
		}
		else
		{
			$tc_status_descr = "not_run";
		}
		
		$init_value = $node ? 1 : 0;
		$tcase_counters[$tc_status_descr] = $init_value;
		$tcase_counters['testcase_count'] = $init_value;
		if ( $my['options']['hideTestCases'] )
		{
			$node = null;
		}
		// ========================================================================
	}  // if($node_type == 'testcase')
	
	
	// ========================================================================
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

        // node must be destroyed if empty had we have using filtering conditions
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

	static $f2call;
	$menustring='';

	// -------------------------------------------------------------------------------
	// Choice for PERFORMANCE:
	// Some pieces of code on TL < 1.9.4 has been wrapped in a function, but when working
	// with BIG amount of testcases (> 5000) impact on performance was high.
	if(!$f2call)
	{
		$f2call['testproject'] = $bForPrinting ? 'TPROJECT_PTP' : 'EP';
		$f2call['testsuite'] = $bForPrinting ? 'TPROJECT_PTS' : 'ETS';
		$f2call['testcase'] = $tc_action_enabled ? 'ET' : 'void';
	}
	$node['testlink_node_name'] = filterString($node['name']);
	$node['testlink_node_type'] = $hash_id_descr[$node['node_type_id']];
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;	
	$pfn = $f2call[$node['testlink_node_type']];
	
	switch($node['testlink_node_type'])
	{
		case 'testproject':
		case 'testsuite':
			$node['text'] =  $node['testlink_node_name'] . " (" . $testcase_count . ")";
			break;
			
		case 'testcase':
			$node['text'] = "";
			if($showTestCaseID)
			{
				$node['text'] .= "<b>{$testCasePrefix}{$node['external_id']}</b>:";
			} 
			$node['text'] .= $node['testlink_node_name'];
			break;
	} // switch	

	$node['position'] = isset($node['node_order']) ? $node['node_order'] : 0;
	$node['href'] = "javascript:{$pfn}({$node['id']})";
	// -------------------------------------------------------------------------------	
	
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
 * @internal revisions
 *
 * @since 1.9.4
 * 20110820 - franciscom - 	TICKET 4710: Performance/Filter Problem on big project
 */
function generateExecTree(&$db,&$menuUrl,$tproject_id,$tproject_name,$tplan_id,
                          $tplan_name,$filters,$options) 
{

 	$chronos[] = microtime(true);


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
   
   
    new dBug($filters);
  	
	$keyword_id = 0;
	$keywordsFilterType = 'Or';
	if (property_exists($filters, 'filter_keywords') && !is_null($filters->filter_keywords)) 
	{
		$keyword_id = $filters->filter_keywords;
		$keywordsFilterType = $filters->filter_keywords_filter_type;
	}
	
	// @since 1.9.4 - TICKET 4790: Setting & Filters panel - Wrong use of BUILD on settings area	
	$buildSettingsPanel = isset($filters->setting_build) ? $filters->setting_build : 0;
	$buildFiltersPanel = isset($filters->filter_result_build) ? $filters->filter_result_build : null;
	$build2filter_assignments = is_null($buildFiltersPanel) ? $buildSettingsPanel : $buildFiltersPanel;
	
	
	$tc_id = isset($filters->filter_tc_id) ? $filters->filter_tc_id : null; 
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
	
	$useCounters=isset($options->useCounters) ? $options->useCounters : null;
	$useColors=isset($options->useColours) ? $options->useColours : null;
	$colorBySelectedBuild = isset($options->testcases_colouring_by_selected_build) ? 
	                        $options->testcases_colouring_by_selected_build : null;

	// 20110823

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
	
  	// remove test spec, test suites (or branches) that have ZERO test cases linked to test plan
  	// 
  	// IMPORTANT:
  	// using 'order_cfg' => array("type" =>'exec_order',"tplan_id" => $tplan_id))
  	// makes the magic of ignoring test cases not linked to test plan.
  	// This unexpected bonus can be useful on export test plan as XML.
  	//
  	$my['options']=array('recursive' => true, 'remove_empty_nodes_of_type' => $tree_manager->node_descr_id['testsuite'],
  	                     'order_cfg' => array("type" =>'exec_order',"tplan_id" => $tplan_id));
 	$my['filters'] = array('exclude_node_types' => $nt2exclude,
 	                       'exclude_children_of' => $nt2exclude_children);
	
 	// BUGID 3301 - added for filtering by toplevel testsuite
 	if (isset($filters->{'filter_toplevel_testsuite'}) && is_array($filters->{'filter_toplevel_testsuite'})) {
 		$my['filters']['exclude_branches'] = $filters->{'filter_toplevel_testsuite'};
 	}
 	
 	// Take Time
 	//$chronos[] = microtime(true);
	//$tnow = end($chronos);
	//$tprev = prev($chronos);
    
    /*
 	
 	$test_spec = $tree_manager->get_subtree($tproject_id,$my['filters'],$my['options']);
 	*/
 	// new dBug($my);
    $test_spec = $tplan_mgr->getSkeleton($tplan_id,$tproject_id,$my['filters'],$my['options']);
 	//echo 'BEFORE';
 	
 	//echo 'AF';
 	//new dBug($test_spec);
 	//die();
 	
 	
 	// Take Time
 	$chronos[] = microtime(true);
	$tnow = end($chronos);
	$tprev = prev($chronos);
	$t_elapsed = number_format( $tnow - $tprev, 4);
	echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (get_subtree()):' . $t_elapsed .'<br>';
	reset($chronos);	

     
	$test_spec['name'] = $tproject_name . " / " . $tplan_name;  // To be discussed
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];
	$map_node_tccount = array();
	
	$tplan_tcases = null;
    $apply_other_filters=true;


	if($test_spec)
	{
		if(is_null($tc_id) || $tc_id > 0)   // 20120519 TO BE CHECKED
		{
			$doFilterByKeyword = (!is_null($keyword_id) && $keyword_id > 0);
			if($doFilterByKeyword)
			{
				$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,$keyword_id,$keywordsFilterType);
			}
			
			// Multiple step algoritm to apply keyword filter on type=AND
			// get_*_tcversions filters by keyword ALWAYS in OR mode.
			$linkedFilters = array('tcase_id' => $tc_id, 
								   'keyword_id' => $keyword_id, 'keyword_filter_type' => $keywordsFilterType,
                                   'assigned_to' => $assignedTo,
                                   'assigned_on_build' => $build2filter_assignments,
                                   'cf_hash' =>  $cf_hash,
                                   'platform_id' => $setting_platform,
                                   'urgencyImportance' => $urgencyImportance,
                                   'exec_type' => $execution_type);
			
			$opt = array('include_unassigned' => $include_unassigned, 'steps_info' => false);
			// TICKET 4710
			if( ($opt['last_execution'] = $useColors && $colorBySelectedBuild) )
			{
				$linkedFilters['build_id'] = $filters->setting_build;
			}
			else
			{
				$opt['last_execution'] = isset($options->absolute_last_execution) ? 
	                        			 $options->absolute_last_execution : false;
			}
			$linkedFilters['tcase_name'] = isset($filters->filter_testcase_name) ? $filters->filter_testcase_name : null; 
				
			echo 'DEBUG' . __FUNCTION__ . '<br>';
			new dBug($linkedFilters);
			new dBug($opt);
									
			$tplan_tcases = $tplan_mgr->get_linked_tcversions($tplan_id,$linkedFilters,$opt);
			//new dBug($tplan_tcases);
			
		 	// Take Time
		 	$chronos[] = microtime(true);
			$tnow = end($chronos);
			$tprev = prev($chronos);
			$t_elapsed = number_format( $tnow - $tprev, 4);
			echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (<b>AFTER get_ln_tcversions()</b>):' . $t_elapsed .'<br>';
			reset($chronos);	


			if($tplan_tcases && $doFilterByKeyword && $keywordsFilterType == 'And')
			{
				$filteredSet = $tcase_mgr->filterByKeyword(array_keys($tplan_tcases),$keyword_id,$keywordsFilterType);

				// CAUTION: if $filteredSet is null,
				// then get_*_tcversions() thinks there are just no filters set,
				// but really there are no testcases which match the wanted keyword criteria,
				// so we have to set $tplan_tcases to null because there is no more filtering necessary
				if ($filteredSet != null) {
					$linkedFilters = array('tcase_id' => array_keys($filteredSet));
					
					// TICKET 4710
					$tplan_tcases = $tplan_mgr->get_linked_tcversions($tplan_id,$linkedFilters,$opt);
					
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
		
		// Take time
	 	//$chronos[] = microtime(true);
		//$tnow = end($chronos);
		//$tprev = prev($chronos);
		//$t_elapsed = number_format( $tnow - $tprev, 4);
		//echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (<b>FROM get_subtree()</b>):' . $t_elapsed .'<br>';
		//reset($chronos);	

		// 20110823 - refactoring		
		if( $apply_other_filters )
		{
			$tplan_tcases = (array)apply_status_filters($tplan_id,$tplan_tcases,$filters,$tplan_mgr,$resultsCfg['status_code']);
			if( count($tplan_tcases) == 0)
			{
				$tplan_tcases = array();
				$apply_other_filters=false;
			}
		}
		// end - 20110823 - refactoring		

		// BUGID 3450 - Change colors/counters in exec tree.
		// Means: replace exec status in filtered array $tplan_tcases 
		// by the one of last execution of selected build.
		// Since this changes exec status, replacing is done after filtering by status.
		// It has to be done before call to prepareNode() though,
		// because that one sets the counters according to status.
		//
		// 20110823 - franciscom
		// A minor analysis need to be done regarding use of platform on following code.
		// Up today exec tree display data for ONLY a SELECTED platform 
		// this happens on EXECUTION FEATURE (execution_mode on tlTestCaseFilterControl.class.php).
		// We never display a tree where we have one test case occurrence for each platform.
		// Under this context query can be simplified, using a fixed value for platform_id filter.
		// 
		if ($apply_other_filters && $useColors && $colorBySelectedBuild) 
		{
			$context = array('tplanID' => $tplan_id, 'buildID' => $filters->setting_build); 
			update_status_for_colors($db,$tplan_tcases,$context,$resultsCfg['status_code']);
		}
		
		// After reviewing code, seems that assignedTo has no sense because tp_tcs
		// has been filtered.
		// Then to avoid changes to prepareNode() due to include_unassigned,
		// seems enough to set assignedTo to 0, if include_unassigned==true
		$assignedTo = $include_unassigned ? null : $assignedTo;
		
		$pnFilters = array('assignedTo' => $assignedTo);
		$keys2init = array('filter_testcase_name','filter_execution_type','filter_priority');
		
		foreach ($keys2init as $keyname) {
			$pnFilters[$keyname] = isset($filters->{$keyname}) ? $filters->{$keyname} : null;
		}

		// Take time
	 	//$chronos[] = microtime(true);
		//$tnow = end($chronos);
		//$tprev = prev($chronos);
		//$t_elapsed = number_format( $tnow - $tprev, 4);
		//echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (<b>BEFORE prepareNode()</b>):' . $t_elapsed .'<br>';
		//reset($chronos);	
	    		
		$pnOptions = array('hideTestCases' => false, 'viewType' => 'executionTree');
		$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
		                                 $tck_map,$tplan_tcases,$pnFilters,$pnOptions);

		// Take time
	 	// $chronos[] = microtime(true);
		// $tnow = end($chronos);
		// $tprev = prev($chronos);
		// $t_elapsed = number_format( $tnow - $tprev, 4);
		// echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (<b>AFTER prepareNode()</b>):' . $t_elapsed .'<br>';
		// reset($chronos);	


		foreach($testcase_counters as $key => $value)
		{
			$test_spec[$key] = $testcase_counters[$key];
		}
	
		$keys = array_keys($tplan_tcases);
		$menustring = renderExecTreeNode(1,$test_spec,$tplan_tcases,
			                             $hash_id_descr,1,$menuUrl,false,$useCounters,$useColors,
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
		if(isset($test_spec['childNodes'])) 
		{
			$menustring = str_ireplace('childNodes', 'children', json_encode($test_spec['childNodes']));
		}
		
		// Remove null elements (Ext JS tree do not like it ).
		// :null happens on -> "children":null,"text" that must become "children":[],"text"
		// $menustring = str_ireplace(array(':null',',null','null,'),array(':[]','',''), $menustring); 
		$menustring = str_ireplace(array(':null',',null','null,','null'),array(':[]','','',''), $menustring); 
	}  
	
	$treeMenu->menustring = $menustring;
	
	return array($treeMenu, $keys);
}


/**
 * 
 * 
 * @param integer $level
 * @param array &$node reference to recursive map
 * @param array &$tcases_map reference to map that contains info about testcase exec status
 *              when node is of testcase type.
 * @param boolean $hideTestCases 1 -> hide testcase
 * 
 * @return datatype description
 * 
 */
function renderExecTreeNode($level,&$node,&$tcase_node,$hash_id_descr,
                            $tc_action_enabled,$linkto,$hideTestCases,$useCounters,$useColors,
                            $showTestCaseID,$testCasePrefix,$showTestSuiteContents)
{
	static $resultsCfg;
	static $l18n;	
	static $pf;	
	static $doColouringOn;
	static $cssClasses;

	$node_type = $hash_id_descr[$node['node_type_id']];
	$menustring = '';
    echo 'FCFCFC';
	if(!$resultsCfg)
	{ 
		echo 'DDD?';
		$doColouringOn['testcase'] = 1;
		$doColouringOn['counters'] = 1;
		if( !is_null($useColors) )
		{
			$doColouringOn['testcase'] = $useColors->testcases;
			$doColouringOn['counters'] = $useColors->counters;
		}

		$resultsCfg = config_get('results');
		$status_descr_code = $resultsCfg['status_code'];
		foreach($resultsCfg['status_label'] as $key => $value)
		{
			$l18n[$status_descr_code[$key]] = lang_get($value);
			$cssClasses[$status_descr_code[$key]] = $doColouringOn['testcase'] ? ('class="light_' . $value . '"') : ''; 
		}
		$pf['testproject'] = $hideTestCases ? 'TPLAN_PTP' : 'SP';
		$pf['testsuite'] = $hideTestCases ? 'TPLAN_PTS' : ($showTestSuiteContents ? 'STS' : null); 
		
	}
	
	$name = filterString($node['name']);

	// custom Property that will be accessed by EXT-JS using node.attributes
	$node['testlink_node_name'] = $name;
   	$node['testlink_node_type'] = $node_type;

	switch($node_type)
	{
		case 'testproject':
		case 'testsuite':
			$node['leaf'] = false;
			$versionID = 0;
			$pfn = $pf[$node_type];
			$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;	
			$node['text'] = $name ." (" . $testcase_count . ")";
			if($useCounters)
			{
				$node['text'] .= create_counters_info($node,$doColouringOn['counters']);
			}
		break;
			
		case 'testcase':
			$node['leaf'] = true;
			$pfn = $tc_action_enabled ? 'ST' :null;
			$versionID = $node['tcversion_id'];

			$status_code = $tcase_node[$node['id']]['exec_status'];
			$node['text'] = "<span {$cssClasses[$status_code]} " . '  title="' .  $l18n[$status_code] . 
					 		'" alt="' . $l18n[$status_code] . '">';
			
			if($showTestCaseID)
			{
				$node['text'] .= "<b>" . htmlspecialchars($testCasePrefix . $node['external_id']) . "</b>:";
			} 
			$node['text'] .= "{$name}</span>";
		break;

		default:
			echo 'USING DEF<br>';
			$pfn = "ST";
		break;
	}
	
	// $node['text'] = $label;
	echo $pfn . '<br>';
	$node['position'] = isset($node['node_order']) ? $node['node_order'] : 0;
	$node['href'] = is_null($pfn)? '' : "javascript:{$pfn}({$node['id']},{$versionID})";

	
	// ----------------------------------------------------------------------------------------------
	if( isset($tcase_node[$node['id']]) )
	{
		unset($tcase_node[$node['id']]);
	}
	if (isset($node['childNodes']) && $node['childNodes'])
	{
	    // need to work always original object in order to change it's values using reference .
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
			                                  $tc_action_enabled,$linkto,$hideTestCases,
			                                  $useCounters,$useColors,$showTestCaseID,
			                                  $testCasePrefix,$showTestSuiteContents);
		}
	}
	return $menustring;
}



/**
 *
 *
 *
 */
function create_counters_info(&$node,$useColors)
{
	static $keys2display;
	static $labelCache;

	if(!$labelCache)
	{
		$resultsCfg = config_get('results');
		$status_label = $resultsCfg['status_label'];
		
		// I will add not_run if not exists
		$keys2display = array('not_run' => 'not_run');
		foreach( $resultsCfg['status_label_for_exec_ui'] as $key => $value)
		{
			if( $key != 'not_run')
			{
				$keys2display[$key]=$key;  
			}  
			$labelCache[$key] = lang_get($status_label[$key]);
		}
	} 

	$add_html='';
	foreach($keys2display as $key)
	{
		if( isset($node[$key]) )
		{
			$css_class = $useColors ? (" class=\"light_{$key}\" ") : '';   
			$add_html .= "<span {$css_class} " . ' title="' . $labelCache[$key] . 
						 '">' . $node[$key] . ",</span>";
		}
	}

	$add_html = "(" . rtrim($add_html,",</span>") . "</span>)"; 
	return $add_html;
}


/**
 * VERY IMPORTANT: node must be passed BY REFERENCE
 * 
 */
function extjs_renderExecTreeNodeOnOpen(&$node,$node_type,$tcase_node,$tc_action_enabled,
                                        $bForPrinting,$useCounters=1,$useColors=null,
                                        $showTestCaseID=1,$testCasePrefix,$showTestSuiteContents=1)
{
	static $resultsCfg;
	static $l18n;	
	static $pf;	
	static $doColouringOn;
	static $cssClasses;
	
	if(!$resultsCfg)
	{ 
		$doColouringOn['testcase'] = 1;
		$doColouringOn['counters'] = 1;
		if( !is_null($useColors) )
		{
			$doColouringOn['testcase'] = $useColors->testcases;
			$doColouringOn['counters'] = $useColors->counters;
		}

		$resultsCfg = config_get('results');
		$status_descr_code = $resultsCfg['status_code'];
		
		//var_dump($resultsCfg);
		//die();
		
		foreach($resultsCfg['status_label'] as $key => $value)
		{
			$l18n[$status_descr_code[$key]] = lang_get($value);
			$cssClasses[$status_descr_code[$key]] = $doColouringOn['testcase'] ? ('class="light_' . $value . '"') : ''; 
		}
		
		$pf['testproject'] = $bForPrinting ? 'TPLAN_PTP' : 'SP';
		$pf['testsuite'] = $bForPrinting ? 'TPLAN_PTS' : ($showTestSuiteContents ? 'STS' : null); 
		
	}
	
	$name = filterString($node['name']);

	// custom Property that will be accessed by EXT-JS using node.attributes
	$node['testlink_node_name'] = $name;
   	$node['testlink_node_type'] = $node_type;

	switch($node_type)
	{
		case 'testproject':
		case 'testsuite':
			$node['leaf'] = false;
			$versionID = 0;
			$pfn = $pf[$node_type];
			$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;	
			$node['text'] = $name ." (" . $testcase_count . ")";
			if($useCounters)
			{
				$node['text'] .= create_counters_info($node,$doColouringOn['counters']);
			}
		break;
			
		case 'testcase':
			$node['leaf'] = true;
			$pfn = $tc_action_enabled ? 'ST' :null;
			$versionID = $node['tcversion_id'];

			$status_code = $tcase_node[$node['id']]['exec_status'];
			$node['text'] = "<span {$cssClasses[$status_code]} " . '  title="' .  $l18n[$status_code] . 
					 		'" alt="' . $l18n[$status_code] . '">';
			
			if($showTestCaseID)
			{
				$node['text'] .= "<b>" . htmlspecialchars($testCasePrefix . $node['external_id']) . "</b>:";
			} 
			$node['text'] .= "{$name}</span>";
		break;

		default:
			$pfn = "ST";
		break;
	}
	
	// $node['text'] = $label;
	$node['position'] = isset($node['node_order']) ? $node['node_order'] : 0;
	$node['href'] = is_null($pfn)? '' : "javascript:{$pfn}({$node['id']},{$versionID})";
}


/**
 * Filter out the testcases that don't have the given value 
 * in their custom field(s) from the tree.
 * Recursive function.
 * 
 * @author Andreas Simon
 * @since 1.9
 * 
 * @param resource &$db reference to DB handler object
 * @param array &$tcase_tree reference to test case set/tree to filter
 * @param array &$cf_hash reference to selected custom field information
 * @param int $node_type_testsuite ID of node type for testsuites
 * @param int $node_type_testcase ID of node type for testcase
 * 
 * @return array $tcase_tree filtered tree structure
 * 
 * @internal revisions:
 * 
 * 20100702 - did some changes to logic in here and added a fix for array indexes
 */
function filter_by_cf_values(&$db, &$tcase_tree, &$cf_hash, $node_types)
{
	static $tables = null;
	static $debugMsg = null;
	
	$rows = null;
	if (!$debugMsg) {
		$tables = tlObject::getDBTables(array('cfield_design_values','nodes_hierarchy'));
		$debugMsg = 'Function: ' . __FUNCTION__;
	}
	
	$node_deleted = false;
	
	// This code is in parts based on (NOT simply copy/pasted)
	// some filter code used in testplan class.
	// Implemented because we have a tree here, 
	// not simple one-dimensional array of testcases like in tplan class.
	
	foreach ($tcase_tree as $key => $node) 
	{
		
		if ($node['node_type_id'] == $node_type_testsuite) 
		{
			$delete_suite = false;
			
			if (isset($node['childNodes']) && is_array($node['childNodes'])) {
				// node is a suite and has children, so recurse one level deeper			
				$tcase_tree[$key]['childNodes'] = filter_by_cf_values($db,$tcase_tree[$key]['childNodes'], 
				                                                      $cf_hash,$node_types); 
				
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
			//BUGID 2877 - Custom Fields linked to TC versions
			$sql = " /* $debugMsg */ SELECT CFD.value FROM {$tables['cfield_design_values']} CFD," .
				   " {$tables['nodes_hierarchy']} NH" .
				   " WHERE CFD.node_id = NH.id" .
				   " AND NH.parent_id = {$node['id']} ";
			// AND value in ('" . implode("' , '",$cf_hash) . "')";
		//BUGID 3995 Custom Field Filters not working properly since the cf_hash is array	
		if (isset($cf_hash)) 
		{	
			$countmain = 1;
			$cf_sql = '';
			foreach ($cf_hash as $cf_id => $cf_value) 
			{
				
				if ( $countmain != 1 ) 
				{
					$cf_sql .= " OR ";
				}
				// single value or array?
				if (is_array($cf_value)) 
				{
					$count = 1;
					foreach ($cf_value as $value) 
					{
						if ($count > 1) 
						{
							$cf_sql .= " AND ";
						}
						$cf_sql .= "( CFD.value LIKE '%{$value}%' AND CFD.field_id = {$cf_id} )";
						$count++;
						//print_r($count);
					}
				} else 
				{
					$cf_sql .= " ( CFD.value LIKE '%{$cf_value}%' ) ";
				}
				$countmain++;
			}
			$sql .=  " AND ({$cf_sql}) ";
		}

			$rows = $db->fetchColumnsIntoArray($sql,'value'); //BUGID 4115
			//if there exist as many rows as custom fields to be filtered by
			//the tc does meet the criteria
			$passed = (count($rows) == count($cf_hash)) ? true : false;
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
 * 
 * @param object &$tplan_mgr reference to test plan manager object
 * @param array &$tcase_set reference to test case set to filter
 * @param integer $tplan_id ID of test plan
 * @param array $filters filters to apply to test case set
 * @return array new tcase_set
 */
function filterStatusSetAtLeastOneOfActiveBuilds(&$tplan_mgr,&$tcase_set,$tplan_id,$filters) 
{
	echo __METHOD__;
	
	$safe_platform = intval($filters->setting_platform);
	$buildSet = array_keys($tplan_mgr->get_builds($tplan_id, testplan::ACTIVE_BUILDS));
	if( !is_null($buildSet) ) 
	{
		if( $safe_platform > 0 )
		{
			$hits = $tplan_mgr->getHitsSameStatusPartialOnPlatform($tplan_id,$safe_platform,
												    		       (array)$filters->filter_result_result); 
		}
		else
		{
			echo '<b><br>ALOP</b><br>';
			$hits = $tplan_mgr->getHitsSameStatusPartialALOP($tplan_id,(array)$filters->filter_result_result); 
		}
		
		if( is_null($hits) ) 
		{
			$tcase_set = array();
		} 
		else 
		{
			helper_filter_cleanup($tcase_set,$hits);
		}
	}
		
	return $tcase_set;
}


/**
 * filterStatusSetAllActiveBuilds()
 *
 * returns:
 *
 * test cases that has AT LEAST ONE of requested status
 * or combinations of requested status 
 * ON LAST EXECUTION ON ALL ACTIVE builds, for a PLATFORM
 *
 * For examples and more info read documentation regarding
 * getHits*() methods on testplan class.
 *
 *
 * @param object &$tplan_mgr reference to test plan manager object
 * @param array &$tcase_set reference to test case set to filter
 *				WILL BE MODIFIED HERE
 *
 * @param integer $tplan_id ID of test plan
 * @param array $filters filters to apply to test case set
 * 
 * @return array new tcase_set
 */
function filterStatusSetAllActiveBuilds(&$tplan_mgr,&$tcase_set,$tplan_id,$filters) 
{
	echo '<h1>' . __METHOD__ . '</h1>';
	$buildSet = array_keys($tplan_mgr->get_builds($tplan_id, testplan::ACTIVE_BUILDS));
	if( !is_null($buildSet) ) 
	{

		$safe_platform = intval($filters->setting_platform);
		if( $safe_platform > 0 )
		{
			$hits = $tplan_mgr->getHitsSameStatusFullOnPlatform($tplan_id,$safe_platform,
													  			(array)$filters->filter_result_result,$buildSet);
		}
		else
		{
			$hits = $tplan_mgr->getHitsSameStatusFullALOP($tplan_id,
													  	  (array)$filters->filter_result_result,$buildSet);
		}

		echo '<h1> hits </h1>';
		new dBug($hits);
		if( is_null($hits) ) 
		{
			$tcase_set = array();
		} 
		else 
		{
			helper_filter_cleanup($tcase_set,$hits);
			unset($hits);
		}
	}
	return $tcase_set;
}

/**
 * used by filter options:
 * 							result on specific build
 * 							result on current build
 *
 *  
 * @param object &$tplan_mgr reference to test plan manager object
 * @param array &$tcase_set reference to test case set to filter
 * @param integer $tplan_id ID of test plan
 * @param array $filters filters to apply to test case set
 *
 * @return array new tcase_set
 */
function filter_by_status_for_build(&$tplan_mgr,&$tcase_set,$tplan_id,$filters) 
{
	echo __METHOD__;
	
	$safe_platform = intval($filters->setting_platform);
	$safe_build = intval($filters->filter_result_build);
	if( $safe_platform > 0)
	{
		
		$hits = $tplan_mgr->getHitsStatusSetOnBuildPlatform($tplan_id,$safe_platform,$safe_build,
															(array)$filters->filter_result_result);
	}
	else
	{
		echo '<br>ALOP<br>';
		$hits = $tplan_mgr->getHitsStatusSetOnBuildALOP($tplan_id,$safe_build,
														(array)$filters->filter_result_result);
	}

	if( is_null($hits) ) 
	{
		$tcase_set = array();
	} 
	else 
	{
		helper_filter_cleanup($tcase_set,$hits);
	}
	
	return $tcase_set;
}

/**
 * filter testcases by the result of their latest execution
 * 
 * CAN NOT BE USED FOR NOT RUN because Not run is not saved on DB
 *
 * @param object &$db reference to database handler
 * @param object &$tplan_mgr reference to test plan manager object
 * @param array &$tcase_set reference to test case set to filter
 * @param integer $tplan_id ID of test plan
 * @param array $filters filters to apply to test case set
 * @return array new tcase_set
 */
function filter_by_status_for_latest_execution(&$tplan_mgr,&$tcase_set,$tplan_id,$filters) 
{

	$safe_tplan = intval($tplan_id);
	$safe_platform = intval($filters->setting_platform);
	
	if($safe_platform > 0)
	{
		$hits = $tplan_mgr->getHitsStatusSetOnLatestExecOnPlatform($safe_tplan,$safe_platform,
															       (array)$filters->filter_result_result);
	}
	else
	{
		$hits = $tplan_mgr->getHitsStatusSetOnLatestExecALOP($safe_tplan,(array)$filters->filter_result_result);
	}
	
	if( is_null($hits) ) 
	{
		$tcase_set = array();
	} 
	else 
	{
		helper_filter_cleanup($tcase_set,$hits);
	}
			
	return $tcase_set;
}


/**
 * 
 * @param object &$tplan_mgr reference to test plan manager object
 * @param array &$tcase_set reference to test case set to filter
 * @param integer $tplan_id ID of test plan
 * @param array $filters filters to apply to test case set
 * @return array new tcase_set
 */
function filter_not_run_for_any_build(&$tplan_mgr,&$tcase_set,$tplan_id,$filters) 
{

	$safe_platform = intval($filters->setting_platform);
	if( $safe_platform > 0)
	{
		$hits = $tplan_mgr->getHitsNotRunPartialOnPlatform($tplan_id,intval($filters->setting_platform));
	}
	else
	{
		$hits = $tplan_mgr->getHitsNotRunPartialALOP($tplan_id);
	}

	if( is_null($hits) ) 
	{
		$tcase_set = array();
	} 
	else 
	{
		helper_filter_cleanup($tcase_set,$hits);
	}
	
	return $tcase_set;
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

/**
 * Generate the necessary data object for the filtered requirement specification tree.
 * 
 * @author Andreas Simon
 * @param Database $db reference to database handler object
 * @param testproject $testproject_mgr reference to testproject manager object
 * @param int $testproject_id ID of the project for which the tree shall be generated
 * @param string $testproject_name Name of the test project
 * @param array $filters Filter settings which shall be applied to the tree, possible values are:
 *                       'filter_doc_id',
 *	                     'filter_title',
 *	                     'filter_status',
 *	                     'filter_type',
 *	                     'filter_spec_type',
 *	                      'filter_coverage',
 *	                     'filter_relation',
 *	                     'filter_tc_id',
 *	                     'filter_custom_fields'
 * @param array $options Further options which shall be applied on generating the tree
 * @return stdClass $treeMenu object with which ExtJS can generate the graphical tree
 */
function generate_reqspec_tree(&$db, &$testproject_mgr, $testproject_id, $testproject_name, 
                             $filters = null, $options = null) 
{

	$tables = tlObjectWithDB::getDBTables(array('requirements', 'req_versions', 
	                                            'req_specs', 'req_relations', 
	                                            'req_specs_revisions',
	                                            'req_coverage', 'nodes_hierarchy'));
	
	$tree_manager = &$testproject_mgr->tree_manager;
	
	$glue_char = config_get('testcase_cfg')->glue_character;
	$tcase_prefix=$testproject_mgr->getTestCasePrefix($testproject_id) . $glue_char;
	
	$req_node_type = $tree_manager->node_descr_id['testcase'];
	$req_spec_node_type = $tree_manager->node_descr_id['testsuite'];
	
	$map_nodetype_id = $tree_manager->get_available_node_types();
	$map_id_nodetype = array_flip($map_nodetype_id);
	
	$my = array();
	
	$my['options'] = array('for_printing' => 0,
	                       'exclude_branches' => null,
	                       'recursive' => true,
	                       'order_cfg' => array('type' => 'spec_order'));
	
	$my['filters'] = array('exclude_node_types' =>  array('testplan' => 'exclude me',
	                                                      'testsuite' => 'exclude me',
	                                                      'testcase' => 'exclude me',
	                                                      'requirement_spec_revision' => 'exclude me'),
	                       'exclude_children_of' => array('testcase' => 'exclude my children',
	                                                      'requirement' => 'exclude my children',
	                                                      'testsuite' => 'exclude my children'),
	                       'filter_doc_id' => null,
	                       'filter_title' => null,
	                       'filter_status' => null,
	                       'filter_type' => null,
	                       'filter_spec_type' => null,
	                       'filter_coverage' => null,
	                       'filter_relation' => null,
	                       'filter_tc_id' => null,
	                       'filter_custom_fields' => null);
	
	// merge with given parameters
	$my['options'] = array_merge($my['options'], (array) $options);
	$my['filters'] = array_merge($my['filters'], (array) $filters);
	
	$req_spec = $tree_manager->get_subtree($testproject_id, $my['filters'], $my['options']);
	
	$req_spec['name'] = $testproject_name;
	$req_spec['id'] = $testproject_id;
	$req_spec['node_type_id'] = $map_nodetype_id['testproject'];
	
	$filtered_map = get_filtered_req_map($db, $testproject_id, $testproject_mgr,
	                                     $my['filters'], $my['options']);
	
	$level = 1;
	$req_spec = prepare_reqspec_treenode($db, $level, $req_spec, $filtered_map, $map_id_nodetype,
	                                     $map_nodetype_id, $my['filters'], $my['options']);
		
	$menustring = null;
	$treeMenu = new stdClass();
	$treeMenu->rootnode = new stdClass();
	$treeMenu->rootnode->total_req_count = $req_spec['total_req_count'];
	$treeMenu->rootnode->name = $req_spec['name'];
	$treeMenu->rootnode->id = $req_spec['id'];
	$treeMenu->rootnode->leaf = isset($req_spec['leaf']) ? $req_spec['leaf'] : false;
	//$treeMenu->rootnode->text = $req_spec['name']; //not needed, accidentally duplicated
	$treeMenu->rootnode->position = $req_spec['position'];	    
	$treeMenu->rootnode->href = $req_spec['href'];
		
	// replace key ('childNodes') to 'children'
	if (isset($req_spec['childNodes']))
	{
		$menustring = str_ireplace('childNodes', 'children', 
		                           json_encode($req_spec['childNodes'])); 
	}

	if (!is_null($menustring))
	{
		// delete null elements for Ext JS
		$menustring = str_ireplace(array(':null',',null','null,','null'),
		                           array(':[]','','',''),
		                           $menustring); 
	}
	$treeMenu->menustring = $menustring; 
	
	return $treeMenu;
}
/**
 * Generate a filtered map with all fitting requirements in it.
 * 
 * @author Andreas Simon
 * @param Database $db reference to database handler object
 * @param int $testproject_id ID of the project for which the tree shall be generated
 * @param testproject $testproject_mgr reference to testproject manager object
 * @param array $filters Filter settings which shall be applied to the tree
 * @param array $options Further options which shall be applied on generating the tree
 * @return array $filtered_map map with all fitting requirements
 */
function get_filtered_req_map(&$db, $testproject_id, &$testproject_mgr, $filters, $options) {
	$filtered_map = null;
	$tables = tlObjectWithDB::getDBTables(array('nodes_hierarchy', 'requirements', 'req_specs',
	                                            'req_relations', 'req_versions', 'req_coverage',
	                                            'tcversions', 'cfield_design_values'));
	
	$sql = " SELECT R.id, R.req_doc_id, NH_R.name AS title, R.srs_id, " .
	       "        RS.doc_id AS req_spec_doc_id, NH_RS.name AS req_spec_title, " .
	       "        RV.version, RV.id AS version_id, NH_R.node_order, " .
	       "        RV.expected_coverage, RV.status, RV.type, RV.active, RV.is_open " .
	       " FROM {$tables['requirements']} R " .
	       " JOIN {$tables['nodes_hierarchy']} NH_R ON NH_R.id = R.id " .
	       " JOIN {$tables['nodes_hierarchy']} NH_RV ON NH_RV.parent_id = NH_R.id " .
	       " JOIN {$tables['req_versions']} RV ON RV.id = NH_RV.id " .
	       " JOIN {$tables['req_specs']} RS ON RS.id = R.srs_id " .
	       " JOIN {$tables['nodes_hierarchy']} NH_RS ON NH_RS.id = RS.id ";

	if (isset($filters['filter_relation'])) {
		$sql .= " JOIN {$tables['req_relations']} RR " .
		        " ON (RR.destination_id = R.id OR RR.source_id = R.id) ";
	}	
	
	if (isset($filters['filter_tc_id'])) {
		$tc_cfg = config_get('testcase_cfg');
		$tc_prefix = $testproject_mgr->getTestCasePrefix($testproject_id);
		$tc_prefix .= $tc_cfg->glue_character;
		
		$tc_ext_id = $db->prepare_int(str_replace($tc_prefix, '', $filters['filter_tc_id']));
		
		$sql .= " JOIN {$tables['req_coverage']} RC ON RC.req_id = R.id " .
		        " JOIN {$tables['nodes_hierarchy']} NH_T ON NH_T.id = RC.testcase_id " .
		        " JOIN {$tables['nodes_hierarchy']} NH_TV on NH_TV.parent_id = NH_T.id " .
		        " JOIN {$tables['tcversions']} TV ON TV.id = NH_TV.id " .
		        "                                    AND TV.tc_external_id = {$tc_ext_id} ";
	}
	
	if (isset($filters['filter_custom_fields'])) {
		$suffix = 1;
		
		foreach ($filters['filter_custom_fields'] as $cf_id => $cf_value) {
			$sql .= " JOIN {$tables['cfield_design_values']} CF{$suffix} " .
			        //BUGID 2877 -  Custom Fields linked to Req versions
			        " ON CF{$suffix}.node_id = RV.id " .
			        " AND CF{$suffix}.field_id = {$cf_id} ";
			
			// single value or array?
			if (is_array($cf_value)) {
				$sql .= " AND ( ";
				$count = 1;
				foreach ($cf_value as $value) {
					if ($count > 1) {
						$sql .= " OR ";
					}
					$sql .= " CF{$suffix}.value LIKE '%{$value}%' ";
					$count++;
				}
				$sql .= " ) ";
			} else {
				$sql .= " AND CF{$suffix}.value LIKE '%{$cf_value}%' ";
			}
			
			$suffix ++;
		}
	}
	
	$sql .= " WHERE RS.testproject_id = {$testproject_id} ";

	if (isset($filters['filter_doc_id'])) {
		$doc_id = $db->prepare_string($filters['filter_doc_id']);
		$sql .= " AND R.req_doc_id LIKE '%{$doc_id}%' OR RS.doc_id LIKE '%{$doc_id}%' ";
	}
	
	if (isset($filters['filter_title'])) {
		$title = $db->prepare_string($filters['filter_title']);
		$sql .= " AND NH_R.name LIKE '%{$title}%' ";
	}
	
	if (isset($filters['filter_coverage'])) {
		$coverage = $db->prepare_int($filters['filter_coverage']);
		$sql .= " AND expected_coverage = {$coverage} ";
	}
	
	if (isset($filters['filter_status'])) {
		$statuses = (array) $filters['filter_status'];
		foreach ($statuses as $key => $status) {
			$statuses[$key] = "'" . $db->prepare_string($status) . "'";
		}
		$statuses = implode(",", $statuses);
		$sql .= " AND RV.status IN ({$statuses}) ";
	}
	
	if (isset($filters['filter_type'])) {
		$types = (array) $filters['filter_type'];

		// BUGID 3671
		foreach ($types as $key => $type) {
			$types[$key] = $db->prepare_string($type);
		}
		$types = implode("','", $types);
		$sql .= " AND RV.type IN ('{$types}') ";
	}
	
	if (isset($filters['filter_spec_type'])) {
		$spec_types = (array) $filters['filter_spec_type'];

		// BUGID 3671
		foreach ($spec_types as $key => $type) {
			$spec_types[$key] = $db->prepare_string($type);
		}
		$spec_types = implode("','", $spec_types);
		$sql .= " AND RS.type IN ('{$spec_types}') ";
	}
	
	if (isset($filters['filter_relation'])) {
		$sql .= " AND ( ";
		$count = 1;
		foreach ($filters['filter_relation'] as $key => $rel_filter) {
			$relation_info = explode('_', $rel_filter);
			$relation_type = $db->prepare_int($relation_info[0]);
			$relation_side = isset($relation_info[1]) ? $relation_info[1] : null;
			$sql .= ($count == 1) ? " ( " : " OR ( ";
			
			if ($relation_side == "destination") {
				$sql .= " RR.destination_id = R.id ";
			} else if ($relation_side == "source") {
				$sql .= " RR.source_id = R.id ";
			} else {
				$sql .= " (RR.destination_id = R.id OR RR.source_id = R.id) ";
			}
			
			$sql .= " AND RR.relation_type = {$relation_type} ) ";
			$count++;
		}
		
		$sql .= " ) ";
	}
	
	$sql .= " ORDER BY RV.version DESC ";
	$filtered_map = $db->fetchRowsIntoMap($sql, 'id');
		
	return $filtered_map;
}

/**
 * Prepares nodes for the filtered requirement tree.
 * Filters out those nodes which are not in the given map and counts the remaining subnodes.
 * @author Andreas Simn
 * @param Database $db reference to database handler object
 * @param int $level gets increased by one for each sublevel in recursion
 * @param array $node the tree structure to traverse
 * @param array $filtered_map a map of filtered requirements, req that are not in this map will be deleted
 * @param array $map_id_nodetype array with node type IDs as keys, node type descriptions as values
 * @param array $map_nodetype_id array with node type descriptions as keys, node type IDs as values
 * @param array $filters
 * @param array $options
 * @return array tree structure after filtering out unneeded nodes
 */
function prepare_reqspec_treenode(&$db, $level, &$node, &$filtered_map, &$map_id_nodetype,
                                  &$map_nodetype_id, &$filters, &$options) {
	$child_req_count = 0;
	
	if (isset($node['childNodes']) && is_array($node['childNodes'])) {
		// node has childs, must be a specification (or testproject)
		foreach ($node['childNodes'] as $key => $childnode) {
			$current_childnode = &$node['childNodes'][$key];
			$current_childnode = prepare_reqspec_treenode($db, $level + 1, $current_childnode, 
			                                             $filtered_map, $map_id_nodetype,
			                                             $map_nodetype_id,
			                                             $filters, $options);
			
			// now count childnodes that have not been deleted and are requirements
			if (!is_null($current_childnode)) {
				switch ($current_childnode['node_type_id']) {
					case $map_nodetype_id['requirement']:
						$child_req_count ++;
					break;
					
					case $map_nodetype_id['requirement_spec']:
						$child_req_count += $current_childnode['child_req_count'];
					break;
				}
			}
		}
	}
	
	$node_type = $map_id_nodetype[$node['node_type_id']];
	
	$delete_node = false;
	
	switch ($node_type) {
		case 'testproject':
			$node['total_req_count'] = $child_req_count;	
		break;
		
		case 'requirement_spec':
			// add requirement count
			$node['child_req_count'] = $child_req_count;
			// delete empty specs
			if (!$child_req_count) {
				$delete_node = true;
			}
		break;
		
		case 'requirement':
			// delete node from tree if it is not in $filtered_map
			if (is_null($filtered_map) || !array_key_exists($node['id'], $filtered_map)) {
				$delete_node = true;
			}
		break;
	}
	
	if ($delete_node) {
		unset($node);
		$node = null;
	} else {
		$node = render_reqspec_treenode($db, $node, $filtered_map, $map_id_nodetype);
	}
	
	return $node;
}

/**
 * Prepares nodes in the filtered requirement tree for displaying with ExtJS.
 * @author Andreas Simon
 * @param Database $db reference to database handler object
 * @param array $node the object to prepare
 * @param array $filtered_map a map of filtered requirements, req that are not in this map will be deleted
 * @param array $map_id_nodetype array with node type IDs as keys, node type descriptions as values
 * @return array tree object with all needed data for ExtJS tree
 */
function render_reqspec_treenode(&$db, &$node, &$filtered_map, &$map_id_nodetype) {
	static $js_functions;
	static $forbidden_parents;
	
	if (!$js_functions) {
		$js_functions = array('testproject' => 'TPROJECT_REQ_SPEC_MGMT',
		                      'requirement_spec' =>'REQ_SPEC_MGMT',
		                      'requirement' => 'REQ_MGMT');
		
		$req_cfg = config_get('req_cfg');
		$forbidden_parents['testproject'] = 'none';
		$forbidden_parents['requirement'] = 'testproject';
		$forbidden_parents['requirement_spec'] = 'requirement_spec';
		if($req_cfg->child_requirements_mgmt) {
			$forbidden_parents['requirement_spec'] = 'none';
		} 
	}
	
	$node_type = $map_id_nodetype[$node['node_type_id']];
	$node_id = $node['id'];
	
	$node['href'] = "javascript:{$js_functions[$node_type]}({$node_id});";
	$node['text'] = htmlspecialchars($node['name']);
	$node['leaf'] = false; // will be set to true later for requirement nodes
	$node['position'] = isset($node['node_order']) ? $node['node_order'] : 0;
	$node['cls'] = 'folder';
	
	// custom Properties that will be accessed by EXT-JS using node.attributes 
	$node['testlink_node_type']	= $node_type;
	$node['forbidden_parent'] = $forbidden_parents[$node_type];
	$node['testlink_node_name'] = $node['text'];
	
	switch ($node_type) {
		case 'testproject':			
		break;
		
		case 'requirement_spec':
			// get doc id from filtered array, it's already stored in there
			$doc_id = '';
			foreach($node['childNodes'] as $child) {
				if (!is_null($child)) {
					$child_id = $child['id'];
					if (isset($filtered_map[$child_id])) {
						$doc_id = htmlspecialchars($filtered_map[$child_id]['req_spec_doc_id']);
					}
					break; // only need to get one child for this
				}
			}
			// BUGID 3765: load doc ID with  if this req spec has no direct req child nodes.
			// Reason: in these cases we do not have a parent doc ID in $filtered_map 
			if ($doc_id == '') {
				static $req_spec_mgr = null;
				if (!$req_spec_mgr) {
					$req_spec_mgr = new requirement_spec_mgr($db);
				}
				$tmp_spec = $req_spec_mgr->get_by_id($node_id);
				$doc_id = $tmp_spec['doc_id'];
				unset($tmp_spec);
			}
			
			$count = $node['child_req_count'];
			$node['text'] = "{$doc_id}:{$node['text']} ({$count})";
		break;
		
		case 'requirement':
			$node['leaf']	= true;
			$doc_id = htmlspecialchars($filtered_map[$node_id]['req_doc_id']);
			$node['text'] = "{$doc_id}:{$node['text']}";
		break;
	}
	
	return $node;       
}


/**
 * 
 * 
 */
function apply_status_filters($tplan_id,&$items,&$fobj,&$tplan_mgr,$statusCfg)
{
	$methods = config_get('execution_filter_methods');
	$methods = $methods['status_code'];
	
	$ffn = array($methods['any_build'] => 'filterStatusSetAtLeastOneOfActiveBuilds',
		         $methods['all_builds'] => 'filterStatusSetAllActiveBuilds',
		         $methods['specific_build'] => 'filter_by_status_for_build',
		         $methods['current_build'] => 'filter_by_status_for_build',
		         $methods['latest_execution'] => 'filter_by_status_for_latest_execution');
	
	$f_method = isset($fobj->filter_result_method) ? $fobj->filter_result_method : null;
	$f_result = isset($fobj->filter_result_result) ? $fobj->filter_result_result : null;
	$f_result = (array)$f_result;

	echo __METHOD__ . '<br>';
	// new dBug($methods);
	new dBug($fobj);
	
	// if "any" was selected as filtering status, don't filter by status
	if (in_array($statusCfg['all'], $f_result)) 
	{
		$f_result = null;
	}

	if (!is_null($f_method) && isset($ffn[$f_method])) 
	{
		// special case: 
		// filtering by "not run" status in any build
		// filtering by "not run" status in specific
		//
		// we change filter function
		if (in_array($statusCfg['not_run'], $f_result)) 
		{
			$ffn[$methods['any_build']] = 'filter_not_run_for_any_build';
		    $ffn[$methods['specific_build']] = 'filter_by_status_for_build';
		}
		
		// special case: when filtering by "current build", we set the build to filter with
		// to the build chosen in settings instead of the one in filters
		if ($f_method == $methods['current_build']) 
		{
			$fobj->filter_result_build = $fobj->setting_build;
		}
		
		$items = $ffn[$f_method]($tplan_mgr, $items, $tplan_id, $fobj);
	}
	return $items; 
}

/**
 * 
 * 
 */
function update_status_for_colors(&$dbHandler,&$items,$context,$statusCfg)
{
	$tables = tlObject::getDBTables(array('executions','nodes_hierarchy'));
	$dummy = current($items);
	$key2scan = array_keys($items);
	$keySet = null;
	foreach($key2scan as $fx)
	{
		$keySet[] = $items[$fx]['tcversion_id'];
	}
	
	extract($context);  // magic to create single variables
	
	$sql = 	" SELECT E.status, NH_TCV.parent_id AS tcase_id " .
			" FROM {$tables['executions']} E " .
			" JOIN {$tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = E.tcversion_id " .
			" JOIN " .
			" ( SELECT MAX(E2.id) AS last_exec_id " .
			"   FROM {$tables['executions']} E2 " .
			"   WHERE testplan_id = {$tplanID} " .
			" 	AND tcversion_id IN (" . implode(',', $keySet) . ") " .
			" 	AND platform_id = {$dummy['platform_id']} " .
			" 	AND build_id = {$buildID} " .
			" 	GROUP BY testplan_id,tcversion_id,platform_id,build_id ) AS EY " .
			" ON E.id = EY.last_exec_id ";

	$result = null;
	$rs = $dbHandler->fetchRowsIntoMap($sql,'tcase_id');
	
	if( !is_null($rs) )
	{
		foreach($key2scan as $tcase_id)
		{
			$rr = isset($rs[$tcase_id]['status']) && !is_null($rs[$tcase_id]['status']) ? 
				  $rs[$tcase_id]['status'] : $statusCfg['not_run'];

			if ($rr != $items[$tcase_id]['exec_status']) 
			{
				$items[$tcase_id]['exec_status'] = $rr;
			}
		}
	}
	
}




function generateTestSpecTreeNEW(&$db,$tproject_id, $tproject_name,$linkto,$filters=null,$options=null)
{
	// echo __FUNCTION__;
		$chronos[] = microtime(true);
	
	$tables = tlObjectWithDB::getDBTables(array('tcversions','nodes_hierarchy'));

	$my = array();
	
	$my['options'] = array('forPrinting' => 0, 'hideTestCases' => 0, 
	                       'tc_action_enabled' => 1, 'viewType' => 'testSpecTree');
	

	$my['filters'] = array('keywords' => null, 'testplan' => null);

	$my['options'] = array_merge($my['options'], (array)$options);
	$my['filters'] = array_merge($my['filters'], (array)$filters);
	// new dBug($my['filters']);
	
	$treeMenu = new stdClass(); 
	$treeMenu->rootnode = null;
	$treeMenu->menustring = '';
	
	$resultsCfg = config_get('results');
	$glueChar = config_get('testcase_cfg')->glue_character;
	$menustring = null;
	
	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;	
	
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
	$status_descr_code=$resultsCfg['status_code'];
	$status_code_descr=$resultsCfg['code_status'];
	
	$decoding_hash=array('node_id_descr' => $hash_id_descr,
		                 'status_descr_code' =>  $status_descr_code,
		                 'status_code_descr' =>  $status_code_descr);
	

	$tcase_prefix = $tproject_mgr->getTestCasePrefix($tproject_id) . $glueChar;
	$test_spec = getTestSpecTree($tproject_id,$tproject_mgr,$filters);
		$chronos[] = microtime(true);
		$tnow = end($chronos);
		$tprev = prev($chronos);
		$t_elapsed = number_format( $tnow - $tprev, 4);
		echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (get_subtree()):' . $t_elapsed .'<br>';
		reset($chronos);
		//new dBug($test_spec);
		//die('AFTER get TEST SPEC');	


	// new dBug($test_spec);
	

	// Added root node for test specification -> testproject
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];

	
	$map_node_tccount=array();
	$tplan_tcs=null;
	
	if($test_spec)
	{
	    if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes'])) 
	    {
	    	$test_spec['childNodes'] = filter_by_cf_values($db, $test_spec['childNodes'],
	    												   $my['filters']['filter_custom_fields'],$hash_descr_id);
	    }
		
	    $pnFilters = array('keywords' => $my['filters']['filter_keywords'],
	    				   'keywords_filter_type' => $my['filters']['filter_keywords_filter_type']);
		$pnOptions = array('hideTestCases' => $my['options']['hideTestCases']);
		
		// Important/CRITIC: 
		// prepareTestSpecNode() will make changes to $test_spec like filtering by test case keywords.
		$testcase_counters = prepareTestSpecNode($tproject_mgr,$tproject_id,$test_spec,$map_node_tccount,
												 $pnFilters,$pnOptions);

		$chronos[] = microtime(true);
		$tnow = end($chronos);
		$tprev = prev($chronos);
		$t_elapsed = number_format( $tnow - $tprev, 4);
		echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (get_subtree()):' . $t_elapsed .'<br>';
		reset($chronos);	

		if( is_null($test_spec) )
		{
			$test_spec['name'] = $tproject_name;
			$test_spec['id'] = $tproject_id;
			$test_spec['node_type_id'] = $hash_descr_id['testproject'];
		}

		foreach($testcase_counters as $key => $value)
		{
			$test_spec[$key] = $testcase_counters[$key];
		}

		
		$showTestCaseID = config_get('treemenu_show_testcase_id');
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
 * 
 * 
 */
function getTestSpecTree($tprojectID,&$tprojectMgr,&$fObj)
{
	
	$flt = array();
	$flt['exclude_branches'] = isset($fObj['filter_toplevel_testsuite']) && is_array($fObj['filter_toplevel_testsuite']) ?
	                    	   $fObj['filter_toplevel_testsuite'] : null;
	
	

	$flt['testcase_name'] = null;
	$flt['testcase_id'] = null;
	$flt['execution_type'] = null;

	if( isset($fObj['filter_testcase_name']) && !is_null($fObj['filter_testcase_name']) )
	{
		if( ($dummy = trim($fObj['filter_testcase_name'])) != '' )
		{
			$flt['testcase_name'] = $dummy;
		}
	}
	
	if( isset($fObj['filter_tc_id']) && !is_null($fObj['filter_tc_id']) )
	{
		$flt['testcase_id'] = intval($fObj['filter_tc_id']);
	}
	
	if( isset($fObj['filter_execution_type']) && !is_null($fObj['filter_execution_type']) )
	{
		$flt['execution_type'] = intval($fObj['filter_execution_type']);
	}
	
	// new dBug($fObj);
	
	$opt = array('recursive' => true,'exclude_testcases' => false);
	$items = $tprojectMgr->getTestSpec($tprojectID,$flt,$opt); 

	// new dBug($items);
	// die();
	return $items;
}


/**
 * 
 * 
 */
function prepareTestSpecNode(&$tprojectMgr,$tprojectID,&$node,&$map_node_tccount,$filters=null,$options=null)
{
	
	static $status_descr_list;
	static $debugMsg;
    static $tables;
    static $my;
    static $filtersApplied;
	static $decoding_info;
	static $tcFilterByKeywords;
	static $doFilterOn;

	if (!$tables)
	{
  	    $debugMsg = 'Class: ' . __CLASS__ . ' - ' . 'Method: ' . __FUNCTION__ . ' - ';
        $tables = tlObjectWithDB::getDBTables(array('tcversions','nodes_hierarchy','testplan_tcversions'));
		$decoding_info = array('node_id_descr' => 
							   array_flip($tprojectMgr->tree_manager->get_available_node_types()));
		$my = array();
		$my['options'] = array('hideTestCases' => 0);
		$my['filters'] = array('keywords' => null);

		$my['options'] = array_merge($my['options'], (array)$options);
		$my['filters'] = array_merge($my['filters'], (array)$filters);
		
		if( ($doFilterOn['keywords'] = !is_null($my['filters']['keywords'])) )
		{
			$tcFilterByKeywords = $tprojectMgr->getTCasesFilteredByKeywords($tprojectID,$my['filters']['keywords'],
			           											    		$my['filters']['keywords_filter_type']);
			if( is_null($tcFilterByKeywords) )
			{
				// tree will be empty
				$node = null;
				$tcase_counters['testcase_count'] = 0;
				return($tcase_counters);
			}
		}
		
		// Critic for logic that prune empty branches
		$filtersApplied = $doFilterOn['keywords'];
	}
		
	$tcase_counters['testcase_count'] = 0;
	$node_type = isset($node['node_type_id']) ? $decoding_info['node_id_descr'][$node['node_type_id']] : null;

	if($node_type == 'testcase')
	{
		if( $my['options']['hideTestCases'] ||
			($doFilterOn['keywords'] && !isset($tcFilterByKeywords[$node['id']])) )
		{
			$node = null;
		}
		else
		{
			// needed to avoid problems when using json_encode with EXTJS
			unset($node['childNodes']);
			$node['leaf']=true;
			$tcase_counters['testcase_count'] = 1;
		}
	}  // if($node_type == 'testcase')
	
	
	// ================================================================================
	if( !is_null($node) && isset($node['childNodes']) && is_array($node['childNodes']) )
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
			
			$counters_map = prepareTestSpecNode($tprojectMgr,$tprojectID,$current,$map_node_tccount);
			$tcase_counters['testcase_count'] += $counters_map['testcase_count'];   
		}
		$node['testcase_count'] = $tcase_counters['testcase_count'];
		
		if (isset($node['id']))
		{
			$map_node_tccount[$node['id']] = array(	'testcount' => $node['testcase_count'],
				                                    'name' => $node['name']);
		}

        // node must be destroyed if empty had we have using filtering conditions
		if( $filtersApplied && 
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
		if( $filtersApplied )
		{
			$node = null;
		}	
	}

	return $tcase_counters;
}

/**
 *
 *
 */
function helper_filter_cleanup(&$itemSet,$hits)
{
	$key2remove = null;
	foreach($itemSet as $tcase_id => $dummy) 
	{
		if( !isset($hits[$tcase_id]) ) 
		{
			$key2remove[]=$tcase_id;
		}
	}
	if( !is_null($key2remove) ) 
	{
		foreach($key2remove as $key) 
		{
			unset($itemSet[$key]); 
		}
	}
}
?>