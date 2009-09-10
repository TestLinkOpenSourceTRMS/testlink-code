<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: execNavigator.php,v $
 *
 * @version $Revision: 1.94 $
 * @modified $Date: 2009/09/10 09:12:58 $ by $Author: havlat $
 *
 * rev: 
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
$args = init_args($db,$cfg);

$tplan_mgr = new testplan($db);
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
function init_args(&$dbHandler,$cfgObj)
{
  	$_REQUEST = strings_stripSlashes($_REQUEST);
    $args = new stdClass();
    
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    $args->user = $_SESSION['currentUser'];
    $args->tplan_id = isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : 0;
    $args->tplan_name = isset($_SESSION['testplanName']) ? $_SESSION['testplanName'] : 'null';

    $args->treeColored = (isset($_REQUEST['colored']) && ($_REQUEST['colored'] == 'result')) ? 'selected="selected"' : null;
    $args->tcase_id = isset($_REQUEST['tcase_id']) ? intval($_REQUEST['tcase_id']) : null;
    $args->advancedFilterMode = isset($_REQUEST['advancedFilterMode']) ? $_REQUEST['advancedFilterMode'] : 0;
    $args->targetTestCase = isset($_REQUEST['targetTestCase']) ? $_REQUEST['targetTestCase'] : null;

    if(!is_null($args->targetTestCase) && !empty($args->targetTestCase))
	{
	  	// need to get internal Id from External ID
	  	$item_mgr = new testcase($dbHandler);
	  	$cfg = config_get('testcase_cfg');
	  	$args->tcase_id = $item_mgr->getInternalID($args->targetTestCase,$cfg->glue_character);
	  	
	  	if($args->tcase_id == 0)
	  	{
	  	    $args->tcase_id = -1;  
	  	}
	}

    // Attention: Is an array because is a multiselect 
    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;

    // not fully implemented yet
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
    $args->optBuildSelected = isset($_REQUEST['build_id']) ? $_REQUEST['build_id'] : -1;
    $args->optPlatformSelected = isset($_REQUEST['platform_id']) ? $_REQUEST['platform_id'] : null;

    $args->include_unassigned = isset($_REQUEST['include_unassigned']) ? $_REQUEST['include_unassigned'] : 0;

    $keyname = "resultAllPrevBuildsFilterType";
    $args->resultAllPrevBuildsFilterType = isset($_REQUEST[$keyname]) ? $_REQUEST[$keyname] : 'IN';

    
    // 20081227 - BUGID 1913
    $key = 'resultAllPrevBuildsSelected';
    $args->$key = isset($_REQUEST['filter_status_all_prev_builds']) ? 
                  (array)$_REQUEST['filter_status_all_prev_builds'] : null;
    if(!is_null($args->$key))
    {
        if(in_array($cfgObj->results['status_code']['all'], $args->$key))
        {
            $args->$key = array($cfgObj->results['status_code']['all']);
        }
        else if(!$args->advancedFilterMode && count($args->$key) > 0)
        {
            // Because user has switched to simple mode we will get ONLY first status
            $dummy = $args->$key;
            $args->$key = array($dummy[0]);
        }
    }
    
    // 20090716 - franciscom - BUGID 2692
    $keyPrefix = 'statusAnyOfPrevBuilds';
    $key = $keyPrefix . 'Selected';
    $args->$key = isset($_REQUEST[$keyPrefix]) ? (array)$_REQUEST[$keyPrefix] : null;
    if(!is_null($args->$key))
    {
        if(in_array($cfgObj->results['status_code']['all'], $args->$key))
        {
            $args->$key = array($cfgObj->results['status_code']['all']);
        }
        else if(!$args->advancedFilterMode && count($args->$key) > 0)
        {
            // Because user has switched to simple mode we will get ONLY first status
            $dummy = $args->$key;
            $args->$key = array($dummy[0]);
        }
    }
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

    if(is_array($argsObj->keyword_id))
    {
       $kl = implode(',',$argsObj->keyword_id);
       $settings .= '&keyword_id=' . $kl;
    }
    else if($argsObj->keyword_id > 0)
    {
    	  $settings .= '&keyword_id='.$argsObj->keyword_id;
    }
    
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

    // BUGID 1913
    if( !is_null($argsObj->resultAllPrevBuildsSelected) && 
        !in_array($cfgObj->results['status_code']['all'],$argsObj->resultAllPrevBuildsSelected) )
    {
        $settings .= '&filter_status_all_prev_builds='. serialize($argsObj->resultAllPrevBuildsSelected);
    }

    // BUGID 2692
    $key='statusAnyOfPrevBuildsSelected';
    if( !is_null($argsObj->$key) && 
        !in_array($cfgObj->results['status_code']['all'],$argsObj->$key) )
    {
        $settings .= '&statusAnyOfPrevBuilds='. serialize($argsObj->$key);
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


/*
  function: initBuildInfo
            only active builds no matter user role

  args :
  
  returns: 

*/
function initBuildInfo(&$dbHandler,&$argsObj,&$tplanMgr)
{
    $htmlSelect = array('items' => null, 'selected' => null);
    $htmlSelect['items'] = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id,ACTIVE);
   
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
    $filters->include_unassigned = $guiObj->include_unassigned;
    
    $filters->tc_id = $argsObj->tcase_id;
    $filters->build_id = $argsObj->optBuildSelected;
   
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
    
    $filters->status = $guiObj->optResultSelected;
    if( !is_null($filters->status) )
    {
        if( in_array($cfgObj->results['status_code']['all'], $guiObj->optResultSelected) )
        {
            $filters->status = null;  
        }
        else 
        {
            // in this way we have code as key
            $dummy = array_flip($guiObj->optResultSelected);
            foreach( $dummy as $status_code => $value)
            {
                $dummy[$status_code] = $status_code;  
            }
            $filters->status = $dummy;
        }
    }
    
    // BUGID 1913
    $filters->statusAllPrevBuilds = $guiObj->resultAllPrevBuildsSelected;
    if( !is_null($filters->statusAllPrevBuilds) )
    {
        if( in_array($cfgObj->results['status_code']['all'], $guiObj->resultAllPrevBuildsSelected) )
        {
            $filters->statusAllPrevBuilds = null;  
        }
        else 
        {
            // in this way we have code as key
            $dummy = array_flip($guiObj->resultAllPrevBuildsSelected);
            foreach( $dummy as $status_code => $value)
            {
                $dummy[$status_code] = $status_code;  
            }
            $filters->statusAllPrevBuilds = $dummy;
        }
    }
    $filters->statusAllPrevBuildsFilterType = $guiObj->resultAllPrevBuildsFilterType->selected;
    
    // BUGID 2692
    $filterKey = 'statusAnyOfPrevBuilds';
    $guiKey = $filterKey .'Selected';
    $filters->$filterKey = $guiObj->$guiKey;
    if( !is_null($filters->$filterKey) )
    {
        if( in_array($cfgObj->results['status_code']['all'], $guiObj->$guiKey) )
        {
            $filters->$filterKey = null;  
        }
        else 
        {
            // in this way we have code as key
            $dummy = array_flip($guiObj->$guiKey);
            foreach( $dummy as $status_code => $value)
            {
                $dummy[$status_code] = $status_code;  
            }
            $filters->$filterKey = $dummy;
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
        
    $gui->design_time_cfields = $exec_cfield_mgr->html_table_of_custom_field_inputs(30);
    $gui->menuUrl = 'lib/execute/execSetResults.php';
    $gui->src_workframe = null;    
    $gui->getArguments = null;
    
    $gui->treeColored = $argsObj->treeColored;
    $gui->tplan_name = $argsObj->tplan_name;
    $gui->tplan_id = $argsObj->tplan_id;
    $gui->optResultSelected = $argsObj->optResultSelected;
    $gui->include_unassigned = $argsObj->include_unassigned;
    $gui->urgencyImportance = $argsObj->urgencyImportance;
    $gui->targetTestCase = $argsObj->targetTestCase;
    
    // Only active builds no matter user role
    $gui->optBuild = initBuildInfo($dbHandler,$argsObj,$tplanMgr);    
    $gui->optPlatform = initPlatformInfo($dbHandler,$argsObj,$platformMgr);    
       
    $gui->keywordsFilterType = new stdClass();
    $gui->keywordsFilterType->options = array('OR' => 'Or' , 'AND' =>'And'); 
    $gui->keywordsFilterType->selected=$argsObj->keywordsFilterType;
    $gui->keywordsFilterItemQty = 0;

    $gui->keyword_id = $argsObj->keyword_id; 
    $gui->keywords_map = $tplanMgr->get_keywords_map($argsObj->tplan_id,' order by keyword ');
    if(!is_null($gui->keywords_map))
    {
        $gui->keywordsFilterItemQty = min(count($gui->keywords_map),3);
        $gui->keywords_map = array( 0 => $gui->str_option_any) + $gui->keywords_map;
    }
    
    // 20090517 - francisco.mancardi@gruppotesi.com
    // Assigned to combo must contain ALSO inactive users
    $users = tlUser::getAll($dbHandler,null,"id",null);
	$gui->users = getTestersForHtmlOptions($dbHandler,$argsObj->tplan_id,$argsObj->tproject_id,
	                                       $users,array(TL_USER_ANYBODY => $gui->str_option_any,
	                                       TL_USER_NOBODY => $gui->str_option_none),'any' );


    $gui->tcase_id=intval($argsObj->tcase_id) > 0 ? $argsObj->tcase_id : '';
    
    $gui->optResult=createResultsMenu();
    $gui->optResult[$cfgObj->results['status_code']['all']] = $gui->str_option_any;

    $gui->resultAllPrevBuilds=$gui->optResult;
    $gui->resultAllPrevBuildsSelected = $argsObj->resultAllPrevBuildsSelected;

    // BUGID 2692 - 20090716 - franciscom
    $gui->statusAnyOfPrevBuilds=$gui->optResult;
    $gui->statusAnyOfPrevBuildsSelected = $argsObj->statusAnyOfPrevBuildsSelected;


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
 
 
    // BUGID 1913
    $gui->resultAllPrevBuildsFilterType=new stdClass();                                 
    $gui->resultAllPrevBuildsFilterType->options = array('IN' => lang_get('In') , 'OUT' => lang_get('Out')); 
    $gui->resultAllPrevBuildsFilterType->selected=$argsObj->resultAllPrevBuildsFilterType;         

    return $gui;
}

?>
