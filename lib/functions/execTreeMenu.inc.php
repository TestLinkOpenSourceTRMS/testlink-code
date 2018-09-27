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
 * @filesource  execTreeMenu.inc.php
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2013,2017 TestLink community 
 * @link        http://testlink.sourceforge.net/ 
 * @uses        config.inc.php
 * @uses        const.inc.php
 *
 */

/**
 * @param $dbHandler
 * @param $menuUrl
 * @param array $context => keys tproject_id,tproject_name,tplan_id,tplan_name
 * @param $objFilters
 * @param $objOptions
 * @return array
 */

// $tproject_id,$tproject_name,$tplan_id,                  $tplan_name,

function execTree(&$dbHandler,&$menuUrl,$context,$objFilters,$objOptions) 
{
  $chronos[] = microtime(true);

  $treeMenu = new stdClass(); 
  $treeMenu->rootnode = null;
  $treeMenu->menustring = '';
  $resultsCfg = config_get('results');
  $glueChar=config_get('testcase_cfg')->glue_character;
  
  $menustring = null;
  $tplan_tcases = null;
  $tck_map = null;
  $idx=0;
  $testCaseQty=0;
  $testCaseSet=null;

  // Seems to be useless 
  /*
  $keyword_id = 0;
  $keywordsFilterType = 'Or';
  if (property_exists($objFilters, 'filter_keywords') && !is_null($objFilters->filter_keywords)) 
  {
    $keyword_id = $objFilters->filter_keywords;
    $keywordsFilterType = $objFilters->filter_keywords_filter_type;
  }
  */
  // ---

  $renderTreeNodeOpt = array();
  $renderTreeNodeOpt['showTestCaseID'] = config_get('treemenu_show_testcase_id');
  list($filters,$options,
       $renderTreeNodeOpt['showTestSuiteContents'],
       $renderTreeNodeOpt['useCounters'],
       $renderTreeNodeOpt['useColors'],$colorBySelectedBuild) = initExecTree($objFilters,$objOptions);

  $renderTreeNodeOpt['showTestCaseExecStatus'] = $options['showTestCaseExecStatus'];

  if( property_exists($objOptions, 'actionJS')) {
    if(isset($objOptions->actionJS['testproject'])) {
      $renderTreeNodeOpt['actionJS']['testproject'] = $objOptions->actionJS['testproject'];
    }  
  }  

  $tplan_mgr = new testplan($dbHandler);
  $tproject_mgr = new testproject($dbHandler);
  $tcase_node_type = $tplan_mgr->tree_manager->node_descr_id['testcase'];

  $hash_descr_id = $tplan_mgr->tree_manager->get_available_node_types();
  $hash_id_descr = array_flip($hash_descr_id);      
  
  $tcase_prefix = $tproject_mgr->getTestCasePrefix($context['tproject_id']) . $glueChar;
  
  // remove test spec, test suites (or branches) that have ZERO test cases linked to test plan
  // 
  // IMPORTANT:
  // using 'order_cfg' => array("type" =>'exec_order',"tplan_id" => $tplan_id))
  // makes the magic of ignoring test cases not linked to test plan.
  // This unexpected bonus can be useful on export test plan as XML.
  //
  $my['options']=array('recursive' => true, 
                       'remove_empty_nodes_of_type' => $tplan_mgr->tree_manager->node_descr_id['testsuite'],
                       'order_cfg' => array("type" =>'exec_order',"tplan_id" => $context['tplan_id']));

  $my['filters'] = array('exclude_node_types' => 
                    array('testplan' => 'exclude_me','requirement_spec'=> 'exclude_me',
                                                       'requirement'=> 'exclude_me'),
                         'exclude_children_of' => 
                           array('testcase' => 'exclude_my_children',
                                 'requirement_spec'=> 'exclude_my_children') );

  // added for filtering by toplevel testsuite
  if (isset($objFilters->filter_toplevel_testsuite) && is_array($objFilters->filter_toplevel_testsuite))  {
    $my['filters']['exclude_branches'] = $objFilters->filter_toplevel_testsuite;
  }

  if (isset($objFilters->filter_custom_fields) && is_array($objFilters->filter_custom_fields)) {
    $my['filters']['filter_custom_fields'] = $objFilters->filter_custom_fields;
  }
    
   
  // Document why this is needed, please  
  $spec = $tplan_mgr->getSkeleton($context['tplan_id'],
            $context['tproject_id'],$my['filters'],$my['options']);

  $test_spec = $spec[0];
  
  // To be discussed
  $test_spec['name'] = $context['tproject_name'] . " / " . $context['tplan_name'];  
  
  $test_spec['id'] = $context['tproject_id'];
  $test_spec['node_type_id'] = $hash_descr_id['testproject'];
  $test_spec['node_type'] = 'testproject';
  $map_node_tccount = array();
  
  $tplan_tcases = null;
  $linkedTestCasesSet = null;

  if($test_spec) {
    // 20120519 TO BE CHECKED
    if(is_null($filters['tcase_id']) || $filters['tcase_id'] > 0) {
      // Step 1 - get item set with exec status.
      // This has to scopes:
      // 1. tree coloring according exec status on (Test plan, platform, build ) context
      // 2. produce sql that can be used to reduce item set on combination with filters
      //    that can not be used on this step like:
      //    a. test cases belonging to branch with root TEST SUITE
      //    b. keyword filter on AND MODE
      //    c. execution results on other builds, any build etc
      //
      // WE NEED TO ADD FILTERING on CUSTOM FIELD VALUES, WE HAVE NOT REFACTORED
      // THIS YET.
      //
      if( !is_null($sql2do = $tplan_mgr->getLinkedForExecTree($context['tplan_id'],$filters,$options)) ) {
        $kmethod = "fetchRowsIntoMap";
        if( is_array($sql2do) ) {       
          if( $filters['keyword_filter_type'] == 'And' ) { 
            $kmethod = "fetchRowsIntoMapAddRC";
            $unionClause = " UNION ALL ";
          }
          else {
            $kmethod = "fetchRowsIntoMap";
            $unionClause = ' UNION ';
          }
          $sql2run = $sql2do['exec'] . $unionClause . $sql2do['not_run'];
        }
        else {
          $sql2run = $sql2do;
        }
        $tplan_tcases = $dbHandler->$kmethod($sql2run,'tcase_id');
      }
    }   

    if( $filters['keyword_filter_type'] == 'And' && !is_null($tplan_tcases)) {
      $kwc = count($filters['keyword_id']);
      $ak = array_keys($tplan_tcases);
      $mx = null;
      foreach($ak as $tk) {
        if($tplan_tcases[$tk]['recordcount'] == $kwc) {
          $mx[$tk] = $tplan_tcases[$tk];
        } 
      } 
      $tplan_tcases = null;
      $tplan_tcases = $mx;
    } 
    $setTestCaseStatus = $tplan_tcases;


    if( !is_null($tplan_tcases) ) {
      // OK, now we need to work on status filters
      // if "any" was selected as filtering status, don't filter by status
      $targetExecStatus = (array)(isset($objFilters->filter_result_result) ? 
        $objFilters->filter_result_result : null);
      
      if( !is_null($targetExecStatus) && (!in_array($resultsCfg['status_code']['all'], $targetExecStatus)) ) {
        applyStatusFilters($context['tplan_id'],$tplan_tcases,$objFilters,$tplan_mgr,$resultsCfg['status_code']);       
      }

      if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes'])) {
        // need to separate cf 4 design that cf 4 testplan_design.
        // Here we ONLY use cf 4 design
        $cfx = cfForDesign($dbHandler,$my['filters']['filter_custom_fields']);
        if( !is_null($cfx) ) {
          $test_spec['childNodes'] = filter_by_cf_values($dbHandler,$test_spec['childNodes'],$cfx,$hash_descr_id);
        }  
      }

      // ATTENTION: sometimes we use $my['options'], other $options
      $pnOptions = array('hideTestCases' => $options['hideTestCases'], 'viewType' => 'executionTree');
      $pnFilters = null;    
      $testcase_counters = prepareExecTreeNode($dbHandler,$test_spec,
                             $map_node_tccount,$tplan_tcases,$pnFilters,$pnOptions);

      foreach($testcase_counters as $key => $value) {
        $test_spec[$key] = $testcase_counters[$key];
      }
    }
    else {
      $tplan_tcases = array();
      unset($test_spec['childNodes']);

      $testcase_counters = helperInitCounters();
      foreach($testcase_counters as $key => $value) {
        $test_spec[$key] = $testcase_counters[$key];
      }
    }  

    $renderTreeNodeOpt['hideTestCases'] = $options['hideTestCases'];
    $renderTreeNodeOpt['tc_action_enabled'] = 1;

    // CRITIC: renderExecTreeNode() WILL MODIFY $tplan_tcases, can empty it completely
    // here filter has been applied
    $lt = array_keys((array)$tplan_tcases);

    // here test cases are in the right order
    $linkedTestCasesSet = null;
    if( isset($spec[1]['nindex']) ) {
      $ltcs = $spec[1]['nindex'];

      // now need to filter out
      $tl = array_flip($lt);
      foreach($ltcs as &$ele) {
        if( isset($tl[$ele]) ) {
          $linkedTestCasesSet[] = $ele;
        }  
      }  
    }  

    renderExecTreeNode(1,$test_spec,$tplan_tcases,$hash_id_descr,$menuUrl,
                       $tcase_prefix,$renderTreeNodeOpt);
  }
  
  $treeMenu->rootnode=new stdClass();
  $treeMenu->rootnode->name=$test_spec['text'];
  $treeMenu->rootnode->id=$test_spec['id'];
  $treeMenu->rootnode->leaf=$test_spec['leaf'];
  $treeMenu->rootnode->text=$test_spec['text'];
  $treeMenu->rootnode->position=$test_spec['position'];     
  $treeMenu->rootnode->href=$test_spec['href'];
  
  // Change key ('childNodes')  to the one required by Ext JS tree.
  $menustring = '';
  if(isset($test_spec['childNodes'])) {
    $menustring = str_ireplace('childNodes', 'children', json_encode($test_spec['childNodes']));
  }
   
  // Remove null elements (Ext JS tree do not like it ).
  // :null happens on -> "children":null,"text" that must become "children":[],"text"
  // $menustring = str_ireplace(array(':null',',null','null,'),array(':[]','',''), $menustring); 
  // $menustring = str_ireplace(array(':null',',null','null,','null'),array(':[]','','',''), $menustring); 
  //   
  // 20140928 - order of replace is CRITIC
  $target = array(',"' . REMOVEME .'"','"' . REMOVEME . '",');
  $menustring = str_ireplace($target,array('',''), $menustring); 

  $target = array(':' . REMOVEME,'"' . REMOVEME . '"');
  $menustring = str_ireplace($target,array(':[]',''), $menustring); 

  $treeMenu->menustring = $menustring;

  return array($treeMenu, $linkedTestCasesSet);
}


