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
 * @package 	  TestLink
 * @author 		  Francisco Mancardi
 * @copyright 	2012, TestLink community 
 * @link 		    http://www.teamst.org/index.php
 * @uses 		    config.inc.php
 * @since       2.0
 *
 * @internal revisions
 */
class tlExecTreeMenu extends tlObjectWithDB
{
  var $cfg;
  var $tplan_mgr;
  var $tproject_mgr;
  
 	public function __construct(&$dbHandler) 
	{
		$this->db = $dbHandler;
  	$this->tplan_mgr = new testplan($dbHandler);
  	$this->tproject_mgr = new testproject($dbHandler);

  	$this->cfg->tcaseNodeType = $this->tplan_mgr->tree_manager->node_descr_id['testcase'];

  	$this->cfg->nodeTypeCode = $this->tplan_mgr->tree_manager->get_available_node_types();
  	$this->cfg->nodeCodeType = array_flip($this->cfg->nodeTypeCode);

    $this->cfg->results = config_get('results');
  	$this->cfg->showTestCaseID = config_get('treemenu_show_testcase_id');
  	$this->cfg->glueChar = config_get('testcase_cfg')->glue_character;
	  
  }

  function execTree(&$menuUrl,$context,$objFilters,$objOptions) 
  {
   	$chronos[] = microtime(true);
    $testCaseQty = 0;
  	$menustring = $any_exec_status = $tplan_tcases = $tck_map = $testCaseSet = null;
  
  	$tcase_prefix = $this->tproject_mgr->getTestCasePrefix($context->tproject_id) . $this->cfg->glueChar;
  	list($my,$filters,$options,$colorBySelectedBuild) = $this->initExecTree($context,$objFilters,$objOptions);
 	      
    $test_spec = $this->tplan_mgr->getSkeleton($context->tplan_id,$context->tproject_id,$my['filters'],$my['options']);
  	$test_spec['name'] = $context->tproject_name . " / " . $context->tplan_name;  // To be discussed
  	$test_spec['id'] = $context->tproject_id;
  	$test_spec['node_type_id'] = $this->nodeTypeCode['testproject'];
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
  			if( !is_null($sql2do = $this->tplan_mgr->getLinkedForExecTree($context->tplan_id,$filters,$options)) )
  			{
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
  				$tplan_tcases = $setTestCaseStatus = $this->db->$kmethod($sql2run,'tcase_id');
  				
  			}
  		}   
  		if (is_null($tplan_tcases))
  		{
  			$tplan_tcases = array();
  			$apply_other_filters=false;
  		}
 
  		// OK, now we need to work on status filters
  		// New dBug($objFilters);
  		// New dBug($objOptions);
  		// if "any" was selected as filtering status, don't filter by status
  		$targetExecStatus = (array)(isset($objFilters->filter_result_result) ? $objFilters->filter_result_result : null);
  		if( !is_null($targetExecStatus) && (!in_array($this->resultsCfg['status_code']['all'], $targetExecStatus)) ) 
  		{
  			$this->applyStatusFilters($context->tplan_id,$tplan_tcases,$objFilters);       
  		}
  		
  		if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes']))
      {
        $test_spec['childNodes'] = $this->tprojectMgr->filterByCFValues($test_spec['childNodes'],
                                                                        $my['filters']['filter_custom_fields'],
                                                                        $my['filters']['filter_active_inactive']);
      }
  
  		// ATTENTION: sometimes we use $my['options'], other $options
  	  $pnFilters = null;		
  		$pnOptions = array('hideTestCases' => $options['hideTestCases'], 'viewType' => 'executionTree');
  		$testcase_counters = $this->prepareExecTreeNode($test_spec,$map_node_tccount,
  		                                  		          $tplan_tcases,$pnFilters,$pnOptions);
  		foreach($testcase_counters as $key => $value)
  		{
  			$test_spec[$key] = $testcase_counters[$key];
  		}
  	
