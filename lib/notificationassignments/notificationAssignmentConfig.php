<?php
require_once('../functions/configCheck.php');
//checkConfiguration();
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../requirements/reqCommands.class.php');
require_once('../functions/testproject.class.php');
require_once('../functions/cfield_mgr.class.php');
/*++++++++++++++++++++++++++++++++++++++++++++++++*/

doDBConnect($db, database::ONERROREXIT);
$args = init_args($db);
//if a new assignment was made or an assignment was deleted, the database will be updated
$reqMgr = new requirement_mgr($db);
$_realPOST = getRealInput('POST');
if(array_key_exists("submit",$_realPOST)) {
	$selectFieldValues = array();
	foreach($_realPOST as $key => $val) {
		$selectPos = strpos($key,"select_");
		if($selectPos !== false) {
			$selectFieldValues[str_replace("_"," ",substr($key,$selectPos+7))] = $val;
		}
	}
	$reqMgr->createNotificationFieldAssignment($args->tproject_id, $_realPOST["fieldName"], $selectFieldValues);
}elseif(array_key_exists("delete",$_realPOST)) {
	$reqMgr->deleteNotificationFieldAssignmentsByFieldName($args->tproject_id,$_realPOST["fieldName"]);
}

//create GUI
$gui = init_gui($db, $args, $reqMgr);
renderGUI($gui);

function renderGUI($guiObj)
{
  global $g_tlLogger; 
  $templateCfg = templateConfiguration();

  $smarty = new TLSmarty();
  $smarty->assign('gui', $guiObj);
  $smarty->display($templateCfg->default_template);
}

function init_gui(&$db, &$args, &$reqMgr)
{
  $commandMgr = new reqCommands($db);
  $gui = $commandMgr->initGuiBean();
  
  //only take custom fields which are of type "list" and are liked to requirements.
  $tprojectMgr = new testproject($db);
  $linkedCustomfields = $tprojectMgr->get_linked_custom_fields($args->tproject_id,"requirement");
  $fieldNamesForGUISize = 2;
  foreach($linkedCustomfields as $key => $val) {
	if($val["type"] == 6) {
		$fieldNamesForGUISize++;
	}  
  }
  
  $ArrFieldNamesForGUI = array(fieldNamesForGUISize);
  $ArrFieldNamesForGUI[0] = "";
  $ArrFieldNamesForGUI["Status"] = "Status";
  for($i = 2; $i<=sizeof($linkedCustomfields)+1; $i++) {
	if($linkedCustomfields[$i-1]["type"] == 6) {
		$ArrFieldNamesForGUI[$linkedCustomfields[$i-1]["name"]] = $linkedCustomfields[$i-1]["name"];
	}
  }

  $gui->fieldNames = $ArrFieldNamesForGUI;
  $gui->assignments = $reqMgr->getAllNotificationFieldAssignments($args->tproject_id);
  return $gui;
}

function init_args(&$db) {
	$args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
	return $args;
}
?>