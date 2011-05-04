<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package 	TestLink
 * @author 		Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright 	2004-2009, TestLink community 
 * @version    	CVS: $Id: specview.php,v 1.72.2.2 2010/12/09 15:25:30 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 *
 *  20101209 - asimon - exchanged strpos by stripos to make search case insensitive
 *	20101118 - amitkhullar - BUGID 4024 Filtering issue 
 *	20101101 - franciscom - improved $pfFilters contruction to avoid warning in event viewer
 *	20101026 - franciscom - BUGID 3889: Add Test Cases to Test plan - checks with test case id and 
 *										test case name filters.
 *	20101025 - franciscom - BUGID 3889: Add Test Cases to Test plan - Right pane does not honor custom field filter
 *	20101024 - franciscom - getTestSpecFromNode() refactoring
 *							BUGID 3932: Add test case to test plan - Execution type filter does not affect right pane
 *
 *							method renamed to getFilteredSpecView()
 *							BUGID 3934: Assign Test Case Execution - Execution type filter does not affect right pane
 *							BUGID 3936: Assign Test Case Execution - Right pane does not reflect custom field filter.
 *
 *
 *  20100911 - asimon - BUGID 3768
 *  20100726 - asimon - BUGID 3628: in addLinkedVersionsInfo(), a missing [0] in condition 
 *                                  caused missing priority
 *  20100721 - asimon - BUGID 3406 - added $options for new user assignments per build to 
 *                                   gen_spec_view(), getFilteredLinkedVersions(), 
 *                                   and keywordFilteredSpecView()
 *  
 *	20100417 - franciscom - BUGID 2498  Add test case to test plan - Filter Test Cases based on Test Importance
 *  20100411 - franciscom - BUGID 2797 - filter by test case execution type
 *
 *  20100218 - asimon - BUGID 3026 - added parameter $testcaseFilter on keywordFilteredSpecView
 *						to include functionality previously used on tc_exec_assignment.php
 * 						to show only testcases present in filter argument
 *	20100119 - franciscom - addCustomFieldsToView() - missing work on platforms
 *	20090808 - franciscom - gen_spec_view() interface changes + refactoring
 *	20090325 - franciscom - added new info about when and who has linked a tcversion
 *	20090325 - franciscom - BUGID - better implementation of BUGID 1497
 *	20081109 - franciscom - fixed filter on getTestSpecFromNode()
 *	                        fixed minor bug on $tsuite_tcqty processing
 *	                        added new value for spec_view_type='uncoveredtestcases'.
 *	
 *	20081030 - franciscom - created removeEmptyTestSuites(), removeEmptyBranches() to refactor.
 *	                        refactored use of tproject_id on gen_spec_view()
 *	
 *	20081019 - franciscom - removed new option to prune empty test suites
 *	                        till we understand were this will be used.
 *	                        In today implementation causes problems
 *	                        Added logic to compute total count of test cases
 *	                        for every test suite in a branch, to avoid use
 *	                        of map_node_tccount argument
 *
 *	20080919 - franciscom - BUGID 1716
 *	20080811 - franciscom - BUGID 1650 (REQ)
 *	20080422 - franciscom - BUGID 1497
 *	Suggested by Martin Havlat execution order will be set to external_id * 10
 *	for test cases not linked yet
 *           
 **/ 

