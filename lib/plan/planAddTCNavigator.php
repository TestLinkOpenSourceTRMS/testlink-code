<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: planAddTCNavigator.php,v 1.49 2010/02/28 10:36:58 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * 	Navigator for feature: add Test Cases to a Test Case Suite in Test Plan. 
 *	It builds the javascript tree that allow the user select a required part 
 *	Test specification. Keywords should be used for filter.
 *
 * @internal Revisions:
 *
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
$args = init_args();
$gui = initializeGui($db,$args);
$gui->ajaxTree = initAjaxTree($args,$_SESSION['basehref']);
$gui->tree = buildTree($db,$gui,$args);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);

// IMPORTANT: A javascript variable 'args' will be initialized with this value
// using inc_head.tpl template.
$smarty->assign('args', $gui->args);
$smarty->assign('menuUrl', $gui->menuUrl);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: get input data 

  args: -

  returns: object expected parameters

*/
function init_args()
{
    $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    // Is an array because is a multiselect 
    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
    
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID'];
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    $args->user_id = $_SESSION['userID'];
    
    $args->doUpdateTree = isset($_REQUEST['doUpdateTree']) ? 1 : 0;

    $args->called_by_me = isset($_REQUEST['called_by_me']) ? 1 : 0;
    $args->called_url = isset($_REQUEST['called_url']) ? $_REQUEST['called_url'] : null;
 
    $args->keywordsFilterType =isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';

    return $args;
}


/*
  function: initializeGui
  args :
  returns: 

  rev:20080629 - franciscom - added missed argument basehref
      20080622 - franciscom - changes for ext js tree
      20080429 - franciscom
*/
function initializeGui(&$dbHandler,&$argsObj,$basehref)
{
    $gui = new stdClass();
    $tprojectMgr = new testproject($dbHandler);
    $tcaseCfg = config_get('testcase_cfg'); 


    $gui->do_reload = 0;
    $gui->src_workframe = null;
    
    $gui->keywordsFilterItemQty = 0;
    $gui->keyword_id = $argsObj->keyword_id; 
    $gui->keywords_map = $tprojectMgr->get_keywords_map($argsObj->tproject_id); 
    if(!is_null($gui->keywords_map))
    {
        $gui->keywordsFilterItemQty = min(count($gui->keywords_map),3);
    }

    // filter using user roles
    $tplans = $_SESSION['currentUser']->getAccessibleTestPlans($dbHandler,$argsObj->tproject_id);
    $gui->map_tplans = array();
    foreach($tplans as $key => $value)
    {
    	$gui->map_tplans[$value['id']] = $value['name'];
    }

    $gui->tplan_id = $argsObj->tplan_id;

    $gui->menuUrl = 'lib/plan/planAddTC.php';
    $gui->args = '&tplan_id=' . $gui->tplan_id;
    if(is_array($argsObj->keyword_id))
    {
		    $kl = implode(',',$argsObj->keyword_id);
		    $gui->args .= '&keyword_id=' . $kl;
    }
    else if($argsObj->keyword_id > 0)
    {
		    $gui->args .= '&keyword_id='.$argsObj->keyword_id;
    }
    $gui->args .= '&keywordsFilterType=' . $argsObj->keywordsFilterType;

    $gui->keywordsFilterType = new stdClass();
    $gui->keywordsFilterType->options = array('OR' => 'Or' , 'AND' =>'And'); 
    $gui->keywordsFilterType->selected=$argsObj->keywordsFilterType;
    
    return $gui;
}


