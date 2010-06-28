<?php
/**
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
*
* 	@version 	$Id: listTestCases.php,v 1.57 2010/06/28 16:19:37 asimon83 Exp $
* 	@author 	Martin Havlat
*
* 	Generates tree menu with test specification.
*   It builds the javascript tree that allows the user to choose testsuite or testcase.
*
*	@internal revision
*
*   20100628 - asimon - removal of constants from filter control class
*   20100624 - asimon - CVS merge (experimental branch to HEAD)
*   20100622 - asimon - huge refactorization for new tlTestCaseFilterControl class
*   20100517 - asimon - BUGID 3301 and related - huge refactoring for first implementation
*                       of filter panel class hierarchy to simplify/standardize
*                       filter panel handling for test cases and requirements
*   20100428 - asimon - BUGID 3301 and related issues - changed name or case
*                       of some variables used in new common template,
*                       added custom field filtering logic
*	20091210 - franciscom - test case execution type filter
*   20090308 - franciscom - added option Any in keywords filter
*   20090210 - BUGID 2062 - franciscom -
*/
require_once('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();

// new class for filter controlling/handling
$control = new tlTestCaseFilterControl($db, 'edit_mode');

$gui = initializeGui($db, $control);
$control->build_tree_menu($gui);

$smarty = new TLSmarty();

$smarty->assign('gui', $gui);
$smarty->assign('control', $control);
$smarty->assign('args', $control->get_argument_string());
$smarty->assign('menuUrl', $gui->menuUrl);

$smarty->display($templateCfg->template_dir . 'tcTree.tpl');


/**
 * Initialize object with information for graphical user interface.
 * 
 * @param tlTestCaseFilterControl $control
 * @return stdClass $gui
 */
function initializeGui(&$dbHandler, &$control) {
	$gui = new stdClass();
	$gui->feature = $control->args->feature;
	$gui->treeHeader = lang_get('title_navigator'). ' - ' . lang_get('title_test_spec');
	
	$feature_path = array('edit_tc' => "lib/testcases/archiveData.php",
	                      'keywordsAssign' => "lib/keywords/keywordsAssign.php",
	                      'assignReqs' => "lib/requirements/reqTcAssign.php");

	$gui->tree_drag_and_drop_enabled = array('edit_tc' => (has_rights($dbHandler, "mgt_modify_tc") == 'yes'),
	                                         'keywordsAssign' => false,
	                                         'assignReqs' => false);

	$gui->menuUrl = $feature_path[$gui->feature];
	return $gui;
}


// old file content

//require_once('../../config.inc.php');
//require_once("common.php");
//require_once("treeMenu.inc.php");
//testlinkInitPage($db);
//
//$templateCfg = templateConfiguration();
//$tproject_mgr = new testproject($db);
//
//$spec_cfg = config_get('spec_cfg');
//
//$feature_action = array('edit_tc' => "lib/testcases/archiveData.php",
//                        'keywordsAssign' => "lib/keywords/keywordsAssign.php",
//                        'assignReqs' => "lib/requirements/reqTcAssign.php");
//
//$treeDragDropEnabled =  array('edit_tc' => (has_rights($db,"mgt_modify_tc") == 'yes'),
//                              'keywordsAssign' => false,
//                              'assignReqs' => false);
//
//$args = init_args($spec_cfg);
//
//// BUGID 3301
//$exec_cfield_mgr = new exec_cfield_mgr($db,$args->tproject_id);
//
//if(isset($feature_action[$args->feature]))
//{
//	$workPath = $feature_action[$args->feature];
//}
//else
//{
//	tLog("Wrong get argument 'feature'.", 'ERROR');
//	exit();
//}
//
//// Here lazy loading tree configuration is done
//$gui = initializeGui($db,$args,$tproject_mgr,$treeDragDropEnabled[$args->feature], $exec_cfield_mgr);
//
//// seems useless - $draw_filter = $spec_cfg->show_tsuite_filter;
//$exclude_branches = null;
//if($spec_cfg->show_tsuite_filter)
//{
//	$mappy = tsuite_filter_mgmt($db,$tproject_mgr,$args->tproject_id,$args->panelFiltersTestSuite);
//	$exclude_branches = $mappy['exclude_branches'];
//	$gui->controlPanel->filters['testSuites']['items'] = $mappy['html_options'];
//	// seems useless - $draw_filter = $mappy['draw_filter'];
//}
//
//$filters = array();
//$filters['keywords'] = buildKeywordsFilter($args->keyword_id,$gui);
//$filters['executionType'] = buildExecTypeFilter($args->panelFiltersExecType,$gui);
//
//// BUGID 3301
//$filters['cf_hash'] = $exec_cfield_mgr->get_set_values();
//
//$applyFilter = !is_null($filters['keywords']) || !is_null($filters['executionType']) 
//            || !is_null($filters['cf_hash']);
//
//if($applyFilter)
//{
//	// Bye, Bye Lazy tree:
//	// we need to use statically generated because user have choosen to apply a filter
//	//
//
//	$options = array('forPrinting' => NOT_FOR_PRINTING, 'hideTestCases' => SHOW_TESTCASES,
//	                 'getArguments' => NO_ADDITIONAL_ARGS, 
//	                 'tc_action_enabled' => DO_ON_TESTCASE_CLICK,
//	                 'ignore_inactive_testcases' => DO_NOT_FILTER_INACTIVE_TESTCASES, 
//	                 'exclude_branches' => $exclude_branches);
//
//    $treeMenu = generateTestSpecTree($db,$args->tproject_id, $args->tproject_name,
//                                     $workPath,$filters,$options);
//
//	$gui->ajaxTree->loader = '';
//	$gui->ajaxTree->root_node = $treeMenu->rootnode;
//	$gui->ajaxTree->children = $treeMenu->menustring ? $treeMenu->menustring : "''";
//	$gui->ajaxTree->cookiePrefix = $args->feature;
//	
//	if($applyFilter)
//	{
//		$gui->ajaxTree->loader = '';  
//	}	
//}
//
//$gui->treeHeader = lang_get('title_navigator'). ' - ' . lang_get('title_test_spec');
//// seems useless - $gui->draw_filter = $draw_filter;
//// $gui->tsuitesCombo = $tsuites_combo;
//$gui->tcSpecRefreshOnAction = $args->do_refresh;
//
//$smarty = new TLSmarty();
//$smarty->assign('gui',$gui);
//$smarty->assign('menuUrl',$workPath);
//$smarty->display($templateCfg->template_dir . 'tcTree.tpl');
//
///*
//  function: tsuite_filter_mgmt
//
//  args :
//  
//  returns: map keys  draw_filter -> 1 / 0
//                     map for smarty html_options
//
//*/
//function tsuite_filter_mgmt(&$db,&$tprojectMgr,$tproject_id,$tsuites_to_show)
//{
//	$ret = array('draw_filter' => 0,'html_options' => array(0 =>''),
//                 'exclude_branches' => null);
//             
//	$fl_tsuites = $tprojectMgr->get_first_level_test_suites($tproject_id,'smarty_html_options');
//	if($tsuites_to_show > 0)
//	{
//		foreach($fl_tsuites as $tsuite_id => $name)
//     	{
//			if($tsuite_id != $tsuites_to_show)
//			{
//        		$ret['exclude_branches'][$tsuite_id] = 'exclude_me';
//        	}	
//     	}  
//  	} 
//  
//	$ret['draw_filter'] = (!is_null($fl_tsuites) && count($fl_tsuites) > 0) ? 1 : 0;
//	$tsuites_combo = array(0 =>'');
//  	if($ret['draw_filter'])
//  	{
//    	// add blank option as first choice
//    	$ret['html_options'] += $fl_tsuites;
//  	}
//  	return $ret;
//}
//
///**
// * 
// *
// */
//function init_args($spec_cfg)
//{
//	$iParams = array("feature" => array(tlInputParameter::STRING_N,0,50),
//			         "keyword_id" => array(tlInputParameter::ARRAY_INT),
//			         "keywordsFilterType" => array(tlInputParameter::STRING_N,0,5),
//			         "panelFiltersTestSuite" => array(tlInputParameter::INT_N),
//					 "tcspec_refresh_on_action" => array(tlInputParameter::INT_N),
//					 "hidden_tcspec_refresh_on_action" => array(tlInputParameter::INT_N),
//					 "panelFiltersExecType" => array(tlInputParameter::INT_N));
//					 
//	$args = new stdClass();
//    R_PARAMS($iParams,$args);
//    
//    if (!$args->keywordsFilterType)
//    {
//    	$args->keywordsFilterType = "OR";
//    }
//    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
//    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
//    $args->basehref = $_SESSION['basehref'];
//    $args->do_refresh = "no";
//   
//    if (!is_null($args->hidden_tcspec_refresh_on_action))
//    {
//    	if (!is_null($args->tcspec_refresh_on_action))
//    	{
//    		$args->do_refresh = $args->tcspec_refresh_on_action ? "yes" : "no";
//        }
//    }
//    else if (isset($_SESSION["tcspec_refresh_on_action"]))
//    {
//    	$args->do_refresh = ($_SESSION["tcspec_refresh_on_action"] == "yes") ? "yes" : "no";
//    }
//    else
//    {	
//    	$args->do_refresh = ($spec_cfg->automatic_tree_refresh > 0) ? "yes": "no";
//    }
//	$_SESSION['tcspec_refresh_on_action'] = $args->do_refresh;
//    	
//	return $args;  
//}
//
///*
//  function: initializeGui
//            initialize gui (stdClass) object that will be used as argument
//            in call to Template Engine.
// 
//  args: argsObj: object containing User Input and some session values
//        basehref: URL to web home of your testlink installation.
//        tprojectMgr: test project manager object.
//        treeDragDropEnabled: true/false. Controls Tree drag and drop behaivor.
//        
//  
//  
//  returns: stdClass object
//  
//  rev: 20100428 - asimon - BUGID 3301 - added $exec_cfield_mgr for custom field filtering
//       20080817 - franciscom
//       added code to get total number of testcases in a test project, to display
//       it on root tree node.
//
//*/
//function initializeGui($dbHandler,$args,&$tprojectMgr,$treeDragDropEnabled, $exec_cfield_mgr)
//{
//    $tcaseCfg = config_get('testcase_cfg');
//    $tcasePrefix = $tprojectMgr->getTestCasePrefix($args->tproject_id);
//
//    $gui = new stdClass();
//    $initValues = array();
//    $initValues['keywords'] = "testproject,{$args->tproject_id}"; 
//    $initValues['execTypes'] = 'init'; 
//    $gui->controlPanel = new tlControlPanel($dbHandler,$args,$initValues);
//
//    
//    $gui->tree = null;
//    $gui->ajaxTree = new stdClass();
//    $gui->ajaxTree->loader = $args->basehref . 'lib/ajax/gettprojectnodes.php?' .
//                             "root_node={$args->tproject_id}&" .
//                             "tcprefix=" . urlencode($tcasePrefix. $tcaseCfg->glue_character) . "&" .
//                             "filter_node={$args->panelFiltersTestSuite}";
//
//    $gui->ajaxTree->root_node = new stdClass();
//    $gui->ajaxTree->root_node->href = "javascript:EP({$args->tproject_id})";
//    $gui->ajaxTree->root_node->id = $args->tproject_id;
//    
//  	$tcase_qty = $tprojectMgr->count_testcases($args->tproject_id);
//    $gui->ajaxTree->root_node->name = $args->tproject_name . " ($tcase_qty)";
//  
//    $gui->ajaxTree->dragDrop = new stdClass();
//    $gui->ajaxTree->dragDrop->enabled = $treeDragDropEnabled;
//    $gui->ajaxTree->dragDrop->BackEndUrl = $args->basehref . 'lib/ajax/dragdroptprojectnodes.php';
//    
//    // TRUE -> beforemovenode() event will use our custom implementation 
//    $gui->ajaxTree->dragDrop->useBeforeMoveNode = false;
//  
//  
//    // Prefix for cookie used to save tree state
//    $gui->ajaxTree->cookiePrefix='tproject_' . $gui->ajaxTree->root_node->id . "_" ;
//  
//    // 20080831 - franciscom - Custom attribute
//    // You can access to it's value using public property 'attributes' of object of Class Ext.tree.TreeNode 
//    // example: mynode.attributes.testlink_node_type
//    //
//    // Important: 
//    // Fore root node (this node)
//    // You need to initialize every custom property you want to add to root node
//    // on the js file that create it (treebyloader.js) and smarty template
//    // 
//    //
//    // Also this property must be managed in php code used to generate JSON code.
//    //
//    // I'appologize for using MAGIC constant
//    $gui->ajaxTree->root_node->testlink_node_type='testproject';
//
//    
//    // 20090118 - franciscom    
//    // $gui->keywordsFilterTypes = new stdClass();
//    // $gui->keywordsFilterTypes->options = array('OR' => 'Or' , 'AND' =>'And'); 
//    // $gui->keywordsFilterTypes->selected = $args->keywordsFilterType;
//
//
//    // $gui->keywordsFilterItemQty = 0;
//    // $gui->keywordID = $args->keyword_id; 
//    // $gui->keywordsMap = $tprojectMgr->get_keywords_map($args->tproject_id); 
//    // if(!is_null($gui->keywordsMap))
//    // {
//    //     $gui->keywordsMap = array(0 => $gui->strOptionAny) + $gui->keywordsMap;
//    // 	$gui->keywordsFilterItemQty = min(count($gui->keywordsMap),3);
//    // }
//    
//    // BUGID 3301
//    $gui->design_time_cfields = $exec_cfield_mgr->html_table_of_custom_field_inputs(30);
//    $gui->feature = $args->feature;
//    
//    return $gui;  
//}
?>