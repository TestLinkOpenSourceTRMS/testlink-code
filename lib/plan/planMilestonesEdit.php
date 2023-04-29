<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	planMilestonesEdit.php
 * @author Francisco Mancardi
 *
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();

list($args,$gui) = initScript($db);

$commandMgr = new planMilestonesCommands($db);

$pFn = $args->doAction;
$op = null;
if(method_exists($commandMgr,$pFn)) {
	$op = $commandMgr->$pFn($args,$_SESSION['basehref']);
}

renderGui($args,$gui,$op,$templateCfg);


/**
 *
 *
 */
function initScript(&$dbH)
{
  $args = init_args($dbH);
  $gui = initialize_gui($dbH,$args);

  return array($args,$gui);
}

/**
 *
 *
 */
function init_args(&$dbHandler) 
{
	$_REQUEST = strings_stripSlashes($_REQUEST);
  $dateFormat = config_get('date_format');

	list($args,$env) = initContext();

  $d2k = array('target_date','start_date');
  foreach ($d2k as $dt) {
    $ori = $dt . "_original";
    $args->$ori = isset($_REQUEST[$dt]) ? $_REQUEST[$dt] : null; 
  }

  // convert target date to iso format to write to db
  $d2k = array('target_date','start_date');
  foreach ($d2k as $dt) {
    if (isset($_REQUEST[$dt]) && $_REQUEST[$dt] != '') {
		  $dpieces = split_localized_date($_REQUEST[$dt], $dateFormat);
		  if ($dpieces != null) {
			 // set date in iso format
			 $args->$dt = $dpieces['year'] . "-" . $dpieces['month'] . "-" . 
                    $dpieces['day'];
		  }
	  }
  }  
	

  $key2loop = array('low_priority_tcases','medium_priority_tcases',
                    'high_priority_tcases','id');
  foreach($key2loop as $key) {
  	$args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : 0; 
  }

	$args->name = isset($_REQUEST['milestone_name']) ? 
                $_REQUEST['milestone_name'] : null;
	$args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : null;

	$args->basehref = $_SESSION['basehref'];

	$args->tplan_name = '';
	if( $args->tplan_id > 0 ) {
	  $tplan_mgr = new testplan($dbHandler);
	  $info = $tplan_mgr->get_by_id($args->tplan_id);
	  $args->tplan_name = $info['name'];
  }

  if ($args->tproject_id == 0 && $args->tplan_id > 0) {
    $args->tproject_id = $info['testproject_id'];
  }
  if ($args->tproject_id == 0) {
    throw new Exception("Bad Test Project ID", 1);
  }  
  $args->tproject_name = 
    testproject::getName($dbHandler,$args->tproject_id);

  $args->user = $_SESSION['currentUser'];

  // ----------------------------------------------------------------
  // Feature Access Check
  // This feature is affected only for right at Test Project Level
  $env = [
    'script' => basename(__FILE__),
    'tproject_id' => $args->tproject_id,
    'tplan_id' => $args->tplan_id
  ];
  $args->user->checkGUISecurityClearance($dbHandler,$env,
                    array('testplan_planning'),'and');
  // ----------------------------------------------------------------

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

/**
 *
 *
 */
function initialize_gui(&$dbHandler,&$argsObj) {
  list($add2args,$gui) = initUserEnv($dbHandler,$argsObj); 

  $gui->activeMenu['execution'] = 'active';  
  $gui->action_descr = null;
  $gui->user_feedback = null;
  $gui->main_descr = lang_get('req_spec');

  $gui->managerURL = "lib/plan/planMilestonesEdit.php" .
                     "?tproject_id=$gui->tproject_id";

  // this will be JS, then single quotes are CRITIC
  $gui->cancelActionJS = "location.href=fRoot+" .
                         "'lib/plan/planMilestonesView.php" .
                         "?tproject_id=$gui->tproject_id" .
                         "&tplan_id=$gui->tplan_id'";

  return $gui;
}