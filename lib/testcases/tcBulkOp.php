<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  tcBulkOp.php
 *
 * 
 * @internal revisions
 * @since 1.9.14
 * 
 */
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../functions/xml.inc.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tcaseMgr = new testcase($db);
$args = init_args($tcaseMgr);
$gui = initializeGui($args);

if($args->doAction == 'apply')
{
  foreach($args->uchoice as $key => $val)
  {
    if($val > 0)
    {
      $tcaseMgr->setIntAttrForAllVersions($args->tcase_id,$key,$val);
    }  
  }  
}  

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: init_args

  args:
  
  returns: 

*/
function init_args(&$tcaseMgr)
{
  $_REQUEST = strings_stripSlashes($_REQUEST);
  
  new dBug($_REQUEST);

  $args = new stdClass();
  $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : null;
  switch($args->doAction)
  {
    case 'apply';
    break;

    default:
      $args->doAction = 'init';
    break;
  }

  $args->tcase_id = isset($_REQUEST['tcase_id']) ? intval($_REQUEST['tcase_id']) : 0;
  $args->goback_url = isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;

  $args->uchoice = array();  
  $k2s = array('importance','status','execution_type');
  foreach($k2s as $tg)
  {
    $args->uchoice[$tg] = intval(isset($_REQUEST[$tg]) ? $_REQUEST[$tg] : -1); 
  }  

  $dummy = getConfigAndLabels('testCaseStatus','code');
  $args->tcStatusCfg['status_code'] = $dummy['cfg'];
  $args->tcStatusCfg['code_label'] = $dummy['lbl'];

  $args->domainTCExecType = $tcaseMgr->get_execution_types();

  $dummy = config_get('importance');
  $args->domainTCImportance = $dummy['code_label'];

  return $args;
}

/**
 *
 *
 */
function initializeGui(&$argsObj)
{
  $guiObj = new stdClass();
  $guiObj->page_title = lang_get('bulk_op');
  $guiObj->uchoice = $argsObj->uchoice;
  $guiObj->tcase_id = $argsObj->tcase_id;

  $guiObj->domainTCStatus = array(-1 => '') + $argsObj->tcStatusCfg['code_label'];
  $guiObj->domainTCExecType = array(-1 => '') + $argsObj->domainTCExecType;
  $guiObj->domainTCImportance = array(-1 => '') + $argsObj->domainTCImportance;

  $guiObj->goback_url = !is_null($argsObj->goback_url) ? $argsObj->goback_url : ''; 
  
  return $guiObj;
}
