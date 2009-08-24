<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsExport.php,v $
 *
 * @version $Revision: 1.10 $
 * @modified $Date: 2009/08/24 19:18:45 $ by $Author: schlundus $
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("csv.inc.php");
require_once("xml.inc.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();

switch ($args->doAction)
{
	case "do_export":
		$op = do_export($db,$smarty,$args);
		break;
}

$keyword = new tlKeyword();
$exportTypes = $keyword->getSupportedSerializationInterfaces();
$main_descr = lang_get('testproject') . TITLE_SEP . $args->testproject_name;
$fileName = is_null($args->export_filename) ? 'keywords.xml' : $args->export_filename;

$smarty = new TLSmarty();
$smarty->assign('export_filename',$fileName);
$smarty->assign('main_descr',$main_descr);
$smarty->assign('action_descr', lang_get('export_keywords'));
$smarty->assign('exportTypes',$exportTypes);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

function init_args()
{
	$iParams = array(
			"doAction" => array("GET",tlInputParameter::STRING_N,0,50),
			"export_filename" => array("POST", tlInputParameter::STRING_N,0,255),
			"exportType" => array("POST", tlInputParameter::STRING_N,0,255),
		);
	$args = new stdClass();
		
	$pParams = I_PARAMS($iParams,$args);

	$args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->testproject_name = $_SESSION['testprojectName'];

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
		$content = $tprojectMgr->$pfn($args->testproject_id);
		downloadContentsToFile($content,$args->export_filename);
		exit();
	}
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_view_key');
}
?>
