<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsExport.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/12/08 18:11:52 $ by $Author: franciscom $
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

$template_dir='keywords/';

$args=init_args();

$main_descr=lang_get('testproject') . TITLE_SEP . $args->testproject_name;
$action_descr=lang_get('export_keywords');


$do_it = 1;
$nothing_todo_msg = '';
$check_children = 0;

$fileName = 'keywords.xml';
$fileName = is_null($args->export_filename) ? $fileName : $args->export_filename;

switch ($args->doAction)
{
  case "export":
  $op=export($smarty,$args);
  break;

  case "do_export":
  $tprojectMgr = new testproject($db);
  $op=do_export($smarty,$args,$tprojectMgr);
  break;

} // switch


$keywordMgr = new tlKeyword();
$exportTypes=$keywordMgr->getSupportedSerializationInterfaces();

$smarty = new TLSmarty();


$smarty->assign('export_filename',$fileName);
$smarty->assign('main_descr',$main_descr);
$smarty->assign('action_descr', $action_descr);
$smarty->assign('exportTypes',$exportTypes);
$smarty->display($template_dir . 'keywordsExport.tpl');
?>


<?php
function init_args()
{
  $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : null;
  $args->exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;
  $args->export_filename=isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : null;

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
  $ret->template='keywordsExport.tpl';
  $ret->status = 1;
  return $ret;
}



/*
  function: do_export
            generate export file

  args :
  
  returns: 

*/
function do_export(&$smarty,&$args,&$tproject_mgr)
{
	$pfn = null;
	switch($args->exportType)
	{
		case 'iSerializationToCSV':
			$pfn = "exportKeywordDataToCSV";
			$fileName = 'keywords.csv';
			break;
	
		case 'iSerializationToXML':
			$pfn = "exportKeywordDataToXML";
			$fileName = 'keywords.xml';
			break;
	}
	if ($pfn)
	{
		$content = $tproject_mgr->$pfn($args->testproject_id);
		downloadContentsToFile($content,$fileName);

		// why this exit() ?
		// If we don't use it, we will find in the exported file
		// the contents of the smarty template.
		exit();
	}
}

?>
