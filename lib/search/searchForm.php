<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Form to set search criteria
 *
 * @filesource  searchForm.php
 * @package     TestLink
 * @author      TestLink community
 * @copyright   2007-2017, TestLink community 
 * @link        http://www.testlink.org
 *
 *
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$args = init_args();
$gui = initializeGui($db,$args);


echo __FILE__;
die();

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . 'tcSearchForm.tpl');
/**
 * 
 *
 */
function init_args()
{              
  $args = new stdClass();
  $args->tprojectID = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  $args->tprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 0;

  if($args->tprojectID <= 0)
  {
    throw new Exception("Error Processing Request - Invalid Test project id " . __FILE__);
  }   
      
  return $args;
}

function initializeGui(&$dbHandler,&$argsObj)
{

  $tproject_mgr = new testproject($dbHandler);
    
  $gui = new stdClass();
  $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($argsObj->tprojectID) . config_get('testcase_cfg')->glue_character;
  $gui->mainCaption = lang_get('testproject') . " " . $argsObj->tprojectName;
  $gui->importance = config_get('testcase_importance_default');
  $gui->creation_date_from = null;
  $gui->creation_date_to = null;
  $gui->modification_date_from = null;
  $gui->modification_date_to = null;
  $gui->search_important_notice = sprintf(lang_get('search_important_notice'),$argsObj->tprojectName);

  $gui->design_cf = $tproject_mgr->cfield_mgr->get_linked_cfields_at_design($argsObj->tprojectID,cfield_mgr::ENABLED,null,'testcase');

  $gui->keywords = $tproject_mgr->getKeywords($argsObj->tprojectID);

  $gui->filter_by['design_scope_custom_fields'] = !is_null($gui->design_cf);
  $gui->filter_by['keyword'] = !is_null($gui->keywords);

  $reqSpecSet = $tproject_mgr->genComboReqSpec($argsObj->tprojectID);
  $gui->filter_by['requirement_doc_id'] = !is_null($reqSpecSet);

  $gui->option_importance = array(0 => '',HIGH => lang_get('high_importance'),MEDIUM => lang_get('medium_importance'), 
                                  LOW => lang_get('low_importance'));

 
  $dummy = getConfigAndLabels('testCaseStatus','code');
  $gui->domainTCStatus = array(0 => '') + $dummy['lbl'];
  return $gui;
}