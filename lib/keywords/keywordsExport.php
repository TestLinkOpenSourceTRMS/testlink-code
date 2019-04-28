<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  keywordsExport.php
 * @package     TestLink
 * @copyright   2005,2015 TestLink community 
 * @link        http://www.testlink.org/
 **
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("csv.inc.php");
require_once("xml.inc.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args($db);
$gui = initializeGui($args);

switch ($args->doAction)
{
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
function init_args(&$dbHandler)
{
  $ipcfg = array("doAction" => array("GET",tlInputParameter::STRING_N,0,50),
                 "tproject_id" => array("GET",tlInputParameter::INT_N),
                 "export_filename" => array("POST", tlInputParameter::STRING_N,0,255),
                 "exportType" => array("POST", tlInputParameter::STRING_N,0,255));

  $args = new stdClass();
  $pps = I_PARAMS($ipcfg,$args);

  if( $args->tproject_id <= 0 )
  {
    throw new Exception("Error Invalid Test Project ID", 1);
  }
  
  // Check rights before doing anything else
  // Abort if rights are not enough 
  $user = $_SESSION['currentUser'];
  $env['tproject_id'] = $args->tproject_id;
  $env['tplan_id'] = 0;
  
  $check = new stdClass();
  $check->items = array('mgt_view_key');
  $check->mode = 'and';
  checkAccess($dbHandler,$user,$env,$check);
 
  $tproj_mgr = new testproject($dbHandler);
  $dm = $tproj_mgr->get_by_id($args->tproject_id,array('output' => 'name'));
  $args->tproject_name = $dm['name'];

  return $args;
}


/*
  function: do_export
            generate export file

  args :
  
  returns: 

*/
function do_export(&$db,&$smarty,&$args)
{
  $pfn = null;
  switch($args->exportType)
  {
    case 'iSerializationToCSV':
      $pfn = "exportKeywordsToCSV";
    break;

    case 'iSerializationToXML':
      $pfn = "exportKeywordsToXML";
    break;
  }

  if ($pfn)
  {
    $tprojectMgr = new testproject($db);
    $content = $tprojectMgr->$pfn($args->tproject_id);
    downloadContentsToFile($content,$args->export_filename);
    exit();
  }
}

/**
 *
 */
function initializeGui(&$argsObj)
{
  $kw = new tlKeyword();
  $gui = new stdClass();
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->exportTypes = $kw->getSupportedSerializationInterfaces();
  $gui->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
  $gui->export_filename = is_null($argsObj->export_filename) ? 'keywords.xml' : $argsObj->export_filename;
  $gui->action_descr = lang_get('export_keywords');

  $gui->actionUrl = "lib/keywords/keywordsExport.php?doAction=do_export&tproject_id={$gui->tproject_id}";
  $gui->cancelUrl = "lib/keywords/keywordsView.php?tproject_id={$gui->tproject_id}";
  return $gui;
} 