<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Custom Fields definition export management
 *
 * @package   TestLink
 * @author    Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright   2005-2009, TestLink community 
 * @version     CVS: $Id: cfieldsExport.php,v 1.4 2010/03/15 20:23:09 franciscom Exp $
 * @link    http://www.teamst.org/index.php
 * @uses    config.inc.php
 *
 * @internal Revisions:
 * 20100315 - franciscom - added tlInputParameter() on init_args + goback managament
 * 20090719 - franciscom - db table prefix management   
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('../../third_party/adodb_xml/class.ADODB_XML.php');

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();
$args = init_args();

$gui = new stdClass();
$gui->page_title = lang_get('export_cfields');
$gui->do_it = 1;
$gui->nothing_todo_msg = '';
$gui->goback_url = !is_null($args->goback_url) ? $args->goback_url : ''; 
$gui->export_filename = is_null($args->export_filename) ? 'customFields.xml' : $args->export_filename;
$gui->exportTypes = array('XML' => 'XML');

switch( $args->doAction )
{
    case 'doExport':
      doExport($db,$gui->export_filename);
      break;  
    
    default:
      break;  
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


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



/*
  function: doExport()

  args: dbHandler
        filename: where to export
  
  returns: -

*/
function doExport(&$dbHandler,$filename)
{
  $tables = tlObjectWithDB::getDBTables(array('custom_fields','cfield_node_types'));

  // To solve issues with MAC OS
  $tmp = (PHP_OS == 'Darwin') ? config_get('temp_dir') : null;

  $adodbXML = new ADODB_XML("1.0", "ISO-8859-1",$tmp);
  $sql = " SELECT name,label,type,possible_values,default_value,valid_regexp, " .
         " length_min,length_max,show_on_design,enable_on_design,show_on_execution," .
         " enable_on_execution,show_on_testplan_design,enable_on_testplan_design, " .
         " node_type_id " .
         " FROM {$tables['custom_fields']} CF,{$tables['cfield_node_types']} " .
         " WHERE CF.id=field_id ";
  
  $adodbXML->setRootTagName('custom_fields');
  $adodbXML->setRowTagName('custom_field');

  $content = $adodbXML->ConvertToXMLString($dbHandler->db, $sql);
  downloadContentsToFile($content,$filename);
  exit();
}

function checkRights(&$db,&$user)
{
  return $user->hasRight($db,"cfield_view");
}
?>