/**
 * Generate data for Test Specification
 * 
 * @param string $spec_view_type can get one of the following values:
 *                        'testproject','testplan','uncoveredtestcases'
 * 
 *                        This setting change the processing done
 *                        to get the keywords.
 *                        And indicates the type of id (testproject/testplan)
 *                        contained in the argument tobj_id.
 *                        when using uncoveredtestcases tobj_id = testproject id
 * @param integer $tobj_id can be a testproject id, or testplan id.
 * @param integer $id node id, that we are using as root for the view we want to build
 * @param string $name
 * @param array $linked_items  map where key=testcase_id
 * 		value map with following keys:
 *                              [testsuite_id] => 2732            
 *                              [tc_id] => 2733        
 *                              [z] => 100  ---> nodes_hierarchy.order             
 *                              [name] => TC1          
 *                              [tcversion_id] => 2734 
 *                              [feature_id] => 9      --->> testplan_tcversions.ID
 *                              [execution_order] => 10
 *                              [version] => 1         
 *                              [active] => 1          
 *                              [external_id] => 1     
 *                              [exec_id] => 1         
 *                              [tcversion_number] => 1
 *                              [executed] => 2734     
 *                              [exec_on_tplan] => 2735
 *                              [user_id] =>           
 *                              [type] =>              
 *                              [status] =>            
 *                              [assigner_id] =>       
 *                              [urgency] => 2    IMPORTANT: exists ONLY FOR LINKED TEST CASES     
 *                              [exec_status] => b
 *                              [priority] => 4	// urgency*importance IMPORTANT: exists ONLY FOR LINKED TEST CASES
 *    
 * @param array $map_node_tccount
 * @TODO probably this argument ($map_node_tccount) is not needed, but it will depend
 * 			of how this feature (gen_spec_view) will be used on other TL areas.
 *
 * @param map $filters keys		
 *                     [keyword_id] default 0
 *                     [tcase_id] default null, can be an array
 *
 * @param map $options keys
 * 					   [write_button_only_if_linked] default 0
 *		               [prune_unlinked_tcversions]: default 0.
 *                     		Useful when working on spec_view_type='testplan'.
 *                      	1 -> will return only linked tcversion
 *                   		0 -> returns all test cases specs.
 *		               [add_custom_fields]: default=0
 *							useful when working on spec_view_type='testproject'
 *							when doing test case assign to test plans.
 *                            1 -> for every test case cfields of area 'testplan_design'
 *                                 will be fetched and displayed.
 *                            0 -> do nothing
 * 
 *		[$tproject_id]: default = null
 *				useful to improve performance in custom field method calls
 *				when add_custom_fields=1.
 *
 * 
 * @return array every element is an associative array with the following
 *       structure: (to get last updated info add debug code and print_r returned value)
 *       [testsuite] => Array( [id] => 28
 *                             [name] => TS1 )
 *       [testcases] => Array(  [2736] => Array
 *                              (
 *                                  [id] => 2736
 *                                  [name] => TC2
 *                                  [tcversions] => Array
 *                                      (
 *                                          [2738] => 2   // key=tcversion id,value=version
 *                                          [2737] => 1
 *                                      )
 *                                  [tcversions_active_status] => Array
 *                                      (
 *                                          [2738] => 1  // key=tcversion id,value=active status
 *                                          [2737] => 1
 *                                      )
 *                                  [tcversions_execution_type] => Array
 *                                      (
 *                                          [2738] => 1
 *                                          [2737] => 2
 *                                      )
 *                                  [tcversions_qty] => 2
 *                                  [linked_version_id] => 2737
 *                                  [executed] => no
 *                                  [user_id] => 0       ---> !=0 if execution has been assigned
 *                                  [feature_id] => 12   ---> testplan_tcversions.id
 *                                  [execution_order] => 20
 *                                  [external_id] => 2
 *                                  [linked_ts] => 2009-06-10 23:00
 *                                  [linked_by] => 2
 *                                  [priority] => HIGH, MEDIUM or LOW
 *                              )
 *                                [81] => Array( [id] => 81
 *                                           [name] => TC88)
 *                                           ...
 *                                           )
 *        [level] = 
 *     [write_buttons] => yes or no
 *     level and write_buttons are used to generate the user interface
 * 
 *     Warning:
 *     if the root element of the spec_view, has 0 test => then the default
 *     structure is returned ( $result = array('spec_view'=>array(), 'num_tc' => 0))
 * 
 * @internal Revisions:
 * 
 *  20100721 - asimon - BUGID 3406 - added user_assignments_per_build to options
 *  
 *  20090808 - franciscom - changed interface to reduce number of arguments
 *
 *	20070707 - franciscom - BUGID 921 - problems with display order in execution screen
 *	20070630 - franciscom - added new logic to include in for inactive test cases, testcase version id.
 *			This is needed to show testcases linked to testplans, but after be linked to
 *			test plan, has been set to inactive on test project.
 *	20061105 - franciscom - added new data on output: [tcversions_qty]
 *                        used in the logic to filter out inactive tcversions,
 *                        and inactive test cases.
 *                        Counts the quantity of active versions of a test case.
 *                        If 0 => test case is considered INACTIVE
 * 	20090625 - Eloff - added priority output
 */

