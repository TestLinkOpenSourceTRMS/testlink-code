<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2005-2009, TestLink community
 * @version    	CVS: $Id: planAddTCNavigator.php,v 1.61 2010/06/24 17:25:53 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * 	Navigator for feature: add Test Cases to a Test Case Suite in Test Plan.
 *	It builds the javascript tree that allow the user select a required part
 *	Test specification. Keywords should be used for filter.
 *
 * @internal Revisions:
 *
 * 20100622 - asimon - huge refactorization for new tlTestCaseFilterControl class
 * 20100428 - asimon - BUGID 3301 and related issues - changed name or case
 *                     of some variables used in new common template,
 *                     added custom field filtering logic
 * 20100417 - franciscom - BUGID 2498: Add test case to test plan - Filter Test Cases based on Test Importance
 * 20100410 - franciscom - BUGID 2797 - filter by test case execution type
 * 20100228 - franciscom - BUGID 0001927: filter on keyword - Filter tree when add/remove testcases - KO
 * 20090415 - franciscom - BUGID 2384 - Tree doesnt load properly in Add / Remove Test Cases
 * 20090118 - franciscom - added logic to switch (for EXTJS tree type), how tree is builded
 *                         when there are filters
 */

require('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");

testlinkInitPage($db);

$templateCfg = templateConfiguration();

$control = new tlTestCaseFilterControl($db, tlTestCaseFilterControl::PLAN_ADD_MODE);
$gui = initializeGui($control);
$control->build_tree_menu($gui);

$smarty = new TLSmarty();

$smarty->assign('gui', $gui);
$smarty->assign('control', $control);
$smarty->assign('args', $gui->args);
$smarty->assign('menuUrl', $gui->menuUrl);

$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * Initialize gui object for use in templates.
 * @param tlTestCaseFilterControl $control
 * @return object $gui
 */
function initializeGui($control) {
	$gui = new stdClass();
	$gui->menuUrl = 'lib/plan/planAddTC.php';
	$gui->args = $control->get_argument_string();
	$gui->additional_string = '';
	$gui->src_workframe = $control->args->basehref . $gui->menuUrl .
	                "?edit=testproject&id={$control->args->testproject_id}" . $gui->args;
	
	return $gui;
}


// old file content

//require('../../config.inc.php');
//require_once("common.php");
//require_once("treeMenu.inc.php");
//testlinkInitPage($db);
//
//$templateCfg = templateConfiguration();
//$args = init_args();
//// BUGID 3301 - added exec_cfield_mgr here
//$exec_cfield_mgr = new exec_cfield_mgr($db,$args->tproject_id);
//$gui = initializeGui($db,$args, $exec_cfield_mgr);
//$gui->ajaxTree = initAjaxTree($args,$_SESSION['basehref']);
//$gui->tree = buildTree($db,$gui,$args, $exec_cfield_mgr);
//
//$smarty = new TLSmarty();
//$smarty->assign('gui', $gui);
//
//// IMPORTANT: A javascript variable 'args' will be initialized with this value
//// using inc_head.tpl template.
//$smarty->assign('args', $gui->args);
//$smarty->assign('additionalArgs',$gui->additionalArgs);
//
//$smarty->assign('menuUrl', $gui->menuUrl);
//$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
//
//
///*
//  function: init_args()
//  			get input data 
//  args: -
//
//  returns: object expected parameters
//
//*/
//function init_args()
//{
//    $args = new stdClass();
//    $_REQUEST = strings_stripSlashes($_REQUEST);
//
//    // Is an array because is a multiselect 
//    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
//    
//    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID'];
//    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
//    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
//    $args->user_id = $_SESSION['userID'];
//    
//    $args->doUpdateTree = isset($_REQUEST['doUpdateTree']) ? 1 : 0;
//
//    $args->called_by_me = isset($_REQUEST['called_by_me']) ? 1 : 0;
//    $args->called_url = isset($_REQUEST['called_url']) ? $_REQUEST['called_url'] : null;
// 
//    $args->keywordsFilterType =isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';
//
//    $args->exec_type = isset($_REQUEST['exec_type']) ? intval($_REQUEST['exec_type']) : 0;
//    
//    // BUGID 2498
//    $args->importance = isset($_REQUEST['importance']) ? intval($_REQUEST['importance']) : 0;
//
//    return $args;
//}
//
//
///*
//  function: initializeGui
//  args :
//  returns: 
//
//  rev:20100428 - asimon - BUGID 3301, added exec_cfield_mgr
//      20080629 - franciscom - added missed argument basehref
//      20080622 - franciscom - changes for ext js tree
//      20080429 - franciscom
//*/
//function initializeGui(&$dbHandler,&$argsObj, &$exec_cfield_mgr)
//{
//    $gui = new stdClass();
//    $tprojectMgr = new testproject($dbHandler);
//    $tcaseCfg = config_get('testcase_cfg'); 
//    $gui_open = config_get('gui_separator_open');
//    $gui_close = config_get('gui_separator_close');
//
//    $gui->strOptionAny = $gui_open . lang_get('any') . $gui_close;
//    $gui->do_reload = 0;
//    $gui->src_workframe = null;
//
//    $gui->keywordsFilterItemQty = 0;
//    $gui->keywordID = $argsObj->keyword_id; 
//    $gui->keywordsMap = $tprojectMgr->get_keywords_map($argsObj->tproject_id); 
//    if(!is_null($gui->keywordsMap))
//    {
//        $gui->keywordsMap = array( 0 => $gui->strOptionAny) + $gui->keywordsMap;
//        $gui->keywordsFilterItemQty = min(count($gui->keywordsMap),3);
//    }
//
//    $gui->execType = $argsObj->exec_type; 
//    
//    $initValues['keywords'] = $gui->keywordsMap;
//    $initValues['execTypes'] = 'init';  // initialisation will be done on tlControlPanel()
//    $gui->controlPanel = new tlControlPanel($dbHandler,$argsObj,$initValues);
//	$gui->execTypeMap = $gui->controlPanel->filters['execTypes']['items'];
//	
//    // filter using user roles
//	$opt = array('output' => 'combo');
//    $gui->controlPanel->settings['testPlans']['items'] = 
//    	$_SESSION['currentUser']->getAccessibleTestPlans($dbHandler,$argsObj->tproject_id,null,$opt);
//
//	$gui->controlPanel->filters['testPlans']['items'] = $gui->controlPanel->settings['testPlans']['items'];
//
//    $gui->mapTPlans = $gui->controlPanel->settings['testPlans']['items'];
//    $gui->tPlanID = $argsObj->tplan_id;
//    $gui->importance = $argsObj->importance; 
//
//	// 20100410    
//
//    $gui->menuUrl = 'lib/plan/planAddTC.php';
//    $gui->args = '&tplan_id=' . $gui->tPlanID;
//    if(is_array($argsObj->keyword_id))
//    {
//		    $kl = implode(',',$argsObj->keyword_id);
//		    $gui->args .= '&keyword_id=' . $kl;
//    }
//    else if($argsObj->keyword_id > 0)
//    {
//		    $gui->args .= '&keyword_id='.$argsObj->keyword_id;
//    }
//    $gui->args .= '&keywordsFilterType=' . $argsObj->keywordsFilterType;
//    $gui->args .= '&executionType=' . $argsObj->exec_type;
//    $gui->args .= '&importance=' . $argsObj->importance;
//
//
//    $gui->keywordsFilterTypes = new stdClass();
//    $gui->keywordsFilterTypes->options = array('OR' => 'Or' , 'AND' =>'And'); 
//    $gui->keywordsFilterTypes->selected=$argsObj->keywordsFilterType;
//
//    // BUGID 3301
//    $gui->design_time_cfields = $exec_cfield_mgr->html_table_of_custom_field_inputs(30);
//    $gui->additionalArgs = '';
//    return $gui;
//}
//
//
///*
//  function: 
//
//  args :
//  
//  returns: 
//  
//  rev:
//  20100428 - asimon - BUGID 3301, added exec_cfield_mgr
//
//*/
//function buildTree(&$dbHandler,&$guiObj,&$argsObj, &$exec_cfield_mgr)
//{
//	$treeMenu = null;
//	$my_workframe = $_SESSION['basehref']. $guiObj->menuUrl .                      
//	                "?edit=testproject&id={$argsObj->tproject_id}" . $guiObj->args;
//	
//	if($argsObj->doUpdateTree)
//	{
//	     $guiObj->src_workframe = $my_workframe; 
//	}
//	else if($argsObj->called_by_me)
//	{
//		// -------------------------------------------------------------------------------
//		// 20090308 - franciscom - think this is result of cut/paste from other
//		//                         piece of TL (look at edit=testsuite that has no use on 
//		//                         test case testplan assignment.
//		//
//		// Explain what is objective of this chunck of code 
//		// Warning:
//		// Algorithm based on field order on URL call
//		// 
//	   	$dummy = explode('?',$argsObj->called_url);
//	   
//	    
//	   	$qs = isset($dummy[1]) ? explode('&',$dummy[1]) : array(0 => '');
//	   	if($qs[0] == 'edit=testsuite')
//	   	{
//			    $guiObj->src_workframe = $dummy[0] . "?" . $qs[0] . "&" . $guiObj->args;
//	   	}
//	   	else 
//	   	{
//			    $guiObj->src_workframe = $my_workframe; 
//		}   
//		// -------------------------------------------------------------------------------
//	}
//
//	$filters = array();
//	$filters['keywords'] = buildKeywordsFilter($argsObj->keyword_id,$guiObj);
//
//	
//	$filters['executionType'] = buildExecTypeFilter($argsObj->exec_type); // BUGID 2797
//	$filters['importance'] = buildImportanceFilter($argsObj->importance); // BUGID 2498
//    
//	// BUGID 3301
//    $filters['cf_hash'] = $exec_cfield_mgr->get_set_values();
//	
//	$applyFilter = !is_null($filters['cf_hash']) || !is_null($filters['keywords']) || 
//				   (!is_null($filters['executionType']) && intval($filters['executionType']->items) > 0) ||
//				   (!is_null($filters['importance']) && intval($filters['importance']->items) > 0);
//
//	if($applyFilter)
//	{
//		// 20100412 - franciscom
//		$filters['testplan'] = $argsObj->tplan_id;
//		$options = array('forPrinting' => NOT_FOR_PRINTING, 'hideTestCases' => HIDE_TESTCASES,
//		                 'tc_action_enabled' => ACTION_TESTCASE_DISABLE,
//		                 'ignore_inactive_testcases' => IGNORE_INACTIVE_TESTCASES,
//		                 'getArguments' => $guiObj->args, 'viewType' => 'testSpecTreeForTestPlan');
//		
//		$treeMenu = generateTestSpecTree($dbHandler,$argsObj->tproject_id, $argsObj->tproject_name,
//		                                 $guiObj->menuUrl,$filters,$options);
//		                                 
//	    // When using filters I need to switch to static generated tree, instead of Lazy Loading Ajax Tree
//	    // that's reason why I'm re-creating from scratch ajaxTree.
//	    //
//	    $cookiePrefix = $guiObj->ajaxTree->cookiePrefix;
//		$guiObj->ajaxTree = new stdClass();
//	    $guiObj->ajaxTree->loader = '';
//	    $guiObj->ajaxTree->root_node = $treeMenu->rootnode;
//		$guiObj->ajaxTree->cookiePrefix = $cookiePrefix;
//	     
//	    // BUGID 2384 - if we return '' or null => EXT JS does not like it, we need to
//	    // return json string that represents an EMPTY Tree => [] 
//	    $guiObj->ajaxTree->children = $treeMenu->menustring ? $treeMenu->menustring : "[]";
//	}
//	return $treeMenu;
//}
//
//
///**
// * init ajax tree class
// *
// */
//function initAjaxTree($argsObj,$basehref)
//{
//    // need to understand if this is useless
//    // 20080629 - franciscom
//    // "filter_node={$argsObj->tsuites_to_show}";
//    $ajaxTree=new stdClass();
//    $ajaxTree->loader=$basehref . 'lib/ajax/gettprojectnodes.php?' .
//                      "root_node={$argsObj->tproject_id}&show_tcases=0";
//                            
//
//    $ajaxTree->root_node=new stdClass();
//    $ajaxTree->root_node->href="javascript:EP({$argsObj->tproject_id})";
//    $ajaxTree->root_node->id=$argsObj->tproject_id;
//    $ajaxTree->root_node->name=$argsObj->tproject_name;
//  
//    // 20080831 - franciscom - Custom attribute
//    // You can access to it's value using public property 'attributes' of object of Class Ext.tree.TreeNode 
//    // example: mynode.attributes.testlink_node_type
//    //
//    // Important: 
//    // For root node (this node)
//    // You need to initialize every custom property you want to add to root node
//    // on the js file that create it (treebyloader.js) and smarty template
//    // 
//    // Also this property must be managed in php code used to generate JSON code.
//    //
//    // I'appologize for using MAGIC constant
//    $ajaxTree->root_node->testlink_node_type='testproject';
//
//    // Prefix for cookie used to save tree state
//    $ajaxTree->cookiePrefix="planaddtc_{$ajaxTree->root_node->id}_{$argsObj->user_id}_";
//
//    // not allowed in this feature
//    $ajaxTree->dragDrop=new stdClass();
//    $ajaxTree->dragDrop->enabled=false;
//    $ajaxTree->dragDrop->BackEndUrl='';
//
//    // TRUE -> beforemovenode() event will use our custom implementation 
//    $ajaxTree->dragDrop->useBeforeMoveNode=FALSE;
//
//    return $ajaxTree;
//}

?>