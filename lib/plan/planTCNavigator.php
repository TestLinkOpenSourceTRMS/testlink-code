<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @version $Id: planTCNavigator.php,v 1.33 2009/08/18 06:48:37 franciscom Exp $
 * @author Martin Havlat
 *
 * Test navigator for Test Plan
 *
 * rev :
 *  20081223 - franciscom - advanced/simple filter feature
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
$gui->additional_string = '';
$gui->tree = buildTree($db,$gui,$args);

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

    // 20081221 - franciscom
    // $args->filter_assigned_to = isset($_REQUEST['filter_assigned_to']) ? intval($_REQUEST['filter_assigned_to']) : 0;
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
    }

    return $args;
}

/*
  function: initializeGui
  
  args :
  
  returns:
   
  rev: 
*/
function initializeGui(&$dbHandler,&$argsObj,&$tplanMgr)
{
    $gui = new stdClass();
    $gui_open = config_get('gui_separator_open');
    $gui_close = config_get('gui_separator_close');
    
    $gui->str_option_any = $gui_open . lang_get('any') . $gui_close;
    $gui->str_option_none = $gui_open . lang_get('nobody') . $gui_close;

    $gui->filter_assigned_to=$argsObj->filter_assigned_to;
    $gui->keywordsFilterItemQty=0;
    $gui->keyword_id=$argsObj->keyword_id; 
   	$gui->toggleFilterModeLabel='';
    $gui->advancedFilterMode=0;
    $gui->chooseFilterModeEnabled=0;


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

    switch($argsObj->feature)
    {
      case 'planUpdateTC':
    	    $gui->menuUrl = "lib/plan/planUpdateTC.php";
    	    $gui->draw_bulk_update_button=true;
    	break;
    
      case 'test_urgency':
    	    $gui->menuUrl = "lib/plan/planUrgency.php";
	    break;
    
      case 'tc_exec_assignment':
    	    // BUGID 1427
    	    $gui->menuUrl = "lib/plan/tc_exec_assignment.php";
    	    $gui->testers = getTestersForHtmlOptions($dbHandler,$argsObj->tplan_id,$argsObj->tproject_id,
    	                                             null,
     	                                             array(TL_USER_ANYBODY => $gui->str_option_any,
	                                                       TL_USER_NOBODY => $gui->str_option_none) );
          
    	    
    	    
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
    	break;
    }

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
    $filters = new stdClass();
    $additionalInfo = new stdClass();

    $filters->keyword_id = $argsObj->keyword_id;
    $filters->keywordsFilterType = $argsObj->keywordsFilterType;
    $filters->platform_id = null;

    $filters->include_unassigned=1;
    $filters->show_testsuite_contents=1;
   	$filters->hide_testcases = 0;

    // Set of filters Off
    $filters->tc_id = null;
    $filters->build_id = 0;
    $filters->assignedTo = null;
    $filters->status = null;
    $filters->cf_hash = null;

    $filters->statusAllPrevBuilds=null;

    switch($argsObj->feature)
    {
      case 'test_urgency':
    	$filters->hide_testcases = 1;
      break;
    
      case 'tc_exec_assignment':
    	$filters->assignedTo = $argsObj->filter_assigned_to;
    	$filters->include_unassigned = 0;
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
    {
    	  $settings .= '&filter_assigned_to=' . serialize($filtersObj->assignedTo);
    }
    
    $settings .= '&tplan_id=' . $argsObj->tplan_id;
    
    return $settings;
}
?>