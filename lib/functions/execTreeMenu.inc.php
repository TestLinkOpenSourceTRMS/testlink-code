<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Functions related to tree menu building ONLY for test execution feature
 * This is a refactoring, this functions are included using treeMenu.inc.php
 * This is a provisory approach
 *
 *
 * @filesource	execTreeMenu.inc.php
 * @package 	TestLink
 * @author 		Francisco Mancardi
 * @copyright 	2012, TestLink community 
 * @link 		http://www.teamst.org/index.php
 * @uses 		config.inc.php
 *
 * @internal revisions
 * @since 1.9.4
 * 20121015 - asimon - TICKET 5284: Filtering by the value of custom fields is not working on tester assignment
 * 20120921 - asimon - TICKET 5229: Filtering by the value of custom fields is not working on test execution
 * 20120816 - franciscom - TICKET 4905: Test Case Tester Assignment - filters dont work properly for 'Assigned to' Field
 */

/**
 * @param $dbHandler
 * @param $menuUrl
 * @param $tproject_id
 * @param $tproject_name
 * @param $tplan_id
 * @param $tplan_name
 * @param $objFilters
 * @param $objOptions
 * @return array
 */
function execTree(&$dbHandler,&$menuUrl,$tproject_id,$tproject_name,$tplan_id,
                  $tplan_name,$objFilters,$objOptions) 
{

	//Echo '<h1>' . __FUNCTION__ . '</h1>';    
	// die();
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
   
   
    //New dBug($objFilters);
  	
	$keyword_id = 0;
	$keywordsFilterType = 'Or';
	if (property_exists($objFilters, 'filter_keywords') && !is_null($objFilters->filter_keywords)) 
	{
		$keyword_id = $objFilters->filter_keywords;
		$keywordsFilterType = $objFilters->filter_keywords_filter_type;
	}
	

	list($filters,$options,
		 $show_testsuite_contents,
	     $useCounters,$useColors,$colorBySelectedBuild) = initExecTree($objFilters,$objOptions);
	
    //New dBug($filters);

	$tplan_mgr = new testplan($dbHandler);
	$tproject_mgr = new testproject($dbHandler);
	$tcase_node_type = $tplan_mgr->tree_manager->node_descr_id['testcase'];

	$hash_descr_id = $tplan_mgr->tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);	    
	
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
  	$my['options']=array('recursive' => true, 
  						 'remove_empty_nodes_of_type' => $tplan_mgr->tree_manager->node_descr_id['testsuite'],
  	                     'order_cfg' => array("type" =>'exec_order',"tplan_id" => $tplan_id));
 	$my['filters'] = array('exclude_node_types' => $nt2exclude,
 	                       'exclude_children_of' => $nt2exclude_children);
	
 	// BUGID 3301 - added for filtering by toplevel testsuite
 	if (isset($objFilters->filter_toplevel_testsuite) && is_array($objFilters->filter_toplevel_testsuite)) 
 	{
 		$my['filters']['exclude_branches'] = $objFilters->filter_toplevel_testsuite;
 	}

    // TICKET 5229: Filtering by the value of custom fields is not working on test execution
    if (isset($objFilters->filter_custom_fields) && is_array($objFilters->filter_custom_fields))
    {
        $my['filters']['filter_custom_fields'] = $objFilters->filter_custom_fields;
    }
    
 	// Take Time
 	//$chronos[] = microtime(true);
	//$tnow = end($chronos);$tprev = prev($chronos);
    
 	//New dBug($my);
	// Document why this is needed, please	
    $test_spec = $tplan_mgr->getSkeleton($tplan_id,$tproject_id,$my['filters'],$my['options']);
 	//Echo 'BEFORE';
 	
 	//echo 'AF';
 	//New dBug($test_spec);
 	
 	
 	// Take Time
 	//$chronos[] = microtime(true);
	//$tnow = end($chronos);
	//$tprev = prev($chronos);
	//$t_elapsed = number_format( $tnow - $tprev, 4);
	//echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (getSkeleton()):' . $t_elapsed .'<br>';
	//reset($chronos);	
 	// die('DYING LINE' . __LINE__);

     
	$test_spec['name'] = $tproject_name . " / " . $tplan_name;  // To be discussed
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];
	$test_spec['node_type'] = 'testproject';
	$map_node_tccount = array();
	
	$tplan_tcases = null;
    $apply_other_filters=true;


	if($test_spec)
	{
		if(is_null($filters['tcase_id']) || $filters['tcase_id'] > 0)   // 20120519 TO BE CHECKED
		{
			// Step 1 - get item set with exec status.
			// This has to scopes:
			// 1. tree coloring according exec status on (Test plan, platform, build ) context
			// 2. produce sql that can be used to reduce item set on combination with filters
			//    that can not be used on this step like:
			//    a. test cases belonging to branch with root TEST SUITE
			//	  b. keyword filter on AND MODE
			//    c. execution results on other builds, any build etc
			//
			// WE NEED TO ADD FILTERING on CUSTOM FIELD VALUES, WE HAVE NOT REFACTORED
			// THIS YET.
			//
			//New dBug($filters, array('label' => __FUNCTION__));
			
			
			if( !is_null($sql2do = $tplan_mgr->getLinkedForExecTree($tplan_id,$filters,$options)) )
			{
				//New dBug($sql2do);
				$kmethod = "fetchRowsIntoMap";
				if( is_array($sql2do) )
				{				
					if( $filters['keyword_filter_type'] == 'And')
					{ 
						$kmethod = "fetchRowsIntoMapAddRC";
						$unionClause = " UNION ALL ";
					}
					else
					{
						$kmethod = "fetchRowsIntoMap";
						$unionClause = ' UNION ';
					}
					$sql2run = $sql2do['exec'] . $unionClause . $sql2do['not_run'];
				}
				else
				{
					$sql2run = $sql2do;
				}
				$tplan_tcases = $setTestCaseStatus = $dbHandler->$kmethod($sql2run,'tcase_id');
				
			}
									
		 	// Take Time
		 	//$chronos[] = microtime(true);
			//$tnow = end($chronos);
			//$tprev = prev($chronos);
			//$t_elapsed = number_format( $tnow - $tprev, 4);
			//echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (<b>AFTER getLinkedForTree()</b>):' . $t_elapsed .'<br>';
			//reset($chronos);	
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

		// OK, now we need to work on status filters
		// New dBug($objFilters);
		// New dBug($objOptions);
		// if "any" was selected as filtering status, don't filter by status
		$targetExecStatus = (array)(isset($objFilters->filter_result_result) ? $objFilters->filter_result_result : null);
		
		//New dBug($targetExecStatus);
		
		if( !is_null($targetExecStatus) && (!in_array($resultsCfg['status_code']['all'], $targetExecStatus)) ) 
		{
			// die('GO ON OTHER FILTERS');
			//echo '<h1> BEFORE applyStatusFilters() </h1>';
			//New dBug($tplan_tcases);
			applyStatusFilters($tplan_id,$tplan_tcases,$objFilters,$tplan_mgr,$resultsCfg['status_code']);       

			//echo '<h1> *** After *** applyStatusFilters() </h1>';
			//New dBug($tplan_tcases);
			
			
		}
		
		// TICKET 5229: Filtering by the value of custom fields is not working on test execution
		if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes']))
        {
            $test_spec['childNodes'] = filter_by_cf_values($dbHandler, $test_spec['childNodes'],
                                                           $my['filters']['filter_custom_fields'],$hash_descr_id);
        }

		// Take time
	 	//$chronos[] = microtime(true);
		//$tnow = end($chronos);
		//$tprev = prev($chronos);
		//$t_elapsed = number_format( $tnow - $tprev, 4);
		//reset($chronos);	

		// New dBug($tplan_tcases);
		
		
	    $pnFilters = null;		
		// ATTENTION: sometimes we use $my['options'], other $options
		$pnOptions = array('hideTestCases' => $options['hideTestCases'], 'viewType' => 'executionTree');
		$testcase_counters = prepareExecTreeNode($dbHandler,$test_spec,$map_node_tccount,
		                                  		 $tplan_tcases,$pnFilters,$pnOptions);

		// Take time
	 	// $chronos[] = microtime(true);
		// $tnow = end($chronos);
		// $tprev = prev($chronos);
		// $t_elapsed = number_format( $tnow - $tprev, 4);
		// echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (<b>AFTER prepareExecTreeNode()</b>):' . $t_elapsed .'<br>';
		// reset($chronos);	


		foreach($testcase_counters as $key => $value)
		{
			$test_spec[$key] = $testcase_counters[$key];
		}
	
		$keys = array_keys($tplan_tcases);
		$menustring = renderExecTreeNode(1,$test_spec,$tplan_tcases,$hash_id_descr,1,$menuUrl,
										 $options['hideTestCases'],
			                             $useCounters,$useColors,
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

		 	//$chronos[] = microtime(true);
			//$tnow = end($chronos);
			//reset($chronos);	
			//$tstart = prev($chronos);
			//$t_elapsed = number_format( $tnow - $tstart, 4);
			//echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (<b>BEFORE RETURN()</b>):' . $t_elapsed .'<br>';
			//reset($chronos);	
	
	return array($treeMenu, $keys);
}


/*
 *
 *
 * @internal revisions
 * @since 1.9.4
 *
 */
function initExecTree($filtersObj,$optionsObj)
{
	$filters = array();
	$options = array();
	
	$buildSettingsPanel = null;
	$buildFiltersPanel = isset($filtersObj->filter_result_build) ? $filtersObj->filter_result_build : null;
	$build2filter_assignments = is_null($buildFiltersPanel) ? $buildSettingsPanel : $buildFiltersPanel;

	$keymap = array('tcase_id' => 'filter_tc_id', 'assigned_to' => 'filter_assigned_user',
					'platform_id' => 'setting_platform', 'exec_type' => 'filter_execution_type',
					'urgencyImportance' => 'filter_priority', 'tcase_name' => 'filter_testcase_name',
					'cf_hash' => 'filter_custom_fields', 'build_id' => array('setting_build','build_id'));
	
	// TICKET 4905: Test Case Tester Assignment - filters dont work properly for 'Assigned to' Field
	if( property_exists($optionsObj,'buildIDKeyMap') && !is_null($filtersObj->filter_result_build) )
	{
		$keymap['build_id'] = $optionsObj->buildIDKeyMap;
	}
	
	foreach($keymap as $key => $prop)
	{
		if( is_array($prop) )
		{
			foreach($prop as $tryme)
			{
				if( isset($filtersObj->$tryme) )
				{
					$filters[$key] = $filtersObj->$tryme;
					break;
				}
				else
				{
					$filters[$key] = null;
				}
			}	
		}
		else
		{
			//Echo '\$key:' . $key . '<br>';
			$filters[$key] = isset($filtersObj->$prop) ? $filtersObj->$prop : null; 
		}	
	}

	//New dBug($filters);

	$filters['keyword_id'] = 0;
	$filters['keyword_filter_type'] = 'Or';
	if (property_exists($filtersObj, 'filter_keywords') && !is_null($filtersObj->filter_keywords)) 
	{
		$filters['keyword_id'] = $filtersObj->filter_keywords;
		$filters['keyword_filter_type'] = $filtersObj->filter_keywords_filter_type;
	}


	$options['hideTestCases'] = isset($optionsObj->hideTestCases) ?
	                      	          $optionsObj->hideTestCases : false;

	$options['include_unassigned'] = isset($filtersObj->filter_assigned_user_include_unassigned) ?
	                      			 $filtersObj->filter_assigned_user_include_unassigned : false;

	// useful when using tree on set urgent test cases
	$options['allow_empty_build'] = isset($optionsObj->allow_empty_build) ?
	                      			      $optionsObj->allow_empty_build : false;


	// NOT CLEAR what to do
	// $status = isset($filters->filter_result_result) ? $filters->filter_result_result : null;
	$show_testsuite_contents = isset($filtersObj->show_testsuite_contents) ? 
	                           $filtersObj->show_testsuite_contents : true;

	
	$useCounters=isset($optionsObj->useCounters) ? $optionsObj->useCounters : null;
	$useColors=isset($optionsObj->useColours) ? $optionsObj->useColours : null;
	$colorBySelectedBuild = isset($optionsObj->testcases_colouring_by_selected_build) ? 
	                        $optionsObj->testcases_colouring_by_selected_build : null;


	return array($filters,$options,$show_testsuite_contents,$useCounters,$useColors,$colorBySelectedBuild);

}




function prepareExecTreeNode(&$db,&$node,&$map_node_tccount,&$tplan_tcases = null,
							 $filters=null, $options=null)
{
	
	static $status_descr_list;
	static $debugMsg;
    static $my;
	static $resultsCfg;

    $tpNode = null;
	if (!$debugMsg)
	{
		// New dBug($tplan_tcases);
		
  	    $debugMsg = 'Class: ' . __CLASS__ . ' - ' . 'Method: ' . __FUNCTION__ . ' - ';

		$resultsCfg = config_get('results');
		$status_descr_list = array_keys($resultsCfg['status_code']);
		$status_descr_list[] = 'testcase_count';

		$my = array();
		$my['options'] = array('hideTestCases' => 0);
		$my['options'] = array_merge($my['options'], (array)$options);


		$my['filters'] = array();
		$my['filters'] = array_merge($my['filters'], (array)$filters);

	}
		
	$tcase_counters = array_fill_keys($status_descr_list, 0);
	$node_type = isset($node['node_type']) ? $node['node_type'] : null;

	if($node_type == 'testcase')
	{
		$tpNode = isset($tplan_tcases[$node['id']]) ? $tplan_tcases[$node['id']] : null;
		if( is_null($tpNode) )
		{			
			unset($tplan_tcases[$node['id']]);
			$node = null;
		} 
		else 
		{
			$node['tcversion_id'] = $tpNode['tcversion_id'];		
			$node['version'] = $tpNode['version'];		
			$node['external_id'] = $tpNode['external_id'];		

			unset($node['childNodes']);
			$node['leaf']=true;
		}

		// ========================================================================
		foreach($tcase_counters as $key => $value)
		{
			$tcase_counters[$key]=0;
		}

		if( isset($tpNode['exec_status']) )
		{
			$tc_status_descr = $resultsCfg['code_status'][$tpNode['exec_status']];   
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
			
			$counters_map = prepareExecTreeNode($db,$current,$map_node_tccount,$tplan_tcases,
												$my['filters'],$my['options']);
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
		if( !is_null($tplan_tcases) && !$tcase_counters['testcase_count'] && ($node_type != 'testproject'))
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



function applyStatusFilters($tplan_id,&$items2filter,&$fobj,&$tplan_mgr,$statusCfg)
{
	$fm = config_get('execution_filter_methods');
	$methods = $fm['status_code'];

	//New dBug($fm,array('label' => __METHOD__));
	//New dBug($methods,array('label' => __METHOD__));
	
	
	$ffn = array($methods['any_build'] => 'filterStatusSetAtLeastOneOfActiveBuilds',
		         $methods['all_builds'] => 'filterStatusSetAllActiveBuilds',
		         $methods['specific_build'] => 'filter_by_status_for_build',
		         $methods['current_build'] => 'filter_by_status_for_build',
		         $methods['latest_execution'] => 'filter_by_status_for_latest_execution');
	
	$f_method = isset($fobj->filter_result_method) ? $fobj->filter_result_method : null;
	$f_result = isset($fobj->filter_result_result) ? $fobj->filter_result_result : null;
	$f_result = (array)$f_result;

	// die();
	
	// if "any" was selected as filtering status, don't filter by status
	if (in_array($statusCfg['all'], $f_result)) 
	{
		$f_result = null;
		return $items2filter; // >>---> Bye!
	}

	if( ($filter_done = !is_null($f_method) ) )
	{
		//echo '<h1>FILTER METHOD:' . $f_method . '::' .  $ffn[$f_method] . '</h1>';
		$logMsg = 'FILTER METHOD:' . $f_method . '::' .  $ffn[$f_method];
		tLog($logMsg,'DEBUG');
		
		// special case: 
		// when filtering by "current build", we set the build to filter with
		// to the build chosen in settings instead of the one in filters
		//
		// Need to understand why we need to do this 'dirty/brute force initialization'
		if ($f_method == $methods['current_build']) 
		{
			$fobj->filter_result_build = $fobj->setting_build;
		}
		
		$items = $ffn[$f_method]($tplan_mgr, $items2filter, $tplan_id, $fobj);
	}

	return $filter_done ? $items : $items2filter; 
}



/*
 *
 * @used-by Assign Test Execution Feature
 *
 * @internal revisions:
 * 20121015 - asimon - TICKET 5284: Filtering by the value of custom fields is not working on tester assignment
 */
function testPlanTree(&$dbHandler,&$menuUrl,$tproject_id,$tproject_name,$tplan_id,
                      $tplan_name,$objFilters,$objOptions) 
{

	//echo '<h1>' . __FUNCTION__ . '</h1>';

	$debugMsg = ' - Method: ' . __FUNCTION__;
 	$chronos[] = $tstart = microtime(true);

	$treeMenu = new stdClass(); 
	$treeMenu->rootnode = null;
	$treeMenu->menustring = '';
	
	$resultsCfg = config_get('results');
	$showTestCaseID = config_get('treemenu_show_testcase_id');
	$glueChar=config_get('testcase_cfg')->glue_character;
	$menustring = null;
	$tplan_tcases = null;

	list($filters,$options,
		 $show_testsuite_contents,
	     $useCounters,$useColors,$colorBySelectedBuild) = initExecTree($objFilters,$objOptions);
	
	$tplan_mgr = new testplan($dbHandler);
	$tproject_mgr = new testproject($dbHandler);
	$tree_manager = $tplan_mgr->tree_manager;
	$tcase_node_type = $tree_manager->node_descr_id['testcase'];

	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);	    
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
  	                     'order_cfg' => array("type" =>'exec_order',"tplan_id" => $tplan_id),
  	                     'hideTestCases' => $options['hideTestCases']);
  	                     
 	$my['filters'] = array('exclude_node_types' => $nt2exclude,
 	                       'exclude_children_of' => $nt2exclude_children);
	
 	if (isset($objFilters->filter_toplevel_testsuite) && is_array($objFilters->filter_toplevel_testsuite)) 
 	{
 		$my['filters']['exclude_branches'] = $objFilters->filter_toplevel_testsuite;
 	}

    // TICKET 5284: Filtering by the value of custom fields is not working on tester assignment
    if (isset($objFilters->filter_custom_fields) && is_array($objFilters->filter_custom_fields))
    {
        $my['filters']['filter_custom_fields'] = $objFilters->filter_custom_fields;
    }

 	// Take Time
 	//$chronos[] = microtime(true);
	//$tnow = end($chronos);
	//$tprev = prev($chronos);
    
    $test_spec = $tplan_mgr->getSkeleton($tplan_id,$tproject_id,$my['filters'],$my['options']);

 	
 	// Take Time
 	// $chronos[] = microtime(true);
	// $tnow = end($chronos);
	// $tprev = prev($chronos);
	// $t_elapsed = number_format( $tnow - $tprev, 4);
	// echo '<br> ' . __FUNCTION__ . '::' . __LINE__ . ' Elapsed (sec) (getSkeleton()):' . $t_elapsed .'<br>';
	// reset($chronos);	

     
	$test_spec['name'] = $tproject_name . " / " . $tplan_name;  // To be discussed
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];
	$test_spec['node_type'] = 'testproject';
	$map_node_tccount = array();
	
	$tplan_tcases = null;
    $apply_other_filters=true;


	if($test_spec)
	{
		if(is_null($filters['tcase_id']) || $filters['tcase_id'] > 0)   // 20120519 TO BE CHECKED
		{
			// Step 1 - get item set with exec status.
			// This has to scopes:
			// 1. tree coloring according exec status on (Test plan, platform, build ) context
			// 2. produce sql that can be used to reduce item set on combination with filters
			//    that can not be used on this step like:
			//    a. test cases belonging to branch with root TEST SUITE
			//	  b. keyword filter on AND MODE
			//    c. execution results on other builds, any build etc
			//
			// WE NEED TO ADD FILTERING on CUSTOM FIELD VALUES, WE HAVE NOT REFACTORED
			// THIS YET.
			//
			//New dBug($filters, array('label' => __FUNCTION__));
			//Echo $objOptions->getTreeMethod;
			//Die();
			if( !is_null($sql2do = $tplan_mgr->{$objOptions->getTreeMethod}($tplan_id,$filters,$options)) )
			{
				if( is_array($sql2do) )
				{				
					if( $filters['keyword_filter_type'] == 'And')
					{ 
						$kmethod = "fetchRowsIntoMapAddRC";
						$unionClause = " UNION ALL ";
					}
					else
					{
						$kmethod = "fetchRowsIntoMap";
						$unionClause = ' UNION ';
					}
					$sql2run = $sql2do['exec'] . $unionClause . $sql2do['not_run'];
				}
				else
				{
					$kmethod = "fetchRowsIntoMap";
					$sql2run = $sql2do;
				}
				
				//New dBug($sql2run);
				$tplan_tcases = $setTestCaseStatus = $dbHandler->$kmethod($sql2run,'tcase_id');
				
			}
									
		 	// Take Time
		 	// $chronos[] = microtime(true);
			// $tnow = end($chronos);
			// $tprev = prev($chronos);
			// $t_elapsed = number_format( $tnow - $tprev, 4);
			// echo '<br> ' . __FUNCTION__ . " Elapsed (sec) (<b>AFTER glinkedMethod()</b>):" . $t_elapsed .'<br>';
			// reset($chronos);	
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

		// OK, now we need to work on status filters
		//New dBug($objFilters);
		//New dBug($objOptions);
		// if "any" was selected as filtering status, don't filter by status
		$targetExecStatus = (array)(isset($objFilters->filter_result_result) ? $objFilters->filter_result_result : null);
		if( !is_null($targetExecStatus) && (!in_array($resultsCfg['status_code']['all'], $targetExecStatus)) ) 
		{
			// die('GO ON OTHER FILTERS');
			//echo '<h1> BEFORE applyStatusFilters() </h1>';
			//New dBug($tplan_tcases);
			applyStatusFilters($tplan_id,$tplan_tcases,$objFilters,$tplan_mgr,$resultsCfg['status_code']);
		}

        // TICKET 5284: Filtering by the value of custom fields is not working on tester assignment
        if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes']))
        {
            $test_spec['childNodes'] = filter_by_cf_values($dbHandler, $test_spec['childNodes'],
                                       $my['filters']['filter_custom_fields'],$hash_descr_id);
        }

		// Take time
	 	//$chronos[] = microtime(true);
		//$tnow = end($chronos);
		//$tprev = prev($chronos);
		//$t_elapsed = number_format( $tnow - $tprev, 4);
		//reset($chronos);	
		
		// here we have LOT OF CONFUSION, sometimes we use $my['options'] other $options
	    $pnFilters = null;		
	    
	    
		$pnOptions = array('hideTestCases' => $my['options']['hideTestCases'], 'viewType' => 'executionTree');
		
		
		
		//New dBug($pnOptions);
		$testcase_counters = prepareExecTreeNode($dbHandler,$test_spec,$map_node_tccount,
		                                  		 $tplan_tcases,$pnFilters,$pnOptions);

		//New dBug($test_spec);
		
		// Take time
	 	// $chronos[] = microtime(true);
		// $tnow = end($chronos);$tprev = prev($chronos);
		// $t_elapsed = number_format( $tnow - $tprev, 4);
		// echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (<b>AFTER prepareExecTreeNode()</b>):' . $t_elapsed .'<br>';
		// reset($chronos);	


		foreach($testcase_counters as $key => $value)
		{
			$test_spec[$key] = $testcase_counters[$key];
		}
	
		$keys = array_keys($tplan_tcases);
		$menustring = renderExecTreeNode(1,$test_spec,$tplan_tcases,
			                             $hash_id_descr,1,$menuUrl,false,$useCounters,$useColors,
			                             $showTestCaseID,$tcase_prefix,$show_testsuite_contents);
	 	//$chronos[] = microtime(true);
		//$tnow = end($chronos);$tprev = prev($chronos);
		//$t_elapsed = number_format( $tnow - $tprev, 4);
		//echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (<b>AFTER renderExecTreeNode()</b>):' . $t_elapsed .'<br>';
		//reset($chronos);	
		
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

		 	//$chronos[] = microtime(true);
			//$tnow = end($chronos);
			//reset($chronos);	
			//$t_elapsed = number_format( $tnow - $tstart, 4);
			//echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (<b>BEFORE RETURN()</b>):' . $t_elapsed .'<br>';
			//reset($chronos);	
	
	return array($treeMenu, $keys);
}

?>