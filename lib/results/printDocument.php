<?php
/**
* 	TestLink Open Source Project - http://testlink.sourceforge.net/ 
*
*  @version 	$Id: printDocument.php,v 1.2 2007/12/10 22:59:45 havlat Exp $
*  @author 	Martin Havlat
* 
* Shows the data that will be printed.
*
* rev :
*      20070509 - franciscom - added Contribution BUGID
*
*/
require('../../config.inc.php');
require_once("common.php");
require_once("print.inc.php");
testlinkInitPage($db);

$print_scope = $_REQUEST['print_scope'];
// $type = isset($_GET['edit']) ?  $_GET['edit'] : null;

$level = isset($_GET['level']) ?  $_GET['level'] : null;
$format = isset($_GET['format']) ? $_GET['format'] : null;
$dataID = isset($_GET['id']) ? intval($_GET['id']) : 0;

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xxx';
$tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$user_id = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;

// Important Notice:
// Elements in this array must be updated if $arrCheckboxes, in selectData.php is changed.
//
// 20070509 - Contribution - BUGID - 
// 20071209 - MHT"contribution - JMU: requirements and keywords added
$printingOptions = array ( 'toc' => 0,'body' => 0,'summary' => 0,'header' => 0,
						               'passfail' => 0, 'author' => 0, 'requirement' => 0, 'keyword' => 0);

foreach($printingOptions as $opt => $val)
{
	$printingOptions[$opt] = (isset($_GET[$opt]) && ($_GET[$opt] == 'y'));
}					

$tck_map = null;
$map_node_tccount=array();

$tproject_mgr = new testproject($db);
$tree_manager = &$tproject_mgr->tree_manager;

// 20071111 - franciscom
$test_spec = $tree_manager->get_subtree($dataID,
	                                      array('testplan'=>'exclude me',
	                                              'requirement_spec'=>'exclude me',
	                                              'requirement'=>'exclude me'),
												                array('testcase'=>'exclude my children',
												                        'requirement_spec'=> 'exclude my children'),
                                        null,null,RECURSIVE_MODE);

$tree = null;
$code = null;					

$item_type=$level;

switch ($print_scope)
{
  case 'testproject':
    if ($level == 'testproject')
    {
    	$tree = &$test_spec;
    	$tree['name'] = $tproject_name;
    	$tree['id'] = $tproject_id;
    	$tree['node_type_id'] = 1;
    	$printingOptions['title'] = '';
    }
    else if ($level == 'testsuite')
    {
    	$tsuite = new testsuite($db);
    	$tInfo = $tsuite->get_by_id($dataID);
    	$tInfo['childNodes'] = isset($test_spec['childNodes']) ? $test_spec['childNodes'] : null;
    	
    	//build the testproject node
    	$tree['name'] = $tproject_name;
    	$tree['id'] = $tproject_id;
    	$tree['node_type_id'] = 1;
    	$tree['childNodes'] = array($tInfo);
    	$printingOptions['title'] = isset($tInfo['name']) ? $tInfo['name'] : $tproject_name;
    }
  break;
  
  case 'testplan':
    if ($level == 'testproject')
    {
    	$tplan_mgr = new testplan($db);
    	$tp_tcs = $tplan_mgr->get_linked_tcversions($tplan_id);
    	
    	$hash_descr_id = $tree_manager->get_available_node_types();
    	$hash_id_descr = array_flip($hash_descr_id);
    	$tree = &$test_spec;
    	$tree['name'] = $tproject_name;
    	$tree['id'] = $tproject_id;
    	$tree['node_type_id'] = 1;
    
    	if (!$tp_tcs)
    	{
    		$tree['childNodes'] = null;
    	}
    	$testcase_count = prepareNode($db,$tree,$hash_id_descr,$map_node_tccount,
    	                              $tck_map,$tp_tcs,SHOW_TESTCASES);
    	$printingOptions['title'] = $tproject_name;
    	
    }
    else if ($level == 'testsuite')
    {
    	$tsuite = new testsuite($db);
    	$tInfo = $tsuite->get_by_id($dataID);
    	$tplan_mgr = new testplan($db);
    	$tp_tcs = $tplan_mgr->get_linked_tcversions($tplan_id);
    	
    	$hash_descr_id = $tree_manager->get_available_node_types();
    	$hash_id_descr = array_flip($hash_descr_id);
    	
    	$tInfo['node_type_id'] = $hash_descr_id['testsuite'];
    	$tInfo['childNodes'] = isset($test_spec['childNodes']) ? $test_spec['childNodes'] : null;
    	$testcase_count = prepareNode($db,$tInfo,$hash_id_descr,$map_node_tccount,
    	                              $tck_map,$tp_tcs,SHOW_TESTCASES);
    	$printingOptions['title'] = isset($tInfo['name']) ? $tInfo['name'] : $tproject_name;
    	
    	$tree['name'] = $tproject_name;
    	$tree['id'] = $tproject_id;
    	$tree['node_type_id'] = 1;
    	$tree['childNodes'] = array($tInfo);
    }
  break;
    
} // switch  


if($tree)
{
  switch ($print_scope)
  {
    case 'testproject':
  	$code = renderTestSpecTreeForPrinting($db,$tree,$item_type,$printingOptions,null,0,1,$user_id);
    break;
  
    case 'testplan':
  	$code = renderTestPlanForPrinting($db,$tree,$item_type,
  	                                  $printingOptions,null,0,1,$user_id,$tplan_id);
    break;
  }  
}


// add MS Word header 
if ($format == 'msword')
{
	header("Content-Disposition: inline; filename=testplan.doc");
	header("Content-Description: PHP Generated Data");
	header("Content-type: application/vnd.ms-word; name='My_Word'");
	flush();
}
echo $code;
?>
