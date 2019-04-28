<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Scope: Import keywords page
 *
 * @filesource  keywordsImport.php
 * @package     TestLink
 * @copyright   2005,2015 TestLink community 
 * @link        http://www.testlink.org/
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('csv.inc.php');
require_once('xml.inc.php');

testlinkInitPage($db);
$templateCfg = templateConfiguration();

$args = init_args($db);
$gui = initializeGui($args);

if(!$gui->msg && $args->UploadFile)
{
  if(($args->source != 'none') && ($args->source != ''))
  { 
    if (move_uploaded_file($args->source, $args->dest))
    {
      $pfn = null;
      switch($args->importType)
      {
        case 'iSerializationToCSV':
          $pfn = "importKeywordsFromCSV";
          break;
 
        case 'iSerializationToXML':
          $pfn = "importKeywordsFromXMLFile";
          break;
      }
 
      if($pfn)
      {
        $tproject = new testproject($db);
        $result = $tproject->$pfn($args->tproject_id,$args->dest);
        if ($result != tl::OK)
        {  
          $gui->msg = lang_get('wrong_keywords_file'); 
        }
        else
        {
          header("Location: keywordsView.php?tproject_id={$gui->tproject_id}");
          exit();   
        }
      }
      @unlink($args->dest);
    }
  } 
  else
  {  
    $gui->msg = lang_get('please_choose_keywords_file');
  }
}
      
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * @return object returns the arguments for the page
 */
function init_args(&$dbHandler)
{
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $ipcfg = array("UploadFile" => array(tlInputParameter::STRING_N,0,1),
                 "importType" => array(tlInputParameter::STRING_N,0,100),
                 "tproject_id" => array(tlInputParameter::INT_N));

  $args = new stdClass();
  R_PARAMS($ipcfg,$args);

  if( $args->tproject_id <= 0 )
  {
    throw new Exception(" Error Invalid Test Project ID", 1);
  }

  // Check rights before doing anything else
  // Abort if rights are not enough 
  $user = $_SESSION['currentUser'];
  $env['tproject_id'] = $args->tproject_id;
  $env['tplan_id'] = 0;
  
  $check = new stdClass();
  $check->items = array('mgt_modify_key');
  $check->mode = 'and';
  checkAccess($dbHandler,$user,$env,$check);
 
  $tproj_mgr = new testproject($dbHandler);
  $dm = $tproj_mgr->get_by_id($args->tproject_id,array('output' => 'name'));
  $args->tproject_name = $dm['name'];

  $args->UploadFile = ($args->UploadFile != "") ? 1 : 0; 
  $args->fInfo = isset($_FILES['uploadedFile']) ? $_FILES['uploadedFile'] : null;
  $args->source = isset($args->fInfo['tmp_name']) ? $args->fInfo['tmp_name'] : null;
  $args->dest = TL_TEMP_PATH . session_id() . "-importkeywords." . $args->importType;

  return $args;
}

/**
 *
 */
function initializeGui(&$argsObj)
{
  $gui = new stdClass();
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->tproject_name = $argsObj->tproject_name;

  $gui->main_descr = lang_get('testproject') . TITLE_SEP . $gui->tproject_name;
  $gui->viewUrl = "lib/keywords/keywordsView.php?tproject_id={$gui->tproject_id}";
  $gui->import_type_selected = $argsObj->importType;
  $gui->msg = getFileUploadErrorMessage($argsObj->fInfo);

  $tlkw = new tlKeyword();
  $gui->importTypes = $tlkw->getSupportedSerializationInterfaces();
  $gui->keywordFormatStrings = $tlkw->getSupportedSerializationFormatDescriptions();

  $fslimit = config_get('import_file_max_size_bytes');
  $gui->fileSizeLimitMsg = 
    sprintf(lang_get('max_file_size_is'), $fslimit/1024 . ' KB ');
  $gui->importLimit = $fslimit;




  return $gui;
}