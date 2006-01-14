<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execSetResults.php,v $
 *
 * @version $Revision: 1.20 $
 * @modified $Date: 2006/01/14 17:47:54 $ $Author: schlundus $
 *
 * @author Martin Havlat
 *
 *
 * @author 20051119 - Francisco Mancardi - BUGID 0000232: Only admin or leader can update test results
 * @author 20051913 - am - build was displayed
 * @author 20050919 - Francisco Mancardi - refactoring SQL and PHP 
 * @author 20050911 - Francisco Mancardi - refactoring  
 * @author 20050825 - scs - added buginterface to smarty
 * @author 20050821 - Francisco Mancardi - refactoring decrease level of global coupling 
 * @author 20050815 - scs - code optimization
 *
 * 20051112 - scs - corrected using a undefined variable, cosmetic changes
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
$smarty->assign('arrTC', $testdata);
$smarty->assign('build', $buildName);
$smarty->assign('owner', $owner);
$smarty->assign('updated', $submitResult);
$smarty->assign('g_bugInterface', $g_bugInterface);
$smarty->display($g_tpl['execSetResults']);
?>
