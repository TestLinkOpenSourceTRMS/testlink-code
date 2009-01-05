<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: planMilestonesView.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2009/01/05 20:05:30 $ by $Author: schlundus $
 * @author Francisco Mancardi
 *
 * rev: 
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initialize_gui($db,$args);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
	$_REQUEST = strings_stripSlashes($_REQUEST);
	$args = new stdClass();

	$args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction']:null;
	$args->basehref=$_SESSION['basehref'];
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";

	$args->tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
	$args->tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : "";

	return $args;
}


/*
  function: initialize_gui

  args : -

  returns:

*/
function initialize_gui(&$dbHandler,&$argsObj)
{
    $manager = new milestone_mgr($dbHandler);
    $gui = new stdClass();
    
    $gui->user_feedback = null;
    $gui->main_descr = lang_get('title_milestones') . $argsObj->tplan_name;
    $gui->action_descr = null;
    $gui->tplan_name = $argsObj->tplan_name;
    $gui->tplan_id = $argsObj->tplan_id;
	$gui->items = $manager->get_all_by_testplan($argsObj->tplan_id);
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