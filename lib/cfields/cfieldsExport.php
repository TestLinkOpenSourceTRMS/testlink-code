<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: cfieldsExport.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2008/09/23 06:59:59 $ by $Author: franciscom $
 *
 * custom fields export
 *
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once('../../third_party/adodb_xml/class.ADODB_XML.php');

testlinkInitPage($db);
$gui=new stdClass();
$templateCfg = templateConfiguration();

$gui->page_title=lang_get('export_cfields');
$gui->do_it=1;
$gui->nothing_todo_msg = '';

$args=init_args();

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
  $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : null;
  $args->export_filename=isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : null;
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
    $adodbXML = new ADODB_XML("1.0", "ISO-8859-1");
    $sql=" SELECT name,label,type,possible_values,default_value,valid_regexp, " .
		     " length_min,length_max,show_on_design,enable_on_design,show_on_execution," .
		     " enable_on_execution,show_on_testplan_design,enable_on_testplan_design, " .
		     " node_type_id " .
		     " FROM custom_fields,cfield_node_types " .
		     " WHERE custom_fields.id=field_id ";
  
    $adodbXML->setRootTagName('custom_fields');
    $adodbXML->setRowTagName('custom_field');
    $content=$adodbXML->ConvertToXMLString($dbHandler->db, $sql);
    downloadContentsToFile($content,$filename);
		exit();
}
?>