<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	planMilestonesEdit.php
 * @author 		Francisco Mancardi
 *
 * @internal revisions
 *  20101026 - Julian - BUGID 3930 - Localized dateformat for datepicker including date validation
 *  20101022 - asimon - BUGID 3716 - replaced old separated inputs for day/month/year by ext js calendar
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);
$date_format_cfg = config_get('date_format');

$templateCfg = templateConfiguration();
$args = init_args($db,$date_format_cfg);
checkRights($db,$_SESSION['currentUser'],$args);

$gui = initialize_gui($db,$_SESSION['currentUser'],$args);
$commandMgr = new planMilestonesCommands($db);

$pFn = $args->doAction;
$op = null;
if(method_exists($commandMgr,$pFn))
{
	$op = $commandMgr->$pFn($args,$_SESSION['basehref']);
}

renderGui($args,$gui,$op,$templateCfg);


/*
  function: 

  args :
  
  returns: 

*/
function init_args(&$dbHandler,$dateFormat)
{
	$_REQUEST = strings_stripSlashes($_REQUEST);
	$args = new stdClass();

	// BUGID 3716
	$args->target_date_original = isset($_REQUEST['target_date']) ? $_REQUEST['target_date'] : null;
	$args->start_date_original = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : null;
	
	// convert target date to iso format to write to db
    if (isset($_REQUEST['target_date']) && $_REQUEST['target_date'] != '') {
		$date_array = split_localized_date($_REQUEST['target_date'], $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			$args->target_date = $date_array['year'] . "-" . $date_array['month'] . "-" . $date_array['day'];
		}
	}
	
	// convert start date to iso format to write to db
    if (isset($_REQUEST['start_date']) && $_REQUEST['start_date'] != '') {
		$date_array = split_localized_date($_REQUEST['start_date'], $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			$args->start_date = $date_array['year'] . "-" . $date_array['month'] . "-" . $date_array['day'];
		}
	}
 	
  	$key2loop = array('low_priority_tcases','medium_priority_tcases','high_priority_tcases');
  	foreach($key2loop as $key)
  	{
  	    $args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : 0;     
  	}

	$args->id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
	$args->name = isset($_REQUEST['milestone_name']) ? $_REQUEST['milestone_name'] : null;
	$args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : null;

	$args->basehref=$_SESSION['basehref'];


	$treeMgr = new tree($dbHandler);
	$args->tproject_name = '';
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	if( $args->tproject_id > 0 )
	{
	    $info = $treeMgr->get_node_hierarchy_info($args->tproject_id);
	    $args->tproject_name = $info['name'];
  	}
	
	$args->tplan_name = '';
	$args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
	if( $args->tplan_id > 0 )
	{
	    $info = $treeMgr->get_node_hierarchy_info($args->tplan_id);
	    $args->tplan_name = $info['name'];
  	}
  	
	return $args;
}


/*
  function: renderGui

  args:

  returns:

*/
function renderGui(&$argsObj,$guiObj,$opObj,$templateCfg)
{
    $smartyObj = new TLSmarty();
    //
    // key: operation requested (normally received from GUI on doAction)
    // value: operation value to set on doAction HTML INPUT
    // This is useful when you use same template (example xxEdit.tpl), for create and edit.
    // When template is used for create -> operation: doCreate.
    // When template is used for edit -> operation: doUpdate.
    //              
    // used to set value of: $guiObj->operation
    //
    $actionOperation=array('create' => 'doCreate', 'edit' => 'doUpdate',
                           'doDelete' => '', 'doCreate' => 'doCreate', 
                           'doUpdate' => 'doUpdate');
     
    $renderType = 'none';
    switch($argsObj->doAction)
    {
        case "edit":
        case "create":
        case "doDelete":
		case "doCreate":
      	case "doUpdate":
            $renderType = 'template';
            $key2loop = get_object_vars($opObj);
            foreach($key2loop as $key => $value)
            {
                $guiObj->$key = $value;
            }
            $guiObj->operation = $actionOperation[$argsObj->doAction];
            
            $tplDir = (!isset($opObj->template_dir)  || is_null($opObj->template_dir)) ? $templateCfg->template_dir : $opObj->template_dir;
            $tpl = is_null($opObj->template) ? $templateCfg->default_template : $opObj->template;
            
            $pos = strpos($tpl, '.php');
           	if($pos === false)
           	{
                $tpl = $tplDir . $tpl;      
            }
            else
            {
                $renderType = 'redirect';  
            }
            break;
    }

    switch($renderType)
    {
        case 'template':
        	$smartyObj->assign('gui',$guiObj);
		    $smartyObj->display($tpl);
        break;  
 
        case 'redirect':
		      header("Location: {$tpl}");
	  		  exit();
        break;

        default:
        break;
    }

}

/*
  function: initialize_gui

  args : -

  returns:

*/
function initialize_gui(&$dbHandler,&$userObj,&$argsObj)
{
    $gui = new stdClass();

    $gui->tproject_id = $argsObj->tproject_id;
    $gui->tplan_id = $argsObj->tplan_id;
    $gui->user_feedback = null;
    $gui->main_descr = lang_get('req_spec');
    $gui->action_descr = null;

    $gui->grants = new stdClass();
    $gui->grants->milestone_mgmt = $userObj->hasRight($dbHandler,"testplan_planning",
    												  $gui->tproject_id,$gui->tplan_id);
	$gui->grants->mgt_view_events = $userObj->hasRight($dbHandler,"mgt_view_events",
													   $gui->tproject_id,$gui->tplan_id);
     
     
	//$manager = "lib/plan/planMilestonesEdit.php?tproject_id={$gui->tproject_id}&doAction=";
	//$gui->actions = new stdClass();
	//$gui->actions->edit = $manager . "edit&tplan_id={$gui->tplan_id}";
	//$gui->actions->delete = $manager . 'doDelete&id=';
	//$gui->actions->create = $manager . "create&tplan_id={$gui->tplan_id}";

	return $gui;
}


/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('testplan_planning'),'and');
}
?>