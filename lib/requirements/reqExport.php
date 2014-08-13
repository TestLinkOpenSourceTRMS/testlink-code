<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  reqExport.php
 *
 * Allows users to export requirements.
 *
**/
require_once("../../config.inc.php");
require_once("csv.inc.php");
require_once("xml.inc.php");
require_once("common.php");
require_once("requirements.inc.php");

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();
$req_spec_mgr = new requirement_spec_mgr($db);

$args = init_args();
$gui = initializeGui($args,$req_spec_mgr);

switch($args->doAction)
{
  case 'export':
    $smarty = new TLSmarty();
    $smarty->assign('gui', $gui);
    $smarty->display($templateCfg->template_dir . $templateCfg->default_template);
  break;
    
  case 'doExport':
    doExport($args,$req_spec_mgr);
  break;
}


/**
 * checkRights
 *
 */
function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'mgt_view_req');
}


/**
 * init_args
 *
 */
function init_args()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);
  $args = new stdClass();
  $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : 'export';
  $args->exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;
  $args->req_spec_id = isset($_REQUEST['req_spec_id']) ? intval($_REQUEST['req_spec_id']) : null;
  $args->export_filename = isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : "";
  
  $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
  if( $args->tproject_id == 0 )
  { 
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }
  $args->scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : 'items';

  return $args;  
}


/**
 * initializeGui
 *
 */
function initializeGui(&$argsObj,&$req_spec_mgr)
{
  $gui = new stdClass();
  $gui->exportTypes = $req_spec_mgr->get_export_file_types();
  $gui->exportType = $argsObj->exportType; 
  $gui->scope = $argsObj->scope;
  $gui->tproject_id = $argsObj->tproject_id;
  
  switch($argsObj->scope)
  {
    case 'tree':
      $gui->req_spec['title'] = lang_get('all_reqspecs_in_tproject');
      $gui->req_spec_id = 0;
      $exportFileName = 'all-req.xml';
    break;
      
    case 'branch':
      $gui->req_spec = $req_spec_mgr->get_by_id($argsObj->req_spec_id);
      $gui->req_spec_id = $argsObj->req_spec_id;
      $exportFileName = $gui->req_spec['title'] . '-req-spec.xml';
    break;

    case 'items':
      $gui->req_spec = $req_spec_mgr->get_by_id($argsObj->req_spec_id);
      $gui->req_spec_id = $argsObj->req_spec_id;
      $exportFileName = $gui->req_spec['title'] . '-child_req.xml';
    break;
      
  }
  
  $gui->export_filename = trim($argsObj->export_filename);
  if($gui->export_filename == "")
  {
    $gui->export_filename = $exportFileName;
  }
  return $gui;  
}



/**
 * doExport
 *
 */
function doExport(&$argsObj,&$req_spec_mgr)
{
  $pfn = null;
  switch($argsObj->exportType)
  {
    case 'csv':
      $requirements_map = $req_spec_mgr->get_requirements($argsObj->req_spec_id);
      $pfn = "exportReqDataToCSV";
      $fileName = 'reqs.csv';
      $content = $pfn($requirements_map);
    break;

    case 'XML':
      $pfn = "exportReqSpecToXML";
      $fileName = 'reqs.xml';
      $content = TL_XMLEXPORT_HEADER;
      $optionsForExport['RECURSIVE'] = $argsObj->scope == 'items' ? false : true;
      $openTag = $argsObj->scope == 'items' ? "requirements>" : 'requirement-specification>';
      
      switch($argsObj->scope)
      {
        case 'tree':
          $reqSpecSet = $req_spec_mgr->getFirstLevelInTestProject($argsObj->tproject_id);
          $reqSpecSet = array_keys($reqSpecSet);
        break;
          
        case 'branch':
        case 'items':
          $reqSpecSet = array($argsObj->req_spec_id);
        break;
      }
      
      $content .= "<" . $openTag . "\n";
      if(!is_null($reqSpecSet))
      {
        foreach($reqSpecSet as $reqSpecID)
        {
          $content .= $req_spec_mgr->$pfn($reqSpecID,$argsObj->tproject_id,$optionsForExport);
        }
      }
      $content .= "</" . $openTag . "\n";
    break;
  }

  if ($pfn)
  {
    $fileName = is_null($argsObj->export_filename) ? $fileName : $argsObj->export_filename;
    downloadContentsToFile($content,$fileName);
    exit();
  }
}