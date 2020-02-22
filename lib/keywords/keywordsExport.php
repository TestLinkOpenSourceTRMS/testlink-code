<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  keywordsExport.php
 * @package     TestLink
 * @copyright   2005,2019 TestLink community 
 * @link        http://www.testlink.org/
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("csv.inc.php");
require_once("xml.inc.php");
require_once("keywordsEnv.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args($db);
$gui = initializeGui($db,$args);

switch ($args->doAction) {
  case "do_export":
    $op = do_export($db,$smarty,$args);
  break;
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 *
 */
function init_args(&$dbHandler) {
  $ipcfg = array("doAction" => array("GET",tlInputParameter::STRING_N,0,50),
                 "tproject_id" => array("GET",tlInputParameter::INT_N),
                 "export_filename" => array("POST", tlInputParameter::STRING_N,0,255),
                 "exportType" => array("POST", tlInputParameter::STRING_N,0,255));

  $args = new stdClass();
  $pps = I_PARAMS($ipcfg,$args);

  if( $args->tproject_id <= 0 ) {
    throw new Exception("Error Invalid Test Project ID", 1);
  }
  
  // Check rights before doing anything else
  // Abort if rights are not enough 
  $args->user = $_SESSION['currentUser'];
  $environment = array('tproject_id' => $args->tproject_id);  
  $check = new stdClass();
  $check->items = array('mgt_view_key');
  $check->mode = 'and';
  checkAccess($dbHandler,$args->user,$environment,$check);
 
  $args->tproject_name = testproject::getName($dbHandler,$args->tproject_id);

  return $args;
}


/*
  function: do_export
            generate export file

  args :
  
  returns: 

*/
function do_export(&$db,&$smarty,&$args) {
  $pfn = null;
  $pfx = null;
  switch($args->exportType) {
    case 'iSerializationToCSV':
      $pfn = null;
      $pfx = "exportKeywordsToCSV";
    break;

    case 'iSerializationToXML':
      $pfn = "exportKeywordsToXML";
    break;
  }

  if (null != $pfn) {
    $tprojectMgr = new testproject($db);
    $content = $tprojectMgr->$pfn($args->tproject_id);
    downloadContentsToFile($content,$args->export_filename);
    exit();
  }

  if (null != $pfx) {
    $cu = getKeywordsEnv($db,$args->user,$args->tproject_id,
            array('usage' => 'csvExport'));

    $content = exportKeywordsToCSV($cu->kwOnTCV);
    downloadContentsToFile($content,$args->export_filename);
    exit();
  }

}

/**
 *
 */
function initializeGui(&$dbH,&$argsObj) {
  
  list($add2args,$gui) = initUserEnv($dbH,$argsObj);
  $gui->activeMenu['projects'] = 'active';
  $gui->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
  $gui->export_filename = is_null($argsObj->export_filename) ? 
    $argsObj->tproject_name . '-keywords.xml' : $argsObj->export_filename;
  $gui->action_descr = lang_get('export_keywords');

  $gui->actionUrl = "lib/keywords/keywordsExport.php?doAction=do_export&tproject_id={$gui->tproject_id}";
  $gui->cancelUrl = "lib/keywords/keywordsView.php?tproject_id={$gui->tproject_id}";

  $kw = new tlKeyword();
  $gui->exportTypes = $kw->getSupportedSerializationInterfaces();
  return $gui;
} 


/**
 *
 */
function exportKeywordsToCSV($kwSet) {
  $keys = array( "keyword","notes","tcv_qty" );
  $csv = exportDataToCSV($kwSet,$keys,$keys,array('addHeader' => 1));
  return $csv;
}