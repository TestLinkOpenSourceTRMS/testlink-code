<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * This file generates tree menu for test specification and test execution.
 * 
 * @filesource	tlTreeMenu.class.php
 * @package 	  TestLink
 * @author 		  Francisco Mancardi
 * @copyright 	2011,2012 TestLink community 
 * @link 		    http://www.teamst.org/index.php
 *
 * @internal revisions
 */
class tlTreeMenu
{
  const REFRESH_GUI = true;

	private $db = null;
	private $tprojectMgr = null;
	private $cfg = null;
	private $tables = null;
	
 	public function __construct(&$dbHandler) 
	{
		$this->db = $dbHandler;
		$this->tprojectMgr = new testproject($dbHandler);

    $this->tables = tlObjectWithDB::getDBTables(array('tcversions','nodes_hierarchy',
        												                      'testplan_tcversions','cfield_design_values'));

		$this->cfg = new stdClass();
		
		$this->cfg->showTestCaseID = config_get('treemenu_show_testcase_id');
		$this->cfg->glueChar = config_get('testcase_cfg')->glue_character;

		$this->cfg->nodeTypeCode = $this->tprojectMgr->tree_manager->get_available_node_types();
		$this->cfg->nodeCodeType = array_flip($this->cfg->nodeTypeCode);	

		$this->cfg->results = config_get('results');

		$this->cfg->renderTestSpecNode = new stdClass();
		$this->cfg->renderTestSpecNode->key2del = array_merge(array_keys($this->cfg->results['status_code']),
															                            array('node_type_id','parent_id','node_order',
															  		                            'node_table','tcversion_id','external_id',
															  		                            'version','testcase_count'));  

	}



  function generateTestSpecTree($env,$linkto,$filters=null,$options=null)
  {
   	$chronos[] = microtime(true);
  
  	$my = array();
  	$my['options'] = array('forPrinting' => 0, 'hideTestCases' => 0, 
  	                       'tc_action_enabled' => 1, 'viewType' => 'testSpecTree');
  
  	// testplan => only used if opetions['viewType'] == 'testSpecTreeForTestPlan'
  	$my['filters'] = array('keywords' => null, 'executionType' => null, 'importance' => null,
  	                       'testplan' => null, 'filter_tc_id' => null);
  
  	$my['options'] = array_merge($my['options'], (array)$options);
  	$my['filters'] = array_merge($my['filters'], (array)$filters);
  	
  	// ATTENTION: Process & >>---> Bye!
  	if( $my['options']['viewType'] == 'testSpecTree' )
  	{
  		return $this->generateTestSpecTreeNew($env,$linkto,$filters,$options); 
  	}
  	
  	// Ok go ahead
    // Prepare Node method settings (pn)
  	$pnOptions = array('hideTestCases' => $my['options']['hideTestCases'], 
  	    				       'viewType' => $my['options']['viewType']);
    $pnFilters = null;
 	  $pnFilters['setting_testplan'] = $my['filters']['setting_testplan'];
  	$keys2init = array('filter_testcase_name','filter_execution_type','filter_priority','filter_tc_id');
  	foreach ($keys2init as $keyname) 
  	{
  		$pnFilters[$keyname] = isset($my['filters'][$keyname]) ? $my['filters'][$keyname] : null;
  	}

  	// IMPORTANT NOTICE
  	// $filters['filter_toplevel_testsuite'] is managed in REVERSE form
  	// it contains NOT WHAT user wants, but all that we need to exclude
  	// in order provide what user WANTS.
  	// This is right way to go.
  	// 
  	$exclude_branches = isset($filters['filter_toplevel_testsuite']) && 
  						          is_array($filters['filter_toplevel_testsuite']) ?
  	                    $filters['filter_toplevel_testsuite'] : null;
  	
  	$test_spec = $this->getTestSpecTree($env->tproject_id,$filters);
  	$test_spec['name'] = $env->tproject_name;
  	$test_spec['id'] = $env->tproject_id;
  	$test_spec['node_type_id'] = $hash_descr_id['testproject'];
  
  	
  	$map_node_tccount = array();
  	$tplan_tcs = null;
  	$menustring = null;
  	
  	if($test_spec)
  	{
  		$tck_map = null;  // means no filter
  		if(!is_null($my['filters']['filter_keywords']))
  		{
  			$tck_map = $this->tprojectMgr->get_keywords_tcases($env->tproject_id,$my['filters']['filter_keywords'],
  			                                                   $my['filters']['filter_keywords_filter_type']);
  			if( is_null($tck_map) )
  			{
  				$tck_map = array();  // means filter everything
  			}
  		}

  	  if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes'])) 
  	  {
  	  	$test_spec['childNodes'] = $this->filterByCFValues($test_spec['childNodes'],
  	  												                             $my['filters']['filter_custom_fields'],
  	  												                             $my['filters']['filter_active_inactive']);
  	  }
  		
  	  // Important: 
  	  // prepareNode() will make changes to $test_spec like filtering by test case keywords using $tck_map;
  		$testcase_counters = $this->prepareNode($test_spec,$map_node_tccount,$tck_map,
  			                                      $tplan_tcs,$pnFilters,$pnOptions);
  
  		foreach($testcase_counters as $key => $value)
  		{
  			$test_spec[$key] = $testcase_counters[$key];
  		}
  		
  		
  		$tcase_prefix = $this->tprojectMgr->getTestCasePrefix($env->tproject_id) . $this->cfg->glueChar;
  		$menustring = $this->renderTreeNode(1,$test_spec,
  			                                  $my['options']['tc_action_enabled'],$linkto,$tcase_prefix,
  			                                  $my['options']['forPrinting'],$this->cfg->showTestCaseID);
  	}
  
  	
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

    // 
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
      // @TODO 20121102 - check for user contribution
  		$menustring = str_ireplace(array(':null',',null','null,','null'),array(':[]','','',''), $menustring); 
  	}
  	$treeMenu->menustring = $menustring; 
  	
