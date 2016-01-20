<?php
require_once('../functions/configCheck.php');
//checkConfiguration();
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../requirements/reqCommands.class.php');
require_once('../functions/testproject.class.php');
require_once('../functions/cfield_mgr.class.php');
require_once('../functions/lang_api.php');
require_once('users.inc.php');
/*++++++++++++++++++++++++++++++++++++++++++++++++*/

doDBConnect($db, database::ONERROREXIT);

//paint the gui
$gui = init_gui($db);
renderGUI($gui);

function renderGUI($guiObj)
{
  global $g_tlLogger; 
  $templateCfg = templateConfiguration();
  
  $smarty = new TLSmarty();
  $smarty->assign('gui', $guiObj);
  $smarty->display($templateCfg->default_template);
}

function init_gui(&$db)
{
  $fieldName = $_POST["fieldName"];
  
  $commandMgr = new reqCommands($db);
  $reqMgr = new requirement_mgr($db);
  $gui = $commandMgr->initGuiBean();
  //fetch the possible values of the given field
  if(strcmp($fieldName,"Status") === 0) {

	  $gui->fieldVals = $reqMgr->getStatusFieldValsLocaledForAssignment();
  }
  else
  {
	$cfield_mgr = new cfield_mgr($db);
	$selctedCField = $cfield_mgr->get_by_name($fieldName);
	foreach($selctedCField as $val) {
		$gui->fieldVals = explode("|",$val["possible_values"]);
	}
  }
  
  $args = init_args($db);
    
  $gui->fieldAssignments = $reqMgr->getNotificationFieldAssignmentByFieldName($args->tproject_id,$fieldName);
  $gui->fieldName = $fieldName;
  $gui->users = getUsersForHtmlOptions($db,"WHERE active=1");
  return $gui;
}

function init_args(&$db) {
	$args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
	$args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
	return $args;
}
?>