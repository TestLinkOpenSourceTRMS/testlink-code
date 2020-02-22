<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource: keywordsEdit.php
 *
 * Allows users to create/edit keywords. 
 *
 * @package    TestLink
 * @copyright  2005,2019 TestLink community 
 * @link       http://www.testlink.org/
 *  
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("csv.inc.php");
require_once("xml.inc.php");
require_once("keywordsEnv.php");


testlinkInitPage($db);
$tplCfg = templateConfiguration();

$tplEngine = new TLSmarty();

$op = new stdClass();
$op->status = 0;

$args = initEnv($db);
$gui = initializeGui($db,$args);

$tprojectMgr = new testproject($db);

$action = $args->doAction;

switch ($action) {
  case "do_create":
  case "do_update":
  case "do_delete":
  case "edit":
  case "create":
  case "cfl":
  case "do_cfl":
    $op = $action($args,$gui,$tprojectMgr);
  break;
}


if($op->status == 1) {
  $tpl = $op->template;
} else {
  $tpl = $tplCfg->default_template;
  $gui->user_feedback = getKeywordErrorMessage($op->status);
}

$gui->keywords = null;
$gui->activeMenu['projects'] = 'active';
$gui->submitCode = "";
if ($tpl != $tplCfg->default_template) {
  // I'm going to return to screen that display all keywords
  $kwe = getKeywordsEnv($db,$args->user,$args->tproject_id);
  foreach($kwe as $prop => $val) {
    $gui->$prop = $val;
  }  
  $setUpDialog = $gui->openByOther;  
} else {
  $setUpDialog = $gui->directAccess;  
  $gui->submitCode="return dialog_onSubmit($gui->dialogName)";
}

if( $setUpDialog ) {
  $gui->dialogName = 'kw_dialog';
  $gui->bodyOnLoad = "dialog_onLoad($gui->dialogName)";
  $gui->bodyOnUnload = "dialog_onUnload($gui->dialogName)";  

  if( $gui->directAccess ) {
    $gui->submitCode = "return dialog_onSubmit($gui->dialogName)";
  }  
}

$tplEngine->assign('gui',$gui);
$tplEngine->display($tplCfg->template_dir . $tpl);


/**
 * @return object returns the arguments for the page
 */
function initEnv(&$dbHandler) {
  $args = new stdClass();
  $_REQUEST = strings_stripSlashes($_REQUEST);
  $source = sizeof($_POST) ? "POST" : "GET";
  
  $ipcfg = 
    array( "doAction" => array($source,tlInputParameter::STRING_N,0,50),
           "id" => array($source, tlInputParameter::INT_N),
           "keyword" => array($source, tlInputParameter::STRING_N,0,100),
           "notes" => array($source, tlInputParameter::STRING_N),
           "tproject_id" => array($source, tlInputParameter::INT_N),
           "openByOther" => array($source, tlInputParameter::INT_N),
           "directAccess" => array($source, tlInputParameter::INT_N),
           "tcversion_id" => array($source, tlInputParameter::INT_N));
    
  $ip = I_PARAMS($ipcfg);

  $args = new stdClass();
  $args->doAction = $ip["doAction"];
  $args->notes = $ip["notes"];
  $args->keyword = $ip["keyword"];
  $args->keyword_id = $ip["id"];
  $args->tproject_id = $ip["tproject_id"];
  $args->openByOther = intval($ip["openByOther"]);
  $args->directAccess = intval($ip["directAccess"]);
  $args->tcversion_id = intval($ip["tcversion_id"]);

 
  if( $args->tproject_id <= 0 ) {
    throw new Exception("Error Invalid Test Project ID", 1);
  }

  // Check rights before doing anything else
  // Abort if rights are not enough 
  // Check Only At Test project level
  $args->user = $_SESSION['currentUser'];
  $environment = array('tproject_id' => $args->tproject_id);
  
  $check = new stdClass();
  $check->items = array('mgt_modify_key','mgt_view_key');
  $check->mode = 'and';
  checkAccess($dbHandler,$args->user,$environment,$check);

  // OK Go ahead
  $args->canManage = true;
  $args->mgt_view_events = $args->user->hasRight($dbHandler,"mgt_view_events",$args->tproject_id);

  $treeMgr = new tree($dbHandler);
  $dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id);
  $args->tproject_name = $dummy['name'];  

  setOpenByAnotherEnv($args);

  return $args;
}

