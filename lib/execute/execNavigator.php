<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: execNavigator.php,v $
 *
 * @version $Revision: 1.104 $
 * @modified $Date: 2010/04/15 18:43:54 $ by $Author: franciscom $
 *
 * rev:
 *      20100409 - eloff - BUGID 3050 - remember selected platform and build in session
 *      20100222 - asimon - fixes in initializeGui() for testplan select box when there are no builds
 *      20100217 - asimon - added check for open builds on initBuildInfo()
 *      20100202 - asimon - changed filtering, BUGID 2455, BUGID 3026
 *      20090828 - franciscom - added contribution platform feature
 *      20090828 - franciscom - BUGID 2296 - filter by Last Exec Result on Any of previous builds
 *      20081227 - franciscom - BUGID 1913 - filter by same results on ALL previous builds
 *      20081220 - franciscom - advanced/simple filters
 *      20081217 - franciscom - only users that have effective role with right 
 *                              that allow test case execution are displayed on
 *                              filter by user combo.
 *                             
 *      20080517 - franciscom - fixed testcase filter bug
 *      20080428 - franciscom - keyword filter can be done on multiple keywords
 *      20080224 - franciscom - refactoring
 *      20080224 - franciscom - BUGID 1056
 **/
require_once('../../config.inc.php');
require_once('common.php');
require_once("users.inc.php");
require_once('treeMenu.inc.php');
require_once('exec.inc.php');
testlinkInitPage($db);


$templateCfg = templateConfiguration();
$cfg = getCfg();
$tproject_mgr = new testproject($db);
$tplan_mgr = new testplan($db);
$args = init_args($db,$cfg,$tproject_mgr,$tplan_mgr);
$exec_cfield_mgr = new exec_cfield_mgr($db,$args->tproject_id);
$platform_mgr = new tlPlatform($db, $args->tproject_id);
$gui = initializeGui($db,$args,$cfg,$exec_cfield_mgr,$tplan_mgr,$platform_mgr);

buildAssigneeFilter($db,$gui,$args,$cfg);

$treeMenu = buildTree($db,$gui,$args,$cfg,$exec_cfield_mgr);
$gui->tree = $treeMenu->menustring;

