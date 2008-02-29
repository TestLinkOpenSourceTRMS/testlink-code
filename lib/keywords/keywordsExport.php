<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsExport.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2008/02/29 23:19:29 $ by $Author: schlundus $
 *
 * test case and test suites export
 *
 * 20070113 - franciscom - added logic to create message when there is 
 *                         nothing to export.
 *
 * 20061118 - franciscom - using different file name, depending the
 *                         type of exported elements.
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("csv.inc.php");
require_once("xml.inc.php");
require_once("keyword.class.php");
testlinkInitPage($db);

$template_dir = 'keywords/';

$args = init_args();

$main_descr = lang_get('testproject') . TITLE_SEP . $args->testproject_name;
$fileName = is_null($args->export_filename) ? 'keywords.xml' : $args->export_filename;

switch ($args->doAction)
{
	case "export":
		$op = export($smarty,$args);
		break;

	case "do_export":
		$op = do_export($db,$smarty,$args);
		break;
}

$keyword = new tlKeyword();
$exportTypes = $keyword->getSupportedSerializationInterfaces();

$smarty = new TLSmarty();
$smarty->assign('export_filename',$fileName);
$smarty->assign('main_descr',$main_descr);
$smarty->assign('action_descr', lang_get('export_keywords'));
$smarty->assign('exportTypes',$exportTypes);
$smarty->display($template_dir . 'keywordsExport.tpl');

function init_args()
{
	$args = new stdClass();
	$args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : null;
	$args->exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;
	$args->export_filename = isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : null;

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
function export(&$smarty,&$args)
{
	$ret = new stdClass();
	$ret->template = 'keywordsExport.tpl';
	$ret->status = 1;

	return $ret;
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

		// why this exit() ?
		// If we don't use it, we will find in the exported file
		// the contents of the smarty template.
		exit();
	}
}

?>