/*
 *  initialize variables to launch user interface (smarty template)
 *  to get information to accomplish create task.
*/
function create(&$argsObj,&$guiObj) {
  $guiObj->submit_button_action = 'do_create';
  $guiObj->submit_button_label = lang_get('btn_save');
  $guiObj->main_descr = lang_get('keyword_management');
  $guiObj->action_descr = lang_get('create_keyword');

  $ret = new stdClass();
  $ret->template = 'keywordsEdit.tpl';
  $ret->status = 1;
  return $ret;
}

/*
 *  initialize variables to launch user interface (smarty template)
 *  to get information to accomplish edit task.
*/
function edit(&$argsObj,&$guiObj,&$tproject_mgr) {
  $guiObj->submit_button_action = 'do_update';
  $guiObj->submit_button_label = lang_get('btn_save');
  $guiObj->main_descr = lang_get('keyword_management');
  $guiObj->action_descr = lang_get('edit_keyword');

  $ret = new stdClass();
  $ret->template = 'keywordsEdit.tpl';
  $ret->status = 1;

  $keyword = $tproject_mgr->getKeyword($argsObj->keyword_id);
  if ($keyword) {
    $guiObj->keyword = $argsObj->keyword = $keyword->name;
    $guiObj->notes = $argsObj->notes = $keyword->notes;
    $guiObj->action_descr .= TITLE_SEP . $guiObj->keyword;
  }

  return $ret;
}

/*
 * Creates the keyword
 */
function do_create(&$args,&$guiObj,&$tproject_mgr) {
  $guiObj->submit_button_action = 'do_create';
  $guiObj->submit_button_label = lang_get('btn_save');
  $guiObj->main_descr = lang_get('keyword_management');
  $guiObj->action_descr = lang_get('create_keyword');

  $op = $tproject_mgr->addKeyword($args->tproject_id,$args->keyword,$args->notes);
  $ret = new stdClass();
  $ret->template = 'keywordsView.tpl';
  $ret->status = $op['status'];
  return $ret;
}

/*
 * Updates the keyword
 */
function do_update(&$argsObj,&$guiObj,&$tproject_mgr) {
  $guiObj->submit_button_action = 'do_update';
  $guiObj->submit_button_label = lang_get('btn_save');
  $guiObj->main_descr = lang_get('keyword_management');
  $guiObj->action_descr = lang_get('edit_keyword');

  $keyword = $tproject_mgr->getKeyword($argsObj->keyword_id);
  if ($keyword) {
    $guiObj->action_descr .= TITLE_SEP . $keyword->name;
  }
  
  $ret = new stdClass();
  $ret->template = 'keywordsView.tpl';
  $ret->status = $tproject_mgr->updateKeyword($argsObj->tproject_id,
    $argsObj->keyword_id,$argsObj->keyword,$argsObj->notes);
  return $ret;
}

/*
 * Deletes the keyword 
 */
function do_delete(&$args,&$guiObj,&$tproject_mgr) {
  $guiObj->submit_button_action = 'do_update';
  $guiObj->submit_button_label = lang_get('btn_save');
  $guiObj->main_descr = lang_get('keyword_management');
  $guiObj->action_descr = lang_get('delete_keyword');

  $ret = new stdClass();
  $ret->template = 'keywordsView.tpl';

  $dko = array('context' => 'getTestProjectName',
               'tproject_id' => $args->tproject_id);
  $ret->status = $tproject_mgr->deleteKeyword($args->keyword_id,$dko);

  return $ret;
}

