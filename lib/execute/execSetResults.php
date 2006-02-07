<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execSetResults.php,v $
 *
 * @version $Revision: 1.21 $
 * @modified $Date: 2006/02/07 11:17:24 $ $Author: franciscom $
 *
 * @author Martin Havlat
 *
 * 20060207 - franciscom - BUGID 0000303 - Solution by: scorpfromhell
 * 20051219 - am - build was displayed
 * 20051119 - Francisco Mancardi - BUGID 0000232: Only admin or leader can update test results
 * 20051119 - Francisco Mancardi - BUGID 0000232: Only admin or leader can update test results
 * 20050919 - Francisco Mancardi - refactoring SQL and PHP 
 * 20050911 - Francisco Mancardi - refactoring  
 * 20050825 - scs - added buginterface to smarty
 * 20050821 - Francisco Mancardi - refactoring decrease level of global coupling 
 * 20050815 - scs - code optimization
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("../../lib/functions/builds.inc.php");
testlinkInitPage($db);

$testdata = array();
$submitResult = null;

$_REQUEST = strings_stripSlashes($_REQUEST);
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$buildID = isset($_REQUEST['build']) ? intval($_REQUEST['build']) : 0;
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
	$submitResult = editTestResults($db,$_SESSION['user'],$_REQUEST,$_GET['build']);
}

$tpID = $_SESSION['testPlanId'];
$builds = getBuilds($db,$tpID, " ORDER BY build.name ");
$buildName = isset($builds[$buildID]) ? $builds[$buildID] : '';

// -------------------------------------------------------------------------------------------
// 20060207 - franciscom - BUGID 0000303 - Solution by: scorpfromhell 
// Added to set Test Results editable by comparing themax Build ID and the requested Build ID.			
$editTestResult = "yes";
$allbuilds = getBuilds($tpID, 'ORDER BY build.id DESC');
$latestBuild = array_keys($allbuilds);
$latestBuild = $latestBuild[0];

if(($latestBuild > $buildID) && !(config_get('edit_old_build_results')))
{
	$editTestResult = "no";
}
// -------------------------------------------------------------------------------------------

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
if (!is_null($sql))
{
	$result = $db->exec_query($sql,$db);
	$testdata = createTestInput($db,$result,$buildID,$tpID);				
}	
// ---------------------------------------------------------------------------------------	
$smarty = new TLSmarty();

// 20051119 - fm - BUGID 0000232: Only admin or leader can update test results
$smarty->assign('rightsEdit', has_rights($db,"tp_execute"));

// 20060207 - franciscom - BUGID 0000303 - Solution by: scorpfromhell
$smarty->assign('edit_test_results', $editTestResult);

$smarty->assign('arrTC', $testdata);
$smarty->assign('build', $buildName);
$smarty->assign('owner', $owner);
$smarty->assign('updated', $submitResult);
$smarty->assign('g_bugInterface', $g_bugInterface);
$smarty->display($g_tpl['execSetResults']);
?>
