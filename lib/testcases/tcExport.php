<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tcExport.php,v $
 *
 * @version $Revision: 1.14 $
 * @modified $Date: 2010/03/15 22:21:20 $ by $Author: franciscom $
 *
 * Scope: test case and test suites export
 * 
 * Revisions:
 *
 * 20100315 - franciscom - improvements on goback management
 * 20100315 - amitkhullar - Added checkboxes options for Requirements and CFields for Export.
 * 20081027 - martin - cleanup
 * 20070113 - franciscom - added logic to create message when there is nothing to export.
 *
 * 20061118 - franciscom - using different file name, depending type of exported elements.
 *
 * ----------------------------------------------------------------------------------- */
/** @TODO martin: csv export is not available now - get it back */

require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../functions/xml.inc.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tree_mgr = new tree($db);
$args = init_args();

$gui = new stdClass();
$gui->do_it = 1;
$gui->nothing_todo_msg = '';
$gui->export_filename = '';
$gui->page_title = '';
$gui->object_name='';
$gui->goback_url = !is_null($args->goback_url) ? $args->goback_url : ''; 

$exporting_just_one_tc = 0;
$node_id = $args->container_id;
$check_children = 0;

if($args->useRecursion)
{
	// Exporting situations:
	// All test suites in test project
	// One test suite 
	$gui->page_title=lang_get('title_tsuite_export');

	$gui->export_filename = 'testsuites.xml';
	if($node_id == $args->tproject_id)
	{
		$gui->page_title=lang_get('title_tsuite_export_all');
		$gui->export_filename = 'all_testsuites.xml';
		$check_children=1; 
		$gui->nothing_todo_msg=lang_get('no_testsuites_to_export');
	}
} 
else
{
	// Exporting situations:
	// All test cases in test suite.
	// One test case.
	$exporting_just_one_tc = ($args->tcase_id && $args->tcversion_id);
	$gui->export_filename = 'testcases.xml';
	
	if($exporting_just_one_tc)
	{
		$node_id = $args->tcase_id;
		$gui->page_title = lang_get('title_tc_export');
	}
	else
	{
		$gui->page_title = lang_get('title_tc_export_all');
		$check_children = 1;
		$gui->nothing_todo_msg = lang_get('no_testcases_to_export');
	}
}
$gui->export_filename = is_null($args->export_filename) ? $gui->export_filename : $args->export_filename;


if( $check_children )
{
	// Check if there is something to export
	$children=$tree_mgr->get_children($node_id, 
	                                  array("testplan" => "exclude_me",
	                                        "requirement_spec" => "exclude_me",
	                                        "requirement" => "exclude_me"));	
	
	$gui->nothing_todo_msg='';
	if(count($children)==0)
	{
		$gui->do_it = 0 ;
	}
}
$node = $tree_mgr->get_node_hierarchy_info($node_id);


if ($args->doExport)
{
	$tcase_mgr = new testcase($db);
	$tsuite_mgr = new testsuite($db);
	
	$optExport = array('REQS' => $args->exportReqs, 'CFIELDS' => $args->exportCFields,
	                   'KEYWORDS' => $args->exportKeywords, 'RECURSIVE' => $args->useRecursion);
	$pfn = null;
	switch($args->exportType)
	{
		case 'XML':
		    $pfn = 'exportTestSuiteDataToXML';
			if ($exporting_just_one_tc)
			{
				$pfn = 'exportTestCaseDataToXML';
			}
			break;
	}
	if ($pfn)
	{
		if ($exporting_just_one_tc)
		{
			$optExport['ROOTELEM'] = "<testcases>{{XMLCODE}}</testcases>";
			$content = $tcase_mgr->$pfn($args->tcase_id,$args->tcversion_id,$args->tproject_id,null,$optExport);
		}	
		else
		{
			$content = TL_XMLEXPORT_HEADER;
			$content .= $tsuite_mgr->$pfn($args->container_id,$args->tproject_id,$optExport);
		}
			
		downloadContentsToFile($content,$gui->export_filename);
		exit();
	}
}

if( $args->useRecursion )
{
  // we are working on a testsuite
  $obj_mgr = new testsuite($db);
}
else
{
  $obj_mgr = new testcase($db);
}

$gui->object_name=$node['name'];
$gui->exportTypes=$obj_mgr->get_export_file_types();
$gui->tproject_name=$args->tproject_name;
$gui->tproject_id=$args->tproject_id;
$gui->tcID=$args->tcase_id; 
$gui->useRecursion=$args->useRecursion ? 1 : 0;
$gui->tcVersionID=$args->tcversion_id;
$gui->containerID=$args->container_id;

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
    $args->exportReqs = isset($_REQUEST['exportReqs']) ? 1 : 0;
    $args->exportCFields = isset($_REQUEST['exportCFields']) ? 1 : 0;
    $args->exportKeywords = isset($_REQUEST['exportKeywords']) ? 1 : 0;
    $args->exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;
    $args->tcase_id = isset($_REQUEST['testcase_id']) ? intval($_REQUEST['testcase_id']) : 0;
    $args->tcversion_id = isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : 0;
    $args->container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
    $args->useRecursion = isset($_REQUEST['useRecursion']) ? $_REQUEST['useRecursion'] : false;
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = $_SESSION['testprojectName'];
    $args->export_filename=isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : null;

	// 20100315 - franciscom
    $args->goback_url=isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;

    return $args;
}
?>