/*
 *
 *
 */
function initExecTree($filtersObj,$optionsObj) {
  $filters = array();
  $options = array();
  
  $buildSettingsPanel = null;
  $buildFiltersPanel = isset($filtersObj->filter_result_build) ? $filtersObj->filter_result_build : null;
  $build2filter_assignments = is_null($buildFiltersPanel) ? $buildSettingsPanel : $buildFiltersPanel;

  $keymap = array('tcase_id' => 'filter_tc_id', 'assigned_to' => 'filter_assigned_user',
                  'platform_id' => 'setting_platform', 'exec_type' => 'filter_execution_type',
                  'urgencyImportance' => 'filter_priority', 'tcase_name' => 'filter_testcase_name',
                  'cf_hash' => 'filter_custom_fields', 'build_id' => array('setting_build','build_id'),
                  'bug_id' => 'filter_bugs');
  
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
  if ( !is_null($filtersObj) && property_exists($filtersObj, 'filter_keywords') && !is_null($filtersObj->filter_keywords)) 
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

  $options['tc_action_enabled'] = isset($optionsObj->tc_action_enabled) ?  $optionsObj->tc_action_enabled : true;
  $options['showTestCaseExecStatus'] = isset($optionsObj->showTestCaseExecStatus) ?  $optionsObj->showTestCaseExecStatus : true;

  return array($filters,$options,$show_testsuite_contents,$useCounters,$useColors,$colorBySelectedBuild);
}



