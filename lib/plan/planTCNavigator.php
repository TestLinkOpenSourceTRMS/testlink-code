<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @version $Id: planTCNavigator.php,v 1.19 2008/07/05 14:17:45 franciscom Exp $
 * @author Martin Havlat
 *
 * Test navigator for Test Plan
 *
 * rev :
 *  20080702 - havlatm - added urgency support
 *  20080629 - franciscom - fixed missing variables bugs
 *  20080621 - franciscom - added code to use ext js tree
 *	20080429 - multiple keyword filter
 *  20080311 - franciscom - BUGID 1427 - first developments
 *  20070925 - franciscom - added management of workframe
 * 
 * ----------------------------------------------------------------------------------- */

require('../../config.inc.php');
require_once("common.php");
require_once("users.inc.php");
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();

$tplan_mgr = new testplan($db);
$args = init_args($tplan_mgr);
$gui = initializeGui($db,$args,$tplan_mgr);
$gui->additional_string='';

$treeMenu=buildTree($db,$gui,$args);                                                
$gui->tree=$treeMenu->menustring;
if( !is_null($treeMenu->rootnode) )
{
    $gui->ajaxTree=new stdClass();
    $gui->ajaxTree->loader='';
    $gui->ajaxTree->root_node=new stdClass();
    $gui->ajaxTree->root_node=$treeMenu->rootnode;
    $gui->ajaxTree->children=$treeMenu->menustring;
    $gui->ajaxTree->cookiePrefix=$args->feature;
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);

//  
// Warning: the following variable names CAN NOT BE Changed,
// because there is global coupling on template logic
$smarty->assign('treeKind',$gui->treeKind);
$smarty->assign('menuUrl',$gui->menuUrl);
$smarty->assign('args',$gui->args);
$smarty->assign('treeHeader', $gui->title);

$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: init_args

  args : pointer to test Plan manager
  returns: 

*/
function init_args(&$tplanMgr)
{
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args = new stdClass();

    $args->feature = $_REQUEST['feature'];
    switch($args->feature)
    {
      	case 'planUpdateTC':
      	case 'removeTC':
		case 'test_urgency':
      	case 'tc_exec_assignment':
    	break;
    
    	default:
    	tLog("Wrong or missing GET argument 'feature'.", 'ERROR');
    	exit();
    	break;
    }

    $args->user_id = $_SESSION['userID'];
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    
    // Array because is a multiselect input
    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
    $args->keywordsFilterType=isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';
   
    
    $args->help_topic = isset($_REQUEST['help_topic']) ? $_REQUEST['help_topic'] : $args->feature;

    if(isset($_REQUEST['doUpdateTree']) || isset($_REQUEST['called_by_me']))
    {
		$args->src_workframe = $_SESSION['basehref'] . 
                      "lib/general/staticPage.php?key={$args->help_topic}";
    }
	else
	{
		$args->src_workframe = '';
	}

    $args->filter_assigned_to = isset($_REQUEST['filter_assigned_to']) ? intval($_REQUEST['filter_assigned_to']) : 0;

    // 20070120 - franciscom -
    // is possible to call this page using a Test Project that have no test plans
    // in this situation the next to entries are undefined in SESSION
    $args->tplan_id = isset($_SESSION['testPlanId']) ? intval($_SESSION['testPlanId']) : 0;
    $args->tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : '';

    if($args->tplan_id != 0)
    {
		    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
		    $tplan_info = $tplanMgr->get_by_id($args->tplan_id);
		    $args->tplan_name = $tplan_info['name'];
    }

    return $args;
}