function gen_spec_view(&$db, $spec_view_type='testproject', $tobj_id, $id, $name, &$linked_items,
                       $map_node_tccount, $filters=null, $options = null, $tproject_id = null)
{

	$out = array(); 
	$result = array('spec_view'=>array(), 'num_tc' => 0, 'has_linked_items' => 0);

	$my = array();
	$my['options'] = array('write_button_only_if_linked' => 0,'prune_unlinked_tcversions' => 0,
	                       'add_custom_fields' => 0) + (array)$options;

	// BUGID 2797 - filter by test case execution type
	$my['filters'] = array('keywords' => 0, 'testcases' => null ,'exec_type' => null, 
						   'importance' => null, 'cfields' => null);
	foreach( $my as $key => $settings)
	{
		if( !is_null($$key) && is_array($$key) )
		{
			$my[$key] = array_merge($my[$key],$$key);
		}
	}	             

	$write_status = $my['options']['write_button_only_if_linked'] ? 'no' : 'yes';
	$is_tplan_view_type=$spec_view_type == 'testplan' ? 1 : 0;
	$is_uncovered_view_type = ($spec_view_type == 'uncoveredtestcases') ? 1 : 0;
	
	if( !$is_tplan_view_type && is_null($tproject_id) )
	{
		$tproject_id = $tobj_id;
	}
	
	$testplan_id = $is_tplan_view_type ? $tobj_id : null;
	
	
	$tcase_mgr = new testcase($db); 
	$hash_descr_id = $tcase_mgr->tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);

	// BUGID 2797 - filter by test case execution type
	$key2map = array('keyword_id' => 'keywords', 'tcase_id' => 'testcases', 
	                 'execution_type' => 'exec_type', 'importance' => 'importance',
		             'cfields' => 'cfields','tcase_name' => 'tcase_name' );

	$pfFilters = array('tcase_node_type_id' => $hash_descr_id['testcase']);
	foreach($key2map as $tk => $fk)
	{
		$pfFilters[$tk] = isset($my['filters'][$fk]) ? $my['filters'][$fk] : null;
	}
	
	// $pfFilters = array('keyword_id' => $my['filters']['keywords'], 
	//                    'tcase_id' => $my['filters']['testcases'], 
	// 	               'tcase_node_type_id' => $hash_descr_id['testcase'],
	// 	               'execution_type' => $my['filters']['exec_type'],
	// 	               'importance' => $my['filters']['importance'],
	// 	               'cfields' => $my['filters']['cfields'],'tcase_name' => $my['filters']['tcase_name'] );
		             
	$test_spec = getTestSpecFromNode($db,$tcase_mgr,$linked_items,$tobj_id,$id,$spec_view_type,$pfFilters);
	
	$platforms = getPlatforms($db,$tproject_id,$testplan_id);
	$idx = 0;
	$a_tcid = array();
	$a_tsuite_idx = array();
	if(count($test_spec))
	{
		$cfg = array('node_types' => $hash_id_descr, 'write_status' => $write_status,
		             'is_uncovered_view_type' => $is_uncovered_view_type);
		             
      	list($a_tcid,$a_tsuite_idx,$tsuite_tcqty,$out) = buildSkeleton($id,$name,$cfg,$test_spec,$platforms);
	} 

	// This code has been replace (see below on Remove empty branches)
	// Once we have created array with testsuite and children testsuites
	// we are trying to remove nodes that has 0 test case count.
	// May be this can be done (as noted by schlundus during performance
	// analisys done on october 2008) in a better way, or better can be absolutely avoided.
	// 
	// This process is needed to prune whole branches that are empty
	// Need to look for every call in TL and understand if this can be removed
	//
	if(!is_null($map_node_tccount))
	{
		foreach($out as $key => $elem)
		{
			if(isset($map_node_tccount[$elem['testsuite']['id']]) &&
					$map_node_tccount[$elem['testsuite']['id']]['testcount'] == 0)  
			{
				// why not unset ?
				$out[$key]=null;
			}
		}
	}
	
	// Collect information related to linked testcase versions
	if(!is_null($out) && count($out) > 0 && !is_null($out[0]) && count($a_tcid))
	{
		$tcaseSet = $tcase_mgr->get_by_id($a_tcid,testcase::ALL_VERSIONS);
		$result = addLinkedVersionsInfo($tcaseSet,$a_tsuite_idx,$out,$linked_items);
	}
	
	// Try to prune empty test suites, to reduce memory usage and to remove elements
	// that do not need to be displayed on user interface.
	if( count($result['spec_view']) > 0)
	{
		removeEmptyTestSuites($result['spec_view'],$tcase_mgr->tree_manager,
			                  ($my['options']['prune_unlinked_tcversions'] && $is_tplan_view_type),$hash_descr_id);
	}
	
	// Remove empty branches
	// Loop to compute test case qty ($tsuite_tcqty) on every level and prune test suite branchs that are empty
	if( count($result['spec_view']) > 0)
	{
		removeEmptyBranches($result['spec_view'],$tsuite_tcqty);
	}   
	
	
	
	/** @TODO: maybe we can integrate this into already present loops above? */
	// This is not right condition for identifing an empty test suite for the porpouse
	// of gen_spec_view(), because for following structure
	// TS1 
	//  \--- TS2
	//        \--TC1
	//        \--TC2
	//
	//  \--- TS3 
	//        \-- TXX
	//
	// When we are displaying a Test Specification we want to see previous structure
	// But if we apply this criteria for empty test suite, TS1 results empty and will
	// be removed -> WRONG
	//
	// Need to understand when this feature will be needed and then reimplement
	//
	// if ($prune_empty_tsuites)
	// {
	// 	foreach($result['spec_view'] as $key => $value) 
	//     {
	//    		if(is_null($value) || !isset($value['testcases']) || !count($value['testcases']))
	//       		unset($result['spec_view'][$key]);
	//     }
	// }
	
	// #1650 We want to manage custom fields when user is doing test case execution assigment
	if( count($result['spec_view']) > 0 && $my['options']['add_custom_fields'])
	{    
		addCustomFieldsToView($result['spec_view'],$tproject_id,$tcase_mgr);
	}
	// --------------------------------------------------------------------------------------------
	unset($tcase_mgr);
	
	// with array_values() we reindex array to avoid "holes"
	$result['spec_view']= array_values($result['spec_view']);
 	return $result;
}


/**
 * get linked versions filtered by Keyword ID
 * Filter is done ONLY on attributes THAT ARE COMMON to ALL test case versions,
 * because (till now) while adding/removing test cases user works on Test Spec Tree
 * and filter applied to this tree acts on:
 *
 * 1. attributes COMMON to all versions
 * 2. attributes present ON LAST ACTIVE version.
 *	
 * But do no make considerations regarding versions linked to test plan
 * DEV NOTE: may be this has to be changed in future ?	
 *
 * @param ref $argsObj: stdClass object with information about filters
 * @param ref $tplanMgr: test plan manager object
 * @param ref $tcaseMgr: test case manager object
 *  
 *
 * @internal Revisions:
 *	
 *  20100721 - asimon - BUGID 3046, added $options
 *	20080919 - franciscom - BUGID 2716
 *
 */
function getFilteredLinkedVersions(&$argsObj, &$tplanMgr, &$tcaseMgr, $options = null)
{
	$doFilterByKeyword=(!is_null($argsObj->keyword_id) && $argsObj->keyword_id > 0) ? true : false;
	
	// Multiple step algoritm to apply keyword filter on type=AND
	// get_linked_tcversions filters by keyword ALWAYS in OR mode.
	//
	// BUGID 2797 - filter by test case execution type
	// $filters = array('keyword_id' => $argsObj->keyword_id, 'exec_type' => $argsObj->executionType);
	$filters = array('keyword_id' => $argsObj->keyword_id);
	$options = array('output' => 'mapOfArray') + (array)$options;
	$tplan_tcases = $tplanMgr->get_linked_tcversions($argsObj->tplan_id, $filters, $options);
	
	// BUGID 2716
	if( !is_null($tplan_tcases) && $doFilterByKeyword && $argsObj->keywordsFilterType == 'AND')
	{
		$filteredSet=$tcaseMgr->filterByKeyword(array_keys($tplan_tcases),
			                                    $argsObj->keyword_id,$argsObj->keywordsFilterType);
		
		$testCaseSet=array_keys($filteredSet);   
	    $filters = array('tcase_id' => $testCaseSet);
		$tplan_tcases = $tplanMgr->get_linked_tcversions($argsObj->tplan_id, $filters, $options);
	}
	
	return $tplan_tcases; 
}


