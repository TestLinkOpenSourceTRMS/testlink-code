<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Platforms definition export management
 *
 * @package   TestLink
 * @author    Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright   2005-2013, TestLink community 
 * @filesource  platformsExport.php
 * @link    http://www.teamst.org/index.php
 * @uses    config.inc.php
 *
 * @internal revisions
 * @since 1.9.9
 * 20130930 - franciscom - goback_url input parameter removed, to avoid XSS attack
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('../../third_party/adodb_xml/class.ADODB_XML.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($args);


switch($args->doAction)
{
  case 'doExport':
    doExport($db,$gui->export_filename,$args->testproject_id);
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
function init_args()
{
  $args = new stdClass();
  $iParams = array("doAction" => array(tlInputParameter::STRING_N,0,50),
                   "export_filename" => array(tlInputParameter::STRING_N,0,255));
    
  R_PARAMS($iParams,$args);
  $args->testproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  $args->testproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
  
  if(is_null($args->export_filename))
  {
    $args->export_filename = $args->testproject_name . "-platforms.xml";
  } 
  $args->export_filename = trim(str_ireplace(" ", "",$args->export_filename));
  return $args;
}

/**
 *
 */
function initializeGui(&$argsObj)
{
  $guiObj = new stdClass();
  $guiObj->export_filename = trim($argsObj->export_filename);
  $guiObj->page_title = lang_get('export_platforms');
  $guiObj->do_it = 1;
  $guiObj->nothing_todo_msg = '';
  $guiObj->exportTypes = array('XML' => 'XML');

  $guiObj->goback_url = $_SESSION['basehref'] . 'lib/platforms/platformsView.php'; 
  return $guiObj;
}

/*
  function: doExport()

  args: dbHandler
        filename: where to export
  
  returns: -

*/
function doExport(&$db,$filename,$testproject_id)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  $tables = tlObjectWithDB::getDBTables(array('platforms'));
  $adodbXML = new ADODB_XML("1.0", "UTF-8");
    
  $sql = "/* $debugMsg */ SELECT name,notes " .
         " FROM {$tables['platforms']} PLAT " .
         " WHERE PLAT.testproject_id=" . intval($testproject_id);
  
  $adodbXML->setRootTagName('platforms');
  $adodbXML->setRowTagName('platform');
  $content = $adodbXML->ConvertToXMLString($db->db, $sql);
  downloadContentsToFile($content,$filename);
  exit();
}

function checkRights(&$db,&$user)
{
  return $user->hasRight($db,"platform_view");
}