  	return $treeMenu;
  }

  // function generateTestSpecTreeNew(&$db,$tproject_id, $tproject_name,$linkto,$filters=null,$options=null)
  function generateTestSpecTreeNew($env,$linkto,$filters=null,$options=null)
  {
  	$chronos[] = microtime(true);
 
  	$my = array();
  	$my['options'] = array('forPrinting' => 0, 'hideTestCases' => 0, 
  	                       'tc_action_enabled' => 1, 'viewType' => 'testSpecTree');
  	$my['filters'] = array('keywords' => null, 'testplan' => null);
  
  	$my['options'] = array_merge($my['options'], (array)$options);
  	$my['filters'] = array_merge($my['filters'], (array)$filters);
  	
  	$treeMenu = new stdClass(); 
  	$treeMenu->rootnode = null;
  	$treeMenu->menustring = '';
  	
  	$menustring = null;
  	
  	$tproject_mgr = new testproject($db);
  	$tree_manager = &$tproject_mgr->tree_manager;	
  	
  	$hash_descr_id = $tree_manager->get_available_node_types();
  	$hash_id_descr = array_flip($hash_descr_id);
  	$status_descr_code = $this->cfg->results['status_code'];
  	$status_code_descr = $this->cfg->results['code_status'];
  	
  	$decoding_hash=array('node_id_descr' => $hash_id_descr,
  		                 'status_descr_code' =>  $status_descr_code,
  		                 'status_code_descr' =>  $status_code_descr);
  	
  
  	$tcase_prefix = $tproject_mgr->getTestCasePrefix($tproject_id) . $this->cfg->glueChar;
  	$test_spec = getTestSpecTree($tproject_id,$tproject_mgr,$filters);
  	//$chronos[] = microtime(true);$tnow = end($chronos);$tprev = prev($chronos);
  	//$t_elapsed = number_format( $tnow - $tprev, 4);
  	//echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (get_subtree()):' . $t_elapsed .'<br>';
  	//reset($chronos);
  
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
  	    	$test_spec['childNodes'] = $this->filterByCFValues($test_spec['childNodes'],
  	    												                             $my['filters']['filter_custom_fields']);
  	    }
  		
  	    $pnFilters = array('keywords' => $my['filters']['filter_keywords'],
  	    				   'keywords_filter_type' => $my['filters']['filter_keywords_filter_type']);
          // TICKET 4353 - added active/inactive filter
  		$pnOptions = array('hideTestCases' => $my['options']['hideTestCases'],
                             'ignoreInactiveTestCases' => $my['options']['ignore_inactive_testcases'],
                             'ignoreActiveTestCases' => $my['options']['ignore_active_testcases']);
  		
  		// Important/CRITIC: 
  		// prepareTestSpecNode() will make changes to $test_spec like filtering by test case keywords.
  		$testcase_counters = prepareTestSpecNode($db, $tproject_mgr,$tproject_id,$test_spec,$map_node_tccount,
  												                     $pnFilters,$pnOptions);
  
  		//$chronos[] = microtime(true);$tnow = end($chronos);$tprev = prev($chronos);
  		//$t_elapsed = number_format( $tnow - $tprev, 4);
  		//echo '<br> ' . __FUNCTION__ . ' Elapsed (sec) (get_subtree()):' . $t_elapsed .'<br>';
  		//reset($chronos);	
  
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
  	if(!is_null($menustring))
  	{
  		// Remove null elements (Ext JS tree do not like it ).
  		// :null happens on -> "children":null,"text" that must become "children":[],"text"
  		$menustring = str_ireplace(array(':null',',null','null,','null'),array(':[]','','',''), $menustring); 
  	}
  	$treeMenu->menustring = $menustring; 
  	
  	return $treeMenu;
  }

  function getTestSpecTree($tprojectID,&$fObj)
  {
  	
  	$flt = array();
  	$flt['exclude_branches'] = null;
  	if( isset($fObj['filter_toplevel_testsuite']) && is_array($fObj['filter_toplevel_testsuite']) )
  	{
  	  $flt['exclude_branches'] = $fObj['filter_toplevel_testsuite'];
    }
  
    $flt['testcase_name'] = null;
  	if( isset($fObj['filter_testcase_name']) && !is_null($fObj['filter_testcase_name']) )
  	{
  		if( ($dummy = trim($fObj['filter_testcase_name'])) != '' )
  		{
  			$flt['testcase_name'] = $dummy;
  		}
  	}

    // logic for intval()
    $kfintval = array('testcase_id' => 'filter_tc_id','execution_type' => 'filter_execution_type',
                      'importance' => 'filter_importance');
    foreach($kfintval as $out => $in)
    {
      $flt[$out] = null;
      if( isset($fObj[$in]) && !is_null($fObj[$in]) )
  	  {
  		  $flt[$out] = intval($fObj[$in]);
  	  }
    }
     
  	$opt = array('recursive' => true,'exclude_testcases' => false);
  	$items = $this->tprojectMgr->getTestSpec($tprojectID,$flt,$opt); 
  
  	return $items;
  }



