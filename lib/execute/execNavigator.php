<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: execNavigator.php,v $
 *
 * @version $Revision: 1.74 $
 * @modified $Date: 2008/12/30 13:34:49 $ by $Author: franciscom $
 *
 * rev: 
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
 *      20071229 - franciscom - refactoring tree colouring and counters config
 *      20071006 - franciscom - changes on exec_cfield_mgr() call
 *      20070912 - jbarchibald - custom field search BUGID - 1051
 *      20070630 - franciscom - set default value for filter_assigned_to
 *      20070607 - franciscom - BUGID 887 - problem with builds
 **/
require_once('../../config.inc.php');
require_once('common.php');
require_once("users.inc.php");
require_once('treeMenu.inc.php');
require_once('exec.inc.php');
require_once('builds.inc.php');
testlinkInitPage($db);

$tplan_mgr = new testplan($db);

$templateCfg = templateConfiguration();


$cfg = getCfg();
$args = init_args($db,$cfg);
$exec_cfield_mgr = new exec_cfield_mgr($db,$args->tproject_id);
$gui = initializeGui($db,$args,$cfg,$exec_cfield_mgr,$tplan_mgr);

buildAssigneeFilter($db,$gui,$args,$cfg);

$treeMenu = buildTree($db,$gui,$args,$cfg,$exec_cfield_mgr);
$gui->tree=$treeMenu->menustring;