/**
 * 
 * @param obj $dbHandler
 * @param obj $argsObj: user input
 * @param obj $argsObj: user input
 * @param obj $tplanMgr: test plan manager
 * @param obj $tcaseMgr: test case manager
 * @param map $filters:  keys keywordsFilter, testcaseFilter,assignedToFilter,
 *							  executionTypeFilter, cfieldsFilter
 *
 *						 IMPORTANT NOTICE: not all filters are here, other arrive via argsObj
 * @param map $options:  keys  ??
 *						 USED TO PASS options to other method called here -> see these method docs.
 *
 * @internal revisions:
 *
 * 20101024 - franciscom - name changed because was misleading, this do lot of filters
 *						   interface changed.
 *
 * 20100721 - asimon - BUGID 3406 - added $options for new user assignments per build
 * 20100218 - asimon - BUGID 3026 - added parameter $testcaseFilter to include functionality
 * 						previously used on tc_exec_assignment.php
 * 						to show only testcases present in filter argument
 *
 */
function getFilteredSpecView(&$dbHandler, &$argsObj, &$tplanMgr, &$tcaseMgr, $filters=null, $options=null) 
{
	$tprojectMgr = new testproject($dbHandler); 
	$tsuite_data = $tcaseMgr->tree_manager->get_node_hierarchy_info($argsObj->id);    
	
	$my = array();  // some sort of local scope
	$my['filters'] = array('keywordsFilter' => null, 'testcaseFilter' => null,
						   'assignedToFilter' => null,'executionTypeFilter' => null);
	$my['filters'] = array_merge($my['filters'], (array)$filters);

	$my['options'] = array('write_button_only_if_linked' => 1, 'prune_unlinked_tcversions' => 1);
	$my['options'] = array_merge($my['options'],(array)$options);
	
	// This does filter on keywords ALWAYS in OR mode.
	// BUGID 3406: added $options
	$tplan_linked_tcversions = getFilteredLinkedVersions($argsObj, $tplanMgr, $tcaseMgr, $options);

	// With these pieces we implement the AND type of keyword filter.
	$testCaseSet = null;
	if(!is_null($my['filters']['keywordsFilter']) && !is_null($my['filters']['keywordsFilter']->items))
	{ 
		$keywordsTestCases = $tprojectMgr->get_keywords_tcases($argsObj->tproject_id,$my['filters']['keywordsFilter']->items,
															   $my['filters']['keywordsFilter']->type);
		$testCaseSet = array_keys($keywordsTestCases);
	}

	// BUGID 3026 - added $testcaseFilter
	if( !is_null($my['filters']['testcaseFilter']) )
	{
		if (is_null($testCaseSet) )
		{
			$testCaseSet = $my['filters']['testcaseFilter'];
		}
		else
		{
			$testCaseSet = array_intersect($testCaseSet, array($my['filters']['testcaseFilter']));
		}
	}
	
	
	// now get values as keys
	// 20100722 - asimon - additional check here because of warning from array_combine when $testCaseSet is null 
	$testCaseSet = !is_null($testCaseSet) ? array_combine($testCaseSet, $testCaseSet) : null;
	
    // BUGID 3406 
	// BUGID 3936: Assign Test Case Execution - Right pane does not reflect custom field filter.
	$genSpecFilters = array('keywords' => $argsObj->keyword_id, 'testcases' => $testCaseSet,
							'exec_type' => $my['filters']['executionTypeFilter'],
							'cfields' =>  $my['filters']['cfieldsFilter']);
							
	$out = gen_spec_view($dbHandler, 'testplan', $argsObj->tplan_id, $argsObj->id, $tsuite_data['name'],
		                 $tplan_linked_tcversions, null, $genSpecFilters, $my['options']);

	return $out;
}


/**
 * get Test Specification data within a Node
 *  
 *	using nodeId (that normally is a test suite id) as starting point
 *	will return subtree that start at nodeId.
 *	If filters are given, the subtree returned is filtered.
 * 
 *	Important Notice regaring keyword filtering
 *	Keyword filter logic inside this function seems to work ONLY on OR mode.
 *	Then how the AND mode is implemented ?
 *	Filter for test case id is used, and the test case set has been generated
 *	applying AND or OR logic (following user's choice).
 *	Then seems that logic regarding keywords here, may be can be removed
 *
 * @param integer $masterContainerId can be a Test Project Id, or a Test Plan id.
 *                is used only if keyword id filter has been specified
 *                to get all keyword defined on masterContainer.
 *
 * @param integer $nodeId node that will be root of the view we want to build.
 * 
 * @param string $specViewType: type of view requested
 *
 * @param array $filters
 *	  			filters['keyword_id']: array of keywords  
 *	  			filters['tcase_id']: 
 *	  			filters['execution_type']: 
 *	  			filters['importance']: 
 *	  			filters['cfields']: 
 *	  			filters['tcase_name']: 
 *
 * 
 * @return array map with view (test cases subtree)
 * 
 * @internal revisions
 * 20101024 - franciscom - BUGID 3932: Add test case to test plan - Execution type filter does not affect right pane
 *						   BUGID 3936: Assign Test Case Execution - Right pane does not reflect custom field filter.
 *
 * 20100417 - franciscom - BUGID 2498 - added logic to filter by importance (defined on test case spec)
 * 20100411 - franciscom - added logic to filter by execution type
 */
