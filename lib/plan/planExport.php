<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Allows export in XML format of test plan in different way using $args->exportContent
 *
 * 'linkedItem' just linked elements
 *   linked platforms
 *	 linked test cases (minimal information)
 *
 * 'tree'
 *   complete plan contents: 
 *   to be defined 	
 * 	 
 * @package 	TestLink
 * @author 		Francisco Mancardi
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: planExport.php,v 1.11 2010/11/01 15:22:53 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions:
 * 20101101	 - franciscom - BUGID 3270 - improve file name.
 * 20101007 - franciscom - BUGID 3270 - Export Test Plan in XML Format
 *						   added management of exportContent	
 *
 **/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../functions/xml.inc.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tplan_mgr = new testplan($db);

$args = init_args();
$gui = initializeGui($args,$tplan_mgr);

if ($args->doExport)
{
	switch ($args->exportContent)
	{
		case 'linkedItems':
		$content = $tplan_mgr->exportLinkedItemsToXML($args->tplan_id);
		break;
		
		case 'tree':
		$context = array('platform_id' => $args->platform_id, 'build_id' => $args->build_id,
						 'tproject_id' => $args->tproject_id);
		$content = $tplan_mgr->exportTestPlanDataToXML($args->tplan_id,$context);
		break;
	}
	downloadContentsToFile($content,$gui->export_filename);
	exit();
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
    $args = new stdClass();
    $args->doExport = isset($_REQUEST['export']) ? $_REQUEST['export'] : null;
    $args->exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;

	// ------------------------------------------------------------------------------------------------
	// IMPORTANT NOTICE - 20101101 - franciscom
	// This page is called (@20101101) from two places
	//
	// From test plan management to export linked test cases & platforms
	// From execution to export test plan contents
	// I've found problems when using in 'execution feature' when I've choose to name hidden inputs
	// on tpl with a name different to that used on execSetResults.php.
	// This resulted on weird effects on execNavigator.tpl
	// Propably one option can be to save 'form_token'.
	// I've used a simple (and may be more suggest to new bugs in future):
	// maintain same names -> build_id instead of buildID, and so on.
	// A change was also needed on JS support function openExportTestPlan().
	// ------------------------------------------------------------------------------------------------
    $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
    $args->build_id = isset($_REQUEST['build_id']) ? intval($_REQUEST['build_id']) : 0;
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
    $args->platform_id = isset($_REQUEST['platform_id']) ? intval($_REQUEST['platform_id']) : 0;

    $args->export_filename=isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : null;
    $args->export_filename = trim($args->export_filename);
    
    // replace blank on name with _
    if( !is_null($args->export_filename) )
    { 
    	$args->export_filename = str_replace(' ','_',$args->export_filename);
    }
    
    $args->goback_url=isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;
    $args->exportContent=isset($_REQUEST['exportContent']) ? $_REQUEST['exportContent'] : 'linkedItems';


    return $args;
}


/**
 * 
 *
 */
function initializeGui(&$argsObj,&$tplanMgr)
{
	$info = $tplanMgr->get_by_id($argsObj->tplan_id);
	$add2name = '';
	
	$guiObj = new stdClass();
	$guiObj->do_it = 1;
	$guiObj->nothing_todo_msg = '';
	
	// If there is a platform setted -> use in name.
	if( $argsObj->platform_id > 0 )
	{
		$dummy = $tplanMgr->getPlatforms($argsObj->tplan_id,array('outputFormat' => 'mapAccessByID'));
		$add2name = '_' . str_replace(' ','_',$dummy[$argsObj->platform_id]['name']);
	}
	$guiObj->export_filename = $argsObj->exportContent . '_' . str_replace(' ','_',$info['name']) . $add2name . '.xml';
	$guiObj->exportTypes = array('XML' => 'XML');
	$guiObj->page_title = lang_get('export_test_plan');
	$guiObj->object_name = $info['name'];
	$guiObj->goback_url = !is_null($argsObj->goback_url) ? $argsObj->goback_url : ''; 

    $guiObj->tplan_id = intval($argsObj->tplan_id);
    $guiObj->tproject_id = intval($argsObj->tproject_id);
    $guiObj->platform_id = intval($argsObj->platform_id);
    $guiObj->build_id = intval($argsObj->build_id);
    $guiObj->exportContent = $argsObj->exportContent;

	return $guiObj;
}
?>
