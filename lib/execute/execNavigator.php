<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: execNavigator.php,v $
 *
 * @version $Revision: 1.63 $
 * @modified $Date: 2008/05/17 14:20:35 $ by $Author: franciscom $
 *
 * rev: 
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

$cfg=getCfg();
$args = init_args($db,$cfg);
$exec_cfield_mgr = new exec_cfield_mgr($db,$args->tproject_id);

$gui = initializeGui($db,$args,$exec_cfield_mgr,$tplan_mgr);
buildAssigneeFilter($db,$gui,$args,$cfg);
$gui->tree=buildTree($db,$gui,$args,$cfg,$exec_cfield_mgr);                                                

                      
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);

// Warning: 
// the following variable names CAN NOT BE Changed,
// because there is global coupling on template logic
//
$smarty->assign('treeKind',$gui->treeKind);
$smarty->assign('menuUrl',$gui->menuUrl);
$smarty->assign('args',$gui->args);

$smarty->assign('SP_html_help_file',TL_INSTRUCTIONS_RPATH . $_SESSION['locale'] . "/executeTest.html");
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function:

  args:

  returns:

  schlundus: changed the user_id to the currentUser of the session
*/
function init_args(&$dbHandler,$cfgObj)
{
    $args = new stdClass();

    $args->tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    $args->user = $_SESSION['currentUser'];
    $args->tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
    $args->tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : 'null';
    $args->treeColored = (isset($_REQUEST['colored']) && ($_REQUEST['colored'] == 'result')) ? 'selected="selected"' : null;
    $args->tcase_id = isset($_REQUEST['tcase_id']) ? intval($_REQUEST['tcase_id']) : null;
    
    
    // 20080517 - franciscom
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
    
    $args->optResultSelected = isset($_REQUEST['filter_status']) ? $_REQUEST['filter_status'] : null;
    if ($args->optResultSelected == $cfgObj->results['status_code']['all'])
    {
	     $args->optResultSelected = null;
    }

    $user_filter_default = 0;
    switch($cfgObj->exec->user_filter_default)
    {
    	case 'logged_user':
    		$user_filter_default = $args->user->dbID;
    		break;

    	case 'none':
    	default:
    		break;
    }
    $args->filter_assigned_to = isset($_REQUEST['filter_assigned_to']) ? intval($_REQUEST['filter_assigned_to']) : $user_filter_default;
    $args->optBuildSelected = isset($_POST['build_id']) ? $_POST['build_id'] : -1;

    // Checkbox
    $args->include_unassigned=isset($_REQUEST['include_unassigned']) ? $_REQUEST['include_unassigned'] : 0;

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
        $settings .= '&tc_id='.$argsObj->tcase_id;

    if($argsObj->filter_assigned_to)
    	  $settings .= '&filter_assigned_to='.$argsObj->filter_assigned_to;
    
    if($argsObj->optResultSelected != $cfgObj->results['status_code']['all'])
        $settings .= '&filter_status='.$argsObj->optResultSelected;
    

    if ($customFieldSelected)
    	 $settings .= '&cfields='. serialize($customFieldSelected);

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
    return $cfg;
}



/*
  function: 

  args :
  
  returns: 

*/
function buildAssigneeFilter(&$dbHandler,&$guiObj,&$argsObj,$cfgObj)
{
    $guiObj->disable_filter_assigned_to = false;
    $guiObj->assigned_to_user = '';
    
    $effective_role = $argsObj->user->getEffectiveRole($dbHandler,$argsObj->tproject_id,$argsObj->tplan_id);
    
    // SCHLUNDUS: hmm, for user defined roles, this wont work correctly
    // Need to check right no role
    $exec_view_mode = ($effective_role->dbID == TL_ROLES_TESTER) ? $cfgObj->exec->view_mode->tester : 'all';
    
    switch ($exec_view_mode)
    {
    	case 'all':
 		    $guiObj->filter_assigned_to = $argsObj->filter_assigned_to;
    		break;
    
    	case 'assigned_to_me':
    		$guiObj->disable_filter_assigned_to = true;
    		$argsObj->filter_assigned_to = $argsObj->user->dbID;
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
    $filters->assignedTo = $guiObj->filter_assigned_to;
    $filters->status = $guiObj->optResultSelected;
    
    $filters->hide_testcases = false;
    $filters->show_testsuite_contents = $cfgObj->exec->show_testsuite_contents;
     
    $filters->cf_hash = $exec_cfield_mgr->get_set_values();
    $guiObj->args=initializeGetArguments($argsObj,$cfgObj,$filters->cf_hash);
    
    //
    $additionalInfo->useCounters=$cfgObj->exec->enable_tree_testcase_counters;
    $additionalInfo->useColors=$cfgObj->exec->enable_tree_colouring;


    // link to load frame named 'workframe' when the update button is pressed
    if($argsObj->doUpdateTree)
    {
	     $guiObj->src_workframe = $_SESSION['basehref']. $guiObj->menuUrl . 
	                              "?level=testproject&id={$argsObj->tproject_id}" . $guiObj->args;
    }
       
    $treeString = generateExecTree($dbHandler,$guiObj->menuUrl,
                                   $argsObj->tproject_id,$argsObj->tproject_name,
                                   $argsObj->tplan_id,$argsObj->tplan_name,
                                   $guiObj->args,$filters,$additionalInfo);
   
    return (invokeMenu($treeString,null,null));
}


/*
  function: initializeGui

  args :
  
  returns: 

  rev: 20080429 - franciscom
*/
function initializeGui(&$dbHandler,&$argsObj,&$exec_cfield_mgr,&$tplanMgr)
{
    $gui = new stdClass();
    $gui->design_time_cfields = $exec_cfield_mgr->html_table_of_custom_field_inputs();
    $gui->menuUrl = 'lib/execute/execSetResults.php';
    $gui->src_workframe=null;    
    $gui->getArguments=null;
    
    $gui->treeColored=$argsObj->treeColored;
    $gui->tplan_name=$argsObj->tplan_name;
    $gui->tplan_id=$argsObj->tplan_id;
    $gui->keyword_id=$argsObj->keyword_id;
    $gui->optResultSelected=$argsObj->optResultSelected;
    $gui->include_unassigned=$argsObj->include_unassigned;
    
    $gui->targetTestCase=$argsObj->targetTestCase;
    
    
    // Only active builds no matter user role
    $gui->optBuild = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id,ACTIVE);
    $gui->optBuildSelected=initBuildInfo($dbHandler,$guiObj,$argsObj,$tplanMgr); 
       
    $gui->keywordsFilterItemQty=0;
    $gui->keywords_map=initKeywordInfo($argsObj->tplan_id,$tplanMgr);
    
    if( !is_null($gui->keywords_map) )
    {
        $gui->keywordsFilterItemQty=min(count($gui->keywords_map),3);
    }

    $gui->users = getUsersForHtmlOptions($dbHandler,null,true);
    $gui->tcase_id=intval($argsObj->tcase_id) > 0 ? $argsObj->tcase_id : '';
    $gui->treeKind=TL_TREE_KIND;
    $gui->optResult=createResultsMenu();
 
    return $gui;
}

?>
