<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	planMilestonesEdit.php
 * @author Francisco Mancardi
 *
 * @internal revisions
 * @since 1.9.4
 * 20120204 - franciscom - TICKET 4906: Several security issues       
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");
$date_format_cfg = config_get('date_format');

$templateCfg = templateConfiguration();
$args = init_args($db,$date_format_cfg);
$gui = initialize_gui($db,$args);
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

	$args->id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$args->name = isset($_REQUEST['milestone_name']) ? $_REQUEST['milestone_name'] : null;
	$args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : null;

	$args->basehref=$_SESSION['basehref'];
	$args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";
	
	$args->tplan_name = '';
	$args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
	if( $args->tplan_id == 0 )
	{
	    $args->tplan_id = isset($_SESSION['testplanID']) ? intval($_SESSION['testplanID']) : 0;
	}
	if( $args->tplan_id > 0 )
	{
	    $tplan_mgr = new testplan($dbHandler);
	    $info = $tplan_mgr->get_by_id($args->tplan_id);
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
function initialize_gui(&$dbHandler,&$argsObj)
{
    $req_spec_mgr = new requirement_spec_mgr($dbHandler);
    $gui = new stdClass();
    
    $gui->user_feedback = null;
    $gui->main_descr = lang_get('req_spec');
    $gui->action_descr = null;

    $gui->grants = new stdClass();
    $gui->grants->milestone_mgmt = has_rights($dbHandler,"testplan_planning");
	$gui->grants->mgt_view_events = has_rights($dbHandler,"mgt_view_events");
	
	return $gui;
}


function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,"testplan_planning"));
}
?>