<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	keywordsExport.php
 * @package 	TestLink
 * @copyright 	2005,2011 TestLink community 
 * @link 		http://www.teamst.org/index.php 
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("csv.inc.php");
require_once("xml.inc.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args($db);
checkRights($db,$_SESSION['currentUser'],$args);

switch ($args->doAction)
{
	case "do_export":
		$op = do_export($db,$smarty,$args);
		break;
}

$keyword = new tlKeyword();

$gui = new stdClass();
$gui->tproject_id = $args->tproject_id;
$gui->exportTypes = $keyword->getSupportedSerializationInterfaces();
$gui->action_descr = lang_get('export_keywords');
$gui->main_descr = lang_get('testproject') . TITLE_SEP . $args->tproject_name;
$gui->export_filename = is_null($args->export_filename) ? $args->tproject_name . '-keywords.xml' : $args->export_filename;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



function init_args(&$dbHandler)
{
	$_REQUEST=strings_stripSlashes($_REQUEST);
	$args = new stdClass();

	$iParams = array("doAction" => array(tlInputParameter::STRING_N,0,50),
					 "export_filename" => array(tlInputParameter::STRING_N,0,255),
					 "exportType" => array(tlInputParameter::STRING_N,0,255),
					 "tproject_id" => array(tlInputParameter::INT_N));
		
	R_PARAMS($iParams,$args);
	
	if( $args->tproject_id > 0 )
	{
		$treeMgr = new tree($dbHandler);
		$dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id);
		$args->tproject_name = $dummy['name'];
	}

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
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('mgt_view_key'),'and');
}
?>