<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Display list of test projects
 *
 * @package 	  TestLink
 * @author 		  TestLink community
 * @copyright   2007-2020, TestLink community 
 * @filesource  projectView.php
 * @uses        projectCommon.php
 * @link 		    http://www.testlink.org/
 *
 */


require_once('../../config.inc.php');
require_once("common.php");
require_once("projectCommon.php");

testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
list($gui,$smarty) = initializeGui($db,$args);

$template2launch = $templateCfg->default_template;
if(!is_null($gui->tprojects) || $args->doAction=='list') {  
  if( $gui->itemQty == 0 ) {
    $template2launch = "projectEdit.tpl"; 
    $gui->doAction = "create";
  } 
}

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $template2launch);


/**
 * 
 *
 */
function init_args() {
  $_REQUEST = strings_stripSlashes($_REQUEST);
   
  list($args,$env) = initContext();
  
  $args->doAction = isset($_REQUEST['doAction']) 
                    ? $_REQUEST['doAction'] : 'list' ;

  $args->doViewReload = isset($_REQUEST['doViewReload']) 
                        ? $_REQUEST['doViewReload'] : 0 ;

  $args->name = isset($_REQUEST['name']) 
                ? trim($_REQUEST['name']) : null ;
  if(!is_null($args->name)) {
    if(strlen($args->name) == 0) {  
      $args->name = null;
    } else {
      $args->name = substr($args->name,0,100);
    }  
  } 

  return $args;  
}

/**
 * 
 *
 */
function initializeGui(&$dbHandler,&$argsObj) {

  $tplEngine = new TLSmarty();

  list($add2args,$guiObj) = initUserEnv($dbHandler,$argsObj);
  $guiObj->activeMenu['projects'] = 'active';
  $guiObj->doAction = $argsObj->doAction;
  $guiObj->doViewReload = $argsObj->doViewReload;

  $guiObj->canManage = $argsObj->user->hasRight($dbHandler,"mgt_modify_product");
  $guiObj->name = is_null($argsObj->name) ? '' : $argsObj->name;
  $guiObj->feedback = '';
  
  switch($argsObj->doAction) {
    case 'list':
      $filters = null;
    break;

    case 'search':
    default:
      $filters = array('name' => 
                   array('op' => 'like', 'value' => $argsObj->name));
      $guiObj->feedback = lang_get('no_records_found');
    break;
  }

  $tproject_mgr = new testproject($dbHandler);
  $opt = array('output' => 'array_of_map', 
               'order_by' => " ORDER BY name ", 
               'add_issuetracker' => true,
               'add_codetracker' => true, 
               'add_reqmgrsystem' => true);
  $guiObj->tprojects = $tproject_mgr->get_accessible_for_user($argsObj->userID,$opt,$filters);
  $guiObj->pageTitle = lang_get('title_testproject_management');
  
  $cfg = getWebEditorCfg('testproject');
  $guiObj->editorType = $cfg['type'];

  $guiObj->itemQty = count($guiObj->tprojects);
  if($guiObj->itemQty > 0) {
    initIntegrations($guiObj->tprojects,$guiObj->itemQty,$tplEngine);
  }  

  $guiObj->actions = $tproject_mgr->getViewActions($argsObj);

  return array($guiObj,$tplEngine);
}


/**
 *
 */
function checkRights(&$db,&$user) {
	return $user->hasRight($db,'mgt_modify_product');
}
