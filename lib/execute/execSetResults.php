<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execSetResults.php,v $
 *
 * @version $Revision: 1.24 $
 * @modified $Date: 2006/03/29 14:34:30 $ $Author: franciscom $
 *
 * @author Martin Havlat
 *
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("../../lib/functions/builds.inc.php");
require_once '../../lib/functions/tree.class.php';     // 20060326 - franciscom
require_once '../../lib/functions/testplan.class.php'; // 20060326 - franciscom
require_once '../../lib/functions/testcase.class.php'; // 20060326 - franciscom

testlinkInitPage($db);

// 20060326 - franciscom
$tree_mgr = New tree($db);
$tplan_mgr = New testplan($db);
$tcase_mgr = New testcase($db);



$testdata = array();
$submitResult = null;

$_REQUEST = strings_stripSlashes($_REQUEST);
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
//$buildID = isset($_REQUEST['build']) ? intval($_REQUEST['build']) : 0;
$level = isset($_REQUEST['level']) ? $_REQUEST['level'] : '';
$owner = isset($_REQUEST['owner']) ? $_REQUEST['owner'] : '';

$keyword = 'All';
if( isset($_REQUEST['keyword']) )
{
	$keyword = $db->prepare_string($keyword);
}
if (isset($_REQUEST['submitTestResults']))
{
	// 20060908 - scs - fixed 90
	//$submitResult = editTestResults($db,$_SESSION['user'],$_REQUEST,$_GET['build']);
}

echo "<pre>debug" ; print_r($_REQUEST); echo "</pre>";

/*
$tpID = $_SESSION['testPlanId'];
$builds = getBuilds($db,$tpID, " ORDER BY build.name ");
$buildName = isset($builds[$buildID]) ? $builds[$buildID] : '';
*/

// -------------------------------------------------------------------------------------------
// 20060207 - franciscom - BUGID 0000303 - Solution by: scorpfromhell 
// Added to set Test Results editable by comparing themax Build ID and the requested Build ID.			
$editTestResult = "yes";
//$allbuilds = getBuilds($tpID, 'ORDER BY build.id DESC');
//$latestBuild = array_keys($allbuilds);
//$latestBuild = $latestBuild[0];

if(($latestBuild > $buildID) && !(config_get('edit_old_build_results')))
{
	$editTestResult = "no";
}
// -------------------------------------------------------------------------------------------

/*
$sql = " SELECT CAT.id AS cat_id, MGTCAT.name AS cat_name, " .
       " TC.id AS tcid, title, summary, steps, exresult, keywords,mgttcid,version " .
       " FROM  component COMP, category CAT, mgtcategory MGTCAT, testcase TC " .
       " WHERE COMP.id = CAT.compid " .
       " AND   MGTCAT.id = CAT.mgtcatid " .
       " AND   CAT.id = TC.catid  ";

if ($keyword != 'All')
{
	$sql .= " AND (TC.keywords LIKE '%,{$keyword},%' OR TC.keywords like '{$keyword},%')";
}	
if($level == 'component')
{ 
	$sql .= " AND   COMP.id = " . $id;
	$sql .= " ORDER BY MGTCAT.CATorder, TCorder, TC.id ASC";
}
else if($level == 'category')
{ 
	$sql .= " AND CAT.id = " . $id ;
	$sql .= " ORDER BY MGTCAT.CATorder, TCorder, TC.id ASC";
}
else if($level == 'testcase')
{
	$sql .= " AND TC.id = " . $id . " AND TC.active = 1";
	$sql .= " ORDER BY MGTCAT.CATorder, TCorder, TC.id ASC";         
}
else
{
	tLog('Invalid GET data', 'ERROR');
	$sql = null;
}
*/

// ----------------------------------------------------------------
// 20060326 - franciscom
$tplan_id = $_SESSION['testPlanId'];

$xx=$tplan_mgr->get_linked_tcversions($tplan_id);

//echo "<pre>debug linked versions" . __FUNCTION__; print_r($xx); echo "</pre>";

$test_spec=array();
$zz=array();
$added=array();
$first_level=array();
$debug_counter=array();
$idx=0;
$jdx=0;


// Get the path for every test case, grouping test cases that
// have same parent.
$items_to_exec=array();

if( $level == 'testcase' )
{
		$items_to_exec[$id]=$xx[$id]['tcversion_id'];    
    $tcase_id = $id;
    $tcversion_id = $xx[$id]['tcversion_id'];
}
else
{
	  $tcase_id=array();
	  $tcversion_id=array();
	  
    foreach($xx as $item)
    {
      $path=$tree_mgr->get_path($item['tc_id'],null,'simplex');
    
      foreach($path as $key => $value)
      {
        if( $value == $id )
        {
          // $items_to_exec[$item['tc_id']]=$item['tcversion_id'];    
          $tcase_id[] =$item['tc_id'];
          $tcversion_id[]=$item['tcversion_id'];
          break;
        }
      } 
    }
}
//$yy=$tcase_mgr->get_by_id($tcase_id,$tcversion_id);
//echo "<pre>debug" . __FUNCTION__; print_r($yy); echo "</pre>";

$zz=$tcase_mgr->get_executions($tcase_id,$tcversion_id,$tplan_id,3);

// echo "<pre>debug "; print_r($zz); echo "</pre>";
// ---------------------------------------------------------------------------------------	
$smarty = new TLSmarty();

$smarty->assign('rightsEdit', has_rights($db,"testplan_execute"));
$smarty->assign('edit_test_results', $editTestResult);

$smarty->assign('arrTC', $zz);
$smarty->assign('build', $buildName);
$smarty->assign('owner', $owner);
$smarty->assign('updated', $submitResult);
$smarty->assign('g_bugInterface', $g_bugInterface);
$smarty->display($g_tpl['execSetResults']);
?>