if( !is_null($treeMenu->rootnode) )
{
    $gui->ajaxTree = new stdClass();
    $gui->ajaxTree->loader = '';
    $gui->ajaxTree->root_node = new stdClass();
    $gui->ajaxTree->root_node = $treeMenu->rootnode;
    $gui->ajaxTree->children = $treeMenu->menustring;
    $gui->ajaxTree->cookiePrefix = 'exec_tplan_id_' . $args->tplan_id;
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->assign('menuUrl',$gui->menuUrl);
$smarty->assign('args',$gui->args);

$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function:
  args:
  returns:

*/
function init_args(&$dbHandler,$cfgObj, &$tprojectMgr, &$tplanMgr)
{
  	$_REQUEST = strings_stripSlashes($_REQUEST);
    $args = new stdClass();
    
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    $args->user = $_SESSION['currentUser'];
    
    $args->tplan_id = isset($_SESSION['testplanID']) ? intval($_SESSION['testplanID']) : 0;
    $args->tplan_name = isset($_SESSION['testplanName']) ? $_SESSION['testplanName'] : '';
    if($args->tplan_id != 0)
    {
		    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID'];
		    $tplan_info = $tplanMgr->get_by_id($args->tplan_id);
		    $args->tplan_name = $tplan_info['name'];
    }
    
    if($args->tplan_id != $_SESSION['testplanID']) {
    	//testplan was changed, so we reset the filters, they were chosen for another testplan
    	$keys2delete = array('tcase_id', 'targetTestCase', 'keyword_id', 'filter_status','keywordsFilterType',
    						'filter_method', 'filter_assigned_to', 'build_id', 'urgencyImportance',
    						'filter_build_id', 'platform_id', 'include_unassigned', 'colored');
    	foreach ($keys2delete as $key) {
    		unset($_REQUEST[$key]);
    	}
    	$currentUser = $_SESSION['currentUser'];
    	$arrPlans = $currentUser->getAccessibleTestPlans($dbHandler,$args->tproject_id);
		foreach ($arrPlans as $plan) {
			if ($plan['id'] == $args->tplan_id) {
				setSessionTestPlan($plan);
			}
		}
	}

    $args->treeColored = (isset($_REQUEST['colored']) && ($_REQUEST['colored'] == 'result')) ? 'selected="selected"' : null;
    $args->tcase_id = isset($_REQUEST['tcase_id']) ? intval($_REQUEST['tcase_id']) : null;
    $args->advancedFilterMode = isset($_REQUEST['advancedFilterMode']) ? $_REQUEST['advancedFilterMode'] : 0;
    
    // Attention: Is an array because is a multiselect 
    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
    $args->keywordsFilterType = isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';
    
    
    $args->doUpdateTree = isset($_REQUEST['submitOptions']) ? 1 : 0;
    
    // 20081220 - franciscom
    // Now can be multivalued
    $args->optResultSelected = isset($_REQUEST['filter_status']) ? (array)$_REQUEST['filter_status'] : null;
    if( !is_null($args->optResultSelected) )
    {
        if( in_array($cfgObj->results['status_code']['all'], $args->optResultSelected) )
        {
            $args->optResultSelected = array($cfgObj->results['status_code']['all']);
        }
        else if( !$args->advancedFilterMode && count($args->optResultSelected) > 0)
        {
            // Because user has switched to simple mode we will get ONLY first status
            $args->optResultSelected=array($args->optResultSelected[0]);
        }
    }
    $args->filter_status = $args->optResultSelected;
	
    // BUGID 2455
	$filter_cfg = config_get('execution_filter_methods');
    $args->filter_method_selected = isset($_REQUEST['filter_method']) ?
    							(array)$_REQUEST['filter_method'] : (array)$filter_cfg['default_type'];
    
    $user_filter_default = null;
    switch($cfgObj->exec->user_filter_default)
    {
    	case 'logged_user':
        	$user_filter_default = $args->user->dbID;
      		break;

    	case 'none':
	    default:
	    	break;
    }
    
    $args->filter_assigned_to = isset($_REQUEST['filter_assigned_to']) ? $_REQUEST['filter_assigned_to'] : $user_filter_default;
    if( !is_null($args->filter_assigned_to) )
    {
        $args->filter_assigned_to = (array)$args->filter_assigned_to;
        if(in_array(TL_USER_ANYBODY, $args->filter_assigned_to))
        {
            $args->filter_assigned_to = array(TL_USER_ANYBODY);  
        }
        else if(in_array(TL_USER_NOBODY, $args->filter_assigned_to))
        {
            $args->filter_assigned_to = array(TL_USER_NOBODY);    
        } 
        else if(!$args->advancedFilterMode && count($args->filter_assigned_to) > 0)
        {
            // Because user has switched to simple mode we will get ONLY first status
            $args->filter_assigned_to=array($args->filter_assigned_to[0]);
        }
    }  
    
    $args->urgencyImportance = isset($_REQUEST['urgencyImportance']) ? intval($_REQUEST['urgencyImportance']) : null;
    if ($args->urgencyImportance == 0)
    {
    	$args->urgencyImportance = null;
    }
    
	// CRITIC: values assigned here will be used on functions initBuildInfo(), initPlatformInfo()
	//         if we can here we need to change functions

	// Set active platform (remember in session)
	$args->optPlatformSelected = isset($_REQUEST['platform_id']) ? $_REQUEST['platform_id'] : null;
	if (is_null($args->optPlatformSelected) && isset($_SESSION['platformID']))
	{
		$args->optPlatformSelected =  intval($_SESSION['platformID']);
	}
	if ($args->optPlatformSelected != $_SESSION['platformID'])
	{
		$_SESSION['platformID'] = $args->optPlatformSelected;
	}

	// Set active build (remember in session)
	$args->optBuildSelected = isset($_REQUEST['build_id']) ? $_REQUEST['build_id'] : -1;
	if ($args->optBuildSelected == -1 && isset($_SESSION['buildID']))
	{
		$args->optBuildSelected = intval($_SESSION['buildID']);
	}
	if ($args->optBuildSelected != $_SESSION['buildID'])
	{
		$_SESSION['buildID'] = $args->optBuildSelected;
	}
	$args->optFilterBuildSelected = isset($_REQUEST['filter_build_id']) ? $_REQUEST['filter_build_id'] : -1;
	$args->include_unassigned = isset($_REQUEST['include_unassigned']) ? $_REQUEST['include_unassigned'] : 0;

    return $args;
}


/**
 * build arguments that will be passed to execSetResults.php
 *           with a http call
 *
 *
 * @internal Revisions:
 * 20090815 - franciscom - added platform feature (contribution)
 */
function initializeGetArguments($argsObj,$cfgObj,$customFieldSelected)
{
    $kl='';
    $settings = '&build_id=' . $argsObj->optBuildSelected .
                '&platform_id=' . $argsObj->optPlatformSelected .
  	            '&include_unassigned=' . $argsObj->include_unassigned;

    if(is_array($argsObj->keyword_id) && !in_array(0, $argsObj->keyword_id))
    {
       $kl = implode(',',$argsObj->keyword_id);
       $settings .= '&keyword_id=' . $kl;
    }
    else if(!is_array($argsObj->keyword_id) && $argsObj->keyword_id > 0)
    {
    	  $settings .= '&keyword_id='.$argsObj->keyword_id;
    }
    $settings .= '&keywordsFilterType='.$argsObj->keywordsFilterType;
    
    if($argsObj->tcase_id != 0)
    {
        $settings .= '&tc_id='.$argsObj->tcase_id;
    }
    
    if ($argsObj->urgencyImportance > 0)
    {
    	$settings .= "&urgencyImportance={$argsObj->urgencyImportance}";
    }
        
    if( !is_null($argsObj->filter_assigned_to) &&
        !in_array(TL_USER_ANYBODY,$argsObj->filter_assigned_to) )
    {
    	  $settings .= '&filter_assigned_to='. serialize($argsObj->filter_assigned_to);
    }   
       
    if( !is_null($argsObj->optResultSelected) && 
        !in_array($cfgObj->results['status_code']['all'],$argsObj->optResultSelected) )
    {
        $settings .= '&filter_status='. serialize($argsObj->optResultSelected);
    }

    if ($customFieldSelected)
    {
    	 $settings .= '&cfields='. serialize($customFieldSelected);
    }
    return $settings;
}


/*
  function: 

  args :
  
  returns: 

*/
function getCfg()
{
    $cfg = new stdClass();
    $cfg->gui = config_get('gui');
    $cfg->exec = config_get('exec_cfg');
    $cfg->results = config_get('results');
    
    return $cfg;
}



/*
  function: buildAssigneeFilter

  args:
  
  returns: 

*/
function buildAssigneeFilter(&$dbHandler,&$guiObj,&$argsObj,$cfgObj)
{
    
    $guiObj->disable_filter_assigned_to = false;
    $guiObj->assigned_to_user = '';
    
    $effective_role = $argsObj->user->getEffectiveRole($dbHandler,$argsObj->tproject_id,$argsObj->tplan_id);
    
    // 20081217 - franciscom
    // If we check right 'testplan_execute', we do not get desired effect, because we are not able
    // to treat in a different way a SIMPLE TESTER from a SENIOR TESTER.
    // Possible solutions:
    // 1- Check again a set of configurable roles
    //
    // 2- Create a set of execute rights, one that allows limited execution that is affected by
    //    exec->view_mode and exec->exec_mode, and other that is immune.
    //
    // 3- on execSetResults.php has been done 
 	//    Role is considered simple tester if:
	//    role == TL_ROLES_TESTER OR Role has Test Plan execute but not Test Plan planning
    //
    // 4- we can support option 1 and 2, or 1 and 3
    //
    //
    //
    $simple_tester_roles = array_flip($cfgObj->exec->simple_tester_roles);
 	$can_execute = $effective_role->hasRight('testplan_execute');
	$can_manage = $effective_role->hasRight('testplan_planning');
    $use_exec_cfg = isset($simple_tester_roles[$effective_role->dbID]) || ($can_execute && !$can_manage);
    $exec_view_mode = $use_exec_cfg ? $cfgObj->exec->view_mode->tester : 'all';
    switch ($exec_view_mode)
    {
    	case 'all':
 		    $guiObj->filter_assigned_to = is_null($argsObj->filter_assigned_to) ? null : $argsObj->filter_assigned_to;
    		break;
    
    	case 'assigned_to_me':
    		$guiObj->disable_filter_assigned_to = true;
    		$argsObj->filter_assigned_to = (array)$argsObj->user->dbID;
            $guiObj->filter_assigned_to = $argsObj->filter_assigned_to;
    		$guiObj->assigned_to_user = $argsObj->user->getDisplayName();
    		break;
    }
}


/**
 * Initialize a map with build info to choose as build to execute, 
 * for creating HTML Select in user interface.
 * Load only active and open builds, no matter what role user has.
 *
 * @param resource &$dbHandler reference to database object
 * @param stdClass &$argsObj reference to object with user input
 * @param tlTestplan &$tplanMgr testplan manager object
 * @return array $htmlSelect HTML-Select for build to execute selection
 *
 * @internal revisions:
 * 20100217 - asimon - added also check for open status of builds,
 * 						since builds have to be active AND open to be executed
 * 
 */
function initBuildInfo(&$dbHandler,&$argsObj,&$tplanMgr)
{
    $htmlSelect = array('items' => null, 'selected' => null);
    $htmlSelect['items'] = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id,
    										testplan::GET_ACTIVE_BUILD, testplan::GET_OPEN_BUILD);
   
    $maxBuildID = $tplanMgr->get_max_build_id($argsObj->tplan_id,
											testplan::GET_ACTIVE_BUILD, testplan::GET_OPEN_BUILD);

    $argsObj->optBuildSelected = $argsObj->optBuildSelected > 0 ? $argsObj->optBuildSelected : $maxBuildID;
    if (!$argsObj->optBuildSelected && sizeof($htmlSelect['items']))
    {
    	$argsObj->optBuildSelected = key($htmlSelect['items']);
    }
    $htmlSelect['selected'] = $argsObj->optBuildSelected;
    
    return $htmlSelect;
}

