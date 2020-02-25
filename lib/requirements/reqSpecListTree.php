<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * @filesource reqSpecListTree.php
 * @author 	Francisco Mancardi (francisco.mancardi@gmail.com)
 * 
 * Tree menu with requirement specifications.
 *
 */

require_once('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
require_once('requirements.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($args);

$control = new tlRequirementFilterControl($db);
$control->build_tree_menu($gui);
$control->formAction = '';

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->assign('control', $control);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 *
 */
function init_args()
{
  $args = new stdClass();
  $args->tproject_id = intval(isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0);
  $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'undefned';
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
 *
 */
function checkRights(&$db,&$user)
{
	return ( $user->hasRight($db,'mgt_view_req') || $user->hasRight($db,'mgt_modify_req') ) ;
}