  		$keys = array_keys($tplan_tcases);
  		$menustring = $this->renderExecTreeNode(1,$test_spec,$tplan_tcases,$menuUrl,$tcPrefix,$options);
  	}  // if($test_spec)
  	
  		
  	$treeMenu->rootnode=new stdClass();
  	$treeMenu->rootnode->name = $test_spec['text'];
  	$treeMenu->rootnode->id = $test_spec['id'];
  	$treeMenu->rootnode->leaf = $test_spec['leaf'];
  	$treeMenu->rootnode->text = $test_spec['text'];
  	$treeMenu->rootnode->position = $test_spec['position'];	    
  	$treeMenu->rootnode->href = $test_spec['href'];
  	
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
  
  
  /*
   *
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function initExecTree($context,$filtersObj,$optionsObj)
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
  			$filters[$key] = isset($filtersObj->$prop) ? $filtersObj->$prop : null; 
  		}	
  	}
  
  	$filters['keyword_id'] = 0;
  	$filters['keyword_filter_type'] = 'Or';
  	if (property_exists($filtersObj, 'filter_keywords') && !is_null($filtersObj->filter_keywords)) 
  	{
  		$filters['keyword_id'] = $filtersObj->filter_keywords;
  		$filters['keyword_filter_type'] = $filtersObj->filter_keywords_filter_type;
  	}
  
  	$colorBySelectedBuild = isset($optionsObj->testcases_colouring_by_selected_build) ? 
  	                        $optionsObj->testcases_colouring_by_selected_build : null;

  
  	$options['hideTestCases'] = isset($optionsObj->hideTestCases) ?
  	                      	          $optionsObj->hideTestCases : false;
  
  	$options['include_unassigned'] = isset($filtersObj->filter_assigned_user_include_unassigned) ?
  	                      			 $filtersObj->filter_assigned_user_include_unassigned : false;
  
  	// useful when using tree on set urgent test cases
  	$options['allow_empty_build'] = isset($optionsObj->allow_empty_build) ?
  	                      			      $optionsObj->allow_empty_build : false;
  
  
  	// NOT CLEAR what to do
  	// $status = isset($filters->filter_result_result) ? $filters->filter_result_result : null;
  	$options['showTestSuiteContents'] = isset($filtersObj->show_testsuite_contents) ? 
  	                                          $filtersObj->show_testsuite_contents : true;
  
  	
  	$options['useCounters'] = isset($optionsObj->useCounters) ? $optionsObj->useCounters : null;
  	$options['useColors'] = isset($optionsObj->useColours) ? $optionsObj->useColours : null;
  

    // remove test spec, test suites (or branches) that have ZERO test cases linked to test plan
    // 
    // IMPORTANT:
    // using 'order_cfg' => array("type" =>'exec_order',"tplan_id" => $tplan_id))
    // makes the magic of ignoring test cases not linked to test plan.
    // This unexpected bonus can be useful on export test plan as XML.
    //
    $my = array();
    $my['options'] = array('recursive' => true,'remove_empty_nodes_of_type' => $this->cfg->nodeTypeCode['testsuite'],
                           'order_cfg' => array("type" =>'exec_order',"tplan_id" => $context->tplan_id,
                                                'hideTestCases' => $options['hideTestCases']));
    
    $my['filters'] = array('exclude_node_types' => 
                           array('testplan' => 'bye','requirement_spec'=> 'bye','requirement'=> 'bye'),
                           'exclude_children_of' => 
                           array('testcase' => 'bye_children','requirement_spec'=> 'bye_children'));
    
    $k2s = array('exclude_branches' => 'filter_toplevel_testsuite','filter_custom_fields' => 'filter_custom_fields');
    foreach($k2s as $fkey => $prop)
    {
     	if (isset($objFilters->$prop) && is_array($objFilters->$prop)) 
     	{
     		$my['filters'][$fkey] = $filtersObj->$prop;
     	}
    }

  
  	return array($my,$filters,$options,$colorBySelectedBuild);
  
  }
  
  
  
  
  function prepareExecTreeNode(&$node,&$map_node_tccount,&$tplan_tcases = null,
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
  			
  			$counters_map = $this->prepareExecTreeNode($current,$map_node_tccount,$tplan_tcases,
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
  
  
  
  function applyStatusFilters($tplan_id,&$items2filter,&$fobj)
  {
  	$fm = config_get('execution_filter_methods');
  	$methods = $fm['status_code'];
 	
  	$ffn = array($methods['any_build'] => 'filterStatusSetAtLeastOneOfActiveBuilds',
  		           $methods['all_builds'] => 'filterStatusSetAllActiveBuilds',
  		           $methods['specific_build'] => 'filter_by_status_for_build',
  		           $methods['current_build'] => 'filter_by_status_for_build',
  		           $methods['latest_execution'] => 'filter_by_status_for_latest_execution');
  	
  	$f_method = isset($fobj->filter_result_method) ? $fobj->filter_result_method : null;
  	$f_result = isset($fobj->filter_result_result) ? $fobj->filter_result_result : null;
  	$f_result = (array)$f_result;
  
  	if (in_array($this->cfg->results['status_code']['all'], $f_result)) 
  	{
  		$f_result = null;
  		return $items2filter; // >>---> Bye!
  	}
  
  	if( ($filter_done = !is_null($f_method) ) )
  	{
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
   * @used-by Assign Test Execution Feature
   *
   * @internal revisions:
   */
  function testPlanTree(&$menuUrl,$context,$objFilters,$objOptions) 
  {
  	$debugMsg = ' - Method: ' . __FUNCTION__;
   	$chronos[] = $tstart = microtime(true);
  

  	$menustring = null;
  	$tplan_tcases = null;
  
  	$tcase_prefix = $this->tproject_mgr->getTestCasePrefix($context->tproject_id) . $this->cfg->glueChar;
  	list($my,$filters,$options,$colorBySelectedBuild) = $this->initExecTree($context,$objFilters,$objOptions);
  	
    $test_spec = $this->tplan_mgr->getSkeleton($context->tplan_id,$context->tproject_id,$my['filters'],$my['options']);

  	$test_spec['name'] = $context->tproject_name . " / " . $context->tplan_name;  // To be discussed
  	$test_spec['id'] = $context->tproject_id;
  	$test_spec['node_type_id'] = $this->cfg->nodeTypeCode['testproject'];
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
  			if( !is_null($sql2do = $this->tplan_mgr->{$objOptions->getTreeMethod}($context->tplan_id,$filters,$options)) )
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
  				
  				$tplan_tcases = $setTestCaseStatus = $this->db->$kmethod($sql2run,'tcase_id');
  			}
  		}   
  
  		
  		if (is_null($tplan_tcases))
  		{
  			$tplan_tcases = array();
  			$apply_other_filters=false;
  		}
  		// if "any" was selected as filtering status, don't filter by status
  		$targetExecStatus = (array)(isset($objFilters->filter_result_result) ? $objFilters->filter_result_result : null);
  		if( !is_null($targetExecStatus) && (!in_array($resultsCfg['status_code']['all'], $targetExecStatus)) ) 
  		{
  			$this->applyStatusFilters($context->tplan_id,$tplan_tcases,$objFilters);
  		}
  
      if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes']))
      {
          $test_spec['childNodes'] = $this->filterByCFValues($test_spec['childNodes'],
                                                             $my['filters']['filter_custom_fields'],
                                                             $my['filters']['filter_active_inactive']);
      }

  		// here we have LOT OF CONFUSION, sometimes we use $my['options'] other $options
  	  $pnFilters = null;		
  		$pnOptions = array('hideTestCases' => $my['options']['hideTestCases'], 'viewType' => 'executionTree');
  		$testcase_counters = $this->prepareExecTreeNode($test_spec,$map_node_tccount,
  		                                  		          $tplan_tcases,$pnFilters,$pnOptions);
  		foreach($testcase_counters as $key => $value)
  		{
  			$test_spec[$key] = $testcase_counters[$key];
  		}
  	
  		$keys = array_keys($tplan_tcases);
  		$menustring = $this->renderExecTreeNode(1,$test_spec,$tplan_tcases,1,$menuUrl,$tcase_prefix,$options);
  		//                                        false,
  		//                                        $useCounters,$useColors,
  		//	                                      $show_testsuite_contents);
  	}  
  		
  	$treeMenu->rootnode = new stdClass();
  	$treeMenu->rootnode->name = $test_spec['text'];
  	$treeMenu->rootnode->id = $test_spec['id'];
  	$treeMenu->rootnode->leaf = $test_spec['leaf'];
  	$treeMenu->rootnode->text = $test_spec['text'];
  	$treeMenu->rootnode->position = $test_spec['position'];	    
  	$treeMenu->rootnode->href = $test_spec['href'];
  	
  	if( !is_null($menustring) )
  	{  
  		// Change key ('childNodes')  to the one required by Ext JS tree.
  		if(isset($test_spec['childNodes'])) 
  		{
  			$menustring = str_ireplace('childNodes', 'children', json_encode($test_spec['childNodes']));
  		}
  		
  		// Remove null elements (Ext JS tree do not like it ).
  		// :null happens on -> "children":null,"text" that must become "children":[],"text"
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
  function renderExecTreeNode($level,&$node,&$tcase_node,$linkto,$testCasePrefix,$opt)
  {
  	static $l18n;	
  	static $pf;	
  	static $doColouringOn;
  	static $cssClasses;
   
  	$menustring = '';
  	$node_type = $this->cfg->nodeCodeType[$node['node_type_id']];
  	if(!$l18n)
  	{ 
  		$doColouringOn['testcase'] = 1;
  		$doColouringOn['counters'] = 1;
  		if( !is_null($opt['useColors']) )
  		{
  			$doColouringOn['testcase'] = $opt['useColors']->testcases;
  			$doColouringOn['counters'] = $opt['useColors']->counters;
  		}
  		foreach($this->cfg->results['status_label'] as $key => $value)
  		{
  			$l18n[$this->cfg->results['status_code'][$key]] = lang_get($value);
  
  			// here we use ONLY key
  			$cssClasses[$this->cfg->results['status_code'][$key]] = 
  			            $doColouringOn['testcase'] ? ('class="light_' . $key . '"') : ''; 
  		}
  		$pf['testproject'] = $opt['hideTestCases'] ? 'TPLAN_PTP' : 'SP';
  		$pf['testsuite'] = $opt['$hideTestCases'] ? 'TPLAN_PTS' : ($opt['showTestSuiteContents'] ? 'STS' : null); 
  		
  	}
  	$name = tlTreeMenu::filterString($node['name']);
  
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
  			if($opt['useCounters'])
  			{
  				$node['text'] .= tlTreeMenu::createCountersInfo($node,$doColouringOn['counters']);
  			}
  		break;
  			
  		case 'testcase':
  			$node['leaf'] = true;
  			$pfn = $opt['tc_action_enabled'] ? 'ST' :null;
  			$versionID = $node['tcversion_id'];
  
  			$status_code = $tcase_node[$node['id']]['exec_status'];
  			$node['text'] = "<span {$cssClasses[$status_code]} " . '  title="' .  $l18n[$status_code] . 
  					 		'" alt="' . $l18n[$status_code] . '">';
  			
  			if($this->cfg->showTestCaseID)
  			{
  				$node['text'] .= "<b>" . htmlspecialchars($testCasePrefix . $node['external_id']) . "</b>:";
  			} 
  			$node['text'] .= "{$name}</span>";
  		break;
  
  		default:
  			$pfn = "ST";
  		break;
  	}
  	
  	$node['position'] = isset($node['node_order']) ? $node['node_order'] : 0;
  	$node['href'] = is_null($pfn)? '' : "javascript:{$pfn}({$node['id']},{$versionID})";
  
  	
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
  			$menustring .= $this->renderExecTreeNode($level+1,$node['childNodes'][$idx],$tcase_node,
  			                                         $linkto,$testCasePrefix,$opt);
  		}
  	}
  	return $menustring;
  }
}   
?>