/**
 * Initialize a map with build info to choose as filter option, 
 * for creating HTML Select in user interface.
 * Load only active builds, as only they are to be displayed.
 * 
 * @author asimon
 * @param resource &$dbHandler reference
 * @param object &$argsObj reference contains user input arguments
 * @param tlTestplan &$tplanMgr reference
 * @return initialize HTML-Select for filter methods
 */
function initFilterBuildInfo(&$dbHandler,&$argsObj,&$tplanMgr)
{
    $htmlSelect = array('items' => null, 'selected' => null);
    $htmlSelect['items'] = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id,
    											testplan::GET_ACTIVE_BUILD);
   
    $maxBuildID = $tplanMgr->get_max_build_id($argsObj->tplan_id, testplan::GET_ACTIVE_BUILD);

    $argsObj->optFilterBuildSelected = $argsObj->optFilterBuildSelected > 0 ? $argsObj->optFilterBuildSelected : $maxBuildID;
    if (!$argsObj->optFilterBuildSelected && sizeof($htmlSelect['items']))
    {
    	$argsObj->optFilterBuildSelected = key($htmlSelect['items']);
    }
    $htmlSelect['selected'] = $argsObj->optFilterBuildSelected;
    
    return $htmlSelect;
}


/**
 * creates a map with platform information, useful to create on user
 * interface an HTML select input.
 * 
 * @param resource &$dbHandler reference
 * @param object &$argsObj reference contains user input
 * @param tlPlatform &$platformMgr reference
 *
 */
