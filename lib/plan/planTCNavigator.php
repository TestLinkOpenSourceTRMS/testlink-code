<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Test navigator for Test Plan
 * 
 *
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: planTCNavigator.php,v 1.41 2010/03/02 10:18:00 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions:
 *	20100202 - asimon - BUGID 2455, BUGID 3026
 *  20081223 - franciscom - advanced/simple filter feature
 **/

require('../../config.inc.php');
require_once("common.php");
require_once("users.inc.php");
require_once("treeMenu.inc.php");
require_once('exec.inc.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$cfg = getCfg();

$tproject_mgr = new testproject($db);
$tplan_mgr = new testplan($db);
$args = init_args($db,$cfg,$tplan_mgr,$tproject_mgr);
$gui = initializeGui($db,$args,$cfg,$tplan_mgr);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);

//  
// Warning: the following variable names CAN NOT BE Changed,
// because there is global coupling on template logic
$smarty->assign('menuUrl',$gui->menuUrl);
$smarty->assign('args',$gui->args);
$smarty->assign('treeHeader', $gui->title);

$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/*
 * function: getCfg()
 * 
 * returns: configuration objects
 */
function getCfg()
{
    $cfg = new stdClass();
    $cfg->gui = config_get('gui');
    $cfg->exec = config_get('exec_cfg');
    $cfg->results = config_get('results');
    $cfg->testcase_cfg = config_get('testcase_cfg');
    return $cfg;
}


/**
 * initializes user inputs
 * 
 * @param resource $dbHandler database handle
 * @param stdClass $cfgObj configuration
 * @param tlTestplan $tplanMgr reference to testplan manager
 * @param tlTestproject $tprojectMgr reference to testproject manager
 * @return stdClass $args user input values
 */
