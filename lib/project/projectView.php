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


//$log = "/tmp/trace.log";
//file_put_contents($log, "\n in file/line: " . __FILE__ . '/' . __LINE__ ,FILE_APPEND);
testlinkInitPage($db,false,false,"checkRights");
//file_put_contents($log, "\n in file/line: " . __FILE__ . '/' . __LINE__ ,FILE_APPEND);

$tplCfg = templateConfiguration();
// file_put_contents($log, "\n in file/line: " . __FILE__ . '/' . __LINE__  . json_encode($tplCfg),FILE_APPEND);


$tpl2launch = $tplCfg->default_template;
$tplDir = $tplCfg->template_dir; 
if ( $tpl2launch !==  'projectView.tpl') {
  $tpl2launch = 'projectView.tpl';
  $tplDir = 'project/';
  // file_put_contents($log, "\n in file/line: " . __FILE__ . '/' . __LINE__ ,FILE_APPEND);
} 

$args = init_args();
list($gui,$smarty) = initializeGui($db,$args);

/*
file_put_contents($log, "\n in file/line: " . __FILE__ . '/' . __LINE__ .'$gui->tprojects > ' . json_encode($gui->tprojects),FILE_APPEND);
file_put_contents($log, "\n in file/line: " . __FILE__ . '/' . __LINE__ .'$gui->itemQty > ' . $gui->itemQty,FILE_APPEND);
*/

// we can arrive here because the user has deleted the latest test project in the system
// in this situation we need to force the project edit feature
if ($gui->itemQty == 0)  {
  $tpl2launch = "projectEdit.tpl"; 
  $tplDir = 'project/';
  initGuiForCreate($db,$args,$gui);
} 

// file_put_contents($log, "\n in file/line: " . __FILE__ . '/' . __LINE__ . ' I will launch > ' . $tplDir . $tpl2launch,FILE_APPEND);
$smarty->assign('gui',$gui);

$smarty->display($tplDir . $tpl2launch);


/**
 * 
 *
 */
function init_args() 
{
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

  $args->user = isset($_SESSION['currentUser']) ? $_SESSION['currentUser'] : null;

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
  $guiObj->mgt_view_events = $argsObj->user->hasRight($dbHandler,"mgt_view_events");
  
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
  $guiObj->tprojects = (array)$tproject_mgr->get_accessible_for_user($argsObj->userID,$opt,$filters);
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