/*
  function: 

  args :
  
  returns: 

*/
function buildTree(&$dbHandler,&$guiObj,&$argsObj)
{
	$treeMenu = null;
	$my_workframe = $_SESSION['basehref']. $guiObj->menuUrl .                      
	                "?edit=testproject&id={$argsObj->tproject_id}" . $guiObj->args;
	
	if($argsObj->doUpdateTree)
	{
	     $guiObj->src_workframe = $my_workframe; 
	}
	else if($argsObj->called_by_me)
	{
		// -------------------------------------------------------------------------------
		// 20090308 - franciscom - think this is result of cut/paste from other
		//                         piece of TL (look at edit=testsuite that has no use on 
		//                         test case testplan assignment.
		//
		// Explain what is objective of this chunck of code 
		// Warning:
		// Algorithm based on field order on URL call
		// 
	   	$dummy = explode('?',$argsObj->called_url);
	   
	   	$qs = explode('&',$dummy[1]);
	   	if($qs[0] == 'edit=testsuite')
	   	{
			    $guiObj->src_workframe = $dummy[0] . "?" . $qs[0] . "&" . $guiObj->args;
	   	}
	   	else 
	   	{
			    $guiObj->src_workframe = $my_workframe; 
		}   
		// -------------------------------------------------------------------------------
	}

	$filters = array();
	$filters['keywords'] = buildKeywordsFilter($argsObj->keyword_id,$guiObj);
	$applyFilter = !is_null($filters['keywords']); // BUGID 0001927
	if($applyFilter)
	{
		$options = array('forPrinting' => NOT_FOR_PRINTING, 'hideTestCases' => HIDE_TESTCASES,
		                 'tc_action_enabled' => ACTION_TESTCASE_DISABLE,
		                 'ignore_inactive_testcases' => IGNORE_INACTIVE_TESTCASES,
		                 'getArguments' => $guiObj->args);
		
		$treeMenu = generateTestSpecTree($dbHandler,$argsObj->tproject_id, $argsObj->tproject_name,
		                                 $guiObj->menuUrl,$filters,$options);
	       
	    // When using filters I need to switch to static generated tree, instead of Lazy Loading Ajax Tree
	    // that's reason why I'm re-creating from scratch ajaxTree.
	    //
	    $cookiePrefix = $guiObj->ajaxTree->cookiePrefix;
		$guiObj->ajaxTree = new stdClass();
	    $guiObj->ajaxTree->loader = '';
	    $guiObj->ajaxTree->root_node = $treeMenu->rootnode;
		$guiObj->ajaxTree->cookiePrefix = $cookiePrefix;
	     
	    // BUGID 2384 - if we return '' or null => EXT JS does not like it, we need to
	    // return json string that represents an EMPTY Tree => [] 
	    $guiObj->ajaxTree->children = $treeMenu->menustring ? $treeMenu->menustring : "[]";
	}
	return $treeMenu;
}


/**
 * init ajax tree class
 *
 */
function initAjaxTree($argsObj,$basehref)
{
    // need to understand if this is useless
    // 20080629 - franciscom
    // "filter_node={$argsObj->tsuites_to_show}";
    $ajaxTree=new stdClass();
    $ajaxTree->loader=$basehref . 'lib/ajax/gettprojectnodes.php?' .
                      "root_node={$argsObj->tproject_id}&show_tcases=0";
                            

    $ajaxTree->root_node=new stdClass();
    $ajaxTree->root_node->href="javascript:EP({$argsObj->tproject_id})";
    $ajaxTree->root_node->id=$argsObj->tproject_id;
    $ajaxTree->root_node->name=$argsObj->tproject_name;
  
    // 20080831 - franciscom - Custom attribute
    // You can access to it's value using public property 'attributes' of object of Class Ext.tree.TreeNode 
    // example: mynode.attributes.testlink_node_type
    //
    // Important: 
    // For root node (this node)
    // You need to initialize every custom property you want to add to root node
    // on the js file that create it (treebyloader.js) and smarty template
    // 
    // Also this property must be managed in php code used to generate JSON code.
    //
    // I'appologize for using MAGIC constant
    $ajaxTree->root_node->testlink_node_type='testproject';

    // Prefix for cookie used to save tree state
    $ajaxTree->cookiePrefix="planaddtc_{$ajaxTree->root_node->id}_{$argsObj->user_id}_";

    // not allowed in this feature
    $ajaxTree->dragDrop=new stdClass();
    $ajaxTree->dragDrop->enabled=false;
    $ajaxTree->dragDrop->BackEndUrl='';

    // TRUE -> beforemovenode() event will use our custom implementation 
    $ajaxTree->dragDrop->useBeforeMoveNode=FALSE;

    return $ajaxTree;
}
?>