function getTestSpecFromNode(&$dbHandler,&$tcaseMgr,&$linkedItems,$masterContainerId,$nodeId,$specViewType,$filters)
{
	
	$applyFilters = false;
	$testCaseSet = null;
	$tck_map = null;
	$tobj_mgr = new testproject($dbHandler);
	$test_spec = $tobj_mgr->get_subtree($nodeId);
	$key2loop = null;
	$useAllowed = false;
	
	// 20100411 - BUGID 2797 - filter by test case execution type
	$nullCheckFilter = array('tcase_id' => false, 'importance' => false, 
							 'tcase_name' => false, 'cfields' => false);
	$zeroNullCheckFilter = array('execution_type' => false);
	$useFilter = array('keyword_id' => false) + $nullCheckFilter + $zeroNullCheckFilter;

	$applyFilters = false;
	foreach($nullCheckFilter as $key => $value)
	{
		$useFilter[$key] = !is_null($filters[$key]);
		
		$applyFilters =	$applyFilters || $useFilter[$key];
	}
	foreach($zeroNullCheckFilter as $key => $value)
	{
		// need to check for > 0, because for some items 0 has same meaning that null -> no filter
		$useFilter[$key] = (!is_null($filters[$key]) && ($filters[$key] > 0));
		$applyFilters =	$applyFilters || $useFilter[$key];
	}
	
	
	if( $useFilter['tcase_id'] )
	{
		$testCaseSet = is_array($filters['tcase_id']) ? $filters['tcase_id'] : array($filters['tcase_id']);
	}
	
	// BUGID 3768
	if(!is_array($filters['keyword_id']) ) {
		$filters['keyword_id'] = array($filters['keyword_id']);
	}

	if(($useFilter['keyword_id']=$filters['keyword_id'][0] > 0))
	{
		$applyFilters = true;
		switch ($specViewType)
		{
			case 'testplan':
				$tobj_mgr = new testplan($dbHandler); 
			break;  
		}
		$tck_map = $tobj_mgr->get_keywords_tcases($masterContainerId,$filters['keyword_id']);
	}  
	
	// BUGID 3936 - Design Time Custom Field Filter
	// if(($useFilter['cfields'] = !is_null($filters['cfields'])))
	// {
	// 	$applyFilters = true;
	// }
    // 
	// if(($useFilter['tcase_name'] = !is_null($filters['tcase_name'])))
	// {
	// 	$applyFilters = true;
	// }

	
	if( $applyFilters )
	{
		$key2loop = array_keys($test_spec);
		
		// first step: generate list of TEST CASE NODES
		$itemSet = null ;
		foreach($key2loop as $key)
		{
			if( ($test_spec[$key]['node_type_id'] == $filters['tcase_node_type_id']) )
			{
				$itemSet[$test_spec[$key]['id']] = $key; 
			}
		}
		$itemKeys = $itemSet;

		foreach($itemKeys as $key => $tspecKey)
		{
			// 20101209 - asimon - exchanged strpos by stripos to make search case insensitive
			if( ($useFilter['keyword_id'] && !isset($tck_map[$test_spec[$tspecKey]['id']]) ) ||
				($useFilter['tcase_id'] && !in_array($test_spec[$tspecKey]['id'],$testCaseSet)) ||
				($useFilter['tcase_name'] && (stripos($test_spec[$tspecKey]['name'],$filters['tcase_name']) === false))				
			  )	
			{
				$test_spec[$tspecKey]=null; 
				unset($itemSet[$key]);
			}
		}

		if( count($itemSet) > 0 && 
			($useFilter['execution_type'] || $useFilter['importance'] || $useFilter['cfields']) )
		{
			$targetSet = array_keys($itemSet);
			$options = ($specViewType == 'testPlanLinking') ? array( 'access_key' => 'testcase_id') : null;
			
			// BUGID 3889: Add Test Cases to Test plan - Right pane does not honor custom field filter
			$getFilters = $useFilter['cfields'] ? array('cfields' => $filters['cfields']) : null;
			$tcversionSet = $tcaseMgr->get_last_active_version($targetSet,$getFilters,$options);
		
			switch($specViewType)
			{
				case 'testPlanLinking':
					// We need to analise linked items and spec
					foreach($targetSet as $idx => $key)
					{
						$targetTestCase = isset($tcversionSet[$key]) ? $tcversionSet[$key]['testcase_id'] : null;			

						// BUGID 3936 - Design Time Custom Field Filter
						if( is_null($targetTestCase) )
						{
							$test_spec[$itemSet[$key]]=null; //BUGID 4024
							$item = null;
						}
						else 
						{
							if( isset($linkedItems[$targetTestCase]) )
							{
								$item = current($linkedItems[$targetTestCase]);
							}
							else
							{
								// hmmm, does not understand this logic.
								$item = null;
								if( isset($test_spec[$itemSet[$targetTestCase]]) )
								{
									$item = $tcversionSet[$targetTestCase];
								}
							}
						}
						
						if( !is_null($item) )
						{
							if( $useFilter['execution_type'] && ($item['execution_type'] != $filters['execution_type']) || 
							    $useFilter['importance'] && ($item['importance'] != $filters['importance']) )
							{
								$tspecKey = $itemSet[$targetTestCase]; 	
								$test_spec[$tspecKey]=null; 
							}
						}						
					}
				break;
				
				default:
					$tcvidSet = array_keys($tcversionSet);
					$options = null;
					
					// BUGID 3934
					$doFilter = true;
					$allowedSet = null;
					if( $useFilter['execution_type'] )
					{
						$allowedSet = $tcaseMgr->filter_tcversions_by_exec_type($tcvidSet,$filters['execution_type'],$options);
						$doFilter = (!is_null($allowedSet) &&  count($allowedSet) > 0);
					}
					
					if( $doFilter )
					{
						// Add another filter on cascade mode
						// BUGID 3936: Assign Test Case Execution - Right pane does not reflect custom field filter.
						if( $useFilter['cfields'] )
						{
							$filteredSet = (!is_null($allowedSet) &&  count($allowedSet) > 0) ? array_keys($allowedSet) : $tcvidSet;
							$dummySet = $tcaseMgr->filter_tcversions_by_cfields($filteredSet,$filters['cfields'],$options);

							// transform to make compatible with filter_tcversions_by_exec_type() return type
							if( !is_null($dummySet) &&  count($dummySet) > 0 )
							{
								$allowedSet = null;
								$work2do = array_keys($dummySet);
								foreach($work2do as $wkey)
								{
									$allowedSet[$wkey] = $dummySet[$wkey][0];
								}
								unset($dummySet);
							}
						}
					}
					
					if( !is_null($allowedSet) &&  count($allowedSet) > 0 )
					{
						$useAllowed = true;
						foreach($allowedSet as $key => $value)
						{
							
							$tspecKey = $itemSet[$value['testcase_id']]; 	
							$test_spec[$tspecKey]['version']=$value['version']; 
						}
						reset($allowedSet);
					}
	        		
					$setToRemove = array_diff_key($tcversionSet,$allowedSet);
					if( !is_null($setToRemove) &&  count($setToRemove) > 0 )
					{
						foreach($setToRemove as $key => $value)
						{
							$tspecKey = $itemSet[$value['testcase_id']]; 	
							$test_spec[$tspecKey]=null; 
						}
					}
				break;	
			}  // end switch
		}
	} // if apply filters
	unset($tobj_mgr);
	return $test_spec;
}


