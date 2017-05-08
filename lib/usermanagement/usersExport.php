<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * User definition export management
 *
 * @package     TestLink
 * @author      Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright   2013,2017 TestLink community 
 * @filesource  usersExport.php
 * @link        http://www.teamst.org/index.php
 * @uses        config.inc.php
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('../../third_party/adodb_xml/class.ADODB_XML.php');

testlinkInitPage($db,false,false,"checkRights");

$args = init_args();
$gui = initializeGui($args);

switch( $args->doAction )
{
  case 'doExport':
    doExport($db,$gui->export_filename);
  break;  
    
  default:
  break;  
}

$tplCfg = templateConfiguration();
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($tplCfg->tpl);


/*
  function: init_args()

  args:
  
  returns: 

*/
function init_args()
{
  $args = new stdClass();
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $iParams = array("doAction" => array(tlInputParameter::STRING_N,0,50),
                   "export_filename" => array(tlInputParameter::STRING_N,0,100),
                   "goback_url" => array(tlInputParameter::STRING_N,0,2048));

  R_PARAMS($iParams,$args);
  $args->userID = $_SESSION['userID'];

  return $args;
}

/**
 *
 */
function initializeGui($argsObj)
{
  $gui = new stdClass();
  $gui->page_title = lang_get('export_cfields');
  $gui->do_it = 1;
  $gui->nothing_todo_msg = '';
  $gui->goback_url = !is_null($argsObj->goback_url) ? $argsObj->goback_url : ''; 
  $gui->export_filename = is_null($argsObj->export_filename) ? 'users.xml' : $argsObj->export_filename;
  $gui->exportTypes = array('XML' => 'XML');
  return $gui;
}


/*
  function: doExport()

  args: dbHandler
        filename: where to export
  
  returns: -

*/
function doExport(&$dbHandler,$filename)
{
  $adodbXML = new ADODB_XML("1.0", "ISO-8859-1");
  $adodbXML->setRootTagName('users');
  $adodbXML->setRowTagName('user');

  $tables = tlObjectWithDB::getDBTables(array('users'));
  $fieldSet = 'id,login,role_id,email,first,last,locale,' . 
              'default_testproject_id,active,expiration_date';
  $sql = " SELECT {$fieldSet} FROM {$tables['users']} ";

  $content = $adodbXML->ConvertToXMLString($dbHandler->db, $sql);
  downloadContentsToFile($content,$filename);
  exit();
}

function checkRights(&$db,&$user)
{
  return $user->hasRight($db,"mgt_users");
}