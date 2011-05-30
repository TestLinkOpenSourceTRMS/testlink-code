<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Platforms definition export management
 *
 * @filesource	platformsExport.php
 * @package 	TestLink
 * @author 		Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright 	2005-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 * @uses 		config.inc.php
 *
 * @internal revisions
 *	
 * 20100227 - franciscom - BUGID 0003229
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('../../third_party/adodb_xml/class.ADODB_XML.php');
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$args = init_args($db);
checkRights($db,$_SESSION['currentUser'],$args);

$gui = new stdClass();
$gui->page_title = lang_get('export_platforms');
$gui->do_it = 1;
$gui->nothing_todo_msg = '';
$gui->goback_url = is_null($args->goback_url) ? '' : $args->goback_url; 
$gui->export_filename = trim($args->export_filename);
$gui->exportTypes = array('XML' => 'XML');
$gui->tproject_id = $args->tproject_id;

switch($args->doAction)
{
    case 'doExport':
	    doExport($db,$gui->export_filename,$args->tproject_id);
	    break;  
    
    default:
    	break;  
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args(&$dbHandler)
{
	$args = new stdClass();
	$_REQUEST=strings_stripSlashes($_REQUEST);

	$iParams = array("doAction" => array(tlInputParameter::STRING_N,0,50),
			         "export_filename" => array(tlInputParameter::STRING_N,0,255),
					 "goback_url" => array(tlInputParameter::STRING_N,0,2048),
					 "tproject_id" => array(tlInputParameter::INT));
		
	R_PARAMS($iParams,$args);

	$args->tproject_name = '';
	if( $args->tproject_id > 0 )
	{
		$treeMgr = new tree($dbHandler);
		$dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id);
		$args->tproject_name = $dummy['name'];
	}

	
	if(is_null($args->export_filename))
	{
		$args->export_filename = $args->tproject_name . "-platforms.xml";
	}	
    $args->export_filename = trim(str_ireplace(" ", "",$args->export_filename));

	$target = $args->goback_url;
	if( strlen(trim($target)) > 0)
	{
		$target .= (strpos($target,"?") === false) ? "?" : "&"; 
		$target .= "tproject_id={$args->tproject_id}";
	}
	$args->goback_url = $target;

    return $args;
}

/*
  function: doExport()

  args: dbHandler
        filename: where to export
  
  returns: -

*/
function doExport(&$db,$filename,$tproject_id)
{
	$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
	$tables = tlObjectWithDB::getDBTables(array('platforms'));
    $adodbXML = new ADODB_XML("1.0", "UTF-8");
    
    $sql = "/* $debugMsg */ SELECT name,notes " .
		   " FROM {$tables['platforms']} PLAT " .
		   " WHERE PLAT.testproject_id={$tproject_id}";
	
    $adodbXML->setRootTagName('platforms');
    $adodbXML->setRowTagName('platform');
    $content = $adodbXML->ConvertToXMLString($db->db, $sql);
    downloadContentsToFile($content,$filename);
	exit();
}

function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('platform_view'),'and');
}
?>