/**
 * remove empty Test Suites
 * 
 * @param array $testSuiteSet reference to set to analyse and clean.
 * @param object $treeMgr reference to object
 * @param TBD $pruneUnlinkedTcversions useful when working on test plans
 * @param TBD $nodeTypes hash key: node type description, value: code
 */
function removeEmptyTestSuites(&$testSuiteSet,&$treeMgr,$pruneUnlinkedTcversions,$nodeTypes)
{
	foreach($testSuiteSet as $key => $value)
	{
		// We will remove test suites that meet the empty conditions:
		// - do not contain other test suites    OR
		// - do not contain test cases
		if( is_null($value) ) 
		{
			unset($testSuiteSet[$key]);
		}

		else if ($pruneUnlinkedTcversions &&
				(isset($value['testcase_qty']) && $value['testcase_qty'] > 0) )
		{
			// only linked tcversion must be returned, but this analisys must be done
			// for test suites that has test cases.
			if( isset($value['linked_testcase_qty']) && $value['linked_testcase_qty']== 0)
			{
				unset($testSuiteSet[$key]);
			} 
			else
			{
				// Only if test suite has children test cases we need to understand
				// if they are linked or not
				if( isset($value['testcases']) && count($value['testcases']) > 0 )
				{
					foreach($value['testcases'] as $skey => $svalue)
					{
						if( $svalue['linked_version_id'] == 0)
						{
							unset($testSuiteSet[$key]['testcases'][$skey]);
						}
					}
				} 
			} // is_null($value)
		}

		else
		{
			// list of children test suites if useful on smarty template, in order
			// to draw nested div.
			$tsuite_id=$value['testsuite']['id'];  
			$testSuiteSet[$key]['children_testsuites']=
				$treeMgr->get_subtree_list($tsuite_id,$nodeTypes['testsuite']);  
			
			if( $value['testcase_qty'] == 0 && $testSuiteSet[$key]['children_testsuites']=='' )
			{
				unset($testSuiteSet[$key]);
			}
		}  
	}
	
}
	  

/**
 * 
 *
 */
function  removeEmptyBranches(&$testSuiteSet,&$tsuiteTestCaseQty)
{
	foreach($testSuiteSet as $key => $elem)
	{
		$tsuite_id=$elem['testsuite']['id'];
		
		if( !isset($tsuiteTestCaseQty[$tsuite_id]) )
		{
			$tsuiteTestCaseQty[$tsuite_id]=0;
		} 
		   
		if( isset($elem['children_testsuites']) && $elem['children_testsuites'] != '' )
		{
			$children=explode(',',$elem['children_testsuites']);
			foreach($children as $access_id)
			{
				if( isset($tsuiteTestCaseQty[$access_id]) )
				{
					$tsuiteTestCaseQty[$tsuite_id] += $tsuiteTestCaseQty[$access_id];                
				}
			}
		}
		
		if( $tsuiteTestCaseQty[$tsuite_id]== 0 )
		{
			unset($testSuiteSet[$key]);
		} 
	}
} // function end	  


/**
 *	@param array &$testSuiteSet: changes will be done to this array
 *                               to add custom fields info.
 *                               Custom field info will be indexed by platform id
 * 
 *	@param integer $tprojectId
 *	@param object &$tcaseMgr reference to testCase class instance
 *
 *  
 *	@internal revisions
 *	20100119 - franciscom - start fixing missing platform refactoring
 *
 */