function initPlatformInfo(&$dbHandler,&$argsObj,&$platformMgr)
{
    $htmlSelect = array('items' => null, 'selected' => null);
    $htmlSelect['items'] = $platformMgr->getLinkedToTestplanAsMap($argsObj->tplan_id);
    if( !is_null($htmlSelect['items']) && is_array($htmlSelect['items']) )
    { 
    	if (is_null($argsObj->optPlatformSelected)) 
    	{
    	    $argsObj->optPlatformSelected = key($htmlSelect['items']);
    	}
    	$htmlSelect['selected'] = $argsObj->optPlatformSelected;
    } 
    return $htmlSelect;
}


/*
  function: buildTree

  args :
  
  returns: 

*/
function buildTree(&$dbHandler,&$guiObj,&$argsObj,&$cfgObj,&$exec_cfield_mgr)
{
    $filters = new stdClass();
    $additionalInfo = new stdClass();
    
    $filters->keyword = buildKeywordsFilter($argsObj->keyword_id,$guiObj);
    $filters->keywordsFilterType = $argsObj->keywordsFilterType;
    $filters->include_unassigned = $guiObj->include_unassigned;
    
    $filters->tc_id = $argsObj->tcase_id;	
    $filters->build_id = $argsObj->optBuildSelected;
    $filters->filter_build_id = $argsObj->optFilterBuildSelected;
   
    // BUGID 2455
    $filters->method = $argsObj->filter_method_selected;
   
    // in this way we have code as key
    $filters->assignedTo = $guiObj->filter_assigned_to;
    if( !is_null($filters->assignedTo) )
    {
        if( in_array(TL_USER_ANYBODY, $guiObj->filter_assigned_to) )
        {
            $filters->assignedTo = null;
        }
        else
        {
            $dummy = array_flip($guiObj->filter_assigned_to);
            foreach( $dummy as $key => $value)
            {
                $dummy[$key] = $key;  
            }
            $filters->assignedTo = $dummy;
        }
    }
    
    $filters->filter_status = null;
    if( !is_null($argsObj->optResultSelected) )
    {
        if( !in_array($cfgObj->results['status_code']['all'], $argsObj->optResultSelected) )
        {
            // in this way we have code as key
            $dummy = array_flip($argsObj->optResultSelected);
            foreach( $dummy as $status_code => $value)
            {
                $dummy[$status_code] = $status_code;  
            }
            $filters->filter_status = $dummy;
        }
    }
   
    
    
    $filters->hide_testcases = false;
    $filters->show_testsuite_contents = $cfgObj->exec->show_testsuite_contents;
    $filters->urgencyImportance = $argsObj->urgencyImportance;
    $filters->platform_id = $argsObj->optPlatformSelected;
    
    $filters->cf_hash = $exec_cfield_mgr->get_set_values();
    $guiObj->args = initializeGetArguments($argsObj,$cfgObj,$filters->cf_hash);
    
    $additionalInfo->useCounters = $cfgObj->exec->enable_tree_testcase_counters;
    
    $additionalInfo->useColours = new stdClass();
    $additionalInfo->useColours->testcases = $cfgObj->exec->enable_tree_testcases_colouring;
    $additionalInfo->useColours->counters = $cfgObj->exec->enable_tree_counters_colouring;

    // link to load frame named 'workframe' when the update button is pressed
    if($argsObj->doUpdateTree)
    {
	     $guiObj->src_workframe = $_SESSION['basehref']. $guiObj->menuUrl . 
	                              "?level=testproject&id={$argsObj->tproject_id}" . $guiObj->args;
    }
       
    $treeMenu = generateExecTree($dbHandler,$guiObj->menuUrl,
                                 $argsObj->tproject_id,$argsObj->tproject_name,
                                 $argsObj->tplan_id,$argsObj->tplan_name,
                                 $guiObj->args,$filters,$additionalInfo);

    // $xx = json_decode($treeMenu->menustring);
 	return $treeMenu;
}


