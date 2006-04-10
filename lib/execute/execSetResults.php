<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execSetResults.php,v $
 *
 * @version $Revision: 1.25 $
 * @modified $Date: 2006/04/10 09:17:34 $ $Author: franciscom $
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
$build_id = isset($_REQUEST['build']) ? intval($_REQUEST['build']) : 0;
$level = isset($_REQUEST['level']) ? $_REQUEST['level'] : '';
$owner = isset($_REQUEST['owner']) ? $_REQUEST['owner'] : '';

$tplan_id = $_SESSION['testPlanId'];
$the_builds = $tplan_mgr->get_builds_for_html_options($tplan_id);
$build_name = isset($the_builds[$build_id]) ? $the_builds[$build_id] : '';
$user_id = $_SESSION['userID'];


$keyword = 'All';
if( isset($_REQUEST['keyword']) )
{
	$keyword = $db->prepare_string($keyword);
}


define('ANY_BUILD',null);
define('GET_NO_EXEC',1);

/*
*/

// -------------------------------------------------------------------------------------------
// 20060207 - franciscom - BUGID 0000303 - Solution by: scorpfromhell 
// Added to set Test Results editable by comparing themax Build ID and the requested Build ID.			
$editTestResult = "yes";
//$allbuilds = getBuilds($tpID, 'ORDER BY build.id DESC');
//$latestBuild = array_keys($allbuilds);
//$latestBuild = $latestBuild[0];

if(($latestBuild > $build_id) && !(config_get('edit_old_build_results')))
{
	$editTestResult = "no";
}
// -------------------------------------------------------------------------------------------


// ----------------------------------------------------------------
// 20060326 - franciscom
$xx=$tplan_mgr->get_linked_tcversions($tplan_id);
$test_spec=array();
$zz=array();

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


$map_last_exec=$tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$tplan_id,$build_id,GET_NO_EXEC);
if (isset($_REQUEST['save_results']))
{
	 $submitResult = write_execution($db,$user_id,$_REQUEST,$tplan_id,$build_id,$map_last_exec);
}
$map_last_exec_any_build=$tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$tplan_id,ANY_BUILD,GET_NO_EXEC);

$exec_to_exclude=null;
if( is_array($map_last_exec) )
{
    $exec_to_exclude=array();
		foreach($map_last_exec as $key => $value)
		{
			  if( intval($value['execution_id']) > 0 )
			  {
   					$exec_to_exclude[] = 	$value['execution_id'];
   			}
		}
		
}
$other_execs=$tcase_mgr->get_executions($tcase_id,$tcversion_id,$tplan_id,$build_id);

// ---------------------------------------------------------------------------------------	
$smarty = new TLSmarty();

$smarty->assign('rightsEdit', has_rights($db,"testplan_execute"));
$smarty->assign('edit_test_results', $editTestResult);

$smarty->assign('map_last_exec', $map_last_exec);
$smarty->assign('other_exec', $other_execs);
$smarty->assign('map_last_exec_any_build', $map_last_exec_any_build);

$smarty->assign('build_name', $build_name);


$smarty->assign('owner', $owner);
$smarty->assign('updated', $submitResult);
$smarty->assign('g_bugInterface', $g_bugInterface);
$smarty->display($g_tpl['execSetResults']);
?>
