<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execSetResults.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2005/08/26 21:01:27 $
 *
 * @author Martin Havlat
 *
 * @todo bugs and owner are not working	    
 *
 * @author Francisco Mancardi - 20050821 
 * refactoring decrease level of global coupling 
 *
 * @author Francisco Mancardi - 20050807 
 * refactoring:  removed deprecated: $_SESSION['project']
 *
 * @author 20050815 - scs - code optimization
 * @author 20050825 - scs - added buginterface to smarty
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
if (isset($_POST['submitTestResults']))
	$submitResult = editTestResults($_POST);

$keyword = isset($_GET['keyword']) ? strings_stripSlashes($_GET['keyword']) : 'All';
if ($keyword != 'All')
	$keyword = mysql_escape_string($keyword);

//parse input; two possibilities: GET and POST (tree menu and update)
if (isset($_GET['build']))
	$_INPUT = $_GET;
else
{
	tLog('Missing input arguments GET', 'ERROR');
	exit();
}

$id = isset($_INPUT['id']) ? intval($_INPUT['id']) : 0;
$level = isset($_INPUT['level']) ? strings_stripSlashes($_INPUT['level']) : '';
$owner = isset($_INPUT['owner']) ? strings_stripSlashes($_INPUT['owner']) : '';
$build = isset($_GET['build']) ? intval($_GET['build']) : 0;

// 20050821 - fm
$tpID = $_SESSION['testPlanId'];
$builds = getBuilds($tpID);
$buildName = isset($builds[$build]) ? $builds[$build] : '';


// Collect data of test cases and results
//if the user has selected to view by component
if($level == 'component')
{ 
	$catResult = do_mysql_query(" SELECT category.id, category.name FROM component,category " .
	                            " WHERE component.id = " . $id .
			                        " AND component.id = category.compid ORDER BY CATorder",$db);
	
	$catIDs = null;
	while ($myrowCAT = mysql_fetch_row($catResult))
		$catIDs[] = $myrowCAT[0];
	if ($catIDs)
	{
		$catIDs = implode(",",$catIDs);
		
		$sql = "SELECT testcase.id, title, summary, steps, exresult, keywords,mgttcid,version " .
		       "FROM testcase,category " .
		       "WHERE category.id IN (" . $catIDs . ") AND testcase.catid = category.id";
		if($keyword != 'All')
			$sql .= " AND (testcase.keywords LIKE '%,{$keyword},%' OR testcase.keywords like '{$keyword},%')";
		$sql .= " order by CATorder,TCorder,testcase.id ASC";
		$result = do_mysql_query($sql,$db);
		
		// 20050821 - fm
		$testdataSuite = createTestInput($result,$build,$tpID);
		foreach ($testdataSuite as $tmp)
		{
			$testdata[] = $tmp;
		}
	}
}
//if the user has selected to view by category
else if($level == 'category')
{ 
		$sql = " SELECT testcase.id, title, summary, steps, exresult, keywords, mgttcid, version " .
		       " FROM testcase,category WHERE category.id = " . $id . " AND testcase.catid = category.id	";
		       
		       
		if($keyword != 'All')
			$sql .= " AND (testcase.keywords LIKE '%,{$keyword},%' OR testcase.keywords like '{$keyword},%')";
		$sql .= " ORDER BY TCorder, testcase.id ASC";
		$result = do_mysql_query($sql,$db);

    // 20050821 - fm
		$testdata = createTestInput($result,$build,$tpID);				
}
else if($level == 'testcase')
{
	$query = " SELECT testcase.id, title, summary, steps, exresult, keywords,mgttcid,version " .
	         " FROM testcase WHERE testcase.id = " . $id . " AND testcase.active = 1";
	         
	if ($keyword != 'All')
		$query .= " AND (testcase.keywords LIKE '%,{$keyword},%' OR testcase.keywords like '{$keyword},%')";
	$result = do_mysql_query($query,$db);

  // 20050821 -fm
	$testdata = createTestInput($result,$build,$tpID);				
}
else
	tLog('Invalid GET data', 'ERROR');
	
	
	
// ---------------------------------------------------------------------------------------	
// launch viewer	
$smarty = new TLSmarty();
$smarty->assign('rightsEdit', has_rights("tp_planning"));
$smarty->assign('arrTC', $testdata);
$smarty->assign('build', $buildName);
$smarty->assign('owner', $owner);
$smarty->assign('updated', $submitResult);
$smarty->assign('g_bugInterface', $g_bugInterface);
$smarty->display('execSetResults.tpl');
?>