/*
  function: initializeGui
  
  args :
  
  returns:
   
  rev: 20080429 - franciscom
*/
function initializeGui(&$dbHandler,&$argsObj,&$tplanMgr)
{
    $gui = new stdClass();

    $gui->treeKind=TL_TREE_KIND;
    $gui->filter_assigned_to=$argsObj->filter_assigned_to;
    $gui->keywordsFilterItemQty=0;
    $gui->keyword_id=$argsObj->keyword_id; 

    // We only want to use in the filter, keywords present in the test cases that are
    // linked to test plan, and NOT all keywords defined for test project
    $gui->keywords_map=$tplanMgr->get_keywords_map($argsObj->tplan_id); 
    if( !is_null($gui->keywords_map) )
    {
        $gui->keywordsFilterItemQty=min(count($gui->keywords_map),3);
    }

    $gui->keywordsFilterType=new stdClass();                                 
    $gui->keywordsFilterType->options = array('OR' => 'Or' , 'AND' =>'And'); 
    $gui->keywordsFilterType->selected=$argsObj->keywordsFilterType;         

    // filter using user roles
    $tplans = getAccessibleTestPlans($dbHandler,$argsObj->tproject_id,$argsObj->user_id,1);
    $gui->map_tplans = array();
    foreach($tplans as $key => $value)
    {
    	$gui->map_tplans[$value['id']] = $value['name'];
    }
    $gui->tplan_id=$argsObj->tplan_id;
    $gui->testers=null;
   	$gui->title = lang_get('title_test_plan_navigator');
    $gui->src_workframe=$argsObj->src_workframe;

    $gui->draw_bulk_update_button=false;
    switch($argsObj->feature)
    {
      case 'planUpdateTC':
    	$gui->menuUrl = "lib/plan/planUpdateTC.php";
    	$gui->draw_bulk_update_button=true;
    	break;
    
      case 'removeTC':
    	$gui->menuUrl = "lib/plan/planTCRemove.php";
    	break;
    
      case 'test_urgency':
    	$gui->menuUrl = "lib/plan/planUrgency.php";
	    break;
    
      case 'tc_exec_assignment':
    	// BUGID 1427
    	$gui->menuUrl = "lib/plan/tc_exec_assignment.php";
    	$gui->testers = getTestersForHtmlOptions($dbHandler,$argsObj->tplan_id,$argsObj->tproject_id);
    	break;
    }

    return $gui;
}

/*
  function: buildTree
  
  args :
  
  returns: 
*/
function buildTree(&$dbHandler,&$guiObj,&$argsObj)
{
    // Developer remarks:
    // using global coupling is 99% (ALWAYS) BAD -> global $tlCfg;
    // use config_get() because:
    //
    // - is standard practice on whole TL code (sued in 75 files).
    // - is better because you do not need to care about name
    //   of config object or variable.
    // 
    $treemenu_type=config_get('treemenu_type');

    $filters = new stdClass();
    $additionalInfo = new stdClass();

    $filters->keyword_id = $argsObj->keyword_id;
    $filters->keywordsFilterType = $argsObj->keywordsFilterType;
    
    $filters->tc_id = FILTER_BY_TC_OFF;
    $filters->build_id = FILTER_BY_BUILD_OFF;
    $filters->assignedTo = FILTER_BY_ASSIGNED_TO_OFF;
    $filters->status = FILTER_BY_TC_STATUS_OFF;
    $filters->cf_hash = SEARCH_BY_CUSTOM_FIELDS_OFF;
    $filters->include_unassigned=1;
    $filters->show_testsuite_contents=1;
    
   	$filters->hide_testcases = 0;
    switch($argsObj->feature)
    {
    
      case 'test_urgency':
//    	$filters->hide_testcases = 1;
      break;
    
      case 'tc_exec_assignment':
    	$filters->assignedTo = $argsObj->filter_assigned_to;
    	$filters->include_unassigned = 0;
    	break;
    }

    $additionalInfo->useCounters=CREATE_TC_STATUS_COUNTERS_OFF;
    $additionalInfo->useColours=COLOR_BY_TC_STATUS_OFF;

    $guiObj->args=initializeGetArguments($argsObj,$filters);
    
    // 20080621 - francisco.mancardi@gruppotesi.com
    $treeMenu = generateExecTree($dbHandler,$guiObj->menuUrl,
                                 $argsObj->tproject_id,$argsObj->tproject_name,
                                 $argsObj->tplan_id,$argsObj->tplan_name,
                                 $guiObj->args,$filters,$additionalInfo);

    if( $treemenu_type != 'EXTJS' )
    {
        $treeMenu->menustring=invokeMenu($treeMenu->menustring,null,null);
    }
    return $treeMenu;
}


/*
  function: initializeGetArguments
            build arguments that will be passed to execSetResults.php
            with a http call
            This arguments that will be passed from tree menu 
            to launched pages, when user do some action on tree (example clicks on a folder)

  args:

  returns:

  rev: 20080427 - franciscom - added cfgObj arguments
       20080224 - franciscom - added include_unassigned

*/
function initializeGetArguments($argsObj,$filtersObj)
{
    $kl='';
    $settings = '&include_unassigned=' . $filtersObj->include_unassigned;

    if( is_array($argsObj->keyword_id) )
    {
       $kl=implode(',',$argsObj->keyword_id);
       $settings .= '&keyword_id=' . $kl;
    }
    else if($argsObj->keyword_id > 0)
    {
    	  $settings .= '&keyword_id='.$argsObj->keyword_id;
    }
    $settings .= '&keywordsFilterType='.$argsObj->keywordsFilterType;
    
    if($filtersObj->assignedTo)
    	  $settings .= '&filter_assigned_to=' . $filtersObj->assignedTo;
    
    $settings .= '&tplan_id=' . $argsObj->tplan_id;
    
    return $settings;
}
?>