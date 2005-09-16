<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execSetResults.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2005/09/16 06:47:11 $
 *
 * @author Martin Havlat
 *
 * @todo bugs and owner are not working	    
 *
 * @author 20050911 - Francisco Mancardi - refactoring  
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
$build = isset($_REQUEST['build']) ? intval($_REQUEST['build']) : 0;
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
$buildName = isset($builds[$build]) ? $builds[$build] : '';


// Collect data of test cases and results
//if the user has selected to view by component
if($level == 'component')
{ 

  $sql = " SELECT category.id, mgtcategory.name " .
         " FROM  component,category,mgtcategory " .
         " WHERE component.id = category.compid " .
         " AND   mgtcategory.id = category.mgtcatid " .
         " AND component.id = " . $id .
	       " ORDER BY mgtcategory.CATorder";
	
	$catResult = do_mysql_query($sql,$db);
	
	$catIDs = null;
	while ($myrowCAT = mysql_fetch_assoc($catResult))
	{
		$catIDs[] = $myrowCAT['id'];
	}	
	if ($catIDs)
	{
		$catIDs = implode(",",$catIDs);
		
		$sql = "SELECT testcase.id, title, summary, steps, exresult, keywords,mgttcid,version " .
		       "FROM testcase,category " .
		       "WHERE category.id IN (" . $catIDs . ") AND testcase.catid = category.id";
		if($keyword != 'All')
		{
			$sql .= " AND (testcase.keywords LIKE '%,{$keyword},%' OR testcase.keywords like '{$keyword},%')";
		}	
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
		{
			$sql .= " AND (testcase.keywords LIKE '%,{$keyword},%' OR testcase.keywords like '{$keyword},%')";
		}	
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

$smarty->display($g_tpl['execSetResults']);
?>