/*
  function: initializeGui
  args :
  returns: 

  rev: 20080429 - franciscom
*/
function initializeGui(&$dbHandler,&$argsObj,&$cfgObj,&$exec_cfield_mgr,&$tplanMgr,&$platformMgr)
{
    $gui = new stdClass();
    $gui_open = config_get('gui_separator_open');
    $gui_close = config_get('gui_separator_close');
    
    $gui->str_option_any = $gui_open . lang_get('any') . $gui_close;
    $gui->str_option_none = $gui_open . lang_get('nobody') . $gui_close;
    $gui->str_option_somebody = $gui_open . lang_get('filter_somebody') . $gui_close;
        
    $gui->design_time_cfields = $exec_cfield_mgr->html_table_of_custom_field_inputs(30);
    $gui->menuUrl = 'lib/execute/execSetResults.php';
    $gui->src_workframe = null;    
    $gui->getArguments = null;
    
    $gui->treeColored = $argsObj->treeColored;
    
    $tplans = $_SESSION['currentUser']->getAccessibleTestPlans($dbHandler,$argsObj->tproject_id);
    
    $gui->map_tplans = array();
    foreach($tplans as $key => $value)
    {
    	//dont take testplans into selection which have no builds assigned
    	$items = $tplanMgr->get_builds($value['id'],
    							testplan::GET_ACTIVE_BUILD, testplan::GET_OPEN_BUILD);
		if (is_array($items) && count($items)) {
    		$gui->map_tplans[$value['id']] = $value['name'];
    	}    	
    }
    
    $gui->tplan_id = $argsObj->tplan_id;
    $gui->tplan_name = $argsObj->tplan_name;
    
    $gui->optResultSelected = $argsObj->optResultSelected;
    $gui->include_unassigned = $argsObj->include_unassigned;
    $gui->urgencyImportance = $argsObj->urgencyImportance;
    
    $gui->optBuild = initBuildInfo($dbHandler,$argsObj,$tplanMgr);
    $gui->optFilterBuild = initFilterBuildInfo($dbHandler,$argsObj,$tplanMgr);
    $gui->optPlatform = initPlatformInfo($dbHandler,$argsObj,$platformMgr);
    
    // count of open builds that can be executed
    $gui->buildCount = count($gui->optBuild['items']);
    
    // count of active builds that are shown and can be filtered
    $gui->filterBuildCount = count($gui->optFilterBuild['items']);
    
    $gui->keywordsFilterType = new stdClass();
    $gui->keywordsFilterType->options = array('OR' => 'Or' , 'AND' =>'And'); 
    $gui->keywordsFilterType->selected=$argsObj->keywordsFilterType;
    $gui->keywordsFilterItemQty = 0;

    $gui->keyword_id = $argsObj->keyword_id; 
    $gui->keywords_map = $tplanMgr->get_keywords_map($argsObj->tplan_id,' order by keyword ');
    if(!is_null($gui->keywords_map))
    {
        $gui->keywords_map = array( 0 => $gui->str_option_any) + $gui->keywords_map;
        $gui->keywordsFilterItemQty = min(count($gui->keywords_map),3);
    }
    
    // 20090517 - francisco.mancardi@gruppotesi.com
    // Assigned to combo must contain ALSO inactive users
    $users = tlUser::getAll($dbHandler,null,"id",null);
	$gui->users = getTestersForHtmlOptions($dbHandler,$argsObj->tplan_id,$argsObj->tproject_id,
	                                       $users,array(TL_USER_ANYBODY => $gui->str_option_any,
	                                       TL_USER_NOBODY => $gui->str_option_none,
	                                       TL_USER_SOMEBODY => $gui->str_option_somebody),'any' );

    $gui->tcase_id=intval($argsObj->tcase_id) > 0 ? $argsObj->tcase_id : '';
    
    $gui->optResult=createResultsMenu();
    $gui->optResult[$cfgObj->results['status_code']['all']] = $gui->str_option_any;

    // BUGID 2455, BUGID 3026
	$filter_cfg = config_get('execution_filter_methods');
    $gui->filter_methods = createExecutionFilterMethodMenu();
	$gui->optFilterMethodSelected = $argsObj->filter_method_selected;
	$gui->filter_method_specific_build = $filter_cfg['status_code']['specific_build'];
	$gui->filter_method_current_build = $filter_cfg['status_code']['current_build'];

    $gui->advancedFilterMode=$argsObj->advancedFilterMode;
    if($gui->advancedFilterMode)
    {
        $label = 'btn_simple_filters';
        $qty = 4; // Standard: not run,passed,failed,blocked
    }
    else
    {
        $label = 'btn_advanced_filters';
        $qty = 1;
    }
    
   	$gui->statusFilterItemQty = $qty;   
    $gui->assigneeFilterItemQty = $qty;
    $gui->toggleFilterModeLabel=lang_get($label);
 
    return $gui;
}

/**
 * create map with filter methods for execution,
 * used for creating HTML Select inputs
 * 
 * @author asimon
 * @return $menu_data HTML Select (labels and values) 
 */
function createExecutionFilterMethodMenu() {
	$filter_cfg = config_get('execution_filter_methods');
	$menu_data = array();
	foreach($filter_cfg['status_code'] as $status => $label) {
		$code = $filter_cfg['status_code'][$status];
		$menu_data[$code] = lang_get($filter_cfg['status_label'][$status]);
	}
	return $menu_data;
}


?>
