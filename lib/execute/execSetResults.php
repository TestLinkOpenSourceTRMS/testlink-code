<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execSetResults.php,v $
 *
 * @version $Revision: 1.11 $
 * @modified $Date: 2005/09/19 17:48:05 $ $Author: franciscom $
 *
 * @author Martin Havlat
 *
 * @todo bugs and owner are not working	    
 *
 * @author 20050919 - Francisco Mancardi - refactoring SQL and PHP 
 * @author 20050911 - Francisco Mancardi - refactoring  
 *
 * @author 20050825 - scs - added buginterface to smarty
 * @author 20050821 - Francisco Mancardi  
 * refactoring decrease level of global coupling 
 *
 * @author 20050815 - scs - code optimization
 * @author 20050807 - Francisco Mancardi  
 * refactoring:  removed deprecated: $_SESSION['project']
 *
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("../../lib/functions/lang_api.php");
require_once("../../lib/functions/builds.inc.php");
testlinkInitPage();

$testdata = array();
$submitResult = null;


$_REQUEST = strings_stripSlashes($_REQUEST);

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$buildID = isset($_REQUEST['build']) ? intval($_REQUEST['build']) : 0;
$level = isset($_REQUEST['level']) ? strings_stripSlashes($_REQUEST['level']) : '';
$owner = isset($_REQUEST['owner']) ? strings_stripSlashes($_REQUEST['owner']) : '';

$keyword = 'All';
if( isset($_REQUEST['keyword']) )
{
	$keyword = mysql_escape_string($keyword);
}


if (isset($_REQUEST['submitTestResults']))
{
	// 20050905 - fm
	// 20060908 - scs - fixed 90
	$submitResult = editTestResults($_SESSION['user'],$_REQUEST,$_GET['build']);
}


// 20050821 - fm
$tpID = $_SESSION['testPlanId'];
$builds = getBuilds($tpID);
$buildName = isset($builds[$buildID]) ? $builds[$buildID] : '';

// 20050919 - fm
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


// Collect data of test cases and results
//if the user has selected to view by component
if($level == 'component')
{ 

  $sql .= " AND   COMP.id = " . $id;
	$sql .= " ORDER BY MGTCAT.CATorder, TCorder, TC.id ASC";

	$result = do_mysql_query($sql,$db);
	$testdata = createTestInput($result,$buildID,$tpID);				

	foreach ($testdataSuite as $tmp)
	{
		$testdata[] = $tmp;
	}
}
//if the user has selected to view by category
else if($level == 'category')
{ 
		$sql .= " AND CAT.id = " . $id ;
		$sql .= " ORDER BY MGTCAT.CATorder, TCorder, TC.id ASC";
		$result = do_mysql_query($sql,$db);
  	$testdata = createTestInput($result,$buildID,$tpID);				
}
else if($level == 'testcase')
{
	$sql .= " AND TC.id = " . $id . " AND TC.active = 1";
	$sql .= " ORDER BY MGTCAT.CATorder, TCorder, TC.id ASC";         
	$result = do_mysql_query($sql,$db);
	$testdata = createTestInput($result,$buildID,$tpID);				
}
else
	tLog('Invalid GET data', 'ERROR');
	
	
	
// ---------------------------------------------------------------------------------------	
// launch viewer	
$smarty = new TLSmarty();
$smarty->assign('rightsEdit', has_rights("tp_planning"));
$smarty->assign('arrTC', $testdata);
$smarty->assign('build', $buildIDName);
$smarty->assign('owner', $owner);
$smarty->assign('updated', $submitResult);
$smarty->assign('g_bugInterface', $g_bugInterface);

$smarty->display($g_tpl['execSetResults']);
?>