function init_args(&$dbHandler,&$cfgObj,&$tplanMgr, &$tprojectMgr)
{
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args = new stdClass();

    $args->feature = $_REQUEST['feature'];
    switch($args->feature)
    {
      	case 'planUpdateTC':
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
    
	// 20070120 - franciscom -
    // is possible to call this page using a Test Project that have no test plans
    // in this situation the next to entries are undefined in SESSION
    $args->tplan_id = isset($_SESSION['testplanID']) ? intval($_SESSION['testplanID']) : 0;
    $args->tplan_name = isset($_SESSION['testplanName']) ? $_SESSION['testplanName'] : '';

    if($args->tplan_id != 0)
    {
		$args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID'];
		$tplan_info = $tplanMgr->get_by_id($args->tplan_id);
		$args->tplan_name = $tplan_info['name'];
    
    
		if($args->tplan_id != $_SESSION['testplanID']) {
	    	//testplan was changed, so we reset the filters, they were chosen for another testplan
	    	$keys2delete = array('keyword_id', 'filter_status', 'keywordsFilterType',
	    						'filter_method', 'filter_assigned_to', 'urgencyImportance',
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
    }
    
    // Array because is a multiselect input
    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
    $args->keywordsFilterType=isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';
    $args->help_topic = isset($_REQUEST['help_topic']) ? $_REQUEST['help_topic'] : $args->feature;

    $args->advancedFilterMode=isset($_REQUEST['advancedFilterMode']) ? $_REQUEST['advancedFilterMode'] : 0;

    if(isset($_REQUEST['doUpdateTree']) || isset($_REQUEST['called_by_me']))
    {
    	$args->src_workframe = $_SESSION['basehref'] .
                      "lib/general/staticPage.php?key={$args->help_topic}";
    }
    else
    {
    	$args->src_workframe = '';
    }

    $args->tcase_id = isset($_REQUEST['tcase_id']) ? intval($_REQUEST['tcase_id']) : null;
    $args->optResultSelected = isset($_REQUEST['filter_status']) ?
			                   (array)$_REQUEST['filter_status'] : array($cfgObj->results['status_code']['all']);
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
	
    $filter_cfg = config_get('execution_assignment_filter_methods');
    $args->filter_method_selected = isset($_REQUEST['filter_method']) ?
    							           (array)$_REQUEST['filter_method'] : (array)$filter_cfg['default_type'];
	  
	$args->optBuildSelected = isset($_REQUEST['build_id']) ? $_REQUEST['build_id'] : -1;
    $args->optFilterBuildSelected = isset($_REQUEST['filter_build_id']) ? $_REQUEST['filter_build_id'] : -1;
    $args->optPlatformSelected = isset($_REQUEST['platform_id']) ? $_REQUEST['platform_id'] : null;
	
    if (in_array(0, (array)$args->optPlatformSelected)) {
		$args->optPlatformSelected = null;
    }
        
    // 20081221 - franciscom
    $args->filter_assigned_to = isset($_REQUEST['filter_assigned_to']) ? $_REQUEST['filter_assigned_to'] : null;                                                                                                                        
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
	$args->include_unassigned = isset($_REQUEST['include_unassigned']) ? $_REQUEST['include_unassigned'] : 0;
    
    return $args;
}

/*
  function: initializeGui
  
  args :
  
  returns:
   
  rev: 
*/
function initializeGui(&$dbHandler,&$argsObj,&$cfgObj,&$tplanMgr)
{
	
	$platformMgr = new tlPlatform($dbHandler, $argsObj->tproject_id);

    $gui = new stdClass();
    $gui_open = config_get('gui_separator_open');
    $gui_close = config_get('gui_separator_close');
    
    $gui->str_option_any = $gui_open . lang_get('any') . $gui_close;
    $gui->str_option_none = $gui_open . lang_get('nobody') . $gui_close;

    // WHY IS NEEDED ?
    // to get all assigned testcases, no matter to whom they are assigned
    $gui->str_option_somebody = $gui_open . lang_get('filter_somebody') . $gui_close;
    
    $gui->filter_assigned_to=$argsObj->filter_assigned_to;
    $gui->include_unassigned = $argsObj->include_unassigned;
    
    $gui->keywordsFilterItemQty=0;
    $gui->keyword_id=$argsObj->keyword_id; 
   	$gui->toggleFilterModeLabel='';
    $gui->advancedFilterMode=0;
    $gui->chooseFilterModeEnabled=0;
	
    // We only want to use in the filter, keywords present in the test cases that are
    // linked to test plan, and NOT all keywords defined for test project
    $gui->keywords_map = array(0 => $gui->str_option_any) + (array)$tplanMgr->get_keywords_map($argsObj->tplan_id);
    if( !is_null($gui->keywords_map) )
    {
        $gui->keywordsFilterItemQty=min(count($gui->keywords_map),3);
    }

    // BUGID 2455
    $gui->optResultSelected = $argsObj->optResultSelected;
    $gui->optFilterBuild = initFilterBuildInfo($dbHandler,$argsObj,$tplanMgr);
    $gui->buildCount = count($gui->optFilterBuild['items']);
    $gui->optPlatform = initPlatformInfo($dbHandler,$argsObj,$platformMgr, $gui->str_option_any);
    
    $gui->keywordsFilterType=new stdClass();                                 
    $gui->keywordsFilterType->options = array('OR' => 'Or' , 'AND' =>'And'); 
    $gui->keywordsFilterType->selected=$argsObj->keywordsFilterType;         

    // filter using user roles
    $tplans = $_SESSION['currentUser']->getAccessibleTestPlans($dbHandler,$argsObj->tproject_id);
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

    $gui->tcase_id=intval($argsObj->tcase_id) > 0 ? $argsObj->tcase_id : '';
    
    $gui->optResult=createResultsMenu();
    $gui->optResult[$cfgObj->results['status_code']['all']] = $gui->str_option_any;

	$filter_cfg = config_get('execution_assignment_filter_methods');
    $gui->filter_methods = createExecutionAssignmentFilterMethodMenu();
    $gui->filter_method_specific_build = $filter_cfg['status_code']['specific_build'];
	$gui->optFilterMethodSelected = $argsObj->filter_method_selected;
  
    switch($argsObj->feature)
    {
      case 'planUpdateTC':
    	    $gui->menuUrl = "lib/plan/planUpdateTC.php";
    	    $gui->draw_bulk_update_button=true;
    	    $gui->draw_tc_unassign_button=false;
    	break;
    
      case 'test_urgency':
    	    $gui->menuUrl = "lib/plan/planUrgency.php";
    	    $gui->draw_bulk_update_button=false;
    	    $gui->draw_tc_unassign_button=false;
	    break;
    
      case 'tc_exec_assignment':
    	    // BUGID 1427
    	    $gui->menuUrl = "lib/plan/tc_exec_assignment.php";
    	    $gui->testers = getTestersForHtmlOptions($dbHandler,$argsObj->tplan_id,$argsObj->tproject_id,
    	                                             null,
     	                                             array(TL_USER_ANYBODY => $gui->str_option_any,
	                                                       TL_USER_NOBODY => $gui->str_option_none,
	                                                       TL_USER_SOMEBODY => $gui->str_option_somebody) );
          
    	    
    	    
    	    $gui->advancedFilterMode=$argsObj->advancedFilterMode;
          if( $gui->advancedFilterMode )
          {
              $label = 'btn_simple_filters';
              $gui->assigneeFilterItemQty=4; // as good as any other number
          }
          else
          {
              $label='btn_advanced_filters';
              $gui->assigneeFilterItemQty=1;
          }
          $gui->chooseFilterModeEnabled=1;  
          $gui->toggleFilterModeLabel=lang_get($label);
          $gui->draw_tc_unassign_button=true;
    	break;
    }


	$gui->additional_string = '';
	$gui->tree = buildTree($dbHandler,$gui,$argsObj,$cfgObj);

    return $gui;
}

/*
  function: buildTree
  
  args:
  
  returns: string used by different tree components to render tree.
           also add ajaxTree property to guiObj
  
  rev: 20081221 - franciscom -
       20080821 - franciscom - added management of ajaxTree property
       
*/
function buildTree(&$dbHandler,&$guiObj,&$argsObj, &$cfgObj)
{
    // Developer remarks:
    // using global coupling is 99% (ALWAYS) BAD -> global $tlCfg;
    // use config_get() because:
    //
    // - is standard practice on whole TL code (used in 75 files).
    // - is better because you do not need to care about name
    //   of config object or variable.
    // 
    $filters = new stdClass();
    $additionalInfo = new stdClass();

    $filters->keyword = buildKeywordsFilter($argsObj->keyword_id,$guiObj);
    $filters->keyword_id = $argsObj->keyword_id;
    $filters->keywordsFilterType = $argsObj->keywordsFilterType;
    $filters->platform_id = null;
    if($argsObj->optPlatformSelected != null) 
    {
    	$filters->platform_id = $argsObj->optPlatformSelected;
    }

    $filters->include_unassigned = $guiObj->include_unassigned;
    $filters->show_testsuite_contents=1;
   	$filters->hide_testcases = 0;
   	
   	$filters->tc_id = $argsObj->tcase_id;	
    $filters->build_id = $argsObj->optBuildSelected;
    $filters->filter_build_id = $argsObj->optFilterBuildSelected;
    $filters->method = $argsObj->filter_method_selected;
    
    $filters->filter_status = null;
    if( !is_null($argsObj->optResultSelected) )
    {
        if( !in_array($cfgObj->results['status_code']['all'], $guiObj->optResultSelected) )
        {
            // want to have code as key
            $dummy = array_flip($argsObj->optResultSelected);
            foreach( $dummy as $status_code => $value)
            {
                $dummy[$status_code] = $status_code;  
            }
            $filters->filter_status = $dummy;
        }
    }
    
    $filters->hide_testcases = false;
    //$filters->show_testsuite_contents = $cfgObj->exec->show_testsuite_contents;
       	
    // Set of filters Off
    $filters->build_id = 0;
    $filters->assignedTo = null;
    $filters->status = null;
    $filters->cf_hash = null;

    switch($argsObj->feature)
    {
      case 'test_urgency':
    	$filters->hide_testcases = 1;
      break;
    
      case 'tc_exec_assignment':
    	$filters->assignedTo = $argsObj->filter_assigned_to;
      if( !is_null($filters->assignedTo) )
      {
          if( in_array(TL_USER_ANYBODY, $argsObj->filter_assigned_to) )
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
    	break;
    }
    
    
    $additionalInfo->useCounters=CREATE_TC_STATUS_COUNTERS_OFF;
    $additionalInfo->useColours=COLOR_BY_TC_STATUS_OFF;
 
    $guiObj->args = initializeGetArguments($argsObj,$filters);
    $treeMenu = generateExecTree($dbHandler,$guiObj->menuUrl,
                                 $argsObj->tproject_id,$argsObj->tproject_name,
                                 $argsObj->tplan_id,$argsObj->tplan_name,
                                 $guiObj->args,$filters,$additionalInfo);
    
   	$guiObj->ajaxTree = new stdClass();
    $guiObj->ajaxTree->loader = '';
    $guiObj->ajaxTree->root_node = $treeMenu->rootnode;
    $guiObj->ajaxTree->children = $treeMenu->menustring ? $treeMenu->menustring : "''";
    $guiObj->ajaxTree->cookiePrefix = $argsObj->feature;
    
    return $treeMenu;
}


/*
  function: initializeGetArguments
            build arguments that will be passed to tc_exec_assignment.php with a http call
            This arguments that will be passed from tree menu to launched pages, 
            when user do some action on tree (example clicks on a folder)

  args:

  returns:

  rev: 20080427 - franciscom - added cfgObj arguments
       20080224 - franciscom - added include_unassigned

*/
function initializeGetArguments($argsObj,$filtersObj)
{
    $kl='';
    $settings = '&include_unassigned=' . $filtersObj->include_unassigned;
	
    if(is_array($argsObj->keyword_id) && !in_array(0, $argsObj->keyword_id))
    {
       $kl=implode(',',$argsObj->keyword_id);
       $settings .= '&keyword_id=' . $kl;
    }
    else if(!is_array($argsObj->keyword_id) && $argsObj->keyword_id > 0)
    {
    	  $settings .= '&keyword_id='.$argsObj->keyword_id;
    }
    $settings .= '&keywordsFilterType='.$argsObj->keywordsFilterType;
    
    if($filtersObj->assignedTo)
    {
    	  $settings .= '&filter_assigned_to=' . serialize($filtersObj->assignedTo);
    }
    
    $settings .= '&tplan_id=' . $argsObj->tplan_id;
    
    return $settings;
}


/**
 * initialize build info to choose as filter option
 * loads only active builds
 * 
 * @author asimon
 * @param resource &$dbHandler reference
 * @param object &$argsObj reference contains user input arguments
 * @param tlTestplan &$tplanMgr reference
 * @return HTML-Select for builds (names and values)
 */
function initFilterBuildInfo(&$dbHandler,&$argsObj,&$tplanMgr)
{
    $htmlSelect = array('items' => null, 'selected' => null);
    $htmlSelect['items'] = $tplanMgr->get_builds_for_html_options($argsObj->tplan_id,
    								testplan::GET_ACTIVE_BUILD);
   
    $maxBuildID = $tplanMgr->get_max_build_id($argsObj->tplan_id,
									testplan::GET_ACTIVE_BUILD);

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
function initPlatformInfo(&$dbHandler,&$argsObj,&$platformMgr, $str_any)
{
    $htmlSelect = array('items' => null, 'selected' => null);
    $htmlSelect['items'] = array(0 => $str_any) + (array)$platformMgr->getLinkedToTestplanAsMap($argsObj->tplan_id);
    
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


/**
 * create map with filter methods for execution assignment,
 * used for creating HTML Select inputs
 * 
 * @author asimon
 * @return $menu_data HTML Select (labels and values) 
 */
function createExecutionAssignmentFilterMethodMenu() {
	$filter_cfg = config_get('execution_assignment_filter_methods');
	$menu_data = array();
	foreach($filter_cfg['status_code'] as $status => $label) {
		$code = $filter_cfg['status_code'][$status];
		$menu_data[$code] = lang_get($filter_cfg['status_label'][$status]);
	}
	return $menu_data;
}

?>