if( !is_null($treeMenu->rootnode) )
{
    $gui->ajaxTree=new stdClass();
    $gui->ajaxTree->loader='';
    $gui->ajaxTree->root_node=new stdClass();
    $gui->ajaxTree->root_node=$treeMenu->rootnode;
    $gui->ajaxTree->children=$treeMenu->menustring;
    $gui->ajaxTree->cookiePrefix='exec_tplan_id_' . $args->tplan_id;
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
// Warning: the following variable names CAN NOT BE Changed,
// because there is global coupling on template logic
$smarty->assign('treeKind',$gui->treeKind);
$smarty->assign('menuUrl',$gui->menuUrl);
$smarty->assign('args',$gui->args);

$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function:
  args:
  returns:

  schlundus: changed the user_id to the currentUser of the session
*/
function init_args(&$dbHandler,$cfgObj)
{
  	$_REQUEST = strings_stripSlashes($_REQUEST);
    $args = new stdClass();
    
    $args->tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    $args->user = $_SESSION['currentUser'];
    $args->tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
    $args->tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : 'null';
    $args->treeColored = (isset($_REQUEST['colored']) && ($_REQUEST['colored'] == 'result')) ? 'selected="selected"' : null;
    $args->tcase_id = isset($_REQUEST['tcase_id']) ? intval($_REQUEST['tcase_id']) : null;


    $args->advancedFilterMode=isset($_REQUEST['advancedFilterMode']) ? $_REQUEST['advancedFilterMode'] : 0;
    $args->targetTestCase = isset($_REQUEST['targetTestCase']) ? $_REQUEST['targetTestCase'] : null;
 	  if(!is_null($args->targetTestCase) && !empty($args->targetTestCase))
	  {
	  	// need to get internal Id from External ID
	  	$item_mgr = new testcase($dbHandler);
	  	$cfg = config_get('testcase_cfg');
	  	$args->tcase_id=$item_mgr->getInternalID($args->targetTestCase,$cfg->glue_character);
	  	
	  	if( $args->tcase_id == 0 )
	  	{
	  	    $args->tcase_id=-1;  
	  	}
	  }

    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
    $args->doUpdateTree=isset($_REQUEST['submitOptions']) ? 1 : 0;
    
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
        if( in_array(TL_USER_ANYBODY, $args->filter_assigned_to) )
        {
            $args->filter_assigned_to = array(TL_USER_ANYBODY);  
        }
        else if( in_array(TL_USER_NOBODY, $args->filter_assigned_to) )
        {
            $args->filter_assigned_to = array(TL_USER_NOBODY);    
        } 
        else if( !$args->advancedFilterMode && count($args->filter_assigned_to) > 0)
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
    $args->optBuildSelected = isset($_POST['build_id']) ? $_POST['build_id'] : -1;

    // Checkbox
    $args->include_unassigned=isset($_REQUEST['include_unassigned']) ? $_REQUEST['include_unassigned'] : 0;

    // 20081225 - franciscom 
    $keyname="resultAllPrevBuildsFilterType";
    $args->resultAllPrevBuildsFilterType=isset($_REQUEST[$keyname]) ? $_REQUEST[$keyname] : 'IN';

    
    // 20081227 - BUGID 1913
    $key='resultAllPrevBuildsSelected';
    $args->$key = isset($_REQUEST['filter_status_all_prev_builds']) ? 
                  (array)$_REQUEST['filter_status_all_prev_builds'] : null;
    if( !is_null($args->$key) )
    {
        if( in_array($cfgObj->results['status_code']['all'], $args->$key) )
        {
            $args->$key = array($cfgObj->results['status_code']['all']);
        }
        else if( !$args->advancedFilterMode && count($args->$key) > 0)
        {
            // Because user has switched to simple mode we will get ONLY first status
            $dummy=$args->$key;
            $args->$key=array($dummy[0]);
        }
    }
    return $args;
}


/*
  function: initializeGetArguments
            build arguments that will be passed to execSetResults.php
            with a http call

  args:

  returns:

  rev: 20080427 - franciscom - added cfgObj arguments
       20080224 - franciscom - added include_unassigned

*/
function initializeGetArguments($argsObj,$cfgObj,$customFieldSelected)
{
    $kl='';
    $settings = '&build_id=' . $argsObj->optBuildSelected .
  	            '&include_unassigned=' . $argsObj->include_unassigned;

    // 20080428 - franciscom  
    if( is_array($argsObj->keyword_id) )
    {
       $kl=implode(',',$argsObj->keyword_id);
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
        
    // 20081220 - franciscom
    if( !is_null($argsObj->filter_assigned_to) &&
        !in_array(TL_USER_ANYBODY,$argsObj->filter_assigned_to) )
    {
    	  $settings .= '&filter_assigned_to='. serialize($argsObj->filter_assigned_to);
    }   
       
       
    // 20081220 - franciscom
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
    $cfg=new stdClass();
    $cfg->gui = config_get('gui');
    $cfg->exec = config_get('exec_cfg');
    $cfg->results = config_get('results');
    $cfg->treemenu_type = config_get('treemenu_type');
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
    
    // SCHLUNDUS: hmm, for user defined roles, this wont work correctly
    // Need to check right no role
    //
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
    $simple_tester_roles=array_flip($cfgObj->exec->simple_tester_roles);
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
function initBuildInfo(&$dbHandler,&$guiObj,&$argsObj,&$tplanMgr)
{
    // 20070607 - franciscom - BUGID 887
    $maxBuildID = $tplanMgr->get_max_build_id($argsObj->tplan_id,
                                              testplan::GET_ACTIVE_BUILD, testplan::GET_OPEN_BUILD);
    $argsObj->optBuildSelected = $argsObj->optBuildSelected > 0 ? $argsObj->optBuildSelected : $maxBuildID;
    if (!$argsObj->optBuildSelected && sizeof($guiObj->optBuild))
    {
    	$argsObj->optBuildSelected = key($guiObj->optBuild);
    }
    
    return $argsObj->optBuildSelected;
}




/*
  function: initKeywordInfo

  args :
  
  returns: 

*/
function initKeywordInfo($tplanID,&$tplanMgr)
{
    $kmap = $tplanMgr->get_keywords_map($tplanID,' order by keyword ');
    if(!is_null($kmap))
    {
       
    	// add the blank option
    	// 0 -> id for no keyword
    	//$blank_map[0] = '';
    	//$kmap = $blank_map + $kmap;
    }
    return $kmap;
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

    $filters->keyword_id = $guiObj->keyword_id;
    $filters->keywordsFilterType='OR';
    
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
    
    
    $filters->hide_testcases = false;
    $filters->show_testsuite_contents = $cfgObj->exec->show_testsuite_contents;
    $filters->urgencyImportance = $argsObj->urgencyImportance;
    
    $filters->cf_hash = $exec_cfield_mgr->get_set_values();
    $guiObj->args = initializeGetArguments($argsObj,$cfgObj,$filters->cf_hash);
    
    $additionalInfo->useCounters = $cfgObj->exec->enable_tree_testcase_counters;
    $additionalInfo->useColours = $cfgObj->exec->enable_tree_colouring;

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

 	  if( $cfgObj->treemenu_type != 'EXTJS' )
    {
        $treeMenu->menustring = invokeMenu($treeMenu->menustring,null,null);
    }

    return $treeMenu;
}


/*
  function: initializeGui
  args :
  returns: 

  rev: 20080429 - franciscom
*/
function initializeGui(&$dbHandler,&$argsObj,&$cfgObj,&$exec_cfield_mgr,&$tplanMgr)
{
    $gui = new stdClass();
    $gui_open = config_get('gui_separator_open');
    $gui_close = config_get('gui_separator_close');
    
    $gui->str_option_any = $gui_open . lang_get('any') . $gui_close;
    $gui->str_option_none = $gui_open . lang_get('nobody') . $gui_close;
        
    $gui->design_time_cfields = $exec_cfield_mgr->html_table_of_custom_field_inputs();
    $gui->menuUrl = 'lib/execute/execSetResults.php';
    $gui->src_workframe=null;    
    $gui->getArguments=null;
    
    $gui->treeColored=$argsObj->treeColored;
    $gui->tplan_name=$argsObj->tplan_name;
    $gui->tplan_id=$argsObj->tplan_id;
    $gui->keyword_id=$argsObj->keyword_id;
    $gui->optResultSelected = $argsObj->optResultSelected;
    $gui->include_unassigned=$argsObj->include_unassigned;
    $gui->urgencyImportance = $argsObj->urgencyImportance;
    $gui->targetTestCase=$argsObj->targetTestCase;
    $gui->resultAllPrevBuildsSelected = $argsObj->resultAllPrevBuildsSelected;
    
    // Only active builds no matter user role
    $gui->optBuild = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id,ACTIVE);
    $gui->optBuildSelected=initBuildInfo($dbHandler,$guiObj,$argsObj,$tplanMgr); 
       
    $gui->keywordsFilterItemQty=0;
    $gui->keywords_map=initKeywordInfo($argsObj->tplan_id,$tplanMgr);
    
    if( !is_null($gui->keywords_map) )
    {
        $gui->keywordsFilterItemQty=min(count($gui->keywords_map),3);
    }
                 
    // 20081217 - franciscom             
    // $gui->users = getUsersForHtmlOptions($dbHandler,null,true);
    $users = tlUser::getAll($dbHandler,null,"id",null,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
	  $gui->users = getTestersForHtmlOptions($dbHandler,$argsObj->tplan_id,$argsObj->tproject_id,
	                                         $users, 
	                                         array(TL_USER_ANYBODY => $gui->str_option_any,
	                                               TL_USER_NOBODY => $gui->str_option_none) );


    $gui->tcase_id=intval($argsObj->tcase_id) > 0 ? $argsObj->tcase_id : '';
    $gui->treeKind=TL_TREE_KIND;
    
    $gui->optResult=createResultsMenu();
    $gui->optResult[$cfgObj->results['status_code']['all']] = $gui->str_option_any;

    $gui->resultAllPrevBuilds=$gui->optResult;
    $gui->resultAllPrevBuilds[$cfgObj->results['status_code']['all']] = $gui->str_option_any;


    $gui->advancedFilterMode=$argsObj->advancedFilterMode;
    if( $gui->advancedFilterMode )
    {
        $label = 'btn_simple_filters';
        $gui->statusFilterItemQty=4;  // Standard: not run,passed,failed,blocked
        $gui->assigneeFilterItemQty=4; // as good as any other number
    }
    else
    {
        $label='btn_advanced_filters';
        $gui->statusFilterItemQty=1;   
        $gui->assigneeFilterItemQty=1;
    }
    $gui->toggleFilterModeLabel=lang_get($label);
 
 
    // BUGID 1913
    $gui->resultAllPrevBuildsFilterType=new stdClass();                                 
    $gui->resultAllPrevBuildsFilterType->options = array('IN' => 'In' , 'OUT' =>'Out'); 
    $gui->resultAllPrevBuildsFilterType->selected=$argsObj->resultAllPrevBuildsFilterType;         

    return $gui;
}

?>