function addCustomFieldsToView(&$testSuiteSet,$tprojectId,&$tcaseMgr)
{
	// Important:
	// testplan_tcversions.id value, that is used to link to manage custom fields that are used
	// during testplan_design is present on key 'feature_id' (only is linked_version_id != 0)
	foreach($testSuiteSet as $key => $value) 
	{
		if( !is_null($value) )
		{
			if( isset($value['testcases']) && count($value['testcases']) > 0 )
			{
				foreach($value['testcases'] as $skey => $svalue)
				{
					if( ($linked_version_id=$svalue['linked_version_id']) > 0 )
					{
						$platformSet = array_keys($svalue['feature_id']);
						foreach($platformSet as $platform_id)
						{
							$testSuiteSet[$key]['testcases'][$skey]['custom_fields'][$platform_id]='';
							if( $linked_version_id != 0  )
							{
                    	        $cf_name_suffix = "_" . $svalue['feature_id'][$platform_id];
								$cf_map = $tcaseMgr->html_table_of_custom_field_inputs($linked_version_id,null,'testplan_design',
									                                                   $cf_name_suffix,$svalue['feature_id'][$platform_id],
									                                                   null,$tprojectId);
								$testSuiteSet[$key]['testcases'][$skey]['custom_fields'][$platform_id] = $cf_map;
							}
						}
					}
				}
			} 
			
		} // is_null($value)
	}
} // function end


/**
 * 
 *
 */
function buildSkeleton($id,$name,$config,&$test_spec,&$platforms)
{
  	$parent_idx=-1;
	$pivot_tsuite = $test_spec[0];
	$level = array();
	$tcase_memory = null;

    $node_types = $config['node_types'];
    $write_status = $config['write_status'];
    $is_uncovered_view_type = $config['is_uncovered_view_type'];

	$out=array();
	$idx = 0;
	$a_tcid = array();
	$a_tsuite_idx = array();
	$hash_id_pos[$id] = $idx;
	$out[$idx]['testsuite'] = array('id' => $id, 'name' => $name);
	$out[$idx]['testcases'] = array();
	$out[$idx]['write_buttons'] =	 'no';
	$out[$idx]['testcase_qty'] = 0;
	$out[$idx]['level'] = 1;
	$out[$idx]['linked_testcase_qty'] = 0;
	$out[$idx]['linked_ts'] = null;                                          
	$out[$idx]['linked_by'] = 0;                                          
    $out[$idx]['priority'] = 0;

	$the_level = $out[0]['level']+1;
	$idx++;
	$tsuite_tcqty=array($id => 0);

	foreach ($test_spec as $current)
	{
		if(is_null($current))
		{
			continue;
		}
		
		// In some situations during processing of testcase, a change of parent can
		// exists, then we need to update $tsuite_tcqty
		if($node_types[$current['node_type_id']] == "testcase")
		{                                         
			$tc_id = $current['id'];
			$parent_idx = $hash_id_pos[$current['parent_id']];
			$a_tsuite_idx[$tc_id] = $parent_idx;
			$out[$parent_idx]['testcases'][$tc_id] = array('id' => $tc_id,'name' => $current['name']);
	
	        // Reference to make code reading more human friendly				
			$outRef = &$out[$parent_idx]['testcases'][$tc_id];
			
			if($is_uncovered_view_type)
			{
				// @TODO understand impacts of platforms
				$outRef['external_id'] = $linked_items[$tc_id]['external_id'];      
			} 
			else
			{
				$out[$parent_idx]['write_buttons'] = $write_status;
				$out[$parent_idx]['linked_testcase_qty'] = 0;
	
				$outRef['tcversions'] = array();
				$outRef['tcversions_active_status'] = array();
				
				// 20080811 - franciscom - BUGID 1650 (REQ)
				$outRef['tcversions_execution_type'] = array();
				$outRef['tcversions_qty'] = 0;
				$outRef['linked_version_id'] = 0;
				$outRef['executed'] = null; // 'no';
	
				// useful for tc_exec_assignment.php          
				$outRef['user_id'] = null; //0;
				$outRef['feature_id'] = null; //0;
				$outRef['linked_by'] = null; //0;
				$outRef['linked_ts'] = null;
			    $outRef['priority'] = 0;
			    $outRef['platforms'] = $platforms;
			    // $outRef['importance'] = 0;
			}
			$out[$parent_idx]['testcase_qty']++;
			$a_tcid[] = $current['id'];
			
			// This piece is needed initialize in right way $tsuite_tcqty 
			// in this kind of situation, for SubSuite2 
			//
			// Tsuite 1
			//    |__ SubSuite1
			//    |      |__TCX1
			//    |      |__TCX2
			//    |
			//    |__ SubSuite2
			//    |      |__TCY1
			//    |      |__TCY2
			//    |
			//    |__ TCZ1
			//
			//               
			if( $tcase_memory['parent_id'] != $current['parent_id'] )
			{
				if( !is_null($tcase_memory) )
				{
					$pidx = $hash_id_pos[$tcase_memory['parent_id']];
					$xdx=$out[$pidx]['testsuite']['id'];
					$tsuite_tcqty[$xdx]=$out[$pidx]['testcase_qty'];
				}
				$tcase_memory=$current;
			}  
		}
		else
		{
			// This node is a Test Suite
			if($parent_idx >= 0)
			{ 
				$xdx=$out[$parent_idx]['testsuite']['id'];
				$tsuite_tcqty[$xdx]=$out[$parent_idx]['testcase_qty'];
			}
			
			if($pivot_tsuite['parent_id'] != $current['parent_id'])
			{
				if ($pivot_tsuite['id'] == $current['parent_id'])
				{
					$the_level++;
					$level[$current['parent_id']] = $the_level;
				}
				else 
					$the_level = $level[$current['parent_id']];
			}
			$out[$idx]['testsuite']=array('id' => $current['id'], 'name' => $current['name']);
			$out[$idx]['testcases'] = array();
			$out[$idx]['testcase_qty'] = 0;
			$out[$idx]['linked_testcase_qty'] = 0;
			$out[$idx]['level'] = $the_level;
			$out[$idx]['write_buttons'] = 'no';
			$hash_id_pos[$current['id']] = $idx;
			$idx++;
			
			// update pivot.
			$level[$current['parent_id']] = $the_level;
			$pivot_tsuite = $current;
		}
	} // foreach
	
	// Update after finished loop
	if($parent_idx >= 0)
	{ 
		$xdx=$out[$parent_idx]['testsuite']['id'];
		$tsuite_tcqty[$xdx]=$out[$parent_idx]['testcase_qty'];
	}

   	unset($tcase_memory);
	$tsuite_tcqty[$id] = $out[$hash_id_pos[$id]]['testcase_qty'];
	return array($a_tcid,$a_tsuite_idx,$tsuite_tcqty,$out);
}
 
 
/**
 * 
 *
 * @internal revisions:
 *  20100726 - asimon - BUGID 3628
 */
