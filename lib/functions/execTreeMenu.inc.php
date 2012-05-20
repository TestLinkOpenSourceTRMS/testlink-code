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
 * 
 */
function execTree(&$dbHandler,&$menuUrl,$tproject_id,$tproject_name,$tplan_id,
                  $tplan_name,$objFilters,$objOptions) 
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
   
   
    // new dBug($objFilters);
  	
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
	

	$tplan_mgr = new testplan($dbHandler);
	$tproject_mgr = new testproject($dbHandler);
	$tcase_mgr = new testcase($dbHandler);
	
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
 	if (isset($objFilters->filter_toplevel_testsuite) && is_array($objFilters->filter_toplevel_testsuite)) 
 	{
 		$my['filters']['exclude_branches'] = $objFilters->filter_toplevel_testsuite;
 	}
 	
 	// Take Time
 	//$chronos[] = microtime(true);
	//$tnow = end($chronos);
	//$tprev = prev($chronos);
    
 	// new dBug($my);
	// Document why this is needed, please	
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
			// new dBug($filters, array('label' => __FUNCTION__));
			
			if( !is_null($sql2do = $tplan_mgr->getLinkedForTree($tplan_id,$filters,$options)) )
			{
				new dBug($sql2do);
				
				// 
				// $setTestCaseStatus = $this->db->fetchColumnsIntoMap($sql2run,'tcase_id','exec_status');
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
				$tplan_tcases = $setTestCaseStatus = $dbHandler->$kmethod($sql2run,'tcase_id');
				
			}
									
			// $tplan_tcases = $tplan_mgr->get_linked_tcversions($tplan_id,$linkedFilters,$opt);
			//new dBug($tplan_tcases);
			
		 	// Take Time
		 	$chronos[] = microtime(true);
			$tnow = end($chronos);
			$tprev = prev($chronos);
			$t_elapsed = number_format( $tnow - $tprev, 4);
			echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (<b>AFTER getLinkedForTree()</b>):' . $t_elapsed .'<br>';
			reset($chronos);	
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
		// new dBug($objFilters);
		// new dBug($objOptions);
		// if "any" was selected as filtering status, don't filter by status
		$targetExecStatus = (array)(isset($objFilters->filter_result_result) ? $objFilters->filter_result_result : null);
		if( !is_null($targetExecStatus) && (!in_array($resultsCfg['status_code']['all'], $targetExecStatus)) ) 
		{
			// die('GO ON OTHER FILTERS');
			echo '<h1> BEFORE applyStatusFilters() </h1>';
			new dBug($tplan_tcases);
			applyStatusFilters($tplan_id,$tplan_tcases,$objFilters,$tplan_mgr,$resultsCfg['status_code']);
		}
		
		
		



		// Take time
	 	//$chronos[] = microtime(true);
		//$tnow = end($chronos);
		//$tprev = prev($chronos);
		//$t_elapsed = number_format( $tnow - $tprev, 4);
		//reset($chronos);	
		
	    $pnFilters = null;		
		$pnOptions = array('hideTestCases' => false, 'viewType' => 'executionTree');
		$testcase_counters = prepareExecTreeNode($dbHandler,$test_spec,$decoding_hash,$map_node_tccount,
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



function initExecTree($filtersObj,$optionsObj)
{
	$filters = array();
	$options = array();
	
	// $buildSettingsPanel = isset($filters->setting_build) ? $filters->setting_build : 0;
	$buildSettingsPanel = null;
	$buildFiltersPanel = isset($filtersObj->filter_result_build) ? $filtersObj->filter_result_build : null;
	$build2filter_assignments = is_null($buildFiltersPanel) ? $buildSettingsPanel : $buildFiltersPanel;

	$keymap = array('tcase_id' => 'filter_tc_id', 'assigned_to' => 'filter_assigned_user',
					'platform_id' => 'setting_platform', 'exec_type' => 'filter_execution_type',
					'urgencyImportance' => 'filter_priority', 'tcase_name' => 'filter_testcase_name',
					'cf_hash' => 'filter_custom_fields', 'build_id' => 'setting_build');
	
	// new dBug($filtersObj);
	foreach($keymap as $key => $prop)
	{
		// echo $prop . '<br>';
		$filters[$key] = isset($filtersObj->$prop) ? $filtersObj->$prop : null; 
	}

	$filters['keyword_id'] = 0;
	$filters['keyword_filter_type'] = 'Or';
	if (property_exists($filtersObj, 'filter_keywords') && !is_null($filtersObj->filter_keywords)) 
	{
		$filters['keyword_id'] = $filtersObj->filter_keywords;
		$filters['keyword_filter_type'] = $filtersObj->filter_keywords_filter_type;
	}

	$options['include_unassigned'] = isset($filtersObj->filter_assigned_user_include_unassigned) ?
	                      			 $filtersObj->filter_assigned_user_include_unassigned : false;

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




function prepareExecTreeNode(&$db,&$node,&$decoding_info,&$map_node_tccount,
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
	}
		
	$tcase_counters = array_fill_keys($status_descr_list, 0);
	$node_type = isset($node['node_type_id']) ? $decoding_info['node_id_descr'][$node['node_type_id']] : null;

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
			
			$counters_map = prepareExecTreeNode($db,$current,$decoding_info,$map_node_tccount,
				                                $tplan_tcases,$my['filters'],$my['options']);
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

	// if "any" was selected as filtering status, don't filter by status
	if (in_array($statusCfg['all'], $f_result)) 
	{
		$f_result = null;
		return $items2filter; // >>---> Bye!
	}

	if( ($filter_done = !is_null($f_method) ) )
	{
		$items = $ffn[$f_method]($tplan_mgr, $items2filter, $tplan_id, $fobj);
	}

	return $filter_done ? $items : $items2filter; 
}

?>