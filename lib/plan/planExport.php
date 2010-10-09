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
 * @version    	CVS: $Id: planExport.php,v 1.8 2010/10/09 18:44:00 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions:
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
		// need to be developed
		$content = $tplan_mgr->exportTestPlanDataToXML($args->tplan_id,$args->platform_id);
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
    $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;

    $args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
    if( $args->tplan_id == 0 )
    {
    	$args->tplan_id = isset($_REQUEST['tplanID']) ? intval($_REQUEST['tplanID']) : 0;
    }

    $args->platform_id = isset($_REQUEST['platform_id']) ? intval($_REQUEST['platform_id']) : 0;
    if( $args->platform_id == 0 )
    {
    	$args->platform_id = isset($_REQUEST['platformID']) ? intval($_REQUEST['platformID']) : 0;
	}
    
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

	$guiObj = new stdClass();
	$guiObj->do_it = 1;
	$guiObj->nothing_todo_msg = '';
	// $guiObj->export_filename = 'export_' . str_replace(' ','_',$info['name']) . '.xml';
	$guiObj->export_filename = $argsObj->exportContent . '_' . str_replace(' ','_',$info['name']) . '.xml';
	$guiObj->exportTypes = array('XML' => 'XML');
	$guiObj->page_title = lang_get('export_test_plan');
	$guiObj->object_name = $info['name'];
	$guiObj->goback_url = !is_null($argsObj->goback_url) ? $argsObj->goback_url : ''; 
    $guiObj->tplan_id = intval($argsObj->tplan_id);
    $guiObj->platform_id = intval($argsObj->platform_id);
    $guiObj->exportContent = $argsObj->exportContent;

	return $guiObj;
}
?>