function addLinkedVersionsInfo($testCaseSet,$a_tsuite_idx,&$out,&$linked_items)
{
    $optionalIntegerFields = array('user_id', 'feature_id','linked_by');
	$result = array('spec_view'=>array(), 'num_tc' => 0, 'has_linked_items' => 0);
	$pivot_id=-1;
	
	foreach($testCaseSet as $the_k => $testCase)
	{
		$tc_id = $testCase['testcase_id'];
		
		// Needed when having multiple platforms
		if($pivot_id != $tc_id )
		{
			$pivot_id=$tc_id;
			$result['num_tc']++;
		}
		$parent_idx = $a_tsuite_idx[$tc_id];
		
    	// Reference to make code reading more human friendly				
		$outRef = &$out[$parent_idx]['testcases'][$tc_id];
		if($testCase['active'] == 1 && !is_null($out[$parent_idx]) )
		{       
			if( !isset($outRef['execution_order']) )
			{
				// Doing this I will set order for test cases that still are not linked.
				// But Because I loop over all version (linked and not) if I always write, 
				// will overwrite right execution order of linked tcversion.
				//
				// N.B.:
				// As suggested by Martin Havlat order will be set to external_id * 10
				$outRef['execution_order'] = $testCase['node_order']*10;
			} 
			$outRef['tcversions'][$testCase['id']] = $testCase['version'];
			$outRef['tcversions_active_status'][$testCase['id']] = 1;
			$outRef['external_id'] = $testCase['tc_external_id'];
			$outRef['tcversions_execution_type'][$testCase['id']] = $testCase['execution_type'];
			$outRef['importance'][$testCase['id']] = $testCase['importance'];
			
			if (!isset($outRef['tcversions_qty']))  
			{
				$outRef['tcversions_qty']=0;
			}
			$outRef['tcversions_qty']++;
		}
		
		if(!is_null($linked_items))
		{
			foreach($linked_items as $linked_testcase)
			{
				if(($linked_testcase[0]['tc_id'] == $testCase['testcase_id']) &&
				   ($linked_testcase[0]['tcversion_id'] == $testCase['id']) )
				{
					// This can be written only once no matter platform qty
					if( !isset($outRef['tcversions'][$testCase['id']]) )
					{
						$outRef['tcversions'][$testCase['id']] = $testCase['version'];
						$outRef['tcversions_active_status'][$testCase['id']] = 0;
						$outRef['external_id'] = $testCase['tc_external_id'];
						$outRef['tcversions_execution_type'][$testCase['id']] = $testCase['execution_type'];
						$outRef['importance'][$testCase['id']] = $testCase['importance'];
					}
					$exec_order= isset($linked_testcase[0]['execution_order'])? $linked_testcase[0]['execution_order']:0;
					$outRef['execution_order'] = $exec_order;
					// 20090625 - Eloff
					// 20100726 - BUGID 3628 - asimon - missing [0] in condition 
					// caused missing priority in GUI
					if( isset($linked_testcase[0]['priority']) )
					{
						$outRef['priority'] = priority_to_level($linked_testcase[0]['priority']);
					}
					$outRef['linked_version_id']= $testCase['id'];
					$out[$parent_idx]['write_buttons'] = 'yes';
					$out[$parent_idx]['linked_testcase_qty']++;
					$result['has_linked_items'] = 1;

                    foreach($linked_testcase as $item)
                    {  
						if(intval($item['executed']))
						{
							$outRef['executed'][$item['platform_id']]='yes';
						}
                    	
						if( isset($item['linked_ts']))
						{
							$outRef['linked_ts'][$item['platform_id']]=$item['linked_ts'];
						}
						
						foreach ($optionalIntegerFields as $fieldKey )
						{
							if( isset($item[$fieldKey]))
							{
								$outRef[$fieldKey][$item['platform_id']]=intval($item[$fieldKey]);
							}
				    	}
				    }
					break;
				}
			}
		} 
	} //foreach($tcase_set
	
	if( !is_null($out[0]) )
	{
		$result['spec_view'] = $out;
	}
	return $result; 
}

/**
 * 
 *
 */
function getPlatforms($db,$tproject_id,$testplan_id)
{
 	$platform_mgr = new tlPlatform($db, $tproject_id);

    if (is_null($testplan_id)) {
        $platforms = $platform_mgr->getAll();
    } else {
        $platforms = $platform_mgr->getLinkedToTestplan($testplan_id);
    }
	if( is_null($platforms) )
	{
		// need to create fake data for platform 0 in order 
		// to have only simple logic
		$platforms = array( 'id' => 0, 'name' => '');
	}
	return $platforms;
}
?>