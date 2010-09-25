<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Allows export in XML format of:
 *
 * . complete plan contents: 
 *   linked platforms
 *	 linked test cases (minimal information)
 *
 * 	 
 * @package 	TestLink
 * @author 		Francisco Mancardi
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: planExport.php,v 1.3 2010/09/25 17:54:47 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions:
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
	$content = $tplan_mgr->exportLinkedItemsToXML($args->tplan_id);
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
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
    $args->tproject_id = $_REQUEST['tproject_id'];

    $args->export_filename=isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : null;
    $args->goback_url=isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;

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
	$guiObj->export_filename = 'export_' . $info['name'] . '.xml';
	$guiObj->exportTypes = array('XML' => 'XML');
	$guiObj->page_title = lang_get('export_test_plan');
	$guiObj->object_name = $info['name'];
	$guiObj->goback_url = !is_null($argsObj->goback_url) ? $argsObj->goback_url : ''; 
    $guiObj->tplan_id = intval($argsObj->tplan_id);
	return $guiObj;
}
?>
