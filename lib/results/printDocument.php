<?php
/**
* 	TestLink Open Source Project - http://testlink.sourceforge.net/ 
*
*  @version 	$Id: printDocument.php,v 1.5 2008/03/01 21:41:27 schlundus Exp $
*  @author 	Martin Havlat
* 
* Shows the data that will be printed.
*
* rev :
*      20070509 - franciscom - added Contribution BUGID
*
*/
require_once('../../config.inc.php');
require_once("common.php");
require_once("print.inc.php");
testlinkInitPage($db);

$args = init_args();

// Important Notice:
// Elements in this array must be updated if $arrCheckboxes, in selectData.php is changed.
//
// 20070509 - Contribution - BUGID - 
// 20071209 - MHT"contribution - JMU: requirements and keywords added
$printingOptions = array ( 'toc' => 0,'body' => 0,'summary' => 0,'header' => 0,
						               'passfail' => 0, 'author' => 0, 'requirement' => 0, 'keyword' => 0);

foreach($printingOptions as $opt => $val)
{
	$printingOptions[$opt] = (isset($_REQUEST[$opt]) && ($_REQUEST[$opt] == 'y'));
}					

$tck_map = null;
$map_node_tccount = array();

$tproject_mgr = new testproject($db);
$tree_manager = &$tproject_mgr->tree_manager;

$hash_descr_id = $tree_manager->get_available_node_types();
$hash_id_descr = array_flip($hash_descr_id);
$status_descr_code = config_get('tc_status');
$status_code_descr = array_flip($status_descr_code);

$decoding_hash  =array('node_id_descr' => $hash_id_descr,
                     'status_descr_code' =>  $status_descr_code,
                     'status_code_descr' =>  $status_code_descr);


$test_spec = $tree_manager->get_subtree($args->itemID,
										array(
											'testplan'=>'exclude me',
											'requirement_spec'=>'exclude me',
											'requirement'=>'exclude me'),
											array('testcase'=>'exclude my children',
											'requirement_spec'=> 'exclude my children'),
											null,null,RECURSIVE_MODE
										);


$tree = null;
$code = null;					
$item_type = $args->level;

switch ($args->print_scope)
{
	case 'testproject':
		if ($item_type == 'testproject')
		{
			$tree = &$test_spec;
			$printingOptions['title'] = '';
		}
		else if ($item_type == 'testsuite')
		{
			$tsuite = new testsuite($db);
			$tInfo = $tsuite->get_by_id($args->itemID);
			$tInfo['childNodes'] = isset($test_spec['childNodes']) ? $test_spec['childNodes'] : null;
			$tree['childNodes'] = array($tInfo);
			$printingOptions['title'] = isset($tInfo['name']) ? $tInfo['name'] : $args->tproject_name;
		}
		break;

	case 'testplan':
		if ($item_type == 'testproject')
		{
			$tplan_mgr = new testplan($db);
			$tp_tcs = $tplan_mgr->get_linked_tcversions($args->tplan_id);
			$tree = &$test_spec;
			if (!$tp_tcs)
				$tree['childNodes'] = null;
			$testcase_count = prepareNode($db,$tree,$decoding_hash,$map_node_tccount,
			                     $tck_map,$tp_tcs,SHOW_TESTCASES);
			$printingOptions['title'] = $args->tproject_name;
		}
		else if ($item_type == 'testsuite')
		{
			$tsuite = new testsuite($db);
			$tInfo = $tsuite->get_by_id($args->itemID);
			$tplan_mgr = new testplan($db);
			$tp_tcs = $tplan_mgr->get_linked_tcversions($args->tplan_id);

			$tInfo['node_type_id'] = $hash_descr_id['testsuite'];
			$tInfo['childNodes'] = isset($test_spec['childNodes']) ? $test_spec['childNodes'] : null;
			$testcase_count = prepareNode($db,$tInfo,$decoding_hash,$map_node_tccount,
			                     $tck_map,$tp_tcs,SHOW_TESTCASES);
			$printingOptions['title'] = isset($tInfo['name']) ? $tInfo['name'] : $args->tproject_name;

			$tree['childNodes'] = array($tInfo);
		}
	break;
}

if($tree)
{
	$tree['name'] = $args->tproject_name;
	$tree['id'] = $args->tproject_id;
	$tree['node_type_id'] = $hash_descr_id['testproject'];
	
	switch ($args->print_scope)
	{
		case 'testproject':
			$code = renderTestSpecTreeForPrinting($db,$tree,$item_type,$printingOptions,null,0,1,$args->user_id);
			break;
	
		case 'testplan':
			$code = renderTestPlanForPrinting($db,$tree,$item_type,$printingOptions,null,0,1,
		                $args->user_id,$args->tplan_id);
		break;
	}
}

// add MS Word header 
if ($args->format == 'msword')
{
	header("Content-Disposition: inline; filename=testplan.doc");
	header("Content-Description: PHP Generated Data");
	header("Content-type: application/vnd.ms-word; name='My_Word'");
	flush();
}
echo $code;

function init_args()
{
	$args = new stdClass();
	$args->print_scope = $_REQUEST['print_scope'];
	$args->level = isset($_REQUEST['level']) ?  $_REQUEST['level'] : null;
	$args->format = isset($_REQUEST['format']) ? $_REQUEST['format'] : null;
	$args->itemID = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xxx';
	$args->tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
	$args->user_id = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;

	return $args;
}
?>