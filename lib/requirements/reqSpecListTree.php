<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@filesource	reqSpecListTree.php
* 	@author 	Francisco Mancardi (francisco.mancardi@gmail.com)
* 
* 	Tree menu with requirement specifications.
*
*   @internal revisions
*   20100808 - asimon - heavy refactoring for requirement filtering
*/

require_once('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
require_once('requirements.inc.php');
testlinkInitPage($db,!TL_UPDATE_ENVIRONMENT,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args($db);
checkRights($db,$_SESSION['currentUser'],$args);

$gui = initializeGui($args);

// new class for filter controling/handling
$control = new tlRequirementFilterControl($db);
$control->build_tree_menu($gui);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->assign('control', $control);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args(&$dbHandler)
{
    $args = new stdClass();
    $args->tproject_name = '';
    $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	if($args->tproject_id > 0) 
	{
		$treeMgr = new tree($dbHandler);
		$dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id);
		$args->tproject_name = $dummy['name'];
	}

    $args->basehref = $_SESSION['basehref'];
    
    return $args;
}

/*
  function: initializeGui
            initialize gui (stdClass) object that will be used as argument
            in call to Template Engine.
 
  args: argsObj: object containing User Input and some session values
        basehref: URL to web home of your testlink installation.
  
  returns: stdClass object
  
  rev: 

*/
function initializeGui($argsObj)
{
    $gui = new stdClass();
    $gui->tree_title = lang_get('title_navigator'). ' - ' . lang_get('title_req_spec');
  
    $gui->req_spec_manager_url = "lib/requirements/reqSpecView.php";
    $gui->req_manager_url = "lib/requirements/reqView.php";
    $gui->basehref = $argsObj->basehref;
    
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
	checkSecurityClearance($db,$userObj,$env,array('mgt_view_req'),'and');
}

?>