/*
 *  initialize variables to launch user interface (smarty template)
 *  to get information to accomplish create task.
*/
function cfl(&$argsObj,&$guiObj) {
  $guiObj->submit_button_action = 'do_cfl';
  $guiObj->submit_button_label = lang_get('btn_create_and_link');
  $guiObj->main_descr = lang_get('keyword_management');
  $guiObj->action_descr = lang_get('create_keyword_and_link');

  $ret = new stdClass();
  $ret->template = 'keywordsEdit.tpl';
  $ret->status = 1;
  return $ret;
}

/*
 * Creates the keyword
 */
function do_cfl(&$args,&$guiObj,&$tproject_mgr) {
  $guiObj->submit_button_action = 'do_cfl';
  $guiObj->submit_button_label = lang_get('btn_save');
  $guiObj->main_descr = lang_get('keyword_management');
  $guiObj->action_descr = lang_get('create_keyword');

  $op = $tproject_mgr->addKeyword($args->tproject_id,$args->keyword,$args->notes);
  
  if( $op['status'] ) {
    $tcaseMgr = new testcase($tproject_mgr->db);
    $tbl = tlObject::getDBTables('nodes_hierarchy');
    $sql = "SELECT parent_id FROM {$tbl['nodes_hierarchy']}
            WHERE id=" . intval($args->tcversion_id);
    $rs = $tproject_mgr->db->get_recordset($sql);
    $tcase_id = intval($rs[0]['parent_id']);
    $tcaseMgr->addKeywords($tcase_id,$args->tcversion_id,
        array($op['id']));
  }

  $ret = new stdClass();
  $ret->template = 'keywordsView.tpl';
  $ret->status = $op['status'];
  return $ret;
}



/**
 *
 */
function getKeywordErrorMessage($code) {

  switch($code) {
    case tlKeyword::E_NAMENOTALLOWED:
      $msg = lang_get('keywords_char_not_allowed'); 
      break;

    case tlKeyword::E_NAMELENGTH:
      $msg = lang_get('empty_keyword_no');
      break;

    case tlKeyword::E_DBERROR:
    case ERROR: 
      $msg = lang_get('kw_update_fails');
      break;

    case tlKeyword::E_NAMEALREADYEXISTS:
      $msg = lang_get('keyword_already_exists');
      break;

    default:
      $msg = 'ok';
  }
  return $msg;
}

/**
 *
 *
 */
function initializeGui(&$dbH,&$args) {

  list($add2args,$gui) = initUserEnv($dbH,$args);
  $gui->openByOther = $args->openByOther;
  $gui->directAccess = $args->directAccess;
  $gui->tcversion_id = $args->tcversion_id;
  $gui->dialogName = $args->dialogName;
  $gui->bodyOnLoad = $args->bodyOnLoad;
  $gui->bodyOnUnload = $args->bodyOnUnload;  

  $gui->user_feedback = '';

  // Needed by the smarty template to be launched
  $kr = array('canManage' => "mgt_modify_key", 'canAssign' => "keyword_assignment");
  foreach( $kr as $vk => $rk ) {
    $gui->$vk = 
      $args->user->hasRight($dbH,$rk,$args->tproject_id);
  }

  $gui->tproject_id = $args->tproject_id;
  $gui->canManage = $args->canManage;
  $gui->mgt_view_events = $args->mgt_view_events;
  $gui->notes = $args->notes;
  $gui->name = $args->keyword;
  $gui->keyword = $args->keyword;
  $gui->keywordID = $args->keyword_id;

  $gui->editUrl = $_SESSION['basehref'] . "lib/keywords/keywordsEdit.php?" .
                  "tproject_id={$gui->tproject_id}"; 

  return $gui;
}