// *******************************************************************************************


	/*
	 *
	 *
	 */	
  // 	function generateTestSpecTree20($env,$linkto,$filters=null,$options=null)
	function generateTestSpecTree20($env,$linkto,$filters=null,$options=null)
	{
	
		$treeMenu = new stdClass(); 
		$treeMenu->rootnode = null;
		$treeMenu->menustring = '';
		$menustring = null;
		$debugMsg = 'Class:' . __CLASS__ . 'Function: ' . __FUNCTION__;

		$my = array();
		$my['options'] = array('forPrinting' => 0, 'hideTestCases' => 0, 
		                       'tc_action_enabled' => 1, 'ignore_inactive_testcases' => 0, 
		                       'viewType' => 'testSpecTree');
		
	
		// new dBug($filters, array('calledFrom' => $debugMsg));
		
		$my['filters'] = array('keywords' => null, 'executionType' => null, 'importance' => null,
		                       'testplan' => null, 'filter_tc_id' => null);
	
		$my['options'] = array_merge($my['options'], (array)$options);
		$my['filters'] = array_merge($my['filters'], (array)$filters);
	
		// new dBug($my['filters'], array('calledFrom' => $debugMsg));
		
		$exclude_branches = isset($filters['filter_toplevel_testsuite']) && 
							is_array($filters['filter_toplevel_testsuite']) ?
		                    $filters['filter_toplevel_testsuite'] : null;
		
		$tcase_prefix = $this->tprojectMgr->getTestCasePrefix($env['tproject_id']) . $this->cfg->glueChar;
		$test_spec = $this->tprojectMgr->get_subtree($env['tproject_id'],testproject::RECURSIVE_MODE,
			                                    	 testproject::INCLUDE_TESTCASES, $exclude_branches);
		
		// Added root node for test specification -> testproject
		$test_spec['name'] = $env['tproject_name'];
		$test_spec['id'] = $env['tproject_id'];
		$test_spec['node_type_id'] = $this->cfg->nodeTypeCode['testproject'];
		
		$map_node_tccount=array();
		$tplan_tcs=null;
		
		if($test_spec)
		{
			$tck_map = null;  // means no filter
			if(!is_null($my['filters']['filter_keywords']))
			{
				$tck_map = $this->tprojectMgr->get_keywords_tcases($env['tproject_id'],$my['filters']['filter_keywords'],
				           									 	   $my['filters']['filter_keywords_filter_type']);
				if( is_null($tck_map) )
				{
					$tck_map=array();  // means filter everything
				}
			}
			
		    if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes'])) 
		    {
		    	$test_spec['childNodes'] = $this->filterByCFValues($test_spec['childNodes'],
		    													   $my['filters']['filter_custom_fields']); 
		    }
			
			// Important: 
			// prepareNode() will make changes to $test_spec like filtering by test case keywords using $tck_map;
			// 
			//$pnFilters = null;
			//$keys2init = array('filter_testcase_name','filter_execution_type','filter_priority','filter_tc_id');
			//foreach ($keys2init as $keyname) 
			//{
			//	$pnFilters[$keyname] = isset($my['filters'][$keyname]) ? $my['filters'][$keyname] : null;
			//}
		    $pnFilters = &$my['filters'];
		    $pnFilters['setting_testplan'] = $my['filters']['setting_testplan'];
		    
		    $pnOptions = array('hideTestCases' => $my['options']['hideTestCases'], 
		    				   'viewType' => $my['options']['viewType'],	
			                   'ignoreInactiveTestCases' => $my['options']['ignore_inactive_testcases']);
			
			$testcase_counters = $this->prepareNode($test_spec,$map_node_tccount,$tck_map,$tplan_tcs,
													$pnFilters,$pnOptions);
	
			foreach($testcase_counters as $key => $value)
			{
				$test_spec[$key] = $testcase_counters[$key];
			}
			
			// new dBug($test_spec);
			
			$rnOptions = array('tc_action_enabled' => $my['options']['tc_action_enabled'],
							   'forPrinting' => $my['options']['forPrinting'],
							   'linkto' => $linkto, 'testCasePrefix' => $tcase_prefix,	
							   'showTestCaseID' => $this->cfg->showTestCaseID);
							   
			$menustring = $this->renderNode($env,1,$test_spec,$rnOptions);
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
	 * Filter out the testcases that don't have the given value 
	 * in their custom field(s) from the tree.
	 * Recursive function.
	 * 
   * Important Notice on logic (see section Apply Query To Filter): 
   * CF are linked to Test case versions.
   * When there are multiple versions of a TC, then row count of filtering query,
   * can be larger than the number of custom fields with the correct value,
   * if we consider ALL TEST CASE VERSIONS.
   * 
   * Example: 
   * Custom field "color", domain: red,blue,green. Default value => empty.
   * Custom field "status" domain: draft,ready,review,rework. Default value => empty.
   * 
   * TC Version 1: color=red, status=empty
   * TC Version 2: color=red, status=empty
   * TC Version 3: color=red, status=ready
   * TC Version 4: color=red, status=ready
   * 
   * Filter by color GREEN and status READY, then $rows looks like this: Array ( [0] => red, [1] => red )
   * => count($rows) returns 2, which matches the number of custom fields we want to filter by.
   * So TC seems OK, but instead is HAS TO BE EXCLUDED.
   * That is wrong, because TC matches only one of the fields we were filtering by!
   *   
   *	 
	 *
	 * @author Andreas Simon
	 * @since 1.9
	 * 
	 * @param array &$tcaseSet reference to test case set/tree to filter
	 * @param array &$cfSet reference to selected custom field information
	 * 
	 * @return array $tcase_tree filtered tree structure
	 * 
	 * @internal revisions
	 * 
	 */
	function filterByCFValues(&$tcaseSet, &$cfSet,$tcversionActiveAttr) 
	{
		static $debugMsg;
		static $cfQty = 0;
    static $sqlSet;

		if(!$debugMsg)
		{
			$debugMsg = 'Function: ' . __FUNCTION__;
			$cfQty = count($cfSet);
      $sqlSet = array();
      
      $activeClause = " AND TCVX.active = ";
      if( $tcversionActiveAttr == tlTestCaseFilterControl::ONLY_INACTIVE_TESTCASES )
      {
          $activeClause .= "0";
      }
      else
      {
          $activeClause .= "1";
      }
      $sqlSet['glaiv'] = " /* get latest TC version ID considering Active/Inactive Status */ " .
                         " SELECT MAX(TCVX.id) AS tcv_id, NHTCVX.parent_id AS tc_id " .
                         " FROM {$tables['tcversions']} TCVX " .
                         " JOIN {$tables['nodes_hierarchy']} NHTCVX " .
                         " ON NHTCVX.id = TCVX.id {$activeClause} " .
                         " WHERE NHTCVX.parent_id = ";
		}

		$rows = null;
		
		// if we delete a node, numeric indexes of array do have missing numbers ('holes'),
		// which causes problems in later loop constructs in other functions that 
		// assume numeric keys in these arrays without 'holes' -> crashes JS tree!
		// so reindex is needed to fix the array indexes.
		$doReindex = false;

		foreach($tcaseSet as $key => $node) 
		{
			if ($node['node_type_id'] == $this->cfg->nodeTypeCode['testsuite']) 
			{
				$doDel = true;
				if (isset($node['childNodes']) && is_array($node['childNodes'])) 
				{
					$tcaseSet[$key]['childNodes'] = $this->filterByCFValues($tcaseSet[$key]['childNodes'],$cfSet,
					                                                        $tcversionActiveAttr);
					
					// remove if it is empty after coming back from recursion
					$doDel = (count($tcaseSet[$key]['childNodes']) == 0);
				} 
				if ($doDel) 
				{
					unset($tcaseSet[$key]);
					$doReindex = true;
				}			
			} 
			else if ($node['node_type_id'] == $this->cfg->nodeTypeCode['testcase']) 
			{
				$doDel = true;
				$auxSQL = $sqlSet['glaiv'] . intval($node['id']) ." GROUP BY NHTCVX.parent_id ";
        $sql = " /* $debugMsg */ SELECT CFD.value " .
               " FROM {$tables['cfield_design_values']} CFD " .
               " JOIN ($auxSQL) LAIV ON LAIV.tcv_id = CFD.node_id " .
               " WHERE 0=0 ";

				if( isset($cfSet) ) 
				{	
					$cf_sql = '';
					$sqlOR = '';
					foreach ($cfSet as $cf_id => $cf_value) 
					{
						$cf_sql .= $sqlOR;

						if (is_array($cf_value)) 
						{
							$sqlAND = '';
							foreach ($cf_value as $value) 
							{
								$cf_sql .= $sqlAND . "( CFD.value LIKE '%{$value}%' AND CFD.field_id = {$cf_id} )";
								$sqlAND = " AND ";
							}
						} 
						else 
						{
							$cf_sql .= " ( CFD.value LIKE '%{$cf_value}%' AND CFD.field_id = {$cf_id} ) ";
						}
						$sqlOR = " OR ";
					}
					$sql .=  " AND ({$cf_sql}) ";
				}
	
				$rows = $this->db->fetchColumnsIntoArray($sql,'value');
				
				// if there exist as many rows as custom fields to be filtered by tc DOES MEET the criteria
				if ((count($rows) != $cfQty)) 
				{
					unset($tcaseSet[$key]);
					$doReindex = true;
				}
			}
		}
		
		if ($doReindex) 
		{
			$tcaseSet = array_values($tcaseSet);
		}
		
		return $tcaseSet;
	}




	/**
	 * Prepares a Node to be displayed in a navigation tree. (it calls itself).
	 * Used in the construction of:
	 *  - Test project specification -> we want ALL test cases defined in test project.
	 *  - Test execution             -> we only want the test cases linked to a test plan.
	 * 
	 * IMPORTANT:
	 * when analising a container node (Test Suite) if it is empty and we have requested
	 * some sort of filtering NODE WILL BE PRUNED.
	 *
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
	function prepareNode20(&$node,&$map_node_tccount,$tck_map = null,
	                     &$tplan_tcases = null,$filters=null, $options=null)
	{
		
		static $status_descr_code;
		static $status_code_descr;
		static $debugMsg;
	    static $my;
	    static $filterOn;
	    static $activeVersionClause;
	    static $filtersApplied;
		static $match;
		static $sqlTCVFilter;
	
	
	    $tpNode = null;
		if (!$debugMsg)
		{
	  	    $debugMsg = 'Class: ' . __CLASS__ . ' - ' . 'Method: ' . __FUNCTION__ . ' - ';
	
			$my = array();
			$my['options'] = array('hideTestCases' => 0, 'showTestCaseID' => 1, 'viewType' => 'testSpecTree',
			                       'getExternalTestCaseID' => 1,'ignoreInactiveTestCases' => 0);
	
			// asimon - added importance here because of "undefined" error in event log
			// TODO REVIEW URGENT
			$my['filters'] = array('status' => null, 'assignedTo' => null, 
			                       'importance' => null, 'executionType' => null,
			                       'filter_tc_id' => null);
			
			$my['options'] = array_merge($my['options'], (array)$options);
			$my['filters'] = array_merge($my['filters'], (array)$filters);
	
			$filterOn['testcase_id'] = isset($my['filters']['filter_tc_id']);
			$filterOn['testcase_name'] = isset($my['filters']['filter_testcase_name']);
			$filterOn['keywords'] = isset($tck_map);
			$filterOn['executionType'] = isset($my['filters']['filter_execution_type']);
			$filterOn['importance'] = isset($my['filters']['filter_importance']);
			$filterOn['custom_fields'] = isset($my['filters']['filter_custom_fields']);

			// $filterOn['importance'] = isset($my['filters']['filter_priority']);

			$sqlTCVFilter = '';
			if( $filterOn['executionType'] )
			{
				$sqlTCVFilter .= " AND TCV.execution_type = {$my['filters']['filter_execution_type']} ";
			}
			if( $filterOn['importance'] )
			{
				$sqlTCVFilter .= " AND TCV.importance = {$my['filters']['filter_importance']} ";
			}

						
			$filtersApplied = false;
			foreach($filterOn as $filterValue)
			{
				$filtersApplied = $filtersApplied || $filterValue; 
			}
			
			$activeVersionClause = ($filterOn['executionType'] || $filterOn['importance']) ? " AND TCV.active=1 " : '';

			$k2l = array('user' => 'filter_assigned_user', 'result' => 'filter_result_result');
			foreach($k2l as $k => $l)
			{
				$match[$k] = isset($my['filters'][$l]) ? $my['filters'][$l] : null;
			}
		}
			
		$counterDomain = array_merge(array('testcase_count'),array_keys($this->cfg->results['status_code']));
		$tcase_counters = array_fill_keys($counterDomain,0);
		$node_type = isset($node['node_type_id']) ? $this->cfg->nodeCodeType[$node['node_type_id']] : null;
	
		if($node_type == 'testcase')
		{
			if( $filterOn['keywords'] && !isset($tck_map[$node['id']]) )
			{
				unset($tplan_tcases[$node['id']]);
				$node = null;
			}
			
			if( ($node && $filterOn['testcase_name']) &&
				(stripos($node['name'], $my['filters']['filter_testcase_name']) === FALSE))
			{
				// IMPORTANT:
				// checking with === here, because function stripos could return 0 when string
				// is found at position 0, if clause would then evaluate wrong because 
				// 0 would be casted to false and we only want to delete node if it really is false
				unset($tplan_tcases[$node['id']]);
				$node = null;
			}
			
			if( ($node && $filterOn['testcase_id']) && ($node['id'] != $my['filters']['filter_tc_id']) ) 
			{
				unset($tplan_tcases[$node['id']]);
				$node = null;
			}
			
			$viewType = $my['options']['viewType'];
			if ($node && $viewType == 'executionTree')
			{
				
				$tpNode = isset($tplan_tcases[$node['id']]) ? $tplan_tcases[$node['id']] : null;
		
				$doDel = is_null($tpNode) || 
						    (!is_null($match['result']) && !isset($match['result'][$tpNode['exec_status']]) );
				
				if( !$doDel && !is_null($match['user']) )
				{
					$doDel = ( isset($match['user'][TL_USER_SOMEBODY]) && !is_numeric($tpNode['user_id']) ) || 
							      ( isset($match['user'][TL_USER_NOBODY]) && !is_null($tpNode['user_id']) ) ||
				             ( !isset($match['user'][TL_USER_NOBODY]) && !isset($match['user'][TL_USER_SOMEBODY]) &&
				               !isset($match['user'][$tpNode['user_id']]) );
				}  
			
				if ($doDel) 
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

						// need to understand when this we get this situation
						if (!isset($tpNode['external_id']))
						{
							$sql = " /* $debugMsg - line:" . __LINE__ . " */ " . 
							       " SELECT TCV.tc_external_id AS external_id " .
								   " FROM {$this->tables['tcversions']}  TCV " .
								   " WHERE TCV.id=" . $node['tcversion_id'];
							
							$result = $this->db->exec_query($sql);
							$myrow = $this->db->fetch_array($result);
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
				$sql=" /* $debugMsg - line:" . __LINE__ . " */ " . 
				     " SELECT count(TCV.id) AS num_active_versions " .
					 " FROM {$this->tables['tcversions']} TCV, {$this->tables['nodes_hierarchy']} NH " .
					 " WHERE NH.parent_id=" . $node['id'] .
					 " AND NH.id = TCV.id AND TCV.active=1";
				
				$result = $this->db->exec_query($sql);
				$myrow = $this->db->fetch_array($result);
				if($myrow['num_active_versions'] == 0)
				{
					$node = null;
				}
			}
			
			// -------------------------------------------------------------------------------
			if ($node && ($viewType=='testSpecTree' || $viewType=='testSpecTreeForTestPlan') )
			{
				$sql = " /* $debugMsg - line:" . __LINE__ . " */ " . 
				       " SELECT COALESCE(MAX(TCV.id),0) AS targetid, TCV.tc_external_id AS external_id" .
					   " FROM {$this->tables['tcversions']} TCV " .
					   " JOIN {$this->tables['nodes_hierarchy']} NH_TCV " .
					   " ON NH_TCV.id = TCV.id {$activeVersionClause} " .
					   " WHERE NH_TCV.parent_id={$node['id']} " .
					   " GROUP BY TCV.tc_external_id ";
				   
				$rs = $this->db->get_recordset($sql);
				// new dBug($rs, array('calledFrom' => 'Class:' . __CLASS__ . ' Method:' . __FUNCTION__));
				if( is_null($rs) )
				{
					$node = null;
				}
				else
				{	
				    $node['external_id'] = $rs[0]['external_id'];
				    $target_id = $rs[0]['targetid'];
					
					// new dBug($filterOn);
					if( $filterOn['executionType'] || $filterOn['importance'] )
					{
						switch ($viewType)
						{
							case 'testSpecTreeForTestPlan':
								// Try to get info from linked tcversions Platform is not needed
								$sql = " /* $debugMsg - line:" . __LINE__ . " */ " . 
									   " SELECT DISTINCT TPTCV.tcversion_id AS targetid " .
									   " FROM {$this->tables['tcversions']} TCV " .
									   " JOIN {$this->tables['nodes_hierarchy']} NH " .
									   " ON NH.id = TCV.id {$activeVersionClause} " .
									   " AND NH.parent_id={$node['id']} " .
									   " JOIN {$this->tables['testplan_tcversions']} TPTCV " .
									   " ON TPTCV.tcversion_id = TCV.id " .
									   " AND TPTCV.testplan_id = {$my['filters']['setting_testplan']}";
				    			$rs = $this->db->get_recordset($sql);
								$target_id = !is_null($rs) ? $rs[0]['targetid'] : $target_id;
							break;
						}		
						
						$sql = " /* $debugMsg - line:" . __LINE__ . " */ " . 
							   " SELECT TCV.execution_type,TCV.importance " .
							   " FROM {$this->tables['tcversions']} TCV " .
							   " WHERE TCV.id = {$target_id} {$sqlTCVFilter}";
		
				    	$rs = $this->db->fetchRowsIntoMap($sql,'execution_type');
				    	if(is_null($rs))
				    	{
				    		$node = null;
				    	}
				    }
				} 
	            if( !is_null($node) )
	            {
					// needed to avoid problems when using json_encode with EXTJS
					//new dBug($node);
					unset($node['childNodes']);
					$node['leaf']=true;
					//new dBug($node);
				}
			}
			// -------------------------------------------------------------------
			
			$tcase_counters = array_fill_keys(array_keys($tcase_counters),0);
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
			$tcase_counters[$tc_status_descr] = $init_value;
			$tcase_counters['testcase_count'] = $init_value;
			if ( $my['options']['hideTestCases'] )
			{
				$node = null;
			} 
			// new dBug($node, array('calledFrom' => $debugMsg . 'Line:' . __LINE__));
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
				
				$counters_map = $this->prepareNode($current,$map_node_tccount,$tck_map,$tplan_tcases,
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
	function renderNode($env,$level,&$node,$options)
	{
		$menustring='';
		$nodeAttr = array('node_type' => $this->cfg->nodeCodeType[$node['node_type_id']], 
						  'testCasePrefix' => $options['testCasePrefix']);
						  
					 
		$this->renderTestSpecNode($node,$nodeAttr,$options,$env);
		if (isset($node['childNodes']) && $node['childNodes'])
		{
			// need to work always original object in order to change it's values using reference .
			// Can not assign anymore to intermediate variables.
			//
			for($idx = 0, $nChildren = sizeof($node['childNodes']); $idx < $nChildren; $idx++)
			{
				if(!isset($node['childNodes'][$idx]))
				{
					continue;
				}
				$menustring .= $this->renderNode($env,$level+1,$node['childNodes'][$idx],$options);
			}
		}
		
		return $menustring;
	}



	/**
	 *
	 * @internal revisions
	 */
	function renderTestSpecNode(&$node,$nodeAttr,$options,$env)
	{
		$name = $this->filterString($node['name']);
		$pfn = "ET";
		$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;	
		
		switch($nodeAttr['node_type'])
		{
			case 'testproject':
				$pfn = $options['forPrinting'] ? 'TPROJECT_PTP' : 'EP';
				$label =  $name . " (" . $testcase_count . ")";
				break;
				
			case 'testsuite':
				$pfn = $options['forPrinting'] ? 'TPROJECT_PTS' : 'ETS';
				$label =  $name . " (" . $testcase_count . ")";	
				break;
				
			case 'testcase':
				if (!$options['tc_action_enabled'])
				{
					$pfn = "void";
				}
				
				$label = "";
				if($options['showTestCaseID'])
				{
					$label .= "<b>{$nodeAttr['testCasePrefix']}{$node['external_id']}</b>:";
				} 
				$label .= $name;
				break;
				
		} // switch	
		
		$node['text'] = $label;
		$node['testlink_node_name'] = $name;
	   	$node['testlink_node_type'] = $nodeAttr['node_type'];
		$node['position']=isset($node['node_order']) ? $node['node_order'] : 0;
		
		$node['href']='';
		if(!is_null($pfn))
		{
			$node['href'] = "javascript:{$pfn}({$env['tproject_id']},";
			$node['href'] .= ($pfn == 'ET') ? "{$node['id']})" : "{$env['tplan_id']},{$node['id']})";
		}
		
		// Remove useless keys
		foreach($this->cfg->renderTestSpecNode->key2del as $key)
		{
			if(isset($node[$key]))
			{
				unset($node[$key]); 
			}  
		}
		
	}



	/** 
	 * Creates data for tree menu used on :
	 * - Execution of Test Cases
	 * - Remove Test cases from test plan
	 * 
	 * @internal Revisions:
	 *
	 *	20101003 - franciscom - added option remove_empty_nodes_of_type on get_subtree() call
	 */
	function generateExecTree(&$db,&$menuUrl,$env,$filters,$options) 
	{
		$tplan_tcases = null;
		$tck_map = null;
	    $idx=0;
	    $apply_other_filters=true;
	    $map_node_tccount = array();
		$renderOpt = array();
		$renderAux = array();
	
		$tplan_mgr = new testplan($db);
		$tproject_mgr = new testproject($db);
	
		// ---------------------------------------------------------------------------------------------
		// initialize configuration and options
		// ---------------------------------------------------------------------------------------------
		$tproject_id = $env['tproject_id'];
		$tplan_id = $env['tplan_id'];
	
	    $my['filters'] = normalizeFilters($filters);
	
		$node_types = $tproject_mgr->tree_manager->get_available_node_types();
		$renderAux['hash_id_descr'] = array_flip($node_types);
		$renderAux['testCasePrefix'] = $tproject_mgr->getTestCasePrefix($tproject_id) . config_get('testcase_cfg')->glue_character;
	
		$renderOpt['showTestCaseID'] = config_get('treemenu_show_testcase_id');
		$renderOpt['hideTCs'] = isset($filters->hide_testcases) ? $filters->hide_testcases : false;
		$renderOpt['showTestSuiteContents'] =  	isset($filters->show_testsuite_contents) ? 
		                           			 	$filters->show_testsuite_contents : true;
		$renderOpt['useCounters'] = isset($options->useCounters) ? $options->useCounters : false;
		$renderOpt['colorOptions'] = isset($options->colorOptions) ? $options->colorOptions : null;
	    $renderOpt['tc_action_enabled'] = isset($options->tc_action_enabled) ? $options->tc_action_enabled : false; 
	
		$colorBySelectedBuild = isset($options->testcases_colouring_by_selected_build) ? 
		                        $options->testcases_colouring_by_selected_build : false;
		// ---------------------------------------------------------------------------------------------
	
		// echo __LINE__;
		$test_spec = getTestSpec4ExecTree($tplan_mgr->tree_manager,$env,$my['filters']);     
		
		if( ($doIt = !is_null($test_spec)) )
		{
			if(is_null($my['filters']->filter_tc_id) || $my['filters']->filter_tc_id >= 0)
			{
				list($tplan_tcases,$tck_map) = getTPlanTCases4ExecTree($db,$tproject_mgr,$tplan_mgr,$env,$my['filters']);
			}   
			
			// new dBug($tplan_tcases);
			// new dBug($tck_map);
			
			if (is_null($tplan_tcases))
			{
				$tplan_tcases = array();
				$apply_other_filters=false;
			}
			else
			{
				$tplan_tcases = applyFilters4ExeTree($tplan_mgr, $tplan_tcases, $env['tplan_id'],$filters); 
				// new dBug($tplan_tcases);
			}
			
			
			$apply_other_filters = (!is_null($tplan_tcases) && (count($tplan_tcases) >0) );
		
			// BUGID 3450 - Change colors/counters in exec tree.
			// Means: replace exec status in filtered array $tplan_tcases  by the one of last execution of selected build.
			// Since this changes exec status, replacing is done after filtering by status.
			// It has to be done before call to prepareNode() though, because that one sets the counters according to status.
			if ($apply_other_filters && (!is_null($renderOpt['colorOptions']) && $colorBySelectedBuild) ) 
			{
				$tplan_tcases = updateStatus4ExecTree($db,$tplan_tcases,$env['tplan_id'],
													  $filters->selected_build,$this->cfg->results);
			}
			
			// 20080224 - franciscom - 
			// After reviewing code, seems that assignedTo has no sense because tp_tcs
			// has been filtered.
			// Then to avoid changes to prepareNode() due to include_unassigned,
			// seems enough to set assignedTo to 0, if include_unassigned==true
			$pnFilters['assignedTo'] = 	$my['filters']->filter_assigned_user_include_unassigned ? null : 
										$my['filters']->filter_assigned_user;
			
			$keys2init = array('filter_testcase_name','filter_execution_type','filter_priority');
			foreach ($keys2init as $keyname) {
				$pnFilters[$keyname] = isset($filters->{$keyname}) ? $filters->{$keyname} : null;
			}
		    		
			$pnOptions = array('hideTestCases' => $renderOpt['hideTCs'], 'viewType' => 'executionTree');
			
		
			$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
			                                 $tck_map,$tplan_tcases,$pnFilters,$pnOptions);
	
			foreach($testcase_counters as $key => $value)
			{
				$test_spec[$key] = $testcase_counters[$key];
			}
			$keys = array_keys($tplan_tcases);
	
			// IMPORTANT NOTICE: process makes changes on $test_spec
			renderExecTreeNode($env,1,$test_spec,$tplan_tcases,$menuUrl,$renderOpt,$renderAux);
		}  // if($test_spec)
	
		
		$treeMenu = new stdClass(); 
		$treeMenu->menustring = '';
		$treeMenu->rootnode=new stdClass();
		$treeMenu->rootnode->name=$test_spec['text'];
		$treeMenu->rootnode->id=$test_spec['id'];
		$treeMenu->rootnode->leaf=$test_spec['leaf'];
		$treeMenu->rootnode->text=$test_spec['text'];
		$treeMenu->rootnode->position=$test_spec['position'];	    
		$treeMenu->rootnode->href=$test_spec['href'];
		$menustring = '';
	
	
		// new dBug($test_spec['childNodes']);
		if( $doIt)
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
	 * 
	 * 
	 * @param integer $level
	 * @param array &$node reference to recursive map
	 * @param array &$tcases_map reference to map that contains info about testcase exec status
	 *              when node is of testcase type.
	 * @param boolean $bHideTCs 1 -> hide testcase
	 * 
	 * @return datatype description
	 * 
	 * @internal revisions
	 */                      
	function renderExecTreeNode($env,$level,&$node,&$tcase_node,$linkto,$options,$auxCfg)
	{
		$node_type = $auxCfg['hash_id_descr'][$node['node_type_id']];
		$nodeAttr = array('node_type' => $node_type, 'testCasePrefix' => $auxCfg['testCasePrefix']);
	    extjs_renderExecTreeNodeOnOpen($node,$nodeAttr,$tcase_node,$options,$env);
		
		// echo $node['id'] . '<br>';
		// new dBug($tcase_node);
		
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
				renderExecTreeNode($env,$level+1,$node['childNodes'][$idx],$tcase_node,$linkto,$options,$auxCfg);
			}
		}
	}


	/**
	 *
	 * @internal revisions
	 */
	function applyFilters4ExeTree(&$tplanMgr, $tplan_tcases, $tplan_id, $filters) 
	{
		$items = $tplan_tcases;
		
		$filter_methods = config_get('execution_filter_methods');
		
		$ffn = array($filter_methods['status_code']['any_build'] => 'filter_by_status_for_any_build',
			         $filter_methods['status_code']['all_builds'] => 'filter_by_same_status_for_all_builds',
			         $filter_methods['status_code']['specific_build'] => 'filter_by_status_for_build',
			         $filter_methods['status_code']['current_build'] => 'filter_by_status_for_build',
			         $filter_methods['status_code']['latest_execution'] => 'filter_by_status_for_last_execution');
		
		$requested_filter_method = isset($filters->filter_result_method) ? $filters->filter_result_method : null;
		$requested_filter_result = isset($filters->filter_result_result) ? $filters->filter_result_result : null;
		
		// if "any" was selected as filtering status, don't filter by status
		$requested_filter_result = (array)$requested_filter_result;
		
		if (in_array($this->cfg->results['status_code']['all'], $requested_filter_result)) {
			$requested_filter_result = null;
		}
	
		if (!is_null($requested_filter_method) && isset($ffn[$requested_filter_method])) 
		{
			// special case 1: when filtering by "not run" status in any build,
			// we need another filter function
			if (in_array($this->cfg->results['status_code']['not_run'], $requested_filter_result)) 
			{
				$ffn[$filter_methods['status_code']['any_build']] = 'filter_not_run_for_any_build';
			}
			
			// special case 2: when filtering by "current build", we set the build to filter with
			// to the build chosen in settings instead of the one in filters
			if ($requested_filter_method == $filter_methods['status_code']['current_build']) 
			{
				$filters->filter_result_build = $filters->setting_build;
			}
			
			// call the filter function and do the filtering
			$items = $ffn[$requested_filter_method]($tplanMgr, $tplan_tcases, $tplan_id, $filters);
	
			if (is_null($items)) {
				$items = array();
			}
		}
	
		return $items;
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
			// BUGID 4023
			$tcase_build_set = $tplan_mgr->get_status_for_any_build($tplan_id,
			                                   array_keys($buildSet),$filters->{$status}, $filters->setting_platform);  
			                                                             
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
			// BUGID 4023
			$tcase_build_set = $tplan_mgr->get_same_status_for_build_set($tplan_id,
			                                                             array_keys($buildSet),$filters->{$status},$filters->setting_platform);  
			                               
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
	 *
	 * @internal revisions
	 */
	function filterString($str)
	{
		$str = str_replace(array("\n","\r"), array("",""), $str);
		$str = htmlspecialchars($str, ENT_QUOTES);	
		return $str;
	}

  function filter_by_cf_values(&$tcase_tree,&$cf_hash)
  {
  	static $debugMsg = null;
  	static $sqlSet = null;
  
  	if (!$debugMsg)
  	{
  		$debugMsg = 'Function: ' . __FUNCTION__;
      $sqlSet = array();
      $sqlSet['glav'] = " /* get latest active TC version ID */ " .
                        " SELECT MAX(TCVX.id) AS max_tcv_id, NHTCX.parent_id AS tc_id " .
                        " FROM {$tables['tcversions']} TCVX " .
                        " JOIN {$tables['nodes_hierarchy']} NHTCX " .
                        " ON NHTCX.id = TCVX.id AND TCVX.active = 1 " .
                        " WHERE NHTCX.parent_id = ";
  	}
  
  	$rows = null;
  	$node_deleted = false;
  	foreach ($tcase_tree as $key => $node) 
  	{
  		if ($node['node_type_id'] == $this->cfg->nodeTypeCode['testsuite']) 
  		{
  			$delete_suite = false;
  			if (isset($node['childNodes']) && is_array($node['childNodes'])) 
  			{
  				// node is a suite and has children, so recurse one level deeper			
  				$tcase_tree[$key]['childNodes'] = $this->filter_by_cf_values($tcase_tree[$key]['childNodes'],$cf_hash);
  				$delete_suite = !count($tcase_tree[$key]['childNodes']);
  			} 
  			else 
  			{
  				$delete_suite = true; // nothing in here, suite was already empty
  			}
  			
  			if ($delete_suite) 
  			{
  				unset($tcase_tree[$key]);
  				$node_deleted = true;
  			}
  		} 
  		else if ($node['node_type_id'] == $this->cfg->nodeTypeCode['testcase']) 
  		{
        // TICKET 5186: added "DISTINCT" to SQL clause, detailed explanation follows at the end of function
        // Note: SQL statement has been adopted to filter by latest active tc version.
        // That is a better solution for the explained problem than using the distinct keyword.
        $latest_active_version_sql = $sqlSet['glav'] . intval($node['id']) .
                                     " GROUP BY NHTCX.parent_id, TCVX.tc_external_id ";
              
        $sql = " /* $debugMsg */ SELECT CFD.value " .
               " FROM {$tables['cfield_design_values']} CFD, {$tables['nodes_hierarchy']} NH " .
               " JOIN ( $latest_active_version_sql ) LAVSQL ON NH.id = LAVSQL.max_tcv_id " .
               " WHERE CFD.node_id = NH.id ";
  
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
              }
            }
            else 
            {
                $cf_sql .= " ( CFD.value LIKE '%{$cf_value}%' AND CFD.field_id = {$cf_id} ) ";
            }
            $countmain++;
          }
          $sql .=  " AND ({$cf_sql}) ";
        }
  
  			$rows = $db->fetchColumnsIntoArray($sql,'value');
  			
        // 
        // Because CF are linked to Test case versions, if there are multiple versions of a TC,
        // then the row count here can be larger than the number of custom fields with the correct value.
        // 
        // Example: 
        // Custom field "color" has possible values "red", "blue", "green", default empty.
        // Custom field "status" has possible values "draft", "ready", "needs review", "needs rework", default empty.
        // 
        // TC Version 1: cfield color=red, cfield status=empty
        // TC Version 2: cfield color=red, cfield status=empty
        // TC Version 3: cfield color=red, cfield status=ready
        // TC Version 4: cfield color=red, cfield status=ready
        // 
        // Filter by color GREEN and status READY, then $rows looks like this: Array ( [0] => red, [1] => red )
        // => count($rows) returns 2, which matches the number of custom fields we want to filter by.
        // So TC seems OK, but instead is HAS TO BE EXCLUDED.
        // That is wrong, because TC matches only one of the fields we were filtering by!
        // 
        // Because of this I extended the SQL statement above with the DISTINCT keyword,
        // so that each custom field only is contained ONCE in the result set.
        // 
              
  			if( !($passed = (count($rows) == count($cf_hash))) ) 
  			{
  				unset($tcase_tree[$key]);
  				$node_deleted = true;
  			}
  		}
  	}
  	
  	// when deleting, the numeric indexes of this array do have missing numbers,
  	// which causes problems in later loop constructs in other functions that assume numeric keys
  	// in these arrays without missing numbers in between - crashes JS tree!
  	// -> so I have to fix the array indexes here starting from 0 without missing a key 
  	if ($node_deleted) 
  	{
  		$tcase_tree = array_values($tcase_tree);
  	}
  	
  	return $tcase_tree;
  }



  function prepareNode(&$node,&$map_node_tccount,$tck_map = null,
                       &$tplan_tcases = null,$filters=null, $options=null)
  {
  	
  	static $status_counter_keys;
  	static $debugMsg;
    static $my;
    static $filterOn;
    static $activeVersionClause;
    static $filterOnTCVersionAttribute;
    static $filtersApplied;
    static $match;
 
  	if (!$debugMsg)
  	{
    	$debugMsg = 'Class: ' . __CLASS__ . ' - ' . 'Method: ' . __FUNCTION__ . ' - ';
  		$status_counter_keys = array_keys($this->cfg->results['status_code']);
  		$status_counter_keys[] = 'testcase_count';
  		                   
      list($my,$filterOn,$activeVersionClause,$filtersApplied,$match) = $this->helperPNInit($filters,$options);  		                   
  	}

    $tpNode = null;
  	$tcase_counters = array_fill_keys($status_counter_keys, 0);

  	$node_type = isset($node['node_type_id']) ? $this->cfg->nodeCodeType[$node['node_type_id']] : null;
  	if($node_type == 'testcase')
  	{
  		if( ($filterOn['keywords'] && !isset($tck_map[$node['id']])) ||
  			  ($filterOn['testcase_name'] && 
  				stripos($node['name'], $my['filters']['filter_testcase_name']) === FALSE)  ||
  			  ($filterOn['testcase_id'] && ($node['id'] != $my['filters']['filter_tc_id']) ) 
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
				  $doDel = is_null($tpNode) || 
						       (!is_null($match['result']) && !isset($match['result'][$tpNode['exec_status']]) );

					if( !$doDel && !is_null($match['user']) )
				  {
				    // somebody wanted but nobody there
				    // unassigned wanted but someone assigned
				    // other condition difficult to explain
					  $doDel = ( isset($match['user'][TL_USER_SOMEBODY]) && !is_numeric($tpNode['user_id']) ) || 
							       ( isset($match['user'][TL_USER_NOBODY]) && !is_null($tpNode['user_id']) ) ||
				             ( !isset($match['user'][TL_USER_NOBODY]) && !isset($match['user'][TL_USER_SOMEBODY]) &&
				               !isset($match['user'][$tpNode['user_id']]) );
				  }  
 
  				if( $doDel ) 
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
  
              // TICKET 4496: added inactive testcase filter
              if ($node && $my['options']['ignoreActiveTestCases'])
              {
                  $sql=" /* $debugMsg - line:" . __LINE__ . " */ " .
                      " SELECT count(TCV.id) AS num_active_versions " .
                      " FROM {$tables['tcversions']} TCV, {$tables['nodes_hierarchy']} NH " .
                      " WHERE NH.parent_id=" . $node['id'] .
                      " AND NH.id = TCV.id AND TCV.active=1";
  
                  $result = $db->exec_query($sql);
                  $myrow = $db->fetch_array($result);
                  if($myrow['num_active_versions'] != 0)
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
  					 	   
  					if( $filterOn['executionType'] )
  					{
  						$sql .= " AND TCV.execution_type = {$my['filters']['filter_execution_type']} ";
  					}
  					
  					if( $filterOn['importance'] )
  					{
  						$sql .= " AND TCV.importance = {$my['filters']['filter_priority']} ";
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



  function helperPNInit($filters,$options)
  {
    $my = array();
    $my['options'] = array('hideTestCases' => 0, 'showTestCaseID' => 1, 'viewType' => 'testSpecTree',
                           'getExternalTestCaseID' => 1);
    
    $my['filters'] = array('status' => null, 'assignedTo' => null,'importance' => null, 'executionType' => null,
                           'filter_tc_id' => null);
    
    $my['options'] = array_merge($my['options'], (array)$options);
    $my['filters'] = array_merge($my['filters'], (array)$filters);
    
    $filterOn['testcase_id'] = isset($my['filters']['filter_tc_id']);
    $filterOn['testcase_name'] = isset($my['filters']['filter_testcase_name']);
    $filterOn['executionType'] = isset($my['filters']['filter_execution_type']);
    $filterOn['importance'] = isset($my['filters']['filter_priority']);
    $filterOn['custom_fields'] = isset($my['filters']['filter_custom_fields']);
    $filterOn['keywords'] = isset($tck_map);
    $filterOn['active_inactive'] = isset($my['filters']['active_inactive']) && 
                                   $my['filters']['active_inactive'] != tlTestCaseFilterControl::ACTIVE_INACTIVE_TESTCASES_NO_FILTER;
    
    
    $filterOnTCVersionAttribute = $filtersOn['executionType'] || $filtersOn['importance'];
    $activeVersionClause = $filterOnTCVersionAttribute ? " AND TCV.active=1 " : '';
    			
    $filtersApplied = false;
    foreach($filtersOn as $filterValue)
    {
    	$filtersApplied = $filtersApplied || $filterValue; 
    }
    
		$k2l = array('user' => 'filter_assigned_user', 'result' => 'filter_result_result');
		foreach($k2l as $k => $l)
		{
			$match[$k] = isset($my['filters'][$l]) ? $my['filters'][$l] : null;
		}
    
    return array($my,$filterOn,$activeVersionClause,$filtersApplied,$match);    
  }
  
} // Class End