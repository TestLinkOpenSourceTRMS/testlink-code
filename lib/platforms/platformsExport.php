<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Platforms definition export management
 *
 * @package   TestLink
 * @author    Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright   2005-2022, TestLink community 
 * @filesource  platformsExport.php
 * @link    http://www.testlink.org
 * @uses    config.inc.php
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('../../third_party/adodb_xml/class.ADODB_XML.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args($db);
$gui = initializeGui($db,$args);

switch($args->doAction) {
  case 'doExport':
    doExport($db,$gui->export_filename,$args->tproject_id);
  break;  
    
  default:
  break;  
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 *
 */
function init_args( &$dbH ) {
  $args = new stdClass();
  $iParams = 
    array("doAction" => array(tlInputParameter::STRING_N,0,50),
          "export_filename" => array(tlInputParameter::STRING_N,0,255),
          "tproject_id" => array(tlInputParameter::INT));
    
  R_PARAMS($iParams,$args);

  list($context,$env) = initContext();
  $args->tproject_id = $context->tproject_id;
  $args->tplan_id = $context->tplan_id;
  
  if( 0 == $args->tproject_id ) {
    throw new Exception("Unable to Get Test Project ID, Aborting", 1);
  }

  $args->tproject_name = '';
  $tables = tlDBObject::getDBTables(array('nodes_hierarchy'));
  $sql = "SELECT name FROM {$tables['nodes_hierarchy']}  
          WHERE id={$args->tproject_id}";
  $info = $dbH->get_recordset($sql);
  if( null != $info ) {
    $args->tproject_name = $info[0]['name'];
  }

  if(is_null($args->export_filename)) {
    $args->export_filename = $args->tproject_name . "-platforms.xml";
  } 
  $args->export_filename = trim(str_ireplace(" ", "",$args->export_filename));
  return $args;
}

/**
 *
 */
function initializeGui($dbH,&$argsObj) {
  list($add2args,$guiObj) = initUserEnv($dbH,$argsObj);

  $guiObj->activeMenu['projects'] = 'active'; 

  $guiObj->export_filename = trim($argsObj->export_filename);
  $guiObj->page_title = lang_get('export_platforms');
  $guiObj->do_it = 1;
  $guiObj->nothing_todo_msg = '';
  $guiObj->exportTypes = array('XML' => 'XML');

  $guiObj->tproject_id = $argsObj->tproject_id;
  $guiObj->goback_url = $_SESSION['basehref'] . 
    'lib/platforms/platformsView.php?tproject_id=' . $guiObj->tproject_id; 

  return $guiObj;
}

/*
  function: doExport()

  args: dbHandler
        filename: where to export
  
  returns: -

*/
function doExport(&$db,$filename,$tproject_id)
{
  $debugMsg = 'File:' . __FILE__ . ' - Function: ' . __FUNCTION__;
  $tables = tlObjectWithDB::getDBTables(array('platforms'));
  $adodbXML = new ADODB_XML("1.0", "UTF-8");

  $sql = "/* $debugMsg */ 
          SELECT name,notes,enable_on_design,enable_on_execution,is_open 
          FROM {$tables['platforms']} PLAT 
          WHERE PLAT.testproject_id=" . intval($tproject_id);
  
  $adodbXML->setRootTagName('platforms');
  $adodbXML->setRowTagName('platform');
  $content = $adodbXML->ConvertToXMLString($db->db, $sql);
  downloadContentsToFile($content,$filename);
  exit();
}

function checkRights(&$db,&$user)
{
  return $user->hasRightOnProj($db,"platform_view");
}