/**
 *
 * @returns test_counters map. key exec_status
 * 
 * @used_by
 * printDocOptions.php
 * planTCNavigator.php
 *
 */
function prepareExecTreeNode(&$db,&$node,&$map_node_tccount,&$tplan_tcases = null,
                             $filters=null, $options=null)
{
  
  static $status_descr_list;
  static $debugMsg;
  static $my;
  static $resultsCfg;

  //debug_print_backtrace();
  //echo __FUNCTION__; die();
  $tpNode = null;
  if (!$debugMsg) {
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

  // Important Development Notes
  // It can seems that analisys of node type can be done in
  // any order, but IS NOT TRUE.
  // This is because structure of $node element.
  // Then BE VERY Carefull if you plan to refactor, to avoid unexpected
  // side effects.
  // 
  if($node_type == 'testcase')
  {

    $tpNode = isset($tplan_tcases[$node['id']]) ? $tplan_tcases[$node['id']] : null;
    $tcase_counters = array_fill_keys($status_descr_list, 0);

    if( is_null($tpNode) )
    {     
      // Dev Notes: when this happens ?
      // 1. two or more platforms on test plan (PLAT-A,PLAT-B)
      // 2. TC-1X => on PLAT-A
      //    TC-1Y => on PLAT-B
      // 3. Build Exec Tree on PLAT-A
      // 4. TC-1Y will match condition
      //
      // 5. Build Exec Tree on PLAT-B
      // 6. TC-1X will match condition
      //
      // What if Test plan has NO PLATFORMS ?
      // This piece of code will not be executed
      //
      unset($tplan_tcases[$node['id']]);
      // $node = null;
      $node = REMOVEME;
    } 
    else 
    {

      if( isset($tpNode['exec_status']) )
      {
        if( isset($resultsCfg['code_status'][$tpNode['exec_status']]) )
        {
          $tc_status_descr = $resultsCfg['code_status'][$tpNode['exec_status']];   
        }  
        else
        {
          throw new Exception("Config Issue - exec status code: {$tpNode['exec_status']}", 1);
        }  
      }
      else
      {
        $tc_status_descr = "not_run";
      }
      $tcase_counters[$tc_status_descr] = $tcase_counters['testcase_count'] = ($node ? 1 : 0);

      if ( $my['options']['hideTestCases'] )
      {
        // $node = null;
        $node = REMOVEME;
      }
      else
      {
        $node['tcversion_id'] = $tpNode['tcversion_id'];    
        $node['version'] = $tpNode['version'];    
        $node['external_id'] = $tpNode['external_id'];    

        unset($node['childNodes']);
        $node['leaf']=true;
      }  

    }
  } 
  else 
  {
    if (isset($node['childNodes']) && is_array($node['childNodes']))
    {
      // node is a Test Suite or Test Project
      $childNodes = &$node['childNodes'];
      $childNodesQty = count($childNodes);
      for($idx = 0;$idx < $childNodesQty ;$idx++)
      {
        $current = &$childNodes[$idx];
        // I use set an element to null to filter out leaf menu items
        if(is_null($current))
        {
          $childNodes[$idx] = REMOVEME;  // 19
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
      
      // hhhm is this test needed ? Why ?
      if (isset($node['id']))
      {
        $map_node_tccount[$node['id']] = array( 'testcount' => $node['testcase_count'],
                                                'name' => $node['name']);
      }

      // need to check is this check can be TRUE on some situation
      // After mail on 20140124, it seems is useless.
      // This piece is useful only when you use platforms.
      // Use Case
      // Test plan with 2 platforms - QQ, WW
      // TC-1A -> platform QQ
      // NO TEST CASE assigned to test plan with platform WW
      // User wants to see execution tree with platform WW
      // You are going to enter here because $tplan_tcases is NULL
      // 
      if( !is_null($tplan_tcases) && !$tcase_counters['testcase_count'] && ($node_type != 'testproject'))
      {
        // echo 'nullfying-';
        // $node = null;
        $node = REMOVEME;
      }
    }
    else if ($node_type == 'testsuite')
    {
      // Empty test suite
      $map_node_tccount[$node['id']] = array( 'testcount' => 0,'name' => $node['name']);
      
      // If is an EMPTY Test suite and we have added filtering conditions, We will destroy it.
      if ($filtersApplied || !is_null($tplan_tcases) )
      {
        // $node = null;
        $node = REMOVEME;
      } 
    }
  }  

  return $tcase_counters;
}


/**
 *
 */
function applyStatusFilters($tplan_id,&$items2filter,&$fobj,&$tplan_mgr,$statusCfg)
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

  // die();
  
  // if "any" was selected as filtering status, don't filter by status
  if (in_array($statusCfg['all'], $f_result)) 
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
 * Provides Test suites and test cases
 * @used-by Assign Test Execution Feature
 *
 * @internal revisions
 */
function testPlanTree(&$dbHandler,&$menuUrl,$tproject_id,$tproject_name,$tplan_id,
                      $tplan_name,$objFilters,$objOptions) 
{
  $debugMsg = ' - Method: ' . __FUNCTION__;
  $chronos[] = $tstart = microtime(true);

  $treeMenu = new stdClass(); 
  $treeMenu->rootnode = null;
  $treeMenu->menustring = '';
  

  $resultsCfg = config_get('results');
  $glueChar=config_get('testcase_cfg')->glue_character;
  $menustring = null;
  $tplan_tcases = null;

  $renderTreeNodeOpt = null;
  $renderTreeNodeOpt['showTestCaseID'] = config_get('treemenu_show_testcase_id');

  list($filters,$options,
       $renderTreeNodeOpt['showTestSuiteContents'],
       $renderTreeNodeOpt['useCounters'],
       $renderTreeNodeOpt['useColors'],$colorBySelectedBuild) = initExecTree($objFilters,$objOptions);

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
                       'hideTestCases' => $options['hideTestCases'],'tc_action_enabled' => $options['tc_action_enabled'],
                       'showTestCaseExecStatus' => $options['showTestCaseExecStatus']);
                         
  $my['filters'] = array('exclude_node_types' => $nt2exclude,
                         'exclude_children_of' => $nt2exclude_children);
  
  if (isset($objFilters->filter_toplevel_testsuite) && is_array($objFilters->filter_toplevel_testsuite)) 
  {
    $my['filters']['exclude_branches'] = $objFilters->filter_toplevel_testsuite;
  }

  if (isset($objFilters->filter_custom_fields) && is_array($objFilters->filter_custom_fields))
  {
    $my['filters']['filter_custom_fields'] = $objFilters->filter_custom_fields;
  }

  if( property_exists($objOptions, 'actionJS') )
  {
    foreach(array('testproject','testsuite','testcase') as $nk)
    {  
      if(isset($objOptions->actionJS[$nk]))
      {
        $renderTreeNodeOpt['actionJS'][$nk] = $objOptions->actionJS[$nk];
      }
    }  
  }  

  if( property_exists($objOptions, 'nodeHelpText') )
  {
    foreach(array('testproject','testsuite','testcase') as $nk)
    {  
      if(isset($objOptions->nodeHelpText[$nk]))
      {
        $renderTreeNodeOpt['nodeHelpText'][$nk] = $objOptions->nodeHelpText[$nk];
      }
    }  
  }  

  $spec = $tplan_mgr->getSkeleton($tplan_id,$tproject_id,$my['filters'],$my['options']);

  $test_spec = $spec[0];
  $test_spec['name'] = $tproject_name . " / " . $tplan_name;  // To be discussed
  $test_spec['id'] = $tproject_id;
  $test_spec['node_type_id'] = $hash_descr_id['testproject'];
  $test_spec['node_type'] = 'testproject';
  $map_node_tccount = array();
  
  $tplan_tcases = array();
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
      //    b. keyword filter on AND MODE
      //    c. execution results on other builds, any build etc
      //
      // WE NEED TO ADD FILTERING on CUSTOM FIELD VALUES, WE HAVE NOT REFACTORED
      // THIS YET.
      //
      if( !is_null($sql2do = $tplan_mgr->{$objOptions->getTreeMethod}($tplan_id,$filters,$options)) )
      {
        $doPinBall = false;
        if( is_array($sql2do) )
        {       
          if( ($doPinBall = $filters['keyword_filter_type'] == 'And') )
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

        $tplan_tcases = $dbHandler->$kmethod($sql2run,'tcase_id');
        if($doPinBall && !is_null($tplan_tcases))
        {
          $kwc = count($filters['keyword_id']);
          $ak = array_keys($tplan_tcases);
          $mx = null;
          foreach($ak as $tk)
          {
            if($tplan_tcases[$tk]['recordcount'] == $kwc)
            {
              $mx[$tk] = $tplan_tcases[$tk];
            } 
          } 
          $tplan_tcases = null;
          $tplan_tcases = $mx;
        } 
        $setTestCaseStatus = $tplan_tcases;
      }
    }   

    
    if (is_null($tplan_tcases))
    {
      $tplan_tcases = array();
    }

    // OK, now we need to work on status filters
    // if "any" was selected as filtering status, don't filter by status
    $targetExecStatus = (array)(isset($objFilters->filter_result_result) ? $objFilters->filter_result_result : null);
    if( !is_null($targetExecStatus) && (!in_array($resultsCfg['status_code']['all'], $targetExecStatus)) ) 
    {
      applyStatusFilters($tplan_id,$tplan_tcases,$objFilters,$tplan_mgr,$resultsCfg['status_code']);
    }

    if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes']))
    {
      $test_spec['childNodes'] = filter_by_cf_values($dbHandler, $test_spec['childNodes'],
                                                     $my['filters']['filter_custom_fields'],$hash_descr_id);
    }

   
    // here we have LOT OF CONFUSION, sometimes we use $my['options'] other $options
    $pnFilters = null;    
    $pnOptions = array('hideTestCases' => $my['options']['hideTestCases'], 'viewType' => 'executionTree');
    $testcase_counters = prepareExecTreeNode($dbHandler,$test_spec,$map_node_tccount,
                                             $tplan_tcases,$pnFilters,$pnOptions);

    foreach($testcase_counters as $key => $value)
    {
      $test_spec[$key] = $testcase_counters[$key];
    }
  
    $keys = array_keys($tplan_tcases);
    $renderTreeNodeOpt['hideTestCases'] = $my['options']['hideTestCases'];
    $renderTreeNodeOpt['tc_action_enabled'] = isset($my['options']['tc_action_enabled']) ? 
                                              $my['options']['tc_action_enabled'] : 1;
    $renderTreeNodeOpt['showTestCaseExecStatus'] = $my['options']['showTestCaseExecStatus']; 

    renderExecTreeNode(1,$test_spec,$tplan_tcases,$hash_id_descr,$menuUrl,$tcase_prefix,$renderTreeNodeOpt);
  }  // if($test_spec)
  

  $treeMenu->rootnode=new stdClass();
  $treeMenu->rootnode->name=$test_spec['text'];
  $treeMenu->rootnode->id=$test_spec['id'];
  $treeMenu->rootnode->leaf=$test_spec['leaf'];
  $treeMenu->rootnode->text=$test_spec['text'];
  $treeMenu->rootnode->position=$test_spec['position'];     
  $treeMenu->rootnode->href=$test_spec['href'];


  // Change key ('childNodes')  to the one required by Ext JS tree.
  $menustring = '';
  if(isset($test_spec['childNodes'])) 
  {
    $menustring = str_ireplace('childNodes', 'children', json_encode($test_spec['childNodes']));
  }
    
  // Remove null elements (Ext JS tree do not like it ).
  // :null happens on -> "children":null,"text" that must become "children":[],"text"
  // $menustring = str_ireplace(array(':null',',null','null,'),array(':[]','',''), $menustring); 
  // $menustring = str_ireplace(array(':null',',null','null,','null'),array(':[]','','',''), $menustring); 
  $target = array(',"' . REMOVEME .'"','"' . REMOVEME . '",');
  $menustring = str_ireplace($target,array('',''), $menustring); 

  $target = array(':' . REMOVEME,'"' . REMOVEME . '"');
  $menustring = str_ireplace($target,array(':[]',''), $menustring); 
  
  $treeMenu->menustring = $menustring;
  return array($treeMenu, $keys);
}

/**
 *
 */
function helperInitCounters()
{
  $resultsCfg = config_get('results');
  $items = array_keys($resultsCfg['status_code']);
  $items[] = 'testcase_count';
  $cc = array_fill_keys($items, 0);
  return $cc;
}

/**
 *
 */
function cfForDesign(&$dbHandler,$cfSet)
{
  static $mgr;
  if(!$mgr)
  {
    $mgr = new cfield_mgr($dbHandler);
  }  

  $ret = null;
  foreach($cfSet as $id => $val)
  {
    $xx = $mgr->get_by_id($id);
    if( $xx[$id]['enable_on_design'] )
    {
      $ret[$id] = $val;
    }  
  }  
  return $ret;
}
